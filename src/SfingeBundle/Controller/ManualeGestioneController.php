<?php

namespace SfingeBundle\Controller;

use BaseBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use SfingeBundle\Form\ManualeType;
use SfingeBundle\Entity\Manuale;
use DocumentoBundle\Entity\DocumentoFile;
use SfingeBundle\Form\Entity\RicercaManuale;
use PaginaBundle\Annotations\Menuitem;

class ManualeGestioneController extends BaseController {

    /**
     * @Route("/carica", name="carica_manuale")
     * @Template("SfingeBundle:Manuale:caricaManuale.html.twig")
     * @PaginaInfo(titolo="Carica manuale",sottoTitolo="")
     * @Menuitem(menuAttivo = "caricaManuale")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="aggiungi manuale")})
     */
    public function caricaManualeAction() {

        $em = $this->getDoctrine()->getManager();
        $request = $this->getCurrentRequest();
        $form = null;

        $manuale = new Manuale();
        $listaTipi = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->ricercaTipologieManuali();

        if (count($listaTipi) > 0) {
            $form = $this->createForm('SfingeBundle\Form\ManualeType', $manuale, array('lista_tipi' => $listaTipi, 'url_indietro' => $this->generateUrl('elenco_manuali')));

            if ($request->isMethod('POST')) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    try {
                        $em->beginTransaction();
                        $documentoFile = $manuale->getDocumentoFile();
                        $manuale->setDocumentoFile($this->get("documenti")->carica($documentoFile));
                        $em->persist($manuale);
                        $em->flush();
                        $em->commit();
                        $this->addFlash('success', "Modifiche salvate correttamente");
                        return $this->redirect($this->generateUrl('carica_manuale'));
                    } catch (\Exception $e) {
                        $em->rollback();
                        $this->addFlash('error', $e->getMessage());
                    }
                }
            }
        }

        if ($form) {
            $risultato["form"] = $form->createView();
        } else {
            $risultato["form"] = null;
        }

        return $risultato;
    }

    /**
     * @Route("/cancella/{id_documento}", name="cancella_manuale")
     */
    public function cancellaManualeAction($id_documento) {
        $this->get('base')->checkCsrf('token');
        $em = $this->getDoctrine()->getManager();
        $manuale = $em->getRepository("SfingeBundle\Entity\Manuale")->findOneBy(array("documento_file" => $id_documento));
        if (is_null($manuale)) {
            return $this->addErrorRedirect("Manuale non trovato", "elenco_manuali");
        }

        try {
            $em->beginTransaction();
            $this->get("documenti")->cancella($manuale->getDocumentoFile());
            $manuale->setDocumentoFile(null);
            $em->remove($manuale);
            $em->flush();
            $em->commit();
            $this->addFlash('success', "Manuale cancellato correttamente");
            return $this->redirect($this->generateUrl('elenco_manuali'));
        } catch (\Exception $e) {
            $em->rollback();
            $this->addFlash('error', $e->getMessage());
            return $this->addErrorRedirect("Operazione non eseguita", "elenco_manuali");
        }
    }
    
    /**
     * @Route("/modifica/{id_manuale}", name="modifica_manuale")
     * @Template("SfingeBundle:Manuale:modificaManuale.html.twig")
     * @PaginaInfo(titolo="Modifica manuale",sottoTitolo="")
     * @Menuitem(menuAttivo = "modificaManuale")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="modifica manuale")})
     */
    public function modificaManualeAction($id_manuale, $read_only = false) {

        $em = $this->getDoctrine()->getManager();
        $request = $this->getCurrentRequest();
        $form = null;

        $manuale = $em->getRepository("SfingeBundle\Entity\Manuale")->findOneById($id_manuale);
        if(\is_null($manuale)){
            throw new \BaseBundle\Exception\SfingeException('Errore nella modifica del manuale');
        }
        $listaTipi = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->ricercaTipologieManuali();
        $form = $this->createForm('SfingeBundle\Form\ManualeType', $manuale, array('lista_tipi' => $listaTipi, 'read_only' => $read_only, 'url_indietro' => $this->generateUrl('elenco_manuali')));
        $documento_file = $manuale->getDocumentoFile();
        if(!\is_null($documento_file)){
            $doc_type = $form->get('documento_file');
            $doc_type->remove('file');
        }
        
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $em->beginTransaction();
                    
                    if(is_null($documento_file)){
                        $manuale->setDocumentoFile($this->get("documenti")->carica($manuale->getDocumentoFile()));
                    }
                    $em->persist($manuale);
                    $em->flush();
                    $em->commit();
                    $this->addFlash('success', "Modifiche salvate correttamente");
                    return $this->redirect($this->generateUrl('modifica_manuale',array('id_manuale' => $id_manuale)));
                } catch (\Exception $e) {
                    $em->rollback();
                    $this->addFlash('error', $e->getMessage());
                }
            } else {
                $this->addFlash('error', "Errore durante la modifica del manuale");
            }
        }

        $risultato["form"] = $form->createView();
        $risultato['manuale'] = $manuale;
        
        return $risultato;
    }

    /**
     * @Route("/visualizza/{id_manuale}", name="visualizza_manuale")
     * @Template("SfingeBundle:Manuale:modificaManuale.html.twig")
     * @PaginaInfo(titolo="Visualizza manuale",sottoTitolo="")
     * @Menuitem(menuAttivo = "visualizzaManuale")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="visualizza manuale")})
     */
    public function visualizzaManualeAction($id_manuale) {
        return $this->modificaManualeAction($id_manuale, true);
    }
    
    
    /**
     * @Route("/elimina_documento_manuale/{id_manuale}", name="elimina_documento_manuale")
     */
    public function eliminaDocumentoManuale($id_manuale) {
        $em = $this->getDoctrine()->getManager();

        $manuale = $em->getRepository("SfingeBundle\Entity\Manuale")->findOneById($id_manuale);
        if (\is_null($manuale)) {
            throw new \BaseBundle\Exception\SfingeException('Errore nella cancellazione del documento legato al manuale');
        }

        try {
            $em->remove($manuale->getDocumentoFile());
            $manuale->setDocumentoFile(null);
            $em->persist($manuale);
            $em->flush();
            $this->addFlash('success', "Cancellazione effettuata. Inserire il nuovo documento");
            return $this->redirect($this->generateUrl('modifica_manuale', array('id_manuale' => $id_manuale)));
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirect($this->generateUrl('modifica_manuale', array('id_manuale' => $id_manuale)));
        }
    }

}
