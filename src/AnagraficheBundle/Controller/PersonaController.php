<?php

namespace AnagraficheBundle\Controller;

use AnagraficheBundle\Entity\Persona;
use AnagraficheBundle\Entity\PersonaRepository;
use BaseBundle\Controller\BaseController;
use LogicException;
use InvalidArgumentException;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PersonaController extends BaseController {

    /**
     * @Route("/persona_registra", name="registra_persona")
     * @PaginaInfo(titolo="Registra Persona", sottoTitolo="")
     * @Menuitem(menuAttivo="registraPersona")
     */
    public function registraPersonaAction() {
        $opzioni["utente"] = $this->getUser();
        $opzioni["is_utente_PA"] = false;
        $ruoliUtente = $this->get('gestione_utenti')->getRuoliUtente($this->getUser());
        if (in_array('ROLE_UTENTE_PA', $ruoliUtente)) {
            $opzioni["is_utente_PA"] = true;
        }

        $csrfTokenManager = $this->get("security.csrf.token_manager");
        $token = $csrfTokenManager->getToken("token")->getValue();

        return $this->get('inserimento_persona')->inserisciPersona($this->generateUrl("home"), 'home', ['_token' => $token], $opzioni);
    }

    /**
     * @Route("/persona_visualizza", name="visualizza_persona")
     * @PaginaInfo(titolo="Dati persona")
     * @Template("AnagraficheBundle:Persona:datiPersona.html.twig")
     */
    public function visualizzaPersonaAction() {
        $funzioniService = $this->get('funzioni_utili');
        $utente = $this->getUser();
        $persona = $utente->getPersona();
        if (is_null($persona)) {
            $this->addFlash('error', "Persona non associata all'utenza");
            return $this->redirectToRoute('home');
        }
        $request = $this->getCurrentRequest();
        $data = $funzioniService->getDataComuniFromRequest($request, $persona->getLuogoResidenza());
        $dataPersona = $funzioniService->getDataComuniPersonaFromRequest($request, $persona);
        $options["readonly"] = true;
        $options["dataIndirizzo"] = $data;
        $options["dataPersona"] = $dataPersona;
        $options["disabilitaEmail"] = true;
        $options["url_indietro"] = $this->generateUrl("home");

        $form = $this->createForm('AnagraficheBundle\Form\PersonaType', $persona, $options);

        $form_params["form"] = $form->createView();
        $form_params["persona"] = $persona;

        return $form_params;
    }

    /**
     * @Route("/persona_cerca_rest", name="persona_cerca_rest")
     * @throws LogicException 
     * @throws InvalidArgumentException 
     */
    public function cercaPersonaRest(Request $request): Response {
        /** @var PersonaRepository $personaRepository */
        $personaRepository = $this->getDoctrine()->getRepository(Persona::class);
        $query = $request->query->get('query', '');
        $resOggetti = $personaRepository->findPersonaByNomeOrCodiceFiscale($query);
        $resNormalizzato = \array_map(function(Persona $persona) {
            return [
                'id' => $persona->getId(),
                'nome' => $persona->getNome(),
                'cognome' => $persona->getCognome(),
                'codice_fiscale' => $persona->getCodiceFiscale(),
            ];
        }, $resOggetti);

        return new JsonResponse($resNormalizzato);
    }

    /**
     * @Route("/selezione_ruoli", name="selezione_ruoli")
     */
    public function selezioneRuoliAction() {
        return $this->render('UtenteBundle:Utente:selezioneRuolo.html.twig', array());
    }

    /**
     * @Route("/seleziona_ruolo/{contesto}", name="seleziona_ruolo")
     */
    public function selezionaRuoloAction($contesto) {
        $em = $this->getDoctrine()->getManager();
        $utente = $this->getUser();
        //ruoli base che sono in comune tra le due utenze
        //a:4:{i:0;s:9:"ROLE_USER";i:1;s:14:"ROLE_UTENTE_PA";i:2;s:19:"ROLE_ISTRUTTORE_ATC";i:3;s:15:"ROLE_VALUTATORE";}
        $ruoli = ['ROLE_USER','ROLE_UTENTE_PA','ROLE_ISTRUTTORE_ATC','ROLE_VALUTATORE'];
        if ($contesto == 'FESR') {
            $ruoli[] = 'ROLE_ISTRUTTORE_CONTROLLI'; //istruttore controlli per verifiche in loco
            $ruoli[] = 'ROLE_SUPERVISORE_CONTROLLI';
            $ruoli[] = 'ROLE_PAGAMENTI_READONLY'; //se sei un controllore loco vedi i pagamento ma non puoi editarli
        } elseif ($contesto == 'INVITALIA') {
            $ruoli[] = 'ROLE_ISTRUTTORE_INVITALIA';//per invitalia lato rendicontazione basta solo il ruiolo invitalia perchè ROLE_ISTRUTTORE_ATC è già in base
        } else {
            $this->addFlash('error', "Valore non valido");
            return $this->redirectToRoute('home');
        }

        try {
            $utente->setRoles($ruoli);
            $em->persist($utente);
            $em->flush();
        } catch (\Exception $e) {
            $this->get('logger')->error($e->getMessage());
            $this->addFlash('error', "Errore nel salvaltaggio delle informazioni");
        }

        return $this->redirect($this->generateUrl('home'));
    }

}
