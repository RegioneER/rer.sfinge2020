<?php

namespace CipeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use CipeBundle\Services\CipeService;
use CipeBundle\Services\WsGeneraCupService;
use CipeBundle\Entity\WsGeneraCup;
use CipeBundle\Entity\Ricerche\RicercaWsGeneraCup;
use BaseBundle\Service\IAttributiRicerca;
use Symfony\Component\HttpFoundation\Response;



class CipeBaseController extends Controller
{
	/**
	 * @var CipeService
	 */
	protected $CipeService;
	protected function getCipeService() { return $this->CipeService; }
	protected function setCipeService($CipeService) { $this->CipeService = $CipeService; }
	
	protected $profile="user";
	protected function getProfile() { return $this->profile; }
	protected function setProfile($profile) { $this->profile = $profile; }
	
	
	protected function initServices() {
		$CipeService = $this->get("cipe.cipe_service");
		$this->setCipeService($CipeService);
	}
	
	/**
	 * 
	 * @param integer $WsGeneraCup_id
	 * @param WsGeneraCup $WsGeneraCup
	 * @return Response
	 */
	public function visualizzaWsGeneraCup($WsGeneraCup_id, $WsGeneraCup = null) {
		if(\is_null($WsGeneraCup)) {
			$WsGeneraCup = $this->getDoctrine()->getRepository("\CipeBundle\Entity\WsGeneraCup")->findOneBy(array("id" => $WsGeneraCup_id));
		}

		$params = array(
						"WsGeneraCup"			=> $WsGeneraCup, 
						"profile"				=> $this->getProfile()
					
						);
		
		
        return $this->render('CipeBundle:wsgeneracup:ws_genera_cup.html.twig', $params);
		
	}
	
	
	public function visualizzaErrore($error_message, $redirect=null) {
		return $this->render('CipeBundle:generici:errore.html.twig', array("error_message" => $error_message, "redirect" => $redirect));

	}
	
    public function inoltraRichiestaAction($id_progetto)
    {
		$this->initServices();
		$DatiRichiesta = $this->getCipeService()->findDatiRichiestaCupGenerazione($id_progetto);
		$return = $this->getCipeService()->inoltraRichiestaCupGenerazione($DatiRichiesta);
		if(\is_string($return)) return $this->visualizzaErrore($return);
		
        return $this->visualizzaWsGeneraCup(null, $return);
    }
	
	
	
	public function elencoWsGeneraCupAction() {
		
		
		$datiRicerca = new RicercaWsGeneraCup();
		$this->get("ricerca")->pulisci($datiRicerca);
		$datiRicerca->setNumeroElementi(50);

		$risultato = $this->get("ricerca")->ricerca($datiRicerca);

		return $this->render('CipeBundle:wsgeneracup:elenco_ws_genera_cup.html.twig', 
				array(
						'wsGeneraCups'				=> $risultato["risultato"], 
						"formRicercaWsGeneraCup"	=> $risultato["form_ricerca"], 
						"filtro_attivo"				=> $risultato["filtro_attivo"],
						"profile"					=> $this->getProfile()
		));
		
	}
	
	/**
	 * 
	 * @param IAttributiRicerca $datiRicerca
	 * @param string $template
	 * @return Response
	 */
	protected function elencoClassificazioneCipe($datiRicerca, $template) {
		
		$tipo_classificazione_cipe = $this->get('session')->has('tipo_classificazione_cipe') ? $this->get('session')->get('tipo_classificazione_cipe') : null;
		if ($datiRicerca->getTipoClassificazione() != $tipo_classificazione_cipe) {
			$this->get("ricerca")->pulisci($datiRicerca);
			$this->get('session')->set('tipo_classificazione_cipe', $datiRicerca->getTipoClassificazione());
			$datiRicerca->setNumeroElementi(50);
		}
		
		$risultato = $this->get("ricerca")->ricerca($datiRicerca);

		return $this->render("CipeBundle:classificazioni:$template.html.twig", 
				array(
						'Classificazione'			=> $risultato["risultato"], 
						"formRicerca"				=> $risultato["form_ricerca"], 
						"filtro_attivo"				=> $risultato["filtro_attivo"],
						"profile"					=> $this->getProfile()
		));
	}
	
	
	/**
	 * 
	 * @param string $tipoClassificazione
	 * @return Response
	 */
	public function elencoClassificazioneCupAction($tipoClassificazione) {
		$datiRicerca = new \CipeBundle\Entity\Ricerche\RicercaClassificazioneCipe();
		$datiRicerca->setTipoClassificazione("$tipoClassificazione");
		$template = "elenco_classificazione_cipe";
		return $this->elencoClassificazioneCipe($datiRicerca, $template);
		
	}
	
	public function elencoClassificazioneCupStatoAction() {
		return $this->elencoClassificazioneCupAction("CupStato");
		
	}
	
	public function elencoClassificazioneStrumentoProgrammazioneAction() {
		return $this->elencoClassificazioneCupAction("CupStrumentoProgrammazione");
	}
	
	public function elencoClassificazioneTipoCoperturaFinanziariaAction() {
		return $this->elencoClassificazioneCupAction("CupTipoCoperturaFinanziaria");
	}
	
	public function elencoClassificazioneTipoIndirizzoAction() {
		return $this->elencoClassificazioneCupAction("CupTipoIndirizzo");
	}
	
	public function elencoClassificazioneCupNaturaAction() {
		return $this->elencoClassificazioneCupAction("CupNatura");
	}
	
	public function elencoClassificazioneCupTipologiaAction() {
		$datiRicerca = new \CipeBundle\Entity\Ricerche\RicercaClassificazioneCipeTipologia();
		$datiRicerca->setTipoClassificazione("CupTipologia");
		$template = "elenco_classificazione_cipe_tipologia";
		return $this->elencoClassificazioneCipe($datiRicerca, $template);
	}
	
	public function elencoClassificazioneCupSettoreAction() {
		$datiRicerca = new \CipeBundle\Entity\Ricerche\RicercaClassificazioneCipeSettore();
		$datiRicerca->setTipoClassificazione("CupSettore");
		$template = "elenco_classificazione_cipe_settore";
		return $this->elencoClassificazioneCipe($datiRicerca, $template);
	}
	
	public function elencoClassificazioneCupSottosettoreAction() {
		$datiRicerca = new \CipeBundle\Entity\Ricerche\RicercaClassificazioneCipeSottosettore();
		$datiRicerca->setTipoClassificazione("CupSottosettore");
		$template = "elenco_classificazione_cipe_sottosettore";
		return $this->elencoClassificazioneCipe($datiRicerca, $template);
	}
	
	public function elencoClassificazioniCupCategoriaAction() {
		$datiRicerca = new \CipeBundle\Entity\Ricerche\RicercaClassificazioneCipeCategoria();
		$datiRicerca->setTipoClassificazione("CupCategoria");
		$template = "elenco_classificazione_cipe_categoria";
		return $this->elencoClassificazioneCipe($datiRicerca, $template);
	}
	
	public function __construct() {
		
		return ;
	}

	
}
