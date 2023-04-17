<?php

namespace IstruttorieBundle\Controller;

use BaseBundle\Controller\BaseController;
use IstruttorieBundle\Entity\AssegnamentoIstruttoriaRichiesta;
use IstruttorieBundle\Form\Entity\RicercaIstruttoria;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use Symfony\Component\HttpFoundation\Request;


class CruscottoRichiesteController extends BaseController {

	/**
	 * @Route("/elenco_istruttori/{page}", defaults={"page" = "1"}, name="elenco_istruttori_richieste")
	 * @PaginaInfo(titolo="Elenco istruttori richieste",sottoTitolo="mostra l'elenco degli istruttori delle richieste")
	 * @Menuitem(menuAttivo = "elencoIstruttoriRichieste")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco istruttori")})
	 */
	public function elencoIstruttoriAction() {
        $repo = $this->getEm()->getRepository("RichiesteBundle:Richiesta");
        $ricerca_utenti = new \UtenteBundle\Form\Entity\RicercaUtenti();
        $ricerca_utenti->setRuoli("ISTRUTTORE");
        $ricerca_utenti->setNumeroElementi(10);
        
        $risultato = $this->get("ricerca")->ricerca($ricerca_utenti);
        
        $dati_aggiuntivi = array();
        foreach ($risultato["risultato"] as $istruttore) {       
            $count_istruite = $repo->getRichiesteIstruttoreCount($istruttore, true);
            $count_assegnate = $repo->getRichiesteIstruttoreCount($istruttore, false);
            $dati_aggiuntivi[$istruttore->getId()] = array("completate" => $count_istruite, "assegnate" => $count_assegnate);
        }

		return $this->render('IstruttorieBundle:Istruttoria/CruscottoRichieste:elencoIstruttori.html.twig', 
            array('risultati' => $risultato["risultato"], 'dati_aggiuntivi' => $dati_aggiuntivi));
	}

    /**
     * @Route("/assegna_istruttoria_richiesta/{id_richiesta}", name="assegna_istruttoria_richiesta")
     * @PaginaInfo(titolo="Assegnazione istruttoria richiesta")
     * @Menuitem(menuAttivo = "elencoIstruttoriRichieste")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco operazioni inviate", route="elenco_richieste_inviate" ),
     *                       @ElementoBreadcrumb(testo="Assegnazione istruttoria")})
     * 
     * @param Request $request
     * @param $id_richiesta
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function assegnaIstruttoriaRichiestaAction(Request $request, $id_richiesta) {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);

        $assegnamento = new AssegnamentoIstruttoriaRichiesta();
        $assegnamento->setDataAssegnamento(new \DateTime());
        $assegnamento->setRichiesta($richiesta);
        $assegnamento->setAssegnatore($this->getUser());

        $options["istruttori"] = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->getIstruttoriRichieste();
        $options["url_indietro"] = $this->generateUrl('elenco_richieste_inviate');
        $options["disabled"] = !$this->isGranted("ROLE_ISTRUTTORE_SUPERVISORE");
        
        $form = $this->createForm('IstruttorieBundle\Form\AssegnamentoIstruttoriaRichiestaType', $assegnamento, $options);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    if (!is_null($richiesta->getAssegnamentoIstruttoriaAttivo())) {
                        $richiesta->getAssegnamentoIstruttoriaAttivo()->setAttivo(false);
                    }
                    $assegnamento->setAttivo(true);
                    $this->getEm()->persist($assegnamento);
                    $this->getEm()->flush();
                    $this->addFlash('success', "Istruttoria richiesta correttamente assegnata");

                    return $this->redirect($this->generateUrl('elenco_istruttori_richieste'));
                } catch (\Exception $e) {
                    $this->addFlash('error', "Si è verificato un problema nel salvataggio. Si prega di riprovare o contattare l'assistenza.");
                    $this->get("logger")->error($e->getMessage());
                }
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["richiesta"] = $richiesta;

        return $this->render("IstruttorieBundle:Istruttoria/CruscottoRichieste:assegnaIstruttoriaRichiesta.html.twig", $form_params);
    }

    /**
     * @Route("/elenco_richieste_assegnate/{id_istruttore}/{page}", defaults={"page" = "1"}, name="elenco_richieste_assegnate")
     * @PaginaInfo(titolo="Richieste assegnate",sottoTitolo="mostra l'elenco delle richieste assegnate ad un istruttore")
     * @Menuitem(menuAttivo = "elencoIstruttoriRichieste")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco istruttori", route="elenco_istruttori_richieste" )})
     * @param $id_istruttore
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function richiesteAssegnateAction($id_istruttore) {
        $istruttore = $this->getEm()->getRepository("SfingeBundle\Entity\Utente")->find($id_istruttore);
        $ricerca_richieste = new RicercaIstruttoria();
        $ricerca_richieste->setIstruttoreCorrente($istruttore);
        $ricerca_richieste->setUtente($this->getUser());

        $risultato = $this->get("ricerca")->ricerca($ricerca_richieste);

        $this->container->get("pagina")->aggiungiElementoBreadcrumb($istruttore->__toString());

        return $this->render('IstruttorieBundle:Istruttoria\CruscottoRichieste:richiesteAssegnate.html.twig', array('risultati' => $risultato["risultato"]));
    }
}
