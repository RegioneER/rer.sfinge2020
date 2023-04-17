<?php

namespace AttuazioneControlloBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use BaseBundle\Annotation\ControlloAccesso;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/ingegneria_finanziaria/giustificativi")
 */
class GiustificativiIngegneriaFinanziariaController extends \BaseBundle\Controller\BaseController
{

	/**
	 * @Route("/{id_pagamento}/elenco", name="elenco_giustificativi_ing_fin")
	 * @PaginaInfo(titolo="Elenco giustificativi",sottoTitolo="Elenco dei giustificativi definiti per un pagamento")
	 * @Menuitem(menuAttivo = "elencoRichiesteIngFin")
	 */
	public function elencoGiustificativiAction($id_pagamento) 
	{
        return $this->get("gestore_giustificativi")->getGestore()->elencoGiustificativi($id_pagamento);
	}
    
	/**
	 * @Route("/{id_pagamento}/aggiungi", name="aggiungi_giustificativo_ing_fin")
	 * @PaginaInfo(titolo="Creazione giustificativo",sottoTitolo="pagina di creazione di un giustificativo")
	 * @Menuitem(menuAttivo = "elencoRichiesteIngFin")
	 */
	public function aggiungiGiustificativoAction($id_pagamento) 
	{
        return $this->get("gestore_giustificativi")->getGestore()->aggiungiGiustificativo($id_pagamento);
	}
    
	/**
	 * @Route("/{id_giustificativo}/elimina", name="elimina_giustificativo_ing_fin")
	 */
	public function eliminaGiustificativoAction($id_giustificativo) 
	{
        return $this->get("gestore_giustificativi")->getGestore()->eliminaGiustificativo($id_giustificativo);
	}
	
	/**
	 * @Route("/{id_giustificativo}/{id_documento_giustificativo}/elimina_documento_giustificativo", name="elimina_documento_giustificativo_ing_fin")
	 */
	public function eliminaDocumentoGiustificativoAction($id_giustificativo, $id_documento_giustificativo) 
	{
        return $this->get("gestore_giustificativi")->getGestore()->eliminaDocumentoGiustificativo($id_documento_giustificativo, $id_giustificativo);
	}

	/**
	 * @Route("/{id_giustificativo}/dettaglio", name="dettaglio_giustificativo_ing_fin")
	 * @PaginaInfo(titolo="Dettaglio giustificativo",sottoTitolo="pagina di riepilogo del giustificativo")
	 * @Menuitem(menuAttivo = "elencoRichiesteIngFin")
	 */
	public function dettaglioGiustificativoAction($id_giustificativo) 
	{
        return $this->get("gestore_giustificativi")->getGestore()->dettaglioGiustificativo($id_giustificativo);
	}  
	
	/**
	 * @Route("/{id_giustificativo}/modifica", name="modifica_giustificativo_ing_fin")
	 * @PaginaInfo(titolo="Modifica giustificativo",sottoTitolo="pagina di modifica del giustificativo")
	 * @Menuitem(menuAttivo = "elencoRichiesteIngFin")
	 */
	public function modificaGiustificativoAction($id_giustificativo) 
	{
        return $this->get("gestore_giustificativi")->getGestore()->modificaGiustificativo($id_giustificativo);
	}    
	
	/**
	 *
	 * @Route("/{id_pagamento}/completa_pagamento", name="completa_pagamento_ing_fin")
	 * @Method({"GET"})
	 * @Menuitem(menuAttivo = "elencoRichiesteIngFin")
	 */
	public function completaPagamentoAction($id_pagamento) {
        $this->get('base')->checkCsrf('token');
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
		return $this->get("gestore_pagamenti")->getGestore($pagamento->getRichiesta()->getProcedura())->completaPagamento($id_pagamento);
	} 
       
}
