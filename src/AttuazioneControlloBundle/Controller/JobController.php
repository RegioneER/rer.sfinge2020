<?php

namespace AttuazioneControlloBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;

/**
 * @Route("/job")
 */
class JobController extends \BaseBundle\Controller\BaseController {

	/**
	 * @Route("/creazione_giustificativi_da_dicui", name="creazione_giustificativi_da_dicui")
	 */
	public function creazioneGiustificativiDaDiCuiAction() {
		return $this->get("creazione_giustificativi_da_dicui")->bonifica();
	}
	
	/**
	 * @Route("/bonifica_giustificativi_bando_8", name="bonifica_giustificativi_bando_8")
	 */
	public function bonificaGiustificativiBando8Action() {
		return $this->get("bonifica_giustificativi_bando_8")->bonifica();
	}
	
	/**
	 * @Route("/bonifica_giustificativi_bando_32", name="bonifica_giustificativi_bando_32")
	 */
	public function bonificaGiustificativiBando32Action() {
		return $this->get("bonifica_giustificativi_bando_32")->bonifica();
	}

		/**
	 * @Route("/bonifica_contratti_bando_8", name="bonifica_contratti_bando_8")
	 */
	public function bonificaContrattiBando8() {
		return $this->get("bonifica_contratti_bando_774_32")->bonifica(8);
	}
	
	/**
	 * @Route("/bonifica_contratti_bando_32", name="bonifica_contratti_bando_32")
	 */
	public function bonificaContrattiBando32() {
		return $this->get("bonifica_contratti_bando_774_32")->bonifica(32);
	}

	/**
	 * @Route("/bonifica_importo_ammesso_7_8_32", name="bonifica_importo_ammesso_7_8_32")
	 */
	public function bonificaImportoAmmesso_7_8_32() {
		return $this->get("bonifica_importo_ammesso_7_8_32")->bonifica();
	}
	
}
