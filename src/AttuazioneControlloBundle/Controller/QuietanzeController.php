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
 * @Route("/beneficiario/quietanze")
 */
class QuietanzeController extends \BaseBundle\Controller\BaseController
{
	
	/**
	 * @Route("/{id_giustificativo}/aggiungi", name="aggiungi_quietanza")
	 * @PaginaInfo(titolo="Creazione quietanza",sottoTitolo="pagina di creazione di una quietanza")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 * @ControlloAccesso(contesto="giustificativo", classe="AttuazioneControlloBundle:GiustificativoPagamento", opzioni={"id" = "id_giustificativo"}, azione=\AttuazioneControlloBundle\Security\GiustificativoVoter::WRITE)  
	 */
	public function aggiungiQuietanzaAction($id_giustificativo) 
	{
        return $this->get("gestore_quietanze")->getGestore()->aggiungiQuietanza($id_giustificativo);
	}
	
	/**
	 * @Route("/{id_quietanza}/modifica", name="modifica_quietanza")
	 * @PaginaInfo(titolo="Modifica quietanza",sottoTitolo="pagina di modifica di una quietanza")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @ControlloAccesso(contesto="quietanza", classe="AttuazioneControlloBundle:QuietanzaGiustificativo", opzioni={"id" = "id_quietanza"}, azione=\AttuazioneControlloBundle\Security\QuietanzaVoter::WRITE)
	 */
	public function modificaQuietanzaAction($id_quietanza) 
	{
        return $this->get("gestore_quietanze")->getGestore()->modificaQuietanza($id_quietanza);
	}
	
	/**
	 * @Route("/{id_quietanza}/{id_documento_quietanza}/elimina_documento_quietanza", name="elimina_documento_quietanza")
     * @ControlloAccesso(contesto="quietanza", classe="AttuazioneControlloBundle:QuietanzaGiustificativo", opzioni={"id" = "id_quietanza"}, azione=\AttuazioneControlloBundle\Security\QuietanzaVoter::WRITE)
	 */
	public function eliminaDocumentoQuietanzaAction($id_quietanza, $id_documento_quietanza) 
	{
        return $this->get("gestore_quietanze")->getGestore()->eliminaDocumentoQuietanza($id_documento_quietanza, $id_quietanza);
	}
    
	/**
	 * @Route("/{id_quietanza}/elimina", name="elimina_quietanza")
     * @ControlloAccesso(contesto="quietanza", classe="AttuazioneControlloBundle:QuietanzaGiustificativo", opzioni={"id" = "id_quietanza"}, azione=\AttuazioneControlloBundle\Security\QuietanzaVoter::WRITE)
	 */
	public function eliminaQuietanzaAction($id_quietanza) 
	{
        return $this->get("gestore_quietanze")->getGestore()->eliminaQuietanza($id_quietanza);
	}   
       
}
