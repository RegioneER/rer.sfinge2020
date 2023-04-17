<?php

namespace SfingeBundle\Controller;

use BaseBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use SfingeBundle\Form\AttoType;
use SfingeBundle\Entity\Atto;
use PaginaBundle\Annotations\Menuitem;
use DocumentoBundle\Component\ResponseException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AttoGestioneController extends BaseController {
    /**
     * @Route("/crea_atto", name="crea_atto")
     * @PaginaInfo(titolo="Nuovo Atto", sottoTitolo="pagina per creare un nuovo atto")
     * @Menuitem(menuAttivo="elencoAtto")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Crea Atto", route="crea_atto")})
     */
    public function creaAttoAction(Request $request): Response {
        $atto = new Atto();
        $options["readonly"] = false;
        $options["url_indietro"] = $this->generateUrl("home");
        $options["mostra_indietro"] = false;
        $form = $this->createForm(AttoType::class, $atto, $options);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $documento = $atto->getDocumentoAtto();
                if (!\is_null($documento->getFile())) {
                    $this->get("documenti")->carica($documento);
                } else {
                    $atto->setDocumentoAtto(null);
                }
                $em = $this->getEm();
                $em->persist($atto);
                $em->flush();
                $this->addFlash('success', "Modifiche salvate correttamente");

                return $this->redirect($this->generateUrl('elenco_atti'));
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["atto"] = $atto;

        return $this->render('SfingeBundle:Atto:atto.html.twig', $form_params);
    }

    /**
     * @Route("/modifica_atto/{id_atto}", name="modifica_atto")
     * @Template("SfingeBundle:Atto:atto.html.twig")
     * @PaginaInfo(titolo="Modifica Atto", sottoTitolo="pagina per modificare un atto")
     * @Menuitem(menuAttivo="elencoAtti")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Gestione atti", route="elenco_atti", parametri={"id_atto"}), @ElementoBreadcrumb(testo="Modifica Atto")})
     */
    public function modificaAttoAction(Request $request, $id_atto) {
        $em = $this->getDoctrine()->getManager();
        $atto = $em->getRepository('SfingeBundle:Atto')->findOneById($id_atto);

        $ctrl_istr1 = $em->getRepository('IstruttorieBundle:IstruttoriaRichiesta')->findOneBy(['atto_concessione_atc' => $id_atto]);
        $ctrl_istr2 = $em->getRepository('IstruttorieBundle:IstruttoriaRichiesta')->findOneBy(['atto_ammissibilita_atc' => $id_atto]);
        $ctrl_richiesta = $em->getRepository('RichiesteBundle:Richiesta')->getRichiesteByattoId($id_atto);

        if (\is_null($ctrl_istr1) && \is_null($ctrl_istr2) && 0 == count($ctrl_richiesta)) {
            $options["readonly_procedura"] = false;
        } else {
            $options["readonly_procedura"] = true;
        }

        $options["readonly"] = false;
        $options["url_indietro"] = $this->generateUrl("crea_atto");

        $form = $this->createForm('SfingeBundle\Form\AttoType', $atto, $options);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $em->beginTransaction();

                    $documento_old = $atto->getDocumentoAtto();

                    if (!is_null($atto->getDocumentoAtto()->getFile())) {
                        $this->get("documenti")->carica($atto->getDocumentoAtto());
                    } else {
                        $atto->setDocumentoAtto($documento_old);
                    }

                    $em->persist($atto);
                    $em->flush();
                    $em->commit();
                    $this->addFlash('success', "Modifiche salvate correttamente");

                    return $this->redirect($this->generateUrl('elenco_atti'));
                } catch (\Exception $e) {
                    $em->rollback();
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["atto"] = $atto;

        return $form_params;
    }

    /**
     * @Route("/cancella_documento_atto/{id_atto}", name="cancella_documento_atto")
     * @Template("SfingeBundle:Atto:atto.html.twig")
     * @PaginaInfo(titolo="Modifica Atto", sottoTitolo="pagina per modificare un atto")
     * @Menuitem(menuAttivo="elencoAtti")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Gestione atti", route="elenco_atti", parametri={"id_atto"}), @ElementoBreadcrumb(testo="Modifica Atto")})
     */
    public function cancellaDocumentoAttoAction(Request $request, $id_atto) {
        $em = $this->getDoctrine()->getManager();
        $atto = $em->getRepository('SfingeBundle:Atto')->findOneById($id_atto);

        try {
            $em->remove($atto->getDocumentoAtto());
            $atto->setDocumentoAtto(null);
            $em->persist($atto);
            $em->flush();

            return $this->addSuccessRedirect("Documento eliminato correttamente", "modifica_atto", ["id_atto" => $id_atto]);
        } catch (ResponseException $e) {
            $this->addFlash('error', $e->getMessage());
        }
    }
}
