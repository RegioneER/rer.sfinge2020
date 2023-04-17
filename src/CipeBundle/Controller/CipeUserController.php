<?php

namespace CipeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use CipeBundle\Controller\CipeBaseController;



class CipeUserController extends CipeBaseController
{

	public function __construct() {
		parent::__construct();
		$this->setProfile("user");
	}
	
	/**
	 * 
	 * @Route("/inoltra_richiesta/{id_progetto}", name="cipe_user_inoltra_richiesta")
	 * @param integer $id_progetto
	 */
	public function inoltraRichiestaAction($id_progetto) {
		return parent::inoltraRichiestaAction($id_progetto);
	}

	/**
	 * @Route("/ws_genera_cup/visualizza/{WsGeneraCup_id}", name="cipe_user_visualizza_ws_genera_cup")
	 * @param integer $WsGeneraCup_id
	 */
	public function visualizzaWsGeneraCupAction($WsGeneraCup_id) {
		return parent::visualizzaWsGeneraCup($WsGeneraCup_id);
	}

	
}
