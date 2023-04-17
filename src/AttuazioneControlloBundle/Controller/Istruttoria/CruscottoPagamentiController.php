<?php

namespace AttuazioneControlloBundle\Controller\Istruttoria;

use BaseBundle\Controller\BaseController;
use BaseBundle\Exception\SfingeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use BaseBundle\Annotation\ControlloAccesso;
use DocumentoBundle\Entity\DocumentoFile;
use IstruttorieBundle\Entity\DocumentoIstruttoria;

/**
 * @Route("/istruttoria/cruscotto_pagamenti")
 */
class CruscottoPagamentiController extends BaseController {

	/**
	 * @Route("/elenco_istruttori/{page}", defaults={"page" = "1"}, name="elenco_istruttori_pagamenti")
	 * @PaginaInfo(titolo="Elenco istruttori pagamenti",sottoTitolo="mostra l'elenco degli istruttori dei pagamenti")
	 * @Menuitem(menuAttivo = "elencoIstruttoriPagamenti")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco istruttori")})
	 */
	public function elencoIstruttoriAction() {
        $repo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento");
        $ricerca_utenti = new \UtenteBundle\Form\Entity\RicercaUtenti();
        $ricerca_utenti->setRuoli("ISTRUTTORE_ATC");
        $ricerca_utenti->setNumeroElementi(10);
        
        $risultato = $this->get("ricerca")->ricerca($ricerca_utenti);
        
        $dati_aggiuntivi = array();
        
        foreach ($risultato["risultato"] as $istruttore) {       
            $count_istruite = $repo->getPagamentiIstruttoreCount($istruttore, true);
            $count_assegnate = $repo->getPagamentiIstruttoreCount($istruttore, false);
            
            $dati_aggiuntivi[$istruttore->getId()] = array("completate" => $count_istruite, "assegnate" => $count_assegnate);
        }

		return $this->render('AttuazioneControlloBundle:Istruttoria\CruscottoPagamenti:elencoIstruttori.html.twig', array('risultati' => $risultato["risultato"], 'dati_aggiuntivi' => $dati_aggiuntivi));
	} 
    
	/**
	 * @Route("/elenco_pagamenti_assegnati/{id_istruttore}/{page}", defaults={"page" = "1"}, name="elenco_pagamenti_assegnati")
	 * @PaginaInfo(titolo="Pagamenti assegnati",sottoTitolo="mostra l'elenco dei pagamenti assegnati ad un istruttore")
	 * @Menuitem(menuAttivo = "elencoIstruttoriPagamenti")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco istruttori", route="elenco_istruttori_pagamenti" )})
	 */
	public function pagamentiAssegnatiAction($id_istruttore) {
        $istruttore = $this->getEm()->getRepository("SfingeBundle\Entity\Utente")->find($id_istruttore);
        $ricerca_pagamenti = new \AttuazioneControlloBundle\Form\Entity\Istruttoria\RicercaPagamenti();
        $ricerca_pagamenti->setIstruttoreCorrente($istruttore);
        $ricerca_pagamenti->setCompletata(false);
        
        $risultato = $this->get("ricerca")->ricerca($ricerca_pagamenti);
        
        $this->container->get("pagina")->aggiungiElementoBreadcrumb($istruttore->__toString());

		return $this->render('AttuazioneControlloBundle:Istruttoria\CruscottoPagamenti:pagamentiAssegnati.html.twig', array('risultati' => $risultato["risultato"]));
	}
    
	/**
	 * @Route("/assegna_istruttoria_pagamento/{id_pagamento}", name="assegna_istruttoria_pagamento")
	 * @PaginaInfo(titolo="Assegnazione istruttoria pagamento")
	 * @Menuitem(menuAttivo = "elencoIstruttoriaPagamenti")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco pagamenti", route="elenco_istruttoria_pagamenti" ),
     *                       @ElementoBreadcrumb(testo="Assegnazione istruttoria")})
	 */
	public function assegnaIstruttoriaPagamentoAction(\Symfony\Component\HttpFoundation\Request $request, $id_pagamento) {
		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
                   
        $assegnamento = new \AttuazioneControlloBundle\Entity\Istruttoria\AssegnamentoIstruttoriaPagamento();       
        $assegnamento->setDataAssegnamento(new \DateTime());
        $assegnamento->setPagamento($pagamento);
        $assegnamento->setAssegnatore($this->getUser());
        
        $options["istruttori"] = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->getIstruttoriPagamenti();
		$options["url_indietro"] = $this->generateUrl('elenco_istruttoria_pagamenti');
        $options["disabled"] = !$this->isGranted("ROLE_ISTRUTTORE_SUPERVISORE_ATC");
		$form = $this->createForm('AttuazioneControlloBundle\Form\Istruttoria\AssegnamentoIstruttoriaPagamentoType', $assegnamento, $options);

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				try {
                    if (!is_null($pagamento->getAssegnamentoIstruttoriaAttivo())) {
                        $pagamento->getAssegnamentoIstruttoriaAttivo()->setAttivo(false);
                    }                    
                    $assegnamento->setAttivo(true);
                    $this->getEm()->persist($assegnamento);
					$this->getEm()->flush();
					$this->addFlash('success', "Istruttoria pagamento correttamente assegnata");

					return $this->redirect($this->generateUrl('elenco_istruttoria_pagamenti'));
				} catch (\Exception $e) {
					$this->addFlash('error', "Si è verificato un problema nel salvataggio. Si prega di riprovare o contattare l'assistenza");
                    $this->get("logger")->error($e->getMessage());
				}
			}
		}

		$form_params["form"] = $form->createView();
        $form_params["pagamento"] = $pagamento;

        return $this->render("AttuazioneControlloBundle:Istruttoria\CruscottoPagamenti:assegnaIstruttoriaPagamento.html.twig", $form_params);
	}    
	
}
