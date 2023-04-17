<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace RichiesteBundle\Service\Cipe;

use CipeBundle\Services\CipeService;
use RichiesteBundle\Entity\Richiesta;
use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use CipeBundle\Entity\Aggregazioni\DatiRichiesta;
use RichiesteBundle\Entity\RichiestaCupBatch;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * Description of GestoreRichiestaCipeService
 *
 * @author gaetanoborgosano
 */
class RichiestaCipeService {

    const TIPOIND = '05';
    const DESCSTRUMPROGR = "POR FESR EMILIA_ROMAGNA 2014-2020 - ";
    const STRUMPROGR = '99';
    const CUMULATIVO = 'N';
    const MAX_TIME_LIMIT_ELAB = 1800; // 30 minuti

    /**
     * @var ContainerInterface
     */
    protected $container;

    protected function getContainer() {
        return $this->container;
    }

    protected function setContainer($container) {
        $this->container = $container;
    }

    protected function getParameter($name) {
        return $this->getContainer()->getParameter($name);
    }

    /**
     * @var Registry
     */
    protected $doctrine;

    protected function getDoctrine() {
        return $this->doctrine;
    }

    protected function setDoctrine($doctrine) {
        $this->doctrine = $doctrine;
    }

    protected function getEm() {
        return $this->getDoctrine()->getManager();
    }

    /**
     * @var CipeService
     */
    protected $CipeService;

    protected function getCipeService() {
        return $this->CipeService;
    }

    protected function setCipeService(CipeService $CipeService) {
        $this->CipeService = $CipeService;
    }

    protected $array_id_richieste;

    protected function getArray_id_richieste() {
        return $this->array_id_richieste;
    }

    protected function setArray_id_richieste($array_id_richieste) {
        $this->array_id_richieste = $array_id_richieste;
    }

    /**
     *
     * @var RichiestaCupBatch
     */
    protected $currentRichiestaCupBatch;

    function getCurrentRichiestaCupBatch() {
        return $this->currentRichiestaCupBatch;
    }

    function setCurrentRichiestaCupBatch(RichiestaCupBatch $currentRichiestaCupBatch = null) {
        $this->currentRichiestaCupBatch = $currentRichiestaCupBatch;
    }

    public function __construct($container, $doctrine, $CipeService) {
        $this->setContainer($container);
        $this->setDoctrine($doctrine);
        $this->setCipeService($CipeService);
        $this->setArray_id_richieste(array());
    }

    /**
     * 
     * @param Richiesta $Richiesta
     * @return DatiRichiesta
     */
    public function buildDatiRichiestaFromRichiesta(Richiesta $Richiesta) {
        try {
            $DatiRichiesta = new DatiRichiesta();

            // set elementi statici
            $DatiRichiesta->setTipo_ind_area_rifer(self::TIPOIND);
            $DatiRichiesta->setStrum_progr(self::STRUMPROGR);
            $DatiRichiesta->setCumulativo(self::CUMULATIVO);

            $DatiRichiesta->setIdRichiesta($Richiesta->getId());
            $DatiRichiesta->setIdProgetto($Richiesta->getId());

            $codifica_locale = $Richiesta->getProtocollo();
            $DatiRichiesta->setCodifica_locale($codifica_locale);

            $anno_decisione = $Richiesta->getProcedura()->getAnnoProgrammazione();
            $DatiRichiesta->setAnno_decisione($anno_decisione);


            /* @var $Istruttoria \IstruttorieBundle\Entity\IStruttoriaRichiesta */
            $Istruttoria = $Richiesta->getIstruttoria();
            
            $id_procedura = $Richiesta->getProcedura()->getId();
            var_dump($Richiesta->getId());
        
            $natura = $Istruttoria->getCupNatura()->getCodice();
            $tipologia = $Istruttoria->getCupTipologia()->getCodice();
            $settore = $Istruttoria->getCupSettore()->getCodice();
            $sottosettore = $Istruttoria->getCupSottosettore()->getCodice();
            $categoria = $Istruttoria->getCupCategoria()->getCodice();
            $codici_tipologia_cop_finanz = $Istruttoria->getArrayCodiciCupTipiCopertura();

            $costo = $Istruttoria->getCostoAmmesso();
            $finanziamento = $Istruttoria->getContributoAmmesso();

            $DatiRichiesta->setCodici_tipologia_cop_finanz($codici_tipologia_cop_finanz);
            $DatiRichiesta->setNatura($natura);
            $DatiRichiesta->setTipologia($tipologia);
            $DatiRichiesta->setSettore($settore);
            $DatiRichiesta->setSottosettore($sottosettore);
            $DatiRichiesta->setCategoria($categoria);
            $DatiRichiesta->setCosto($costo);
            $DatiRichiesta->setFinanziamento($finanziamento);
            /* @var \RichiesteBundle\Entity\Proponente $Mandatario */
            $Mandatario = $Richiesta->getMandatario();

            /* @var $SoggettoMandatario \SoggettoBundle\Entity\Soggetto */
            $SoggettoMandatario = $Mandatario->getSoggetto();

            /*
             * Per il bando professionisti vanno gestite le sedi din intervento
             * più avanti la cosa va estesa per altre procedure e specificata in base alla
             * procedura magati usando il campo che discrimi se la l'intervento viene gestito o meno
             */
            if (in_array($id_procedura, array(26, 66, 100)) && count($Mandatario->getInterventi()) > 0) {
                $interventi = $Mandatario->getInterventi();
                $intervento = $interventi[0];
                /* @var $GeoComune \GeoBundle\Entity\GeoComune */
                $arrayCipeLoc = $this->getCipeService()->TransfromGeoComuneIntoCipeLocalizzazione($intervento->getIndirizzo()->getComune());
                $DatiRichiesta->setStato($arrayCipeLoc['stato']);
                $DatiRichiesta->setRegione($arrayCipeLoc['regione']);
                $DatiRichiesta->setProvincia($arrayCipeLoc['provincia']);
                $DatiRichiesta->setComune($arrayCipeLoc['comune']);
            } elseif (in_array($id_procedura, array(61, 95)) && count($Mandatario->getSedi()) > 0) {
                $sedi = $Mandatario->getSedi();
                $sede = $sedi[0]->getSede();
                /* @var $GeoComune \GeoBundle\Entity\GeoComune */
                $arrayCipeLoc = $this->getCipeService()->TransfromGeoComuneIntoCipeLocalizzazione($sede->getIndirizzo()->getComune());
                $DatiRichiesta->setStato($arrayCipeLoc['stato']);
                $DatiRichiesta->setRegione($arrayCipeLoc['regione']);
                $DatiRichiesta->setProvincia($arrayCipeLoc['provincia']);
                $DatiRichiesta->setComune($arrayCipeLoc['comune']);
            } else {
                /* @var $GeoComune \GeoBundle\Entity\GeoComune */
                $arrayCipeLoc = $this->getCipeService()->TransfromGeoComuneIntoCipeLocalizzazione($SoggettoMandatario->getComune());
                $DatiRichiesta->setStato($arrayCipeLoc['stato']);
                $DatiRichiesta->setRegione($arrayCipeLoc['regione']);
                $DatiRichiesta->setProvincia($arrayCipeLoc['provincia']);
                $DatiRichiesta->setComune($arrayCipeLoc['comune']);
            }
            $partita_iva = $SoggettoMandatario->getPartitaIva();
            if (\is_null($partita_iva)) {
                $partita_iva = $SoggettoMandatario->getCodiceFiscale();
            }

            $DatiRichiesta->setPartita_iva($partita_iva);
            $benficiario = $SoggettoMandatario->getDenominazione();
            $DatiRichiesta->setBenficiario($benficiario);

            if (in_array($id_procedura, array(26, 66, 100)) && count($Mandatario->getInterventi()) > 0) {
                $interventi = $Mandatario->getInterventi();
                $intervento = $interventi[0];
                $via = $intervento->getIndirizzo()->getVia();
                $civico = $intervento->getIndirizzo()->getNumeroCivico();
                $cap = $intervento->getIndirizzo()->getCap();
                $ind_area_rifer = trim(trim(trim($via) . " " . trim($civico)) . " " . trim($cap));
                $DatiRichiesta->setInd_area_rifer($ind_area_rifer);
            } elseif (in_array($id_procedura, array(61, 95, 99, 108, 109, 113, 124, 122, 126, 134)) && !$Mandatario->getSedi()->isEmpty()) {
                $sedi = $Mandatario->getSedi();
                $sede = $sedi[0]->getSede();
                $via = $sede->getIndirizzo()->getVia();
                $civico = $sede->getIndirizzo()->getNumeroCivico();
                $cap = $sede->getIndirizzo()->getCap();
                $ind_area_rifer = trim(trim(trim($via) . " " . trim($civico)) . " " . trim($cap));
                $DatiRichiesta->setInd_area_rifer($ind_area_rifer);
            } else {
                $via = $SoggettoMandatario->getVia();
                $civico = $SoggettoMandatario->getCivico();
                $cap = $SoggettoMandatario->getCap();
                $ind_area_rifer = trim(trim(trim($via) . " " . trim($civico)) . " " . trim($cap));
                $DatiRichiesta->setInd_area_rifer($ind_area_rifer);
            }

            $Asse = $Richiesta->getProcedura()->getAsse();

            $descr_obiettivi = "";

            $ObiettiviSpecifici = $Richiesta->getProcedura()->getObiettiviSpecifici();
            foreach ($ObiettiviSpecifici as $ObiettivoSpecifico) {
                $descr_obiettivi .= $ObiettivoSpecifico->getDescrizione();
                if (strlen($descr_obiettivi) > 100) {
                    break;
                }
            }

            $descr_strum_progr = trim(substr(self::DESCSTRUMPROGR . $Asse->getDescrizione(), 0, 100));
            $altre_informazioni = substr($descr_obiettivi, 0, 100);
            // Per bypassare alcuni scarti del CIPE interveniamo direttamente a livello generico sovrascrivendo per tutte le richieste
            // di un bando i campi problematici
            switch ($id_procedura) {
                case 6:
                    $descr_intervento = 'Progetto di innovazione e diversificazione di prodotto/servizio';
                    break;
                case 7:
                    $descr_intervento = 'Progetto di ricerca e sviluppo';
                    $descr_strum_progr = 'POR FESR EMILIA_ROMAGNA 2014-2020 - ASSE 1';
                    $altre_informazioni = 'Sostegno a progetti collaborativi di ricerca e sviluppo delle imprese';
                    break;
                case 33:
                case 114:
                    $descr_intervento = 'Progetto di ricerca e sviluppo';
                    $descr_strum_progr = 'POR FESR EMILIA_ROMAGNA 2014-2020 - ASSE 1';
                    $altre_informazioni = 'Sostegno a progetti collaborativi di ricerca e sviluppo delle imprese';
                    break;
                case 15:
                    $descr_intervento = 'Riqualificazione di immobili e acquisto attrezzature';
                    break;
                case 26:
                case 66:
                case 100:
                    $descr_intervento = 'Intervento per il riposizionamento strategico dell\'attività professionale';
                    break;
                case 61:
                    $descr_intervento = 'AMMODERNAMENTO DEGLI IMPIANTI MACCHINARI E ATTREZZATURE';
                    $descr_strum_progr = "POR FESR 2014/2020. COMPETITIVITA' E ATTRATTIVITA' DEL SISTEMA PRODUTTIVO";
                    break;
                case 95:
                case 121:
                    $descr_strum_progr = "ORDINANZA DEL COMMISSARIO DELEGATO PER LA RICOSTRUZIONE N. 2 E S.M.I. E N. 3 DEL 2019";
                    $descr_intervento = 'Interventi per l\'insediamento e la riqualificazione, l\'ammodernamento e l\'ampliamento delle attività d\'impresa professionali e/o no profit.';
                    break;
                case 99:
                    $descr_strum_progr = "Asse 3, azioni 3.3.2 e 3.3.4";
                    $descr_intervento = 'Interventi per l\'innovazione tecnologica delle attività commerciali';
                    break;
                case 108:
                case 122:
                    $descr_intervento = 'BANDO START UP INNOVATIVE 2019';
                    $descr_strum_progr = "POR FESR 2014-2020 DGR 854/2019";
                    break;
                case 109:
                    $descr_strum_progr = "Fondi regionali (art. 6 L.R. 25/2018)";
                    $descr_intervento = 'BANDO PER IL SOSTEGNO AGLI INVESTIMENTI DELLE IMPRESE OPERANTI NELLE ATTIVITA RICETTIVE E TURISTICO-RICREATIVE';
                    $altre_informazioni = 'Riqualificazione, ristrutturazione, ammodernamento e rinnovo degli immobili e delle attrezzature ';
                    break;
                case 103:
                    $descr_intervento = 'Progetto di ricerca e sviluppo';
                    $descr_strum_progr = 'Ordinanza 5/2019 Ricerca industriale delle imprese operanti nelle filiere coinvolte dal sisma 2012';
                    $altre_informazioni = 'Sostegno a progetti collaborativi di ricerca e sviluppo delle imprese';
                    break;
                case 113:
                    $descr_strum_progr = "POR FESR 2014-2020 ASSE 3 ATTIVITÀ 3.3.2.-3.3.4 BANDO PRODUZ. ARTIGIANALI ARTISTICHE E TRADIZIONALI";
                    $descr_intervento = 'Intervento per l\'innovazione e la tecnologia delle attività artigianali';
                    break;
                case 124:
                    $descr_strum_progr = "POR – FESR – L.R.N. 41 DEL 10/12/1997";
                    $descr_intervento = 'Qualificazione e valorizzazione delle imprese che operano nel settore del commercio al dettaglio e della somministrazione al pubblico di alimenti e bevande';
                    $altre_informazioni = 'COMMERCIO';
                    break;
                case 126:
                    $descr_intervento = 'BANDO Progetti di ricerca e innovazione COVID-19';
                    $descr_strum_progr = "POR-FESR 2014-2020 DGR n.342/2020";
                    $altre_informazioni = 'EMERGENZA COVID-19​';
                    break;
                case 134:
                    $descr_strum_progr = 'Fondi regionali (Articolo 11, comma 3 bis - Legge regionale n. 40/2002)';
                    $descr_intervento = 'BANDO PER LA QUALIFICAZIONE E INNOVAZIONE DEGLI STABILIMENTI BALNEARI E DELLE STRUTTURE BALNEARI MARITTIME';
                    $altre_informazioni = 'Riqualificazione e innovazione degli stabilimenti e delle strutture balneari marittime';
                    break;
                case 149:
                    $descr_strum_progr = 'AZIONE 3.5.2 DEL POR FESR 2014/2020 E ARTICOLO 12 BIS DELLA L.R. 9 FEBBRAIO 2010';
                    $descr_intervento = 'BANDO PER LA TRANSIZIONE DIGITALE DELLE IMPRESE ARTIGIANE';
                    $altre_informazioni = 'BANDO PER LA TRANSIZIONE DIGITALE DELLE IMPRESE ARTIGIANE';
                    break;
                case 154:
                    $descr_strum_progr = 'Azione 1.4.1 Sostegno alla creazione e al consolidamento di start-up innovative';
                    $descr_intervento = 'BANDO PER L’ATTRAZIONE E IL CONSOLIDAMENTO DI START UP INNOVATIVE';
                    $altre_informazioni = 'Sostenere lo sviluppo e il consolidamento nel territorio regionale di start up innovative';
                    break;
                default:
                    $descr_intervento = substr(trim($Richiesta->getTitolo()), 0, 255);
            }

            $DatiRichiesta->setDescr_intervento($descr_intervento);
            $DatiRichiesta->setDescr_strum_progr($descr_strum_progr);
            $DatiRichiesta->setAltre_informazioni($altre_informazioni);

            $Ateco = $SoggettoMandatario->getCodiceAteco();

            $SediOperative = $Mandatario->getSedi();

            $denominazione_impresa_stabilimento = substr(trim($SoggettoMandatario->getDenominazione()), 0, 100);
            $DatiRichiesta->setDenominazione_impresa_stabilimento($denominazione_impresa_stabilimento);

            // Se esistono sedi operative uso la denominazione dello stabilimento come denominazione
            if (count($SediOperative) > 0 && !in_array($id_procedura, array(114))) {
                /* @var $SedeOperativa \SoggettoBundle\Entity\Sede */
                $SedeOperativa = $SediOperative[0]->getSede();
                $denominazione_stabilimento = substr(trim($SedeOperativa->getDenominazione()), 0, 100);
                if (strlen($denominazione_stabilimento) > 0)
                    $denominazione_impresa_stabilimento = $denominazione_stabilimento;
                // se esiste la sede operativa si utilizza il suo riferimento Ateco
                $Ateco = $SedeOperativa->getAteco();
            }
            if (!\is_null($Ateco) && !in_array($natura, array('03', '01', '02'))) {
                /* @var $Ateco \SoggettoBundle\Entity\Ateco */
                $ateco_sezione = $Ateco->getCodiceMacroSettore();
                $ateco_divisione = $Ateco->getCodiceArea();
                $ateco_gruppo = substr($Ateco->getCodice(), 0, 4);
                $ateco_classe = substr($Ateco->getCodice(), 0, 5);
                $ateco_categoria = substr($Ateco->getCodice(), 0, 7);
                $ateco_sottocategoria = $Ateco->getCodice();
                $DatiRichiesta->setAteco_sezione($ateco_sezione);
                $DatiRichiesta->setAteco_divisione($ateco_divisione);
                $DatiRichiesta->setAteco_gruppo($ateco_gruppo);
                $DatiRichiesta->setAteco_classe($ateco_classe);
                $DatiRichiesta->setAteco_categoria($ateco_categoria);
                $DatiRichiesta->setAteco_sottocategoria($ateco_sottocategoria);
            }

            // -----------------------------
            // sezione elementi di tracciato
            // -----------------------------
            $ente = $SoggettoMandatario->getDenominazione();
            $DatiRichiesta->setEnte($ente);
            $ragione_sociale = $SoggettoMandatario->getDenominazione();
            $DatiRichiesta->setRagione_sociale($ragione_sociale);
            $denom_progetto = $denominazione_progetto = $Richiesta->getTitolo();
            $DatiRichiesta->setDenom_progetto($denom_progetto);
            $DatiRichiesta->setDenominazione_progetto($denominazione_progetto);
            /**
             * $costo = $datiRichiesta['COSTO'];
              $finanziamento = $datiRichiesta['CONTRIBUTO'];

              $codici_tipologia_cop_finanz = array("001");

              if($natura == '07' || $natura == '08') $codici_tipologia_cop_finanz[] = "006";
              $contributo = $datiRichiesta['CONTRIBUTO'];
              if($costo != $contributo) $codici_tipologia_cop_finanz[] = '007';
             */
            /**
             * @todo
             * struttura
             * nome_str_infrastr
             * str_infrastr_unica
             * bene
             * servizio
             * 
             */
            return $DatiRichiesta;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * protocolla singola richiesta tramite web-service
     * @param Richiesta $Richiesta
     * @return type
     * @throws \Exception
     */
    public function ProtocollazioneCipeRichiesta(Richiesta $Richiesta) {
        try {
            $DatiRichiesta = $this->buildDatiRichiestaFromRichiesta($Richiesta);
            return $this->getCipeService()->inoltraRichiestaCupGenerazione($DatiRichiesta);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Genera nomefile con timestamp per richiesta Cup batch
     * @return string
     */
    protected function makeFilenameRichiestaCup() {
        $prefix = "generazioneCup";
        $today = new \DateTime("NOW");
        $timestamp = $today->format("Y-m-d H:i:s");
        $name = $prefix . "_" . $timestamp . ".xml";
        $filename = str_replace(" ", "_", $name);
        $filename = str_replace(":", "-", $filename);
        return $filename;
    }

    /**
     * effettua il pop di  un elemento Richiesta dall'array delle richieste e lo trasforma in un oggetto DatiRichiesta
     * @return DatiRichiesta
     * @throws \Exception
     */
    public function popRichiestaProtocollazioneBatch() {
        try {
            $array_id_richieste = $this->getArray_id_richieste();
            if (count($array_id_richieste) == 0)
                return false;
            $richiesta_id = array_shift($array_id_richieste);
            $this->setArray_id_richieste($array_id_richieste);

            /* @var $IstruttoriaRichiesta IstruttoriaRichiesta */
            $IstruttoriaRichiesta = $this->getDoctrine()->getRepository("IstruttorieBundle\Entity\IstruttoriaRichiesta")->findOneBy(array("id" => $richiesta_id));
            if (\is_null($IstruttoriaRichiesta))
                throw new \Exception("Impossibile trovare istruttoria richiesta con id:[$richiesta_id]");

            $IstruttoriaRichiesta->setUltimaRichiestaCupBatch($this->getCurrentRichiestaCupBatch());

            $Richiesta = $IstruttoriaRichiesta->getRichiesta();
            /* @var $Richiesta Richiesta */
            if (\is_null($Richiesta))
                throw new \Exception("Impossibile trovare la richiesta con id:[$richiesta_id]");

            $this->getEm()->detach($Richiesta);
            $DatiRichiesta = $this->buildDatiRichiestaFromRichiesta($Richiesta);
            if (\is_null($DatiRichiesta))
                throw new \Exception("Impossibile convertire in DatiRichiesta la richiesta con id:[$richiesta_id]");
            $DatiRichiesta->setIstruttoriaRichiesta_id($richiesta_id);
            return $DatiRichiesta;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * effettua il pop di  un elemento Richiesta dall'array delle richieste e lo trasforma in un oggetto DatiRichiesta
     * @return DatiRichiesta
     * @throws \Exception
     */
    public function test_popRichiestaProtocollazioneBatch() {
        try {
            $array_id_richieste = $this->getArray_id_richieste();
            if (count($array_id_richieste) == 0)
                return false;
            $richiesta_id = array_shift($array_id_richieste);
            $this->setArray_id_richieste($array_id_richieste);

            /* @var $Richiesta Richiesta */
            $DatiRichiesta = $this->getDoctrine()->getRepository(\get_class(new DatiRichiesta()))->findOneBy(array("id" => $richiesta_id));
            if (\is_null($DatiRichiesta))
                throw new \Exception("Impossibile trovare la richiesta con id:[$richiesta_id]");

//			$DatiRichiesta = $this->buildDatiRichiestaFromRichiesta($Richiesta);
            if (\is_null($DatiRichiesta))
                throw new \Exception("Impossibile convertire in DatiRichiesta la richiesta con id:[$richiesta_id]");

            $this->getEm()->detach($DatiRichiesta);
            return $DatiRichiesta;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * genera la richiesta cup batch su un array di id richieste
     * @param array $array_id_richieste - array id delle richieste su cui generare il cup batch
     * @throws \Exception
     */
    public function test_GeneraRichiestaProtocollazioneBatch($number_test) {
        try {
            \set_time_limit(self::MAX_TIME_LIMIT_ELAB);

            $array_id_richieste = range(1, $number_test);
            $this->setArray_id_richieste($array_id_richieste);

            $RichiestaCupBatch = new RichiestaCupBatch();
            $NomefileRichiestaCup = $this->makeFilenameRichiestaCup();
            $cupBatchDocumentoRichiesta = $this->getCipeService()->generaRichiestaCupBatch($NomefileRichiestaCup, array(), $this, "test_popRichiestaProtocollazioneBatch");

            $RichiestaCupBatch->setCupBatchDocumentoRichiesta($cupBatchDocumentoRichiesta);
            $this->getEm()->persist($RichiestaCupBatch);
            $this->getEm()->flush();
            return $RichiestaCupBatch;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * genera la richiesta cup batch su un array di id richieste
     * @param array $array_id_richieste - array id delle richieste su cui generare il cup batch
     * @throws \Exception
     */
    public function generaRichiestaProtocollazioneBatch($array_id_richieste) {
        try {
            \set_time_limit(self::MAX_TIME_LIMIT_ELAB);
            $this->setArray_id_richieste($array_id_richieste);

            $RichiestaCupBatch = new RichiestaCupBatch();
            $this->getEm()->persist($RichiestaCupBatch);
            $this->getEm()->flush();
            $this->setCurrentRichiestaCupBatch($RichiestaCupBatch);
            $NomefileRichiestaCup = $this->makeFilenameRichiestaCup();
            $cupBatchDocumentoRichiesta = $this->getCipeService()->generaRichiestaCupBatch($NomefileRichiestaCup, array(), $this, "popRichiestaProtocollazioneBatch");

            $RichiestaCupBatch->setCupBatchDocumentoRichiesta($cupBatchDocumentoRichiesta);
            $this->getEm()->persist($RichiestaCupBatch);
            $this->getEm()->flush();
            $this->setCurrentRichiestaCupBatch(null);
            return $RichiestaCupBatch;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * valida la richiesta cup batch su un array di id richieste
     * @param array $array_id_richieste - array id delle richieste su cui generare il cup batch
     * @throws \Exception
     */
    public function validaRichiestaProtocollazioneBatch($array_id_richieste) {
        try {
            \set_time_limit(self::MAX_TIME_LIMIT_ELAB);
            $this->setArray_id_richieste($array_id_richieste);


            $array_validazione = $this->getCipeService()->validaRichiestaCupBatch(array(), $this, "popRichiestaProtocollazioneBatch");
            return $array_validazione;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function test_elaboraDettaglioElaborazioneCupArrayFromXml() {
        try {
            $file = __DIR__ . "/../../../../app/cache/generazione_scartata.xml";
            $xml_DettaglioElaborazioneCup = \file_get_contents($file);
            $array = $this->getCipeService()->getCupBatchService()->elaboraDettaglioElaborazioneCupArrayFromXml($xml_DettaglioElaborazioneCup);
            var_dump($array);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function elaboraFileRichiestaCupBatch($xml_filename) {
        try {
            $array = $this->getCipeService()->getCupBatchService()->elaboraDettaglioElaborazioneCupArrayFromXmlFile($xml_filename);
            return $array;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

}
