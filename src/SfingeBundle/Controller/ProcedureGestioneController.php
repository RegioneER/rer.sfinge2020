<?php

namespace SfingeBundle\Controller;

use BaseBundle\Controller\BaseController;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use SfingeBundle\Entity\AssistenzaTecnica;
use SfingeBundle\Entity\Bando;
use SfingeBundle\Entity\DocumentoProcedura;
use SfingeBundle\Entity\ManifestazioneInteresse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use BaseBundle\Annotation\ControlloAccesso;
use SfingeBundle\Entity\IngegneriaFinanziaria;
use DocumentoBundle\Component\ResponseException;
use SfingeBundle\Entity\Acquisizioni;
use SfingeBundle\Entity\Asse;
use SfingeBundle\Entity\ObiettivoSpecifico;
use SfingeBundle\Form\BandoType;
use Symfony\Component\HttpFoundation\Request;

class ProcedureGestioneController extends BaseController
{
    /**
     * @Route("/crea_bando", name="crea_bando")
     * @Template("SfingeBundle:Procedura:bando.html.twig")
     * @Menuitem(menuAttivo = "creaBando")
     * @PaginaInfo(titolo="Nuova procedura",sottoTitolo="pagina per creare una nuova procedura")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Atti Amministrativi", route="elenco_atti_amministrativi"), @ElementoBreadcrumb(testo="Crea procedura")})
     */
    public function creaBandoAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $bando = new Bando();

        $assi = $em->getRepository(Asse::class)->getAssi($this->getUser());

        if (count($assi) == 0) {
            $this->addFlash('error', 'Nessun asse associato');

            return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
        }

        $options['url_indietro'] = $this->generateUrl('elenco_atti_amministrativi');
        $options['assi'] = $assi;
        $bando->setCodiceCci('2014IT16RFOP008');
        $bando->setTitolo('');

        $form = $this->createForm(BandoType::class, $bando, $options);

        $form->handleRequest($request);
        if ($form->isValid() && $form->isSubmitted()) {
            try {
                $fase = $this->getEm()->getRepository('SfingeBundle:Fase')->findOneByCodice('PRE');
                $bando->setFase($fase);
                $bando->setVisibileInCorso(true);              
                $em->persist($bando);
                $em->flush();
                $this->addFlash('success', 'Modifiche salvate correttamente');

                return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
            } catch (ResponseException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        $form_params['form'] = $form->createView();
        $form_params['bando'] = $bando;
        $form_params['proceduraProgrammi'] = $em->getRepository('SfingeBundle:ProgrammaProcedura')->findBy(array('procedura' => $bando));

        return $form_params;
    }

    /**
     * @Route("/crea_manifestazione_interesse", name="crea_manifestazione_interesse")
     * @Template("SfingeBundle:Procedura:manifestazione_interesse.html.twig")
     * @Menuitem(menuAttivo = "creaManifestazioneInteresse")
     * @PaginaInfo(titolo="Nuova Manifestazione d'interesse",sottoTitolo="pagina per creare una nuova manifestazione d'interesse")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Atti Amministrativi", route="elenco_atti_amministrativi"), @ElementoBreadcrumb(testo="Crea manifestazione interesse")})
     */
    public function creaManifestazioneInteresseAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $manifestazione = new ManifestazioneInteresse();
                $manifestazione->setTitolo('');


        $assi = $em->getRepository("SfingeBundle\Entity\Asse")->getAssi($this->getUser());

        if (count($assi) == 0) {
            $this->addFlash('error', 'Nessun asse associato');

            return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
        }

        $options['url_indietro'] = $this->generateUrl('elenco_atti_amministrativi');
        $options['assi'] = $assi;

        $form = $this->createForm('SfingeBundle\Form\ManifestazioneInteresseType', $manifestazione, $options);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $fase = $this->getEm()->getRepository('SfingeBundle:Fase')->findOneByCodice('PRE');
                $manifestazione->setFase($fase);
                $manifestazione->setVisibileInCorso(true);
                $em->persist($manifestazione);
                $em->flush();
                $this->addFlash('success', 'Modifiche salvate correttamente');

                return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        $form_params['form'] = $form->createView();
        $form_params['manifestazione'] = $manifestazione;
		$form_params['proceduraProgrammi'] = $em->getRepository('SfingeBundle:ProgrammaProcedura')->findBy(array('procedura' => $manifestazione));


        return $form_params;
    }

    /**
     * @Route("/atto_amministrativo_modifica/{id_procedura}", name="atto_amministrativo_modifica")
     */
    public function modificaProceduraAction($id_procedura)
    {
        $em = $this->getDoctrine()->getManager();
        $procedura = $em->getRepository('SfingeBundle:Procedura')->findOneById($id_procedura);
        if (\is_null($procedura)) {
            $this->addFlash('error', 'Atto amministrativo non trovato');

            return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
        }
        if ($procedura instanceof ManifestazioneInteresse) {
            return $this->redirect($this->generateUrl('manifestazione_interesse_modifica', array('id_manifestazione' => $id_procedura)));
        }
        if ($procedura instanceof Bando) {
            return $this->redirect($this->generateUrl('bando_modifica', array('id_bando' => $id_procedura)));
        }

        if ($procedura instanceof AssistenzaTecnica) {
            return $this->redirect($this->generateUrl('assistenza_tecnica_modifica', array('id_assistenza' => $id_procedura)));
        }

        if ($procedura instanceof IngegneriaFinanziaria) {
            return $this->redirect($this->generateUrl('modifica_ingegneria_finanziaria', array('id_ingegneria_finanziaria' => $id_procedura)));
        }
		
		if ($procedura instanceof Acquisizioni) {
            return $this->redirect($this->generateUrl('acquisizioni_modifica', array('id_acquisizione' => $id_procedura)));
        }
    }

    /**
     * @Route("/bando_modifica/{id_bando}", name="bando_modifica")
     * @Template("SfingeBundle:Procedura:bando.html.twig")
     * @PaginaInfo(titolo="Modifica Bando",sottoTitolo="pagina per modificare i dati del bando selezionato")
     * @Menuitem(menuAttivo = "elencoAtti")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Atti Amministrativi", route="elenco_atti_amministrativi"), @ElementoBreadcrumb(testo="modifica bando")})
     * @ControlloAccesso(contesto="procedura", classe="SfingeBundle:Procedura", opzioni={"id" = "id_bando"}, azione=\SfingeBundle\Security\ProceduraVoter::WRITE)
     */
    public function modificaBandoAction(Request $request, $id_bando) {
        $em = $this->getDoctrine()->getManager();
        $bando = $em->getRepository('SfingeBundle:Procedura')->find($id_bando);

        if (\is_null($bando)) {
            $this->addFlash('error', 'Bando non trovato');

            return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
        }
        
        $assi = $em->getRepository("SfingeBundle\Entity\Asse")->getAssi($this->getUser());
        if (count($assi) == 0) {
            $this->addFlash('error', 'Nessun asse associato');

            return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
        }
        
        $form = $this->createForm(BandoType::class, $bando, [
            'assi' => $assi,
            'url_indietro' => $this->generateUrl('elenco_atti_amministrativi'),
        ]);
        $nuovoProgramma = new \SfingeBundle\Entity\ProgrammaProcedura($bando);
        $formProgramma = $this->createForm('SfingeBundle\Form\ProgrammaProceduraType', $nuovoProgramma, array(
            'url_indietro' => $this->generateUrl('elenco_atti_amministrativi'),
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($bando);
                $em->flush();
                $this->addFlash('success', 'Modifiche salvate correttamente');

                return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }
        $formProgramma->handleRequest($request);
        if ($formProgramma->isSubmitted() && $formProgramma->isValid()) {
            try {
                $em->persist($nuovoProgramma);
                $em->flush();
                $this->addFlash('success', 'Modifiche salvate correttamente');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }
        $proceduraProgrammi = $em->getRepository('SfingeBundle:ProgrammaProcedura')->findBy(array('procedura' => $bando));

        $form_params = array(
            'form' => $form->createView(),
            'bando' => $bando,
            'formProgramma' => $formProgramma->createView(),
            'proceduraProgrammi' => $proceduraProgrammi,
        );

        return $form_params;
    }

    /**
     * @Route("/elimina_procedura_programma/{id_procedura_programma}", name="elimina_procedura_programma")
     * @ControlloAccesso(contesto="procedura", classe="SfingeBundle:Procedura", opzioni={"id" = "id_bando"}, azione=\SfingeBundle\Security\ProceduraVoter::WRITE)
     */
    public function eliminaProceduraProgramma($id_procedura_programma)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getCurrentRequest();

        $proceduraProgramma = $em->getRepository('SfingeBundle:ProgrammaProcedura')->findOneById($id_procedura_programma);
        if (!$proceduraProgramma) {
            throw new \BaseBundle\Exception\SfingeException('Errore: programma collegato non trovato');
        }
        $procedura = $proceduraProgramma->getProcedura();

        $url = null;
        switch (get_class($procedura)) {
            case 'SfingeBundle\Entity\Bando':
            $url = $this->redirectToRoute('bando_modifica', array('id_bando' => $procedura->getId()));
            break;
            case 'SfingeBundle\Entity\ManifestazioneInteresse':
                $url = $this->redirectToRoute('manifestazione_interesse_modifica', array('id_manifestazione' => $procedura->getId()));
                break;
                default:
                throw new \BaseBundle\Exception\SfingeException('Errore: tipo di procedura non previsto');
            }

        try {
            //Effettuo verifica sicurezza token CSRF
            $this->checkCsrf('token', 'csrf');

            $em->remove($proceduraProgramma);
            $em->flush();
            $this->addFlash('success', 'Modifiche salvate correttamente');
        } catch (\Exception $e) {
            $this->container->get('monolog.logger.schema31')->error($e->getMessage());
            $this->addFlash('error', 'Errore durante il salvataggio delle informazioni');
        }

        return $url;
    }

    /**
     * @Route("/modifica_procedura_programma/{id_procedura_programma}", name="modifica_procedura_programma")
     * @ControlloAccesso(contesto="procedura", classe="SfingeBundle:Procedura", opzioni={"id" = "id_bando"}, azione=\SfingeBundle\Security\ProceduraVoter::WRITE)
     * @Template("SfingeBundle:Procedura:modifica_programma_procedura.html.twig")
     * @Menuitem(menuAttivo = "elencoAtti")
     * @PaginaInfo(titolo="Modifica associazione programma",sottoTitolo="pagina per modificare il programma associato alla procedura")
     */
    public function modificaProceduraProgramma($id_procedura_programma)
    {
        $request = $this->getCurrentRequest();
        $em = $this->getEm();

        $proceduraProgramma = $em->getRepository('SfingeBundle:ProgrammaProcedura')->find($id_procedura_programma);
        $procedura = $proceduraProgramma->getProcedura();
        $url = null;
        switch (get_class($procedura)) {
            case 'SfingeBundle\Entity\Bando':
                $url = $this->generateUrl('bando_modifica', array('id_bando' => $procedura->getId()));
                break;
            case 'SfingeBundle\Entity\ManifestazioneInteresse':
                $url = $this->generateUrl('manifestazione_interesse_modifica', array('id_manifestazione' => $procedura->getId()));
                break;
            default:
                throw new \BaseBundle\Exception\SfingeException('Errore: tipo di procedura non previsto');
        }

        $form = $this->createForm('SfingeBundle\Form\ProgrammaProceduraType', $proceduraProgramma, array(
            'url_indietro' => $url,
            'disabled' => false,
        ));
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $em->persist($proceduraProgramma);
                    $em->flush();
                    $this->addFlash('success', 'Modifiche salvate correttamente');
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }

        return array(
                'form' => $form->createView(),
                'bando' => $procedura,
            );
    }

    /**
     * @Route("/manifestazione_interesse_modifica/{id_manifestazione}", name="manifestazione_interesse_modifica")
     * @Template("SfingeBundle:Procedura:manifestazione_interesse.html.twig")
     * @PaginaInfo(titolo="Modifica Manifestazione d'Interesse",sottoTitolo="pagina per modificare i dati della manifestazione d'interesse selezionata")
     * @Menuitem(menuAttivo = "elencoAtti")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Atti Amministrativi", route="elenco_atti_amministrativi"), @ElementoBreadcrumb(testo="modifica manifestazione interesse")})
     */
    public function modificaManifestazioneInteresseAction(Request $request, $id_manifestazione)
    {
        $em = $this->getDoctrine()->getManager();
        $manifestazione_interesse = $em->getRepository('SfingeBundle:Procedura')->find($id_manifestazione);
        if (\is_null($manifestazione_interesse)) {
            $this->addFlash('error', "Manifestazione d'interesse non trovata");

            return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
        }
        $nuovoProgramma = new \SfingeBundle\Entity\ProgrammaProcedura($manifestazione_interesse);

        $assi = $em->getRepository("SfingeBundle\Entity\Asse")->getAssi($this->getUser());

        if (count($assi) == 0) {
            $this->addFlash('error', 'Nessun asse associato');

            return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
        }

        $options['assi'] = $assi;
        $options['readonly'] = false;
        $options['em'] = $this->getEm();
        $options['url_indietro'] = $this->generateUrl('elenco_atti_amministrativi');

        $form = $this->createForm('SfingeBundle\Form\ManifestazioneInteresseType', $manifestazione_interesse, $options);
        $formProgramma = $this->createForm('SfingeBundle\Form\ProgrammaProceduraType', $nuovoProgramma, array(
            'url_indietro' => $this->generateUrl('elenco_atti_amministrativi'),
        ));

        $form->handleRequest($request);
        $formProgramma->handleRequest($request);
        if ($form->isValid() && $form->isSubmitted()) {
            try {
                $em->persist($manifestazione_interesse);
                $em->flush();
                $this->addFlash('success', 'Modifiche salvate correttamente');

                return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
            } catch (\Exception $e) {
                $this->container->get('monolog.logger.schema31')->error($e->getMessage());
                $this->addFlash('error', 'Errore durante il salvataggio delle informazioni');
            }
        }
        if ($formProgramma->isSubmitted() && $formProgramma->isValid()) {
            try {
                $em->persist($nuovoProgramma);
                $em->flush();

                $this->addFlash('success', 'Informazioni inserite correttamente');
            } catch (\Exception $ex) {
                $this->container->get('monolog.logger.schema31')->error($ex->getMessage());
                $this->addFlash('error', 'Errore durante il salvataggio delle informazioni');
            }
        }

        $proceduraProgrammi = $em->getRepository('SfingeBundle:ProgrammaProcedura')->findBy(array('procedura' => $manifestazione_interesse));

        $form_params = array(
            'form' => $form->createView(),
            'manifestazione_interesse' => $manifestazione_interesse,
            'proceduraProgrammi' => $proceduraProgrammi,
            'formProgramma' => $formProgramma->createView(),
        );

        return $form_params;
    }

    /**
     * @Route("/html/asse/{asse_id}/obiettivi_specifici", name="obiettivi_specifici_asse_options")
     */
    public function obiettiviSpecificiByAsseAction($asse_id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $r = $em->getRepository('SfingeBundle\Entity\ObiettivoSpecifico');
        $html = "<option value=''>-</option>\n";
        $os = $r->findBy(array('asse' => $asse_id));

        foreach ($os as $obiettivo) {
            $html .= "<option value='{$obiettivo->getId()}' >{$obiettivo->getDescrizione()}</option>\n";
        }

        return new Response($html);
    }

    /**
     * @Route("/html/obiettivo_specifico/{obiettivo_specifico_id}/azioni", name="azioni_obiettivo_specifico_options")
     */
    public function azioniByObiettivoSpecificoAction($obiettivo_specifico_id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $r = $em->getRepository('SfingeBundle\Entity\Azione');
        //$html = "<option value=''>-</option>\n";
        $html = '';
        $azioni = $r->findBy(array('obiettivo_specifico' => $obiettivo_specifico_id));

        foreach ($azioni as $azione) {
            $html .= "<option value='{$azione->getId()}' >{$azione->__toString()}</option>\n";
        }

        return new Response($html);
    }

    /**
     * @Route("/elenco_documenti_atto_amministrativo/{id_procedura}/{sort}/{direction}/{page}", defaults={"sort" = "s.id", "direction" = "asc", "page" = "1"}, name="modifica_atto_amministrativo_documenti")
     * @Template("SfingeBundle:Procedura:elencoDocumentiProcedure.html.twig")
     * @Menuitem(menuAttivo = "elencoAtti")
     * @PaginaInfo(titolo="Elenco documenti dell'atto amministrativo",sottoTitolo="pagina per gestione dei documenti dell'atto amministrativo selezionato")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Atti Amministrativi", route="elenco_atti_amministrativi"), @ElementoBreadcrumb(testo="elenco documenti atto")})
     */
    public function elencoDocumentiProcedureAction($id_procedura)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $procedura = $em->getRepository('SfingeBundle:Procedura')->find($id_procedura);
        if (\is_null($procedura)) {
            $this->addFlash('error', 'Atto amministrativo non trovato');

            return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
        }
        $request = $this->getCurrentRequest();

        $options['url_indietro'] = $this->generateUrl('elenco_atti_amministrativi');
        $documento = new DocumentoProcedura();
        $form = $this->createForm('SfingeBundle\Form\DocumentoProceduraType', $documento, $options);

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                try {
                    $documentoFile = $documento->getDocumento();
                    $this->get('documenti')->carica($documentoFile);
                    $documento->setProcedura($procedura);
                    $em->persist($documento);
                    $em->flush();
                    $this->addFlash('success', 'Documento salvato correttamente');
                } catch (ResponseException $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }

        $form_params['form'] = $form->createView();
        $form_params['documenti'] = $procedura->getDocumenti();
        $form_params['procedura'] = $procedura;
        $form_params['tipo'] = $procedura->getTipoProcedura();

        return $form_params;
    }
}
