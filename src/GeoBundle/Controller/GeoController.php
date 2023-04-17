<?php

namespace GeoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class GeoController extends Controller {

	/**
	 * @Route("/html/provincia/{provincia_id}/comuni", name="comuni_provincia_options")
     *
	 */
	public function comuniByProvinciaAction($provincia_id) {
        $em = $this->get('doctrine.orm.entity_manager');
        $r = $em->getRepository('GeoBundle\Entity\GeoComune');
        $html = "<option value=''>-</option>\n";
        $comuni = $r->findBy(array("provincia" => $provincia_id, "ceduto_legge_1989" => 0,  "cessato" => 0), array("denominazione"=>"ASC"));
        
        foreach ($comuni as $comune) {
            $html .= "<option value='{$comune->getId()}' " . ($comune->getCapoluogo() ? "selected='selected'" : '') . ">{$comune->getDenominazione()}</option>\n";
        }

        return new Response($html);
    }
    
    /**
	 * @Route("/html/provincia/{provincia_id}/comuni_persona", name="comuni_provincia_options_persona")
     *
	 */
	public function comuniByProvinciaPersonaAction($provincia_id) {
        $em = $this->get('doctrine.orm.entity_manager');
        $r = $em->getRepository('GeoBundle\Entity\GeoComune');
        $html = "<option value=''>-</option>\n";
        $comuni = $r->findBy(array("provincia" => $provincia_id), array("denominazione"=>"ASC"));
        
        foreach ($comuni as $comune) {
            $html .= "<option value='{$comune->getId()}' " . ($comune->getCapoluogo() ? "selected='selected'" : '') . ">{$comune->getDenominazione()}</option>\n";
        }

        return new Response($html);
    }
    
    /**
     * 
     * @param integer $regione_id
     * @Route("/json/provincie_da_regione/", name="geo_json_provincie_regione")
     */
    public function provincieByRegioneJsonAction(){
        $regione_id = $this->container->get('request_stack')->getCurrentRequest()->query->get('q');
        $em = $this->getDoctrine()->getManager();
        $res = array_map( function($geoProvincia){
            return array(
                'id' => $geoProvincia->getId(),
                'denominazione' => $geoProvincia->getDenominazione(),
            );
        }, $em->getRepository('GeoBundle:GeoProvincia')->findByRegione($regione_id));
        return new \Symfony\Component\HttpFoundation\JsonResponse($res);
    }
            
    /**
     * 
     * @param integer $provincia_id
     * @Route("/json/comuni_da_provincia/", name="geo_json_comuni_provincia")
     */
    public function comuniByProvinciaJsonAction(){
        $provincia_id = $this->container->get('request_stack')->getCurrentRequest()->query->get('q');
        $em = $this->getDoctrine()->getManager();
        $res = array_map( function( $comune ){
            return array(
                'id' => $comune->getId(),
                'denominazione' => $comune->getDenominazione(),
            );
        },$em->getRepository('GeoBundle:GeoComune')->findByProvincia($provincia_id));
        return new \Symfony\Component\HttpFoundation\JsonResponse($res);
    }
}
