<?php

namespace ProtocollazioneBundle\Service;

use AttuazioneControlloBundle\Entity\Istruttoria\ComunicazionePagamento;
use AttuazioneControlloBundle\Entity\Istruttoria\RispostaComunicazionePagamento;
use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\DependencyInjection\ContainerInterface;
use BaseBundle\Exception\SfingeException;
use ProtocollazioneBundle\Entity\RichiestaProtocolloFinanziamento;
use ProtocollazioneBundle\Entity\RichiestaProtocolloDocumento;
use ProtocollazioneBundle\Entity\RichiestaProtocolloPagamento;
use ProtocollazioneBundle\Entity\RichiestaProtocolloVariazione;
use SoggettoBundle\Entity\ComuneUnione;
use AttuazioneControlloBundle\Entity\Pagamento;
use AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimento;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Description of DocERInitProtocollazioneService
 *
 * @author Davide Cannistraro
 */
class DocERInitProtocollazioneService extends DocERBaseService {

    /**
     * @var EntityManagerInterface
     */
    protected $em;
    protected $serviceContainer;

    public function __construct($doctrine, ContainerInterface $serviceContainer) {
        parent::__construct($serviceContainer);
        $this->em = $doctrine->getManager();
        $this->serviceContainer = $serviceContainer;
    }

    public function getRichiesteInviatePA() {
        //Leggo le richieste di protocollazione inviate alla PA e non
        //presenti nella tabella richieste_protocollo (stato=PRE_INVIATA_PA) 
        try {
            $msg = "";
            $righeDB = $this->em->getRepository('RichiesteBundle:Richiesta')
                    ->getRichiesteInviatePA();

            if (count($righeDB) > 0) {
                $this->setMsg_array(array('msg_titolo_head' => 'Inizializzazione tabelle di protocollazione...'));

                foreach ($righeDB as $richiesta) {
                    $this->setTabProtocollazione($richiesta['id'], 'FINANZIAMENTO');
                    $this->setMsg_array(array(
                        'messaggio' => "<div style='font-size: 15px'>Caricata richiesta protocollazione: <span style='color:blue'>" . $richiesta['id'] . "</span></div>"));
                }

                $this->setMsg_array(array('msg_titolo_foot' => "Inizializzazione conclusa con successo"));
            } else {
                $msg = "<strong>Avviso: </strong>non vi sono richieste di protocollazione da inserire manualmente in tabella";
                $code = 4000;
                throw new \Exception($msg, $code);
            }
        } catch (\Exception $ex) {
            $code = $ex->getCode();
            if ($code == 4000) {
                $this->setMsg_array(array('avviso' => $msg));
            } else {
                $msg = '<strong>Errore: </strong>impossibile ottenere le richieste di protocollazione inviate alla PA - ' . $ex->getMessage();
                $this->setMsg_array(array('errore' => $msg));
            }
        }
    }

    public function getVariazioniInviatePA() {
        //Leggo le richieste di protocollazione inviate alla PA e non
        //presenti nella tabella richieste_protocollo (stato=PRE_INVIATA_PA) 
        try {
            $msg = "";
            $righeDB = $this->em->getRepository('AttuazioneControlloBundle:VariazioneRichiesta')->getVariazioniInviatePA();

            if (count($righeDB) > 0) {
                $this->setMsg_array(array('msg_titolo_head' => 'Inizializzazione tabelle di protocollazione...'));

                foreach ($righeDB as $variazione) {
                    $this->setTabProtocollazioneVariazione($variazione['id']);
                    $this->setMsg_array(array(
                        'messaggio' => "<div style='font-size: 15px'>Caricata richiesta protocollazione: <span style='color:blue'>" . $variazione['id'] . "</span></div>"));
                }

                $this->setMsg_array(array('msg_titolo_foot' => "Inizializzazione conclusa con successo"));
            } else {
                $msg = "<strong>Avviso: </strong>non vi sono richieste di protocollazione da inserire manualmente in tabella";
                $code = 4000;
                throw new \Exception($msg, $code);
            }
        } catch (\Exception $ex) {
            $code = $ex->getCode();
            if ($code == 4000) {
                $this->setMsg_array(array('avviso' => $msg));
            } else {
                $msg = '<strong>Errore: </strong>impossibile ottenere le richieste di protocollazione inviate alla PA';
                $this->setMsg_array(array('errore' => $msg));
            }
        }
    }

    public function setTabProtocollazione($id_oggetto_protocollazione, $tipo_oggetto_protocollazione) {
        try {
            //Avvio la transazione
            $this->em->beginTransaction();

            switch ($tipo_oggetto_protocollazione) {
                case 'FINANZIAMENTO':
                    //Leggo l'oggetto richiesta
                    /** @var Richiesta $richiesta */
                    $richiesta = $this->em->getRepository('RichiesteBundle:Richiesta')->find($id_oggetto_protocollazione);

                    /* Popolamento tabelle protocollazione
                     * - richieste_protocollo
                     * - richieste_protocollo_documenti
                     */
                    $oggetto_protocollazione = new RichiestaProtocolloFinanziamento();

                    //Leggo l'id del processo con codice 'protocollazione_domande_contributo'
                    $processo = $this->em->getRepository('ProtocollazioneBundle:Processo')
                            ->findOneBy(array('codice' => 'protocollazione_domande_contributo'));

                    $oggetto_protocollazione->setProcedura($richiesta->getProcedura());
                    $oggetto_protocollazione->setRichiesta($richiesta);

                    //Inserimento data richiesta e data invio PA
                    $data_creazione = $richiesta->getDataCreazione();
                    $data_invio = $richiesta->getDataInvio();

                    $oggetto_protocollazione->setDataInvioPA($data_invio);

                    //Creazione oggetto richiesta
                    $num_richiesta = $richiesta->getId();
                    $soggetto_mandatario = $richiesta->getSoggetto();
                    $denominazione = $soggetto_mandatario->getDenominazione();
                    $codice = ($soggetto_mandatario instanceof ComuneUnione) ? $soggetto_mandatario->getCodiceFiscale() : $soggetto_mandatario->getPartitaIva();
                    $oggetto = 'Domanda di contributo n.' . $num_richiesta . ' ' . $denominazione . ' ' . $codice;

                    break;

                case 'INTEGRAZIONE':

                    //Leggo l'oggetto integrazione
                    $integrazione_istruttoria = $this->em->getRepository('IstruttorieBundle:IntegrazioneIstruttoria')->find($id_oggetto_protocollazione);
                    //Leggo la richiesta
                    $richiesta = $integrazione_istruttoria->getIstruttoria()->getRichiesta();

                    /* Popolamento tabelle protocollazione
                     */
                    $oggetto_protocollazione = new \ProtocollazioneBundle\Entity\RichiestaProtocolloIntegrazione();

                    //Leggo l'id del processo con codice 'protocollazione_domande_contributo'
                    $processo = $this->em->getRepository('ProtocollazioneBundle:Processo')
                            ->findOneBy(array('codice' => 'protocollazione_integrazione_istruttoria'));

                    $oggetto_protocollazione->setProcedura($integrazione_istruttoria->getIstruttoria()->getRichiesta()->getProcedura());
                    $oggetto_protocollazione->setRichiesta($richiesta);
                    $oggetto_protocollazione->setIntegrazione($integrazione_istruttoria);

                    //Inserimento data richiesta 
                    $data_creazione = new \DateTime(date('Y-m-d H:i:s'));

                    //Creazione oggetto 
//                    $num_integrazione = $integrazione_istruttoria->getId();
                    $soggetto_mandatario = $richiesta->getSoggetto();
                    $denominazione = $soggetto_mandatario->getDenominazione();
                    $codice = ($soggetto_mandatario instanceof ComuneUnione) ? $soggetto_mandatario->getCodiceFiscale() : $soggetto_mandatario->getPartitaIva();
//                    $oggetto = 'Integrazione istruttoria n.' . $num_integrazione . ' ' . $denominazione . ' ' . $codice;
                    $oggetto = 'Richiesta di integrazioni ' . $denominazione . ' ' . $codice;
                    break;

                case 'RISPOSTA_INTEGRAZIONE':

                    //Leggo l'oggetto risposta integrazione
                    $risposta_integrazione_istruttoria = $this->em->getRepository('IstruttorieBundle:RispostaIntegrazioneIstruttoria')->find($id_oggetto_protocollazione);
                    $integrazione_istruttoria = $risposta_integrazione_istruttoria->getIntegrazione();
                    //Leggo la richiesta
                    $istruttoria = $integrazione_istruttoria->getIstruttoria();
                    $richiesta = $istruttoria->getRichiesta();

                    /* Popolamento tabelle protocollazione
                     */
                    $oggetto_protocollazione = new \ProtocollazioneBundle\Entity\RichiestaProtocolloRispostaIntegrazione();

                    //Leggo l'id del processo con codice 'protocollazione_domande_contributo'
                    $processo = $this->em->getRepository('ProtocollazioneBundle:Processo')
                            ->findOneBy(array('codice' => 'protocollazione_risposta_integrazione_istruttoria'));

                    $oggetto_protocollazione->setProcedura($richiesta->getProcedura());
                    $oggetto_protocollazione->setRichiesta($richiesta);
                    $oggetto_protocollazione->setRispostaIntegrazione($risposta_integrazione_istruttoria);

                    //Inserimento data richiesta 
                    $data_creazione = $risposta_integrazione_istruttoria->getDataCreazione();

                    //Creazione oggetto 
                    $num_risposta_integrazione = $risposta_integrazione_istruttoria->getId();
                    $soggetto_mandatario = $richiesta->getSoggetto();
                    $denominazione = $soggetto_mandatario->getDenominazione();
                    $codice = ($soggetto_mandatario instanceof ComuneUnione) ? $soggetto_mandatario->getCodiceFiscale() : $soggetto_mandatario->getPartitaIva();
                    $oggetto = 'Risposta integrazione istruttoria n.' . $num_risposta_integrazione . ' ' . $denominazione . ' ' . $codice;

                    break;

                default:
                    break;
            }

            $oggetto_protocollazione->setProcesso($processo);
            $oggetto_protocollazione->setTipo($tipo_oggetto_protocollazione);
            $oggetto_protocollazione->setDataCreazioneRichiesta($data_creazione);
            $oggetto_protocollazione->setOggetto($oggetto);

            if ($richiesta->isProceduraParticolare()) {
                $oggetto_protocollazione->setStato('POST_PROTOCOLLAZIONE');
                $oggetto_protocollazione->setFase(0);
                $oggetto_protocollazione->setEsitoFase(1);
            } else {
                $oggetto_protocollazione->setStato('PRONTO_PER_PROTOCOLLAZIONE');
            }

            $this->em->persist($oggetto_protocollazione);

            $richiesta_protocollo_doc = new RichiestaProtocolloDocumento();

            if ($richiesta->isProceduraParticolare() == false) {
                //Richiesta: documento principale
                switch ($tipo_oggetto_protocollazione) {
                    case 'FINANZIAMENTO':
                        if ($richiesta->getProcedura()->isRichiestaFirmaDigitale()) {
                            $doc_principale = $richiesta->getDocumentoRichiestaFirmato();
                        } else {
                            $doc_principale = $richiesta->getDocumentoRichiesta();
                        }
                        $tabella_documento = 'richieste';
                        break;
                    case 'INTEGRAZIONE':
                        $doc_principale = $integrazione_istruttoria->getDocumento();
                        $tabella_documento = 'integrazioni';
                        break;
                    case 'RISPOSTA_INTEGRAZIONE':
                        $doc_principale = $risposta_integrazione_istruttoria->getDocumentoRispostaFirmato();
                        $tabella_documento = 'risposte_integrazioni';
                        break;
                }

                $doc_principale_path = $doc_principale->getPath();
                $doc_principale_file = $doc_principale->getNome();
                $path = $doc_principale_path . $doc_principale_file;

                $richiesta_protocollo_doc->setRichiestaProtocollo($oggetto_protocollazione);
                $richiesta_protocollo_doc->setTabellaDocumento($tabella_documento);
                $richiesta_protocollo_doc->setPath($path);
                $richiesta_protocollo_doc->setPrincipale(1);

                $this->em->persist($richiesta_protocollo_doc);

                //Richiesta: documenti allegati
                switch ($tipo_oggetto_protocollazione) {
                    case 'FINANZIAMENTO':
                        $doc_allegati = $richiesta->getDocumentiRichiesta();
                        // Fix per escludere dalla protocollazione i video di presentazione
                        foreach ($doc_allegati as $key => $doc_allegato) {
                            if (strstr($doc_allegato->getDocumentoFile()->getTipologiaDocumento()->getCodice(), 'VIDEO_DI_PRESENTAZIONE')) {
                                unset($doc_allegati[$key]);
                            }
                        }
                        break;
                    case 'INTEGRAZIONE':
                        $doc_allegati = null;
                        break;
                    case 'RISPOSTA_INTEGRAZIONE':
                        $doc_allegati = $risposta_integrazione_istruttoria->getDocumenti();
                        // Fix per escludere dalla protocollazione i video della risposta all'integrazione
                        foreach ($doc_allegati as $key => $doc_allegato) {
                            if (strstr($doc_allegato->getDocumentoFile()->getTipologiaDocumento()->getCodice(), 'VIDEO_DI_PRESENTAZIONE')) {
                                unset($doc_allegati[$key]);
                            }
                        }
                        break;
                }

                if (!is_null($doc_allegati)) {
                    foreach ($doc_allegati as $allegato) {
                        $allegato_path = $allegato->getDocumentoFile()->getPath();
                        $allegato_file = $allegato->getDocumentoFile()->getNome();
                        $path = $allegato_path . $allegato_file;

                        $richiesta_protocollo_doc = new RichiestaProtocolloDocumento();
                        $richiesta_protocollo_doc->setRichiestaProtocollo($oggetto_protocollazione);
                        $richiesta_protocollo_doc->setTabellaDocumento($tabella_documento);
                        $richiesta_protocollo_doc->setPath($path);
                        $richiesta_protocollo_doc->setPrincipale(0);

                        $this->em->persist($richiesta_protocollo_doc);
                    }
                }
                if ($tipo_oggetto_protocollazione == 'FINANZIAMENTO') {
                    if ($richiesta->getDocumentoMarcaDaBolloDigitale()) {
                        $documentoMarcaDaBolloDigitale = $richiesta->getDocumentoMarcaDaBolloDigitale();
                        // Avendo introdotto il pagamento della marca da bollo digitale
                        // vado ad aggiungere all'elenco degli allegati anche il pdf della marca da bollo digitale.
                        $allegato_path = $documentoMarcaDaBolloDigitale->getPath();
                        $allegato_file = $documentoMarcaDaBolloDigitale->getNome();
                        $path = $allegato_path . $allegato_file;

                        $richiesta_protocollo_doc = new RichiestaProtocolloDocumento();
                        $richiesta_protocollo_doc->setRichiestaProtocollo($oggetto_protocollazione);
                        $richiesta_protocollo_doc->setTabellaDocumento($tabella_documento);
                        $richiesta_protocollo_doc->setPath($path);
                        $richiesta_protocollo_doc->setPrincipale(0);
                        $this->em->persist($richiesta_protocollo_doc);
                    }

                    //Richiesta: documenti proponenti
                    $proponenti = $richiesta->getProponenti();
                    foreach ($proponenti as $proponente) {

                        $documenti_proponente = $proponente->getDocumentiproponente();

                        foreach ($documenti_proponente as $documento_proponente) {
                            $documento_proponente_path = $documento_proponente->getDocumentoFile()->getPath();
                            $documento_proponente_file = $documento_proponente->getDocumentoFile()->getNome();
                            $path_doc_prop = $documento_proponente_path . $documento_proponente_file;

                            $richiesta_protocollo_doc = new RichiestaProtocolloDocumento();
                            $richiesta_protocollo_doc->setRichiestaProtocollo($oggetto_protocollazione);

                            $richiesta_protocollo_doc->setTabellaDocumento('proponenti');
                            $richiesta_protocollo_doc->setPath($path_doc_prop);
                            $richiesta_protocollo_doc->setPrincipale(0);

                            $this->em->persist($richiesta_protocollo_doc);
                        }
                    }
                }
            }
            $this->em->flush();  //Inserisco effettivamente l’oggetto nel database
            //Provo ad effettuare il commit
            $this->em->commit();
        } catch (\Exception $ex) {
            //Effettuo il rollback
            $this->em->rollback();

            switch ($tipo_oggetto_protocollazione) {
                case 'FINANZIAMENTO':
                    $stringa_eccezione = ' l\'invio della richiesta';
                    break;
                case 'INTEGRAZIONE':
                    $stringa_eccezione = ' la creazione dell\'integrazione dell\'istruttoria';
                    break;
                case 'RISPOSTA_INTEGRAZIONE':
                    $stringa_eccezione = ' l\'invio della risposta dell\'integrazione dell\'istruttoria';
                    break;
            }
            throw new SfingeException("Impossibile inizializzare le tabelle di protocollazione dopo" . $stringa_eccezione);
        }

        return $oggetto_protocollazione;
    }

    public function setTabProtocollazioneProroga($proroga) {
        try {

            //Avvio la transazione
            $this->em->beginTransaction();
            /* Popolamento tabelle protocollazione
             * - richieste_protocollo
             * - richieste_protocollo_documenti
             */
            $richiesta_protocollo = new \ProtocollazioneBundle\Entity\RichiestaProtocolloProroga();

            //Leggo l'id del processo con codice 'protocollazione_domande_contributo'
            $processo = $this->em->getRepository('ProtocollazioneBundle:Processo')->findOneBy(array('codice' => 'protocollazione_proroga'));

            $richiesta_protocollo->setProcesso($processo);
            $richiesta_protocollo->setProcedura($proroga->getRichiesta()->getProcedura());
            $richiesta_protocollo->setTipo('PROROGA');

            $richiesta_protocollo->setProroga($proroga);

            //Inserimento data paagemnto e data invio PA
            $data_creazione = $proroga->getDataCreazione();
            $data_invio = $proroga->getDataInvio();
            $richiesta_protocollo->setDataCreazioneRichiesta($data_creazione);
            $richiesta_protocollo->setDataInvioPA($data_invio);

            //Creazione oggetto richiesta
            $num_richiesta = $proroga->getId();
            $soggetto_mandatario = $proroga->getSoggetto();
            $denominazione = $soggetto_mandatario->getDenominazione();
            $codice = ($soggetto_mandatario instanceof ComuneUnione) ? $soggetto_mandatario->getCodiceFiscale() : $soggetto_mandatario->getPartitaIva();
            $oggetto = 'Domanda di proroga n.' . $num_richiesta . ' ' . $denominazione . ' ' . $codice;

            $richiesta_protocollo->setOggetto($oggetto);
            $richiesta_protocollo->setStato('PRONTO_PER_PROTOCOLLAZIONE');

            $this->em->persist($richiesta_protocollo);

            $richiesta_protocollo_doc = new RichiestaProtocolloDocumento();

            $doc_principale = $proroga->getDocumentoProrogaFirmato();
            $doc_principale_path = $doc_principale->getPath();
            $doc_principale_file = $doc_principale->getNome();
            $path = $doc_principale_path . $doc_principale_file;

            $richiesta_protocollo_doc->setRichiestaProtocollo($richiesta_protocollo);
            $richiesta_protocollo_doc->setTabellaDocumento('proroghe');
            $richiesta_protocollo_doc->setPath($path);
            $richiesta_protocollo_doc->setPrincipale(1);

            $this->em->persist($richiesta_protocollo_doc);

            $doc_allegati = $proroga->getDocumenti();

            foreach ($doc_allegati as $allegato) {
                $allegato_path = $allegato->getDocumento()->getPath();
                $allegato_file = $allegato->getDocumento()->getNome();
                $path = $allegato_path . $allegato_file;

                $richiesta_protocollo_doc = new RichiestaProtocolloDocumento();
                $richiesta_protocollo_doc->setRichiestaProtocollo($richiesta_protocollo);
                $richiesta_protocollo_doc->setTabellaDocumento('proroghe');
                $richiesta_protocollo_doc->setPath($path);
                $richiesta_protocollo_doc->setPrincipale(0);

                $this->em->persist($richiesta_protocollo_doc);
            }
            $this->em->flush();  //Inserisco effettivamente l’oggetto nel database
            //Provo ad effettuare il commit
            $this->em->commit();
        } catch (\Exception $ex) {
            throw new SfingeException("Impossibile inizializzare le tabelle di protocollazione dopo l'invio del pagamento");
        }
    }

    public function setTabProtocollazionePagamento($pagamento) {
        try {

            /* Popolamento tabelle protocollazione
             * - richieste_protocollo
             * - richieste_protocollo_documenti
             */
            $richiesta_protocollo_pag = new RichiestaProtocolloPagamento();

            //Leggo l'id del processo con codice 'protocollazione_domande_contributo'
            $processo = $this->em->getRepository('ProtocollazioneBundle:Processo')->findOneBy(array('codice' => 'protocollazione_pagamenti'));

            $richiesta_protocollo_pag->setProcesso($processo);
            $richiesta_protocollo_pag->setProcedura($pagamento->getRichiesta()->getProcedura());
            $richiesta_protocollo_pag->setTipo('PAGAMENTO');

            $richiesta_protocollo_pag->setPagamento($pagamento);

            //Inserimento data paagemnto e data invio PA
            $data_creazione = $pagamento->getDataCreazione();
            $data_invio = $pagamento->getDataInvio();
            $richiesta_protocollo_pag->setDataCreazioneRichiesta($data_creazione);
            $richiesta_protocollo_pag->setDataInvioPA($data_invio);

            //Creazione oggetto richiesta
            $num_richiesta = $pagamento->getId();
            $soggetto_mandatario = $pagamento->getSoggetto();
            $denominazione = $soggetto_mandatario->getDenominazione();
            $codice = ($soggetto_mandatario instanceof ComuneUnione) ? $soggetto_mandatario->getCodiceFiscale() : $soggetto_mandatario->getPartitaIva();
            $oggetto = 'Domanda di pagamento n.' . $num_richiesta . ' ' . $denominazione . ' ' . $codice;

            $richiesta_protocollo_pag->setOggetto($oggetto);

            if ($pagamento->isProceduraParticolare() == true) {
                $richiesta_protocollo_pag->setStato('POST_PROTOCOLLAZIONE');
                $richiesta_protocollo_pag->setFase(0);
                $richiesta_protocollo_pag->setEsitoFase(1);
            } else {
                $richiesta_protocollo_pag->setStato('PRONTO_PER_PROTOCOLLAZIONE');
            }

            $this->em->persist($richiesta_protocollo_pag);

            $richiesta_protocollo_doc = new RichiestaProtocolloDocumento();

            if ($pagamento->isProceduraParticolare() == false) {
                //Richiesta: documento principale
                $doc_principale = $pagamento->getDocumentoPagamentoFirmato();
                if (!$pagamento->getProcedura()->getRendicontazioneProceduraConfig()->isRichiestaFirmaDigitale()) {
                    $doc_principale = $pagamento->getDocumentoPagamento();
                }
                $doc_principale_path = $doc_principale->getPath();
                $doc_principale_file = $doc_principale->getNome();
                $path = $doc_principale_path . $doc_principale_file;
                $richiesta_protocollo_doc->setPath($path);
                $richiesta_protocollo_doc->setPrincipale(1);
                $richiesta_protocollo_doc->setRichiestaProtocollo($richiesta_protocollo_pag);
                $richiesta_protocollo_doc->setTabellaDocumento('pagamenti');
                //perstist doc principale
                $this->em->persist($richiesta_protocollo_doc);


                //Richiesta: documenti allegati
                $doc_allegati = $pagamento->getDocumentiPagamento();

                foreach ($doc_allegati as $allegato) {
                    // Escludo dalla protocollazione il video del pagamento
                    if ($allegato->getDocumentoFile()->getTipologiaDocumento()->getCodice() == 'VIDEO_PAGAMENTO') {
                        continue;
                    }

                    $allegato_path = $allegato->getDocumentoFile()->getPath();
                    $allegato_file = $allegato->getDocumentoFile()->getNome();
                    $path = $allegato_path . $allegato_file;

                    $richiesta_protocollo_doc = new RichiestaProtocolloDocumento();
                    $richiesta_protocollo_doc->setRichiestaProtocollo($richiesta_protocollo_pag);
                    $richiesta_protocollo_doc->setTabellaDocumento('pagamenti');
                    $richiesta_protocollo_doc->setPath($path);
                    $richiesta_protocollo_doc->setPrincipale(0);

                    $this->em->persist($richiesta_protocollo_doc);
                }

                //doc giustificativi
                $doc_giustificativi = array();

                if ($pagamento->getProcedura()->getId() == 7 || $pagamento->getProcedura()->getId() == 8 || $pagamento->getProcedura()->getId() == 32) {
                    foreach ($pagamento->getGiustificativi() as $giustificativo) {
                        foreach ($giustificativo->getDocumentiGiustificativo() as $docGiustificativo) {
                            $doc_giustificativi[] = $docGiustificativo->getDocumentoFile();
                        }
                    }
                } else {
                    foreach ($pagamento->getGiustificativi() as $giustificativo) {
                        $tipologia = $giustificativo->getTipologiaGiustificativo();
                        if (!is_null($tipologia) && $tipologia->isInvisibile() == true) {
                            continue;
                        }

                        $documentoGiustificativo = $giustificativo->getDocumentoGiustificativo();
                        // per bando legge 14 è stato deciso di rendere opzionale il caricamento del documento principale del giustificativo
                        if (!is_null($documentoGiustificativo)) {
                            $doc_giustificativi[] = $documentoGiustificativo;
                        }

                        foreach ($giustificativo->getDocumentiGiustificativo() as $docGiustificativo) {
                            $doc_giustificativi[] = $docGiustificativo->getDocumentoFile();
                        }
                    }
                }

                foreach ($doc_giustificativi as $giustificativo) {
                    $giustificativo_path = $giustificativo->getPath();
                    $giustificativo_file = $giustificativo->getNome();
                    $path = $giustificativo_path . $giustificativo_file;

                    $richiesta_protocollo_doc = new RichiestaProtocolloDocumento();
                    $richiesta_protocollo_doc->setRichiestaProtocollo($richiesta_protocollo_pag);
                    $richiesta_protocollo_doc->setTabellaDocumento('pagamenti');
                    $richiesta_protocollo_doc->setPath($path);
                    $richiesta_protocollo_doc->setPrincipale(0);

                    $this->em->persist($richiesta_protocollo_doc);
                }

                //doc quietanze
                $doc_quietanze = array();
                foreach ($pagamento->getGiustificativi() as $giustificativo) {
                    foreach ($giustificativo->getQuietanze() as $quietanza) {
                        $doc_quietanze[] = $quietanza->getDocumentoQuietanza();
                    }
                }

                foreach ($doc_quietanze as $quietanza) {
                    $quietanza_path = $quietanza->getPath();
                    $quietanza_file = $quietanza->getNome();
                    $path = $quietanza_path . $quietanza_file;

                    $richiesta_protocollo_doc = new RichiestaProtocolloDocumento();
                    $richiesta_protocollo_doc->setRichiestaProtocollo($richiesta_protocollo_pag);
                    $richiesta_protocollo_doc->setTabellaDocumento('pagamenti');
                    $richiesta_protocollo_doc->setPath($path);
                    $richiesta_protocollo_doc->setPrincipale(0);

                    $this->em->persist($richiesta_protocollo_doc);
                }

                //doc contratti
                $doc_contratti = array();
                foreach ($pagamento->getContratti() as $contratto) {
                    foreach ($contratto->getDocumentiContratto() as $doc) {
                        $doc_contratti[] = $doc->getDocumentoFile();
                    }
                }

                foreach ($doc_contratti as $contratto) {
                    $contratto_path = $contratto->getPath();
                    $contratto_file = $contratto->getNome();
                    $path = $contratto_path . $contratto_file;

                    $richiesta_protocollo_doc = new RichiestaProtocolloDocumento();
                    $richiesta_protocollo_doc->setRichiestaProtocollo($richiesta_protocollo_pag);
                    $richiesta_protocollo_doc->setTabellaDocumento('pagamenti');
                    $richiesta_protocollo_doc->setPath($path);
                    $richiesta_protocollo_doc->setPrincipale(0);

                    $this->em->persist($richiesta_protocollo_doc);
                }
                // schedulazione della protocollazione dei documenti propri del bando 7				
                if ($pagamento->getProcedura()->getId() == 7) {
                    $this->setDocumentiBando7($pagamento, $richiesta_protocollo_pag);
                }
                // schedulazione della protocollazione dei documenti propri del bando 8
                if ($pagamento->getProcedura()->getId() == 8 || $pagamento->getProcedura()->getId() == 32) {
                    $this->setDocumentiBando8($pagamento, $richiesta_protocollo_pag);
                }
            }
        } catch (\Exception $ex) {
            throw new SfingeException("Impossibile inizializzare le tabelle di protocollazione dopo l'invio del pagamento");
        }
    }

    public function setTabProtocollazioneIntegrazionePagamento($pagamento, $integrazione) {
        try {

            $richiesta_protocollo_pag = new \ProtocollazioneBundle\Entity\RichiestaProtocolloIntegrazionePagamento();

            //Leggo l'id del processo con codice 'protocollazione_domande_contributo'
            $processo = $this->em->getRepository('ProtocollazioneBundle:Processo')->findOneBy(array('codice' => 'protocollazione_integrazione_pagamento'));

            $richiesta_protocollo_pag->setProcesso($processo);
            $richiesta_protocollo_pag->setProcedura($pagamento->getRichiesta()->getProcedura());
            $richiesta_protocollo_pag->setTipo('INTEGRAZIONE_PAGAMENTO');

            $richiesta_protocollo_pag->setIntegrazionePagamento($integrazione);

            //Inserimento data paagemnto e data invio PA
            $data_creazione = $integrazione->getDataCreazione();
            $data_invio = $integrazione->getData();
            $richiesta_protocollo_pag->setDataCreazioneRichiesta($data_creazione);
            $richiesta_protocollo_pag->setDataInvioPA($data_invio);

            //Creazione oggetto richiesta
            $num_richiesta = $pagamento->getId();
            $soggetto_mandatario = $pagamento->getSoggetto();
            $denominazione = $soggetto_mandatario->getDenominazione();
            $codice = ($soggetto_mandatario instanceof ComuneUnione) ? $soggetto_mandatario->getCodiceFiscale() : $soggetto_mandatario->getPartitaIva();
            $oggetto = 'Domanda di integrazione pagamento n.' . $num_richiesta . ' ' . $denominazione . ' ' . $codice;

            $richiesta_protocollo_pag->setOggetto($oggetto);
            $richiesta_protocollo_pag->setStato('PRONTO_PER_PROTOCOLLAZIONE');

            $this->em->persist($richiesta_protocollo_pag);

            $richiesta_protocollo_doc = new RichiestaProtocolloDocumento();

            //Richiesta: documento principale
            $doc_principale = $integrazione->getDocumento();
            $doc_principale_path = $doc_principale->getPath();
            $doc_principale_file = $doc_principale->getNome();
            $path = $doc_principale_path . $doc_principale_file;
            $richiesta_protocollo_doc->setPath($path);
            $richiesta_protocollo_doc->setPrincipale(1);
            $richiesta_protocollo_doc->setRichiestaProtocollo($richiesta_protocollo_pag);
            $richiesta_protocollo_doc->setTabellaDocumento('integrazioni_pagamenti');
            //perstist doc principale
            $this->em->persist($richiesta_protocollo_doc);
        } catch (\Exception $ex) {
            throw new SfingeException("Impossibile inizializzare le tabelle di protocollazione dopo l'invio del pagamento");
        }

        return $richiesta_protocollo_pag;
    }

    public function setTabProtocollazioneIntegrazioneRispostaPagamento($pagamento, $integrazione) {
        try {

            $richiesta_protocollo_pag = new \ProtocollazioneBundle\Entity\RichiestaProtocolloRispostaIntegrazionePagamento();

            //Leggo l'id del processo con codice 'protocollazione_domande_contributo'
            $processo = $this->em->getRepository('ProtocollazioneBundle:Processo')->findOneBy(array('codice' => 'protocollazione_risposta_integrazione_pagamento'));

            $richiesta_protocollo_pag->setProcesso($processo);
            $richiesta_protocollo_pag->setProcedura($pagamento->getRichiesta()->getProcedura());
            $richiesta_protocollo_pag->setTipo('RISPOSTA_INTEGRAZIONE_PAGAMENTO');

            $richiesta_protocollo_pag->setRispostaIntegrazionePagamento($integrazione);

            //Inserimento data paagemnto e data invio PA
            $data_creazione = $integrazione->getDataCreazione();
            $data_invio = $integrazione->getData();
            $richiesta_protocollo_pag->setDataCreazioneRichiesta($data_creazione);
            $richiesta_protocollo_pag->setDataInvioPA($data_invio);

            //Creazione oggetto richiesta
            $num_richiesta = $pagamento->getId();
            $soggetto_mandatario = $pagamento->getSoggetto();
            $denominazione = $soggetto_mandatario->getDenominazione();
            $codice = ($soggetto_mandatario instanceof ComuneUnione) ? $soggetto_mandatario->getCodiceFiscale() : $soggetto_mandatario->getPartitaIva();
            $oggetto = 'Risposta di integrazione pagamento n.' . $num_richiesta . ' ' . $denominazione . ' ' . $codice;

            $richiesta_protocollo_pag->setOggetto($oggetto);
            $richiesta_protocollo_pag->setStato('PRONTO_PER_PROTOCOLLAZIONE');

            $this->em->persist($richiesta_protocollo_pag);

            $richiesta_protocollo_doc = new RichiestaProtocolloDocumento();

            //Richiesta: documento principale
            if ($integrazione->getProcedura()->isRichiestaFirmaDigitaleStepSuccessivi()) {
                $doc_principale = $integrazione->getDocumentoRispostaFirmato();
            } else {
                $doc_principale = $integrazione->getDocumentoRisposta();
            }

            $doc_principale_path = $doc_principale->getPath();
            $doc_principale_file = $doc_principale->getNome();
            $path = $doc_principale_path . $doc_principale_file;
            $richiesta_protocollo_doc->setPath($path);
            $richiesta_protocollo_doc->setPrincipale(1);
            $richiesta_protocollo_doc->setRichiestaProtocollo($richiesta_protocollo_pag);
            $richiesta_protocollo_doc->setTabellaDocumento('risposte_integrazioni_pagamenti');
            //perstist doc principale
            $this->em->persist($richiesta_protocollo_doc);

            $doc_allegati = $integrazione->getDocumenti();

            foreach ($doc_allegati as $allegato) {
                // Escludo dalla protocollazione il file video della risposta all'integrazione del pagamento
                if ($allegato->getDocumentoFile()->getTipologiaDocumento()->getCodice() == 'INTEGRAZIONE_PAGAMENTO_VIDEO') {
                    continue;
                }

                $allegato_path = $allegato->getDocumentoFile()->getPath();
                $allegato_file = $allegato->getDocumentoFile()->getNome();
                $path = $allegato_path . $allegato_file;

                $richiesta_protocollo_doc = new RichiestaProtocolloDocumento();
                $richiesta_protocollo_doc->setRichiestaProtocollo($richiesta_protocollo_pag);
                $richiesta_protocollo_doc->setTabellaDocumento('risposte_integrazioni_pagamenti');
                $richiesta_protocollo_doc->setPath($path);
                $richiesta_protocollo_doc->setPrincipale(0);

                $this->em->persist($richiesta_protocollo_doc);
            }
        } catch (\Exception $ex) {
            throw new SfingeException("Impossibile inizializzare le tabelle di protocollazione dopo l'invio del pagamento");
        }

        return $richiesta_protocollo_pag;
    }

    /**
     * @param RichiestaChiarimento $richiesta_chiarimento
     */
    public function setTabProtocollazioneRichiestaChiarimenti($oggetto, $richiesta_chiarimento) {
        try {

            $richiesta_protocollo_pag = new \ProtocollazioneBundle\Entity\RichiestaProtocolloRichiestaChiarimenti();

            //Leggo l'id del processo con codice 'protocollazione_domande_contributo'
            $processo = $this->em->getRepository('ProtocollazioneBundle:Processo')->findOneBy(array('codice' => 'protocollazione_richiesta_chiarimenti'));

            $richiesta_protocollo_pag->setProcesso($processo);
            $richiesta_protocollo_pag->setProcedura($oggetto->getRichiesta()->getProcedura());
            $richiesta_protocollo_pag->setTipo('RICHIESTA_CHIARIMENTI');

            $richiesta_protocollo_pag->setRichiestaChiarimento($richiesta_chiarimento);

            //Inserimento data paagemnto e data invio PA
            $data_creazione = $oggetto->getDataCreazione();
            $data_invio = $oggetto->getDataInvio();
            $richiesta_protocollo_pag->setDataCreazioneRichiesta($data_creazione);
            $richiesta_protocollo_pag->setDataInvioPA($data_invio);

            //Creazione oggetto richiesta
            $num_richiesta = $oggetto->getId();
            $soggetto_mandatario = $oggetto->getSoggetto();
            $denominazione = $soggetto_mandatario->getDenominazione();
            $codice = ($soggetto_mandatario instanceof ComuneUnione) ? $soggetto_mandatario->getCodiceFiscale() : $soggetto_mandatario->getPartitaIva();

            $oggetto_protocollazione = 'Richiesta chiarimenti ' . ($oggetto instanceof Pagamento ? 'sul pagamento' : 'sulla richiesta') . ' n.' . $num_richiesta . ' ' . $denominazione . ' ' . $codice;

            $richiesta_protocollo_pag->setOggetto($oggetto_protocollazione);
            $richiesta_protocollo_pag->setStato('PRONTO_PER_PROTOCOLLAZIONE');

            $this->em->persist($richiesta_protocollo_pag);

            $richiesta_protocollo_doc = new RichiestaProtocolloDocumento();

            //Richiesta: documento principale
            $doc_principale = $richiesta_chiarimento->getDocumento();
            $doc_principale_path = $doc_principale->getPath();
            $doc_principale_file = $doc_principale->getNome();
            $path = $doc_principale_path . $doc_principale_file;
            $richiesta_protocollo_doc->setPath($path);
            $richiesta_protocollo_doc->setPrincipale(1);
            $richiesta_protocollo_doc->setRichiestaProtocollo($richiesta_protocollo_pag);
            $richiesta_protocollo_doc->setTabellaDocumento('richieste_chiarimenti');

            foreach ($richiesta_chiarimento->getAllegati() as $allegato) {
                $allegato_path = $allegato->getDocumento()->getPath();
                $allegato_file = $allegato->getDocumento()->getNome();
                $path = $allegato_path . $allegato_file;

                $protocolloAllegato = new RichiestaProtocolloDocumento();
                $protocolloAllegato->setRichiestaProtocollo($richiesta_protocollo_pag);
                $protocolloAllegato->setTabellaDocumento('richieste_chiarimenti');
                $protocolloAllegato->setPath($path);
                $protocolloAllegato->setPrincipale(0);

                $this->em->persist($protocolloAllegato);
            }

            //perstist doc principale
            $this->em->persist($richiesta_protocollo_doc);
        } catch (\Exception $ex) {
            throw new SfingeException("Impossibile inizializzare le tabelle di protocollazione dopo l'invio del chiarimento");
        }

        return $richiesta_protocollo_pag;
    }

    public function setTabProtocollazioneRispostaRichiestaChiarimenti($oggetto, $risposta_richiesta_chiarimenti) {
        try {

            $richiesta_protocollo_pag = new \ProtocollazioneBundle\Entity\RichiestaProtocolloRispostaRichiestaChiarimenti();

            //Leggo l'id del processo con codice 'protocollazione_domande_contributo'
            $processo = $this->em->getRepository('ProtocollazioneBundle:Processo')->findOneBy(array('codice' => 'protocollazione_risposta richiesta_chiarimenti'));

            $richiesta_protocollo_pag->setProcesso($processo);
            $richiesta_protocollo_pag->setProcedura($oggetto->getRichiesta()->getProcedura());
            $richiesta_protocollo_pag->setTipo('RISPOSTA_RICHIESTA_CHIARIMENTI');

            $richiesta_protocollo_pag->setRispostaRichiestaChiarimenti($risposta_richiesta_chiarimenti);

            //Inserimento data paagemnto e data invio PA
            $data_creazione = $oggetto->getDataCreazione();
            $data_invio = $oggetto->getDataInvio();
            $richiesta_protocollo_pag->setDataCreazioneRichiesta($data_creazione);
            $richiesta_protocollo_pag->setDataInvioPA($data_invio);

            //Creazione oggetto richiesta
            $num_richiesta = $oggetto->getId();
            $soggetto_mandatario = $oggetto->getSoggetto();
            $denominazione = $soggetto_mandatario->getDenominazione();
            $codice = ($soggetto_mandatario instanceof ComuneUnione) ? $soggetto_mandatario->getCodiceFiscale() : $soggetto_mandatario->getPartitaIva();

            $oggetto_protocollazione = 'Risposta alla richiesta chiarimenti ' . ($oggetto instanceof Pagamento ? 'su pagamento' : 'sulla richiesta') . ' n.' . $num_richiesta . ' ' . $denominazione . ' ' . $codice;

            $richiesta_protocollo_pag->setOggetto($oggetto_protocollazione);
            $richiesta_protocollo_pag->setStato('PRONTO_PER_PROTOCOLLAZIONE');

            $this->em->persist($richiesta_protocollo_pag);

            $richiesta_protocollo_doc = new RichiestaProtocolloDocumento();

            //Richiesta: documento principale
            $doc_principale = $risposta_richiesta_chiarimenti->getDocumentoRispostaFirmato();
            $doc_principale_path = $doc_principale->getPath();
            $doc_principale_file = $doc_principale->getNome();
            $path = $doc_principale_path . $doc_principale_file;
            $richiesta_protocollo_doc->setPath($path);
            $richiesta_protocollo_doc->setPrincipale(1);
            $richiesta_protocollo_doc->setRichiestaProtocollo($richiesta_protocollo_pag);
            $richiesta_protocollo_doc->setTabellaDocumento('risposte_richieste_chiarimenti');
            //perstist doc principale
            $this->em->persist($richiesta_protocollo_doc);

            $doc_allegati = $risposta_richiesta_chiarimenti->getDocumenti();

            foreach ($doc_allegati as $allegato) {
                $allegato_path = $allegato->getDocumentoFile()->getPath();
                $allegato_file = $allegato->getDocumentoFile()->getNome();
                $path = $allegato_path . $allegato_file;

                $richiesta_protocollo_doc = new RichiestaProtocolloDocumento();
                $richiesta_protocollo_doc->setRichiestaProtocollo($richiesta_protocollo_pag);
                $richiesta_protocollo_doc->setTabellaDocumento('risposte_richieste_chiarimenti');
                $richiesta_protocollo_doc->setPath($path);
                $richiesta_protocollo_doc->setPrincipale(0);

                $this->em->persist($richiesta_protocollo_doc);
            }
        } catch (\Exception $ex) {
            throw new SfingeException("Impossibile inizializzare le tabelle di protocollazione dopo l'invio della risposta alla richiesta di chiarimenti");
        }

        return $richiesta_protocollo_pag;
    }

    public function setTabProtocollazioneVariazione($id_variazione) {
        try {

            //Avvio la transazione
            $this->em->beginTransaction();

            //Leggo l'oggetto richiesta
            $variazione = $this->em->getRepository('AttuazioneControlloBundle:VariazioneRichiesta')->find($id_variazione);

            $richiesta_protocollo_var = new RichiestaProtocolloVariazione();

            //Leggo l'id del processo con codice 'protocollazione_domande_contributo'
            $processo = $this->em->getRepository('ProtocollazioneBundle:Processo')->findOneBy(array('codice' => 'protocollazione_variazioni'));

            $richiesta_protocollo_var->setProcesso($processo);
            $richiesta_protocollo_var->setProcedura($variazione->getAttuazioneControlloRichiesta()->getRichiesta()->getProcedura());
            $richiesta_protocollo_var->setTipo('VARIAZIONE');

            $richiesta_protocollo_var->setVariazione($variazione);

            //Inserimento data paagemnto e data invio PA
            $data_creazione = $variazione->getDataCreazione();
            $data_invio = $variazione->getDataInvio();
            $richiesta_protocollo_var->setDataCreazioneRichiesta($data_creazione);
            $richiesta_protocollo_var->setDataInvioPA($data_invio);

            //Creazione oggetto richiesta
            $num_richiesta = $variazione->getId();
            $soggetto_mandatario = $variazione->getSoggetto();
            $denominazione = $soggetto_mandatario->getDenominazione();
            $codice = ($soggetto_mandatario instanceof ComuneUnione) ? $soggetto_mandatario->getCodiceFiscale() : $soggetto_mandatario->getPartitaIva();
            $oggetto = 'Domanda di variazione n.' . $num_richiesta . ' ' . $denominazione . ' ' . $codice;

            $richiesta_protocollo_var->setOggetto($oggetto);
            $richiesta_protocollo_var->setStato('PRONTO_PER_PROTOCOLLAZIONE');

            $this->em->persist($richiesta_protocollo_var);

            $richiesta_protocollo_doc = new RichiestaProtocolloDocumento();

            //Richiesta: documento principale
            if ($variazione->getProcedura()->isRichiestaFirmaDigitaleStepSuccessivi()) {
                $doc_principale = $variazione->getDocumentoVariazioneFirmato();
            } else {
                $doc_principale = $variazione->getDocumentoVariazione();
            }

            $doc_principale_path = $doc_principale->getPath();
            $doc_principale_file = $doc_principale->getNome();
            $path = $doc_principale_path . $doc_principale_file;

            $richiesta_protocollo_doc->setRichiestaProtocollo($richiesta_protocollo_var);
            $richiesta_protocollo_doc->setTabellaDocumento('variazioni');
            $richiesta_protocollo_doc->setPath($path);
            $richiesta_protocollo_doc->setPrincipale(1);

            $this->em->persist($richiesta_protocollo_doc);

            $doc_allegati = $variazione->getDocumentiVariazione();

            foreach ($doc_allegati as $allegato) {
                $allegato_path = $allegato->getDocumentoFile()->getPath();
                $allegato_file = $allegato->getDocumentoFile()->getNome();
                $path = $allegato_path . $allegato_file;

                $richiesta_protocollo_doc = new RichiestaProtocolloDocumento();
                $richiesta_protocollo_doc->setRichiestaProtocollo($richiesta_protocollo_var);
                $richiesta_protocollo_doc->setTabellaDocumento('variazioni');
                $richiesta_protocollo_doc->setPath($path);
                $richiesta_protocollo_doc->setPrincipale(0);

                $this->em->persist($richiesta_protocollo_doc);
            }
            $this->em->flush();  //Inserisco effettivamente l’oggetto nel database
            //Provo ad effettuare il commit
            $this->em->commit();
        } catch (\Exception $ex) {
            throw new SfingeException("Impossibile inizializzare le tabelle di protocollazione dopo l'invio della variazione");
        }
    }

    public function setTabProtocollazioneEsitoIstruttoriaPagamento($pagamento, $esito_istruttoria_pagamento) {
        try {

            $richiesta_protocollo_pag = new \ProtocollazioneBundle\Entity\RichiestaProtocolloEsitoIstruttoriaPagamento();

            //Leggo l'id del processo con codice 'protocollazione_domande_contributo'
            $processo = $this->em->getRepository('ProtocollazioneBundle:Processo')->findOneBy(array('codice' => 'protocollazione_esito_istruttoria_pagamento'));

            $richiesta_protocollo_pag->setProcesso($processo);
            $richiesta_protocollo_pag->setProcedura($pagamento->getRichiesta()->getProcedura());
            $richiesta_protocollo_pag->setTipo('ESITO_ISTRUTTORIA_PAGAMENTO');

            $richiesta_protocollo_pag->setEsitoIstruttoriaPagamento($esito_istruttoria_pagamento);

            //Inserimento data paagemnto e data invio PA
            $data_creazione = $pagamento->getDataCreazione();
            $data_invio = $pagamento->getDataInvio();
            $richiesta_protocollo_pag->setDataCreazioneRichiesta($data_creazione);
            $richiesta_protocollo_pag->setDataInvioPA($data_invio);

            //Creazione oggetto richiesta
            $num_richiesta = $pagamento->getId();
            $soggetto_mandatario = $pagamento->getSoggetto();
            $denominazione = $soggetto_mandatario->getDenominazione();
            $codice = ($soggetto_mandatario instanceof ComuneUnione) ? $soggetto_mandatario->getCodiceFiscale() : $soggetto_mandatario->getPartitaIva();
            $oggetto = 'Esito istruttoria pagamento n.' . $num_richiesta . ' ' . $denominazione . ' ' . $codice;

            $richiesta_protocollo_pag->setOggetto($oggetto);
            $richiesta_protocollo_pag->setStato('PRONTO_PER_PROTOCOLLAZIONE');

            $this->em->persist($richiesta_protocollo_pag);

            $richiesta_protocollo_doc = new RichiestaProtocolloDocumento();

            //Richiesta: documento principale
            $doc_principale = $esito_istruttoria_pagamento->getDocumento();
            $doc_principale_path = $doc_principale->getPath();
            $doc_principale_file = $doc_principale->getNome();
            $path = $doc_principale_path . $doc_principale_file;
            $richiesta_protocollo_doc->setPath($path);
            $richiesta_protocollo_doc->setPrincipale(1);
            $richiesta_protocollo_doc->setRichiestaProtocollo($richiesta_protocollo_pag);
            $richiesta_protocollo_doc->setTabellaDocumento('documenti_esito_istruttoria');
            //perstist doc principale
            $this->em->persist($richiesta_protocollo_doc);

            // Richiesta: allegati:
            foreach ($esito_istruttoria_pagamento->getDocumentiEsitoIstruttoria() as $documenti_esito_istruttoria_pagamento) {

                $richiesta_protocollo_doc = new RichiestaProtocolloDocumento();

                $doc_da_allegare = $documenti_esito_istruttoria_pagamento->getDocumentoFile();

                $doc_da_allegare_path = $doc_da_allegare->getPath();
                $doc_da_allegare_file = $doc_da_allegare->getNome();
                $path = $doc_da_allegare_path . $doc_da_allegare_file;
                $richiesta_protocollo_doc->setPath($path);
                $richiesta_protocollo_doc->setPrincipale(0);
                $richiesta_protocollo_doc->setRichiestaProtocollo($richiesta_protocollo_pag);
                $richiesta_protocollo_doc->setTabellaDocumento('documenti_esito_istruttoria');
                //perstist doc allegati
                $this->em->persist($richiesta_protocollo_doc);
            }
        } catch (\Exception $ex) {
            throw new SfingeException("Impossibile inizializzare le tabelle di protocollazione dopo l'invio del pagamento");
        }

        return $richiesta_protocollo_pag;
    }

    // schedulazione della protocollazione dei documenti propri del bando 7
    public function setDocumentiBando7($pagamento, $richiesta_protocollo_pag) {

        $documentiContratto = array();
        $documentiEstensionePagamento = array();
        $documentiEstensioneGiustificativo = array();
        $documentiPersonale = array();
        $documentiLavorazioni = array();

        foreach ($pagamento->getGiustificativi() as $giustificativo) {

            $estensioneGiustificativo = $giustificativo->getEstensione();
            if (!is_null($estensioneGiustificativo)) {
                foreach ($estensioneGiustificativo->getDocumenti() as $documentoEstensioneGiustificativo) {
                    $documentiEstensioneGiustificativo[] = $documentoEstensioneGiustificativo->getDocumentoFile();
                }
            }

            $contratto = $giustificativo->getContratto();
            if (!is_null($contratto)) {
                foreach ($contratto->getDocumentiContratto() as $documentoContratto) {
                    $documentiContratto[] = $documentoContratto->getDocumentoFile();
                }
            }
        }

        $estensionePagamento = $pagamento->getEstensione();
        if (!is_null($estensionePagamento)) {

            foreach ($estensionePagamento->getDocumenti() as $documentoEstensionePagamento) {
                $documentiEstensionePagamento[] = $documentoEstensionePagamento->getDocumentoFile();
            }

            foreach ($estensionePagamento->getDocumentiLavorazioni() as $documentoLavorazioni) {
                $documentiLavorazioni[] = $documentoLavorazioni->getDocumentoFile();
            }
        }

        foreach ($pagamento->getPersonale() as $personale) {
            foreach ($personale->getDocumentiPersonale() as $documentoPersonale) {
                $documentiPersonale[] = $documentoPersonale->getDocumentoFile();
            }
        }

        $documentiDaProtocollare = array_merge(
                $documentiContratto, $documentiEstensioneGiustificativo, $documentiEstensionePagamento, $documentiPersonale, $documentiLavorazioni);

        foreach ($documentiDaProtocollare as $documentoDaProtocollare) {

            $filePath = $documentoDaProtocollare->getPath();
            $fileName = $documentoDaProtocollare->getNome();
            $path = $filePath . $fileName;

            $richiestaProtocolloDocumento = new RichiestaProtocolloDocumento();
            $richiestaProtocolloDocumento->setRichiestaProtocollo($richiesta_protocollo_pag);
            $richiestaProtocolloDocumento->setTabellaDocumento('pagamenti');
            $richiestaProtocolloDocumento->setPath($path);
            $richiestaProtocolloDocumento->setPrincipale(0);

            $this->em->persist($richiestaProtocolloDocumento);
        }
    }

    public function setTabProtocollazioneEsitoIstruttoriaRichiesta($istruttoria, $comunicazione_esito) {
        try {

            $richiesta_protocollo_esi = new \ProtocollazioneBundle\Entity\RichiestaProtocolloEsitoIstruttoria();

            $processo = $this->em->getRepository('ProtocollazioneBundle:Processo')->findOneBy(array('codice' => 'protocollazione_esito_istruttoria_richiesta'));

            $richiesta_protocollo_esi->setProcesso($processo);
            $richiesta_protocollo_esi->setProcedura($istruttoria->getRichiesta()->getProcedura());
            $richiesta_protocollo_esi->setTipo('COMUNICAZIONE_ESITO_RICHIESTA');

            $richiesta_protocollo_esi->setComunicazioneEsito($comunicazione_esito);

            $data_creazione = $comunicazione_esito->getData();
            $data_invio = $comunicazione_esito->getDataInvio();
            $richiesta_protocollo_esi->setDataCreazioneRichiesta($data_creazione);
            $richiesta_protocollo_esi->setDataInvioPA($data_invio);

            //Creazione oggetto richiesta
            $num_richiesta = $comunicazione_esito->getIstruttoria()->getRichiesta()->getId();
            $soggetto_mandatario = $istruttoria->getRichiesta()->getSoggetto();
            $denominazione = $soggetto_mandatario->getDenominazione();
            $codice = ($soggetto_mandatario instanceof ComuneUnione) ? $soggetto_mandatario->getCodiceFiscale() : $soggetto_mandatario->getPartitaIva();
            $oggetto = 'Comunicazione di esito istruttoria n.' . $num_richiesta . ' ' . $denominazione . ' ' . $codice;

            $richiesta_protocollo_esi->setOggetto($oggetto);
            $richiesta_protocollo_esi->setStato('PRONTO_PER_PROTOCOLLAZIONE');

            $this->em->persist($richiesta_protocollo_esi);

            $richiesta_protocollo_doc = new RichiestaProtocolloDocumento();

            //Richiesta: documento principale
            $doc_principale = $comunicazione_esito->getDocumento();
            $doc_principale_path = $doc_principale->getPath();
            $doc_principale_file = $doc_principale->getNome();
            $path = $doc_principale_path . $doc_principale_file;
            $richiesta_protocollo_doc->setPath($path);
            $richiesta_protocollo_doc->setPrincipale(1);
            $richiesta_protocollo_doc->setRichiestaProtocollo($richiesta_protocollo_esi);
            $richiesta_protocollo_doc->setTabellaDocumento('comunicazioni_esiti');
            //perstist doc principale
            $this->em->persist($richiesta_protocollo_doc);

            foreach ($comunicazione_esito->getDocumentiComunicazione() as $documentoDaProtocollare) {

                $filePath = $documentoDaProtocollare->getDocumentoFile()->getPath();
                $fileName = $documentoDaProtocollare->getDocumentoFile()->getNome();
                $path = $filePath . $fileName;

                $richiestaProtocolloDocumento = new RichiestaProtocolloDocumento();
                $richiestaProtocolloDocumento->setRichiestaProtocollo($richiesta_protocollo_esi);
                $richiestaProtocolloDocumento->setTabellaDocumento('comunicazioni_esiti');
                $richiestaProtocolloDocumento->setPath($path);
                $richiestaProtocolloDocumento->setPrincipale(0);

                $this->em->persist($richiestaProtocolloDocumento);
            }
        } catch (\Exception $ex) {
            throw new SfingeException("Impossibile inizializzare le tabelle di protocollazione dopo l'invio del pagamento");
        }

        return $richiesta_protocollo_esi;
    }

    public function setTabProtocollazioneRispostaEsitoRichiesta($comunicazione_risposta) {
        try {

            $richiesta_protocollo_esi = new \ProtocollazioneBundle\Entity\RichiestaProtocolloRispostaEsitoIstruttoria();

            $processo = $this->em->getRepository('ProtocollazioneBundle:Processo')->findOneBy(array('codice' => 'protocollazione_esito_istruttoria_risposta'));

            $richiesta_protocollo_esi->setProcesso($processo);
            $richiesta_protocollo_esi->setProcedura($comunicazione_risposta->getRichiesta()->getProcedura());
            $richiesta_protocollo_esi->setTipo('RISPOSTA_ESITO_COMUNICAZIONE');

            $richiesta_protocollo_esi->setRispostaComunicazione($comunicazione_risposta);

            $data_creazione = $comunicazione_risposta->getDataCreazione();
            $data_invio = $comunicazione_risposta->getData();
            $richiesta_protocollo_esi->setDataCreazioneRichiesta($data_creazione);
            $richiesta_protocollo_esi->setDataInvioPA($data_invio);

            //Creazione oggetto richiesta
            $num_richiesta = $comunicazione_risposta->getRichiesta()->getId();
            $soggetto_mandatario = $comunicazione_risposta->getRichiesta()->getSoggetto();
            $denominazione = $soggetto_mandatario->getDenominazione();
            $codice = ($soggetto_mandatario instanceof ComuneUnione) ? $soggetto_mandatario->getCodiceFiscale() : $soggetto_mandatario->getPartitaIva();
            $oggetto = 'Risposta comunicazione di esito istruttoria n.' . $num_richiesta . ' ' . $denominazione . ' ' . $codice;

            $richiesta_protocollo_esi->setOggetto($oggetto);
            $richiesta_protocollo_esi->setStato('PRONTO_PER_PROTOCOLLAZIONE');

            $this->em->persist($richiesta_protocollo_esi);

            $richiesta_protocollo_doc = new RichiestaProtocolloDocumento();

            //Richiesta: documento principale
            $doc_principale = $comunicazione_risposta->getDocumentoRispostaFirmato();
            $doc_principale_path = $doc_principale->getPath();
            $doc_principale_file = $doc_principale->getNome();
            $path = $doc_principale_path . $doc_principale_file;
            $richiesta_protocollo_doc->setPath($path);
            $richiesta_protocollo_doc->setPrincipale(1);
            $richiesta_protocollo_doc->setRichiestaProtocollo($richiesta_protocollo_esi);
            $richiesta_protocollo_doc->setTabellaDocumento('risposte_comunicazioni_esiti');
            //perstist doc principale
            $this->em->persist($richiesta_protocollo_doc);

            foreach ($comunicazione_risposta->getDocumenti() as $documentoDaProtocollare) {

                $filePath = $documentoDaProtocollare->getDocumentoFile()->getPath();
                $fileName = $documentoDaProtocollare->getDocumentoFile()->getNome();
                $path = $filePath . $fileName;

                $richiestaProtocolloDocumento = new RichiestaProtocolloDocumento();
                $richiestaProtocolloDocumento->setRichiestaProtocollo($richiesta_protocollo_esi);
                $richiestaProtocolloDocumento->setTabellaDocumento('risposte_comunicazioni_esiti');
                $richiestaProtocolloDocumento->setPath($path);
                $richiestaProtocolloDocumento->setPrincipale(0);

                $this->em->persist($richiestaProtocolloDocumento);
            }
        } catch (\Exception $ex) {
            throw new SfingeException("Impossibile inizializzare le tabelle di protocollazione dopo l'invio della risposta alla comunicazione");
        }

        return $richiesta_protocollo_esi;
    }

    // documenti atc bando 8
    public function setDocumentiBando8($pagamento, $richiesta_protocollo_pag) {

        // consulenze e brevetti
        $documentiContratto = array();

        // prototipi
        $documentiPrototipo = array();

        // racchiude sia i documenti del personale, sia i documenti generali.
        // tipo['generale', 'personale']
        $documentiEstensionePagamento = array();

        // gds..non dovrebbero essercene, ma io che non so ne leggere ne scrivere lo lascio
        $documentiEstensioneGiustificativo = array();

        foreach ($pagamento->getGiustificativi() as $giustificativo) {

            // non dovrebbero essercene..non previsti
            $estensioneGiustificativo = $giustificativo->getEstensione();
            if (!is_null($estensioneGiustificativo)) {
                foreach ($estensioneGiustificativo->getDocumenti() as $documentoEstensioneGiustificativo) {
                    $documentiEstensioneGiustificativo[] = $documentoEstensioneGiustificativo->getDocumentoFile();
                }
            }

            $contratto = $giustificativo->getContratto();
            if (!is_null($contratto)) {
                foreach ($contratto->getDocumentiContratto() as $documentoContratto) {
                    $documentiContratto[] = $documentoContratto->getDocumentoFile();
                }
            }

            foreach ($giustificativo->getDocumentiPrototipo() as $documentoPrototipo) {
                $documentiPrototipo[] = $documentoPrototipo->getDocumentoFile();
            }
        }

        $estensionePagamento = $pagamento->getEstensione();
        if (!is_null($estensionePagamento)) {
            foreach ($estensionePagamento->getDocumenti() as $documentoEstensionePagamento) {
                $documentiEstensionePagamento[] = $documentoEstensionePagamento->getDocumentoFile();
            }
        }

        $documentiDaProtocollare = array_merge(
                $documentiContratto, $documentiPrototipo, $documentiEstensionePagamento, $documentiEstensioneGiustificativo);

        foreach ($documentiDaProtocollare as $documentoDaProtocollare) {

            $filePath = $documentoDaProtocollare->getPath();
            $fileName = $documentoDaProtocollare->getNome();
            $path = $filePath . $fileName;

            $richiestaProtocolloDocumento = new RichiestaProtocolloDocumento();
            $richiestaProtocolloDocumento->setRichiestaProtocollo($richiesta_protocollo_pag);
            $richiestaProtocolloDocumento->setTabellaDocumento('pagamenti');
            $richiestaProtocolloDocumento->setPath($path);
            $richiestaProtocolloDocumento->setPrincipale(0);

            $this->em->persist($richiestaProtocolloDocumento);
        }
    }

    public function setTabProtocollazioneComunicazioneProgetto($comunicazione) {
        try {

            $richiesta_protocollo = new \ProtocollazioneBundle\Entity\RichiestaProtocolloComunicazioneProgetto();

            $processo = $this->em->getRepository('ProtocollazioneBundle:Processo')->findOneBy(array('codice' => 'protocollazione_comunicazione_progetto_pa'));

            $richiesta_protocollo->setProcesso($processo);

            if ($comunicazione->getTipoOggetto() == 'RICHIESTA') {
                $richiesta_protocollo->setProcedura($comunicazione->getRichiesta()->getProcedura());
                //Creazione oggetto richiesta
                $num_richiesta = $comunicazione->getRichiesta()->getId();
                $soggetto_mandatario = $comunicazione->getRichiesta()->getSoggetto();
            }
            if ($comunicazione->getTipoOggetto() == 'VARIAZIONE') {
                $richiesta_protocollo->setProcedura($comunicazione->getVariazione()->getProcedura());
                //Creazione oggetto richiesta
                $num_richiesta = $comunicazione->getVariazione()->getRichiesta()->getId();
                $soggetto_mandatario = $comunicazione->getVariazione()->getRichiesta()->getSoggetto();
            }

            $richiesta_protocollo->setTipo('COMUNICAZIONE_PROGETTO');
            $richiesta_protocollo->setComunicazioneProgetto($comunicazione);

            $data_creazione = $comunicazione->getData();
            $data_invio = $comunicazione->getDataInvio();
            $richiesta_protocollo->setDataCreazioneRichiesta($data_creazione);
            $richiesta_protocollo->setDataInvioPA($data_invio);

            $denominazione = $soggetto_mandatario->getDenominazione();
            $codice = ($soggetto_mandatario instanceof ComuneUnione) ? $soggetto_mandatario->getCodiceFiscale() : $soggetto_mandatario->getPartitaIva();
            $oggetto = 'Comunicazione di progetto n.' . $num_richiesta . ' ' . $denominazione . ' ' . $codice;

            $richiesta_protocollo->setOggetto($oggetto);
            $richiesta_protocollo->setStato('PRONTO_PER_PROTOCOLLAZIONE');

            $this->em->persist($richiesta_protocollo);

            $richiesta_protocollo_doc = new RichiestaProtocolloDocumento();

            //Richiesta: documento principale
            $doc_principale = $comunicazione->getDocumento();
            $doc_principale_path = $doc_principale->getPath();
            $doc_principale_file = $doc_principale->getNome();
            $path = $doc_principale_path . $doc_principale_file;
            $richiesta_protocollo_doc->setPath($path);
            $richiesta_protocollo_doc->setPrincipale(1);
            $richiesta_protocollo_doc->setRichiestaProtocollo($richiesta_protocollo);
            $richiesta_protocollo_doc->setTabellaDocumento('comunicazioni_progetti');
            //perstist doc principale
            $this->em->persist($richiesta_protocollo_doc);

            foreach ($comunicazione->getDocumentiComunicazione() as $documentoDaProtocollare) {

                $filePath = $documentoDaProtocollare->getDocumentoFile()->getPath();
                $fileName = $documentoDaProtocollare->getDocumentoFile()->getNome();
                $path = $filePath . $fileName;

                $richiestaProtocolloDocumento = new RichiestaProtocolloDocumento();
                $richiestaProtocolloDocumento->setRichiestaProtocollo($richiesta_protocollo);
                $richiestaProtocolloDocumento->setTabellaDocumento('comunicazioni_progetti');
                $richiestaProtocolloDocumento->setPath($path);
                $richiestaProtocolloDocumento->setPrincipale(0);

                $this->em->persist($richiestaProtocolloDocumento);
            }
        } catch (\Exception $ex) {
            throw new SfingeException("Impossibile inizializzare le tabelle di protocollazione dopo l'invio del pagamento");
        }

        return $richiesta_protocollo;
    }

    public function setTabProtocollazioneRispostaComunicazioneProgetto($comunicazione) {
        try {

            $richiesta_protocollo = new \ProtocollazioneBundle\Entity\RichiestaProtocolloRispostaComunicazioneProgetto();

            $processo = $this->em->getRepository('ProtocollazioneBundle:Processo')->findOneBy(array('codice' => 'protocollazione_comunicazione_progetto_risposta'));

            $richiesta_protocollo->setProcesso($processo);
            $richiesta_protocollo->setProcedura($comunicazione->getRichiesta()->getProcedura());
            $richiesta_protocollo->setTipo('COMUNICAZIONE_PROGETTO_RISPOSTA');

            $richiesta_protocollo->setRispostaComunicazioneProgetto($comunicazione);

            $data_creazione = $comunicazione->getData();
            $data_invio = $comunicazione->getDataInvio();
            $richiesta_protocollo->setDataCreazioneRichiesta($data_creazione);
            $richiesta_protocollo->setDataInvioPA($data_invio);

            //Creazione oggetto richiesta
            $num_richiesta = $comunicazione->getRichiesta()->getId();
            $soggetto_mandatario = $comunicazione->getRichiesta()->getSoggetto();
            $denominazione = $soggetto_mandatario->getDenominazione();
            $codice = ($soggetto_mandatario instanceof ComuneUnione) ? $soggetto_mandatario->getCodiceFiscale() : $soggetto_mandatario->getPartitaIva();
            $oggetto = 'Comunicazione di progetto n.' . $num_richiesta . ' ' . $denominazione . ' ' . $codice;

            $richiesta_protocollo->setOggetto($oggetto);
            $richiesta_protocollo->setStato('PRONTO_PER_PROTOCOLLAZIONE');

            $this->em->persist($richiesta_protocollo);

            $richiesta_protocollo_doc = new RichiestaProtocolloDocumento();

            //Richiesta: documento principale
            $doc_principale = $comunicazione->getDocumentoRispostaFirmato();
            $doc_principale_path = $doc_principale->getPath();
            $doc_principale_file = $doc_principale->getNome();
            $path = $doc_principale_path . $doc_principale_file;
            $richiesta_protocollo_doc->setPath($path);
            $richiesta_protocollo_doc->setPrincipale(1);
            $richiesta_protocollo_doc->setRichiestaProtocollo($richiesta_protocollo);
            $richiesta_protocollo_doc->setTabellaDocumento('comunicazioni_progetti_risposte');
            //perstist doc principale
            $this->em->persist($richiesta_protocollo_doc);

            foreach ($comunicazione->getDocumenti() as $documentoDaProtocollare) {

                $filePath = $documentoDaProtocollare->getDocumentoFile()->getPath();
                $fileName = $documentoDaProtocollare->getDocumentoFile()->getNome();
                $path = $filePath . $fileName;

                $richiestaProtocolloDocumento = new RichiestaProtocolloDocumento();
                $richiestaProtocolloDocumento->setRichiestaProtocollo($richiesta_protocollo);
                $richiestaProtocolloDocumento->setTabellaDocumento('comunicazioni_progetti_risposte');
                $richiestaProtocolloDocumento->setPath($path);
                $richiestaProtocolloDocumento->setPrincipale(0);

                $this->em->persist($richiestaProtocolloDocumento);
            }
        } catch (\Exception $ex) {
            throw new SfingeException("Impossibile inizializzare le tabelle di protocollazione dopo l'invio del pagamento");
        }

        return $richiesta_protocollo;
    }

    public function setTabProtocollazioneComunicazioneAttuazione($comunicazione) {
        try {

            $richiesta_protocollo = new \ProtocollazioneBundle\Entity\RichiestaProtocolloComunicazioneAttuazione();

            $processo = $this->em->getRepository('ProtocollazioneBundle:Processo')->findOneBy(array('codice' => 'protocollazione_comunicazione_attuazione_pa'));

            $richiesta_protocollo->setProcesso($processo);

            if ($comunicazione->getTipoOggetto() == 'ATTUAZIONE') {
                $richiesta_protocollo->setProcedura($comunicazione->getRichiesta()->getProcedura());
                //Creazione oggetto richiesta
                $num_richiesta = $comunicazione->getRichiesta()->getId();
                $soggetto_mandatario = $comunicazione->getRichiesta()->getSoggetto();
            }

            $richiesta_protocollo->setTipo('COMUNICAZIONE_ATTUAZIONE');
            $richiesta_protocollo->setComunicazioneAttuazione($comunicazione);

            $data_creazione = $comunicazione->getData();
            $data_invio = $comunicazione->getDataInvio();
            $richiesta_protocollo->setDataCreazioneRichiesta($data_creazione);
            $richiesta_protocollo->setDataInvioPA($data_invio);

            $denominazione = $soggetto_mandatario->getDenominazione();
            $codice = ($soggetto_mandatario instanceof ComuneUnione) ? $soggetto_mandatario->getCodiceFiscale() : $soggetto_mandatario->getPartitaIva();
            $oggetto = 'Comunicazione attuazione n.' . $num_richiesta . ' ' . $denominazione . ' ' . $codice;

            $richiesta_protocollo->setOggetto($oggetto);
            $richiesta_protocollo->setStato('PRONTO_PER_PROTOCOLLAZIONE');

            $this->em->persist($richiesta_protocollo);

            $richiesta_protocollo_doc = new RichiestaProtocolloDocumento();

            //Richiesta: documento principale
            $doc_principale = $comunicazione->getDocumento();
            $doc_principale_path = $doc_principale->getPath();
            $doc_principale_file = $doc_principale->getNome();
            $path = $doc_principale_path . $doc_principale_file;
            $richiesta_protocollo_doc->setPath($path);
            $richiesta_protocollo_doc->setPrincipale(1);
            $richiesta_protocollo_doc->setRichiestaProtocollo($richiesta_protocollo);
            $richiesta_protocollo_doc->setTabellaDocumento('comunicazioni_attuazione');
            //perstist doc principale
            $this->em->persist($richiesta_protocollo_doc);

            foreach ($comunicazione->getDocumentiComunicazione() as $documentoDaProtocollare) {

                $filePath = $documentoDaProtocollare->getDocumentoFile()->getPath();
                $fileName = $documentoDaProtocollare->getDocumentoFile()->getNome();
                $path = $filePath . $fileName;

                $richiestaProtocolloDocumento = new RichiestaProtocolloDocumento();
                $richiestaProtocolloDocumento->setRichiestaProtocollo($richiesta_protocollo);
                $richiestaProtocolloDocumento->setTabellaDocumento('comunicazioni_attuazione');
                $richiestaProtocolloDocumento->setPath($path);
                $richiestaProtocolloDocumento->setPrincipale(0);

                $this->em->persist($richiestaProtocolloDocumento);
            }
        } catch (\Exception $ex) {
            throw new SfingeException("Impossibile inizializzare le tabelle di protocollazione dopo l'invio del pagamento");
        }

        return $richiesta_protocollo;
    }

    public function setTabProtocollazioneRispostaComunicazioneAttuazione($comunicazione) {
        try {

            $richiesta_protocollo = new \ProtocollazioneBundle\Entity\RichiestaProtocolloRispostaComunicazioneAttuazione();

            $processo = $this->em->getRepository('ProtocollazioneBundle:Processo')->findOneBy(array('codice' => 'protocollazione_comunicazione_attuazione_risposta'));

            $richiesta_protocollo->setProcesso($processo);
            $richiesta_protocollo->setProcedura($comunicazione->getRichiesta()->getProcedura());
            $richiesta_protocollo->setTipo('COMUNICAZIONE_ATTUAZIONE_RISPOSTA');

            $richiesta_protocollo->setRispostaComunicazioneAttuazione($comunicazione);

            $data_creazione = $comunicazione->getData();
            $data_invio = $comunicazione->getDataInvio();
            $richiesta_protocollo->setDataCreazioneRichiesta($data_creazione);
            $richiesta_protocollo->setDataInvioPA($data_invio);

            //Creazione oggetto richiesta
            $num_richiesta = $comunicazione->getRichiesta()->getId();
            $soggetto_mandatario = $comunicazione->getRichiesta()->getSoggetto();
            $denominazione = $soggetto_mandatario->getDenominazione();
            $codice = ($soggetto_mandatario instanceof ComuneUnione) ? $soggetto_mandatario->getCodiceFiscale() : $soggetto_mandatario->getPartitaIva();
            $oggetto = 'Comunicazione n.' . $num_richiesta . ' ' . $denominazione . ' ' . $codice;

            $richiesta_protocollo->setOggetto($oggetto);
            $richiesta_protocollo->setStato('PRONTO_PER_PROTOCOLLAZIONE');

            $this->em->persist($richiesta_protocollo);

            $richiesta_protocollo_doc = new RichiestaProtocolloDocumento();

            //Richiesta: documento principale
            $doc_principale = $comunicazione->getDocumentoRispostaFirmato();
            $doc_principale_path = $doc_principale->getPath();
            $doc_principale_file = $doc_principale->getNome();
            $path = $doc_principale_path . $doc_principale_file;
            $richiesta_protocollo_doc->setPath($path);
            $richiesta_protocollo_doc->setPrincipale(1);
            $richiesta_protocollo_doc->setRichiestaProtocollo($richiesta_protocollo);
            $richiesta_protocollo_doc->setTabellaDocumento('comunicazioni_attuazione_risposte');
            //perstist doc principale
            $this->em->persist($richiesta_protocollo_doc);

            foreach ($comunicazione->getDocumenti() as $documentoDaProtocollare) {

                $filePath = $documentoDaProtocollare->getDocumentoFile()->getPath();
                $fileName = $documentoDaProtocollare->getDocumentoFile()->getNome();
                $path = $filePath . $fileName;

                $richiestaProtocolloDocumento = new RichiestaProtocolloDocumento();
                $richiestaProtocolloDocumento->setRichiestaProtocollo($richiesta_protocollo);
                $richiestaProtocolloDocumento->setTabellaDocumento('comunicazioni_attuazione_risposte');
                $richiestaProtocolloDocumento->setPath($path);
                $richiestaProtocolloDocumento->setPrincipale(0);

                $this->em->persist($richiestaProtocolloDocumento);
            }
        } catch (\Exception $ex) {
            throw new SfingeException("Impossibile inizializzare le tabelle di protocollazione dopo l'invio della richiesta");
        }

        return $richiesta_protocollo;
    }

    /**
     * @param ComunicazionePagamento $comunicazionePagamento
     * @return \ProtocollazioneBundle\Entity\RichiestaProtocolloComunicazionePagamento
     * @throws SfingeException
     */
    public function setTabProtocollazioneComunicazionePagamento(ComunicazionePagamento $comunicazionePagamento) {
        try {
            $richiesta_protocollo_pag = new \ProtocollazioneBundle\Entity\RichiestaProtocolloComunicazionePagamento();

            // Leggo l'id del processo con codice 'protocollazione_comunicazione_pagamento'
            $processo = $this->em->getRepository('ProtocollazioneBundle:Processo')->findOneBy(array('codice' => 'protocollazione_comunicazione_pagamento'));

            $richiesta_protocollo_pag->setProcesso($processo);
            $richiesta_protocollo_pag->setProcedura($comunicazionePagamento->getPagamento()->getRichiesta()->getProcedura());
            $richiesta_protocollo_pag->setTipo('COMUNICAZIONE_PAGAMENTO');

            $richiesta_protocollo_pag->setComunicazionePagamento($comunicazionePagamento);

            // Inserimento data pagamento e data invio PA
            $data_creazione = $comunicazionePagamento->getDataCreazione();
            $data_invio = $comunicazionePagamento->getData();
            $richiesta_protocollo_pag->setDataCreazioneRichiesta($data_creazione);
            $richiesta_protocollo_pag->setDataInvioPA($data_invio);

            // Creazione oggetto richiesta
            $num_richiesta = $comunicazionePagamento->getPagamento()->getId();
            $soggetto_mandatario = $comunicazionePagamento->getPagamento()->getSoggetto();
            $denominazione = $soggetto_mandatario->getDenominazione();
            $codice = ($soggetto_mandatario instanceof ComuneUnione) ? $soggetto_mandatario->getCodiceFiscale() : $soggetto_mandatario->getPartitaIva();
            $tipologia_comunicazione = strtolower($comunicazionePagamento->getTipologiaComunicazione()->getDescrizione());
            $oggetto = 'Comunicazione di ' . $tipologia_comunicazione . ' pagamento n.' . $num_richiesta . ' ' . $denominazione . ' ' . $codice;

            $richiesta_protocollo_pag->setOggetto($oggetto);
            $richiesta_protocollo_pag->setStato('PRONTO_PER_PROTOCOLLAZIONE');

            $this->em->persist($richiesta_protocollo_pag);

            $richiesta_protocollo_doc = new RichiestaProtocolloDocumento();

            // Richiesta: documento principale
            $doc_principale = $comunicazionePagamento->getDocumento();
            $doc_principale_path = $doc_principale->getPath();
            $doc_principale_file = $doc_principale->getNome();
            $path = $doc_principale_path . $doc_principale_file;
            $richiesta_protocollo_doc->setPath($path);
            $richiesta_protocollo_doc->setPrincipale(1);
            $richiesta_protocollo_doc->setRichiestaProtocollo($richiesta_protocollo_pag);
            $richiesta_protocollo_doc->setTabellaDocumento('comunicazioni_pagamenti');

            foreach ($comunicazionePagamento->getAllegati() as $allegato) {
                $allegato_path = $allegato->getDocumento()->getPath();
                $allegato_file = $allegato->getDocumento()->getNome();
                $path = $allegato_path . $allegato_file;

                $protocolloAllegato = new RichiestaProtocolloDocumento();
                $protocolloAllegato->setRichiestaProtocollo($richiesta_protocollo_pag);
                $protocolloAllegato->setTabellaDocumento('comunicazioni_pagamenti');
                $protocolloAllegato->setPath($path);
                $protocolloAllegato->setPrincipale(0);

                $this->em->persist($protocolloAllegato);
            }

            // Persist doc principale
            $this->em->persist($richiesta_protocollo_doc);
        } catch (\Exception $ex) {
            throw new SfingeException("Impossibile inizializzare le tabelle di protocollazione dopo l'invio del pagamento");
        }

        return $richiesta_protocollo_pag;
    }

    /**
     * @param RispostaComunicazionePagamento $rispostaComunicazionePagamento
     * @return \ProtocollazioneBundle\Entity\RichiestaProtocolloRispostaComunicazionePagamento
     * @throws SfingeException
     */
    public function setTabProtocollazioneRispostaComunicazionePagamento(RispostaComunicazionePagamento $rispostaComunicazionePagamento) {
        try {
            $richiestaProtocolloRispostaComunicazionePagamento = new \ProtocollazioneBundle\Entity\RichiestaProtocolloRispostaComunicazionePagamento();

            // Leggo l'id del processo con codice 'protocollazione_risposta_comunicazione_pagamento'
            $processo = $this->em->getRepository('ProtocollazioneBundle:Processo')->findOneBy(array('codice' => 'protocollazione_risposta_comunicazione_pagamento'));
            $pagamento = $rispostaComunicazionePagamento->getComunicazione()->getPagamento();

            $richiestaProtocolloRispostaComunicazionePagamento->setProcesso($processo);
            $richiestaProtocolloRispostaComunicazionePagamento->setProcedura($pagamento->getRichiesta()->getProcedura());
            $richiestaProtocolloRispostaComunicazionePagamento->setTipo('RISPOSTA_COMUNICAZIONE_PAGAMENTO');
            $richiestaProtocolloRispostaComunicazionePagamento->setRispostaComunicazionePagamento($rispostaComunicazionePagamento);

            // Inserimento data pagamento e data invio PA
            $dataCreazione = $rispostaComunicazionePagamento->getDataCreazione();
            $dataInvio = $rispostaComunicazionePagamento->getData();
            $richiestaProtocolloRispostaComunicazionePagamento->setDataCreazioneRichiesta($dataCreazione);
            $richiestaProtocolloRispostaComunicazionePagamento->setDataInvioPA($dataInvio);

            // Creazione oggetto richiesta
            $numeroPagamento = $pagamento->getId();
            $soggettoMandatario = $pagamento->getSoggetto();
            $denominazione = $soggettoMandatario->getDenominazione();
            $codice = ($soggettoMandatario instanceof ComuneUnione) ? $soggettoMandatario->getCodiceFiscale() : $soggettoMandatario->getPartitaIva();
            $oggetto = 'Risposta alla comunicazione del pagamento n.' . $numeroPagamento . ' ' . $denominazione . ' ' . $codice;

            $richiestaProtocolloRispostaComunicazionePagamento->setOggetto($oggetto);
            $richiestaProtocolloRispostaComunicazionePagamento->setStato('PRONTO_PER_PROTOCOLLAZIONE');

            $this->em->persist($richiestaProtocolloRispostaComunicazionePagamento);

            $richiestaProtocolloDoc = new RichiestaProtocolloDocumento();

            // Richiesta: documento principale
            $docPrincipale = $rispostaComunicazionePagamento->getDocumentoRispostaFirmato();
            $docPrincipalePath = $docPrincipale->getPath();
            $docPrincipaleFile = $docPrincipale->getNome();
            $path = $docPrincipalePath . $docPrincipaleFile;
            $richiestaProtocolloDoc->setPath($path);
            $richiestaProtocolloDoc->setPrincipale(1);
            $richiestaProtocolloDoc->setRichiestaProtocollo($richiestaProtocolloRispostaComunicazionePagamento);
            $richiestaProtocolloDoc->setTabellaDocumento('risposte_comunicazioni_pagamenti');

            // Persist doc principale
            $this->em->persist($richiestaProtocolloDoc);

            $docAllegati = $rispostaComunicazionePagamento->getDocumenti();

            foreach ($docAllegati as $allegato) {
                $allegatoPath = $allegato->getDocumentoFile()->getPath();
                $allegatoFile = $allegato->getDocumentoFile()->getNome();
                $path = $allegatoPath . $allegatoFile;

                $richiestaProtocolloDoc = new RichiestaProtocolloDocumento();
                $richiestaProtocolloDoc->setRichiestaProtocollo($richiestaProtocolloRispostaComunicazionePagamento);
                $richiestaProtocolloDoc->setTabellaDocumento('risposte_comunicazioni_pagamenti');
                $richiestaProtocolloDoc->setPath($path);
                $richiestaProtocolloDoc->setPrincipale(0);

                $this->em->persist($richiestaProtocolloDoc);
            }
        } catch (\Exception $ex) {
            throw new SfingeException("Impossibile inizializzare le tabelle di protocollazione dopo l'invio del pagamento");
        }

        return $richiestaProtocolloRispostaComunicazionePagamento;
    }

    public function setTabProtocollazioneLottiPagamento($pagamento) {
        try {
            $parametro_lotto_documenti = $this->em->getRepository("SfingeBundle:ParametroSistema")->findOneByCodice('NUM_DOC_PROTOCOLLAZIONE');
            $parametro_lotto_documenti = $parametro_lotto_documenti->getValore();

            if ($pagamento->isProceduraParticolare() == false) {
                //Richiesta: documento principale
                $doc_principale = $pagamento->getDocumentoPagamentoFirmato();
                if (!$pagamento->getProcedura()->getRendicontazioneProceduraConfig()->isRichiestaFirmaDigitale()) {
                    $doc_principale = $pagamento->getDocumentoPagamento();
                }
                //Inizializzazione array documenti
                $documenti_da_processare = array();
                //Richiesta: documenti allegati
                $allegati = $pagamento->getDocumentiPagamento();
                foreach ($allegati as $doc_allegato) {
                    // Escludo dalla protocollazione il video del pagamento
                    if ($doc_allegato->getDocumentoFile()->getTipologiaDocumento()->getCodice() == 'VIDEO_PAGAMENTO') {
                        continue;
                    }
                    $documenti_da_processare[] = $doc_allegato->getDocumentoFile();
                }

                //doc giustificativi
                if ($pagamento->getProcedura()->getId() == 7 || $pagamento->getProcedura()->getId() == 8 || $pagamento->getProcedura()->getId() == 32) {
                    foreach ($pagamento->getGiustificativi() as $giustificativo) {
                        foreach ($giustificativo->getDocumentiGiustificativo() as $docGiustificativo) {
                            $documenti_da_processare[] = $docGiustificativo->getDocumentoFile();
                        }
                    }
                } else {
                    foreach ($pagamento->getGiustificativi() as $giustificativo) {
                        $tipologia = $giustificativo->getTipologiaGiustificativo();
                        if (!is_null($tipologia) && $tipologia->isInvisibile() == true) {
                            continue;
                        }

                        $documentoGiustificativo = $giustificativo->getDocumentoGiustificativo();
                        // per bando legge 14 è stato deciso di rendere opzionale il caricamento del documento principale del giustificativo
                        if (!is_null($documentoGiustificativo)) {
                            $documenti_da_processare[] = $documentoGiustificativo;
                        }

                        foreach ($giustificativo->getDocumentiGiustificativo() as $docGiustificativo) {
                            $documenti_da_processare[] = $docGiustificativo->getDocumentoFile();
                        }
                    }
                }
                //doc quietanze
                foreach ($pagamento->getGiustificativi() as $giustificativo) {
                    foreach ($giustificativo->getQuietanze() as $quietanza) {
                        $documenti_da_processare[] = $quietanza->getDocumentoQuietanza();
                    }
                }

                //doc contratti 
                foreach ($pagamento->getContratti() as $contratto) {
                    foreach ($contratto->getDocumentiContratto() as $documentoContratto) {
                        $documenti_da_processare[] = $documentoContratto->getDocumentoFile();
                    }
                }


                $num_totale_documenti = count($documenti_da_processare);
                $num_doc_corrente = 0;
                $richiesta_protocollo_pag_precedente = false;

                if ($num_totale_documenti == 0) {
                    $richiesta_protocollo_pag = $this->creaRichiestaProtocolloPagamento($pagamento);
                    $this->em->persist($richiesta_protocollo_pag);

                    //Richiesta: documento principale
                    $doc_principale_path = $doc_principale->getPath();
                    $doc_principale_file = $doc_principale->getNome();
                    $path = $doc_principale_path . $doc_principale_file;
                    $richiesta_protocollo_doc = $this->creaRichiestaProtocolloDoc($path, 1);
                    //persist doc principale
                    $this->em->persist($richiesta_protocollo_doc);
                } else {

                    while ($num_doc_corrente < $num_totale_documenti) {

                        $richiesta_protocollo_pag = $this->creaRichiestaProtocolloPagamento($pagamento);
                        if ($num_totale_documenti / $parametro_lotto_documenti > 1) {
                            $richiesta_protocollo_pag->setSuddivisioneInLotti(true);
                        }
                        if ($richiesta_protocollo_pag_precedente != false) {
                            $richiesta_protocollo_pag->setRichiestaProtocolloPagamentoPrecedente($richiesta_protocollo_pag_precedente);
                        }
                        $this->em->persist($richiesta_protocollo_pag);

                        //Richiesta: documento principale
                        $doc_principale_path = $doc_principale->getPath();
                        $doc_principale_file = $doc_principale->getNome();
                        $path = $doc_principale_path . $doc_principale_file;
                        $richiesta_protocollo_doc = $this->creaRichiestaProtocolloDoc($richiesta_protocollo_pag, $path, 1);
                        //persist doc principale
                        $this->em->persist($richiesta_protocollo_doc);

                        $i = 0;
                        while ($i < $parametro_lotto_documenti && $num_doc_corrente < $num_totale_documenti) {
                            //                    for($i=0; $i < $parametro_lotto_documenti && $num_doc_corrente < $num_totale_documenti; $i++){
                            $doc_path = $documenti_da_processare[$num_doc_corrente]->getPath();
                            $doc_file = $documenti_da_processare[$num_doc_corrente]->getNome();
                            $path_documento_altro = $doc_path . $doc_file;
                            $richiesta_protocollo_documento_altro = $this->creaRichiestaProtocolloDoc($richiesta_protocollo_pag, $path_documento_altro, 0);
                            $this->em->persist($richiesta_protocollo_documento_altro);
                            $i++;
                            $num_doc_corrente++;
                        }
                        $richiesta_protocollo_pag_precedente = $richiesta_protocollo_pag;
                    }
                }
            } else {
                $richiesta_protocollo_pag = $this->creaRichiestaProtocolloPagamento($pagamento);
                $this->em->persist($richiesta_protocollo_pag);
            }
        } catch (\Exception $ex) {
            throw new SfingeException("Impossibile inizializzare le tabelle di protocollazione dopo l'invio del pagamento");
        }
    }

    public function creaRichiestaProtocolloPagamento($pagamento) {
        /* Popolamento tabelle protocollazione
         * - richieste_protocollo
         * - richieste_protocollo_documenti
         */
        $richiesta_protocollo_pag = new RichiestaProtocolloPagamento();

        //Leggo l'id del processo con codice 'protocollazione_domande_contributo'
        $processo = $this->em->getRepository('ProtocollazioneBundle:Processo')->findOneBy(array('codice' => 'protocollazione_pagamenti'));
        $richiesta_protocollo_pag->setProcesso($processo);
        $richiesta_protocollo_pag->setProcedura($pagamento->getRichiesta()->getProcedura());
        $richiesta_protocollo_pag->setTipo('PAGAMENTO');
        $richiesta_protocollo_pag->setPagamento($pagamento);

        //Inserimento data paagemnto e data invio PA
        $data_creazione = $pagamento->getDataCreazione();
        $data_invio = $pagamento->getDataInvio();
        $richiesta_protocollo_pag->setDataCreazioneRichiesta($data_creazione);
        $richiesta_protocollo_pag->setDataInvioPA($data_invio);

        //Creazione oggetto richiesta
        $num_richiesta = $pagamento->getId();
        $soggetto_mandatario = $pagamento->getSoggetto();
        $denominazione = $soggetto_mandatario->getDenominazione();
        $codice = ($soggetto_mandatario instanceof ComuneUnione) ? $soggetto_mandatario->getCodiceFiscale() : $soggetto_mandatario->getPartitaIva();
        $oggetto = 'Domanda di pagamento n.' . $num_richiesta . ' ' . $denominazione . ' ' . $codice;

        $richiesta_protocollo_pag->setOggetto($oggetto);
        if ($pagamento->isProceduraParticolare() == true) {
            $richiesta_protocollo_pag->setStato('POST_PROTOCOLLAZIONE');
            $richiesta_protocollo_pag->setFase(0);
            $richiesta_protocollo_pag->setEsitoFase(1);
        } else {
            $richiesta_protocollo_pag->setStato('PRONTO_PER_PROTOCOLLAZIONE');
        }
        return $richiesta_protocollo_pag;
    }

    public function creaRichiestaProtocolloDoc($richiesta_protocollo_pag, $path, $principale = 0) {
        $richiesta_protocollo_doc = new RichiestaProtocolloDocumento();
        $richiesta_protocollo_doc->setPath($path);
        $richiesta_protocollo_doc->setPrincipale($principale);
        $richiesta_protocollo_doc->setRichiestaProtocollo($richiesta_protocollo_pag);
        $richiesta_protocollo_doc->setTabellaDocumento('pagamenti');
        return $richiesta_protocollo_doc;
    }

}
