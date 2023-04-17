<?php

namespace AttuazioneControlloBundle\Controller\Controlli;

use AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto;
use AttuazioneControlloBundle\Entity\Controlli\ControlloCampione;
use AttuazioneControlloBundle\Entity\Controlli\ElementoChecklistControllo;
use AttuazioneControlloBundle\Form\Entity\ChecklistSpecifica;
use AttuazioneControlloBundle\Form\Entity\GestioneChecklistSpecifica;
use AttuazioneControlloBundle\Form\ControlliStabilita\GestioneChecklistSpecificaStabilitaType;
use AttuazioneControlloBundle\Form\ControlliStabilita\GestioneChecklistSpecificaPuntualeType;
use BaseBundle\Annotation\ControlloAccesso;
use BaseBundle\Controller\BaseController;
use BaseBundle\Service\SpreadsheetFactory;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use RichiesteBundle\Entity\Richiesta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormError;

/**
 * @Route("/attuazione/controlli_stabilita")
 */
class ControlliStabilitaController extends BaseController {

    /**
     * @Route("/home_controlli_stabilita/",  name="home_controlli_stabilita")
     * @PaginaInfo(titolo="Elenco controlli", sottoTitolo="mostra l'elenco delle procedure con controlli")
     * @Menuitem(menuAttivo="elencoControlliStabilita")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Controlli")})
     */
    public function homeStabilitaAction() {

        $em = $this->getEm();
        $campioni = $em->getRepository('AttuazioneControlloBundle\Entity\Controlli\ControlloCampione')->findAll();

        /*
         * Sono costretto a manipolare i risultati a posteriori per poter aggiungere
         * i risultati delle colonne richieste perchè con una singola query potrebbe essere molto ostico
         */
        foreach ($campioni as $campione) {
            $repository = $em->getRepository('AttuazioneControlloBundle\Entity\Controlli\ControlloCampione');
            $id_procedura = $campione->getId();
            $imprese_campionate = $repository->getImpreseCampionate($id_procedura);
            $imprese_controllate = $repository->getImpreseControllate($id_procedura);
            $cl_rend_ammessa = $repository->getCampioniConClAmmesseSenzaEsito($id_procedura);
            if ($campione->getTipo() == 'AUTO') {
                $universo = $repository->getCampioniByDate($campione);
            } elseif ($campione->getTipo() == 'FILE') {
                $universo = $repository->getCampioniByArrayId($campione);
            } else {
                $universo = array();
            }
            $campione->setImpreseCampionate($imprese_campionate);
            $campione->setImpreseControllate($imprese_controllate);
            $campione->setClRendAmmesse($cl_rend_ammessa);
            $campione->setUniverso($universo);
        }

        $dati = [
            'menu_principale' => 'procedura',
            'risultati' => $campioni];

        return $this->render('AttuazioneControlloBundle:ControlliStabilita:elencoControlliCampione.html.twig', $dati);
    }

    /**
     * @Route("/elenco_controlli_stabilita/{id_campione}/{sort}/{direction}/{page}", defaults={"sort" : "i.id", "direction" : "asc", "page" : "1"}, name="elenco_controlli_stabilita")
     * @PaginaInfo(titolo="Elenco controlli", sottoTitolo="mostra l'elenco dei controlli")
     * @Menuitem(menuAttivo="elencoControlliStabilita")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco controlli")})
     */
    public function elencoStabilitaAction($id_campione) {
        $em = $this->getEm();
        $datiRicerca = new \AttuazioneControlloBundle\Form\Entity\Controlli\RicercaControlli();
        $datiRicerca->setUtente($this->getUser());
        $campione = $em->getRepository('AttuazioneControlloBundle\Entity\Controlli\ControlloCampione')->findOneById($id_campione);
        $datiRicerca->setCampione($campione);
        $datiRicerca->setTipoControllo('STABILITA_PUNTUALE');
        //$campioni = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto")->getControlliCampione($id_campione);
        $campioni = $this->get("ricerca")->ricerca($datiRicerca);
        

        return $this->render('AttuazioneControlloBundle:ControlliStabilita:elencoControlli.html.twig', [
                'menu_principale' => 'campioni',
                'risultati' => $campioni["risultato"],
                "formRicerca" => $campioni["form_ricerca"],
                "filtro_attivo" => $campioni["filtro_attivo"]
        ]);
    }
    
     /**
     * @Route("/elenco_controlli_stabilita_pulisci", name="elenco_controlli_stabilita_pulisci")
     */
    public function elencoControlliStabilitaPulisciAction() {
        $this->get("ricerca")->pulisci(new \AttuazioneControlloBundle\Form\Entity\Controlli\RicercaControlli());
        return $this->redirectToRoute("home_controlli_stabilita");
    }

    /**
     * @Route("/valuta_stabilita/{id_valutazione_checklist}", name="valuta_checklist_controlli_stabilita")
     * @Menuitem(menuAttivo="elencoControlliStabilita")
     * ControlloAccesso(contesto="procedura", classe="IstruttorieBundle:ValutazioneChecklistIstruttoria", opzioni={"id" = "id_valutazione_checklist"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_valutazione_checklist
     */
    public function valutaChecklistAction($id_valutazione_checklist) {
        $valutazione_checklist = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ValutazioneChecklistControllo")->find($id_valutazione_checklist);
        return $this->get("gestore_controlli_stabilita")->getGestore($valutazione_checklist->getControlloProgetto()->getProcedura())->valutaChecklist($valutazione_checklist);
    }

    /**
     * @Route("/{id_controllo}/riepilogo_controllo_stabilita", name="riepilogo_controllo_stabilita")
     * @PaginaInfo(titolo="Riepilogo del controllo", sottoTitolo="dati riepilogativi del controllo")
     * @Menuitem(menuAttivo="elencoControlliStabilita")
     * ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_controllo
     */
    public function riepilogoStabilitaAction($id_controllo) {
        $controllo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto")->find($id_controllo);
        return $this->get("gestore_controlli_stabilita")->getGestore($controllo->getProcedura())->riepilogoControllo($controllo);
    }

    /**
     * @Route("/{id_controllo}/esito_finale_stabilita", name="esito_finale_controlli_stabilita")
     * @PaginaInfo(titolo="Esito finale istruttoria pagamento")
     * @Menuitem(menuAttivo="elencoControlliStabilita")
     * ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_controllo
     */
    public function esitoFinaleStabilitaAction($id_controllo) {
        $controllo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto")->find($id_controllo);
        return $this->get("gestore_controlli_stabilita")->getGestore($controllo->getProcedura())->esitoFinale($controllo);
    }

    /**
     * @Route("/{id_controllo}/documenti_controllo_stabilita", name="documenti_controlli_stabilita")
     * @PaginaInfo(titolo="Documenti controllo progetto", sottoTitolo="documenti caricati per il controllo del progetto")
     * @Menuitem(menuAttivo="elencoControlliStabilita")
     * @param mixed $id_controllo
     */
    public function documentiStabilitaAction($id_controllo) {
        $controllo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto")->find($id_controllo);
        return $this->get("gestore_controlli_stabilita")->getGestore($controllo->getProcedura())->documentiControllo($controllo);
    }

    /**
     * @Route("/{id_controllo}/documenti_controllo_campione", name="documenti_controllo_campione")
     * @PaginaInfo(titolo="Documenti controllo campione", sottoTitolo="documenti caricati per il controllo della procedura")
     * @Menuitem(menuAttivo="elencoControlliStabilita")
     * @param mixed $id_controllo
     */
    public function documentiControlloCampioneAction($id_controllo) {
        $controllo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ControlloCampione")->find($id_controllo);
        return $this->get("gestore_controlli_stabilita")->getGestore()->documentiControlloCampione($controllo);
    }

    /**
     * @Route("/{id_controllo}/elimina_documento_controllo_stabilita/{id_documento}/{verbale}", defaults={"verbale" : "0"}, name="elimina_documento_controllo_stabilita")
     * @param mixed $id_controllo
     * @param mixed $id_documento
     * @param mixed $verbale
     */
    public function eliminaDocumentoStabilitaAction($id_controllo, $id_documento, $verbale) {
        $controllo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto")->find($id_controllo);
        $documento_controllo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\DocumentoControllo")->find($id_documento);
        return $this->get("gestore_controlli_stabilita")->getGestore($controllo->getProcedura())->eliminaDocumentoControllo($controllo, $documento_controllo, $verbale);
    }

    /**
     * @Route("/{id_controllo}/elimina_documento_controllo_campione/{id_documento}", name="elimina_documento_controllo_campione")
     * @param mixed $id_controllo
     * @param mixed $id_documento
     */
    public function eliminaDocumentoControlloProceduraAction($id_controllo, $id_documento) {
        $controllo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ControlloProcedura")->find($id_controllo);
        $documento_controllo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\DocumentoControlloCampione")->find($id_documento);
        return $this->get("gestore_controlli_stabilita")->getGestore()->eliminaDocumentoControlloCampione($controllo, $documento_controllo);
    }

    /**
     * @Route("/{id_controllo}/valuta_sopralluogo_form_stabilita", name="valuta_sopralluogo_form_stabilita")
     * @PaginaInfo(titolo="Esito finale istruttoria pagamento")
     * @Menuitem(menuAttivo="elencoControlliStabilita")
     * ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_controllo
     */
    public function valutaSopralluogoStabilitaFormAction($id_controllo) {
        $controllo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto")->find($id_controllo);
        return $this->get("gestore_controlli_stabilita")->getGestore($controllo->getProcedura())->valutaSopralluogoForm($controllo);
    }

    /**
     * @Route("/estrazioni/estrazione_universo_stabilita/", name="estrazione_universo_stabilita")
     */
    public function estrazioniUniversoProgetti() {
        $em = $this->getEm();
        $procedura = $em->getRepository("SfingeBundle\Entity\Procedura")->find(4);
        return $this->get("gestore_controlli_stabilita")->getGestore($procedura)->estrazioneUniversoStabilita();
    }

    /**
     * @Route("/gestione_checklist_specifiche_stabilita/", name="gestione_checklist_specifiche_stabilita")
     * @PaginaInfo(titolo="Modifica controlli specifici", sottoTitolo="Permette la modifica delle domande specifiche per procedura")
     * @Menuitem(menuAttivo="gestione-checklist-specifiche-post")
     */
    public function gestioneChecklistSpecificheAction(Request $request): Response {
        $elementiChecklist = $this->getEm()->getRepository(ElementoChecklistControllo::class)->getElementiSpecificoStabilita();

        $query = $this->getEm()->createQuery("
			select p from AttuazioneControlloBundle\Entity\Controlli\ControlloCampione p
			where p.id in (:ids) 
		");
        /** @var ChecklistSpecifica[] $elementiForm */
        $elementiForm = array_map(function (ElementoChecklistControllo $elemento) use ($query) {
            $procedure = $query->setParameter('ids', $elemento->getProcedure())->getResult();
            return new ChecklistSpecifica($elemento, $procedure);
        }, $elementiChecklist);
        $elementoRootForm = new GestioneChecklistSpecifica($elementiForm);

        $form = $this->createForm(GestioneChecklistSpecificaStabilitaType::class, $elementoRootForm);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($elementiForm as $elemento) {
                $ids = \array_map(function (\AttuazioneControlloBundle\Entity\Controlli\ControlloCampione $p) {
                    return $p->getId();
                }, $elemento->procedure);

                $elemento->elemento->setProcedure($ids);
            }

            try {
                $this->getEm()->flush();

                $this->addSuccess('Operazione eseguita con successo');
            } catch (\Exception $e) {
                $this->addError('Errore durante il salvataggio delle informazioni');
                $this->get('logger')->error($e->getTraceAsString());

                throw $e;
            }
        }
        $mv = [
            'form' => $form->createView(),
        ];

        return $this->render('AttuazioneControlloBundle:Controlli:gestioneChecklistSpecifica.html.twig', $mv);
    }

    /**
     * @Route("/gestione_checklist_specifiche_puntuali/", name="gestione_checklist_specifiche_puntuali")
     * @PaginaInfo(titolo="Modifica controlli specifici", sottoTitolo="Permette la modifica delle domande specifiche per procedura")
     * @Menuitem(menuAttivo="gestione-checklist-puntuali-post")
     */
    public function gestioneChecklistSpecifichePuntualiAction(Request $request): Response {
        $elementiChecklist = $this->getEm()->getRepository(ElementoChecklistControllo::class)->getElementiSpecificoPuntuale();

        $query = $this->getEm()->createQuery("
			select p from AttuazioneControlloBundle\Entity\Controlli\ControlloCampione p
			where p.id in (:ids)
		");
        /** @var ChecklistSpecifica[] $elementiForm */
        $elementiForm = array_map(function (ElementoChecklistControllo $elemento) use ($query) {
            $procedure = $query->setParameter('ids', $elemento->getProcedure())->getResult();
            return new ChecklistSpecifica($elemento, $procedure);
        }, $elementiChecklist);
        $elementoRootForm = new GestioneChecklistSpecifica($elementiForm);

        $form = $this->createForm(GestioneChecklistSpecificaPuntualeType::class, $elementoRootForm);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($elementiForm as $elemento) {
                $ids = \array_map(function (\AttuazioneControlloBundle\Entity\Controlli\ControlloCampione $p) {
                    return $p->getId();
                }, $elemento->procedure);

                $elemento->elemento->setProcedure($ids);
            }

            try {
                $this->getEm()->flush();

                $this->addSuccess('Operazione eseguita con successo');
            } catch (\Exception $e) {
                $this->addError('Errore durante il salvataggio delle informazioni');
                $this->get('logger')->error($e->getTraceAsString());

                throw $e;
            }
        }
        $mv = [
            'form' => $form->createView(),
        ];

        return $this->render('AttuazioneControlloBundle:Controlli:gestioneChecklistSpecifica.html.twig', $mv);
    }

    /**
     * @Route("/{id_controllo}/verbale_sopralluogo_form_stabilita", name="verbale_sopralluogo_form_stabilita")
     * @PaginaInfo(titolo="Verbale del controllo fase sopralluogo", sottoTitolo="dati riepilogativi del controllo fase sopralluogo")
     * @Menuitem(menuAttivo="elencoControlliStabilita")
     * ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_controllo
     */
    public function verbaleSopralluogoStabilitaAction($id_controllo) {
        $controllo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto")->find($id_controllo);
        return $this->get("gestore_controlli_stabilita")->getGestore($controllo->getProcedura())->verbaleSopralluogoControllo($controllo);
    }

    /**
     * @Route("/{id_controllo}/genera_verbale_sopralluogo_stabilita", name="genera_verbale_sopralluogo_stabilita")
     * @param mixed $id_controllo
     */
    public function generaVerbaleSopralluogoStabilitaAction($id_controllo) {
        $controllo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto")->find($id_controllo);
        $response = $this->get("gestore_controlli_stabilita")->getGestore($controllo->getProcedura())->generaVerbaleSopralluogoStabilita($controllo);
        return $response;
    }

    /**
     * @Route("/estrazioni_stabilita", name="estrazioni_stabilita")
     * @PaginaInfo(titolo="Elenco estrazioni disponibili")
     * @Menuitem(menuAttivo="elencoControlliStabilita")
     */
    public function estrazioniAction() {
        $dati = ['menu_principale' => 'estrazioni'];
        return $this->render('AttuazioneControlloBundle:ControlliStabilita:estrazioni.html.twig', $dati);
    }

    /**
     * @Route("/aggiungi_campione", name="aggiungi_campione")
     * @PaginaInfo(titolo="Elenco controlli", sottoTitolo="mostra l'elenco delle procedure con controlli")
     * @Menuitem(menuAttivo="elencoControlliStabilita")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Controlli")})
     */
    public function aggiungiCampioneAction() {
        $em = $this->getEm();
        $campione = new \AttuazioneControlloBundle\Entity\Controlli\ControlloCampione();

        $options = array();
        $options["url_indietro"] = $this->generateUrl("home_controlli_stabilita");

        $form = $this->createForm("AttuazioneControlloBundle\Form\ControlliStabilita\ControlloCampioneType", $campione, $options);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($campione->getTipo() == 'AUTO') {
                if (is_null($campione->getDataInizio()) || is_null($campione->getDataTermine())) {
                    $form->addError(new FormError("Una delle date non è valorizzata"));
                }
                if ($campione->getDataInizio() > $campione->getDataTermine()) {
                    $form->addError(new FormError("La data iniziale non può essere maggiore della data finale"));
                }
                if (!is_null($campione->getFile())) {
                    $form->addError(new FormError("Non è possible caricare da file se si sceglie la tipologia automatica"));
                }
            }
            if ($campione->getTipo() == 'FILE') {
                if (!is_null($campione->getDataInizio()) || !is_null($campione->getDataTermine())) {
                    $form->addError(new FormError("Non valorizzare le date in caso di campionamento da file"));
                }
                if (is_null($campione->getFile())) {
                    $form->addError(new FormError("In caso di campionamenti da file è obbligatiorio caricare il file"));
                } else {
                    $progettiDaImportare = $this->effettuaParsingExcelSimple($campione->getFile());
                    if (count($progettiDaImportare) == 0) {
                        $form->addError(new FormError("Nessun progetto importato, si consiglia di verificare il formato del file"));
                    }
                }
            }

            if ($form->isValid()) {
                try {
                    $em->beginTransaction();
                    if ($campione->getTipo() == 'FILE' && !is_null($campione->getFile())) {
                        $campione->setPreCampione($progettiDaImportare);
                    }
                    $em->persist($campione);
                    $em->flush();
                    $em->commit();
                    $this->addFlash("success", "Campione correttamente salvato");
                    return $this->redirectToRoute("home_controlli_stabilita");
                } catch (\Exception $e) {
                    $em->rollback();
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }
            }
        }

        $dati = array();
        $dati["form"] = $form->createView();

        return $this->render("AttuazioneControlloBundle:ControlliStabilita:campione.html.twig", $dati);
    }

    /**
     * @Route("/associa_operazioni_campione_stabilita/{id_campione}/{sort}/{direction}/{page}", defaults={"sort" = "a.id", "direction" = "asc", "page" = "1"}, name="associa_operazioni_campione_stabilita")
     * @PaginaInfo(titolo="Elenco controlli", sottoTitolo="mostra l'elenco delle procedure con controlli")
     * @Menuitem(menuAttivo="elencoControlliStabilita")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Controlli")})
     */
    public function associaOperazioneCampioneStabilitaAction($id_campione) {

        $em = $this->getEm();
        $campione = $em->getRepository('AttuazioneControlloBundle\Entity\Controlli\ControlloCampione')->findOneById($id_campione);

        $datiRicerca = new \AttuazioneControlloBundle\Form\Entity\ControlliStabilita\RicercaControlli();
        $datiRicerca->setCampione($campione);
        $datiRicerca->setUtente($this->getUser());

        $risultato = $this->get("ricerca")->ricerca($datiRicerca);

        $dati["id_campione"] = $campione->getId();

        $options = array();
        $options["url_indietro"] = $this->generateUrl("home_controlli_stabilita");
        $tipoControllo = $campione->getTipoControllo();

        $campioni_indicizzati = array();
        if (!is_null($campione->getControlli())) {
            foreach ($campione->getControlli() as $controllo) {
                $campioni_indicizzati[$controllo->getRichiesta()->getId()] = $controllo;
            }
        }

        foreach ($risultato["risultato"] as $richiesta) {
            $controllo = new ControlloProgetto($richiesta['richiesta']);
            $controllo->setTipologia($tipoControllo);

            if (isset($campioni_indicizzati[$richiesta['richiesta']->getId()])) {
                $controllo->setSelezionato(true);
            }

            $campione->addCampioneEsteso($controllo);
        }

        $form = $this->createForm("AttuazioneControlloBundle\Form\ControlliStabilita\AssociazioneCampioneType", $campione, $options);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                foreach ($form->get("campioni_estesi")->all() as $form_campione) {
                    $campione_esteso = $form_campione->getData();

                    if (isset($campioni_indicizzati[$campione_esteso->getRichiesta()->getId()])) {
                        $campione_nuovo = $campioni_indicizzati[$campione_esteso->getRichiesta()->getId()];
                    } else {
                        $campione_nuovo = new ControlloProgetto();
                    }

                    if ($campione_esteso->getSelezionato()) {
                        $campione_nuovo->setRichiesta($campione_esteso->getRichiesta());
                        $campione_nuovo->setTipologia($tipoControllo);
                        $campione->addControllo($campione_nuovo);
                    } else {
                        if (!is_null($campione_nuovo->getId())) {
                            $em->remove($campione_nuovo);
                        }
                    }
                }

                $em = $this->getEm();
                try {
                    $em->flush();
                    $this->addFlash("success", "La pianificazione per il requisito è stata correttamente salvata");
                    return $this->redirectToRoute("associa_operazioni_campione_stabilita", array("id_campione" => $campione->getId()));
                } catch (\Exception $e) {
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza. " . $e->getMessage());
                }
            }
        }

        $dati = array('risultati' => $risultato["risultato"], "formRicerca" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"]);
        $dati["form"] = $form->createView();
        $dati["menu"] = "riepilogo";
        $dati["risultati"] = $risultato["risultato"];
        $dati["formRicerca"] = $risultato["form_ricerca"];
        $dati["filtro_attivo"] = $risultato["filtro_attivo"];

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco controlli", $this->generateUrl("elenco_controlli_stabilita", array('id_campione' => $controllo->getCampione()->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Universo campionario");

        return $this->render("AttuazioneControlloBundle:ControlliStabilita:associaCampione.html.twig", $dati);
    }

    /**
     * @Route("/associa_operazioni_campione_stabilita_pulisci/{id_campione}", name="associa_operazioni_campione_stabilita_pulisci")
     */
    public function elencoOperazionePulisciAction($id_campione) {
        $em = $this->getEm();
        $campione = $em->getRepository('AttuazioneControlloBundle\Entity\Controlli\ControlloCampione')->findOneById($id_campione);
        $datiRicerca = new \AttuazioneControlloBundle\Form\Entity\ControlliStabilita\RicercaControlli();
        $datiRicerca->setCampione($campione);
        $datiRicerca->setUtente($this->getUser());
        $this->get("ricerca")->pulisci($datiRicerca);
        return $this->redirectToRoute("associa_operazioni_campione_stabilita", array("id_campione" => $id_campione));
    }

    /**
     * @Route("/importa_campione/{id_campione}", name="importa_campione")
     * @PaginaInfo(titolo="Elenco controlli", sottoTitolo="mostra l'elenco delle procedure con controlli")
     * @Menuitem(menuAttivo="elencoControlliStabilita")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Controlli")})
     */
    public function importaCampioneAction($id_campione, Request $request) {
        $em = $this->getEm();
        $campione = $em->getRepository('AttuazioneControlloBundle\Entity\Controlli\ControlloCampione')->findOneById($id_campione);
        $tipoControllo = $campione->getTipoControllo();
        $form = $this->createFormBuilder()
            ->add('file', FileType::class, [
                'label' => 'Foglio di calcolo importazione progetti',
                'estensione' => 'xls, xlsx, ods, csv',
                'constraints' => new Assert\File([
                    'mimeTypes' => [
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                        'application/vnd.oasis.opendocument.spreadsheet',
                        'text/csv',
                    ],
                    'mimeTypesMessage' => 'I formati supportati sono: OpenDocument spreadsheet document, Microsoft Excel (OpenXML), Microsoft Excel e CSV',
                    ]),
            ])
            ->add('importa', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var File */
            if (!$this->isGranted("ROLE_SUPER_ADMIN") && !$this->isGranted("ROLE_SUPERVISORE_CONTROLLI")) {
                $this->addFlash('error', "Non sei abilitato ad eseguira l'operazione");
                return $this->redirectToRoute("home_controlli_stabilita");
            }
            $file = $form->get('file')->getData();
            try {
                $progettiDaImportare = $this->effettuaParsingExcel($file, $tipoControllo);
                $em = $this->getEm();
                $numeroControlliInseriti = 0;
                foreach ($progettiDaImportare as $richiesta) {
                    $controllo = new ControlloProgetto($richiesta);
                    $controllo->setTipologia($tipoControllo);
                    $controllo->setCampione($campione);
                    $em->persist($controllo);
                    $numeroControlliInseriti++;
                }
                $em->flush();
                $this->addSuccess("Inseriti $numeroControlliInseriti controlli");
                return $this->redirectToRoute("home_controlli_stabilita");
            } catch (\Exception $e) {
                $this->get('logger')->error('Errore durante l\'elaborazione del file');
            }
        }
        return $this->render('AttuazioneControlloBundle:ControlliStabilita:importaCampione.html.twig', ['menu_principale' => 'campioni', 'form' => $form->createView()]);
    }

    private function effettuaParsingExcel(File $file, $tipoControllo): array {
        /** @var SpreadsheetFactory */
        $spreadSheetFactory = $this->get('phpoffice.spreadsheet');
        $spreadSheet = $spreadSheetFactory->readFile($file);
        $sheet = $spreadSheet->getActiveSheet();
        $valoriSheet = $sheet->rangeToArray("A2:A{$sheet->getHighestRow()}");
        $valoriSheetNormalizzati = \array_map('reset', $valoriSheet);
        $valoriSheetValidi = \array_filter($valoriSheetNormalizzati, 'is_numeric');

        //Filtro in funzione dei dati presenti su DB
        $dql = "SELECT r
            FROM RichiesteBundle:Richiesta r
            LEFT JOIN r.controlli cp WITH (cp.tipologia = '$tipoControllo')
            WHERE r IN (:progetti) AND cp.id IS NULL  ";
        $progettiControlloNonPresente = $this->getEm()
            ->createQuery($dql)
            ->setParameter('progetti', $valoriSheetValidi)
            ->getResult();

        return $progettiControlloNonPresente;
    }

    private function effettuaParsingExcelSimple(File $file): array {
        /** @var SpreadsheetFactory */
        $spreadSheetFactory = $this->get('phpoffice.spreadsheet');
        $spreadSheet = $spreadSheetFactory->readFile($file);
        $sheet = $spreadSheet->getActiveSheet();
        $valoriSheet = $sheet->rangeToArray("A2:A{$sheet->getHighestRow()}");
        $valoriSheetNormalizzati = \array_map('reset', $valoriSheet);
        $valoriSheetValidi = \array_filter($valoriSheetNormalizzati, 'is_numeric');

        return $valoriSheetValidi;
    }

    /**
     * @Route("/estrazioni/estrazione_universo_campionario/{id_campione}", name="estrazione_universo_campionario")
     */
    public function estrazioniUniversoCampionario($id_campione) {
        $em = $this->getEm();
        $procedura = $em->getRepository("SfingeBundle\Entity\Procedura")->find(4);
        $campione = $em->getRepository('AttuazioneControlloBundle\Entity\Controlli\ControlloCampione')->findOneById($id_campione);
        return $this->get("gestore_controlli_stabilita")->getGestore($procedura)->estrazioneUniversoCampionario($campione);
    }

}
