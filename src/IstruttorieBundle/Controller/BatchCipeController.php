<?php

namespace IstruttorieBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use BaseBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use RichiesteBundle\Service\Cipe\RichiestaCipeService;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use RichiesteBundle\Entity\RichiestaCupBatch;
use IstruttorieBundle\Form\Entity\RicercaIstruttoriaCipe;

/**
 * @Route("/cipe")
 */
class BatchCipeController extends BaseController {

    protected $em;
    protected $procedura;
    
        
    /**
     * @Route("/elenco/{sort}/{direction}/{page}", defaults={"sort" = "", "direction" = "asc", "page" = "1"}, name="elenco_richieste_cipe")
     *
     * @PaginaInfo(titolo="Elenco richieste CIPE",sottoTitolo="generazione file batch")
     * @Menuitem(menuAttivo = "elencoGeneraCup")
     */
    public function elencoRichiesteCipeAction() {

		ini_set("memory_limit","512M");
        $this->em = $this->getEm();
		
		$datiRicerca = new RicercaIstruttoriaCipe();
		$datiRicerca->setCup(1);
		$datiRicerca->setNumeroElementiPerPagina(999999);
		$datiRicerca->setMetodoRicerca("getRichiesteInIstruttoriaCipe");
		$datiRicerca->setBypassMaxElementiPerPagina(true);
		$risultato = $this->get("ricerca")->ricerca($datiRicerca);
		$listaRichieste = $risultato["risultato"];
//        $listaRichieste = $this->em->getRepository('IstruttorieBundle:IstruttoriaRichiesta')
//                                   ->findBy( array('richiedi_cup' => 1,
//                                                   'codice_cup' => NULL));

        return $this->render('IstruttorieBundle:Istruttoria:elencoRichiesteCup.html.twig', 
                array(
                    'tipo'               => 'elenco',
                    'listaRichiesteCipe' => $listaRichieste,
					"formRicercaIstruttoria" => $risultato["form_ricerca"], 
					"filtro_attivo" => $risultato["filtro_attivo"]
                ));
        
    }
	
	
	

    /**
     * @Route("/valida_richieste", name="valida_richieste")
     * @Method({"POST"})     
     * @PaginaInfo(titolo="Elenco richieste CIPE",sottoTitolo="generazione file batch")
     * @Menuitem(menuAttivo = "elencoGeneraCup")
     */
    public function validaRichiesteCipeAction(Request $request) {
        
        try {
			
            $this->em = $this->getEm();
            $elencoRichieste = $request->get('check');
            $arrayRichiesteIstruttorie = array();
            //Rimuovo l'attributo 'elencoVal' 
            $this->getSession()->remove('elencoVal');
            
			ini_set('memory_limit','512M');
            //Viene effettuata la validazione per l'intero elenco di richieste selezionate
            $elencoValidazione = $this->get('richiesta_cipe_service')->validaRichiestaProtocollazioneBatch($elencoRichieste);
            
            if (count($elencoValidazione) > 0) {

                
                foreach ($elencoValidazione as $key_ricId => $val_RicId) {                    
                    $richiestaIstruttoria = $this->em->getRepository('IstruttorieBundle:IstruttoriaRichiesta')
                                                     ->find($key_ricId);
                    
                    ( count($val_RicId)>0 ) ? $validazione = "non valida" : $validazione = "valida"; 
					
                    $richiestaIstruttoria->setValidazione($validazione);
                    $arrayRichiesteIstruttorie[] = $richiestaIstruttoria;
					
                    $errore_validazione[$key_ricId] = array(
                                                            "errori"        => $val_RicId, 
                                                            "protocollo"    => $richiestaIstruttoria->getRichiesta()->getProtocollo(),
                                                            "denominazione" => $richiestaIstruttoria->getRichiesta()->getSoggetto()->getDenominazione()
                                                            );
                }

                $this->getSession()->set('elencoVal', $errore_validazione);
            }

            return $this->render('IstruttorieBundle:Istruttoria:elencoRichiesteCup.html.twig', 
                    array(
                        'tipo'                 => 'elencovalidato',
                        'listaRichiesteCipe'   => $arrayRichiesteIstruttorie
                    ));

        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * @Route("/richieste_non_valide", name="richieste_non_valide")
     * @PaginaInfo(titolo="Elenco richieste CIPE",sottoTitolo="richieste non valide")
     * @Menuitem(menuAttivo = "elencoGeneraCup")
     */
    public function elencoRichiesteCipeNonValideAction() {

        $elencoValidazione = $this->getSession()->get('elencoVal');
        return $this->render('IstruttorieBundle:Istruttoria:elencoRichiesteCupNonValide.html.twig', 
                    array('listaErroriRichieste' => $elencoValidazione));
        
    }
    
    /**
     * @Route("/invia_elenco_richieste", name="invia_elenco_richieste")
     * @PaginaInfo(titolo="Elenco richieste CIPE",sottoTitolo="generazione file batch")
     * @Menuitem(menuAttivo = "elencoGeneraCup")
     */
    public function inviaelencoRichiesteCipeAction(Request $request){
            $elencoRichieste = $request->get('check');
			ini_set('memory_limit', '512M');
            //Viene generata la richiesta cup batch per l'intero elenco di richieste selezionate
			/* @var $richiestaCupBatch RichiestaCupBatch */

			$this->getSession()->remove("elencoVal");

            $richiestaCupBatch = $this->get('richiesta_cipe_service')->generaRichiestaProtocollazioneBatch($elencoRichieste);
        
			return $this->redirectToRoute('richiestacupbatch_show', array("id" => $richiestaCupBatch->getId()));
        
    }
	
    /**
	 * @Route("/elenco_richieste_cipe_pulisci", name="elenco_richieste_cipe_pulisci")
	 */
	public function elencoRichiesteCipePulisciAction() {
		$this->get("ricerca")->pulisci(new RicercaIstruttoriaCipe());
		return $this->redirectToRoute("elenco_richieste_cipe");
	}

}