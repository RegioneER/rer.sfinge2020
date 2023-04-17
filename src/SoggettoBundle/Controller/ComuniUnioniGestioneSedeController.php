<?php

namespace SoggettoBundle\Controller;

use BaseBundle\Annotation\ControlloAccesso;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use RichiesteBundle\Entity\Proponente;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SoggettoBundle\Entity\Sede;
use SoggettoBundle\Entity\Soggetto;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Soggetti Giuridici", route="elenco_soggetti_giuridici")})
 */
class ComuniUnioniGestioneSedeController extends Controller {
    /**
     * @Route("/aggiungi/{id_soggetto}", name="comune_aggiungi_sede_operativa")
     * @Template("SoggettoBundle:Sede:sede.html.twig")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Gestione sedi", route="elenco_sedi_operative_comune", parametri={"id_soggetto"}), @ElementoBreadcrumb(testo="Aggiunta sede")})
     * @PaginaInfo(titolo="Comune - Aggiunta sede")
     * @ControlloAccesso(contesto="soggetto", classe="SoggettoBundle:Soggetto", opzioni={"id" : "id_soggetto"}, azione="edit")
     * @param mixed $id_soggetto
     */
    public function aggungiSedeOperativaAction(Request $request, $id_soggetto) {
        $em = $this->getDoctrine()->getManager();
        $funzioniService = $this->get('funzioni_utili');

        /** @var Soggetto $azienda */
        $azienda = $em->getRepository('SoggettoBundle:Soggetto')->findOneById($id_soggetto);

        $sede = new Sede();
        $data = $funzioniService->getIndirizzoSedeOperativaAzienda($request, $sede->getIndirizzo());

        $options["dataIndirizzo"] = $data;
        $options["url_indietro"] = $this->generateUrl("elenco_sedi_operative_comune", ["id_soggetto" => $id_soggetto]);
        $options["pubblico"] = false;
                $options["validation_groups"] = ["Default", "persona", "sede"];

        $form = $this->createForm("SoggettoBundle\Form\SedeType", $sede, $options);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $sede->setSoggetto($azienda);

                try {
                    $em->persist($sede);
                    $em->flush();

                    $this->addFlash('success', "Modifiche salvate correttamente");

                    return $this->redirect($this->generateUrl('elenco_sedi_operative_comune', ['id_soggetto' => $azienda->getId()]));
                } catch (\Exception $e) {
                    $this->get('logger')->error($e->getMessage());
                    $this->addFlash('error', "Errore nel salvaltaggio delle informazioni");
                }
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["azienda"] = $azienda;
        $form_params["pubblico"] = $options["pubblico"];

        return $form_params;
    }

    /**
     * @Route("{id_richiesta}/aggiungi_rich/{id_proponente}", name="comune_aggiungi_sede_operativa_rich")
     * @Template("SoggettoBundle:Sede:sede.html.twig")
     * @PaginaInfo(titolo="Aggiunta sede", sottoTitolo="Aggiungi una sede")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco proponenti", route="elenco_proponenti", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Dettagli proponente", route="dettaglio_proponente", parametri={"id_richiesta", "id_proponente"}),
     * 				@ElementoBreadcrumb(testo="Aggiunta sede")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggettoMandatario", classe="RichiesteBundle:Proponente", opzioni={"id" : "id_proponente"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
     * @param mixed $id_richiesta
     * @param mixed $id_proponente
     */
    public function aggungiSedeOperativaDaRichiestaAction(Request $request, $id_richiesta, $id_proponente) {
        $em = $this->getDoctrine()->getManager();
        $funzioniService = $this->get('funzioni_utili');

        /** @var Proponente $proponente */
        $proponente = $em->getRepository('RichiesteBundle:Proponente')->find($id_proponente);

        /** @var Soggetto $azienda */
        $azienda = $proponente->getSoggetto();
        $sede = new Sede();
        $data = $funzioniService->getIndirizzoSedeOperativaAzienda($request, $sede->getIndirizzo());

        $indietro = $request->query->has('refer') ? 
			$request->query->get('refer') :
			$this->generateUrl("cerca_sede", array("id_richiesta"=>$id_richiesta, 'id_proponente' => $id_proponente));
        $options["dataIndirizzo"] = $data;
        $options["url_indietro"] = $indietro;
        $options["pubblico"] = false;
        $form = $this->createForm("SoggettoBundle\Form\SedeType", $sede, $options);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $sede->setSoggetto($azienda);

                try {
                    $em->persist($sede);
                    $em->flush();

                    $this->addFlash('success', "Modifiche salvate correttamente");

                    return $this->redirect($indietro);
                } catch (\Exception $e) {
                    $this->get('logger')->error($e->getMessage());
                    $this->addFlash('error', "Errore nel salvaltaggio delle informazioni");
                }
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["azienda"] = $azienda;
        $form_params["pubblico"] = $options["pubblico"];

        return $form_params;
    }
}
