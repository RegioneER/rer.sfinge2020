<?php

namespace SfingeBundle\Controller;

use BaseBundle\Controller\BaseController;
use DocumentoBundle\Entity\DocumentoFile;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SfingeBundle\Entity\DocumentiOOII;

//use RichiesteBundle\Service\GestoreResponse;

class OrganismiIntermediController extends BaseController {
    /**
     * @Route("/organismi_intermedi", name="organismi_intermedi")
     * @Template("SfingeBundle:OrganismiIntermedi:OrganismiIntermedi.html.twig")
     * @PaginaInfo(titolo="Organismi intermedi", sottoTitolo="")
     * @Menuitem(menuAttivo="organismi_intermedi_id")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Organismi Intermedi")})
     */
    public function organismiIntermediAction() {
        $em = $this->getDoctrine()->getManager();
        $organismi_intermedi = $em->getRepository("SoggettoBundle\Entity\OrganismoIntermedio")->findAll();

        return ['organismi_intermedi' => $organismi_intermedi];
    }

    /**
     * @Route("/organismi_intermedi/documenti/{id_organismo_intermedio}", name="organismi_intermedi_documenti")
     * @Template("SfingeBundle:OrganismiIntermedi:Documenti.html.twig")
     * @PaginaInfo(titolo="Organismi intermedi - Documenti", sottoTitolo="")
     * @Menuitem(menuAttivo="organismi_intermedi_id")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Organismi Intermedi", route="organismi_intermedi"), @ElementoBreadcrumb(testo="Documenti")})
     * @param mixed $id_organismo_intermedio
     * @param mixed $opzioni
     */
    public function documentiAction($id_organismo_intermedio, $opzioni = []) {
        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        $documento_ooii = new DocumentiOOII();
        $documento_file = new DocumentoFile();

        $documenti_caricati = $em->getRepository("SfingeBundle\Entity\DocumentiOOII")->findDocumentiCaricati($id_organismo_intermedio);

        $organismo_intermedio = $em->getRepository("SoggettoBundle\Entity\OrganismoIntermedio")->find($id_organismo_intermedio);

        $tipo_doc = $em->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findOneBy(['codice' => "DOC_OOII"]);

        $opzioni_form['tipo'] = $tipo_doc;

        $form = $this->createForm("DocumentoBundle\Form\Type\DocumentoFileType", $documento_file, $opzioni_form);
        $form->add("submit", "Symfony\Component\Form\Extension\Core\Type\SubmitType", ["label" => "Salva"]);

        if ($request->isMethod("POST")) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $this->container->get("documenti")->carica($documento_file, 0);

                    $documento_ooii->setDocumentoFile($documento_file);
                    $documento_ooii->setOrganismoIntermedio($organismo_intermedio);
                    $em->persist($documento_ooii);

                    $em->flush();
                    return $this->addSuccessRedirect("Documento caricato correttamente", "organismi_intermedi_documenti", ["id_organismo_intermedio" => $id_organismo_intermedio]);
                } catch (\Exception $e) {
                    $this->get('logger')->error($e->getMessage());
                    $this->addFlash('error', "Errore nel salvaltaggio delle informazioni");
                }
            }
        }
        $form_view = $form->createView();

        $dati = ["documenti" => $documenti_caricati, "id_organismo_intermedio" => $id_organismo_intermedio, "form" => $form_view];
        return $this->render("SfingeBundle:OrganismiIntermedi:Documenti.html.twig", $dati);
    }

    /**
     * @Route("/organismi_intermedi/cancella_documenti/{id_documento}", name="cancella_documento_oi")
     * @Template("SfingeBundle:OrganismiIntermedi:Documenti.html.twig")
     * @PaginaInfo(titolo="Organismi intermedi - Documenti", sottoTitolo="")
     * @Menuitem(menuAttivo="organismi_intermedi_id")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Organismi Intermedi", route="organismi_intermedi"), @ElementoBreadcrumb(testo="Documenti")})
     * @param mixed $id_documento
     */
    public function cancellaDocumentoAction($id_documento) {
        $em = $this->getEm();
        $documento = $em->getRepository("SfingeBundle\Entity\DocumentiOOII")->find($id_documento);
        $id_organismo_intermedio = $documento->getOrganismoIntermedio()->getId();
        try {
            $em->remove($documento->getDocumentoFile());
            $em->remove($documento);
            $em->flush();
            return $this->addSuccessRedirect("Documento eliminato correttamente", "organismi_intermedi_documenti", ["id_organismo_intermedio" => $id_organismo_intermedio]);
        } catch (\Exception $e) {
            $this->get('logger')->error($e->getMessage());
            $this->addFlash('error', "Errore nel salvaltaggio delle informazioni");
        }
    }
}
