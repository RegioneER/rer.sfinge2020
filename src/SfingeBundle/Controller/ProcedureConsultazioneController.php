<?php

namespace SfingeBundle\Controller;

use BaseBundle\Controller\BaseController;
use DocumentoBundle\Entity\DocumentoFile;
use DocumentoBundle\Entity\TipologiaDocumento;
use DocumentoBundle\Form\Type\DocumentoFileType;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use SfingeBundle\Entity\AssistenzaTecnica;
use SfingeBundle\Entity\Bando;
use SfingeBundle\Entity\DocumentoProcedura;
use SfingeBundle\Entity\ManifestazioneInteresse;
use SfingeBundle\Form\Entity\RicercaProcedura;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use BaseBundle\Annotation\ControlloAccesso;
use SfingeBundle\Entity\IngegneriaFinanziaria;
use SfingeBundle\Entity\Acquisizioni;
use SfingeBundle\Form\BandoType;

class ProcedureConsultazioneController extends BaseController
{
    /**
     * @Route("/elenco_atti_amministrativi/{sort}/{direction}/{page}", defaults={"sort" = "s.id", "direction" = "asc", "page" = "1"}, name="elenco_atti_amministrativi")
     * @Template("SfingeBundle:Procedura:elencoProcedure.html.twig")
     * @Menuitem(menuAttivo = "elencoAtti")
     * @PaginaInfo(titolo="Elenco procedure",sottoTitolo="pagina per la gestione delle procedure operative censite a sistema")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Atti Amministrativi")})
     */
    public function elencoProcedureAction() {
        $datiRicerca = new RicercaProcedura();
		$datiRicerca->setUtente($this->getUser());
		$datiRicerca->setAdmin($this->isAdmin());
		
		$em = $this->getEm();
		$responsabili = $em->getRepository("SfingeBundle\Entity\Utente")->cercaUtentiPaDTO();
		$datiRicerca->setResponsabili($responsabili);
		
        $risultato = $this->get("ricerca")->ricerca($datiRicerca);
				
		$dati = array(
			'procedure' => $risultato["risultato"],
			"form_ricerca_procedure" => $risultato["form_ricerca"],
			"filtro_attivo" => $risultato["filtro_attivo"],
			"numeroRichiesteBando" => array( // hanno chiesto sta cosa per il bando professionisti..vogliono vedere le richieste inoltrate per finestra temporale
				'primaFinestra' => $this->getEm()->getRepository("RichiesteBundle:Richiesta")->getCountRichiesteInoltrateProcedura(26, 1),
				'secondaFinestra' => $this->getEm()->getRepository("RichiesteBundle:Richiesta")->getCountRichiesteInoltrateProcedura(26, 2),
				'professionisti_2018' => $this->getEm()->getRepository("RichiesteBundle:Richiesta")->getCountRichiesteInoltrateProcedura(66),
				'sostegno_2018_I' => $this->getEm()->getRepository("RichiesteBundle:Richiesta")->getCountRichiesteInoltrateProcedura(61, 1),
				'sostegno_2018_II' => $this->getEm()->getRepository("RichiesteBundle:Richiesta")->getCountRichiesteInoltrateProcedura(61, 2),
				'centri_storici_I' => $this->getEm()->getRepository("RichiesteBundle:Richiesta")->getCountRichiesteInoltrateProcedura(95, 1),
                'professionisti_2019' => $this->getEm()->getRepository("RichiesteBundle:Richiesta")->getCountRichiesteInoltrateProcedura(100),
			)
		);
		
        return $dati;
    }

    /**
     * @Route("/elenco_atti_amministrativi_pulisci", name="elenco_atti_amministrativi_pulisci")
     */
    public function elencoProcedurePulisciAction() {
        $this->get("ricerca")->pulisci(new RicercaProcedura());
        return $this->redirectToRoute("elenco_atti_amministrativi");
    }

    /**
     * @Route("/atto_amministrativo_visualizza/{id_procedura}", name="atto_amministrativo_visualizza")
     */
    public function visualizzaProceduraAction($id_procedura) {
        $em = $this->getDoctrine()->getManager();
        $procedura = $em->getRepository('SfingeBundle:Procedura')->findOneById($id_procedura);
        if(\is_null($procedura)){
            $this->addFlash('error', "Atto amministrativo non trovato");
            return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
        }
        if($procedura instanceof ManifestazioneInteresse){
            return $this->redirect($this->generateUrl('manifestazione_interesse_visualizza', array('id_manifestazione' => $id_procedura)));
        }
        if($procedura instanceof Bando){
            return $this->redirect($this->generateUrl('bando_visualizza', array('id_bando' => $id_procedura)));
        }

        if($procedura instanceof AssistenzaTecnica){
            return $this->redirect($this->generateUrl('assistenza_tecnica_visualizza', array('id_assistenza' => $id_procedura)));
        }
		
        if($procedura instanceof IngegneriaFinanziaria){
            return $this->redirect($this->generateUrl('ingegneria_finanziaria_visualizza', array('id_ingegneria_finanziaria' => $id_procedura)));
        }
		
		 if($procedura instanceof Acquisizioni ){
            return $this->redirect($this->generateUrl('acquisizioni_visualizza', array('id_procedura' => $id_procedura)));
        }
    }

    /**
     * @Route("/bando_visualizza/{id_bando}", name="bando_visualizza")
     * @Template("SfingeBundle:Procedura:bando.html.twig")
     * @PaginaInfo(titolo="Visualizza procedura",sottoTitolo="pagina per visualizzare i dati della procedura")
	 * @Menuitem(menuAttivo = "elencoAtti")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Atti Amministrativi", route="elenco_atti_amministrativi"), @ElementoBreadcrumb(testo="visualizza procedura")})
	 * @ControlloAccesso(contesto="procedura", classe="SfingeBundle:Procedura", opzioni={"id" = "id_bando"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function visualizzaBandoAction($id_bando) {

        $em = $this->getDoctrine()->getManager();
        $bando = $em->getRepository('SfingeBundle:Procedura')->findOneById($id_bando);
        if(\is_null($bando)){
            $this->addFlash('error', "Bando non trovato");
            return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
        }

        $options['disabled'] = true;
        $options["url_indietro"] = $this->generateUrl("elenco_atti_amministrativi");

        $form = $this->createForm(BandoType::class , $bando, $options);

        $form_params["form"] = $form->createView();
        $form_params["bando"] = $bando;
        $form_params["proceduraProgrammi"]= $em->getRepository('SfingeBundle:ProgrammaProcedura')->findBy(array('procedura' => $bando));

        return $form_params;
    }

    /**
     * @Route("/manifestazione_interesse_visualizza/{id_manifestazione}", name="manifestazione_interesse_visualizza")
     * @Template("SfingeBundle:Procedura:manifestazione_interesse.html.twig")
     * @PaginaInfo(titolo="Visualizza manifestazione d'interesse",sottoTitolo="pagina per visualizzare i dati della manifestazione d'interesse selezionata")
	 * @Menuitem(menuAttivo = "elencoAtti")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Atti Amministrativi", route="elenco_atti_amministrativi"), @ElementoBreadcrumb(testo="visualizza manifestazione")})
	 * @ControlloAccesso(contesto="procedura", classe="SfingeBundle:Procedura", opzioni={"id" = "id_manifestazione"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function visualizzaManifestazioneAction($id_manifestazione) {

        $em = $this->getDoctrine()->getManager();
        $manifestazione = $em->getRepository('SfingeBundle:Procedura')->findOneById($id_manifestazione);
        if(\is_null($manifestazione)){
            $this->addFlash('error', "Manifestazione d'interesse non trovata");
            return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
        }

        $options["disabled"] = true;
        $options["url_indietro"] = $this->generateUrl("elenco_atti_amministrativi");

        $form = $this->createForm('SfingeBundle\Form\ManifestazioneInteresseType', $manifestazione, $options);
        $proceduraProgrammi = $em->getRepository('SfingeBundle:ProgrammaProcedura')->findBy(array('procedura' => $manifestazione));
        $form_params = array(
            "form" => $form->createView(),
            "manifestazione" => $manifestazione,
            "proceduraProgrammi" => $proceduraProgrammi,
        );
        return $form_params;
    }

    /**
     * @Route("/assistenza_tecnica_visualizza/{id_assistenza}", name="assistenza_tecnica_visualizza")
     * @Template("SfingeBundle:Procedura:assistenza_tecnica.html.twig")
     * @PaginaInfo(titolo="Visualizza assistenza tecnica",sottoTitolo="pagina per visualizzare i dati dell'assistenza tecnica selezionata")
	 * @Menuitem(menuAttivo = "elencoAtti")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Atti Amministrativi", route="elenco_atti_amministrativi"), @ElementoBreadcrumb(testo="visualizza assistenza tecnica")})
	 * @ControlloAccesso(contesto="procedura", classe="SfingeBundle:Procedura", opzioni={"id" = "id_assistenza"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function visualizzaAssistenzaTecnicaAction($id_assistenza) {
        $em = $this->getDoctrine()->getManager();
        $assistenza = $em->getRepository(AssistenzaTecnica::class)->findOneById($id_assistenza);
        if(\is_null($assistenza)){
            $this->addFlash('error', "Assistenza tecnica non trovata");
            return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
        }

        $options["disabled"] = true;
        $options["url_indietro"] = $this->generateUrl("elenco_atti_amministrativi");
        $options["TIPOLOGIA_DOCUMENTO"] = $this->trovaDaCostante("DocumentoBundle:TipologiaDocumento", TipologiaDocumento::ALTRO);
		
        $form = $this->createForm('SfingeBundle\Form\AssistenzaTecnicaType', $assistenza, $options);

        $form_params["form"] = $form->createView();
        $form_params["assistenza"] = $assistenza;
        $form_params["lettura"] = true;
        return $form_params;
    }

    /**
     * @Route("/ingegneria_finanziaria_visualizza/{id_ingegneria_finanziaria}", name="ingegneria_finanziaria_visualizza")
     * @Template("SfingeBundle:Procedura:ingegneria_finanziaria.html.twig")
     * @PaginaInfo(titolo="Visualizza ingegneria finanziaria",sottoTitolo="pagina per visualizzare i dati dell'ingegneria finanziaria")
	 * @Menuitem(menuAttivo = "elencoAtti")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Atti Amministrativi", route="elenco_atti_amministrativi"), @ElementoBreadcrumb(testo="visualizza ingegneria finanziaria")})
	 * @ControlloAccesso(contesto="procedura", classe="SfingeBundle:Procedura", opzioni={"id" = "id_ingegneria_finanziaria"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function visualizzaIngegneriaFinanziariaAction($id_ingegneria_finanziaria) {

        $em = $this->getDoctrine()->getManager();
        $ingegneria_finanziaria = $em->getRepository('SfingeBundle:Procedura')->findOneById($id_ingegneria_finanziaria);
        if(\is_null($ingegneria_finanziaria)){
            $this->addFlash('error', "Ingegneria finanziaria non trovata");
            return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
        }

        $options["disabled"] = true;
        $options["url_indietro"] = $this->generateUrl("elenco_atti_amministrativi");
        $options["TIPOLOGIA_DOCUMENTO"] = $this->trovaDaCostante("DocumentoBundle:TipologiaDocumento", TipologiaDocumento::ALTRO);

        $form = $this->createForm('SfingeBundle\Form\IngegneriaFinanziariaType', $ingegneria_finanziaria, $options);

        $form_params["form"] = $form->createView();
        $form_params["ingegneria_finanziaria"] = $ingegneria_finanziaria;
        $form_params["lettura"] = true;
        return $form_params;
    }
	
    /**
     *
     * @Route("/elenco_documenti_atto_amministrativo/{id_procedura}/{sort}/{direction}/{page}", defaults={"sort" = "s.id", "direction" = "asc", "page" = "1"}, name="atto_amministrativo_documenti")
     * @Template("SfingeBundle:Procedura:elencoDocumentiProcedure.html.twig")
     * @Menuitem(menuAttivo = "elencoAtti")
     * @PaginaInfo(titolo="Elenco documenti dell'atto amministrativo",sottoTitolo="pagina per gestione dei documenti dell'atto amministrativo selezionato")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Atti Amministrativi", route="elenco_atti_amministrativi"), @ElementoBreadcrumb(testo="elenco documenti atto")})
     */
    public function elencoDocumentiProcedureAction($id_procedura) {
        $em = $this->get('doctrine.orm.entity_manager');
        $procedura = $em->getRepository("SfingeBundle:Procedura")->find($id_procedura);
        if(\is_null($procedura)){
            $this->addFlash('error', "Atto amministrativo non trovato");
            return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
        }

        $form_params["documenti"] = $procedura->getDocumenti();
        $form_params["procedura"] = $procedura;
        $form_params["tipo"] = $procedura->getTipoProcedura();

        return $form_params;
    }  

    /**
     * @Route("/visualizza_procedura_programma/{id_procedura_programma}", name="visualizza_procedura_programma")
     * @ControlloAccesso(contesto="procedura", classe="SfingeBundle:Procedura", opzioni={"id" = "id_bando"}, azione=\SfingeBundle\Security\ProceduraVoter::WRITE)
     * @Template("SfingeBundle:Procedura:modifica_programma_procedura.html.twig")
     * @Menuitem(menuAttivo = "elencoAtti")
     * @PaginaInfo(titolo="Visualizza associazione programma",sottoTitolo="pagina per visualizzae il programma associato alla procedura")
     */
     public function visualizzaProceduraProgramma($id_procedura_programma) {
        $em = $this->getEm();
        $proceduraProgramma = $em->getRepository('SfingeBundle:ProgrammaProcedura')->find($id_procedura_programma);
        $procedura = $proceduraProgramma->getProcedura();
        $url = null;
        switch (get_class($procedura)) {
            case 'SfingeBundle\Entity\Bando':
                $url = $this->generateUrl('bando_visualizza', array('id_bando' => $procedura->getId()));
                break;
            case 'SfingeBundle\Entity\ManifestazioneInteresse':
                $url = $this->generateUrl('manifestazione_interesse_visualizza', array('id_manifestazione' => $procedura->getId()));
                break;
            default:
                throw new \BaseBundle\Exception\SfingeException('Errore: tipo di procedura non previsto');
        }

        $form = $this->createForm('SfingeBundle\Form\ProgrammaProceduraType', $proceduraProgramma, array(
            "url_indietro" => $url,
            "disabled" => true,
        ));
       
        return array(
                'form' => $form->createView(),
                "bando" => $procedura,
            );
    }
	
	/**
     * @Route("/acquisizioni_visualizza/{id_procedura}", name="acquisizioni_visualizza")
     * @Template("SfingeBundle:Procedura:acquisizione.html.twig")
     * @PaginaInfo(titolo="Visualizza procedura acquisizione",sottoTitolo="pagina per visualizzare i dati della procedura acquisizione selezionata")
	 * @Menuitem(menuAttivo = "elencoAtti")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Atti Amministrativi", route="elenco_atti_amministrativi"), @ElementoBreadcrumb(testo="visualizza assistenza tecnica")})
	 * @ControlloAccesso(contesto="procedura", classe="SfingeBundle:Procedura", opzioni={"id" = "id_procedura"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function visualizzaAcquisizioniAction($id_procedura) {

        $em = $this->getDoctrine()->getManager();
        $procedura = $em->getRepository('SfingeBundle:Procedura')->findOneById($id_procedura);
        if(\is_null($procedura)){
            $this->addFlash('error', "Procedura acquisizioni non trovata");
            return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
        }

        $options["readonly"] = true;
        $options["em"] = $this->getEm();
        $options["url_indietro"] = $this->generateUrl("elenco_atti_amministrativi");
        $options["dataAsse"] = $procedura->getAsse();
        $options["dataObiettivoSpecifico"] = $procedura->getObiettiviSpecifici();
		
        $form = $this->createForm('SfingeBundle\Form\AcquisizioniType', $procedura, $options);

        $form_params["form"] = $form->createView();
        $form_params["procedura"] = $procedura;
        $form_params["lettura"] = true;
        return $form_params;
    }




}
