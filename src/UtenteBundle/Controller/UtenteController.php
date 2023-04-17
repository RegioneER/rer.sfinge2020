<?php

/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 18/01/16
 * Time: 15:50
 */

namespace UtenteBundle\Controller;

use BaseBundle\Controller\BaseController;
use SfingeBundle\Entity\Utente;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use UtenteBundle\Form\Entity\RicercaUtenti;
use BaseBundle\Annotation\ControlloAccesso;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class UtenteController extends BaseController {
    /**
     * @Route("/crea_utente", name="crea_utente")
     * @PaginaInfo(titolo="Nuovo utente", sottoTitolo="pagina per la creazione di una nuova utenza")
     * @Menuitem(menuAttivo="creaUtente")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Crea utente")})
     */
    public function creaUtenteAction() {
        $userManager = $this->get('fos_user.user_manager');
        $utente = $userManager->createUser();
        return $this->get('gestione_utenti')->gestioneUtente($utente);
    }

    /**
     * @Route("/modifica_utente/{id_utente}", name="modifica_utente")
     * @PaginaInfo(titolo="Modifica utente", sottoTitolo="pagina per la modifica di un'utenza")
     * @Menuitem(menuAttivo="creaUtente")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco utenti", route="elenco_utenti"), @ElementoBreadcrumb(testo="Modifica utente")})
     * @ControlloAccesso(contesto="utente", classe="SfingeBundle:Utente", opzioni={"id" : "id_utente"})
     * @ParamConverter("utente", options={"mapping" : {"id_utente" : "id"}})
     */
    public function modificaUtenteAction(Utente $utente) {
        return $this->get('gestione_utenti')->gestioneUtente($utente);
    }

    /**
     * @Route("/elenco_utenti/{sort}/{direction}/{page}", defaults={"sort" : "u.id", "direction" : "asc", "page" : "1"}, name="elenco_utenti")
     * @PaginaInfo(titolo="Elenco utenti", sottoTitolo="pagina con la lista di tutti gli utenti")
     * @Menuitem(menuAttivo="elencoUtenti")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco utenti")})
     */
    public function elencoUtentiAction() {
        ini_set('memory_limit', '2G');
        $datiRicerca = new RicercaUtenti();
        if ($this->isGranted("ROLE_ADMIN_PA")) {
            $datiRicerca->setRuoliEsclusi(["ROLE_SUPER_ADMIN"]);
        }
        $risultato = $this->get("ricerca")->ricerca($datiRicerca);
        return $this->render('UtenteBundle:Utente:elencoUtenti.html.twig', ['utenti' => $risultato["risultato"], "formRicercaUtenti" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"],
        ]);
    }

    /**
     * @Route("/elenco_persone_pulisci", name="elenco_utenti_pulisci")
     */
    public function elencoUtentiPulisciAction() {
        $this->get("ricerca")->pulisci(new RicercaUtenti());
        return $this->redirectToRoute("elenco_utenti");
    }

    /**
     * @Route("/visualizza_utente/{id_utente}", name="visualizza_utente")
     * @Template("UtenteBundle:Utente:visualizzaUtente.html.twig")
     * @PaginaInfo(titolo="Dati utente", sottoTitolo="pagina contentente i dati dell'utenti")
     * @Menuitem(menuAttivo="elencoUtenti")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco utenti", route="elenco_utenti"), @ElementoBreadcrumb(testo="Visualizza utente")})
     * @ControlloAccesso(contesto="utente", classe="SfingeBundle:Utente", opzioni={"id" : "id_utente"})
     * @ParamConverter("utente", options={"mapping" : {"id_utente" : "id"}})
     */
    public function visualizzaUtenteAction(Utente $utente) {
        return ['utente' => $utente];
    }

    /**
     * @Route("/cancella_utente/{id_utente}", name="cancella_utente")
     * @ControlloAccesso(contesto="utente", classe="SfingeBundle:Utente", opzioni={"id" : "id_utente"})
     * @ParamConverter("utente", options={"mapping" : {"id_utente" : "id"}})
     */
    public function cancellaUtenteAction(Utente $utente) {
        $this->get('base')->checkCsrf('token');
        $em = $this->getDoctrine()->getManager();

        try {
            $utente->setEnabled(false);
            //$utente->getPersona()->setDataCancellazione(new \DateTime());
            //$utente->setPersona(null);
            $em->persist($utente);
            $em->flush();
            $this->addFlash('success', "Utente disattivato correttamente");
            return $this->redirect($this->generateUrl('elenco_utenti'));
        } catch (\Exception $e) {
            $this->get('logger')->error($e->getMessage());
            $this->addFlash('error', "Errore nel salvaltaggio delle informazioni");
        }
    }

    /**
     * @Route("/riattiva_utente/{id_utente}", name="riattiva_utente")
     * @ControlloAccesso(contesto="utente", classe="SfingeBundle:Utente", opzioni={"id" : "id_utente"})
     * @ParamConverter("utente", options={"mapping" : {"id_utente" : "id"}})
     */
    public function riattivaUtenteAction(Utente $utente) {
        $this->get('base')->checkCsrf('token');
        $em = $this->getDoctrine()->getManager();

        try {
            $utente->setEnabled(true);
            $filter = $em->getFilters()->enable('softdeleteable');
            $filter->disableForEntity('AnagraficheBundle\Entity\Persona');

            $utente->getPersona()->setDataCancellazione(null);

            $em->persist($utente);
            $em->flush();
            $filter->enableForEntity('AnagraficheBundle\Entity\Persona');
            $this->addFlash('success', "Utente cancellato correttamente");
            return $this->redirect($this->generateUrl('elenco_utenti'));
        } catch (\Exception $e) {
            $this->get('logger')->error($e->getMessage());
            $this->addFlash('error', "Errore nel salvaltaggio delle informazioni");
        }
    }
}
