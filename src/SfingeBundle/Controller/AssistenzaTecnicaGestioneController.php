<?php

namespace SfingeBundle\Controller;

use BaseBundle\Controller\BaseController;
use DocumentoBundle\Entity\DocumentoFile;
use DocumentoBundle\Entity\TipologiaDocumento;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use SfingeBundle\Entity\AssistenzaTecnica;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use DocumentoBundle\Component\ResponseException;

class AssistenzaTecnicaGestioneController extends BaseController {
    /**
     * @Route("/crea_assistenza_tecnica", name="crea_assistenza_tecnica")
     * @Template("SfingeBundle:Procedura:assistenza_tecnica.html.twig")
     * @Menuitem(menuAttivo="creaAssistenzaTecnica")
     * @PaginaInfo(titolo="Nuova Assistenza Tecnica", sottoTitolo="pagina per creare una nuova assistenza tecnica")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Atti Amministrativi", route="elenco_atti_amministrativi"), @ElementoBreadcrumb(testo="Crea assistenza tecnica")})
     */
    public function creaAssistenzaTecnicaAction() {
        $em = $this->getDoctrine()->getManager();

        $assistenza = new AssistenzaTecnica();
        $request = $this->getCurrentRequest();

        $documentoConvenzione = new DocumentoFile();
        $assistenza->setDocumentoConvenzione($documentoConvenzione);

        $assi = $em->getRepository("SfingeBundle\Entity\Asse")->getAssi($this->getUser());
        $modalita_pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\ModalitaPagamento")->findAll();

        if (0 == count($assi)) {
            $this->addFlash('error', "Nessun asse associato");
            return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
        }

        $stato = $this->getEm()->getRepository("SfingeBundle:StatoProcedura")->findOneByCodice("CONCLUSO");
        $assistenza->setStatoProcedura($stato);
        $assistenza->setNumeroRichieste(1);

        $options["assi"] = $assi;
        $options["disabled"] = false;
        $options["url_indietro"] = $this->generateUrl("elenco_atti_amministrativi");
        $options["documento_opzionale"] = false;
        $options["TIPOLOGIA_DOCUMENTO"] = $this->trovaDaCostante("DocumentoBundle:TipologiaDocumento", TipologiaDocumento::ALTRO);
        $form = $this->createForm('SfingeBundle\Form\AssistenzaTecnicaType', $assistenza, $options);

		$form->handleRequest($request);
        if ($form->isSubmitted()) {

            $assistenza->setTipiOperazioni([$assistenza->getTipiOperazioni()]);

            if ($form->isValid()) {
                try {
                    $this->get("documenti")->carica($documentoConvenzione);

                    foreach ($modalita_pagamento as $modalita) {
                        $modalita_pagamento_procedura = new \AttuazioneControlloBundle\Entity\ModalitaPagamentoProcedura();
                        $modalita_pagamento_procedura->setModalitaPagamento($modalita);
                        $modalita_pagamento_procedura->setProcedura($assistenza);
                        $assistenza->addModalitaPagamento($modalita_pagamento_procedura);
                        $em->persist($modalita_pagamento_procedura);
                    }
                    $assistenza->setModalitaFinanziamentoAttiva(false);
                    $assistenza->setVisibileInCorso(true);
                    $assistenza->setRendicontazioneAttiva(false);
                    $em->persist($assistenza);
                    $em->flush();
                    $this->addFlash('success', "Modifiche salvate correttamente");

                    return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            } else {
                $this->addFlash('error', 'Alcuni valori non sono validi');
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["assistenza"] = $assistenza;
        $form_params["lettura"] = false;
        return $form_params;
    }

    /**
     * @Route("/assistenza_tecnica_modifica/{id_assistenza}", name="assistenza_tecnica_modifica")
     * @Template("SfingeBundle:Procedura:assistenza_tecnica.html.twig")
     * @PaginaInfo(titolo="Modifica Assistenza Tecnica", sottoTitolo="pagina per modificare i dati dell'assistenza tecnica selezionata")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Atti Amministrativi", route="elenco_atti_amministrativi"), @ElementoBreadcrumb(testo="modifica assistenza tecnica")})
     * @param mixed $id_assistenza
     */
    public function modificaAssistenzaTecnicaAction(Request $request, $id_assistenza) {
        $em = $this->getDoctrine()->getManager();
        $assistenza = $em->getRepository('SfingeBundle:Procedura')->findOneById($id_assistenza);
        if (\is_null($assistenza)) {
            $this->addFlash('error', "Assistenza tecnica non trovata");
            return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
        }
        $vecchioDocumento = $assistenza->getDocumentoConvenzione();
        $documentoConvenzione = new DocumentoFile();
        $assistenza->setDocumentoConvenzione($documentoConvenzione);

        $assi = $em->getRepository("SfingeBundle\Entity\Asse")->getAssi($this->getUser());
        if (0 == count($assi)) {
            $this->addFlash('error', "Nessun asse associato");
            return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
        }

        $options["assi"] = $assi;
        $options["disabled"] = false;
        $options["url_indietro"] = $this->generateUrl("elenco_atti_amministrativi");
        $options["documento_opzionale"] = true;
        $options["TIPOLOGIA_DOCUMENTO"] = $this->trovaDaCostante("DocumentoBundle:TipologiaDocumento", TipologiaDocumento::ALTRO);

        $tipi_operazioni = $assistenza->getTipiOperazioni()->toArray();
        $assistenza->setTipiOperazioni($tipi_operazioni[0]);

        $form = $this->createForm('SfingeBundle\Form\AssistenzaTecnicaType', $assistenza, $options);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $assistenza->setTipiOperazioni([$assistenza->getTipiOperazioni()]);

            if ($form->isValid()) {
                try {
                    // Se non ho inserito un nuovo documento ri-associo quello vecchio.
                    if (!is_null($assistenza->getDocumentoConvenzione()->getFile())) {
                        $this->get("documenti")->carica($assistenza->getDocumentoConvenzione());
                        // cancello il vecchio file
                        $vecchioDocumento->setDataCancellazione(new \DateTime());
                    } else {
                        $assistenza->setDocumentoConvenzione($vecchioDocumento);
                    }
                    $em->persist($assistenza);
                    $em->flush();
                    $this->addFlash('success', "Modifiche salvate correttamente");

                    return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
                } catch (ResponseException $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            } else {
                $this->addFlash('error', 'Alcuni valori non sono validi');
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["assistenza"] = $assistenza;
        $form_params["lettura"] = true;
        $form_params["vecchioDocumento"] = $vecchioDocumento;

        return $form_params;
    }
}
