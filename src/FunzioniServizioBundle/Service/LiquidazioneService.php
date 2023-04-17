<?php
namespace FunzioniServizioBundle\Service;

use AnagraficheBundle\Entity\Persona;
use AttuazioneControlloBundle\Entity\DatiBancari;
use AttuazioneControlloBundle\Entity\Partita;
use DateTime;
use Exception;
use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Entity\Richiesta;
use SfingeBundle\Entity\Procedura;

/**
 * Class LiquidazioneService
 */
class LiquidazioneService
{
    const MAX_TIME_LIMIT_ELAB = 600; // 10 minuti

    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @param array $arrayIdRichieste
     * @param string $ambiente
     * @return string[]
     * @throws Exception
     */
    public function generaQuietanzeBatch(array $arrayIdRichieste, string $ambiente): array
    {
        try {
            set_time_limit(self::MAX_TIME_LIMIT_ELAB);
            $em = $this->container->get("doctrine")->getManager();
            $retVal = ['creati' => [], 'gia_presenti' => [], 'altri' => []];

            if (!empty($arrayIdRichieste)) {
                foreach ($arrayIdRichieste as $idRichiesta) {
                    /** @var Richiesta $richiesta */
                    $richiesta = $this->container->get("doctrine")
                        ->getRepository("RichiesteBundle:Richiesta")->find($idRichiesta);
                    /** @var Proponente $soggetto */
                    $proponente = $richiesta->getMandatario();
                    /** @var DatiBancari[] $datiBancari */
                    $datiBancari = $proponente->getDatiBancari();

                    if ($datiBancari[0]) {
                        $datoBancario = $datiBancari[0];
                        $iban = strtoupper($datoBancario->getIban());
                        $lifnr = $this->getLifnrRichiesta($richiesta);

                        // Crea quietanza tramite WS
                        $result = $this->container->get('app.sap_service')->creaQuietanza(
                            $iban, $lifnr, 20, '', '', $ambiente
                        );

                        if ($result->E_RC === 0) {
                            // Creazione Iban
                            $datoBancario->setFlagIbanSap(1);
                            $datoBancario->setDataCreazioneIbanSap(new DateTime());
                            $datoBancario->setProgressivoIbanSap($result->E_PROGRESSIVO);
                            $retVal['creati'][] = ['id' => $idRichiesta, 'messaggi' => $result->E_MESSAGES];
                        } elseif ($result->E_RC === 10) {
                            // Iban già presente
                            $datoBancario->setFlagIbanSap(1);
                            $retVal['gia_presenti'][] = ['id' => $idRichiesta, 'messaggi' => $result->E_MESSAGES];
                        } else {
                            // Tutti gli altri errori
                            $retVal['altri'][] = ['id' => $idRichiesta, 'messaggi' => $result->E_MESSAGES];
                        }
                        $em->persist($datoBancario);
                        $em->flush();
                    }
                }
            }

            return $retVal;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param array $arrayIdRichieste
     * @param string $ambiente
     * @return string[]
     * @throws Exception
     */
    public function generaPartiteBatch(array $arrayIdRichieste, string $ambiente): array
    {
        try {
            set_time_limit(self::MAX_TIME_LIMIT_ELAB);
            $retVal = ['create' => [], 'errori' => []];

            if (!empty($arrayIdRichieste)) {
                $em = $this->container->get("doctrine")->getManager();
                foreach ($arrayIdRichieste as $idRichiesta) {
                    /** @var Richiesta $richiesta */
                    $richiesta = $this->container->get("doctrine")
                        ->getRepository("RichiesteBundle:Richiesta")->find($idRichiesta);

                    /** @var Procedura $procedura */
                    $procedura = $richiesta->getProcedura();

                    /** @var IstruttoriaRichiesta $istruttoria */
                    $istruttoria = $richiesta->getIstruttoria();

                    if ($istruttoria) {
                        if (!empty($istruttoria->getNumeroImpegno())
                            && !empty($istruttoria->getAttoConcessioneAtc()->getNumero())
                            && !empty($istruttoria->getAttoConcessioneAtc()->getDataPubblicazione())) {

                            $lifnrSap = $this->getLifnrRichiesta($richiesta);

                            $data['budat'] = date("Y-m-d"); // Data di registrazione nel documento
                            $data['bldat'] = $istruttoria->getAttoConcessioneAtc()->getDataPubblicazione()->format('Y-m-d'); // Data atto di concessione
                            //$data['zlsch'] = 4; // Fisso viene già passato dalla funzione creaPartita
                            $data['xblnr'] = $istruttoria->getAttoConcessioneAtc()->getNumero(); // Numero di adozione dell’atto di concessione
                            $data['zz_num_loc'] = '000001'; // Si era deciso per tutti 000001 Regione Emilia-Romagna
                            $data['lifnr'] = $lifnrSap;
                            $data['kblnr'] = $istruttoria->getNumeroImpegno(); // Numero impegno
                            $data['kblpos'] = $istruttoria->getPosizioneImpegno(); // Posizione impegno
                            $data['wrbtr'] = $istruttoria->getContributoAmmesso(); // Importo lordo
                            $data['kostl'] = $procedura->getCentroDiCosto(); // Centro di costo

                            // Crea quietanza tramite WS
                            $result = $this->container->get('app.sap_service')->creaPartita($data, $ambiente);
                            if ($result->E_RC === 0) {
                                $partita = new Partita();
                                $partita->setAttuazioneControlloRichiesta($richiesta->getAttuazioneControllo());
                                $partita->setNumeroPartita($result->E_LOTKZ);

                                try {
                                    $em->persist($partita);
                                    $em->flush();
                                } catch (Exception $e) {
                                    $retVal['errori'][] = ['richiesta' => $richiesta, 'messaggi' => $result->E_MESSAGES];
                                }

                                $retVal['create'][] = ['richiesta' => $richiesta, 'numero_partita' => $result->E_LOTKZ, 'messaggi' => $result->E_MESSAGES];
                            } else {
                                $retVal['errori'][] = ['richiesta' => $richiesta, 'messaggi' => $result->E_MESSAGES];
                            }
                        }
                    }
                }
            }

            return $retVal;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param array $datiPartite
     * @param string $ambiente
     * @return string[]
     * @throws Exception
     */
    public function generaPartiteStabilimentiBalneariBatch(array $datiPartite, string $ambiente): array
    {
        try {
            set_time_limit(self::MAX_TIME_LIMIT_ELAB);
            $retVal = ['create' => [], 'errori' => []];

            if (!empty($datiPartite)) {
                $em = $this->container->get("doctrine")->getManager();

                foreach ($datiPartite as $datiPartita) {
                    // Crea quietanza tramite WS
                    $result = $this->container->get('app.sap_service')->creaPartita($datiPartita, $ambiente);

                    /** @var Richiesta $richiesta */
                    $richiesta = $this->container->get("doctrine")->getRepository("RichiesteBundle:Richiesta")
                        ->find($datiPartita['richiesta_id']);
                    if ($result->E_RC === 0) {
                        $partita = new Partita();
                        $partita->setAttuazioneControlloRichiesta($richiesta->getAttuazioneControllo());
                        $partita->setNumeroPartita($result->E_LOTKZ);
                        $partita->setImportoPartita($datiPartita['wrbtr']);

                        try {
                            $em->persist($partita);
                            $em->flush();
                        } catch (Exception $e) {
                            $retVal['errori'][] = ['richiesta' => $richiesta, 'messaggi' => $result->E_MESSAGES];
                        }

                        $retVal['create'][] = [
                            'richiesta' => $richiesta,
                            'numero_partita' => $result->E_LOTKZ,
                            'messaggi' => $result->E_MESSAGES
                        ];
                    } else {
                        $retVal['errori'][] = ['richiesta' => $richiesta, 'messaggi' => $result->E_MESSAGES];
                    }
                }
            }

            return $retVal;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param Richiesta $richiesta
     * @return mixed|string|null
     */
    public function getLifnrRichiesta(Richiesta $richiesta)
    {
        $em = $this->container->get("doctrine")->getManager();
        $proceduraId = $richiesta->getProcedura()->getId();
        $bandiIbridi = [150, 151, 164,];

        if ($proceduraId == 139) {
            /** @var Persona $legaleRappresentante */
            $legaleRappresentante = $em->getRepository("AnagraficheBundle:Persona")
                ->getPersonaByUsername($richiesta->getUtenteInvio());
            $lifnr = $legaleRappresentante[0]->getLifnrSap();
        } elseif (in_array($proceduraId, $bandiIbridi)) {
            $cliRepo = $em->getRepository("IstruttorieBundle:ChecklistIstruttoria");
            $codice_check_list = 'checklist_formale_' . $proceduraId;
            $checklist_formale = $cliRepo->findOneBy(['codice' => $codice_check_list]);

            if ($proceduraId == 150) {
                $elemento_checklist = "TIPOLOGIA_SOGGETTO_GIURIDICO";
            } elseif ($proceduraId == 151) {
                $elemento_checklist = "TIPOLOGIA_PROPONENTE_151";
            } elseif ($proceduraId == 164) {
                $elemento_checklist = "SOGGETTO_PROPONENTE_CHECKLIST";
            }

            $tipologia_proponente = '';
            if ($checklist_formale) {
                $elementi_checklist_formale = $em->getRepository("IstruttorieBundle:IstruttoriaRichiesta")
                    ->getElementiCheckList($richiesta->getIstruttoria()->getId(), $checklist_formale->getId());

                foreach ($elementi_checklist_formale as $elemento) {
                    if ($elemento['codice'] == $elemento_checklist) {
                        $tipologia_proponente = $elemento['valore'];
                    }
                }
            }

            if (!empty($tipologia_proponente)) {
                if ($tipologia_proponente == 'Persona fisica') {
                    /** @var Persona $persona */
                    $persona = $em->getRepository("AnagraficheBundle:Persona")
                        ->findOneBy(['codice_fiscale' => $richiesta->getMandatario()->getSoggetto()->getCodiceFiscale()]);
                    $lifnr = $persona->getLifnrSap();
                } else {
                    $lifnr = $richiesta->getMandatario()->getSoggetto()->getLifnrSap();
                }
            } else {
                $lifnr = '';
            }
        } else {
            $lifnr = $richiesta->getMandatario()->getSoggetto()->getLifnrSap();
        }

        return $lifnr;
    }
}
