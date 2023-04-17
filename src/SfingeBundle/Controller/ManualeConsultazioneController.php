<?php

namespace SfingeBundle\Controller;

use BaseBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;

use SfingeBundle\Form\ManualeType;
use SfingeBundle\Entity\Manuale;

use DocumentoBundle\Entity\DocumentoFile;

use SfingeBundle\Form\Entity\RicercaManuale;
use PaginaBundle\Annotations\Menuitem;

class ManualeConsultazioneController extends BaseController {
	
	/**
	 * @Route("/elenco", name="elenco_manuali")
	 * @Menuitem(menuAttivo = "elencoManuali")
	 * @PaginaInfo(titolo="Elenco manuali", sottoTitolo="")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco manuali")})
	 */
    public function elencoManualiAction(){
    	$is_utente = 0;
		if($this->isUtente()){
	    	$is_utente = 1;
		}
		$manuali = $this->getEm()->getRepository("SfingeBundle\Entity\Manuale")->cercaManuale($is_utente);
                
                foreach ($manuali as $manuale_key => $manuale ) {
                    $documentoFile = $manuale->getDocumentoFile();
                    $path = $this->container->get("funzioni_utili")->encid($documentoFile->getPath().$documentoFile->getNome());
                    $manuali[$manuale_key]->path = $path;
                }
                
		return $this->render('SfingeBundle:Manuale:elencoManuali.html.twig', array('manuali' => $manuali));
    }


}
