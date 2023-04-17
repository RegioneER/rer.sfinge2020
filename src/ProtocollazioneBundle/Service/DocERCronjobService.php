<?php

namespace ProtocollazioneBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use ProtocollazioneBundle\Service\DocERLogService;
use ProtocollazioneBundle\Service\IntegrazioneDocERService;
use ProtocollazioneBundle\Entity\IstanzaProcesso;
use ProtocollazioneBundle\Entity\FascicoloBandoAzienda;
use SoggettoBundle\Entity\ComuneUnione;
use ProtocollazioneBundle\Exception\ProtocollazioneException;

/**
 * Description of DocERCronjobService
 *
 * @author dcannistraro, gaetanoborgosano - refactoring dcannistraro
 * 
 */
class DocERCronjobService extends DocERBaseService {

    const IN_PREPARAZIONE = "IN_PREPARAZIONE";
    const PRONTO_PER_PROTOCOLLAZIONE = "PRONTO_PER_PROTOCOLLAZIONE";
    const IN_LAVORAZIONE = "IN_LAVORAZIONE";
    const PROTOCOLLATA = "PROTOCOLLATA";
    const POST_PROTOCOLLAZIONE = "POST_PROTOCOLLAZIONE";
    const DEFAULT_MAX_NUM_DOCUMENTI = 999;
    const CODICE_NUM_MAX_DOC = 'MAX_DOC_FASE_2';

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var DocERLogService
     */
    protected $logger;

    /**
     * @var ServiceContainer
     */
    protected $serviceContainer;

    /**
     * @var IntegrazioneDocERService
     */
    protected $integrazione;
    protected $codice_processo;
    protected $processo;
    protected $istanza;
    protected $richiestaProtDoc;
    protected $richiestaProt;
    protected $procedura;
    protected $richiesta;
    protected $richiesta_integrazione;
    protected $risposta_integrazione;
    protected $ricProt;

    /**
     * @var integer
     */
    protected $numeroMassimoDocumentiDaCaricare;

    public function __construct($codice_processo, $doctrine, DocERLogService $logger, ContainerInterface $serviceContainer, IntegrazioneDocERService $integrazione, RegistrazioneDocERService $registrazione) {
        parent::__construct($serviceContainer);
        $this->codice_processo = $codice_processo;
        $this->em = $doctrine->getManager();
        $this->logger = $logger;
        $this->serviceContainer = $serviceContainer;
        $this->integrazione = $integrazione;
        $this->registrazione = $registrazione;
        try {
            /** @var \SfingeBundle\Entity\ParametroSistema $parametroSistema */
            $parametroSistema = $this->em->getRepository('SfingeBundle:ParametroSistema')
                ->findOneByCodice(self::CODICE_NUM_MAX_DOC);
            if (\is_null($parametroSistema)) {
                throw new \Exception('Nessun parametro trovato');
            }
            $valoreParametro = $parametroSistema->getValore();
            $this->numeroMassimoDocumentiDaCaricare = (int) $valoreParametro;
        } catch (\Exception $e) {
            $this->numeroMassimoDocumentiDaCaricare = self::DEFAULT_MAX_NUM_DOCUMENTI;
        }
    }

    public function __destruct() {
        if ($this->istanza && \is_null($this->istanza->getDataFine())) {
            $this->setFineIstanza();
        }
        if (!\is_null($this->procedura)) {
            $this->getServiceDaProcedura($this->procedura)->logoutDocER();
        }
    }

    /**
     * Crea una istanza di processo di tipo $this->processo
     * @throws \Exception
     * questa funzione è BLOCCANTE IN CASO DI ERRORI 
     */
    protected function createIstanza() {
        $dataAvvio = new \DateTime('NOW');

        $istanza_processo = new IstanzaProcesso();
        $istanza_processo->setProcesso($this->processo);
        $istanza_processo->setDataAvvio($dataAvvio);

        $this->em->persist($istanza_processo); //comunico a Doctrine che l’oggetto appena creato necessita di essere gestito  
        $this->em->flush();  //inserisco effettivamente l’oggetto nel database
        $this->istanza = $istanza_processo;
    }

    /**
     * Inserisce la fase iniziale (fase = 1) nella tabella richieste_protocollo 
     * @throws \Exception
     * questa funzione è BLOCCANTE IN CASO DI ERRORI 
     */
    protected function setFaseIniziale() {
        $this->setApp_function("setFaseIniziale");

        //Leggo le richieste di protocollazione che hanno come processo $this->processo
        //e come stato "PRONTO_PER_PROTOCOLLAZIONE"
        $richieste = $this->em->getRepository('ProtocollazioneBundle:RichiestaProtocollo')
            ->findBy(array('processo' => $this->processo->getId(),
            'stato' => self::PRONTO_PER_PROTOCOLLAZIONE));
        if (count($richieste) > 0) {
            foreach ($richieste as $obj) {
                $obj->setFase(1);
            }

            $this->em->flush();
        } else {
            //Per il processo $this->processo leggo le richieste di protocollazione sospese 
            //che hanno come stato "IN_LAVORAZIONE"
            $richieste_in_lav = $this->em->getRepository('ProtocollazioneBundle:RichiestaProtocollo')
                ->findBy(array('processo' => $this->processo->getId(),
                'stato' => self::IN_LAVORAZIONE));
            if (count($richieste_in_lav) == 0) {
                $code = 4000;
                throw new \Exception("<strong>Avviso: </strong>non vi sono richieste da protocollare per il processo [" . $this->codice_processo . "]", $code);
            }
        }
    }

    /**
     * Funzione che aggiorna la tabella 'richieste_protocollo'
     * inserendo lo stato in cui si trova il processo d'integrazione e
     * l'istanza del processo.
     * @param varchar(255) $stato 
     * @throws \Exception
     * questa funzione è BLOCCANTE IN CASO DI ERRORI
     */
    protected function setStatoProcesso($stato) {
        $this->setApp_function("setStatoProcesso");

        $this->richiestaProt->setStato($stato);
        $this->richiestaProt->setIstanzaProcesso($this->istanza);

        $this->em->flush();
    }

    /**
     * Funzione che aggiorna la tabella 'richieste_protocollo'
     * inserendo la fase in cui si trova il processo d'integrazione e
     * l'esito della stessa; 
     * aggiorna inoltre la tabella 'richieste_protocollo_documenti'
     * inserendo l'idDocument del documento principale o degli allegati
     * e l'esito relativo
     * @param tinyint(1) $fase
     * @param bool $esito [0,1] Se null non aggiorno il campo
     * @param varchar(255) $idDoc Se null non aggiorno il campo
     * @throws \Exception
     * questa funzione è BLOCCANTE IN CASO DI ERRORI
     */
    protected function setFaseProcesso($fase, $esito = null, $idDoc = null) {
        $this->setApp_function("setFaseProcesso");

        //Tabella richieste_protocollo e richieste_protocollo_documenti
        $this->richiestaProt->setFase($fase);
        if (!is_null($esito)) {
            $this->richiestaProt->setEsitoFase($esito);
            $this->richiestaProtDoc->setEsito($esito);
        }

        //Tabella richieste_protocollo_documenti
        if (!is_null($idDoc)) {
            $this->richiestaProtDoc->setIdentificativoDocEr($idDoc);
        }

        $this->em->flush();
    }

    /**
     * Funzione che aggiorna la tabella 'richieste_protocollo_documenti'
     * inserendo l'esito relativo al caricamento su DocER dell'allegato
     * e l'idDocument dello stesso
     * @param object $obj - istanza di RichiestaProtocolloDocumento     
     * @param bool $esito [0,1]
     * @param varchar(255) $idDoc
     * @throws \Exception
     * questa funzione è BLOCCANTE IN CASO DI ERRORI
     */
    protected function setEsitoCaricaAllegato($obj, $esito, $idDoc) {
        $this->setApp_function("setEsitoCaricaAllegato");

        //Tabella richieste_protocollo_documenti
        $obj->setEsito($esito);
        $obj->setIdentificativoDocEr($idDoc);

        $this->em->flush();
    }

    /**
     * Registra la chiusura di una istanza di processo
     * @throws \Exception
     * questa funzione è BLOCCANTE IN CASO DI ERRORI 	
     */
    protected function setFineIstanza() {
        $this->setApp_function("setFineIstanza");

        $dataFine = new \DateTime();
        $this->istanza->setDataFine($dataFine);

        $this->em->flush();
    }

    /**
     * Setta il fascicolo nella tabella richieste_protocollo 
     * con id uguale a $this->richiestaProt
     * @param string $fascicolo
     * @throws \Exception
     * questa funzione è BLOCCANTE IN CASO DI ERRORI
     */
    protected function setFascicolo($fascicolo) {
        $this->setApp_function("setFascicolo");

        //Verifico che il fascicolo sia nullo
        $retFasc = $this->richiestaProt->getFascicolo();
        if (\is_null($retFasc)) {
            $this->richiestaProt->setFascicolo($fascicolo);
            $this->em->flush();
        }
    }

    /**
     * Funzione che crea un nuovo record nella tabella 'fascicoli_bandi_aziende'
     * e aggiorna la tabella 'richieste_protocollo' inserendo il fascicolo
     * nella forma <fascicolo>/<sottofascicolo>,
     * @param string $fascicolo
     * @param int $bando
     * @param string $codice
     * @throws \Exception
     * questa funzione è BLOCCANTE IN CASO DI ERRORI 
     */
    protected function creaFascicoloBandoAzienda($fascicolo, $bando, $codice, $tipo_protocollazione) {
        $this->setApp_function("creaFascicoloBandoAzienda");

        //Aggiorno la tabella 'richieste_protocollo'
        $this->setFascicolo($fascicolo);
        //Crea un nuovo record nella tabella 'fascicoli_bandi_aziende'
        $fascicolo_bando_azienda = new FascicoloBandoAzienda();
        $fascicolo_bando_azienda->setCodice($codice);
        $fascicolo_bando_azienda->setBando($bando);
        $fascicolo_bando_azienda->setFascicolo($fascicolo);
        $fascicolo_bando_azienda->setTipo($tipo_protocollazione);

        $this->em->persist($fascicolo_bando_azienda);   //comunico a Doctrine che l’oggetto appena creato necessita di essere gestito  
        $this->em->flush();  //inserisco effettivamente l’oggetto nel database
    }

    /**
     * Funzione che verifica i dati di fascicolazione e li inizializza
     * @param string $classifica
     * @param string $anno_protocollazione
     * @param string $unita_organizzativa
     * @param string $des_fascicolo
     * @param string $fascicolo_principale
     * @throws \Exception
     * questa funzione è BLOCCANTE IN CASO DI ERRORI 
     */
    protected function checkInitDatiFascicolazione($classifica, $anno_protocollazione, $unita_organizzativa, $des_fascicolo, $fascicolo_principale, $ricProt) {
        $this->setApp_function("checkInitDatiFascicolazione");

        if (!\is_null($classifica) && !\is_null($anno_protocollazione) && !\is_null($unita_organizzativa) &&
            !\is_null($des_fascicolo) && !\is_null($fascicolo_principale)) {

            $this->getServiceDaProcedura($this->procedura)->initDatiFascicolazione($classifica, $anno_protocollazione, $unita_organizzativa, $des_fascicolo, $fascicolo_principale);
        } else {
            throw new \Exception("impossibile trovare i dati di fascicolazione associati alla richiesta $ricProt");
        }
    }

    /**
     * Funzione che aggiorna la tabella 'richieste_protocollo'
     * inserendo lo stato in cui si trova la protocollazione, 
     * l'esito, l'anno protocollo, la data di protocollazione,
     * il numero di protocollo e il registro protocollo
     * @throws \Exception
     * questa funzione è BLOCCANTE IN CASO DI ERRORI
     */
    protected function setDatiProtocollazione($stato, $datiProt = null) {
        $this->setApp_function("setDatiProtocollazione");

        $codiceStato = "";
        $classe = $this->richiesta->getNomeClasse();

        try {
            switch ($this->richiesta->getNomeClasse()) {
                case 'Richiesta':
                    $statoRichiesta = $this->richiesta->getStato();
                    $codiceStato = 'PRE_INVIATA_PA';
                    break;

                case 'VariazioneRichiesta':
                    $statoRichiesta = $this->richiesta->getStato();
                    $codiceStato = 'VAR_INVIATA_PA';
                    break;

                case 'Pagamento':
                    $statoRichiesta = $this->richiesta->getStato();
                    $codiceStato = 'PAG_INVIATO_PA';
                    break;

                case 'Proroga':
                    $statoRichiesta = $this->richiesta->getStato();
                    $codiceStato = 'PROROGA_INVIATA_PA';
                    break;

                case 'IntegrazioneIstruttoria':
                case 'RispostaIntegrazioneIstruttoria':
                case 'IntegrazionePagamento':
                case 'RispostaIntegrazionePagamento':
                    $statoRichiesta = $this->richiesta->getStato();
                    $codiceStato = 'INT_INVIATA_PA';
                    break;

                case 'RichiestaChiarimento':
                case 'RispostaRichiestaChiarimenti':
                    $statoRichiesta = $this->richiesta->getStato();
                    $codiceStato = 'RICH_CHIAR_INVIATA_PA';
                    break;

                case 'ComunicazioneEsitoIstruttoria':
                case 'RispostaComunicazioneEsitoIstruttoria':
                    $statoRichiesta = $this->richiesta->getStato();
                    $codiceStato = 'ESI_INVIATA_PA';
                    break;

                case 'EsitoIstruttoriaPagamento':
                    $statoRichiesta = $this->richiesta->getStato();
                    $codiceStato = 'ESITO_IP_INVIATA_PA';
                    break;

                case 'ComunicazioneProgetto':
                case 'RispostaComunicazioneProgetto':
                case 'ComunicazioneAttuazione':
                case 'RispostaComunicazioneAttuazione':
                case 'ComunicazionePagamento':
                case 'RispostaComunicazionePagamento':
                    $statoRichiesta = $this->richiesta->getStato();
                    $codiceStato = 'COM_INVIATA_PA';
                    break;
            }

            if (!\is_null($datiProt)) {
                if ($codiceStato == "") {
                    throw new \Exception("Impossibile verificare il tipo richiesta da protocollare");
                }

                if ($this->isRegistrazione($this->procedura) == true) {
                    $registro_pg = $datiProt['IDRegistro'];
                    $data_pg = new \DateTime($datiProt['DataRegistrazione']);
                    $anno_pg = $data_pg->format('Y');
                    $data = $datiProt['DataRegistrazione'];
                    $num_pg = $datiProt['NumeroRegistrazione'];
                    $oggetto_pg = $datiProt['OggettoRegistrazione'];
                } else {
                    $registro_pg = $datiProt['REGISTRO_PG'];
                    $anno_pg = $datiProt['ANNO_PG'];
                    $data = $datiProt['DATA_PG'];
                    $data_pg = new \DateTime($data);
                    $num_pg = $datiProt['NUM_PG'];
                    $oggetto_pg = null;
                }
                $this->richiestaProt->setRegistro_pg($registro_pg);
                $this->richiestaProt->setAnno_pg($anno_pg);
                $this->richiestaProt->setData_pg($data_pg);
                $this->richiestaProt->setNum_pg($num_pg);
                $this->richiestaProt->setOggetto_pg($oggetto_pg);
                $this->richiestaProt->setEsitoFase(1);

                if ($statoRichiesta->getCodice() != $codiceStato) {
                    $this->setMsg_array(array('messaggio' => "<strong>La richiesta <span style='color:blue'>" . $this->richiesta->getId() . "</span> "
                        . "reinserita manualmente è stata protocollata</strong>"));
                } else {
                    $this->avanzaStatoRichiesta($this->richiesta, $statoRichiesta);
                }
            }

            if ($this->richiestaProt->getFase() == 6 && $statoRichiesta->getCodice() == $codiceStato) {
                $this->avanzaStatoRichiesta($this->richiesta, $statoRichiesta);
            }

            $this->richiestaProt->setStato($stato);
            $this->em->flush();
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    private function avanzaStatoRichiesta($richiesta, $statoRichiesta) {
        $this->setApp_function("avanzaStatoRichiesta");

        try {
            $logStato = new \BaseBundle\Entity\StatoLog();

            //trovo l'utente connesso
            $utente = $this->serviceContainer->get('security.token_storage')->getToken()->getUser();
            $logStato->setUsername($utente->getUsername());

            //setto l'oggetto da loggare

            $logStato->setIdOggetto($richiesta->getId());
            $logStato->setStatoPrecedente($statoRichiesta);

            $classe = $richiesta->getNomeClasse();

            switch ($richiesta->getNomeClasse()) {
                case 'Richiesta':
                    $codiceStatoFinale = 'PRE_PROTOCOLLATA';
                    $logStato->setOggetto("RichiesteBundle\Entity\Richiesta");
                    break;

                case 'VariazioneRichiesta':
                    $codiceStatoFinale = 'VAR_PROTOCOLLATA';
                    $logStato->setOggetto("AttuazioneControlloBundle\Entity\VariazioneRichiesta");
                    break;

                case 'Pagamento':
                    $codiceStatoFinale = 'PAG_PROTOCOLLATO';
                    $logStato->setOggetto("AttuazioneControlloBundle\Entity\Pagamento");
                    break;

                case 'Proroga':
                    $codiceStatoFinale = 'PROROGA_PROTOCOLLATA';
                    $logStato->setOggetto("AttuazioneControlloBundle\Entity\Proroga");
                    break;

                case 'IntegrazioneIstruttoria':
                    $codiceStatoFinale = 'INT_PROTOCOLLATA';
                    $logStato->setOggetto("IstruttorieBundle\Entity\IntegrazioneIstruttoria");
                    break;

                case 'RispostaIntegrazioneIstruttoria':
                    $codiceStatoFinale = 'INT_PROTOCOLLATA';
                    $logStato->setOggetto("IstruttorieBundle\Entity\RispostaIntegrazioneIstruttoria");
                    break;

                case 'IntegrazionePagamento':
                    $codiceStatoFinale = 'INT_PROTOCOLLATA';
                    $logStato->setOggetto("AttuazioneControlloBundle\Entity\IntegrazionePagamento");
                    break;

                case 'RispostaIntegrazionePagamento':
                    $codiceStatoFinale = 'INT_PROTOCOLLATA';
                    $logStato->setOggetto("AttuazioneControlloBundle\Entity\RispostaIntegrazionePagamento");
                    break;

                case 'RichiestaChiarimento':
                    $codiceStatoFinale = 'RICH_CHIAR_PROTOCOLLATA';
                    $logStato->setOggetto("AttuazioneControlloBundle\Entity\RichiestaChiarimento");
                    break;

                case 'RispostaRichiestaChiarimenti':
                    $codiceStatoFinale = 'RICH_CHIAR_PROTOCOLLATA';
                    $logStato->setOggetto("AttuazioneControlloBundle\Entity\RispostaRichiestaChiarimenti");
                    break;

                case 'ComunicazioneEsitoIstruttoria':
                    $codiceStatoFinale = 'ESI_PROTOCOLLATA';
                    $logStato->setOggetto("IstruttorieBundle\Entity\ComunicazioneEsitoIstruttoria");
                    break;

                case 'RispostaComunicazioneEsitoIstruttoria':
                    $codiceStatoFinale = 'ESI_PROTOCOLLATA';
                    $logStato->setOggetto("IstruttorieBundle\Entity\RispostaComunicazioneEsitoIstruttoria");
                    break;

                case 'EsitoIstruttoriaPagamento':
                    $codiceStatoFinale = 'ESITO_IP_PROTOCOLLATA';
                    $logStato->setOggetto("AttuazioneControlloBundle\Entity\Istruttoria\EsitoIstruttoriaPagamento");
                    break;

                case 'ComunicazioneProgetto':
                    $codiceStatoFinale = 'COM_PROTOCOLLATA';
                    $logStato->setOggetto("AttuazioneControlloBundle\Entity\Istruttoria\ComunicazioneProgetto");
                    break;

                case 'RispostaComunicazioneProgetto':
                    $codiceStatoFinale = 'COM_PROTOCOLLATA';
                    $logStato->setOggetto("AttuazioneControlloBundle\Entity\Istruttoria\RispostaComunicazioneProgetto");
                    break;

                case 'ComunicazioneAttuazione':
                    $codiceStatoFinale = 'COM_PROTOCOLLATA';
                    $logStato->setOggetto("AttuazioneControlloBundle\Entity\ComunicazioneAttuazione");
                    break;

                case 'RispostaComunicazioneAttuazione':
                    $codiceStatoFinale = 'COM_PROTOCOLLATA';
                    $logStato->setOggetto("AttuazioneControlloBundle\Entity\RispostaComunicazioneAttuazione");
                    break;

                case 'ComunicazionePagamento':
                    $codiceStatoFinale = 'COM_PROTOCOLLATA';
                    $logStato->setOggetto("AttuazioneControlloBundle\Entity\Istruttoria\ComunicazionePagamento");
                    break;

                case 'RispostaComunicazionePagamento':
                    $codiceStatoFinale = 'COM_PROTOCOLLATA';
                    $logStato->setOggetto("AttuazioneControlloBundle\Entity\Istruttoria\RispostaComunicazionePagamento");
                    break;
            }

            if ($codiceStatoFinale == "") {
                throw new \Exception("Impossibile verificare il tipo richiesta da protocollare");
            }

            $logStato->setStatoDestinazione($codiceStatoFinale);

            $stato = $this->em->getRepository('BaseBundle:Stato')
                ->findOneByCodice($codiceStatoFinale);

            $richiesta->setStato($stato);

            $this->em->persist($logStato);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Metodo principale
     * 
     */
    public function elabora() {
        $this->setApp_function("elabora");
        try {
            //Verifico che il processo identificato dal codice ($this->codice_processo) sia presente in tabella,
            //sia 'attivo' e in caso affermativo inizializzo la proprietà processo con il relativo id
            $processo = $this->em->getRepository('ProtocollazioneBundle:Processo')
                ->findOneBy(array('codice' => $this->codice_processo,
                'attivo' => 1));
            if (\is_null($processo)) {
                throw new \Exception("<strong>Errore: </strong>il processo [" . $this->codice_processo . "] non è attivo o è impossibile determinarne l'identificativo");
            }
            $this->processo = $processo;

            //Verifico se esistono istanze di processo di tipo $this->processo in esecuzione
            $istanze = $this->em->getRepository('ProtocollazioneBundle:IstanzaProcesso')
                ->cercaIstanzeByProcessoId($this->processo->getId());
            if (count($istanze) > 0) {
                $code = 4000;
                throw new \Exception("<strong>Avviso: </strong>sono presenti istanze di processo in esecuzione.", $code);
            }

            //Creo un'istanza di processo di tipo $this->processo
            $this->createIstanza();
            $this->setSottotitolo('istanza processo: [ ' . $this->istanza->getId() . ' ]');

            //Imposto la fase al valore iniziale '1' nella tabella richieste_protocolli
            $this->setFaseIniziale();

            //Leggo le richieste di protocollazione pendenti (fase <> 0) per il
            //processo $this->processo e i dati relativi al documento principale
            $righeDB = $this->em->getRepository('ProtocollazioneBundle:RichiestaProtocolloDocumento')
                ->cercaFaseByProcessoId($this->processo->getId());

            if (count($righeDB) > 0) {
                //Ottengo solo la prima richiesta di protocollazione
                //PROCEDIMENTO MANUALE - una protocollazione per volta
                //CRONJOB - tutte le richieste (eliminare riga di codice successiva)
                $righeDB = array($righeDB[0]);

                $this->setMsg_array(array('msg_titolo_head' => 'Avvio procedimento...'));

                //All'interno del ciclo verra' elaborato un documento principale alla volta
                foreach ($righeDB as $richiestaProtDoc) {

                    $esci = false;
                    $param = array();
                    $this->richiestaProtDoc = $richiestaProtDoc;
                    $kpDoc = $this->richiestaProtDoc->getId(); //Chiave documento principale
                    $filePathNameDocPrincipale = $this->richiestaProtDoc->getPath();
                    $idDocP = $this->richiestaProtDoc->getIdentificativoDocEr();
                    $this->richiestaProt = $this->richiestaProtDoc->getRichiestaProtocollo();
                    $this->ricProt = $this->richiestaProt->getId();
                    $ricProt = $this->ricProt;
                    $fase = $this->richiestaProt->getFase();

                    $this->procedura = $this->richiestaProt->getProcedura();
                    $bando = $this->procedura->getId();
                    //perora così, poi lo prendo dalla procedura
                    $registro_id = $this->procedura->getRegistroProtocollazione();

                    /*
                      //aggiunta cinquina controllo in loco quando ci sarà
                      $classifica = $this->procedura->getClassificaCtrl();
                      $fascicolo_principale = $this->procedura->getFascicoloPrincipaleCtrl();
                      $anno_protocollazione = $this->procedura->getAnnoProtocollazioneCtrl();
                      $unita_organizzativa = $this->procedura->getUnitaOrganizzativaCtrl();
                     */
                    if ($this->richiestaProt->getNomeClasse() == 'ProtocolloFinanziamento') {
                        $this->richiesta = $this->richiestaProt->getRichiesta();
                        $proponente = $this->em->getRepository('RichiesteBundle:Proponente')
                            ->findOneBy(array('richiesta' => $this->richiestaProt->getRichiesta()->getId(),
                            'mandatario' => 1));
                        $tipo_protocollazione = 'PRES';
                        //cinquina presentazione e gestione
                        $classifica = $this->procedura->getClassifica();
                        $fascicolo_principale = $this->procedura->getFascicolo_principale();
                        $anno_protocollazione = $this->procedura->getAnnoProtocollazione();
                        $unita_organizzativa = $this->procedura->getUnita_organizzativa();
                        $this->getServiceDaProcedura($this->procedura)->loginDocERAutenticazione($tipo_protocollazione, $this->richiestaProt->getProcedura());
                    }

                    if ($this->richiestaProt->getNomeClasse() == 'ProtocolloVariazione') {
                        $this->richiesta = $this->richiestaProt->getVariazione();
                        $proponente = $this->em->getRepository('RichiesteBundle:Proponente')
                            ->findOneBy(array('richiesta' => $this->richiestaProt->getVariazione()->getAttuazioneControlloRichiesta()->getRichiesta()->getId(),
                            'mandatario' => 1));
                        $tipo_protocollazione = 'PRES';
                        //cinquina presentazione e gestione
                        $classifica = $this->procedura->getClassifica();
                        $fascicolo_principale = $this->procedura->getFascicolo_principale();
                        $anno_protocollazione = $this->procedura->getAnnoProtocollazione();
                        $unita_organizzativa = $this->procedura->getUnita_organizzativa();
                        $this->getServiceDaProcedura($this->procedura)->loginDocERAutenticazione($tipo_protocollazione, $this->richiestaProt->getProcedura());
                    }

                    if ($this->richiestaProt->getNomeClasse() == 'ProtocolloProroga') {
                        $this->richiesta = $this->richiestaProt->getProroga();
                        $proponente = $this->em->getRepository('RichiesteBundle:Proponente')
                            ->findOneBy(array('richiesta' => $this->richiestaProt->getProroga()->getAttuazioneControlloRichiesta()->getRichiesta()->getId(),
                            'mandatario' => 1));
                        $tipo_protocollazione = 'PRES';
                        //cinquina presentazione e gestione
                        $classifica = $this->procedura->getClassifica();
                        $fascicolo_principale = $this->procedura->getFascicolo_principale();
                        $anno_protocollazione = $this->procedura->getAnnoProtocollazione();
                        $unita_organizzativa = $this->procedura->getUnita_organizzativa();
                        $this->getServiceDaProcedura($this->procedura)->loginDocERAutenticazione($tipo_protocollazione, $this->richiestaProt->getProcedura());
                    }

                    if ($this->richiestaProt->getNomeClasse() == 'ProtocolloPagamento') {
                        $this->richiesta = $this->richiestaProt->getPagamento();
                        $proponente = $this->em->getRepository('RichiesteBundle:Proponente')
                            ->findOneBy(array('richiesta' => $this->richiestaProt->getPagamento()->getAttuazioneControlloRichiesta()->getRichiesta()->getId(),
                            'mandatario' => 1));
                        $tipo_protocollazione = 'REND';
                        //aggiunta cinquina rendicontazione
                        $classifica = $this->procedura->getClassificaRend();
                        $fascicolo_principale = $this->procedura->getFascicoloPrincipaleRend();
                        $anno_protocollazione = $this->procedura->getAnnoProtocollazioneRend();
                        $unita_organizzativa = $this->procedura->getUnitaOrganizzativaRend();
                        $this->getServiceDaProcedura($this->procedura)->loginDocERAutenticazione($tipo_protocollazione, $this->richiestaProt->getProcedura());
                    }

                    if ($this->richiestaProt->getNomeClasse() == 'RichiestaProtocolloIntegrazione') {
                        $this->richiesta = $this->richiestaProt->getIntegrazione();
                        $proponente = $this->em->getRepository('RichiesteBundle:Proponente')
                            ->findOneBy(array('richiesta' => $this->richiestaProt->getRichiesta()->getId(),
                            'mandatario' => 1));
                        $tipo_protocollazione = 'PRES';
                        //cinquina presentazione e gestione
                        $classifica = $this->procedura->getClassifica();
                        $fascicolo_principale = $this->procedura->getFascicolo_principale();
                        $anno_protocollazione = $this->procedura->getAnnoProtocollazione();
                        $unita_organizzativa = $this->procedura->getUnita_organizzativa();
                        $this->getServiceDaProcedura($this->procedura)->loginDocERAutenticazione($tipo_protocollazione, $this->richiestaProt->getProcedura());
                    }

                    if ($this->richiestaProt->getNomeClasse() == 'RichiestaProtocolloRispostaIntegrazione') {
                        $this->richiesta = $this->richiestaProt->getRispostaIntegrazione();
                        $proponente = $this->em->getRepository('RichiesteBundle:Proponente')
                            ->findOneBy(array('richiesta' => $this->richiestaProt->getRichiesta()->getId(),
                            'mandatario' => 1));
                        $tipo_protocollazione = 'PRES';
                        //cinquina presentazione e gestione
                        $classifica = $this->procedura->getClassifica();
                        $fascicolo_principale = $this->procedura->getFascicolo_principale();
                        $anno_protocollazione = $this->procedura->getAnnoProtocollazione();
                        $unita_organizzativa = $this->procedura->getUnita_organizzativa();
                        $this->getServiceDaProcedura($this->procedura)->loginDocERAutenticazione($tipo_protocollazione, $this->richiestaProt->getProcedura());
                    }

                    if ($this->richiestaProt->getNomeClasse() == 'ProtocolloIntegrazionePagamento') {
                        $this->richiesta = $this->richiestaProt->getIntegrazionePagamento();
                        $proponente = $this->em->getRepository('RichiesteBundle:Proponente')
                            ->findOneBy(array('richiesta' => $this->richiesta->getPagamento()->getRichiesta()->getId(),
                            'mandatario' => 1));
                        $tipo_protocollazione = 'REND';
                        //aggiunta cinquina rendicontazione
                        $classifica = $this->procedura->getClassificaRend();
                        $fascicolo_principale = $this->procedura->getFascicoloPrincipaleRend();
                        $anno_protocollazione = $this->procedura->getAnnoProtocollazioneRend();
                        $unita_organizzativa = $this->procedura->getUnitaOrganizzativaRend();
                        $this->getServiceDaProcedura($this->procedura)->loginDocERAutenticazione($tipo_protocollazione, $this->richiestaProt->getProcedura());
                    }

                    if ($this->richiestaProt->getNomeClasse() == 'RichiestaProtocolloRispostaIntegrazionePagamento') {
                        $this->richiesta = $this->richiestaProt->getRispostaIntegrazionePagamento();
                        $proponente = $this->em->getRepository('RichiesteBundle:Proponente')
                            ->findOneBy(array('richiesta' => $this->richiesta->getRichiesta()->getId(),
                            'mandatario' => 1));
                        $tipo_protocollazione = 'REND';
                        //aggiunta cinquina rendicontazione
                        $classifica = $this->procedura->getClassificaRend();
                        $fascicolo_principale = $this->procedura->getFascicoloPrincipaleRend();
                        $anno_protocollazione = $this->procedura->getAnnoProtocollazioneRend();
                        $unita_organizzativa = $this->procedura->getUnitaOrganizzativaRend();
                        $this->getServiceDaProcedura($this->procedura)->loginDocERAutenticazione($tipo_protocollazione, $this->richiestaProt->getProcedura());
                    }

                    if ($this->richiestaProt->getNomeClasse() == 'ProtocolloRichiestaChiarimenti') {
                        $this->richiesta = $this->richiestaProt->getRichiestaChiarimento();
                        $proponente = $this->em->getRepository('RichiesteBundle:Proponente')
                            ->findOneBy(array('richiesta' => $this->richiesta->getPagamento()->getRichiesta()->getId(),
                            'mandatario' => 1));
                        $tipo_protocollazione = 'REND';
                        //aggiunta cinquina rendicontazione
                        $classifica = $this->procedura->getClassificaRend();
                        $fascicolo_principale = $this->procedura->getFascicoloPrincipaleRend();
                        $anno_protocollazione = $this->procedura->getAnnoProtocollazioneRend();
                        $unita_organizzativa = $this->procedura->getUnitaOrganizzativaRend();
                        $this->getServiceDaProcedura($this->procedura)->loginDocERAutenticazione($tipo_protocollazione, $this->richiestaProt->getProcedura());
                    }

                    if ($this->richiestaProt->getNomeClasse() == 'RichiestaProtocolloRispostaRichiestaChiarimenti') {
                        $this->richiesta = $this->richiestaProt->getRispostaRichiestaChiarimenti();
                        $proponente = $this->em->getRepository('RichiesteBundle:Proponente')
                            ->findOneBy(array('richiesta' => $this->richiesta->getRichiesta()->getId(),
                            'mandatario' => 1));
                        $tipo_protocollazione = 'REND';
                        //aggiunta cinquina rendicontazione
                        $classifica = $this->procedura->getClassificaRend();
                        $fascicolo_principale = $this->procedura->getFascicoloPrincipaleRend();
                        $anno_protocollazione = $this->procedura->getAnnoProtocollazioneRend();
                        $unita_organizzativa = $this->procedura->getUnitaOrganizzativaRend();
                        $this->getServiceDaProcedura($this->procedura)->loginDocERAutenticazione($tipo_protocollazione, $this->richiestaProt->getProcedura());
                    }

                    if ($this->richiestaProt->getNomeClasse() == 'RichiestaProtocolloEsitoIstruttoria') {
                        $this->richiesta = $this->richiestaProt->getComunicazioneEsito();
                        $proponente = $this->em->getRepository('RichiesteBundle:Proponente')
                            ->findOneBy(array('richiesta' => $this->richiesta->getRichiesta()->getId(),
                            'mandatario' => 1));
                        $tipo_protocollazione = 'PRES';
                        //cinquina presentazione e gestione
                        $classifica = $this->procedura->getClassifica();
                        $fascicolo_principale = $this->procedura->getFascicolo_principale();
                        $anno_protocollazione = $this->procedura->getAnnoProtocollazione();
                        $unita_organizzativa = $this->procedura->getUnita_organizzativa();
                        $this->getServiceDaProcedura($this->procedura)->loginDocERAutenticazione($tipo_protocollazione, $this->richiestaProt->getProcedura());
                    }

                    if ($this->richiestaProt->getNomeClasse() == 'RichiestaProtocolloRispostaEsitoIstruttoria') {
                        $this->richiesta = $this->richiestaProt->getRispostaComunicazione();
                        $proponente = $this->em->getRepository('RichiesteBundle:Proponente')
                            ->findOneBy(array('richiesta' => $this->richiesta->getRichiesta()->getId(),
                            'mandatario' => 1));
                        $tipo_protocollazione = 'PRES';
                        //cinquina presentazione e gestione
                        $classifica = $this->procedura->getClassifica();
                        $fascicolo_principale = $this->procedura->getFascicolo_principale();
                        $anno_protocollazione = $this->procedura->getAnnoProtocollazione();
                        $unita_organizzativa = $this->procedura->getUnita_organizzativa();
                        $this->getServiceDaProcedura($this->procedura)->loginDocERAutenticazione($tipo_protocollazione, $this->richiestaProt->getProcedura());
                    }

                    if ($this->richiestaProt->getNomeClasse() == 'ProtocolloEsitoIstruttoriaPagamento') {
                        $this->richiesta = $this->richiestaProt->getEsitoIstruttoriaPagamento();
                        $proponente = $this->em->getRepository('RichiesteBundle:Proponente')
                            ->findOneBy(array('richiesta' => $this->richiesta->getRichiesta()->getId(),
                            'mandatario' => 1));
                        $tipo_protocollazione = 'REND';
                        //aggiunta cinquina rendicontazione
                        $classifica = $this->procedura->getClassificaRend();
                        $fascicolo_principale = $this->procedura->getFascicoloPrincipaleRend();
                        $anno_protocollazione = $this->procedura->getAnnoProtocollazioneRend();
                        $unita_organizzativa = $this->procedura->getUnitaOrganizzativaRend();
                        $this->getServiceDaProcedura($this->procedura)->loginDocERAutenticazione($tipo_protocollazione, $this->richiestaProt->getProcedura());
                    }

                    if ($this->richiestaProt->getNomeClasse() == 'RichiestaProtocolloComunicazioneProgetto') {
                        $this->richiesta = $this->richiestaProt->getComunicazioneProgetto();
                        $proponente = $this->em->getRepository('RichiesteBundle:Proponente')
                            ->findOneBy(array('richiesta' => $this->richiesta->getRichiesta()->getId(),
                            'mandatario' => 1));
                        $tipo_protocollazione = 'PRES';
                        //cinquina presentazione e gestione
                        $classifica = $this->procedura->getClassifica();
                        $fascicolo_principale = $this->procedura->getFascicolo_principale();
                        $anno_protocollazione = $this->procedura->getAnnoProtocollazione();
                        $unita_organizzativa = $this->procedura->getUnita_organizzativa();
                        $this->getServiceDaProcedura($this->procedura)->loginDocERAutenticazione($tipo_protocollazione, $this->richiestaProt->getProcedura());
                    }

                    if ($this->richiestaProt->getNomeClasse() == 'RichiestaProtocolloRispostaComunicazioneProgetto') {
                        $this->richiesta = $this->richiestaProt->getRispostaComunicazioneProgetto();
                        $proponente = $this->em->getRepository('RichiesteBundle:Proponente')
                            ->findOneBy(array('richiesta' => $this->richiesta->getRichiesta()->getId(),
                            'mandatario' => 1));
                        $tipo_protocollazione = 'PRES';
                        //cinquina presentazione e gestione
                        $classifica = $this->procedura->getClassifica();
                        $fascicolo_principale = $this->procedura->getFascicolo_principale();
                        $anno_protocollazione = $this->procedura->getAnnoProtocollazione();
                        $unita_organizzativa = $this->procedura->getUnita_organizzativa();
                        $this->getServiceDaProcedura($this->procedura)->loginDocERAutenticazione($tipo_protocollazione, $this->richiestaProt->getProcedura());
                    }

                    if ($this->richiestaProt->getNomeClasse() == 'RichiestaProtocolloComunicazioneAttuazione') {
                        $this->richiesta = $this->richiestaProt->getComunicazioneAttuazione();
                        $proponente = $this->em->getRepository('RichiesteBundle:Proponente')
                            ->findOneBy(array('richiesta' => $this->richiesta->getRichiesta()->getId(),
                            'mandatario' => 1));
                        $tipo_protocollazione = 'REND';
                        //aggiunta cinquina rendicontazione
                        $classifica = $this->procedura->getClassificaRend();
                        $fascicolo_principale = $this->procedura->getFascicoloPrincipaleRend();
                        $anno_protocollazione = $this->procedura->getAnnoProtocollazioneRend();
                        $unita_organizzativa = $this->procedura->getUnitaOrganizzativaRend();
                        $this->getServiceDaProcedura($this->procedura)->loginDocERAutenticazione($tipo_protocollazione, $this->richiestaProt->getProcedura());
                    }

                    if ($this->richiestaProt->getNomeClasse() == 'RichiestaProtocolloRispostaComunicazioneAttuazione') {
                        $this->richiesta = $this->richiestaProt->getRispostaComunicazioneAttuazione();
                        $proponente = $this->em->getRepository('RichiesteBundle:Proponente')
                            ->findOneBy(array('richiesta' => $this->richiesta->getRichiesta()->getId(),
                            'mandatario' => 1));
                        $tipo_protocollazione = 'REND';
                        //aggiunta cinquina rendicontazione
                        $classifica = $this->procedura->getClassificaRend();
                        $fascicolo_principale = $this->procedura->getFascicoloPrincipaleRend();
                        $anno_protocollazione = $this->procedura->getAnnoProtocollazioneRend();
                        $unita_organizzativa = $this->procedura->getUnitaOrganizzativaRend();
                        $this->getServiceDaProcedura($this->procedura)->loginDocERAutenticazione($tipo_protocollazione, $this->richiestaProt->getProcedura());
                    }

                    if ($this->richiestaProt->getNomeClasse() == 'ProtocolloComunicazionePagamento') {
                        $this->richiesta = $this->richiestaProt->getComunicazionePagamento();
                        $proponente = $this->em->getRepository('RichiesteBundle:Proponente')
                            ->findOneBy(array('richiesta' => $this->richiesta->getRichiesta()->getId(),
                            'mandatario' => 1));
                        $tipo_protocollazione = 'REND';
                        //aggiunta cinquina rendicontazione
                        $classifica = $this->procedura->getClassificaRend();
                        $fascicolo_principale = $this->procedura->getFascicoloPrincipaleRend();
                        $anno_protocollazione = $this->procedura->getAnnoProtocollazioneRend();
                        $unita_organizzativa = $this->procedura->getUnitaOrganizzativaRend();
                        $this->getServiceDaProcedura($this->procedura)->loginDocERAutenticazione($tipo_protocollazione, $this->richiestaProt->getProcedura());
                    }

                    if ($this->richiestaProt->getNomeClasse() == 'RichiestaProtocolloRispostaComunicazionePagamento') {
                        $this->richiesta = $this->richiestaProt->getRispostaComunicazionePagamento();
                        $proponente = $this->em->getRepository('RichiesteBundle:Proponente')
                            ->findOneBy(array('richiesta' => $this->richiesta->getRichiesta()->getId(),
                            'mandatario' => 1));
                        $tipo_protocollazione = 'REND';
                        //aggiunta cinquina rendicontazione
                        $classifica = $this->procedura->getClassificaRend();
                        $fascicolo_principale = $this->procedura->getFascicoloPrincipaleRend();
                        $anno_protocollazione = $this->procedura->getAnnoProtocollazioneRend();
                        $unita_organizzativa = $this->procedura->getUnitaOrganizzativaRend();
                        $this->getServiceDaProcedura($this->procedura)->loginDocERAutenticazione($tipo_protocollazione, $this->richiestaProt->getProcedura());
                    }

                    $soggetto = $proponente->getSoggetto();
//                  //Decommentare per TEST  
//                    $codice   = ($soggetto instanceof ComuneUnione) ? $soggetto->getCodiceFiscale() : $soggetto->getPartitaIva();
                    //Decommentare per PROD
                    $codice = (\is_null($soggetto->getPartitaIva())) ? $soggetto->getCodiceFiscale() : $soggetto->getPartitaIva();
                    $denominazione = $soggetto->getDenominazione();
                    $des_fascicolo = $denominazione . " " . $codice;

                    //Setto lo stato 'IN_LAVORAZIONE' e aggiorno l'istanza del processo
                    //nella tabella richieste_protocollo                                                    
                    $this->setStatoProcesso(self::IN_LAVORAZIONE);

                    //Inizializzo la proprietà 'richiestaProt' del servizio DocERLogService
                    $this->logger->setRichiestaProt($ricProt);

                    while (($fase != 0) && (!$esci)) {

                        $this->logger->setFaseRichiestaProt($fase);

//                        switch (Fasi::getFase($fase)) {
                        switch ($fase) { /*
                         *  CREA DOCUMENTO PRINCIPALE IN DOC/ER
                         *  SETTA ACL DOCUMENTO PRINCIPALE                                    
                         */
                            case 1:
                                /*
                                 * Carica documento principale
                                 *   if ok inserire fase = 2, esito_fase = 1, idDocument = id documento principale
                                 *   else non aggiorno il record
                                 */
                                try {
                                    $this->setMsg_array(array('msg_fase' => 'Fase iniziale: caricamento documento principale...'));

                                    /*                                     * ******* OK INTEGRAZIONE ******** */
                                    //Metadati presenti in DocERDocumentService->CreateDocument(...)
                                    /*                                     * ******************************** */
                                    $idDocP = $this->getServiceDaProcedura($this->procedura)->caricaDocumentoPrincipale($filePathNameDocPrincipale);
                                    //                                $idDocP = 1000;     //TEST
                                    //                                $idDocP = false;  //TEST

                                    if ($idDocP) {
                                        $this->setMsg_array(array('messaggio' => "Caricato documento principale: <span style='color:blue'>" . $idDocP . "</span>"));
                                        $fase++; //Setto fase successiva: 2 - CREA ALLEGATO e SETTA ACL 

                                        /* if ($this->processo->getId() == 3) {
                                          //INTEGRAZIONE  -> RichiestaProtocolloIntegrazione
                                          //Salto la fase 2 perché non vi saranno documenti allegati
                                          $fase++; //Setto fase successiva: 3 - CREA UNITA' DOCUMENTALE
                                          } */

                                        $this->setFaseProcesso($fase, 1, $idDocP);
                                    } else {
                                        throw new \Exception('Risposta nulla da webservice per caricamento documento principale');
                                    }

                                    break;
                                } catch (\Exception $ex) {
                                    $msg = "impossibile riversare il documento principale [$idDocP]";
                                    throw new \Exception($msg);
                                }

                            /*
                             *  CREA DOCUMENTI ALLEGATI IN DOC/ER
                             *  SETTA ACL DOCUMENTI ALLEGATI
                             */
                            case 2:
                                /*
                                 *  Carica documenti allegati
                                 *    Cicla per ogni allegato
                                 *        if ok inserire fase = 3, esito = 1 per ogni allegato, idDocument = id documento allegato
                                 *        else non aggiorno il record
                                 *    End ciclo                                                 
                                 */
                                $this->setMsg_array(array('msg_fase' => 'Fase 2: caricamento allegati...'));

                                //Leggo le informazioni dei documenti allegati alla richiesta di protocollazione 
                                //che ha richiesta_protocollo uguale a $ricProt
                                $documentiA = $this->em->getRepository('ProtocollazioneBundle:RichiestaProtocolloDocumento')
                                    ->findBy(array('richiesta_protocollo' => $ricProt,
                                    'principale' => 0,
                                    'esito' => 0));

                                //lista di documenti caricati ad un eventuale chiamata precedente 
                                $documentiB = $this->em->getRepository('ProtocollazioneBundle:RichiestaProtocolloDocumento')
                                    ->findBy(array('richiesta_protocollo' => $ricProt,
                                    'principale' => 0,
                                    'esito' => 1));

                                $totAll = count($documentiA);
                                if ($totAll == 0) {
                                    $this->setMsg_array(array('messaggio' => "Nessun allegato associato alla richiesta di protocollo [$ricProt]"));

                                    $fase++; //Setto fase successiva: 3 - CREA UNITA' DOCUMENTALE
                                    $this->setFaseProcesso($fase);
                                    break;
                                }

                                $this->getServiceDaProcedura($this->procedura)->setIdDocument_principale($idDocP);
                                $countOk = 0;   //Conta i documenti allegati con esito = 1 (Caricamento andato a buon fine)

                                try {
                                    foreach ($documentiA as $obj) {
                                        if ($countOk >= $this->numeroMassimoDocumentiDaCaricare) {
                                            throw new ProtocollazioneException('Processo interrotto per numero elevato di documenti da caricare');
                                        }
                                        $filePathNameDocAllegato = $obj->getPath();

                                        $idDocA = $this->getServiceDaProcedura($this->procedura)->caricaAllegato($filePathNameDocAllegato);

                                        if ($idDocA) {
                                            $this->setMsg_array(array('messaggio' => "Caricato documento allegato: <span style='color:blue'>" . $idDocA . "</span>"));

                                            //Inserimento esito e idDocument nella tabella 'richieste_protocollo_documenti'
                                            $this->setEsitoCaricaAllegato($obj, 1, $idDocA);
                                            $countOk++;
                                        } else {
                                            throw new \Exception("Errore da caricamento documento: $filePathNameDocAllegato");
                                        }
                                    }
                                    //se al giro precedente si è rimasti appesi in fase 2 ripopolo la lista dei doc per 
                                    //l'unita documentale co i precedentemente caricati
                                    foreach ($documentiB as $obj) {
                                        $idDocument = $obj->getIdentificativoDocEr();
                                        $this->getServiceDaProcedura($this->procedura)->addIdDocument_allegati($idDocument);
                                    }
                                    /*
                                     * Verifica esiti allegati:
                                     * Se sono tutti a 1 setto la fase successiva: 3 - CREA UNITA' DOCUMENTALE
                                     * altrimenti lascio la fase a 2 - CREA ALLEGATO e SETTA ACL per una successiva ripresa
                                     */
                                    if ($totAll == $countOk) {
                                        //Inserimento fase nel documento principale
                                        $fase++; //Setto fase successiva: 3 - CREA UNITA' DOCUMENTALE
                                        $this->setFaseProcesso($fase);
                                    } else {
                                        $esci = true;
                                    }

                                    break;
                                } catch (ProtocollazioneException $e) {
                                    throw $e;
                                } catch (\Exception $ex) {
                                    throw new \Exception("impossibile riversare il documento allegato [$idDocA].", 0, $ex);
                                }

                            /*
                             *  CREA UNITA' DOCUMENTALE IN DOC/ER
                             */
                            case 3:
                                /*
                                 * Crea unita' documentale
                                 *   if ok inserire fase = 4
                                 *   else non aggiorno il record
                                 */
                                try {
                                    //se gli allegati sono 0 o partono da 0 e non ci interessa o c'è stato un blocco in fase 3 e quindi ripopolo
                                    //purtroppo con 12000 allegati del bando sanità è l'unica
                                    $allegati = $this->getServiceDaProcedura($this->procedura)->getIdDocument_allegati();
                                    if (count($allegati) == 0) {
                                        $documentiB = $this->em->getRepository('ProtocollazioneBundle:RichiestaProtocolloDocumento')
                                            ->findBy(array('richiesta_protocollo' => $ricProt,
                                            'principale' => 0,
                                            'esito' => 1));
                                        foreach ($documentiB as $obj) {
                                            $idDocument = $obj->getIdentificativoDocEr();
                                            $this->getServiceDaProcedura($this->procedura)->addIdDocument_allegati($idDocument);
                                        }
                                    }
                                    $this->setMsg_array(array('msg_fase' => "Fase 3: creazione unita' documentale..."));

                                    $this->getServiceDaProcedura($this->procedura)->setIdDocument_principale($idDocP);

                                    $resUD = $this->getServiceDaProcedura($this->procedura)->definisciUnitaDocumentale();
//                                    $resUD = true;    //TEST
//                                    $resUD = false;  //TEST

                                    if ($resUD) {
                                        $this->setMsg_array(array('messaggio' => "Creata unita' documentale: <span style='color:blue'>" . $idDocP . "</span>"));
                                        //Inserimento fase nel documento principale
                                        $fase++; //Setto fase successiva: 4 - FASCICOLAZIONE UNITA' DOCUMENTALE
                                        $this->setFaseProcesso($fase);
                                    } else {
                                        throw new \Exception('Unità documentale non definita');
                                    }

                                    break;
                                } catch (\Exception $ex) {
                                    throw new \Exception("impossibile creare l'unita' documentale del documento principale [$idDocP]", 0, $ex);
                                }

                            /*
                             *  INIZIALIZZA DATI DI FASCICOLAZIONE
                             *  VERIFICA ESISTENZA SOTTOFASCICOLO PER AZIENDA - BANDO
                             *  CREA SOTTOFASCICOLO PER UNITA' DOCUMENTALE
                             */
                            case 4:
                                /*
                                 * Verifica l'esistenza del sottofascicolo per azienda - bando
                                 *   if exist salta alla fase 5 di protocollazione
                                 *   else procede con:
                                 *   Crea sottofascicolo
                                 *     if ok inserire fase = 5
                                 *     else non aggiorno il record
                                 * @param: true - restituisce id sottofascicolo
                                 *         false - restituisce array informazioni 
                                 */
                                $this->setMsg_array(array('msg_fase' => "Fase 4: fascicolazione..."));

                                //Verifico l'esistenza del sottofascicolo
                                //Se il bando ammette piva e cf duplicati cerco la concatenazione tra piva/cf e id del soggetto
                                //il codice modificato così doverbbe essere univoco sempre in relazione al bando e alla tipologia
                                if ($this->procedura->isAmmettePivaDuplicati() == true) {
                                    $fascicolo = $this->em->getRepository('ProtocollazioneBundle:FascicoloBandoAzienda')
                                        ->findOneBy(array('codice' => $codice . "_" . $soggetto->getId(),
                                        'bando' => $bando, 'tipo' => $tipo_protocollazione));
                                } else {
                                    $fascicolo = $this->em->getRepository('ProtocollazioneBundle:FascicoloBandoAzienda')
                                        ->findOneBy(array('codice' => $codice,
                                        'bando' => $bando, 'tipo' => $tipo_protocollazione));
                                }
                                if (!\is_null($fascicolo)) {
                                    // SOTTOFASCICOLO PER AZIENDA ESISTENTE
                                    $sottofascicolo = $fascicolo->getFascicolo();
                                    $this->setMsg_array(array('messaggio' => "Il sottofascicolo: <span style='color:blue'>" . $sottofascicolo . "</span> "
                                        . "esiste per l'azienda in esame"));

                                    //Inserisco il fascicolo nella tabella richieste_protocollo                                       
                                    $this->setFascicolo($sottofascicolo);

                                    //Inserimento fase nel documento principale
                                    $fase++; //Setto fase successiva: 5 - PROTOCOLLAZIONE UNITA' DOCUMENTALE
                                    $this->setFaseProcesso($fase);
                                } else {
                                    // CREAZIONE DEL SOTTOFASCICOLO PER AZIENDA
                                    try {
                                        $this->getServiceDaProcedura($this->procedura)->setIdDocument_principale($idDocP);

                                        //Verifico e inizializzo i dati di fascicolazione
                                        $this->checkInitDatiFascicolazione($classifica, $anno_protocollazione, $unita_organizzativa, $des_fascicolo, $fascicolo_principale, $ricProt);

                                        $sottofascicolo = $this->getServiceDaProcedura($this->procedura)->creaFascicolo(true);
//                                       $sottofascicolo = "1/4000";      //TEST *****************
//                                        $sottofascicolo = false;         //TEST *****************

                                        if ((!\is_null($sottofascicolo)) && $sottofascicolo) {
                                            $this->setMsg_array(array('messaggio' => "Creato sottofascicolo: <span style='color:blue'>" . $sottofascicolo . "</span>"));

                                            if ($this->procedura->isAmmettePivaDuplicati() == true) {
                                                $this->creaFascicoloBandoAzienda($sottofascicolo, $bando, $codice . "_" . $soggetto->getId(), $tipo_protocollazione);
                                            } else {
                                                $this->creaFascicoloBandoAzienda($sottofascicolo, $bando, $codice, $tipo_protocollazione);
                                            }

                                            //Inserimento fase nel documento principale
                                            $fase++; //Setto fase successiva: 5 - PROTOCOLLAZIONE UNITA' DOCUMENTALE
                                            $this->setFaseProcesso($fase);
                                        } else {
                                            throw new \Exception('Sottofascicolo nullo');
                                        }
                                    } catch (\Exception $ex) {
                                        //Scrivo nella tabella di log il messaggio di errore di symfony
                                        $log = $this->logger->createLog($ex->getMessage(), $this->getApp_function(), null, null);
                                        if (!$log) {
                                            $this->setMsg_array(array('errore' => '<strong>Errore: </strong>impossibile scrivere il log in tabella'));
                                        }
                                        throw new \Exception("impossibile creare il sottofascicolo.", 0, $ex);
                                    }
                                } // end if\else creazione del sottofascicolo per azienda
                                break;
                            /*
                             *  INIZIALIZZA DATI DI PROTOCOLLAZIONE
                             *  PROTOCOLLA UNITA' DOCUMENTALE
                             */
                            case 5:
                                /*
                                 * Protocolla unita' documentale
                                 *   if ok inserire fase = 0
                                 *   else non aggiorno il record
                                 */
                                try {
                                    $this->setMsg_array(array('msg_fase' => "Fase 5: protocollazione..."));

                                    $this->getServiceDaProcedura($this->procedura)->setIdDocument_principale($idDocP);

                                    //Verifico e inizializzo i dati di fascicolazione
                                    $this->checkInitDatiFascicolazione($classifica, $anno_protocollazione, $unita_organizzativa, $des_fascicolo, $fascicolo_principale, $ricProt);

                                    $Prot_Oggetto = $this->richiestaProt->getOggetto();
                                    $Prot_Fascicolo_Primario_Prog = $this->richiestaProt->getFascicolo();

                                    switch ($this->processo->getCodice()) {

                                        /*                                         * ******************************************* */
                                        /** protocollazioni in ingresso ********** */
                                        case 'protocollazione_domande_contributo':
                                        case 'protocollazione_pagamenti':
                                        case 'protocollazione_risposta_integrazione_istruttoria':
                                        case 'protocollazione_variazioni':
                                        case 'protocollazione_risposta_integrazione_pagamento':
                                        case 'protocollazione_esito_istruttoria_risposta':
                                        case 'protocollazione_risposta richiesta_chiarimenti':
                                        case 'protocollazione_proroga':
                                        case 'protocollazione_comunicazione_progetto_risposta':
                                        case 'protocollazione_comunicazione_attuazione_risposta':
                                        case 'protocollazione_risposta_comunicazione_pagamento':
                                            //tipo: FINANZIAMENTO
                                            //tipo: PAGAMENTO
                                            //tipo: RISPOSTA INTEGRAZIONE ISTRUTTORIA
                                            //tipo: VARIAZIONE
                                            //tipo: RISPOSTA INTEGRAZIONE PAGAMENTO
                                            //tipo: PROROGA PROGETTO
                                            //Preparo i dati relativi all'azienda (mittente)
                                            $Prot_Mittente_id = $codice;
                                            $Prot_Mittente_Denominazione = $denominazione;
                                            $this->getServiceDaProcedura($this->procedura)->initProtocollazione($Prot_Oggetto, $Prot_Mittente_id, $Prot_Mittente_Denominazione, $Prot_Fascicolo_Primario_Prog, $registro_id);
                                            $datiProt = $this->getServiceDaProcedura($this->procedura)->protocollaUnitaDocumentale($param);
                                            break;
                                        /**                                         * ****************************************** */
                                        /**                                         * ****************************************** */
                                        /**                                         * ****************************************** */
                                        /*                                         * *** protocollazioni in uscita..servono dei paramentri in più ********** */
                                        case 'protocollazione_integrazione_istruttoria':
                                        case 'protocollazione_integrazione_pagamento':
                                        case 'protocollazione_esito_istruttoria_richiesta':
                                        case 'protocollazione_esito_istruttoria_pagamento':
                                        case 'protocollazione_richiesta_chiarimenti':
                                        case 'protocollazione_comunicazione_progetto_pa':
                                        case 'protocollazione_comunicazione_attuazione_pa':
                                        case 'protocollazione_comunicazione_pagamento':
                                            //tipo: INTEGRAZIONE ISTRUTTORIA
                                            //tipo: INTEGRAZIONE PAGAMENTO
                                            //Preparo i dati relativi al flusso
                                            $param['Oggetto'] = $Prot_Oggetto;
                                            $param['TipoRichiesta'] = 'U';
                                            $param['Firma'] = 'FD';
                                            $param['ForzaRegistrazione'] = '1';
                                            $param['DestinatarioDenominazione'] = $denominazione;
                                            $param['CodiceFiscale'] = $soggetto->getCodiceFiscale();
                                            $param['PartitaIVA'] = $soggetto->getPartitaIva();

                                            $param['Classifica'] = $classifica;
                                            $param['Anno'] = $anno_protocollazione;
                                            $param['FascicoloPrimarioProgressivo'] = $Prot_Fascicolo_Primario_Prog;
                                            $datiProt = $this->getServiceDaProcedura($this->procedura)->protocollaUnitaDocumentaleInUscita($param, $registro_id);
                                            break;
                                        /**                                         * ****************************************** */
                                        /**                                         * ****************************************** */
                                        default:
                                            break;
                                    }

//                                    $fase = 0; //TEST
//                                    break;
//
//                                    $data = new \DateTime('2016-02-19 10:30:00');                                                       //TEST ***************  
//                                    $datiProt = array('REGISTRO_PG'=>'PG', 'ANNO_PG'=>'2016', 'NUM_PG'=>'000000', 'DATA_PG'=>$data);    //TEST ***************
//                                    $datiProt = false;                                                                                  //TEST ***************

                                    if ($datiProt) {
                                        $this->setDatiProtocollazione(self::PROTOCOLLATA, $datiProt);
                                        if ($this->isRegistrazione($this->procedura) == true) {
                                            $data_pg = new \DateTime($datiProt['DataRegistrazione']);
                                            $anno_pg = $data_pg->format('Y');
                                            $this->setMsg_array(array('messaggio' =>
                                                "Unita' documentale <span style='color:blue'>" . $idDocP . "</span> protocollata - 
                                                <span style='color:blue'>" . $datiProt['IDRegistro'] . "/" . $anno_pg . "/" . $datiProt['NumeroRegistrazione'] . "</span>"));
                                        } else {
                                            $this->setMsg_array(array('messaggio' =>
                                                "Unita' documentale <span style='color:blue'>" . $idDocP . "</span> protocollata - 
                                                <span style='color:blue'>" . $datiProt['REGISTRO_PG'] . "/" . $datiProt['ANNO_PG'] . "/" . $datiProt['NUM_PG'] . "</span>"));
                                        }
                                        //Inserimento fase nel documento principale
                                        $fase++; //Setto fase successiva: 6 - POST PROTOCOLLAZIONE
                                        $this->setFaseProcesso($fase);
                                    } else {
                                        throw new \Exception('Dati di protocollazione non validi');
                                    }

                                    break;
                                } catch (\Exception $ex) {
                                    $msg = "impossibile protocollare l'unita' documentale: <strong>" . $idDocP . "</strong></br>" . $ex->getMessage();
                                    throw new \Exception($msg, 0, $ex);
                                }

                            /*
                             *  POST PROTOCOLLAZIONE
                             *  SETTA I DATI
                             */
                            case 6:
                                $this->setMsg_array(array('msg_fase' => "Fase 6: post protocollazione..."));

                                $this->setDatiProtocollazione(self::POST_PROTOCOLLAZIONE);

                                //Inserimento fase nel documento principale
                                $fase = 0; //Setto fase finale: 0 - CONCLUSIONE PROTOCOLLAZIONE
                                $this->setFaseProcesso($fase);
                                $this->setFineIstanza();
                                $this->setMsg_array(array('messaggio' => "Dati correttamente settati per la post protocollazione"));
//                                $this->setMsg_array(array('messaggio' => "<div style='margin-top: 20px'></div>"));
                                $this->setMsg_array(array('msg_titolo_foot' => "Protocollazione conclusa con successo"));

                                break;

                            default:
                                break;
                        } // end switch
                    } // end while (($fase != 0) && (!$esci))
                } // end foreach ($righeDB as $richiestaProtDoc)
            } // end if count 
        } catch (ProtocollazioneException $e) {
            $msg = $e->getMessage();
            $this->setMsg_array(array('avviso' => $msg));
            $log = $this->logger->createLog($e->getMessage(), $this->getApp_function(), null, null);
            if (!$log) {
                $this->setMsg_array(array('errore' => '<strong>Errore: </strong>impossibile scrivere il log in tabella'));
            }
        } catch (\Exception $ex) {
            $code = $ex->getCode();
            $msg = $ex->getMessage();
            if ($code == 4000) {
                $this->setMsg_array(array('avviso' => $msg));
            } else {
                if (!empty($this->ricProt)) {
                    $msg = 'Errore interno di elaborazione su richiesta protocollo [' . $this->ricProt . '] : ' . $ex->getMessage();
                }
                $this->setMsg_array(array('errore' => $msg));
            }

            //Scrivo nella tabella di log
            $log = $this->logger->createLog($ex->getMessage(), $this->getApp_function(), null, null);
            if (!$log) {
                $this->setMsg_array(array('errore' => '<strong>Errore: </strong>impossibile scrivere il log in tabella'));
            }
        }
    }

    private function getServiceDaProcedura($procedura) {
        /*
         * URL DI COMODO PER I TEST
         */
        //https://docer-test.ente.regione.emr.it/docersystem/services/AuthenticationService?wsdl
        //https://vpnssl.regione.emilia-romagna.it/Portal/Main
        //https://test-protocollo.ente.regione.emr.it/axisSviluppo/services/WSInvioEmailProtocollo?wsdl
        switch ($procedura->getServizioProtocollazione()) {
            case 'REGISTRAZIONE':
                return $this->registrazione;
            case 'INTEGRAZIONE':
                return $this->integrazione;
            default:
                return $this->integrazione;
        }
    }

    private function isRegistrazione($procedura) {
        if ($procedura->getServizioProtocollazione() == 'REGISTRAZIONE') {
            return true;
        }
        return false;
    }

}
