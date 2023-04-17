<?php
namespace AttuazioneControlloBundle\Fascicoli;

use AttuazioneControlloBundle\Entity\Pagamento;
use DateTime;
use FascicoloBundle\Entity\IstanzaFascicolo;
use FascicoloBundle\Entity\IstanzaPagina;
use GeoBundle\Entity\GeoComune;
use PDO;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class BandoMontagna2022
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    protected $fascicoloService;

    /**
     * @param $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->fascicoloService = $this->container->get("fascicolo.istanza");
    }

    /**
     * @param IstanzaPagina $istanzaPagina
     * @return ConstraintViolationList
     */
    public function validaLocalizzazioneImmobile(IstanzaPagina $istanzaPagina): ConstraintViolationList
    {
        $violazioni = new ConstraintViolationList();
        $istanzaFascicolo = $istanzaPagina->getIstanzaFascicolo();
        $em = $this->container->get("doctrine")->getManager();
        /** @var Pagamento $pagamento */
        $pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->findOneBy(["istanza_fascicolo" => $istanzaFascicolo->getId()]);
        $richiesta = $pagamento->getRichiesta();
        $oggettoRichiesta = $richiesta->getPrimoOggetto();
        $istanzaFascicoloPresentazione = $oggettoRichiesta->getIstanzaFascicolo();

        $idComuneImmobilePresentazione = $this->fascicoloService
            ->getOne($istanzaFascicoloPresentazione, 'bando_montagna_2022_171.indice.sezione_alloggio.form_localizzazione_immobile.comune_immobile', true);
        /** @var GeoComune $comuneImmobileRendicontazione */
        $comuneImmobilePresentazione = $em->getRepository("GeoBundle\Entity\GeoComune")->find($idComuneImmobilePresentazione);

        $idComuneImmobileRendicontazione =  $this->fascicoloService
            ->getOne($istanzaFascicolo, 'rendicontazione_bando_montagna_2022_171.indice.sezione_localizzazione_immobile.form_localizzazione_immobile.comune', true);

        if ($idComuneImmobileRendicontazione) {
            /** @var GeoComune $comuneImmobileRendicontazione */
            $comuneImmobileRendicontazione = $em->getRepository("GeoBundle\Entity\GeoComune")->find($idComuneImmobileRendicontazione);

            $fasciaComunePresentazione = $this->getInfoComune($comuneImmobilePresentazione->getCodiceCompleto());
            $fasciaComuneRendicontazione = $this->getInfoComune($comuneImmobileRendicontazione->getCodiceCompleto());
            $denominazioneComunePresentazione = $this->getInfoComune($comuneImmobilePresentazione->getCodiceCompleto(), false);
            $denominazioneComuneRendicontazione = $this->getInfoComune($comuneImmobileRendicontazione->getCodiceCompleto(), false);

            $erroreFascia = 'Comune indicato in fase di presentazione: '
                . $denominazioneComunePresentazione . ' di <strong>Fascia ' . $fasciaComunePresentazione . '</strong>.<br/>
                Comune indicato in fase di rendicontazione: ' . $denominazioneComuneRendicontazione . ' di <strong>Fascia ' . $fasciaComuneRendicontazione . '</strong>.<br/>
                Il comune della fase di rendicontazione deve essere della stessa fascia del comune indicato in fase di presentazione oppure di fascia superiore.';

            $erroreFusioneComuni = 'In fase di presentazione è stato indicato un comune appartenente ad una fusione di comuni ('
                . $denominazioneComunePresentazione . ') pertanto è necessario indicare un comune che appartenenga ad una fusione di comuni.';

            $controlloFascia = $fasciaComuneRendicontazione < $fasciaComunePresentazione;
            $controlloFusioneComuni = strstr($denominazioneComunePresentazione, 'Ex comune oggetto di fusione')
                && strstr($denominazioneComuneRendicontazione, 'Ex comune oggetto di fusione') === false;

            if ($controlloFascia && $controlloFusioneComuni) {
                $violazioni->add(new ConstraintViolation($erroreFascia . '<br/><br/>Inoltre:<br/><br/>' . $erroreFusioneComuni, null, [], $istanzaPagina, "", null));
            } elseif ($controlloFascia) {
                $violazioni->add(new ConstraintViolation($erroreFascia, null, [], $istanzaPagina, "", null));
            } elseif ($controlloFusioneComuni) {
                $violazioni->add(new ConstraintViolation($erroreFusioneComuni, null, [], $istanzaPagina, "", null));
            }
        }

        return $violazioni;
    }

    /**
     * @param int $codiceCompletoComune
     * @param $getFascia
     * @return array|false|mixed|string|string[]|null
     */
    public function getInfoComune(int $codiceCompletoComune, $getFascia = true)
    {
        $em = $this->container->get("doctrine")->getManager();
        $conn = $em->getConnection();
        $sql = '
        SELECT
    comune.id AS codice,
    CONCAT(
		COALESCE(
			CASE
				WHEN comune.denominazione = "GRANAGLIONE" THEN "Alto Reno Terme - "
				WHEN comune.denominazione = "PORRETTA TERME" THEN "Alto Reno Terme - "

				WHEN comune.denominazione = "CASTELLO DI SERRAVALLE" THEN "Valsamoggia - "
				WHEN comune.denominazione = "MONTEVEGLIO" THEN "Valsamoggia - "
				WHEN comune.denominazione = "SAVIGNO" THEN "Valsamoggia - "

				WHEN comune.denominazione = "PECORARA" THEN "Alta Val Tidone - "

				WHEN comune.denominazione = "BUSANA" THEN "Ventasso - "
				WHEN comune.denominazione = "COLLAGNA" THEN "Ventasso - "
				WHEN comune.denominazione = "LIGONCHIO" THEN "Ventasso - "
				WHEN comune.denominazione = "RAMISETO" THEN "Ventasso - "

				WHEN comune.denominazione = "TORRIANA" THEN "Poggio Torriana - "
			END
		, ""),

    COALESCE(comune.denominazione, ""),
            " (",
            COALESCE(provincia.sigla_automobilistica, ""),
            ")",
	COALESCE(
		CASE
			-- WHEN comune.denominazione = "ALTO RENO TERME" THEN " - Fascia 0"
			WHEN comune.denominazione = "BORGO TOSSIGNANO" THEN " - Fascia 0"
			WHEN comune.denominazione = "CAMUGNANO" THEN " - Fascia 1"
			WHEN comune.denominazione = "CASALFIUMANESE" THEN " - Fascia 1"
			WHEN comune.denominazione = "CASTEL D\'AIANO" THEN " - Fascia 1"
			WHEN comune.denominazione = "CASTEL DEL RIO" THEN " - Fascia 1"
			WHEN comune.denominazione = "CASTEL DI CASIO" THEN " - Fascia 1"
			WHEN comune.denominazione = "CASTIGLIONE DEI PEPOLI" THEN " - Fascia 1"
			WHEN comune.denominazione = "FONTANELICE" THEN " - Fascia 0"
			WHEN comune.denominazione = "GAGGIO MONTANO" THEN " - Fascia 0"
			WHEN comune.denominazione = "GRIZZANA MORANDI" THEN " - Fascia 0"
			WHEN comune.denominazione = "LIZZANO IN BELVEDERE" THEN " - Fascia 1"
			WHEN comune.denominazione = "LOIANO" THEN " - Fascia 0"
			WHEN comune.denominazione = "MARZABOTTO" THEN " - Fascia 0"
			WHEN comune.denominazione = "MONGHIDORO" THEN " - Fascia 0"
			WHEN comune.denominazione = "MONTE SAN PIETRO" THEN " - Fascia 0"
			WHEN comune.denominazione = "MONTERENZIO" THEN " - Fascia 0"
			WHEN comune.denominazione = "MONZUNO" THEN " - Fascia 0"
			WHEN comune.denominazione = "PIANORO" THEN " - Fascia 0"
			WHEN comune.denominazione = "SAN BENEDETTO VAL DI SAMBRO" THEN " - Fascia 0"
			WHEN comune.denominazione = "SASSO MARCONI" THEN " - Fascia 0"
			WHEN comune.denominazione = "VALSAMOGGIA" THEN " - Fascia 0"
			WHEN comune.denominazione = "VERGATO" THEN " - Fascia 1"
			WHEN comune.denominazione = "BAGNO DI ROMAGNA" THEN " - Fascia 1"
			WHEN comune.denominazione = "BORGHI" THEN " - Fascia 1"
			WHEN comune.denominazione = "CIVITELLA DI ROMAGNA" THEN " - Fascia 0"
			WHEN comune.denominazione = "DOVADOLA" THEN " - Fascia 1"
			WHEN comune.denominazione = "GALEATA" THEN " - Fascia 1"
			WHEN comune.denominazione = "MELDOLA" THEN " - Fascia 0"
			WHEN comune.denominazione = "MERCATO SARACENO" THEN " - Fascia 0"
			WHEN comune.denominazione = "MODIGLIANA" THEN " - Fascia 1"
			WHEN comune.denominazione = "PORTICO E SAN BENEDETTO" THEN " - Fascia 2"
			WHEN comune.denominazione = "PREDAPPIO" THEN " - Fascia 1"
			WHEN comune.denominazione = "PREMILCUORE" THEN " - Fascia 2"
			WHEN comune.denominazione = "ROCCA SAN CASCIANO" THEN " - Fascia 1"
			WHEN comune.denominazione = "RONCOFREDDO" THEN " - Fascia 0"
			WHEN comune.denominazione = "SANTA SOFIA" THEN " - Fascia 0"
			WHEN comune.denominazione = "SARSINA" THEN " - Fascia 1"
			WHEN comune.denominazione = "SOGLIANO AL RUBICONE" THEN " - Fascia 1"
			WHEN comune.denominazione = "TREDOZIO" THEN " - Fascia 1"
			WHEN comune.denominazione = "VERGHERETO" THEN " - Fascia 1"
			WHEN comune.denominazione = "FANANO" THEN " - Fascia 1"
			WHEN comune.denominazione = "FIUMALBO" THEN " - Fascia 2"
			WHEN comune.denominazione = "FRASSINORO" THEN " - Fascia 2"
			WHEN comune.denominazione = "GUIGLIA" THEN " - Fascia 0"
			WHEN comune.denominazione = "LAMA MOCOGNO" THEN " - Fascia 1"
			WHEN comune.denominazione = "MARANO SUL PANARO" THEN " - Fascia 0"
			WHEN comune.denominazione = "MONTECRETO" THEN " - Fascia 1"
			WHEN comune.denominazione = "MONTEFIORINO" THEN " - Fascia 1"
			WHEN comune.denominazione = "MONTESE" THEN " - Fascia 1"
			WHEN comune.denominazione = "PALAGANO" THEN " - Fascia 1"
			WHEN comune.denominazione = "PAVULLO NEL FRIGNANO" THEN " - Fascia 0"
			WHEN comune.denominazione = "PIEVEPELAGO" THEN " - Fascia 2"
			WHEN comune.denominazione = "POLINAGO" THEN " - Fascia 1"
			WHEN comune.denominazione = "PRIGNANO SULLA SECCHIA" THEN " - Fascia 0"
			WHEN comune.denominazione = "RIOLUNATO" THEN " - Fascia 2"
			WHEN comune.denominazione = "SERRAMAZZONI" THEN " - Fascia 0"
			WHEN comune.denominazione = "SESTOLA" THEN " - Fascia 1"
			WHEN comune.denominazione = "ZOCCA" THEN " - Fascia 1"
			WHEN comune.denominazione = "ALTA VAL TIDONE" THEN " - Fascia 2"
			WHEN comune.denominazione = "BETTOLA" THEN " - Fascia 2"
			WHEN comune.denominazione = "BOBBIO" THEN " - Fascia 2"
			WHEN comune.denominazione = "CERIGNALE" THEN " - Fascia 2"
			WHEN comune.denominazione = "COLI" THEN " - Fascia 2"
			WHEN comune.denominazione = "CORTE BRUGNATELLA" THEN " - Fascia 2"
			WHEN comune.denominazione = "FARINI" THEN " - Fascia 2"
			WHEN comune.denominazione = "FERRIERE" THEN " - Fascia 2"
			WHEN comune.denominazione = "GROPPARELLO" THEN " - Fascia 2"
			WHEN comune.denominazione = "MORFASSO" THEN " - Fascia 2"
			WHEN comune.denominazione = "OTTONE" THEN " - Fascia 2"
			WHEN comune.denominazione = "PIOZZANO" THEN " - Fascia 2"
			WHEN comune.denominazione = "TRAVO" THEN " - Fascia 1"
			WHEN comune.denominazione = "VERNASCA" THEN " - Fascia 2"
			WHEN comune.denominazione = "ZERBA" THEN " - Fascia 2"
			WHEN comune.denominazione = "ALBARETO" THEN " - Fascia 2"
			WHEN comune.denominazione = "BARDI" THEN " - Fascia 2"
			WHEN comune.denominazione = "BEDONIA" THEN " - Fascia 2"
			WHEN comune.denominazione = "BERCETO" THEN " - Fascia 1"
			WHEN comune.denominazione = "BORE" THEN " - Fascia 2"
			WHEN comune.denominazione = "BORGO VAL DI TARO" THEN " - Fascia 1"
			WHEN comune.denominazione = "CALESTANO" THEN " - Fascia 0"
			WHEN comune.denominazione = "COMPIANO" THEN " - Fascia 2"
			WHEN comune.denominazione = "CORNIGLIO" THEN " - Fascia 2"
			WHEN comune.denominazione = "FORNOVO DI TARO" THEN " - Fascia 0"
			WHEN comune.denominazione = "LANGHIRANO" THEN " - Fascia 0"
			WHEN comune.denominazione = "LESIGNANO DE\' BAGNI" THEN " - Fascia 0"
			WHEN comune.denominazione = "MONCHIO DELLE CORTI" THEN " - Fascia 2"
			WHEN comune.denominazione = "NEVIANO DEGLI ARDUINI" THEN " - Fascia 1"
			WHEN comune.denominazione = "PALANZANO" THEN " - Fascia 2"
			WHEN comune.denominazione = "PELLEGRINO PARMENSE" THEN " - Fascia 2"
			WHEN comune.denominazione = "SOLIGNANO" THEN " - Fascia 1"
			WHEN comune.denominazione = "TERENZO" THEN " - Fascia 1"
			WHEN comune.denominazione = "TIZZANO VAL PARMA" THEN " - Fascia 0"
			WHEN comune.denominazione = "TORNOLO" THEN " - Fascia 2"
			WHEN comune.denominazione = "VALMOZZOLA" THEN " - Fascia 1"
			WHEN comune.denominazione = "VARANO DE\' MELEGARI" THEN " - Fascia 0"
			WHEN comune.denominazione = "VARSI" THEN " - Fascia 2"
			WHEN comune.denominazione = "BRISIGHELLA" THEN " - Fascia 1"
			WHEN comune.denominazione = "CASOLA VALSENIO" THEN " - Fascia 1"
			WHEN comune.denominazione = "RIOLO TERME" THEN " - Fascia 0"
			WHEN comune.denominazione = "BAISO" THEN " - Fascia 1"
			WHEN comune.denominazione = "CANOSSA" THEN " - Fascia 0"
			WHEN comune.denominazione = "CARPINETI" THEN " - Fascia 1"
			WHEN comune.denominazione = "CASINA" THEN " - Fascia 0"
			WHEN comune.denominazione = "CASTELNOVO NE\' MONTI" THEN " - Fascia 0"
			WHEN comune.denominazione = "TOANO" THEN " - Fascia 1"
			WHEN comune.denominazione = "VENTASSO" THEN " - Fascia 2"
			WHEN comune.denominazione = "VETTO" THEN " - Fascia 1"
			WHEN comune.denominazione = "VIANO" THEN " - Fascia 0"
			WHEN comune.denominazione = "VILLA MINOZZO" THEN " - Fascia 2"
			WHEN comune.denominazione = "CASTELDELCI" THEN " - Fascia 2"
			WHEN comune.denominazione = "MAIOLO" THEN " - Fascia 1"
			WHEN comune.denominazione = "MONTECOPIOLO" THEN " - Fascia 2"
			WHEN comune.denominazione = "NOVAFELTRIA" THEN " - Fascia 1"
			WHEN comune.denominazione = "PENNABILLI" THEN " - Fascia 2"
			WHEN comune.denominazione = "POGGIO TORRIANA" THEN " - Fascia 1"
			WHEN comune.denominazione = "SAN LEO" THEN " - Fascia 1"
			WHEN comune.denominazione = "SANT\'AGATA FELTRIA" THEN " - Fascia 2"
			WHEN comune.denominazione = "SASSOFELTRIO" THEN " - Fascia 1"
			WHEN comune.denominazione = "TALAMELLO" THEN " - Fascia 0"
			WHEN comune.denominazione = "VERUCCHIO" THEN " - Fascia 0"

            WHEN comune.denominazione = "GRANAGLIONE" THEN " - Fascia 0 - Ex comune oggetto di fusione"
			WHEN comune.denominazione = "PORRETTA TERME" THEN " - Fascia 0 - Ex comune oggetto di fusione"

			WHEN comune.denominazione = "CASTELLO DI SERRAVALLE" THEN " - Fascia 0 - Ex comune oggetto di fusione"
			WHEN comune.denominazione = "MONTEVEGLIO" THEN " - Fascia 0 - Ex comune oggetto di fusione"
			WHEN comune.denominazione = "SAVIGNO" THEN " - Fascia 0 - Ex comune oggetto di fusione"

			WHEN comune.denominazione = "PECORARA" THEN " - Fascia 2 - Ex comune oggetto di fusione"

			WHEN comune.denominazione = "BUSANA" THEN " - Fascia 2 - Ex comune oggetto di fusione"
			WHEN comune.denominazione = "COLLAGNA" THEN " - Fascia 2 - Ex comune oggetto di fusione"
			WHEN comune.denominazione = "LIGONCHIO" THEN " - Fascia 2 - Ex comune oggetto di fusione"
			WHEN comune.denominazione = "RAMISETO" THEN " - Fascia 2 - Ex comune oggetto di fusione"

			WHEN comune.denominazione = "TORRIANA" THEN " - Fascia 1 - Ex comune oggetto di fusione"
		END
	, "")
            ) AS descrizione
FROM
    geo_comuni AS comune
        JOIN
    geo_province AS provincia ON (comune.provincia_id = provincia.id)

WHERE comune.codice_completo = :id_comune
GROUP BY comune.id';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id_comune' => $codiceCompletoComune]);
        $result = $stmt->fetch(PDO::FETCH_BOTH);
        if (is_array($result) && !empty($result['descrizione'])) {
            if ($getFascia) {
                $fascia = strstr($result['descrizione'], 'Fascia ');
                // Estraggo dalla stringa solamente il numero che indica la fascia.
                return preg_replace('/[^0-9]/', '', $fascia);
            } else {
                return $result['descrizione'];
            }
        }

        return null;
    }

    /**
     * @param IstanzaFascicolo $istanzaFascicolo
     * @return bool
     */
    public function mostraLocalizzazioneImmobile(IstanzaFascicolo $istanzaFascicolo): bool
    {
        $em = $this->container->get("doctrine")->getManager();
        /** @var Pagamento $pagamento */
        $pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->findOneBy(["istanza_fascicolo" => $istanzaFascicolo->getId()]);
        $richiesta = $pagamento->getRichiesta();
        $oggettoRichiesta = $richiesta->getPrimoOggetto();
        $istanzaFascicoloPresentazione = $oggettoRichiesta->getIstanzaFascicolo();

        $alloggioGiaIndividuato = $this->fascicoloService
            ->getOne($istanzaFascicoloPresentazione, 'bando_montagna_2022_171.indice.sezione_alloggio.form_alloggio_gia_individuato.alloggio_gia_individuato', true);
        if ($alloggioGiaIndividuato === '1') {
            return true;
        }
        return false;
    }

    /**
     * @param IstanzaPagina $istanzaPagina
     * @return ConstraintViolationList
     */
    public function validaDataRogito(IstanzaPagina $istanzaPagina): ConstraintViolationList
    {
        $violazioni = new ConstraintViolationList();
        $istanzaFascicolo = $istanzaPagina->getIstanzaFascicolo();

        $em = $this->container->get("doctrine")->getManager();
        /** @var Pagamento $pagamento */
        $pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->findOneBy(["istanza_fascicolo" => $istanzaFascicolo->getId()]);
        $richiesta = $pagamento->getRichiesta();
        $istruttoria = $richiesta->getIstruttoria();
        $dataAvvioProgetto = $istruttoria->getDataAvvioProgetto()->setTime(0,0,0);
        $dataTermineProgetto = $istruttoria->getDataTermineProgetto()->setTime(23,59,59);
        $dataRogito = $this->fascicoloService->getOne($istanzaFascicolo, 'rendicontazione_bando_montagna_2022_171.indice.sezione_data_rogito.form_data_rogito.data_rogito');
        $oggi = new DateTime();
        $oggi->setTime(23,59,59);

        if ($dataRogito) {
            $dataRogito = DateTime::createFromFormat('d/m/Y', $dataRogito);

            if ($dataRogito < $dataAvvioProgetto) {
                $violazioni->add(new ConstraintViolation('La data del rogito deve essere uguale o successiva al ' . $dataAvvioProgetto->format('d/m/Y'), null, array(), $istanzaPagina, "", null));
            } elseif ($dataRogito > $dataTermineProgetto) {
                $violazioni->add(new ConstraintViolation('La data del rogito deve essere uguale o precedente al ' . $dataTermineProgetto->format('d/m/Y'), null, array(), $istanzaPagina, "", null));
            } elseif ($dataRogito > $oggi) {
                $violazioni->add(new ConstraintViolation('La data del rogito deve essere uguale o precedente al ' . $oggi->format('d/m/Y'), null, array(), $istanzaPagina, "", null));
            }
        }

        return $violazioni;
    }

    /**
     * @param IstanzaPagina $istanzaPagina
     * @return ConstraintViolationList
     */
    public function validaDataRichiestaResidenza(IstanzaPagina $istanzaPagina): ConstraintViolationList
    {
        $violazioni = new ConstraintViolationList();
        $istanzaFascicolo = $istanzaPagina->getIstanzaFascicolo();

        $dataRichiestaResidenza = $this->fascicoloService->getOne($istanzaFascicolo, 'rendicontazione_bando_montagna_2022_171.indice.sezione_data_richiesta_residenza.form_data_richiesta_residenza.data_richiesta_residenza');
        $dataRichiestaResidenza = DateTime::createFromFormat('d/m/Y', $dataRichiestaResidenza);

        $oggi = new DateTime();
        $oggi->setTime(23,59,59);

        if ($dataRichiestaResidenza) {
            if ($dataRichiestaResidenza > $oggi) {
                $violazioni->add(new ConstraintViolation('La data di richiesta di residenza non può essere successiva alla data odierna.', null, array(), $istanzaPagina, "", null));
            }
        }

        return $violazioni;
    }
}
