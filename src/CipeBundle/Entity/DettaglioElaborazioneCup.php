<?php

namespace CipeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Context\ExecutionContext;
use CipeBundle\Services\CipeEntityService;
use CipeBundle\Entity\DettaglioCupGenerazione;

/**
 
 *
 */
class DettaglioElaborazioneCup extends CipeEntityService
{
	
	
	protected $DettagliCupGenerazione;
	function getDettagliCupGenerazione() { return $this->DettagliCupGenerazione; }
	function setDettagliCupGenerazione($DettagliCupGenerazione) { $this->DettagliCupGenerazione = $DettagliCupGenerazione; }
	function addDettaglioCupGenerazione(DettaglioCupGenerazione $DettaglioCupGenerazione) {
		$DettagliCupGenerazione = $this->getDettagliCupGenerazione();
		$DettagliCupGenerazione[] = $DettaglioCupGenerazione;
		$this->setDettagliCupGenerazione($DettagliCupGenerazione);
	}
			
	public function __construct() {
		parent::__construct(null);
		$this->setXmlName("DETTAGLIO_ELABORAZIONE_CUP");
	}
	
	
    public function validate(ExecutionContext $context) { 
			
		return parent::validate($context);
	}
	
	/**
	 * 
	 * @return string
	 * @throws \Exception
	 */
	public function serialize() {
		try {
			parent::serialize();
			$nodeName = $this->getXmlName();
			$attributes = array(
							
								);
			
						
			$innerElements 
						= array(
//										array(
//												"nodeName"	=> 'MESSAGGI_DI_SCARTO',
//												"value"		=> $this->getMessaggi_di_scarto()
//										),

							
								);
			
			$xml = $this->generateXmlNode($nodeName, $attributes, null, $innerElements);
			return $xml;
			
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
	
	// TODO VERIFICA
	
	public function deserialize($xml) {
		try {
			$xml = parent::deserialize($xml);
			
			$xml_nodi_dettaglio_cup_generazione = $xml->DETTAGLIO_CUP_GENERAZIONE;
			foreach ($xml_nodi_dettaglio_cup_generazione as $xml_dettaglio_cup_generazione) {
				$dettaglio_cup_generazione = (string) $xml_dettaglio_cup_generazione;
				if(!\is_null($dettaglio_cup_generazione)) {
					$DettaglioCupGenerazione = new DettaglioCupGenerazione();
					$DettaglioCupGenerazione->deserialize($dettaglio_cup_generazione);
					$this->addDettaglioCupGenerazione ($DettaglioCupGenerazione);
				}
			}
			
			
			return $this;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	



	// --- OPERATIONS ---

} 

