<?php

namespace FascicoloBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use FascicoloBundle\Entity\Fascicolo;
use FascicoloBundle\Entity\Pagina;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FascicoloBundle\Form\Type\FascicoloType;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Menuitem;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/fascicolo")
 */
class FascicoloController extends Controller {
    protected function preValidazioneFascicolo($form) {
        $em = $this->getDoctrine()->getManager();
        $fascicolo = $form->getData();
        $alias = $fascicolo->getAlias();
        $paginaVerifica = $em->getRepository("FascicoloBundle\Entity\Pagina")->getPagineFascicoloAlias($alias, $fascicolo);
        if ($paginaVerifica) {
            $form->get('alias')->addError(new \Symfony\Component\Form\FormError('L\'alias specificato è già presente a sistema'));
        }

        if (!$this->get('templating')->exists($fascicolo->getTemplate())) {
            $form->get('template')->addError(new \Symfony\Component\Form\FormError('Il template indicato non esiste'));
        }

        $indice = $fascicolo->getIndice();

        if (is_null($indice)) {
            $indice = new Pagina();
            $fascicolo->setIndice($indice);
        }

        $indice->setMaxMolteplicita(1);
        $indice->setMinMolteplicita(1);
        $indice->setTitolo($fascicolo->getTitolo());
        $indice->setAlias($fascicolo->getAlias());
        $indice->setCallBack($fascicolo->getCallBack());
        $indice->setOrdinamento(0);

        return;
    }

    /**
     * @Route("/crea-fascicolo/", name="crea_fascicolo")
     * @Template("FascicoloBundle:Fascicolo:creaFascicolo.html.twig")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Pagine", route="visualizza_fascicoli")})
     * @PaginaInfo(titolo="Crea Pagina")
     * @Menuitem(menuAttivo="crea_fascicolo")
     */
    public function creaFascicoloAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $fascicolo = new Fascicolo();
        $form = $this->createForm(new FascicoloType(), $fascicolo, array("button" => true));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            $this->preValidazioneFascicolo($form);

            if ($form->isValid()) {
                try {
                    $em->persist($fascicolo);
                    $em->flush();
                    $this->addFlash('success', "Fascicolo creato correttamente");
                    return $this->redirect($this->generateUrl('modifica_fascicolo', array("id_fascicolo" => $fascicolo->getId())));
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }

        $form_params["form"] = $form->createView();
        return $form_params;
    }

    /**
     * @Route("/modifica-fascicolo/{id_fascicolo}", name="modifica_fascicolo")
     * @Template("FascicoloBundle:Fascicolo:modificaFascicolo.html.twig")
     *
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Pagine", route="visualizza_fascicoli")})
     * @PaginaInfo(titolo="Modifica Pagina")
     * @Menuitem(menuAttivo="visualizza_fascicoli")
     */
    public function modificaFascicoloAction(Request $request, $id_fascicolo) {
        $em = $this->getDoctrine()->getManager();
        $fascicolo = $em->getRepository("FascicoloBundle\Entity\Fascicolo")->findOneById($id_fascicolo);
        $fascicolo->setTitolo($fascicolo->getIndice()->getTitolo());
        $fascicolo->setAlias($fascicolo->getIndice()->getAlias());
        $fascicolo->setCallback($fascicolo->getIndice()->getCallback());

        $form = $this->createForm(FascicoloType::class, $fascicolo, array("button" => true));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            $this->preValidazioneFascicolo($form);

            if ($form->isValid()) {
                try {
                    $em->persist($fascicolo);
                    $em->flush();
                    $this->addFlash('success', "Modifiche salvate correttamente");
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }
        $form_params["form"] = $form->createView();
        $form_params["id_pagina_indice"] = $fascicolo->getIndice()->getId();
        return $form_params;
    }

    /**
     * @Route("/elimina-fascicolo/{id_fascicolo}", name="elimina_fascicolo")
     */
    public function eliminaFascicoloAction($id_fascicolo) {
        $this->get('base')->checkCsrf('token');
        $em = $this->getDoctrine()->getManager();
        $fascicolo = $em->getRepository("FascicoloBundle\Entity\Fascicolo")->findOneById($id_fascicolo);

        if (!$fascicolo) {
            throw $this->createNotFoundException('Unable to find Fascicolo entity.');
        }
        try {
            $em->remove($fascicolo->getIndice());
            $em->remove($fascicolo);
            $em->flush();
            $this->addFlash('success', "Fascicolo eliminato correttamente");
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('visualizza_fascicoli');
    }

    /**
     * @Route("/visualizza-fascicoli", name="visualizza_fascicoli")
     * @Template("FascicoloBundle:Fascicolo:visualizzaFascicoli.html.twig")
     * @PaginaInfo(titolo="Elenco Pagine")
     * @Menuitem(menuAttivo="visualizza_fascicoli")
     */
    public function visualizzaFascicoliAction() {
        $em = $this->getDoctrine()->getManager();

        $fascicoli = $em->getRepository("FascicoloBundle\Entity\Fascicolo")->findAll();

        $param["fascicoli"] = $fascicoli;
        return $param;
    }

    /**
     * @Route("/genera-albero-fascicolo/{id_fascicolo}", name="genera_albero_fascicolo")
     * @Template("FascicoloBundle:Fascicolo:generaAlberoFascicolo.html.twig")
     *
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Pagine", route="visualizza_fascicoli")})
     * @PaginaInfo(titolo="Albero Fascicolo")
     * @Menuitem(menuAttivo="visualizza_fascicoli")
     */
    public function generaAlberoFascicoloAction($id_fascicolo) {
        $em = $this->getDoctrine()->getManager();
        $fascicolo = $em->getRepository("FascicoloBundle\Entity\Fascicolo")->findOneById($id_fascicolo);

        $param["fascicolo"] = $fascicolo;
        return $param;
    }

    /**
     * @Route("/clona-fascicolo/{id_fascicolo}", name="clona_fascicolo")
     */
    public function clonaFascicoloAction($id_fascicolo) {
        $em = $this->getDoctrine()->getManager();
        $fascicolo = $em->getRepository("FascicoloBundle\Entity\Fascicolo")->findOneById($id_fascicolo);

        if (!$fascicolo) {
            throw $this->createNotFoundException('Unable to find Fascicolo entity.');
        }
        try {
            $fascicoloClonato = clone $fascicolo;
            $em->persist($fascicoloClonato);
            $em->flush();
            $this->addFlash('success', "Fascicolo clonato correttamente");
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('visualizza_fascicoli');
    }

    /**
     * @Route("/esporta-fascicolo/{id_fascicolo}", name="esporta_fascicolo")
     */
    public function esportaFascicoloAction($id_fascicolo) {
        $em = $this->getDoctrine()->getManager();
        $fascicolo = $em->getRepository("FascicoloBundle\Entity\Fascicolo")->findOneById($id_fascicolo);

        if (!$fascicolo) {
            throw $this->createNotFoundException('Unable to find Fascicolo entity.');
        }

        $response = $this->render('FascicoloBundle:Fascicolo:esportaFascicolo.sql.twig', array("fascicolo" => $fascicolo));

        // Set headers
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', 'application/x-sql');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fascicolo->getIndice()->getAlias() . '.sql";');
        // $response->headers->set('Content-length', filesize($filename));

        // Send headers before outputting anything
        $response->sendHeaders();

        return $response;
    }
}
