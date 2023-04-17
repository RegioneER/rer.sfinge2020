<?php

namespace SfingeBundle\Controller;

use AttuazioneControlloBundle\Entity\ModalitaPagamentoProcedura;
use BaseBundle\Controller\BaseController;
use Doctrine\ORM\OptimisticLockException;
use DocumentoBundle\Component\ResponseException;
use Exception;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use ProtocollazioneBundle\Entity\EmailProtocollo;
use ProtocollazioneBundle\Entity\RichiestaProtocollo;
use ProtocollazioneBundle\Form\Entity\RicercaLog;
use RichiesteBundle\Entity\Bando127\OggettoSanificazione;
use RichiesteBundle\Entity\Bando95\OggettoCentriStorici;
use RichiesteBundle\Entity\PrioritaProponente;
use RichiesteBundle\Entity\RichiestaRepository;
use RichiesteBundle\Entity\SedeOperativaRichiestaRepository;
use RichiesteBundle\GestoriRichieste\GestoreRichiesteBando_138;
use RichiesteBundle\GestoriRichieste\GestoreRichiesteBando_168;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SfingeBundle\Entity\Importazione774\LogImportazioneIstruttoria774;
use SfingeBundle\Entity\Importazione774\LogImportazioneIstruttoria774Info;
use SfingeBundle\Entity\Utente;
use SfingeBundle\Form\AggiungiModalitaPagamentoProceduraType;
use SfingeBundle\Form\Entity\RicercaRichiestaProtocollo;
use SfingeBundle\Form\Entity\RicercaModalitaPagamentoProcedura;
use SfingeBundle\Form\ModificaModalitaPagamentoProceduraType;
use SpreadsheetExcelReader\Spreadsheet_Excel_Reader;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SfingeController extends BaseController {
    /**
     * @Route("/", name="home")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Pagina", route="home")})
     */
    public function indexAction() {
        $em = $this->getEm();
        $ruoliUtente = $this->get('gestione_utenti')->getRuoliUtente($this->getUser());
        /** @var RichiestaRepository $richiestaRepository */
        $richiestaRepository = $this->getEm()->getRepository("RichiesteBundle:Richiesta");

        $soggetti = $this->getEm()->getRepository('SoggettoBundle:Soggetto')->cercaTuttiDaPersonaIncarico($this->getPersonaId());

        $idProcedure = [];

        foreach ($soggetti as $s) {
            $richieste = $richiestaRepository->getRichiesteDaSoggetto($s->getId());
            foreach ($richieste as $richiesta) {
                $id = $richiesta->getProcedura()->getId();
                if (!in_array($id, $idProcedure)) {
                    $idProcedure[] = $id;
                }
            }
        }
        
        $notizie = $em->getRepository('NotizieBundle\Entity\Notizia')->getNotizieRuoli($ruoliUtente, $idProcedure);

        $procedureConContatore = $this->getEm()->getRepository("SfingeBundle:Bando")->getBandiConContatore();
        foreach ($procedureConContatore as $proceduraConContatore) {
            switch ($proceduraConContatore->getId()) {
                case 95:
                    $proceduraConContatore->richiesteInviate['richieste_inviate'] = $richiestaRepository->getCountRichiesteInoltrateProcedura($proceduraConContatore->getId(), $proceduraConContatore->getAttualeFinestraTemporalePresentazione());

                    $oggettoCentriStorici = new OggettoCentriStorici();
                    $vincoliComuni = $oggettoCentriStorici->getVincoliComuni();

                    $arrayRichiestePerComuni = [];
                    $totaleComuniConLimite = 0;
                    $limiteAltriComuni = 0;
                    foreach ($vincoliComuni as $key => $vincolo) {
                        $comune = $this->getEm()->getRepository('GeoBundle:GeoComune')->find($key);
                        $richiesteInviatePerComune = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->getRichiesteInoltrateProceduraPerComune($proceduraConContatore->getId(), $proceduraConContatore->getAttualeFinestraTemporalePresentazione(), $comune);
                        $arrayRichiestePerComuni[$comune->getId()]['denominazione'] = $comune->getDenominazione();
                        $arrayRichiestePerComuni[$comune->getId()]['richiesteInviate'] = count($richiesteInviatePerComune);
                        $arrayRichiestePerComuni[$comune->getId()]['limite'] = $vincolo['nrMassimoRichieste'];
                        $totaleComuniConLimite += count($richiesteInviatePerComune);
                        $limiteAltriComuni += $vincolo['nrMassimoRichieste'];
                    }
                    
                    $arrayRichiestePerComuni['altri_comuni']['denominazione'] = 'Altri comuni';
                    $arrayRichiestePerComuni['altri_comuni']['richiesteInviate'] = $proceduraConContatore->richiesteInviate['richieste_inviate'] - $totaleComuniConLimite;
                    $arrayRichiestePerComuni['altri_comuni']['limite'] = $proceduraConContatore->getNumeroMassimoRichiesteProcedura() - $limiteAltriComuni;

                    $proceduraConContatore->richiesteInviate['arrayRichiestePerComuni'] = $arrayRichiestePerComuni;
                    break;
                
                case 126:
                    $proceduraConContatore->richiesteInviate = $richiestaRepository->getImportoRichiesteInoltrateProcedura($proceduraConContatore->getId(), $proceduraConContatore->getAttualeFinestraTemporalePresentazione());
                    break;
                    
                case 127:
                    $importoGiaPrenotatoAttivitaRicettive = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->getTotaleImportiPrenotatiBandoSanificazione('ATTIVITA_RICETTIVE');
                    $residuoAttivitaRicettive = bcsub(OggettoSanificazione::ATTIVITA_RICETTIVE, $importoGiaPrenotatoAttivitaRicettive, 2);

                    $importoGiaPrenotatoAttivitaSomministrazione = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->getTotaleImportiPrenotatiBandoSanificazione('ATTIVITA_SOMMINISTRAZIONE');
                    $residuoAttivitaSomministrazione = bcsub(OggettoSanificazione::ATTIVITA_SOMMINISTRAZIONE, $importoGiaPrenotatoAttivitaSomministrazione, 2);

                    /** @var SedeOperativaRichiestaRepository $richiestaRepository */
                    $sedeOperativaRichiestaRepository = $this->getEm()->getRepository("RichiesteBundle:SedeOperativaRichiesta");
                    $proceduraConContatore->richiesteInviate['richieste_inviate'] = count($sedeOperativaRichiestaRepository->getSediOperativeRichiestaConfermate($proceduraConContatore, $proceduraConContatore->getAttualeFinestraTemporalePresentazione()));
                    $proceduraConContatore->richiesteInviate['disponibilita'] = [
                        [
                            'tipologia' => OggettoSanificazione::TIPOLOGIE_UNITA_LOCALE['ATTIVITA_RICETTIVE'],
                            'dotazione_finanziaria' => number_format(OggettoSanificazione::ATTIVITA_RICETTIVE, 2, ',', '.'),
                            'disponibilita_residua' => number_format($residuoAttivitaRicettive, 2, ',', '.')
                        ],
                        [
                            'tipologia' => OggettoSanificazione::TIPOLOGIE_UNITA_LOCALE['ATTIVITA_SOMMINISTRAZIONE'],
                            'dotazione_finanziaria' => number_format(OggettoSanificazione::ATTIVITA_SOMMINISTRAZIONE, 2, ',', '.'),
                            'disponibilita_residua' => number_format($residuoAttivitaSomministrazione, 2, ',', '.')],
                    ];
                    break;

                case 138:
                    $objGestoreRichiesteBando138 = new GestoreRichiesteBando_138($this->container);
                    $proceduraConContatore->totaleContributoRichiesto = $objGestoreRichiesteBando138->contributoTotaleRichiesto($proceduraConContatore);
                    $proceduraConContatore->richiesteInviate = $richiestaRepository->getCountRichiesteInoltrateProcedura($proceduraConContatore->getId(), $proceduraConContatore->getAttualeFinestraTemporalePresentazione());
                    break;
                
                case 168:
                    $objGestoreRichiesteBando168 = new GestoreRichiesteBando_168($this->container);
                    $proceduraConContatore->totaleContributoRichiesto = $objGestoreRichiesteBando168->contributoTotaleRichiesto($proceduraConContatore);
                    $proceduraConContatore->richiesteInviate = $richiestaRepository->getCountRichiesteInoltrateProcedura($proceduraConContatore->getId(), $proceduraConContatore->getAttualeFinestraTemporalePresentazione());
                    break;
                    
                default:
                    $proceduraConContatore->richiesteInviate = $richiestaRepository->getCountRichiesteInoltrateProcedura($proceduraConContatore->getId(), $proceduraConContatore->getAttualeFinestraTemporalePresentazione());
                    break;
            }
        }
        
        // Comunicazioni di integrazione
//        $proceduraBandoIrap = $this->getEm()->getRepository('SfingeBundle:Procedura')->find(118);
//        $proceduraSecondoBandoIrap = $this->getEm()->getRepository('SfingeBundle:Procedura')->find(125);
//        $gestoreIntegrazioneBase = new GestoreIntegrazioneBase($this->container);
//        $riposteIntegrazioniNonLetteBandoIrap = $gestoreIntegrazioneBase->getComunicazioniInterazioneInArrivo($proceduraBandoIrap, $this->getUser());
//        $riposteIntegrazioniNonLetteSecondoBandoIrap = $gestoreIntegrazioneBase->getComunicazioniInterazioneInArrivo($proceduraSecondoBandoIrap, $this->getUser());
        $isSuperAdminIrap = $this->isSuperAdminIrap();
        $csrfTokenManager = $this->container->get("security.csrf.token_manager");
        $token = $csrfTokenManager->getToken("token")->getValue();
        
        return $this->render('SfingeBundle:Default:index.html.twig', [
            'notizie' => $notizie,
            //'soggettiNoPec' => $soggettiNoPec,
            //'comunicazioniIntegrazioneNonLetteBandoIrap' => $riposteIntegrazioniNonLetteBandoIrap,
            //'comunicazioniIntegrazioneNonLetteSecondoBandoIrap' => $riposteIntegrazioniNonLetteSecondoBandoIrap,
            'isSuperAdminIrap' => $isSuperAdminIrap,
            'token' => $token,
            'procedureConContatore' => $procedureConContatore,
            ]
        );
    }

    /**
     * @Route("/manutenzione", name="manutenzione")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Pagina", route="manutenzione")})
     */
    public function manutenzioneAction() {
        return $this->render('SfingeBundle:Default:manutenzione.html.twig');
    }

    /**
     * @Route("/accettazione-privacy", name="accettazionePrivacy")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Pagina", route="accettazionePrivacy")})
     */
    public function accettazionePrivacyAction(Request $request) {
        $formBuilder = $this->createFormBuilder()
            ->add('non_accetto', SubmitType::class, [
                'label' => 'Non accetto ed esco',
            ])
            ->add('accetto', SubmitType::class, [
                'label' => 'Accetto i termini',
            ]);

        $form = $formBuilder->getForm();

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get("non_accetto")->isClicked()) {
                return $this->redirect($this->generateUrl('fos_user_security_logout'));
            }

            /** @var Utente $utente */
            $utente = $this->getUser();

            $utente->setPrivacyAccettata(true);
            $this->getDoctrine()->getManager()->persist($utente);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirect($this->generateUrl('home'));
        }

        return $this->render('accettazione_privacy.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Route("/importa_istruttoria", name="importa_istruttoria")
     * @PaginaInfo(titolo="Importa Istruttoria", sottoTitolo="Importazione delle istruttorie per vecchi bandi")
     * @Menuitem(menuAttivo="importaIstruttoria")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Home", route="home"), @ElementoBreadcrumb(testo="Importa Istruttoria")})
     */
    public function importaIstruttoriaAction(Request $request) {
        ini_set('memory_limit', '2048M');

        if ($request->isMethod('POST')) {
            set_time_limit(0);

            $file = $request->files->get('xlsFile');

            if (null == $file) {
                $this->addFlash('error', 'Selezionare un file di tipo xls');
            } else {
                $pathName = $file->getPathName();
                $data = new Spreadsheet_Excel_Reader();
                $data->read($pathName);

                $righe_ok = 0;

                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->getConfiguration()->setSQLLogger(null);

                // la numerazione parte da 1, ma la prima riga è l'intestazione... quindi il ciclo parte da 2
                // L'ultima riga è invece un totale
                for ($i = 2; $i <= $data->sheets[0]['numRows']; ++$i) {
                    try {
                        // per l'i-sima riga:
                        if (!isset($data->sheets[0]['cells'][$i][1])) {
                            continue;
                        }

                        $num_prot = $data->sheets[0]['cells'][$i][1];
                        //$tipo_progetto    = $data->sheets[0]['cells'][$i][2];
                        //$dimesione_impres = $data->sheets[0]['cells'][$i][3];
                        //$ragione_sociale	= $data->sheets[0]['cells'][$i][4];

                        $importi_excel = [];

                        /* CODICE         vs           IDENTIFICATIVO HTML */
                        /* RI1    */ $importi_excel['A-380'] = is_numeric($data->sheets[0]['cells'][$i][4]) ? $data->sheets[0]['cells'][$i][4] : 0;
                        /* RI2    */ $importi_excel['B-380'] = is_numeric($data->sheets[0]['cells'][$i][5]) ? $data->sheets[0]['cells'][$i][5] : 0;
                        /* RI3    */ $importi_excel['C-380'] = is_numeric($data->sheets[0]['cells'][$i][6]) ? $data->sheets[0]['cells'][$i][6] : 0;
                        /* RI4	  */ $importi_excel['D-380'] = is_numeric($data->sheets[0]['cells'][$i][7]) ? $data->sheets[0]['cells'][$i][7] : 0;
                        /* RI5    */ $importi_excel['E-380'] = is_numeric($data->sheets[0]['cells'][$i][8]) ? $data->sheets[0]['cells'][$i][8] : 0;
                        // RI6   NON E' DEFINITO
                        /* RI7    */ $importi_excel['F-380'] = is_numeric($data->sheets[0]['cells'][$i][9]) ? $data->sheets[0]['cells'][$i][9] : 0;
                        /* RI_TOT */ $importi_excel['VTOT1-380'] = is_numeric($data->sheets[0]['cells'][$i][10]) ? $data->sheets[0]['cells'][$i][10] : 0;

                        /* SP1    */ $importi_excel['G-380'] = is_numeric($data->sheets[0]['cells'][$i][11]) ? $data->sheets[0]['cells'][$i][11] : 0;
                        /* SP2    */ $importi_excel['H-380'] = is_numeric($data->sheets[0]['cells'][$i][12]) ? $data->sheets[0]['cells'][$i][12] : 0;
                        /* SP3    */ $importi_excel['I-380'] = is_numeric($data->sheets[0]['cells'][$i][13]) ? $data->sheets[0]['cells'][$i][13] : 0;
                        /* SP4    */ $importi_excel['L-380'] = is_numeric($data->sheets[0]['cells'][$i][14]) ? $data->sheets[0]['cells'][$i][14] : 0;
                        /* SP5    */ $importi_excel['M-380'] = is_numeric($data->sheets[0]['cells'][$i][15]) ? $data->sheets[0]['cells'][$i][15] : 0;
                        /* SP6    */ $importi_excel['N-380'] = is_numeric($data->sheets[0]['cells'][$i][16]) ? $data->sheets[0]['cells'][$i][16] : 0;
                        /* SP7    */ $importi_excel['O-380'] = is_numeric($data->sheets[0]['cells'][$i][17]) ? $data->sheets[0]['cells'][$i][17] : 0;
                        /* SS_TOT */ $importi_excel['VTOT-380'] = is_numeric($data->sheets[0]['cells'][$i][18]) ? $data->sheets[0]['cells'][$i][18] : 0;

                        /* TOT	  */ $importi_excel['TOT'] = is_numeric($data->sheets[0]['cells'][$i][26]) ? $data->sheets[0]['cells'][$i][26] : 0;

                        $importi_excel['CONTR_RICH'] = is_numeric($data->sheets[0]['cells'][$i][27]) ? $data->sheets[0]['cells'][$i][27] : 0;
                        $importi_excel['MAGG_5_P'] = ('SI' == $data->sheets[0]['cells'][$i][30]);
                        $importi_excel['MAGG_10_P'] = ('SI' == $data->sheets[0]['cells'][$i][31]);

                        //if ($stato == 'FINANZIATO FESR') {
                        // 1. recupero il protocollo
                        $protocollo = $em->getRepository("ProtocollazioneBundle:RichiestaProtocolloFinanziamento")->findOneBy(["num_pg" => $num_prot]);

                        if (null == $protocollo) {
                            $this->addFlash('error', "Protocollo $num_prot non trovato sul database.");
                            continue;
                        }

                        // 2. recupero la richiesta
                        $richiesta = $protocollo->getRichiesta();

                        $oggetti_richiesta = $richiesta->getOggettiRichiesta();

                        $oggetto_richiesta = $oggetti_richiesta[0];

                        $oggetto_richiesta->setContributoImportatoExcel($importi_excel['CONTR_RICH']);
                        $oggetto_richiesta->setMaggiorazioneCinquePerc($importi_excel['MAGG_5_P']);
                        $oggetto_richiesta->setMaggiorazioneDieciPerc($importi_excel['MAGG_10_P']);

                        $em->persist($oggetto_richiesta);

                        // 3. recupero le voci piano costi
                        $voci_piano_costi = $richiesta->getVociPianoCosto();

                        foreach ($voci_piano_costi as $v) {
                            // $codice_voce_piano_costo = {RI1 RI2 RI3 RI4 RI5 RI7 SP1 SP2 SP3 SP4 SP5 SP6 SP7 TOT}
                            $codice_voce_piano_costo = $v->getPianoCosto()->getIdentificativoHtml();

                            if (isset($importi_excel[$codice_voce_piano_costo])) {
                                $importo_ammissibile = $importi_excel[$codice_voce_piano_costo];
                                $importo_richiesto = $v->getImportoAnno1();
                                $taglio_anno_1 = $importo_richiesto - $importo_ammissibile;

                                $istruttoria = new \IstruttorieBundle\Entity\IstruttoriaVocePianoCosto();

                                $istruttoria->setVocePianoCosto($v);
                                $istruttoria->setTaglioAnno1($taglio_anno_1);
                                $istruttoria->setImportoAmmissibileAnno1($importo_ammissibile);

                                $em->persist($istruttoria);
                                //$em->flush();
                                // Solo per debug:
                                //$this->addFlash('success', "$i $num_prot $codice_voce_piano_costo $importo_richiesto $importo_ammissibile $taglio");
                            }
                        }

                        $em->flush();
                        ++$righe_ok;

                        //} //END IF FINANZIATO FESR
                    } catch (\Exception $ex) {
                        $this->addFlash('error', 'Errore sulla riga excel ' . $i . ': ' . $ex->getMessage());
                    }
                } // End FOR

                $this->addFlash('success', "Sono state elaborate correttamente $righe_ok righe" /* . " in stato 'FINANZIATO FESR'." */);
            } // End ELSE sul file presente
        } // End IF POST

        return $this->render('SfingeBundle:ImportaIstruttoria:importaIstruttoria.html.twig', []);
    }

    /**
     * Route("/importa_sistemi_produttivi", name="importa_sistemi_produttivi")
     * @PaginaInfo(titolo="Importa Sistemi Produttivi", sottoTitolo="Importazione dei sistemi produttivi")
     * @Menuitem(menuAttivo="importaSistemiProduttivi")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Home", route="home"), @ElementoBreadcrumb(testo="Importa Sistemi Produttivi")})
     */
    public function importaSistemiProduttiviAction(Request $request) {
        if ($request->isMethod('POST')) {
            set_time_limit(0);

            $file = $request->files->get('xlsFile');

            if (null == $file) {
                $this->addFlash('error', 'Selezionare un file di tipo xls');
            } else {
                $pathName = $file->getPathName();
                $data = new Spreadsheet_Excel_Reader();
                $data->read($pathName);

                $righe_ok = 0;

                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->getConfiguration()->setSQLLogger(null);

                for ($i = 1; $i <= $data->sheets[0]['numRows']; ++$i) {
                    try {
                        // per l'i-sima riga:
                        if (!isset($data->sheets[0]['cells'][$i][1])) {
                            continue;
                        }

                        $id_rich_sfinge_old = $data->sheets[0]['cells'][$i][1];
                        $codice_sist_produttivo = $data->sheets[0]['cells'][$i][2];
                        $codice_orient_tematico = $data->sheets[0]['cells'][$i][3];

                        $richiesta = $em->getRepository("RichiesteBundle:Richiesta")->findOneBy(["id_sfinge_2013" => $id_rich_sfinge_old]);
                        $sistema_produttivo = $em->getRepository("SfingeBundle:SistemaProduttivo")->findOneBy(["codice" => $codice_sist_produttivo]);
                        $orientamento_tematico = $em->getRepository("SfingeBundle:OrientamentoTematico")->findOneBy(["codice" => $codice_orient_tematico]);

                        if (null == $richiesta) {
                            $this->addFlash('error', "Richiesta id_sfinge_2013 $id_rich_sfinge_old non trovata sul database.");
                            continue;
                        }

                        if (null == $sistema_produttivo) {
                            $this->addFlash('error', "Codice Sistema Produttivo $codice_sist_produttivo non trovato sul database.");
                            continue;
                        }

                        if (null == $orientamento_tematico) {
                            $this->addFlash('error', "Codice Orientamento Tematico $codice_orient_tematico non trovato sul database.");
                            continue;
                        }

                        // Recupero il proponente mandatario
                        $proponente = $richiesta->getMandatario();

                        $priorita_proponente = new PrioritaProponente();

                        $priorita_proponente->setProponente($proponente);
                        $priorita_proponente->setSistemaProduttivo($sistema_produttivo);
                        $priorita_proponente->setOrientamentoTematico($orientamento_tematico);

                        $em->persist($priorita_proponente);
                        $em->flush();

                        ++$righe_ok;

                        //}
                    } catch (\Exception $ex) {
                        $this->addFlash('error', 'Errore sulla riga excel ' . $i . ': ' . $ex->getMessage());
                    }
                } // End FOR

                $this->addFlash('success', "Sono state elaborate correttamente $righe_ok righe.");
            } // End ELSE sul file presente
        } // End IF POST
        // Si... anche se è per i sistemi produttivi... posso usare il seguente TWIG
        return $this->render('SfingeBundle:ImportaIstruttoria:importaIstruttoria.html.twig', []);
    }

    /**
     * @Route("/attiva_manutenzione", name="attiva_manutenzione")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Pagina", route="home")})
     */
    public function attivaManutezioneAction() {
        $em = $this->getDoctrine()->getManager();
        $parametro_manutenzione = $em->getRepository("SfingeBundle:ParametroSistema")->findOneByCodice('MANUTENZIONE');
        try {
            $parametro_manutenzione->setValore('true');
            $em->flush();
        } catch (ResponseException $e) {
            $this->addFlash('error', $e->getMessage());
        }
        return $this->redirect($this->generateUrl('home'));
    }

    /**
     * @Route("/disattiva_manutenzione", name="disattiva_manutenzione")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Pagina", route="home")})
     */
    public function disattivaManutezioneAction() {
        $em = $this->getDoctrine()->getManager();
        $parametro_manutenzione = $em->getRepository("SfingeBundle:ParametroSistema")->findOneByCodice('MANUTENZIONE');
        try {
            $parametro_manutenzione->setValore('false');
            $em->flush();
        } catch (ResponseException $e) {
            $this->addFlash('error', $e->getMessage());
        }
        return $this->redirect($this->generateUrl('home'));
    }

    /**
     * Route("/importa_anagrafica_380", name="importa_anagrafica_380")
     * @PaginaInfo(titolo="Importazione Anagrafica 380", sottoTitolo="Importazione Anagrafica 380")
     * @Menuitem(menuAttivo="importaAnagrafica380")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Home", route="home"), @ElementoBreadcrumb(testo="Importazione Anagrafica 380")})
     */
    public function importaAnagrafica380(Request $request) {
        if ($request->isMethod('POST')) {
            $file = $request->files->get('xlsFile');
            if (null == $file) {
                $this->addFlash('error', 'Selezionare un file di tipo xls');
            } else {
                ini_set('memory_limit', '2048M');
                set_time_limit(0);
                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->getConfiguration()->setSQLLogger(null);
                $pathName = $file->getPathName();
                $data = new Spreadsheet_Excel_Reader();
                $data->read($pathName);
                $righe_ok = 0;
                for ($i = 2; $i <= $data->sheets[0]['numRows']; ++$i) {
                    try {
                        // per l'i-sima riga:
                        if (!isset($data->sheets[0]['cells'][$i][1])) {
                            continue;
                        }

                        $num_prot = $data->sheets[0]['cells'][$i][1];
                        $via = $data->sheets[0]['cells'][$i][2];
                        $numero_via = $data->sheets[0]['cells'][$i][3];
                        $cap = $data->sheets[0]['cells'][$i][4];
                        $localita = $data->sheets[0]['cells'][$i][5];
                        $provincia = $data->sheets[0]['cells'][$i][6];

                        $protocollo = $em->getRepository("ProtocollazioneBundle:RichiestaProtocolloFinanziamento")->findOneBy(["num_pg" => $num_prot]);

                        if (null == $protocollo) {
                            $this->addFlash('error', "Protocollo $num_prot non trovato sul database.");
                            continue;
                        }

                        $richiesta = $protocollo->getRichiesta();

                        $oggetti_richiesta = $richiesta->getOggettiRichiesta();
                        $oggetto_richiesta = $oggetti_richiesta[0];

                        $oggetto_richiesta->setIndirizzoSede($via);
                        $oggetto_richiesta->setCivicoSede($numero_via);
                        $oggetto_richiesta->setCapSede($cap);
                        $oggetto_richiesta->setLocalitaSede($localita);
                        $oggetto_richiesta->setProvinciaSede($provincia);

                        $em->persist($oggetto_richiesta);

                        ++$righe_ok;
                    } catch (\Exception $ex) {
                        $this->addFlash('error', 'Errore sulla riga excel ' . $i . ': ' . $ex->getMessage());
                    }
                } // End FOR

                $em->flush();
                $this->addFlash('success', "Sono state elaborate correttamente $righe_ok righe");
            } // End ELSE sul file presente
        } // End IF POST

        return $this->render('SfingeBundle:ImportaIstruttoria:importaIstruttoria.html.twig', []);
    }

    /**
     * Route("/importa_istruttoria_373", name="importa_istruttoria_373")
     * @PaginaInfo(titolo="Importa Istruttoria 373", sottoTitolo="Importazione delle istruttorie per vecchi bandi 373")
     * @Menuitem(menuAttivo="importaistruttoria373")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Home", route="home"), @ElementoBreadcrumb(testo="Importa Istruttoria 373")})
     */
    public function importaIstruttoria373Action(Request $request) {
        ini_set('memory_limit', '2048M');

        $acronimiNotMatch = [];

        if ($request->isMethod('POST')) {
            set_time_limit(0);

            $file = $request->files->get('xlsFile');

            if (null == $file) {
                $this->addFlash('error', 'Selezionare un file di tipo xls');
            } else {
                $pathName = $file->getPathName();
                $data = new Spreadsheet_Excel_Reader();
                $data->read($pathName);

                $righe_ok = 0;

                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->getConfiguration()->setSQLLogger(null);

                // la numerazione parte da 1, ma la prima riga è l'intestazione... quindi il ciclo parte da 2
                // L'ultima riga è invece un totale

                $ultimo_protocollo_per_cui_e_stato_segnalato_errore = '';

                // la numerazione parte da 1, ma la prima riga è l'intestazione... quindi il ciclo parte da 2
                // L'ultima riga è invece un totale
                // DEB for ($i = 2; $i <= 100; $i++) {
                for ($i = 2; $i <= $data->sheets[0]['numRows']; ++$i) {
                    try {
                        // per l'i-sima riga:
                        if (!isset($data->sheets[0]['cells'][$i][1])) {
                            continue;
                        }

                        $id = $data->sheets[0]['cells'][$i][1];
                        $num_prot = $data->sheets[0]['cells'][$i][2];
                        $acronimoLaboratorio = $data->sheets[0]['cells'][$i][3];
                        $acronimoNormalizzato = $data->sheets[0]['cells'][$i][4];
                        $acronimoLaboratorioSch31 = $data->sheets[0]['cells'][$i][5];
                        $codiceFiscale = $data->sheets[0]['cells'][$i][6];
                        $ragioneSociale = $data->sheets[0]['cells'][$i][7];
                        $nomeCompletoConAcronimo = $data->sheets[0]['cells'][$i][8];
                        $ruolo = $data->sheets[0]['cells'][$i][9];
                        $ambito = $data->sheets[0]['cells'][$i][10];
                        $punteggio = $data->sheets[0]['cells'][$i][11];
                        $esito = $data->sheets[0]['cells'][$i][12];

                        $importi_excel = [];

                        /* CODICE         vs          IDENTIFICATIVO HTML */

                        /* Ricerca industriale */
                        /* RIND1    */ $importi_excel['A-373'] = isset($data->sheets[0]['cells'][$i][13]) && is_numeric($data->sheets[0]['cells'][$i][13]) ? $data->sheets[0]['cells'][$i][13] : null;
                        /* RIND2    */ $importi_excel['B-373'] = isset($data->sheets[0]['cells'][$i][14]) && is_numeric($data->sheets[0]['cells'][$i][14]) ? $data->sheets[0]['cells'][$i][14] : null;
                        /* RIND3    */ $importi_excel['C-373'] = isset($data->sheets[0]['cells'][$i][15]) && is_numeric($data->sheets[0]['cells'][$i][15]) ? $data->sheets[0]['cells'][$i][15] : null;
                        /* RIND4    */ $importi_excel['D-373'] = isset($data->sheets[0]['cells'][$i][16]) && is_numeric($data->sheets[0]['cells'][$i][16]) ? $data->sheets[0]['cells'][$i][16] : null;
                        /* RIND5    */ $importi_excel['E-373'] = isset($data->sheets[0]['cells'][$i][17]) && is_numeric($data->sheets[0]['cells'][$i][17]) ? $data->sheets[0]['cells'][$i][17] : null;
                        /* RIND_TOT */ $importi_excel['TOT-373'] = isset($data->sheets[0]['cells'][$i][18]) && is_numeric($data->sheets[0]['cells'][$i][18]) ? $data->sheets[0]['cells'][$i][18] : null;

                        /* Sviluppo sperimentale */
                        /* DEVSP1    */ $importi_excel['A1-373'] = isset($data->sheets[0]['cells'][$i][19]) && is_numeric($data->sheets[0]['cells'][$i][19]) ? $data->sheets[0]['cells'][$i][19] : null;
                        /* DEVSP2    */ $importi_excel['B1-373'] = isset($data->sheets[0]['cells'][$i][20]) && is_numeric($data->sheets[0]['cells'][$i][20]) ? $data->sheets[0]['cells'][$i][20] : null;
                        /* DEVSP3    */ $importi_excel['C1-373'] = isset($data->sheets[0]['cells'][$i][21]) && is_numeric($data->sheets[0]['cells'][$i][21]) ? $data->sheets[0]['cells'][$i][21] : null;
                        /* DEVSP4    */ $importi_excel['D1-373'] = isset($data->sheets[0]['cells'][$i][22]) && is_numeric($data->sheets[0]['cells'][$i][22]) ? $data->sheets[0]['cells'][$i][22] : null;
                        /* DEVSP5    */ $importi_excel['E1-373'] = isset($data->sheets[0]['cells'][$i][23]) && is_numeric($data->sheets[0]['cells'][$i][23]) ? $data->sheets[0]['cells'][$i][23] : null;
                        /* DEVSP_TOT */ $importi_excel['TOT1-373'] = isset($data->sheets[0]['cells'][$i][24]) && is_numeric($data->sheets[0]['cells'][$i][24]) ? $data->sheets[0]['cells'][$i][24] : null;

                        /* Attività di diffusione */
                        /* ATDIF1    */ $importi_excel['A2-373'] = isset($data->sheets[0]['cells'][$i][25]) && is_numeric($data->sheets[0]['cells'][$i][25]) ? $data->sheets[0]['cells'][$i][25] : null;
                        /* ATDIF2    */ $importi_excel['B2-373'] = isset($data->sheets[0]['cells'][$i][26]) && is_numeric($data->sheets[0]['cells'][$i][26]) ? $data->sheets[0]['cells'][$i][26] : null;
                        /* ATDIF3    */ $importi_excel['C2-373'] = isset($data->sheets[0]['cells'][$i][27]) && is_numeric($data->sheets[0]['cells'][$i][27]) ? $data->sheets[0]['cells'][$i][27] : null;
                        /* ATDIF4    */ $importi_excel['D2-373'] = isset($data->sheets[0]['cells'][$i][28]) && is_numeric($data->sheets[0]['cells'][$i][28]) ? $data->sheets[0]['cells'][$i][28] : null;
                        /* ATDIF5    */ $importi_excel['E2-373'] = isset($data->sheets[0]['cells'][$i][29]) && is_numeric($data->sheets[0]['cells'][$i][29]) ? $data->sheets[0]['cells'][$i][29] : null;
                        /* ATDIF_TOT */ $importi_excel['TOT2-373'] = isset($data->sheets[0]['cells'][$i][30]) && is_numeric($data->sheets[0]['cells'][$i][30]) ? $data->sheets[0]['cells'][$i][30] : null;

                        $tot_gen = isset($data->sheets[0]['cells'][$i][31]) && is_numeric($data->sheets[0]['cells'][$i][31]) ? $data->sheets[0]['cells'][$i][31] : null;
                        $cont_rich = isset($data->sheets[0]['cells'][$i][32]) && is_numeric($data->sheets[0]['cells'][$i][32]) ? $data->sheets[0]['cells'][$i][32] : null;

                        // 1. recupero il protocollo
                        $protocollo = $em->getRepository("ProtocollazioneBundle:RichiestaProtocolloFinanziamento")->findOneBy(["num_pg" => $num_prot]);

                        if (null == $protocollo) {
                            if ($num_prot != $ultimo_protocollo_per_cui_e_stato_segnalato_errore) {
                                $log_info = new LogImportazioneIstruttoria774Info();
                                $log_info->setInfo("Protocollo $num_prot non trovato sul database.");
                                $em->persist($log_info);
                                $em->flush();

                                $this->addFlash('error', "Protocollo $num_prot non trovato sul database.");
                                $ultimo_protocollo_per_cui_e_stato_segnalato_errore = $num_prot;
                            }
                            continue;
                        }

                        // 2. recupero la richiesta
                        $richiesta = $protocollo->getRichiesta();

                        $oggetti_richiesta = $richiesta->getOggettiRichiesta();

                        $oggetto_richiesta = $oggetti_richiesta[0];

                        //$oggetto_richiesta->setCostoTotaleImportatoExcel($tot_gen);
                        //$oggetto_richiesta->setContributoImportatoExcel($cont_rich);
                        $oggetto_richiesta->setPunteggioExcel($punteggio);
                        $oggetto_richiesta->setEsitoExcel($esito);

                        $em->persist($oggetto_richiesta);
                        $em->flush();

                        if ('NO AMM' != $esito) {
                            // 3. recupero tutti i proponenti della richiesta:
                            // SOLUZIONE OTTIMIZZATA
                            $proponente_by_acronimo = $em->getRepository("RichiesteBundle:Proponente")->getProponenteByAcronimoLaboratorio($richiesta, $acronimoLaboratorioSch31, $acronimoNormalizzato, $acronimoLaboratorio);

                            if (is_null($proponente_by_acronimo)) {
                                //$id_richiesta = $richiesta->getId();
                                //$this->addFlash('error', "Riga excel $i - Protocollo $num_prot - Richiesta ID $id_richiesta : proponente non trovato per acronimo laboratorio");

                                $proponenti = $richiesta->getProponenti();
                                $lista_acronimi_proponenti = [];
                                foreach ($proponenti as $proponente) {
                                    $lista_acronimi_proponenti[] = $proponente->getSoggetto()->getAcronimoLaboratorio();
                                }

                                $acronimiNotMatch[$i] = [
                                    'protocollo' => $num_prot,
                                    'acron_lab_excel' => $acronimoLaboratorio,
                                    'acron_norm_excel' => $acronimoNormalizzato,
                                    'acron_sc31_excel' => $acronimoLaboratorioSch31,
                                    'acron_db' => $lista_acronimi_proponenti,
                                ];

                                $log = new LogImportazioneIstruttoria774();

                                $log->setRigaExcel($i);
                                $log->setProtocollo($num_prot);
                                $log->setAcronimoLaboratorioExcel($acronimoLaboratorio);
                                $log->setAcronimoNormalizzatoExcel($acronimoNormalizzato);
                                $log->setAcronimoLaboratorioSc31Excel($acronimoLaboratorioSch31);
                                $log->setAcronimoLaboratorioSfinge(implode('|', $lista_acronimi_proponenti));

                                $em->persist($log);
                                $em->flush();

                                continue;

                                /* query per la verifica:
                                 * SELECT acronimo_laboratorio FROM soggetti s
                                 * JOIN proponenti p ON p.soggetto_id = s.id
                                 * WHERE p.richiesta_id = ???
                                 */
                            }

                            $proponente_by_acronimo->setCostoTotaleImportatoExcel($tot_gen);
                            $proponente_by_acronimo->setContributoImportatoExcel($cont_rich);
                            $em->persist($proponente_by_acronimo);
                            $em->flush();

                            // 4. recupero le voci piano costi
                            $voci_piano_costi = $proponente_by_acronimo->getVociPianoCosto();

                            foreach ($voci_piano_costi as $v) {
                                $codice_voce_piano_costo = $v->getPianoCosto()->getIdentificativoHtml();

                                if (isset($importi_excel[$codice_voce_piano_costo])) {
                                    // 5. prima di inserire l'istruttoria piano costo, verifico che quest'ultima non esista già!
                                    $istruttoria_voce = $em->getRepository("IstruttorieBundle:IstruttoriaVocePianoCosto")->findOneBy(["voce_piano_costo" => $v]);

                                    if (is_null($istruttoria_voce)) {
                                        $importo_ammissibile = $importi_excel[$codice_voce_piano_costo];
                                        $importo_richiesto = $v->getImportoAnno1();
                                        $taglio_anno_1 = $importo_richiesto - $importo_ammissibile;

                                        $istruttoria = new \IstruttorieBundle\Entity\IstruttoriaVocePianoCosto();

                                        $istruttoria->setVocePianoCosto($v);
                                        $istruttoria->setTaglioAnno1($taglio_anno_1);
                                        $istruttoria->setImportoAmmissibileAnno1($importo_ammissibile);

                                        $em->persist($istruttoria);
                                        //$em->flush();
                                    }
                                }
                            }

                            $em->flush();

                            ++$righe_ok;
                        } //END IF STATO
                    } catch (\Exception $ex) {
                        $this->addFlash('error', 'Errore sulla riga excel ' . $i . ': ' . $ex->getMessage());
                    }
                } // End FOR

                $log_info = new LogImportazioneIstruttoria774Info();
                $log_info->setInfo("Sono state elaborate correttamente $righe_ok righe con esito diverso da NON AMMESSO");
                $em->persist($log_info);
                $em->flush();

                $this->addFlash('success', "Sono state elaborate correttamente $righe_ok righe con esito diverso da NON AMMESSO");
            } // End ELSE sul file presente
        } // End IF POST

        return $this->render('SfingeBundle:ImportaIstruttoria:importaIstruttoria.html.twig', ['acronimiNotMatch' => $acronimiNotMatch]);
    }

    /**
     * Route("/bonifica_dati_bancari", name="bonifica_dati_bancari")
     */
    public function bonificaDatiBancari(Request $request) {
        $errori = [];
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);
        $atcs = $em->getRepository("RichiesteBundle:Richiesta")->getRichiesteConPagamento();
        foreach ($atcs as $atc) {
            $richiesta = $atc->getRichiesta();
            $mandatario = $richiesta->getMandatario();
            $pagamenti = $atc->getPagamenti();
            $pagamento = $pagamenti->last();
            if (!is_null($pagamento->getAgenzia()) || !is_null($pagamento->getIban()) || !is_null($pagamento->getBanca()) || !is_null($pagamento->getIntestatario())) {
                $datiBancari = new \AttuazioneControlloBundle\Entity\DatiBancari();
                $datiBancari->setProponente($mandatario);
                $datiBancari->setAgenzia($pagamento->getAgenzia());
                $datiBancari->setBanca($pagamento->getBanca());
                $datiBancari->setIban($pagamento->getIban());
                $datiBancari->setIntestatario($pagamento->getIntestatario());

                try {
                    $em->persist($datiBancari);
                    $em->flush();
                } catch (\Exception $ex) {
                    $this->addFlash('error', 'Errore pagamento ' . $pagamento->getId() . ': ' . $ex->getMessage());
                    $errori[] = 'Errore pagamento ' . $pagamento->getId() . ': ' . $ex->getMessage();
                }
            }
        }
        return $this->render('SfingeBundle:SuperAdmin:bonificaDatiBancari.html.twig', ['errori' => $errori]);
    }

    /**
     * Route("/bonifica_importi_rend_773", name="bonifica_importi_rend_773")
     */
    public function bonificaImportoRendicontato773(Request $request) {
        $errori = [];
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        $procedura = $this->getEm()->getRepository("SfingeBundle\Entity\Procedura")->findOneById(7);
        $this->container->get("gestore_giustificativi_istruttoria")->getGestore($procedura)->bonificaImportiAmmessiRISP();
        return $this->render('SfingeBundle:SuperAdmin:bonificaDatiBancari.html.twig', ['errori' => $errori]);
    }

    /**
     * Route("/bonifica_importi_rend_774", name="bonifica_importi_rend_774")
     */
    public function bonificaImportoRendicontato774(Request $request) {
        $errori = [];
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        $procedura = $this->getEm()->getRepository("SfingeBundle\Entity\Procedura")->findOneById(8);
        $this->container->get("gestore_giustificativi_istruttoria")->getGestore($procedura)->bonificaImportiAmmessi();
        return $this->render('SfingeBundle:SuperAdmin:bonificaDatiBancari.html.twig', ['errori' => $errori]);
    }

    /**
     * Route("/bonifica_generali_3", name="bonifica_generali_3")
     */
    public function bonificaSpeseGeneraliBando3(Request $request) {
        $errori = [];
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        $procedura = $this->getEm()->getRepository("SfingeBundle\Entity\Procedura")->findOneById(3);
        $this->container->get("gestore_giustificativi_istruttoria")->getGestore($procedura)->bonificaSpeseGenerali();
        return $this->render('SfingeBundle:SuperAdmin:bonificaDatiBancari.html.twig', ['errori' => $errori]);
    }

    /**
     * Route("/bonifica_importi_3", name="bonifica_importi_3")
     */
    public function bonificaImportiBando3(Request $request) {
        $errori = [];
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        $procedura = $this->getEm()->getRepository("SfingeBundle\Entity\Procedura")->findOneById(3);
        $this->container->get("gestore_giustificativi_istruttoria")->getGestore($procedura)->bonificaImportiAmmessi();
        return $this->render('SfingeBundle:SuperAdmin:bonificaDatiBancari.html.twig', ['errori' => $errori]);
    }

    /**
     * Route("/bonifica_importi_2", name="bonifica_importi_2")
     */
    public function bonificaImportiBando2(Request $request) {
        $errori = [];
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        $procedura = $this->getEm()->getRepository("SfingeBundle\Entity\Procedura")->findOneById(2);
        $this->container->get("gestore_giustificativi_istruttoria")->getGestore($procedura)->bonificaImportiAmmessi();
        return $this->render('SfingeBundle:SuperAdmin:bonificaDatiBancari.html.twig', ['errori' => $errori]);
    }

    /**
     * Route("/bonifica_importi_15", name="bonifica_importi_15")
     */
    public function bonificaImportiBando15(Request $request) {
        $errori = [];
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        $procedura = $this->getEm()->getRepository("SfingeBundle\Entity\Procedura")->findOneById(15);
        $this->container->get("gestore_giustificativi_istruttoria")->getGestore($procedura)->bonificaImportiAmmessi();
        return $this->render('SfingeBundle:SuperAdmin:bonificaDatiBancari.html.twig', ['errori' => $errori]);
    }

    /**
     * Route("/bonifica_importi_26", name="bonifica_importi_26")
     */
    public function bonificaImportiBando26(Request $request) {
        $errori = [];
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        $procedura = $this->getEm()->getRepository("SfingeBundle\Entity\Procedura")->findOneById(26);
        $this->container->get("gestore_giustificativi_istruttoria")->getGestore($procedura)->bonificaImportiAmmessi();
        return $this->render('SfingeBundle:SuperAdmin:bonificaDatiBancari.html.twig', ['errori' => $errori]);
    }

    /**
     * Route("/bonifica_importi_rend_7", name="bonifica_importi_rend_7")
     */
    public function bonificaRendicontatiBando7(Request $request) {
        $errori = [];
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        $procedura = $this->getEm()->getRepository("SfingeBundle\Entity\Procedura")->findOneById(7);
        $this->container->get("gestore_giustificativi_istruttoria")->getGestore($procedura)->bonificaImportiRendicontati();
        return $this->render('SfingeBundle:SuperAdmin:bonificaDatiBancari.html.twig', ['errori' => $errori]);
    }

    /**
     * @Route("/importa_comuni", name="importa_comuni")
     */
    public function importaComuniAction(Request $request): Response {
        \ini_set('memory_limit', '512M');
        $comuni = $this->get('geo.istat_import')->importComuni();
        $twig = [
            'comuni' => $comuni,
        ];
        return $this->render('SfingeBundle:SuperAdmin:importaComuni.html.twig', $twig);
    }

    /**
     * @Route("/elenco_modalita_pagamento_procedura/{sort}/{direction}/{page}", defaults={"sort" : "p.id", "direction" : "asc", "page" : "1"}, name="elenco_modalita_pagamento_procedura")
     * @PaginaInfo(titolo="Elenco modalita pagamento", sottoTitolo="Elenco delle modalita di pagamento associate alle procedure")
     * @Menuitem(menuAttivo="elencoModalitaPagamentoProcedura")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function elencoModalitaPagamentoProceduraAction(): Response {
        $datiRicerca = new RicercaModalitaPagamentoProcedura();
        $risultato = $this->get("ricerca")->ricerca($datiRicerca);
        $mv = [
            'risultato' => $risultato["risultato"],
            "form" => $risultato["form_ricerca"],
            "filtro_attivo" => $risultato["filtro_attivo"],
        ];

        return $this->render('SfingeBundle:SuperAdmin:elencoModalitaPagamentoProcedura.html.twig', $mv);
    }

    /**
     * @Route("/elenco_modalita_pagamento_procedura_pulisci", name="elenco_modalita_pagamento_procedura_pulisci")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function elencoModalitaPagamentoProceduraPulisciAction(): Response {
        $this->get("ricerca")->pulisci(new RicercaModalitaPagamentoProcedura());

        return $this->redirectToRoute("elenco_modalita_pagamento_procedura");
    }

    /**
     * @Route("/aggiungi_modalita_pagamento_procedura", name="aggiungi_modalita_pagamento_procedura")
     * @PaginaInfo(titolo="Aggiungi modalità di pagamento", sottoTitolo="Associa ad una procedura una modalità di pagamento")
     * @Menuitem(menuAttivo="elencoModalitaPagamentoProcedura")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function aggiungiModalitaPagamentoProceduraAction(Request $request): Response {
        $formOptions = [
            'indietro' => $this->generateUrl('elenco_modalita_pagamento_procedura_pulisci'),
        ];
        $form = $this->createForm(AggiungiModalitaPagamentoProceduraType::class, null, $formOptions);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $modalita = $form->getData();
            try {
                $em = $this->getEm();
                $em->persist($modalita);
                $em->flush();
                return $this->addSuccessRedirect('Operazione effettuata con successo', 'elenco_modalita_pagamento_procedura');
            } catch (\Exception $e) {
                $this->container->get('logger')->error($e->getMessage());
                $this->addError('Errore durante il salvataggio delle informazioni');
            }
        }
        $mv = [
            'form' => $form->createView(),
        ];

        return $this->render('SfingeBundle:SuperAdmin:formModalitaPagamentoProcedura.html.twig', $mv);
    }

    /**
     * @Route("/modifica_modalita_pagamento_procedura/{id}", name="modifica_modalita_pagamento_procedura")
     * @PaginaInfo(titolo="Modifica modalità di pagamento", sottoTitolo="Modifica associazione ad una procedura una modalità di pagamento")
     * @Menuitem(menuAttivo="elencoModalitaPagamentoProcedura")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function modificaModalitaPagamentoProceduraAction(Request $request, ModalitaPagamentoProcedura $modalitaPagamentoProcedura): Response {
        $formOptions = [
            'indietro' => $this->generateUrl('elenco_modalita_pagamento_procedura_pulisci'),
        ];
        $form = $this->createForm(ModificaModalitaPagamentoProceduraType::class, $modalitaPagamentoProcedura, $formOptions);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->getEm()->flush();
                return $this->addSuccessRedirect('Operazione effettuata con successo', 'elenco_modalita_pagamento_procedura');
            } catch (\Exception $e) {
                $this->container->get('logger')->error($e->getMessage());
                $this->addError('Errore durante il salvataggio delle informazioni');
            }
        }
        $mv = [
            'form' => $form->createView(),
        ];

        return $this->render('SfingeBundle:SuperAdmin:formModalitaPagamentoProcedura.html.twig', $mv);
    }

    /**
     * @Route("/elimina_modalita_pagamento_procedura/{id}", name="elimina_modalita_pagamento_procedura")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function eliminaModalitaPagamentoProceduraAction(ModalitaPagamentoProcedura $modalitaPagamentoProcedura): Response {
        try {
            $this->getEm()->remove($modalitaPagamentoProcedura);
            $this->getEm()->flush();
            $this->addSuccess('Operazione effettuata correttamente');
        } catch (\Exception $e) {
            $this->addError('Errore durante il salvataggio dei dati');
        }

        return $this->redirectToRoute('elenco_modalita_pagamento_procedura');
    }

    /**
     * @Route("/utilities", name="utilities")
     * @PaginaInfo(titolo="Utilities")
     * @Menuitem(menuAttivo="utilities")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Home", route="home"), @ElementoBreadcrumb(testo="Utilities")})
     */
    public function utilitiesAction(): Response
    {
        $procedureInScadenza = $this->getEm()->getRepository("SfingeBundle:Bando")->getElencoProcedureInScadenza();
        $parameters = ['risultato' => $procedureInScadenza, ];
        return $this->render('SfingeBundle:SuperAdmin:utilities.html.twig', $parameters);
    }

    /**
     * @Route("/elenco_richieste_protocollo/{sort}/{direction}/{page}", defaults={"sort" : "p.id", "direction" : "asc", "page" : "1"}, name="elenco_richieste_protocollo")
     * @PaginaInfo(titolo="Elenco richieste protocollo")
     * @Menuitem(menuAttivo="utilities")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Home", route="home"),
     * 				@ElementoBreadcrumb(testo="Utilities", route="utilities"),
     *              @ElementoBreadcrumb(testo="Elenco richieste protocollo")
     * 				})
     */
    public function elencoRichiesteProtocolloAction(): Response
    {
        $datiRicerca = new RicercaRichiestaProtocollo();
        $risultato = $this->get("ricerca")->ricerca($datiRicerca);
        $parameters = [
            'risultato' => $risultato["risultato"],
            "form" => $risultato["form_ricerca"],
            "filtro_attivo" => $risultato["filtro_attivo"],
        ];

        return $this->render('SfingeBundle:SuperAdmin:elencoRichiestaProtocollo.html.twig', $parameters);
    }

    /**
     * @Route("/elenco_scadenze_procedure/{sort}/{direction}/{page}", defaults={"sort" : "p.id", "direction" : "asc", "page" : "1"}, name="elenco_scadenze_procedure")
     * @PaginaInfo(titolo="Elenco scadenze procedure")
     * @Menuitem(menuAttivo="utilities")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Home", route="home"),
     * 				@ElementoBreadcrumb(testo="Utilities", route="utilities"),
     *              @ElementoBreadcrumb(testo="Elenco scadenze procedure")
     * 				})
     */
    public function elencoScadenzeProcedureAction(): Response
    {
        $procedureInScadenza = $this->getEm()->getRepository("SfingeBundle:Bando")->getElencoProcedureInScadenza();
        $parameters = ['risultato' => $procedureInScadenza, ];
        return $this->render('SfingeBundle:SuperAdmin:elencoScadenzeProcedure.html.twig', $parameters);
    }

    /**
     * @Route("/elenco_log_protocollazione/{sort}/{direction}/{page}", defaults={"sort" : "p.id", "direction" : "asc", "page" : "1"}, name="elenco_log_protocollazione")
     * @PaginaInfo(titolo="Elenco LOG protocollazione")
     * @Menuitem(menuAttivo="utilities")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Home", route="home"),
     * 				@ElementoBreadcrumb(testo="Utilities", route="utilities"),
     *              @ElementoBreadcrumb(testo="Elenco LOG protocollazione")
     * 				})
     */
    public function elencoLogProtocollazioneAction(): Response
    {
        $datiRicerca = new RicercaLog();
        $risultato = $this->get("ricerca")->ricerca($datiRicerca);
        $parameters = [
            'logs' => $risultato["risultato"],
            "form" => $risultato["form_ricerca"],
            "filtro_attivo" => $risultato["filtro_attivo"],
        ];

        return $this->render('SfingeBundle:SuperAdmin:elencoLogProtocollazione.html.twig', $parameters);
    }

    /**
     * @Route("/elenco_log_protocollazione_pulisci", name="elenco_log_protocollazione_pulisci")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function elencoLogProtocollazionePulisciAction(): Response
    {
        $this->get("ricerca")->pulisci(new RicercaLog());
        return $this->redirectToRoute("elenco_log_protocollazione");
    }

    /**
     * @Route("/elenco_richieste_protocollo_pulisci", name="elenco_richieste_protocollo_pulisci")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function elencoRichiesteProtocolloPulisciAction(): Response
    {
        $this->get("ricerca")->pulisci(new RicercaRichiestaProtocollo());
        return $this->redirectToRoute("elenco_richieste_protocollo");
    }

    /**
     * @Route("/dettaglio_email_protocollo/{id}", name="dettaglio_email_protocollo")
     * @PaginaInfo(titolo="Dettaglio PEC inviate")
     * @Menuitem(menuAttivo="utilities")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Home", route="home"),
     * 				@ElementoBreadcrumb(testo="Utilities", route="utilities"),
     *              @ElementoBreadcrumb(testo="Elenco richieste protocollo", route="elenco_richieste_protocollo"),
     *              @ElementoBreadcrumb(testo="Dettaglio PEC inviate")
     * 				})
     */
    public function dettaglioEmailProtocolloAction(RichiestaProtocollo $richiestaProtocollo): Response
    {
        $parameters = [
            'emailsProtocollo' => $richiestaProtocollo->getEmailProtocollo(),
            'soggetto' => $richiestaProtocollo->getRichiesta()->getMandatario()->getSoggetto(),
        ];

        return $this->render('SfingeBundle:SuperAdmin:dettaglioEmailProtocollo.html.twig', $parameters);
    }

    /**
     * @Route("/reinvia_email_protocollo/{id}", name="reinvia_email_protocollo")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @throws OptimisticLockException
     */
    public function svuotaDatiEmailProtocolloAction(EmailProtocollo $emailProtocollo): Response
    {
        try {
            $emailProtocollo->setRicevutePervenute([]);
            $emailProtocollo->setStato(EmailProtocollo::DA_INVIARE);
            $emailProtocollo->setDestinatario(NULL);
            $emailProtocollo->setIdEmail([]);
            $emailProtocollo->setDataInvio(NULL);
            $emailProtocollo->setModificatoDa($emailProtocollo->getCreatoDa());

            $this->getEm()->persist($emailProtocollo);
            $this->getEm()->flush();

            $this->addFlash('success', "Dati salvati correttamente, al prossimo passaggio del CRON verrà inviata una nuova PEC all’indirizzo "
                . $emailProtocollo->getRichiestaProtocollo()->getRichiesta()->getMandatario()->getSoggetto()->getEmailPec());
        } catch (Exception $e) {
            $this->addFlash('error', "Re-invio non riuscito");
            $this->get("logger")->error($e->getMessage());
        }

        return $this->redirect($this->generateUrl('dettaglio_email_protocollo', ['id' => $emailProtocollo->getRichiestaProtocollo()->getId()]));
    }
    
    /**
     * @Route("/passaggio_attuazione", name="passaggio_attuazione")
     * @PaginaInfo(titolo="Passa i progetti di un bando in attuzione", sottoTitolo="Passa i progetti di un bando in attuzione")
     * @Menuitem(menuAttivo="elencoModalitaPagamentoProcedura")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function passaggioAttuazioneAction(Request $request): Response {
        $formOptions = [
            'indietro' => $this->generateUrl('utilities'),
        ];
        $data = new \SfingeBundle\Form\Entity\PassaggioAttuazione();
        $form = $this->createForm(\SfingeBundle\Form\PassaggioAttuazioneType::class, $data, $formOptions);
        $form->handleRequest($request);

        $content = '';
        
        if ($form->isSubmitted() && $form->isValid()) {

            $kernel = $this->get('kernel');
            $application = new \Symfony\Component\Console\Application($kernel);
            $application->setAutoExit(false);

            $input = new \Symfony\Component\Console\Input\ArrayInput(array('id_bando' => $data->getIdProcedura()));

            $output = new \Symfony\Component\Console\Output\BufferedOutput(
                \Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERY_VERBOSE,
                true // true for decorated
            );
            $command = $this->get('generaIstruttoriaCommand');
            $command->run($input, $output);

            $content = $output->fetch();
        }
        $mv = [
            'form' => $form->createView(),
            'content' => $content
        ];

        return $this->render('SfingeBundle:SuperAdmin:passaggioAttuzione.html.twig', $mv);
    }
    
    /**
     * @Route("/aggiorna_giustificativi_rinviati", name="aggiorna_giustificativi_rinviati")
     * @PaginaInfo(titolo="Aggiorna giustificativi rinviati pagamento", sottoTitolo="Aggiorna giustificativi rinviati pagamento")
     * @Menuitem(menuAttivo="elencoModalitaPagamentoProcedura")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function aggiornaGiustificativiRinviatiAction(Request $request): Response {
        $formOptions = [
            'indietro' => $this->generateUrl('utilities'),
        ];
        $data = new \SfingeBundle\Form\Entity\GestionePagamenti();
        $form = $this->createForm(\SfingeBundle\Form\GestionePagamentiType::class, $data, $formOptions);
        $form->handleRequest($request);

        $content = '';
        
        if ($form->isSubmitted() && $form->isValid()) {

            $kernel = $this->get('kernel');
            $application = new \Symfony\Component\Console\Application($kernel);
            $application->setAutoExit(false);

            $input = new \Symfony\Component\Console\Input\ArrayInput(array('id_pagamento' => $data->getIdPagamento()));

            $output = new \Symfony\Component\Console\Output\BufferedOutput(
                \Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERY_VERBOSE,
                true // true for decorated
            );
            $command = $this->get('aggiornaRinviatiCommand');
            $command->run($input, $output);

            $content = $output->fetch();
        }
        $mv = [
            'form' => $form->createView(),
            'content' => $content
        ];

        return $this->render('SfingeBundle:SuperAdmin:aggiornaGiustificativiRinviati.html.twig', $mv);
    }
}
