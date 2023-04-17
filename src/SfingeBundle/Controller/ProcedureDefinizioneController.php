<?php

namespace SfingeBundle\Controller;

use BaseBundle\Annotation\ControlloAccesso;
use BaseBundle\Controller\BaseController;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ProcedureDefinizioneController extends BaseController {
    /**
     * @Route("/visualizza_piano_costi/{id_procedura}", name="visualizza_piano_costi")
     * @Template("SfingeBundle:Procedura:piano_costi.html.twig")
     * @PaginaInfo(titolo="Piano costi procedura", sottoTitolo="pagina per visualizzare il piano costi associato alla procedura")
     * @Menuitem(menuAttivo="elencoAtti")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Atti Amministrativi", route="elenco_atti_amministrativi"), @ElementoBreadcrumb(testo="Visualizza piano costi")})
     * @ControlloAccesso(contesto="procedura", classe="SfingeBundle:Procedura", opzioni={"id" : "id_procedura"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_procedura
     */
    public function visualizzaPianoCostiAction($id_procedura) {
        $em = $this->getDoctrine()->getManager();
        $procedura = $em->getRepository('SfingeBundle:Procedura')->find($id_procedura);
        if (\is_null($procedura)) {
            $this->addFlash('error', "Procedura non trovata");
            return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
        }

        $form_params["procedura"] = $procedura;

        return $form_params;
    }

    /**
     * @Route("/elimina_piano_costo/{id_piano_costo}", name="elimina_piano_costo")
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:PianoCosto", opzioni={"id" : "id_piano_costo"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_piano_costo
     */
    public function eliminaPianoCostoAction($id_piano_costo) {
        $em = $this->getEm();
        $piano_costo = $em->getRepository("RichiesteBundle\Entity\PianoCosto")->find($id_piano_costo);
        $procedura = $piano_costo->getProcedura();

        if (!$procedura->isModificabile()) {
            $this->addFlash('error', "Procedura non modificabile");
            return $this->redirect($this->generateUrl('visualizza_piano_costi', ["id_procedura" => $procedura->getId()]));
        }

        try {
            $em->remove($piano_costo);
            $em->flush();
            $this->addFlash('success', "Eliminazione effettuata correttamente");
        } catch (\Exception $e) {
            $this->get('logger')->error($e->getMessage());
            $this->addFlash('error', "Errore nel salvaltaggio delle informazioni");
        }

        return $this->redirect($this->generateUrl('visualizza_piano_costi', ["id_procedura" => $procedura->getId()]));
    }

    /**
     * @Route("/elimina_sezione_piano_costo/{id_sezione_piano_costo}", name="elimina_sezione_piano_costo")
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:SezionePianoCosto", opzioni={"id" : "id_sezione_piano_costo"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_sezione_piano_costo
     */
    public function eliminaSezionePianoCostoAction($id_sezione_piano_costo) {
        $em = $this->getEm();
        $sezione_piano_costo = $em->getRepository("RichiesteBundle\Entity\SezionePianoCosto")->find($id_sezione_piano_costo);
        $procedura = $sezione_piano_costo->getProcedura();

        if (!$procedura->isModificabile()) {
            $this->addFlash('error', "Procedura non modificabile");
            return $this->redirect($this->generateUrl('visualizza_piano_costi', ["id_procedura" => $procedura->getId()]));
        }

        try {
            $em->remove($sezione_piano_costo);
            $em->flush();
            $this->addFlash('success', "Eliminazione effettuata correttamente");
        } catch (\Exception $e) {
            $this->get('logger')->error($e->getMessage());
            $this->addFlash('error', "Errore nel salvaltaggio delle informazioni");
        }

        return $this->redirect($this->generateUrl('visualizza_piano_costi', ["id_procedura" => $procedura->getId()]));
    }

    /**
     * @Route("/aggiungi_sezione_piano_costo/{id_procedura}", name="aggiungi_sezione_piano_costo")
     * @Template("SfingeBundle:Procedura:sezione_piano_costo.html.twig")
     * @Menuitem(menuAttivo="elencoAtti")
     * @PaginaInfo(titolo="Aggiunta sezione piano costo", sottoTitolo="pagina per aggiungere una sezione piano costo ad una procedura")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Atti Amministrativi", route="elenco_atti_amministrativi"),
     *     @ElementoBreadcrumb(testo="Piano costi", route="visualizza_piano_costi", parametri={"id_procedura" : "id_procedura"}),
     * @ElementoBreadcrumb(testo="Aggiunta sezione piano costo") })
     * @ControlloAccesso(contesto="procedura", classe="SfingeBundle:Procedura", opzioni={"id" : "id_procedura"}, azione=\SfingeBundle\Security\ProceduraVoter::WRITE)
     * @param mixed $id_procedura
     */
    public function aggiungiSezionePianoCostoAction($id_procedura) {
        $em = $this->getEm();
        $procedura = $em->getRepository("SfingeBundle:Procedura")->find($id_procedura);
        if (\is_null($procedura)) {
            $this->addFlash('error', "Atto amministrativo non trovato");
            return $this->redirect($this->generateUrl('visualizza_piano_costi', ["id_procedura" => $procedura->getId()]));
        }

        if (!$procedura->isModificabile()) {
            $this->addFlash('error', "Procedura non modificabile");
            return $this->redirect($this->generateUrl('visualizza_piano_costi', ["id_procedura" => $procedura->getId()]));
        }

        $request = $this->getCurrentRequest();

        $options["url_indietro"] = $this->generateUrl('visualizza_piano_costi', ["id_procedura" => $procedura->getId()]);
        $sezione_piano_costo = new \RichiesteBundle\Entity\SezionePianoCosto();
        $sezione_piano_costo->setProcedura($procedura);
        $form = $this->createForm('SfingeBundle\Form\SezionePianoCostoType', $sezione_piano_costo, $options);

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                try {
                    $em->persist($sezione_piano_costo);
                    $em->flush();
                    $this->addFlash('success', "Inserimento effettuato correttamente");
                    return $this->redirect($this->generateUrl('visualizza_piano_costi', ["id_procedura" => $procedura->getId()]));
                } catch (\Exception $e) {
                    $this->get('logger')->error($e->getMessage());
                    $this->addFlash('error', "Errore nel salvaltaggio delle informazioni");
                }
            }
        }

        $form_params["form"] = $form->createView();

        return $form_params;
    }

    /**
     * @Route("/modifica_sezione_piano_costo/{id_procedura}/{id_sezione_piano_costo}", name="modifica_sezione_piano_costo")
     * @Template("SfingeBundle:Procedura:sezione_piano_costo.html.twig")
     * @Menuitem(menuAttivo="elencoAtti")
     * @PaginaInfo(titolo="Modifica sezione piano costo", sottoTitolo="pagina per modificare una sezione piano costo")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Atti Amministrativi", route="elenco_atti_amministrativi"),
     *     @ElementoBreadcrumb(testo="Piano costi", route="visualizza_piano_costi", parametri={"id_procedura" : "id_procedura"}),
     * @ElementoBreadcrumb(testo="Modifica sezione piano costo") })
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:SezionePianoCosto", opzioni={"id" : "id_sezione_piano_costo"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_procedura
     * @param mixed $id_sezione_piano_costo
     */
    public function modificaSezionePianoCostoAction($id_procedura, $id_sezione_piano_costo) {
        $em = $this->getEm();
        $sezione_piano_costo = $em->getRepository("RichiesteBundle:SezionePianoCosto")->find($id_sezione_piano_costo);
        $procedura = $sezione_piano_costo->getProcedura();

        if (!$procedura->isModificabile()) {
            $this->addFlash('error', "Procedura non modificabile");
            return $this->redirect($this->generateUrl('visualizza_piano_costi', ["id_procedura" => $procedura->getId()]));
        }

        $request = $this->getCurrentRequest();

        $options["url_indietro"] = $this->generateUrl('visualizza_piano_costi', ["id_procedura" => $procedura->getId()]);

        $form = $this->createForm('SfingeBundle\Form\SezionePianoCostoType', $sezione_piano_costo, $options);

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                try {
                    $em->flush();
                    $this->addFlash('success', "Modifica effettuata correttamente");
                    return $this->redirect($this->generateUrl('visualizza_piano_costi', ["id_procedura" => $procedura->getId()]));
                } catch (\Exception $e) {
                    $this->get('logger')->error($e->getMessage());
                    $this->addFlash('error', "Errore nel salvaltaggio delle informazioni");
                }
            }
        }

        $form_params["form"] = $form->createView();

        return $form_params;
    }

    /**
     * @Route("/aggiungi_piano_costo/{id_procedura}", name="aggiungi_piano_costo")
     * @Template("SfingeBundle:Procedura:piano_costo.html.twig")
     * @Menuitem(menuAttivo="elencoAtti")
     * @PaginaInfo(titolo="Aggiunta piano costo", sottoTitolo="pagina per aggiungere un piano costo ad una procedura")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Atti Amministrativi", route="elenco_atti_amministrativi"),
     *     @ElementoBreadcrumb(testo="Piano costi", route="visualizza_piano_costi", parametri={"id_procedura" : "id_procedura"}),
     * @ElementoBreadcrumb(testo="Aggiunta piano costo") })
     * @ControlloAccesso(contesto="procedura", classe="SfingeBundle:Procedura", opzioni={"id" : "id_procedura"}, azione=\SfingeBundle\Security\ProceduraVoter::WRITE)
     * @param mixed $id_procedura
     */
    public function aggiungiPianoCostoAction($id_procedura) {
        $em = $this->getEm();
        $procedura = $em->getRepository("SfingeBundle:Procedura")->find($id_procedura);
        if (\is_null($procedura)) {
            $this->addFlash('error', "Atto amministrativo non trovato");
            return $this->redirect($this->generateUrl('visualizza_piano_costi', ["id_procedura" => $procedura->getId()]));
        }

        if (!$procedura->isModificabile()) {
            $this->addFlash('error', "Procedura non modificabile");
            return $this->redirect($this->generateUrl('visualizza_piano_costi', ["id_procedura" => $procedura->getId()]));
        }

        $request = $this->getCurrentRequest();

        $options["url_indietro"] = $this->generateUrl('visualizza_piano_costi', ["id_procedura" => $procedura->getId()]);
        $piano_costo = new \RichiesteBundle\Entity\PianoCosto();
        $piano_costo->setProcedura($procedura);
        $form = $this->createForm('SfingeBundle\Form\PianoCostoType', $piano_costo, $options);

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                try {
                    $em->persist($piano_costo);
                    $em->flush();
                    $this->addFlash('success', "Inserimento effettuato correttamente");
                    return $this->redirect($this->generateUrl('visualizza_piano_costi', ["id_procedura" => $procedura->getId()]));
                } catch (\Exception $e) {
                    $this->get('logger')->error($e->getMessage());
                    $this->addFlash('error', "Errore nel salvaltaggio delle informazioni");
                }
            }
        }

        $form_params["form"] = $form->createView();

        return $form_params;
    }

    /**
     * @Route("/modifica_piano_costo/{id_procedura}/{id_piano_costo}", name="modifica_piano_costo")
     * @Template("SfingeBundle:Procedura:piano_costo.html.twig")
     * @Menuitem(menuAttivo="elencoAtti")
     * @PaginaInfo(titolo="Modifica piano costo", sottoTitolo="pagina per modificare un piano costo")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Atti Amministrativi", route="elenco_atti_amministrativi"),
     *     @ElementoBreadcrumb(testo="Piano costi", route="visualizza_piano_costi", parametri={"id_procedura" : "id_procedura"}),
     * @ElementoBreadcrumb(testo="Modifica piano costo") })
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:PianoCosto", opzioni={"id" : "id_piano_costo"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_procedura
     * @param mixed $id_piano_costo
     */
    public function modificaPianoCostoAction($id_procedura, $id_piano_costo) {
        $em = $this->getEm();
        $piano_costo = $em->getRepository("RichiesteBundle:PianoCosto")->find($id_piano_costo);
        $procedura = $piano_costo->getProcedura();

        if (!$procedura->isModificabile()) {
            $this->addFlash('error', "Procedura non modificabile");
            return $this->redirect($this->generateUrl('visualizza_piano_costi', ["id_procedura" => $procedura->getId()]));
        }

        $request = $this->getCurrentRequest();

        $options["url_indietro"] = $this->generateUrl('visualizza_piano_costi', ["id_procedura" => $procedura->getId()]);

        $form = $this->createForm('SfingeBundle\Form\PianoCostoType', $piano_costo, $options);

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                try {
                    $em->flush();
                    $this->addFlash('success', "Modifica effettuata correttamente");
                    return $this->redirect($this->generateUrl('visualizza_piano_costi', ["id_procedura" => $procedura->getId()]));
                } catch (\Exception $e) {
                    $this->get('logger')->error($e->getMessage());
                    $this->addFlash('error', "Errore nel salvaltaggio delle informazioni");
                }
            }
        }

        $form_params["form"] = $form->createView();

        return $form_params;
    }

    /**
     * @Route("/esporta_procedura/{id_procedura}", name="esporta_procedura")
     * @param mixed $id_procedura
     */
    public function esportaProceduraAction($id_procedura) {
        $em = $this->getDoctrine()->getManager();
        $procedura = $em->getRepository('SfingeBundle:Procedura')->findOneById($id_procedura);

        if (!$procedura) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $response = $this->render('SfingeBundle:Procedura:esportaProcedura.sql.twig', ["procedura" => $procedura]);

        // Set headers
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', 'application/x-sql');
        $response->headers->set('Content-Disposition', 'attachment; filename="xx_bando_x.sql";');

        // Send headers before outputting anything
        $response->sendHeaders();

        return $response;
    }

    /**
     * @Route("/elenco_documenti_richiesti/{id_procedura}", name="elenco_documenti_richiesti")
     * @Template("SfingeBundle:Procedura:documenti_richiesti.html.twig")
     * @PaginaInfo(titolo="Documenti richiesti procedura", sottoTitolo="pagina per visualizzare i documenti richiesti per la procedura")
     * @Menuitem(menuAttivo="elencoAtti")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Atti Amministrativi", route="elenco_atti_amministrativi"), @ElementoBreadcrumb(testo="Elenco documenti richiesti")})
     * @ControlloAccesso(contesto="procedura", classe="SfingeBundle:Procedura", opzioni={"id" : "id_procedura"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_procedura
     */
    public function elencoDocumentiRichiestiAction($id_procedura) {
        $em = $this->getDoctrine()->getManager();
        $procedura = $em->getRepository('SfingeBundle:Procedura')->find($id_procedura);
        if (\is_null($procedura)) {
            $this->addFlash('error', "Procedura non trovata");
            return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
        }

        $form_params["procedura"] = $procedura;

        return $form_params;
    }

    /**
     * @Route("/aggiungi_documento_richiesto/{id_procedura}", name="aggiungi_documento_richiesto")
     * @Template("SfingeBundle:Procedura:documento_richiesto.html.twig")
     * @Menuitem(menuAttivo="elencoAtti")
     * @PaginaInfo(titolo="Aggiunta documento richiesto", sottoTitolo="pagina per aggiungere un documento richiesto ad una procedura")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Atti Amministrativi", route="elenco_atti_amministrativi"),
     *     @ElementoBreadcrumb(testo="Documenti richiesti", route="elenco_documenti_richiesti", parametri={"id_procedura" : "id_procedura"}),
     * @ElementoBreadcrumb(testo="Aggiunta documento") })
     * @ControlloAccesso(contesto="procedura", classe="SfingeBundle:Procedura", opzioni={"id" : "id_procedura"}, azione=\SfingeBundle\Security\ProceduraVoter::WRITE)
     * @param mixed $id_procedura
     */
    public function aggiungiDocumentoRichiestoAction($id_procedura) {
        $em = $this->getEm();
        $procedura = $em->getRepository("SfingeBundle:Procedura")->find($id_procedura);
        if (\is_null($procedura)) {
            $this->addFlash('error', "Atto amministrativo non trovato");
            return $this->redirect($this->generateUrl('elenco_documenti_richiesti', ["id_procedura" => $procedura->getId()]));
        }

        if (!$procedura->isModificabile()) {
            $this->addFlash('error', "Procedura non modificabile");
            return $this->redirect($this->generateUrl('elenco_documenti_richiesti', ["id_procedura" => $procedura->getId()]));
        }

        $request = $this->getCurrentRequest();

        $options["url_indietro"] = $this->generateUrl('elenco_documenti_richiesti', ["id_procedura" => $procedura->getId()]);
        $tipologia_documento = new \DocumentoBundle\Entity\TipologiaDocumento();
        $tipologia_documento->setProcedura($procedura);
        $tipologia_documento->setDimensioneMassima(10);
        $tipologia_documento->setMimeAmmessi("application/pdf,application/pkcs7-mime,application/binary,application/octet-stream");

        $form = $this->createForm('DocumentoBundle\Form\Type\TipologiaDocumentoType', $tipologia_documento, $options);

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                try {
                    $em->persist($tipologia_documento);
                    $em->flush();
                    $this->addFlash('success', "Inserimento effettuato correttamente");
                    return $this->redirect($this->generateUrl('elenco_documenti_richiesti', ["id_procedura" => $procedura->getId()]));
                } catch (\Exception $e) {
                    $this->get('logger')->error($e->getMessage());
                    $this->addFlash('error', "Errore nel salvaltaggio delle informazioni");
                }
            }
        }

        $form_params["form"] = $form->createView();

        return $form_params;
    }

    /**
     * @Route("/modifica_documento_richiesto/{id_procedura}/{id_tipologia}", name="modifica_documento_richiesto")
     * @Template("SfingeBundle:Procedura:documento_richiesto.html.twig")
     * @Menuitem(menuAttivo="elencoAtti")
     * @PaginaInfo(titolo="Modifica documento richiesto", sottoTitolo="pagina per modificare un documento richiesto da una procedura")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Atti Amministrativi", route="elenco_atti_amministrativi"),
     *     @ElementoBreadcrumb(testo="Documenti richiesti", route="elenco_documenti_richiesti", parametri={"id_procedura" : "id_procedura"}),
     * @ElementoBreadcrumb(testo="M documento") })
     * @ControlloAccesso(contesto="procedura", classe="SfingeBundle:Procedura", opzioni={"id" : "id_procedura"}, azione=\SfingeBundle\Security\ProceduraVoter::WRITE)
     * @param mixed $id_procedura
     * @param mixed $id_tipologia
     */
    public function modificaDocumentoRichiestoAction($id_procedura, $id_tipologia) {
        $em = $this->getEm();
        $procedura = $em->getRepository("SfingeBundle:Procedura")->find($id_procedura);
        if (\is_null($procedura)) {
            $this->addFlash('error', "Atto amministrativo non trovato");
            return $this->redirect($this->generateUrl('elenco_documenti_richiesti', ["id_procedura" => $procedura->getId()]));
        }

        if (!$procedura->isModificabile()) {
            $this->addFlash('error', "Procedura non modificabile");
            return $this->redirect($this->generateUrl('elenco_documenti_richiesti', ["id_procedura" => $procedura->getId()]));
        }

        $request = $this->getCurrentRequest();

        $options["url_indietro"] = $this->generateUrl('elenco_documenti_richiesti', ["id_procedura" => $procedura->getId()]);
        $tipologia_documento = $em->getRepository("DocumentoBundle:TipologiaDocumento")->find($id_tipologia);

        $form = $this->createForm('DocumentoBundle\Form\Type\TipologiaDocumentoType', $tipologia_documento, $options);

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                try {
                    $em->flush();
                    $this->addFlash('success', "Modifica effettuata correttamente");
                    return $this->redirect($this->generateUrl('elenco_documenti_richiesti', ["id_procedura" => $procedura->getId()]));
                } catch (\Exception $e) {
                    $this->get('logger')->error($e->getMessage());
                    $this->addFlash('error', "Errore nel salvaltaggio delle informazioni");
                }
            }
        }

        $form_params["form"] = $form->createView();

        return $form_params;
    }

    /**
     * @Route("/elimina_documento_richiesto/{id_tipologia}", name="elimina_documento_richiesto")
     * @ControlloAccesso(contesto="procedura", classe="DocumentoBundle:TipologiaDocumento", opzioni={"id" : "id_tipologia"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_tipologia
     */
    public function eliminaDocumentoRichiestoAction($id_tipologia) {
        $em = $this->getEm();
        $tipologia_documento = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->find($id_tipologia);
        $procedura = $tipologia_documento->getProcedura();

        if (!$procedura->isModificabile()) {
            $this->addFlash('error', "Procedura non modificabile");
            return $this->redirect($this->generateUrl('elenco_documenti_richiesti', ["id_procedura" => $procedura->getId()]));
        }

        try {
            $em->remove($tipologia_documento);
            $em->flush();
            $this->addFlash('success', "Eliminazione effettuata correttamente");
        } catch (\Exception $e) {
            $this->get('logger')->error($e->getMessage());
            $this->addFlash('error', "Errore nel salvaltaggio delle informazioni");
        }

        return $this->redirect($this->generateUrl('elenco_documenti_richiesti', ["id_procedura" => $procedura->getId()]));
    }
}
