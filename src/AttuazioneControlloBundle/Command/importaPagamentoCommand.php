<?php

namespace AttuazioneControlloBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AttuazioneControlloBundle\Entity\Pagamento;
use AttuazioneControlloBundle\Entity\GiustificativoPagamento;
use AttuazioneControlloBundle\Entity\QuietanzaGiustificativo;
use AttuazioneControlloBundle\Entity\Contratto;
use AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo;
use AttuazioneControlloBundle\Entity\ProceduraAggiudicazione;
use AttuazioneControlloBundle\Entity\RichiestaImpegni;
use Swaggest\JsonSchema\Schema;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use AttuazioneControlloBundle\Entity\DocumentoGiustificativo;
use AttuazioneControlloBundle\Entity\DocumentoContratto;
use AttuazioneControlloBundle\Entity\DocumentoPagamento;
use AttuazioneControlloBundle\Entity\ImpegniAmmessi;
use AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;
use Doctrine\ORM\EntityManagerInterface;
use RichiesteBundle\Entity\Richiesta;

/**
 * @author vdamico
 */
class importaPagamentoCommand extends ContainerAwareCommand {

    const LOG_ERROR_CODE = 'ERROR';
    //sotto cartelle per comodità 
    const FATTURE = 'fatture';
    const GIUSTIFICATIVI = 'giustificativi';
    const QUIETANZE = 'mandati';
    const PAGAMENTI = 'pagamenti';
    const CONTRATTI = 'contratti';

    private $errors = array();

    /** @var ValidatorInterface */
    private $validator;

    /** @var EntityManagerInterface */
    private $em;
    private $path;
    private $root;
    private $extractedPath;
    private $zipFile;
    private $id_richiesta;
    private $verificaImporti;
    private $logAttivo;
    private $soloValidazione;
    private $visulizzaErroreFile;

    public function __construct($name = null) {
        parent::__construct($name);
        //locale
        //$this->path = "/Users/vdamico/Sites/Covidsanita/";
        //test e prod
        $this->path = "/mnt/Covidsanita/";
        $this->verificaImporti = false;
        $this->logAttivo = true;
        $this->soloValidazione = true;
        $this->visulizzaErroreFile = true;
    }

    protected function configure() {
        $this->setName('pagamenti:importaPagamento')->setDescription('');
        $this->addArgument('id_richiesta', InputArgument::REQUIRED, 'tipo di risorsa da trasmettere');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->id_richiesta = $input->getArgument('id_richiesta');
        $this->validator = $this->getContainer()->get('validator');
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->zipFile = $this->path . $this->id_richiesta . '/IMPORT/importazione_' . $this->id_richiesta . '.zip';
        $this->extractedPath = $this->path . $this->id_richiesta . '/IMPORT/importazione_' . $this->id_richiesta;
        $zip = new \ZipArchive();
        $res = $zip->open($this->zipFile);

        $validazioneParam = $this->em->getRepository("SfingeBundle:ParametroSistema")->findOneByCodice('VALIDAZIONE_IMPORTAZIONE');
        $this->soloValidazione = $validazioneParam->getValore() == 'true';

        if ($this->soloValidazione == true) {
            $output->writeln("<comment>********** SOLO VALIDAZIONE **********</comment>");
        } else {
            $output->writeln("<comment>********** VALIDAZIONE E SCRITTURA  **********</comment>");
        }
        if ($res === TRUE) {
            $resImport = $this->importa($output);
            $this->cancellaCartella($this->extractedPath);
            //Togliere il commento se si vuole escludere la scrittura dei log a DB (log service)
            //$this->logAttivo = false;
            if ($this->logAttivo == true && !$resImport) {
                foreach ($this->errors as $er) {
                    $this->em->beginTransaction();
                    $this->createLog($this->protocollo->getId(), self::LOG_ERROR_CODE, $er, 'ImportazioneSanitaID:' . $this->id_richiesta);
                    $this->em->commit();
                }
            }
        } else {
            $output->writeln("<error>Impossibile aprire il file zip {$res}</error>");
        }

        $output->writeln("<comment>********** Fine Procedura di procedura di importazione **********</comment>");
    }

    private function importa(OutputInterface $output) {
        // richiesta 26612
        //togliere il commento sotto se si skippare il controllo di coerenza degli importi
        //$this->verificaImporti = false;
        //togliere il commento sotto se si vuole solo validare
        //$this->soloValidazione = true;
        /** @var Richiesta */
        $richiesta = $this->em->getRepository("RichiesteBundle\Entity\Richiesta")->find($this->id_richiesta);
        $this->protocollo = $richiesta->getRichiesteProtocollo()->first();
        $atc = $richiesta->getAttuazioneControllo();
        $zip = new \ZipArchive();
        $zip->open($this->zipFile);
        $zip->extractTo($this->extractedPath);
        $zip->close();
        $strJsonFileContents = file_get_contents($this->extractedPath . '/importazione_' . $this->id_richiesta . '.json');
        $array = json_decode($strJsonFileContents, true);
        //$schemaValidation = Schema::import(dirname(__FILE__) . '/../Resources/JSON/schema.json');
        //$aLuiPiaceCosi = json_decode($strJsonFileContents, false);
        //$schemaValidation->in($aLuiPiaceCosi);
        try {
            $this->em->beginTransaction();
            try {
                $output->writeln("<comment>********** Inizio Procedura di procedura di importazione **********</comment>");
                $pagamento = $this->creaPagamento($atc, $array);
                foreach ($array['contratti'] as $contratto) {
                    try {
                        $contrattoObject = $this->creaContratto($pagamento, $contratto);
                        $impegno = $this->creaImpegno($pagamento, $contratto);
                        $richiesta->addMonImpegni($impegno);

                        $this->effettuaValidazioneImpegno($impegno);

                        foreach ($contratto['giustificativi'] as $giustificativo) {
                            try {
                                $giustificativoObject = $this->creaGiustificativo($pagamento, $contrattoObject, $giustificativo);
                                foreach ($giustificativo['imputazioni'] as $voce) {
                                    try {
                                        $this->creaVoceCostoGiustificativo($pagamento, $giustificativoObject, $voce);
                                    } catch (\Exception $e) {
                                        $this->errors[] = $e->getMessage();
                                        continue;
                                    }
                                }
                                foreach ($giustificativo['quietanze'] as $quietanza) {
                                    try {
                                        $this->creaQuietanza($giustificativoObject, $quietanza);
                                    } catch (\Exception $e) {
                                        $this->errors[] = $e->getMessage();
                                        continue;
                                    }
                                }
                                if ($this->verificaImporti == true && !$giustificativoObject->isRichiestoEqualImputato()) {
                                    throw new \Exception('creaGiustificativo: L\'importo totale delle imputazioni non coincide con quello richiesto per il giustificaitivo numero: '
                                            . $giustificativoObject->getNumeroGiustificativo() . ' Tolale imputazioni = ' . $giustificativoObject->getTotaleImputato() . ' Importo richiesto = ' . $giustificativoObject->getImportoRichiesto());
                                }
                            } catch (\Exception $e) {
                                $this->errors[] = $e->getMessage();
                                continue;
                            }
                        }
                        /* if ($this->verificaImporti == true && !$contrattoObject->isComplessivoEqualGiustificativi()) {
                          throw new \Exception('creaContratto: L\'importo totale deli giustificativi non coincide con quello del contratto numero: ' . $contrattoObject->getNumero());
                          } */
                    } catch (\Exception $e) {
                        $this->errors[] = $e->getMessage();
                        continue;
                    }
                }
                foreach ($array['procedure_aggiudicazione'] as $aggiudicazione) {
                    try {
                        $proceduraAggiudicazione = $this->creaProceduraAggiudicazione($richiesta, $aggiudicazione);
                        $richiesta->addMonProcedureAggiudicazione($proceduraAggiudicazione);
                    } catch (\Exception $e) {
                        $this->errors[] = $e->getMessage();
                        continue;
                    }
                    $this->effettuaValidazioneProceduraAggiudicazione($proceduraAggiudicazione);
                }
                if ($this->verificaImporti == true && !$pagamento->isComplessivoEqualGiustificativi()) {
                    throw new \Exception('creaPagamento: L\'importo totale deli giustificativi non coincide con quello del pagamento: importo pagamento = ' . $pagamento->getImportoRichiesto() . 'importo giustificativi = ' . $pagamento->getRendicontato());
                }
            } catch (\Exception $e) {
                $this->errors[] = $e->getMessage();
            }
            if (count($this->errors) > 0) {
                throw new \Exception('Ci sono errori nel file json, impossibile completare l\'importazione ');
            }

            if ($this->soloValidazione == true) {
                $output->writeln("Il file di importazione risulta valido.");
            } else {
                $this->em->flush();
                $this->em->commit();
                $output->writeln("Il file pagamento è stato importato con successo.");
            }
            return true;
        } catch (\Exception $e) {
            $this->em->rollback();
            $this->em->clear();
            $output->writeln("<error>Si è verificato un errore {$e->getMessage()}</error>");
            foreach ($this->errors as $er) {
                $output->writeln("<error>$er</error>");
            }
            return false;
        }
    }

    private function creaPagamento($atc, $array): Pagamento {
        $pagamento = new Pagamento();
        $pagamento->setAttuazioneControlloRichiesta($atc);
        $this->aggiungiGiustificativiConImportiDaRipresentare($pagamento);
        $pagamento->setImportoRichiesto($array['importo_richiesto']);
        $pagamento->setImportoRendicontato($array['importo_richiesto']);
        $pagamento->setDataInizioRendicontazione($this->valoreVuoto($array['data_inizio_rendicontazione']) ? null : new \DateTime($array['data_inizio_rendicontazione']));
        $pagamento->setDataFineRendicontazione($this->valoreVuoto($array['data_fine_rendicontazione']) ? null : new \DateTime($array['data_fine_rendicontazione']));
        $pagamento->setDataConvenzione($this->valoreVuoto($array['data_convenzione']) ? null : new \DateTime($array['data_convenzione']));
        $pagamento->setImportoRendicontato($array['importo_rendicontato']);
        $pagamento->setAbilitaRendicontazioneChiusa(false);
        $pagamento->setModalitaPagamento($this->em->getRepository("AttuazioneControlloBundle\Entity\ModalitaPagamento")->find(4));
        $pagamento->setStato($this->em->getRepository("AttuazioneControlloBundle\Entity\StatoPagamento")->find(6));
        //carico i documenti del pagamento
        $docs = $this->caricaDocumentiPagamento($array['documenti'], $pagamento->getRichiesta(), $pagamento);
        $pagamento->setDocumentiPagamento($docs);
        $this->effettuaValidazionePagamento($pagamento);
        $this->em->persist($pagamento);
        return $pagamento;
    }

    public function effettuaValidazionePagamento(Pagamento $pagamento) {
        $container = $this->getContainer();
        /** @var ValidatorInterface */
        $validator = $container->get('validator');
        $constraints = $this->getConstraints();
        //$container->getParameter('importazione_pagamenti.validators');
        $result = $validator->validate($pagamento, $constraints, ['Default']);
        foreach ($result as $errore) {
            $this->errors[] = $errore->getMessage();
        }
    }

    private function creaContratto($pagamento, $array) {
        $contratto = new Contratto();
        $tipoF = $this->em->getRepository("AttuazioneControlloBundle\Entity\TipologiaFornitore")->findOneByCodice($array['tipologia_fornitore']);
        $tipoS = $this->em->getRepository("AttuazioneControlloBundle\Entity\TipologiaSpesa")->findOneByCodice($array['tipo_contratto']);
        $tipoA = $this->em->getRepository("AttuazioneControlloBundle\Entity\TipologiaStazioneAppaltante")->findOneByCodice($array['stazione_appaltante']);
        if (is_null($tipoF)) {
            $this->generaErrore('creaContratto: Non esiste una tipologia_fornitore con codice ' . $array['tipologia_fornitore']);
        }
        if (!$this->soloValidazione && is_null($tipoS)) {
            $this->generaErrore('creaContratto: Non esiste un tipo_contratto con codice ' . $array['tipo_contratto']);
        }
        if (!$this->soloValidazione && is_null($tipoA)) {
            $this->generaErrore('creaContratto: Non esiste una stazione_appaltante con codice ' . $array['stazione_appaltante']);
        }

        $contratto->setTipologiaFornitore($tipoF);
        $contratto->setTipologiaSpesa($tipoS);
        $contratto->setTipologiaStazioneAppaltante($tipoA);
        $contratto->setDataInizio($this->valoreVuoto($array['data_inizio']) ? null : new \DateTime($array['data_inizio']));
        $contratto->setDataContratto($this->valoreVuoto($array['data_contratto']) ? null : new \DateTime($array['data_contratto']));
        $contratto->setDescrizione($array['descrizione']);
        $contratto->setFornitore($array['fornitore']);
        $contratto->setNumero($array['numero']);
        $contratto->setBeneficiario($array['beneficiario_contratto']);
        $contratto->setPiattaformaCommittenza($array['piattaforma_committenza']);
        if (array_key_exists("altro_stazione_appaltante", $array)) {
            $contratto->setAltroStazioneAppaltante($array['altro_stazione_appaltante']);
        }
        $contratto->setImportoContrattoComplessivo($array['importo_contratto_complessivo']);
        $contratto->setImportoContrattoComplessivoIvato($array['importo_contratto_complessivo_ivato']);
        $contratto->setPagamento($pagamento);
        //carico i documenti aggiuntivi del giustificativo
        $docs = $this->caricaDocumentiContratto($array['documenti'], $pagamento->getRichiesta(), $contratto);
        $contratto->setDocumentiContratto($docs);
        $contratto->setProvvedimentoAvvioProcedimento($array['provvedimento_avvio_procedimento']);
        $contratto->setNumAttoAggiudicazione($array['num_atto_aggiudicazione']);
        $contratto->setTipologiaAttoAggiudicazione($array['tipologia_atto_aggiudicazione']);
        $contratto->setDataAttoAggiudicazione($this->valoreVuoto($array['data_atto_aggiudicazione']) ? null : new \DateTime($array['data_atto_aggiudicazione']));

        $this->effettuaValidazioneContratto($contratto);
        $this->em->persist($contratto);
        return $contratto;
    }

    public function effettuaValidazioneContratto(Contratto $contratto) {
        $container = $this->getContainer();
        /** @var ValidatorInterface */
        $validator = $container->get('validator');
        $constraints = $this->getConstraints();
        $result = $validator->validate($contratto, $constraints, ['Default']);
        foreach ($result as $errore) {
            $this->errors[] = $errore->getMessage();
        }
    }

    private function creaGiustificativo($pagamento, $contratto, $array) {
        $giustificativo = new GiustificativoPagamento();
        $giustificativo->setPagamento($pagamento);
        $pagamento->addGiustificativi($giustificativo);
        $giustificativo->setContratto($contratto);
        $contratto->addGiustificativiPagamento($giustificativo);
        $giustificativo->setDenominazioneFornitore($array['denominazione_fornitore']);
        $giustificativo->setCodiceFiscaleFornitore($array['codice_fiscale_fornitore']);
        $giustificativo->setDescrizioneGiustificativo($array['descrizione_giustificativo']);
        $giustificativo->setNumeroGiustificativo($array['numero_giustificativo']);
        $giustificativo->setDataGiustificativo($this->valoreVuoto($array['data_giustificativo']) ? null : new \DateTime($array['data_giustificativo']));
        $giustificativo->setDataConsegna($this->valoreVuoto($array['data_consegna']) ? null : new \DateTime($array['data_consegna']));
        $giustificativo->setLuogoConsegna($array['luogo_consegna']);
        $giustificativo->setImportoGiustificativo($array['importo_giustificativo']);
        $giustificativo->setImportoRichiesto($array['importo_richiesto']);
        $giustificativo->setNotaBeneficiario($array['nota_beneficiario']);
        $tipo = $this->em->getRepository("AttuazioneControlloBundle\Entity\TipologiaGiustificativo")->findOneByCodice($array['tipologia_giustificativo']);
        if (is_null($tipo)) {
            $this->generaErrore('creaGiustificativo: Non esiste una tipologia_giustificativo con codice ' . $array['tipologia_giustificativo']);
        }
        $giustificativo->setTipologiaGiustificativo($tipo);

        //GIUSTIFICATIVO tipologia documento fattura
        //carico il documento principale del giustificativo
        $nomeFile = $array['documento_giustificativo'];
        $documento_giustificativo = $this->caricaDocumentoGiustificativo($nomeFile, 'GIUSTIFICATIVO', $pagamento->getRichiesta());
        $giustificativo->setDocumentoGiustificativo($documento_giustificativo);
        //carico i documenti aggiuntivi del giustificativo
        $docs = $this->caricaDocumentiGiustificativo($array['documenti_aggiuntivi'], $pagamento->getRichiesta(), $giustificativo);
        $giustificativo->setDocumentiGiustificativo($docs);
        $this->effettuaValidazioneGiustificativo($giustificativo);
        $this->em->persist($giustificativo);
        return $giustificativo;
    }

    public function effettuaValidazioneGiustificativo(GiustificativoPagamento $giustificativo) {
        $container = $this->getContainer();
        /** @var ValidatorInterface */
        $validator = $container->get('validator');
        $constraints = $this->getConstraints();
        $result = $validator->validate($giustificativo, $constraints, ['Default']);
        foreach ($result as $errore) {
            $this->errors[] = $errore->getMessage();
        }
    }

    private function creaVoceCostoGiustificativo($pagamento, $giustificativo, $array) {
        $voce = new VocePianoCostoGiustificativo();
        $voce->setGiustificativoPagamento($giustificativo);
        $giustificativo->addVocePianoCosto($voce);
        $id_proponente = $pagamento->getRichiesta()->getMandatario()->getId();
        //$vpc = $this->em->getRepository("RichiesteBundle\Entity\VocePianoCosto")->getVoceDaProponenteCodiceSezioneCodice($id_proponente, 'A_140', $array['voce_spesa']);
        $vpc = $this->em->getRepository("RichiesteBundle\Entity\VocePianoCosto")->getVoceDaProponenteCodiceSezioneCodice($id_proponente, 'A_165', $array['voce_spesa']);
        if (is_null($vpc)) {
            $this->generaErrore('creaVoceCostoGiustificativo: Non esiste una voce_spesa con codice ' . $array['voce_spesa']);
        }
        $voce->setVocePianoCosto($vpc);
        if (!is_null($vpc)) {
            $voce->setVocePianoCostoIstruttoria($vpc->getIstruttoria());
        }
        $voce->setImporto($array['importo']);
        $voce->setAnnualita(1);
        $this->effettuaValidazioneVoce($voce);
        $this->em->persist($voce);
        return $voce;
    }

    public function effettuaValidazioneVoce(VocePianoCostoGiustificativo $voce) {
        $container = $this->getContainer();
        /** @var ValidatorInterface */
        $validator = $container->get('validator');
        $constraints = $this->getConstraints();
        $result = $validator->validate($voce, $constraints, ['Default']);
        foreach ($result as $errore) {
            $this->errors[] = $errore->getMessage();
        }
    }

    private function creaQuietanza($giustificativo, $array) {
        $quietanza = new QuietanzaGiustificativo();
        $tipo = $this->em->getRepository("AttuazioneControlloBundle\Entity\TipologiaQuietanza")->findOneByCodice($array['tipologia']);
        if (is_null($tipo)) {
            $this->generaErrore('creaQuietanza: Non esiste una tipologia quietanza con codice ' . $array['tipologia']);
        }
        $quietanza->setTipologiaQuietanza($tipo);
        $quietanza->setGiustificativoPagamento($giustificativo);
        $quietanza->setNumero($array['numero']);
        $quietanza->setDataQuietanza($this->valoreVuoto($array['data_quietanza']) ? null : new \DateTime($array['data_quietanza']));
        $quietanza->setDataValuta($this->valoreVuoto($array['data_quietanza']) ? null : new \DateTime($array['data_quietanza']));
        $quietanza->setImporto($array['importo']);
        $quietanza->setDataAvvenutaEsecuzione($this->valoreVuoto($array['data_avvenuta_esecuzione']) ? null : new \DateTime($array['data_avvenuta_esecuzione']));
        $quietanza->setImportoMandato($array['importo_mandato']);
        //carico il documento principale del giustificativo
        //QUIETANZA
        $nomeFile = $array['documento'];
        $documento = $this->caricaDocumentoQuietanza($nomeFile, 'QUIETANZA', $giustificativo->getPagamento()->getRichiesta());
        $quietanza->setDocumentoQuietanza($documento);
        $this->em->persist($quietanza);
        return $quietanza;
    }

    public function effettuaValidazioneQuietanza(QuietanzaGiustificativo $quietanza) {
        $container = $this->getContainer();
        /** @var ValidatorInterface */
        $validator = $container->get('validator');
        $constraints = $this->getConstraints();
        $result = $validator->validate($quietanza, $constraints, ['Default']);
        foreach ($result as $errore) {
            $this->errors[] = $errore->getMessage();
        }
    }

    private function creaProceduraAggiudicazione(Richiesta $richiesta, $array): ProceduraAggiudicazione {
        $aggiudicazione = new ProceduraAggiudicazione();
        $aggiudicazione->setRichiesta($richiesta);
        if ((array_key_exists('cig', $array) && $array['cig'] != '' && $array['cig'] != NULL)) {
            $aggiudicazione->setCig($array['cig']);
        } else {
            $aggiudicazione->setCig('9999');
            $motivo = $this->em->getRepository("MonitoraggioBundle\Entity\TC22MotivoAssenzaCIG")->findOneBy(array('motivo_assenza_cig' => $array['motivo_assenza_cig']));
            $tipo = $this->em->getRepository("MonitoraggioBundle\Entity\TC23TipoProceduraAggiudicazione")->findOneBy(array('tipo_proc_agg' => $array['tipo']));
            if (is_null($motivo)) {
                $this->generaErrore('creaProceduraAggiudicazione: Non esiste un motivo_assenza_ciga con codice ' . $array['motivo_assenza_cig']);
            }
            if (is_null($tipo)) {
                $this->generaErrore('creaProceduraAggiudicazione: Non esiste un tipo con codice ' . $array['tipo']);
            }

            $aggiudicazione->setMotivoAssenzaCig($motivo);
            $aggiudicazione->setTipoProceduraAggiudicazione($tipo);
            $aggiudicazione->setImportoProceduraAggiudicazione($array['importo_procedura']);
            $aggiudicazione->setDataPubblicazione($this->valoreVuoto($array['data_pubblicazione']) ? null : new \DateTime($array['data_pubblicazione']));
            $aggiudicazione->setImportoAggiudicato($array['importo_aggiudicato']);
            $aggiudicazione->setDataAggiudicazione($this->valoreVuoto($array['data_aggiudicazione']) ? null : new \DateTime($array['data_aggiudicazione']));
            $aggiudicazione->setDescrizioneProceduraAggiudicazione($array['descrizione']);
        }
        $this->em->persist($aggiudicazione);
        return $aggiudicazione;
    }

    private function effettuaValidazioneProceduraAggiudicazione(ProceduraAggiudicazione $procedura): void {
//        $errors = $this->validator->validate($procedura);
//        $this->addErrorMessages($errors);
        $container = $this->getContainer();
        /** @var ValidatorInterface */
        $validator = $container->get('validator');
        $constraints = $this->getConstraints();
        $result = $validator->validate($procedura, $constraints, ['Default']);
        foreach ($result as $errore) {
            $this->errors[] = $errore->getMessage();
        }
    }

    private function creaImpegno(Pagamento $pagamento, $array): RichiestaImpegni {
        $impegno = new RichiestaImpegni();
        $richiesta = $pagamento->getRichiesta();
        $impegno->setRichiesta($richiesta);
        $impegno->setTipologiaImpegno('I');
        $impegno->setDataImpegno($this->valoreVuoto($array['data_contratto']) ? null : new \DateTime($array['data_contratto']));
        $impegno->setImportoImpegno($array['importo_contratto_complessivo']);
        $impegno->setNoteImpegno($array['descrizione']);
        $impegno->setCodice($impegno->calcolaCodice());
        $this->em->persist($impegno);

        /** @var RichiestaProgramma */
        $richiestaProgramma = $richiesta->getMonProgrammi()->first();
        /** @var RichiestaLivelloGerarchico */
        $richiestaLivelloGerarchico = $richiestaProgramma->getLivelliGerarchiciObiettivoSpecifico()->first();
        $impegnoAmmesso = new ImpegniAmmessi($impegno, $richiestaLivelloGerarchico);
        $this->em->persist($impegnoAmmesso);
        $richiestaLivelloGerarchico->addImpegniAmmessi($impegnoAmmesso);

        return $impegno;
    }

    private function effettuaValidazioneImpegno(RichiestaImpegni $impegno): void {
        $errors = $this->validator->validate($impegno);
        $this->addErrorMessages($errors);
    }

    private function addErrorMessages(ConstraintViolationListInterface $violations): void {
        foreach ($violations as $violation) {
            $this->errors[] = $violation->getMessage();
        }
    }

    //carica il documento associato al giustificativo
    private function caricaDocumentoGiustificativo($nomeFile, $codice_tipologia_documento, $richiesta) {
        $service = $this->getContainer()->get('documenti');
        $filePathname = $this->extractedPath . '/' . self::FATTURE . '/' . $nomeFile;
        try {
            $documento_file = $service->caricaDaFileImportazione($filePathname, $codice_tipologia_documento, false, null, false, $richiesta, !$this->soloValidazione);
        } catch (\Exception $e) {
            if ($this->visulizzaErroreFile) {
                $this->generaErrore('Impossibile caricare il file ' . $nomeFile . ', nome file errato o file non valido (' . $e->getMessage() . ")");
            }
            return null;
        }
        return $documento_file;
    }

    //carica il documento associato alla quietanza
    private function caricaDocumentoQuietanza($nomeFile, $codice_tipologia_documento, $richiesta) {
        $service = $this->getContainer()->get('documenti');
        $filePathname = $this->extractedPath . '/' . self::QUIETANZE . '/' . $nomeFile;
        try {
            $documento_file = $service->caricaDaFileImportazione($filePathname, $codice_tipologia_documento, false, null, false, $richiesta, !$this->soloValidazione);
        } catch (\Exception $e) {
            if ($this->visulizzaErroreFile) {
                $this->generaErrore('Impossibile caricare il file ' . $nomeFile . ', nome file errato o file non valido (' . $e->getMessage() . ")");
            }
            return null;
        }
        return $documento_file;
    }

    //carica eventuali documenti aggiuntivi al giustificaitvo
    private function caricaDocumentiGiustificativo($arrayFile, $richiesta, $giustificativo) {
        $arrayTipiAmmessi = array("dg2_bando165", "dg3_bando165", "dg4_bando165", "dg6_bando165", "dg7_bando165", "dg8_bando165", "dg9_bando165",
            "dg10_bando165", "dg11_bando165", "dg12_bando165", "dg13_bando165", "dg14_bando165");
        $service = $this->getContainer()->get('documenti');
        $arrayDocCaricati = array();
        foreach ($arrayFile as $file) {
            if (!in_array($file['tipologia'], $arrayTipiAmmessi)) {
                $this->errors[] = 'Impossibile caricare il file ' . $file['nome_file'] . ', tipologia ' . $file['tipologia'] . ' non ammessa ';
                continue;
            }
            $filePathname = $this->extractedPath . '/' . self::GIUSTIFICATIVI . '/' . $file['nome_file'];
            try {
                $documento_file = $service->caricaDaFileImportazione($filePathname, $file['tipologia'], false, null, false, $richiesta, !$this->soloValidazione);
            } catch (\Exception $e) {
                if ($this->visulizzaErroreFile) {
                    $this->errors[] = 'Impossibile caricare il file ' . $file['nome_file'] . ', nome file errato o file non valido (' . $e->getMessage() . ")";
                }
                continue;
            }
            $docG = new DocumentoGiustificativo();
            $docG->setDocumentoFile($documento_file);
            $docG->setGiustificativoPagamento($giustificativo);
            $this->em->persist($docG);
            $arrayDocCaricati[] = $docG;
        }
        return $arrayDocCaricati;
    }

    //carica eventuali documenti contratto
    private function caricaDocumentiContratto($arrayFile, $richiesta, $contratto) {
        /*$arrayTipiAmmessi = array("dc1_bando140", "dc2_bando140", "dc3_bando140", "dc4_bando140", "dc5_bando140", "dc6_bando140", "dc7_bando140", "dc8_bando140",
            "dc9_bando140", "dc10_bando140", "dc11_bando140", "dc12_bando140", "dc13_bando140", "dc14_bando140", "dc15_bando140", "dc16_bando140",
            "dc17_bando140", "dc18_bando140", "dc19_bando140", "dc20_bando140", "dc21_bando140", "dc22_bando140", "dc23_bando140", "dc24_bando140",
            "dc25_bando140", "dc26_bando140", "dc27_bando140", "dc28_bando140", "dc29_bando140", "dc30_bando140", "dc31_bando140", "dc32_bando140",
            "dc33_bando140", "dc34_bando140", "dc35_bando140", "dc36_bando140", "dc37_bando140", "dc38_bando140", "dc39_bando140", "dc40_bando140",
            "dc41_bando140", "dc42_bando140", "dc43_bando140", "dc44_bando140", "dc45_bando140", "dc46_bando140", "dc47_bando140", "dc48_bando140", "dc49_bando140"
            , "dc50_bando140", "dc51_bando140", "dc52_bando140", "dc53_bando140", "dc54_bando140");*/
        $arrayTipiAmmessi = array("dc1_bando165", "dc2_bando165", "dc3_bando165", "dc4_bando165", "dc5_bando165", "dc6_bando165", "dc7_bando165", "dc8_bando165",
            "dc9_bando165", "dc10_bando165", "dc11_bando165", "dc12_bando165", "dc13_bando165", "dc14_bando165", "dc15_bando165", "dc16_bando165",
            "dc17_bando165", "dc18_bando165", "dc19_bando165", "dc20_bando165", "dc21_bando165", "dc22_bando165", "dc23_bando165", "dc24_bando165",
            "dc25_bando165", "dc26_bando165", "dc27_bando165", "dc28_bando165", "dc29_bando165", "dc30_bando165", "dc31_bando165", "dc32_bando165",
            "dc33_bando165", "dc34_bando165", "dc35_bando165", "dc36_bando165", "dc37_bando165", "dc38_bando165", "dc39_bando165", "dc40_bando165",
            "dc41_bando165", "dc42_bando165", "dc43_bando165", "dc44_bando165", "dc45_bando165", "dc46_bando165", "dc47_bando165", "dc48_bando165", "dc49_bando165"
            , "dc50_bando165", "dc51_bando165", "dc52_bando165", "dc53_bando165", "dc54_bando165", "dc55_bando165", "dc56_bando165", "dc57_bando165");
        $service = $this->getContainer()->get('documenti');
        $arrayDocCaricati = array();
        foreach ($arrayFile as $file) {
            if (!in_array($file['tipologia'], $arrayTipiAmmessi)) {
                $this->errors[] = 'Impossibile caricare il file ' . $file['nome_file'] . ', tipologia ' . $file['tipologia'] . ' non ammessa ';
                continue;
            }
            $filePathname = $this->extractedPath . '/' . self::CONTRATTI . '/' . $file['sotto_cartella'] . '/' . $file['nome_file'];
            try {
                $documento_file = $service->caricaDaFileImportazione($filePathname, $file['tipologia'], false, null, false, $richiesta, !$this->soloValidazione);
            } catch (\Exception $e) {
                if ($this->visulizzaErroreFile) {
                    $this->errors[] = 'Impossibile caricare il file ' . $file['nome_file'] . ', nome file errato o file non valido (' . $e->getMessage() . ")";
                }
                continue;
            }
            $docG = new DocumentoContratto();
            $docG->setDocumentoFile($documento_file);
            $docG->setNota($file['nota']);
            $docG->setContratto($contratto);
            $this->em->persist($docG);
            $arrayDocCaricati[] = $docG;
        }
        return $arrayDocCaricati;
    }

    //carica eventuali documenti pagamento
    private function caricaDocumentiPagamento($arrayFile, $richiesta, $pagamento) {
        $service = $this->getContainer()->get('documenti');
        $arrayDocCaricati = new \Doctrine\Common\Collections\ArrayCollection();
        /*$arrayTipiAmmessi = array("dp1_bando140", "dp2_bando140", "dp4_bando140", "dp5_bando140", "dp6_bando140"
            , "dp7_bando140", "dp8_bando140", "dp9_bando140", "dp10_bando140", "dp11_bando140");*/
        $arrayTipiAmmessi = array("dp1_bando165", "dp2_bando165", "dp5_bando165", "dp6_bando165"
            , "dp7_bando165", "dp8_bando165", "dp9_bando165", "dp10_bando165", "dp11_bando165", "dp12_bando165");
        foreach ($arrayFile as $file) {
            if (!in_array($file['tipologia'], $arrayTipiAmmessi)) {
                $this->errors[] = 'Impossibile caricare il file ' . $file['nome_file'] . ', tipologia ' . $file['tipologia'] . ' non ammessa ';
                continue;
            }
            $filePathname = $this->extractedPath . '/' . self::PAGAMENTI . '/' . $file['nome_file'];
            try {
                $documento_file = $service->caricaDaFileImportazione($filePathname, $file['tipologia'], false, null, false, $richiesta, !$this->soloValidazione);
            } catch (\Exception $e) {
                if ($this->visulizzaErroreFile) {
                    $this->errors[] = 'Impossibile caricare il file ' . $file['nome_file'] . ', nome file errato o file non valido (' . $e->getMessage() . ")";
                }
                continue;
            }
            $docG = new DocumentoPagamento();
            $docG->setDocumentoFile($documento_file);
            $docG->setPagamento($pagamento);
            $this->em->persist($docG);
            $arrayDocCaricati->add($docG);
        }
        return $arrayDocCaricati;
    }

    private function getConstraints(): array {
        return [
            'sanita'
                // Inserire qui elenco dei validatori per il progetto
        ];
    }

    private function createLog($richiestaProtocolloId, $code, $message, $appFunction = null) {
        $log = new \ProtocollazioneBundle\Entity\Log();
        $log->setRichiesta_protocollo_id($richiestaProtocolloId);
        $log->setCode($code);
        $log->setLogTime(new \DateTime('now'));
        $log->setMessage($message);
        $log->setAppFunction($appFunction);
        $this->em->persist($log);
        try {
            $this->em->flush($log);
        } catch (\Exception $e) {
            
        }
    }

    private function generaErrore($messaggio) {
        if ($this->soloValidazione == false) {
            throw new \Exception($messaggio);
        } else {
            $this->errors[] = $messaggio;
        }
    }

    private function cancellaCartella($dir) {

        $files = array_diff(scandir($dir), array('.', '..'));

        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->cancellaCartella("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    private function valoreVuoto($var) {
        return (\is_null($var) || $var == '' || $var == ' ');
    }

    private function aggiungiGiustificativiConImportiDaRipresentare(Pagamento $pagamentoAttuale) {
        $pagamentoPrecedente = $pagamentoAttuale->getPagamentoPrecedente($pagamentoAttuale);
        if ($pagamentoPrecedente) {
            $pagamentoPrecedente->creaGiustificativiConImportiDaRipresentare($pagamentoAttuale);
        }
    }

}

function getMessage(ConstraintViolation $error): string {
    return $error->getMessage();
}
