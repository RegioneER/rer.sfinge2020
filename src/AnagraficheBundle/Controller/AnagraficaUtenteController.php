<?php

namespace AnagraficheBundle\Controller;

use AnagraficheBundle\Entity\Persona;
use AnagraficheBundle\Form\Entity\RicercaPersone;
use BaseBundle\Annotation\ControlloAccesso;
use BaseBundle\Controller\BaseController;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class AnagraficaUtenteController extends BaseController {
    /**
     * @Route("/persona_crea", name="crea_persona")
     * @PaginaInfo(titolo="Nuova Persona", sottoTitolo="")
     * @Menuitem(menuAttivo="creaPersona")
     */
    public function creaPersonaAction() {
        $opzioni["is_utente_PA"] = false;
        $ruoliUtente = $this->get('gestione_utenti')->getRuoliUtente($this->getUser());
        if (in_array('ROLE_UTENTE_PA', $ruoliUtente)) {
            $opzioni["is_utente_PA"] = true;
        }

        if ($this->isUtente()) {
            $this->addFlash("warning", "Si ricorda che per l’assegnazione dei ruoli di OPERATORE ed UTENTE PRINCIPALE non bisogna creare la relativa persona in questa sezione ma gli utenti stessi devono accedere a Sfinge2020, registrare la propria utenza ed inserire tramite quella i propri dati");
            $this->addFlash("warning", "Attenzione: I liberi professionisti e le associazioni di professionisti dovranno registrarsi nella sezione AZIENDE");
        }

        $csrfTokenManager = $this->get("security.csrf.token_manager");
        $token = $csrfTokenManager->getToken("token")->getValue();

        return $this->get('inserimento_persona')->inserisciPersona($this->generateUrl("home"), 'home', ['_token' => $token], $opzioni);
    }

    /**
     * @Route("/elenco_persone/{sort}/{direction}/{page}", defaults={"sort" : "p.id", "direction" : "asc", "page" : "1"}, name="elenco_persone")
     * @PaginaInfo(titolo="Elenco persone", sottoTitolo="pagina per gestione delle persone censite a sistema")
     * @Menuitem(menuAttivo="elencoPersone")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco persone")})
     */
    public function elencoPersoneAction() {
        $utente = $this->getUser();
        $datiRicerca = new RicercaPersone();
        $datiRicerca->setUtente($utente->getUsername());

        $risultato = $this->get("ricerca")->ricerca($datiRicerca);

        return $this->render('AnagraficheBundle:Persona:elencoPersone.html.twig', ['persone' => $risultato["risultato"], "formRicercaPersone" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"],
        ]);
    }

    /**
     * @Route("/elenco_persone_pulisci", name="elenco_persone_pulisci")
     */
    public function elencoPersonePulisciAction() {
        $this->get("ricerca")->pulisci(new RicercaPersone());
        return $this->redirectToRoute("elenco_persone");
    }

    /**
     * @Route("/visualizza/{id_persona}", name="visualizza_persona_anagrafica")
     * @Template("AnagraficheBundle:Persona:datiPersona.html.twig")
     * @PaginaInfo(titolo="Visualizza persona")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco persone", route="elenco_persone"), @ElementoBreadcrumb(testo="Visualizza persona")})
     * @Menuitem(menuAttivo="elencoPersone")
     * @ControlloAccesso(contesto="persona", classe="AnagraficheBundle:Persona", opzioni={"id" : "id_persona"}, azione=\AnagraficheBundle\Security\PersonaVoter::SHOW)
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
        $options["url_indietro"] = $this->generateUrl("elenco_persone");
        $form = $this->createForm('AnagraficheBundle\Form\PersonaType', $persona, $options);

        $form_params["form"] = $form->createView();
        $form_params["persona"] = $persona;

        return $form_params;
    }

    /**
     * @Route("/modifica/{id_persona}", name="modifica_persona_anagrafica")
     * @Template("AnagraficheBundle:Persona:datiPersona.html.twig")
     * @PaginaInfo(titolo="Modifica persona")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco persone", route="elenco_persone"), @ElementoBreadcrumb(testo="Modifica persona")})
     * @Menuitem(menuAttivo="elencoPersone")
     * @ControlloAccesso(contesto="persona", classe="AnagraficheBundle:Persona", opzioni={"id" : "id_persona"}, azione=\AnagraficheBundle\Security\PersonaVoter::EDIT)
     * @param mixed $id_persona
     */
    public function modificaPersonaAction($id_persona) {
        $em = $this->getEm();

        $persona = $em->getRepository('AnagraficheBundle:Persona')->findOneById($id_persona);

        if (!$persona) {
            throw $this->createNotFoundException('Risorsa non trovata');
        }
        $funzioniService = $this->get('funzioni_utili');
        $request = $this->getCurrentRequest();
        $data = $funzioniService->getDataComuniFromRequest($request, $persona->getLuogoResidenza());
        $dataPersona = $funzioniService->getDataComuniPersonaFromRequest($request, $persona);
        $options["readonly"] = false;
        $options["dataIndirizzo"] = $data;
        $options["dataPersona"] = $dataPersona;
        $options["disabilitaEmail"] = true;
        $options["url_indietro"] = $this->generateUrl("elenco_persone");
        $form = $this->createForm('AnagraficheBundle\Form\PersonaType', $persona, $options);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                try {
                    $em->persist($persona);
                    $em->flush();

                    $this->addFlash('success', "Modifiche salvate correttamente");
                    return $this->redirect($this->generateUrl('elenco_persone'));
                } catch (\Exception $e) {
                    $this->get('logger')->error($e->getMessage());
                    $this->addFlash('error', "Errore nel salvataggio delle informazioni");
                }
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["persona"] = $persona;

        return $form_params;
    }
}
