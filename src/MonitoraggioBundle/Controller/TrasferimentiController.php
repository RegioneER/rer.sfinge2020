<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Controller;

use BaseBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use MonitoraggioBundle\Form\Entity\RicercaTrasferimento;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use MonitoraggioBundle\Entity\Trasferimento;

/**
 * Description of TrasferimentiController.
 *
 * @author vbuscemi
 */

/**
 * @Route("/trasferimenti")
 */
class TrasferimentiController extends BaseController {
    /**
     * @Route("/elenco/{sort}/{direction}/{page}", defaults={"sort": "t.id", "direction": "asc", "page": "1"}, name="elenco_trasferimenti")
     * Template("MonitoraggioBundle:Trasferimenti:elencoTrasferimenti.html.twig")
     * @PaginaInfo(titolo="Elenco trasferimenti", sottoTitolo="mostra l'elenco dei trasferimenti")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco trasferimenti")})
     */
    public function elencoTrasferimentiAction() {
        $datiRicerca = new RicercaTrasferimento();
        $risultato = $this->get('ricerca')->ricerca($datiRicerca);

        $ruoloScrittura = $this->isGranted('ROLE_MONITORAGGIO_SCRITTURA');
        $risultato['ruolo_scrittura'] = $ruoloScrittura;

        return $this->render('MonitoraggioBundle:Trasferimenti:elencoTrasferimenti.html.twig', $risultato);
    }

    /**
     * @Route("/elenco_pulisci/{sort}/{direction}/{page}", defaults={"sort": "t.id", "direction": "asc", "page": "1"}, name="elenco_trasferimenti_pulisci")
     * Template("MonitoraggioBundle:Trasferimenti:elencoTrasferimenti.html.twig")
     * @PaginaInfo(titolo="Elenco trasferimenti", sottoTitolo="mostra l'elenco dei trasferimenti")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco trasferimenti")})
     */
    public function elencoTrasferimentiPulisciAction() {
        $datiRicerca = new RicercaTrasferimento();
        $this->get('ricerca')->pulisci($datiRicerca);

        return $this->redirectToRoute('elenco_trasferimenti');
    }

    /**
     * @Route("/dettaglio_trasferimento/{idTrasferimento}", name="dettaglio_trasferimento")
     * Template("MonitoraggioBundle:Trasferimenti:dettaglioTrasferimento.html.twig")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="dettaglio trasferimento")})
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     */
    public function dettaglioTrasferimentoAction($idTrasferimento) {
        $trasferimento = $this->getEm()->getRepository('MonitoraggioBundle:Trasferimento')->findOneById($idTrasferimento);
        $form = $this->createForm('MonitoraggioBundle\Form\TrasferimentoType', $trasferimento, array('url_indietro' => $this->generateUrl('elenco_trasferimenti')));
        $form->handleRequest($this->getCurrentRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->getEm()->persist($trasferimento);
                $this->getEm()->flush();
                $this->addFlash('success', 'Modifica dei dati effettuata con successo.');
            } catch (\Exception $exc) {
                $this->get('monolog.logger.schema31')->error($exc->getTraceAsString());
                $this->addFlash('error', 'Errore nella modifica dei dati.');
            }
        }

        return $this->render('MonitoraggioBundle:Trasferimenti:dettaglioTrasferimento.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/ajax_dettaglio_trasferimento", name="ajax_dettaglio_trasferimento")
     */
    public function ajaxGetSoggetto() {
        $request = $this->getCurrentRequest();
        $q = $request->query->get('q');
        $soggetti = $this->getEm()->getRepository('MonitoraggioBundle:Trasferimento')->cercaSoggetti($q);
        $response = new JsonResponse($soggetti);

        return $response;
    }

    /**
     * @Route("/nuovo_trasferimento", name="nuovo_trasferimento")
     * Template("MonitoraggioBundle:Trasferimenti:nuovoTrasferimento.html.twig")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="inserimento nuovo trasferimento")})
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     * @PaginaInfo(titolo="Inserimento nuovo trasferimento", sottoTitolo="")
     */
    public function nuovoTrasferimentoAction() {
        $em = $this->getEm();
        $trasferimento = new Trasferimento();
        $form = $this->createForm('MonitoraggioBundle\Form\TrasferimentoType', $trasferimento, array(
            'url_indietro' => $this->generateUrl('elenco_trasferimenti'),
        ));
        $form->handleRequest($this->getCurrentRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($trasferimento);
                $em->flush();
                return $this->addSuccessRedirect('Trasferimento salvato con successo', 'elenco_trasferimenti');
            } catch (\Exception $e) {
                $this->addError('Errore durante salvataggio informazioni');
            }
        }
        return $this->render('MonitoraggioBundle:Trasferimenti:nuovoTrasferimento.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
