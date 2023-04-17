<?php

namespace SfingeBundle\Controller;

use BaseBundle\Controller\BaseController;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SfingeBundle\Entity\PermessiAsse;
use SfingeBundle\Entity\PermessiProcedura;
use SfingeBundle\Form\Entity\RicercaPermessiAsse;
use SfingeBundle\Form\Entity\RicercaPermessiProcedura;
use Symfony\Component\HttpFoundation\Request;

class PermessiController extends BaseController {
    /**
     * @Route("/permessi_asse", name="permessi_asse")
     * @Template("SfingeBundle:Permessi:permessiAsse.html.twig")
     * @PaginaInfo(titolo="Permessi asse", sottoTitolo="")
     * @Menuitem(menuAttivo="permessi_asse")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Permessi asse")})
     */
    public function permessiAsseAction() {
        $em = $this->getDoctrine()->getManager();

        $permesso = new PermessiAsse();
        $request = $this->getCurrentRequest();

        $options["readonly"] = false;
        $options["em"] = $this->getEm();
        $options["url_indietro"] = $this->generateUrl("home");

        $form = $this->createForm('SfingeBundle\Form\PermessiAsseType', $permesso, $options);

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                try {
                    $em->persist($permesso);
                    $em->flush();
                    $this->addFlash('success', "Modifiche salvate correttamente");
                    return $this->redirect($this->generateUrl('elenco_permessi_asse'));
                } catch (\Exception $e) {
                    $this->get('logger')->error($e->getMessage());
                    $this->addFlash('error', "Errore nel salvaltaggio delle informazioni");
                }
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["permesso"] = $permesso;

        return $form_params;
    }

    /**
     * @Route("/modifica_permesso_asse/{id_permesso}", name="modifica_permesso_asse")
     * @Template("SfingeBundle:Permessi:permessiAsse.html.twig")
     * @PaginaInfo(titolo="Permessi asse", sottoTitolo="")
     * @Menuitem(menuAttivo="modifica_permesso_asse")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Modifica permessi asse")})
     * @param mixed $id_permesso
     */
    public function modificaPermessiAsseAction(Request $request, $id_permesso) {
        $em = $this->getDoctrine()->getManager();
        $permesso = $em->getRepository('SfingeBundle:PermessiAsse')->findOneById($id_permesso);

        $options["em"] = $em;
        $options["readonly"] = false;
        $options["url_indietro"] = $this->generateUrl("elenco_permessi_asse");

        $form = $this->createForm('SfingeBundle\Form\PermessiAsseType', $permesso, $options);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $em->persist($permesso);
                    $em->flush();
                    $this->addFlash('success', "Modifiche salvate correttamente");

                    return $this->redirect($this->generateUrl('elenco_permessi_asse'));
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["permesso"] = $permesso;

        return $form_params;
    }

    /**
     * @Route("/elenco_permessi_asse/{sort}/{direction}/{page}", defaults={"sort" : "a.id", "direction" : "asc", "page" : "1"}, name="elenco_permessi_asse")
     * @Menuitem(menuAttivo="elenco_permessi_asse")
     * @PaginaInfo(titolo="Elenco permessi asse", sottoTitolo="")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco permessi asse")})
     */
    public function elencoPermessiAsseAction() {
        $datiRicerca = new RicercaPermessiAsse();
        $risultato = $this->get("ricerca")->ricerca($datiRicerca);

        return $this->render('SfingeBundle:Permessi:elencoPermessiAsse.html.twig', ['permessi' => $risultato["risultato"], "formRicercaPermessiAsse" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"],
        ]);
    }

    /**
     * @Route("/elenco_permessi_asse_pulisci", name="elenco_permessi_asse_pulisci")
     */
    public function elencoPermessiAssePulisciAction() {
        $this->get("ricerca")->pulisci(new RicercaPermessiAsse());
        return $this->redirectToRoute("elenco_permessi_asse");
    }

    /**
     * @Route("/cancella_permessi_asse/{id_permesso}", name="cancella_permessi_asse")
     * @param mixed $id_permesso
     */
    public function cancellaPermessiAsseAction($id_permesso) {
        $this->get('base')->checkCsrf('token');
        $em = $this->getDoctrine()->getManager();
        $permesso = $em->getRepository("SfingeBundle\Entity\PermessiAsse")->find($id_permesso);
        if (is_null($permesso)) {
            return $this->addErrorRedirect("Permesso non trovato", "elenco_permessi_asse");
        }
        try {
            $permesso->setDataCancellazione(new \DateTime());
            $em->persist($permesso);
            $em->flush();
            $this->addFlash('success', "Permesso cancellato correttamente");
            return $this->redirect($this->generateUrl('elenco_permessi_asse'));
        } catch (\Exception $e) {
            $this->get('logger')->error($e->getMessage());
            $this->addFlash('error', "Errore nel salvaltaggio delle informazioni");
        }
    }

    /**
     * @Route("/permessi_procedura", name="permessi_procedura")
     * @Template("SfingeBundle:Permessi:permessiProcedura.html.twig")
     * @PaginaInfo(titolo="Permessi procedura", sottoTitolo="")
     * @Menuitem(menuAttivo="permessi_procedura")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Permessi procedura")})
     */
    public function permessiProceduraAction() {
        $em = $this->getDoctrine()->getManager();

        $permesso = new PermessiProcedura();
        $request = $this->getCurrentRequest();

        $options["readonly"] = false;
        $options["em"] = $this->getEm();
        $options["url_indietro"] = $this->generateUrl("home");

        $form = $this->createForm('SfingeBundle\Form\PermessiProceduraType', $permesso, $options);

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                try {
                    $em->persist($permesso);
                    $em->flush();
                    $this->addFlash('success', "Modifiche salvate correttamente");
                    return $this->redirect($this->generateUrl('elenco_permessi_procedure'));
                } catch (\Exception $e) {
                    $this->get('logger')->error($e->getMessage());
                    $this->addFlash('error', "Errore nel salvaltaggio delle informazioni");
                }
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["permesso"] = $permesso;

        return $form_params;
    }

    /**
     * @Route("/modifica_permesso_procedura/{id_permesso}", name="modifica_permesso_procedura")
     * @Template("SfingeBundle:Permessi:permessiProcedura.html.twig")
     * @PaginaInfo(titolo="Permessi procedura", sottoTitolo="")
     * @Menuitem(menuAttivo="modifica_permesso_procedura")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Modifica permessi procedura")})
     * @param mixed $id_permesso
     */
    public function modificaPermessiProceduraAction(Request $request, $id_permesso) {
        $em = $this->getDoctrine()->getManager();
        $permesso = $em->getRepository('SfingeBundle:PermessiProcedura')->findOneById($id_permesso);

        $options["em"] = $em;
        $options["readonly"] = false;
        $options["url_indietro"] = $this->generateUrl("elenco_permessi_procedure");

        $form = $this->createForm('SfingeBundle\Form\PermessiProceduraType', $permesso, $options);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $em->persist($permesso);
                    $em->flush();
                    $this->addFlash('success', "Modifiche salvate correttamente");

                    return $this->redirect($this->generateUrl('elenco_permessi_procedure'));
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["permesso"] = $permesso;

        return $form_params;
    }

    /**
     * @Route("/elenco_permessi_procedure/{sort}/{direction}/{page}", defaults={"sort" : "a.id", "direction" : "asc", "page" : "1"}, name="elenco_permessi_procedure")
     * @Menuitem(menuAttivo="elenco_permessi_procedure")
     * @PaginaInfo(titolo="Elenco permessi procedure", sottoTitolo="")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco permessi procedure")})
     */
    public function elencoPermessiProceduraAction() {
        $datiRicerca = new RicercaPermessiProcedura();
        $risultato = $this->get("ricerca")->ricerca($datiRicerca);

        return $this->render('SfingeBundle:Permessi:elencoPermessiProcedura.html.twig', ['permessi' => $risultato["risultato"], "formRicercaPermessiProcedura" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"],
        ]);
    }

    /**
     * @Route("/cancella_permessi_procedura/{id_permesso}", name="cancella_permessi_procedura")
     * @param mixed $id_permesso
     */
    public function cancellaPermessiProceduraAction($id_permesso) {
        $this->get('base')->checkCsrf('token');
        $em = $this->getDoctrine()->getManager();
        $permesso = $em->getRepository("SfingeBundle\Entity\PermessiProcedura")->find($id_permesso);
        if (is_null($permesso)) {
            return $this->addErrorRedirect("Permesso non trovato", "elenco_permessi_procedure");
        }
        try {
            $permesso->setDataCancellazione(new \DateTime());
            $em->persist($permesso);
            $em->flush();
            $this->addFlash('success', "Permesso cancellato correttamente");
            return $this->redirect($this->generateUrl('elenco_permessi_procedure'));
        } catch (\Exception $e) {
            $this->get('logger')->error($e->getMessage());
            $this->addFlash('error', "Errore nel salvaltaggio delle informazioni");
        }
    }

    /**
     * @Route("/elenco_permessi_procedura_pulisci", name="elenco_permessi_procedura_pulisci")
     */
    public function elencoPermessiProceduraPulisciAction() {
        $this->get("ricerca")->pulisci(new RicercaPermessiProcedura());
        return $this->redirectToRoute("elenco_permessi_procedure");
    }
}
