<?php

namespace FascicoloBundle\Form\Type;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;

class InstanzaFrammentoType extends CommonType {
	
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $frammento = $options["attr"]["frammento"];
		$dati = $options["attr"]["dati"];
		$container = $options["attr"]["container"];
		$istanzaFascicolo = $options["attr"]["istanza_fascicolo"];
		$alias_fascicolo = $istanzaFascicolo->getFascicolo()->getIndice()->getAlias();
		$path = $options["attr"]["path"];
		
		/**
		 * @todo: da ottimizzare
		 */
		$dati_indicizzati = array();
		foreach ($dati->getIstanzeCampi() as $campo_dati) {
			$dati_indicizzati[$campo_dati->getCampo()->getAlias()][] = $campo_dati;
		}		
		
		foreach ($frammento->getCampi() as $campo) {
			$callbackPresenzaCampo = $campo->getCallbackPresenza();
			
			if (!is_null($callbackPresenzaCampo)) {
				if (!$container->has("fascicolo.istanza.".$alias_fascicolo)) {
					/**
					 * @todo: loggare errore
					 */
					return;
				}

				$servizioIstanzaFascicolo = $container->get("fascicolo.istanza.".$alias_fascicolo);
				if (method_exists($servizioIstanzaFascicolo, $callbackPresenzaCampo)) {
					$presente = $servizioIstanzaFascicolo->$callbackPresenzaCampo($istanzaFascicolo, $path.".".$campo->getAlias());
					if(!$presente){
						 continue;
					}
				}			
			}				
			
			if (isset($dati_indicizzati[$campo->getAlias()])) {
				$dato =  $dati_indicizzati[$campo->getAlias()];
			} else {
				$dato = array(new \FascicoloBundle\Entity\IstanzaCampo());
			}
			
			$options_campo = array("attr" => array());
			$options_campo["attr"]["container"] = $container;
			$options_campo["attr"]["campo"] = $campo;
			$options_campo["attr"]["dato"] = $dato;
			
			$builder->add($campo->getAlias(), "FascicoloBundle\Form\Type\IstanzaCampoType", $options_campo);
		}
    }
}