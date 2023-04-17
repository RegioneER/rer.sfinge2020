<?php

namespace AttuazioneControlloBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use BaseBundle\Annotation\ControlloAccesso;

/**
 * @Route("/beneficiario/voci_piano_costo")
 */
class VociPianoCostoController extends \BaseBundle\Controller\BaseController
{
	
	/**
	 * @Route("/{id_giustificativo}/aggiungi", name="aggiungi_voce_costo_giustificativo")
	 * @PaginaInfo(titolo="Associa voce piano costo",sottoTitolo="pagina di associazione di una voce piano costo al giustificativo")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="giustificativo", classe="AttuazioneControlloBundle:GiustificativoPagamento", opzioni={"id" = "id_giustificativo"}, azione=\AttuazioneControlloBundle\Security\GiustificativoVoter::WRITE)  
	 */
	public function aggiungiVocePianoCostoAction($id_giustificativo) 
	{
        return $this->get("gestore_voci_piano_costo_giustificativo")->getGestore()->aggiungiVocePianoCosto($id_giustificativo);
	}
	
	/**
	 * @Route("/{id_voce_piano}/modifica_voce", name="modifica_voce")
	 * @PaginaInfo(titolo="Modifica voce",sottoTitolo="pagina di modifica di una voce")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @ControlloAccesso(contesto="vocepianocostogiustificativo", classe="AttuazioneControlloBundle:VocePianoCostoGiustificativo", opzioni={"id" = "id_voce_piano"}, azione=\AttuazioneControlloBundle\Security\VocePianoCostoGiustificativoVoter::WRITE)
	 */
	public function modificaVocePianoCostoAction($id_voce_piano) 
	{
        return $this->get("gestore_voci_piano_costo_giustificativo")->getGestore()->modificaVocePianoCosto($id_voce_piano);
	}
    
	/**
	 * @Route("/{id_voce_costo_giustificativo}/elimina", name="elimina_voce_costo_giustificativo")
     * @ControlloAccesso(contesto="vocepianocostogiustificativo", classe="AttuazioneControlloBundle:VocePianoCostoGiustificativo", opzioni={"id" = "id_voce_costo_giustificativo"}, azione=\AttuazioneControlloBundle\Security\VocePianoCostoGiustificativoVoter::WRITE)
	 */
	public function eliminaVocePianoCostoAction($id_voce_costo_giustificativo) 
	{
        return $this->get("gestore_voci_piano_costo_giustificativo")->getGestore()->eliminaVocePianoCosto($id_voce_costo_giustificativo);
	}   
       
}
