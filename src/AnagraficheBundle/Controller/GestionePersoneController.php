<?php

namespace AnagraficheBundle\Controller;

use AnagraficheBundle\Entity\Persona;
use AnagraficheBundle\Form\Entity\RicercaPersonaAdmin;
use BaseBundle\Controller\BaseController;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GestionePersoneController extends BaseController {
    /**
     * @Route("/elenco/{sort}/{direction}/{page}", defaults={"sort" : "p.id", "direction" : "asc", "page" : "1"}, name="elenco_persone_admin")
     * @Template
     * @Menuitem(menuAttivo="elencoPersoneAdmin")
     * @PaginaInfo(titolo="Elenco persone", sottoTitolo="pagina per gestione persone censite a sistema")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco persone")})
     */
    public function elencoPersoneAction() {
        $ricerca = new RicercaPersonaAdmin();
        $ricerca->setUtenteRicercante($this->getUser());

        $risultato = $this->get("ricerca")->ricerca($ricerca);

        return ['persone' => $risultato["risultato"], "form_ricerca" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"]];
    }

    /**
     * @Route("/elenco_admin_pulisci", name="elenco_persone_admin_pulisci")
     */
    public function elencoPersonePulisciAction() {
        $this->get("ricerca")->pulisci(new RicercaPersonaAdmin());
        return $this->redirectToRoute("elenco_persone_admin");
    }

    /**
     * @Route("/crea", name="crea_persona_admin")
     * @PaginaInfo(titolo="Nuova Persona", sottoTitolo="")
     * @Menuitem(menuAttivo="creaPersonaAdmin")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco persone", route="elenco_persone_admin"), @ElementoBreadcrumb(testo="Crea persona")})
     */
    public function creaPersonaAdminAction() {
        return $this->get('inserimento_persona')->inserisciPersona($this->generateUrl("elenco_persone_admin"), 'elenco_persone_admin');
    }

    /**
     * @Route("/crea_pa", name="crea_persona_pa_admin")
     * @PaginaInfo(titolo="Nuova Persona Pa", sottoTitolo="")
     * @Menuitem(menuAttivo="creaPersonaPaAdmin")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco persone", route="elenco_persone_admin"), @ElementoBreadcrumb(testo="Crea persona Pa")})
     */
    public function creaPersonaPaAdminAction() {
        $opzioni['is_utente_PA'] = true;
        return $this->get('inserimento_persona')->inserisciPersona($this->generateUrl("elenco_persone_admin"), 'elenco_persone_admin', [], $opzioni);
    }

    /**
     * @Route("/modifica/{id_persona}", name="modifica_persona_admin")
     * @Menuitem(menuAttivo="elencoPersoneAdmin")
     * @Template("AnagraficheBundle:Persona:datiPersona.html.twig")
     * @PaginaInfo(titolo="Modifica persona")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco persone", route="elenco_persone_admin"), @ElementoBreadcrumb(testo="Modifica persona")})
     * @param mixed $id_persona
     */
    public function modificaPersonaAction($id_persona) {
        $em = $this->getEm();

        $persona = $em->getRepository('AnagraficheBundle:Persona')->findOneById($id_persona);

        if (!$persona) {
            throw $this->createNotFoundException('Risorsa non trovata');
        }

        $utente = $persona->getUtente();
        $isPA = !is_null($utente) ? $utente->isPA() : false;

        $funzioniService = $this->get('funzioni_utili');
        $request = $this->getCurrentRequest();
        $data = $funzioniService->getDataComuniFromRequest($request, $persona->getLuogoResidenza());
        $dataPersona = $funzioniService->getDataComuniPersonaFromRequest($request, $persona);
        $options["readonly"] = false;
        $options["dataIndirizzo"] = $data;
        $options["dataPersona"] = $dataPersona;
        $options["disabilitaEmail"] = true;
        $options["url_indietro"] = $this->generateUrl("elenco_persone_admin");
        $options["validation_groups"] = $isPA ? ["Default"] : ["Default", "persona"];
        $form = $this->createForm('AnagraficheBundle\Form\PersonaType', $persona, $options);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                try {
                    if ($isPA) {
                        $luogoResidenza = (array) $persona->getLuogoResidenza();
                        $luogoResidenzaImplode = implode('', $luogoResidenza);
                        $luogoResidenza = $persona->getLuogoResidenza();
                        if (empty($luogoResidenzaImplode)) {
                            $persona->setLuogoResidenza(null);
                        } elseif (is_null($luogoResidenza->getVia()) || is_null($luogoResidenza->getNumeroCivico()) || is_null($luogoResidenza->getCap()) || is_null($luogoResidenza->getStato())) {
                            $persona->setLuogoResidenza(null);
                        }
                    }
                    $em->persist($persona);
                    $em->flush();

                    $this->addFlash('success', "Modifiche salvate correttamente");
                    return $this->redirect($this->generateUrl('elenco_persone_admin'));
                } catch (\Exception $e) {
                    $this->get('logger')->error($e->getMessage());
                    $this->addFlash('error', "Errore nel salvaltaggio delle informazioni");
                }
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["persona"] = $persona;

        return $form_params;
    }

    /**
     * @Route("/visualizza/{id_persona}", name="visualizza_persona_admin")
     * @Template("AnagraficheBundle:Persona:datiPersona.html.twig")
     * @PaginaInfo(titolo="Visualizza persona")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco persone", route="elenco_persone_admin"), @ElementoBreadcrumb(testo="Visualizza persona")})
     * @Menuitem(menuAttivo="elencoPersone")
     * @param mixed $id_persona
     */
    public function visualizzaPersonaAction($id_persona) {
        $em = $this->getEm();

        $persona = $em->getRepository('AnagraficheBundle:Persona')->findOneById($id_persona);

        if (!$persona) {
            throw $this->createNotFoundException('Risorsa non trovata');
        }
        $funzioniService = $this->get('funzioni_utili');
        $request = $this->getCurrentRequest();
        $data = $funzioniService->getDataComuniFromRequest($request, $persona->getLuogoResidenza());
        $dataPersona = $funzioniService->getDataComuniPersonaFromRequest($request, $persona);
        $options["readonly"] = true;
        $options["dataIndirizzo"] = $data;
        $options["dataPersona"] = $dataPersona;
        $options["disabilitaEmail"] = true;
        $options["url_indietro"] = $this->generateUrl("elenco_persone_admin");
        $form = $this->createForm('AnagraficheBundle\Form\PersonaType', $persona, $options);

        $form_params["form"] = $form->createView();
        $form_params["persona"] = $persona;

        return $form_params;
    }

    /**
     * @Route("/associa_utente_admin/{pa}", name="associa_utente_admin", defaults={"pa" : ""})
     * @PaginaInfo(titolo="Associa utente", sottoTitolo="pagina per associare un utente ad una persona")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Associa utenti")})
     * @param mixed $pa
     */
    public function associaUtenteAdminAction(Request $request, $pa): Response {
        $associaPersonaUtente = new \AnagraficheBundle\Form\Entity\AssociaPersonaUtente();

        $options['is_pa'] = (true == $pa);
        $this->get("pagina")->setMenuAttivo(true == $pa ? "associaUtentePA" : "associaUtente", $request->getSession());

        $form = $this->createForm(\AnagraficheBundle\Form\AssociaUtenteType::class, $associaPersonaUtente, $options);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $persona = $associaPersonaUtente->getPersona();
            $utente = $associaPersonaUtente->getUtente();
            $utente->setPersona($persona);
            $utente->setDatiPersonaInseriti(true);

            $em = $this->getEm();
            try {
                $em->flush();
                $this->addFlash('success', "Associazione creata correttamente");

                return $this->redirect($this->generateUrl('associa_utente_admin', ["pa" => $pa]));
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        $dati["form"] = $form->createView();
        return $this->render("AnagraficheBundle:Persona:associaUtente.html.twig", $dati);
    }
}
