<?php

namespace CipeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Context\ExecutionContext;
use CipeBundle\Services\CipeEntityService;
use CipeBundle\Entity\RispostaCupGenerazione;


/**
 
 *
 * @see http://cb.schema31.it/cb/issue/173219
 *
 * @author Gaetano Borgosano <gborgosano@schema31.it>
 * @ORM\Table(name="dettagli_elaborazione")
 * @ORM\Entity()
 */
class DettaglioElaborazione extends CipeEntityService
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	function getId() { return $this->id; }
	function setId($id) { $this->id = $id; }
    
    /**
     * @var String
 	 * @ORM\Column(type="string", length=2, nullable=false)
     */
    protected $EsitoElaborazione = null;
	function getEsitoElaborazione() { return $this->EsitoElaborazione; }
	function setEsitoElaborazione($EsitoElaborazione) { $this->EsitoElaborazione = self::setFilterParam($EsitoElaborazione, "string"); }

    /**
     * @var String
	 * @ORM\Column(type="text", nullable=false)
     */
    protected $DescrizioneEsitoElaborazione = null;
	function getDescrizioneEsitoElaborazione() { return $this->DescrizioneEsitoElaborazione; }
	function setDescrizioneEsitoElaborazione($DescrizioneEsitoElaborazione) { $this->DescrizioneEsitoElaborazione = self::setFilterParam($DescrizioneEsitoElaborazione, "string"); }

	/**
	 * @var RispostaCupGenerazione
	 * @ORM\OneToOne(targetEntity="CipeBundle\Entity\RispostaCupGenerazione", inversedBy="DettaglioElaborazione")
     * @ORM\JoinColumn(name="RispostaCupGenerazione_id", referencedColumnName="id", nullable=false, unique=true)
	 */
	protected $RispostaCupGenerazione = null;
	function getRispostaCupGenerazione() { return $this->RispostaCupGenerazione; }
	function setRispostaCupGenerazione(RispostaCupGenerazione $RispostaCupGenerazione) { $this->RispostaCupGenerazione = $RispostaCupGenerazione; }

		
	public function __construct() {
		parent::__construct(null);
		$this->setXmlName("DETTAGLIO_ELABORAZIONE");
	}
	
    public function validate(ExecutionContext $context) { 
		return parent::validate($context);
	}
	
	/**
	* <!ELEMENT DETTAGLIO_ELABORAZIONE (ESITO_ELABORAZIONE, DESCRIZIONE_ESITO_ELABORAZIONE)>
	<!ELEMENT ESITO_ELABORAZIONE (#PCDATA)*>
	<!ELEMENT DESCRIZIONE_ESITO_ELABORAZIONE (#PCDATA)*>
	 * @return String
	 * @throws \Exception
	 */
	public function serialize() {
		try {
				parent::serialize();
				$nodeName = $this->getXmlName();
				$attributes = array();
				$value=null;
				$innerElements = array(
										array(
												"nodeName"	=> "ESITO_ELABORAZIONE",
												"value"		=> $this->getEsitoElaborazione()
										),
										array(
												"nodeName"	=> "DESCRIZIONE_ESITO_ELABORAZIONE",
												"value"		=> $this->getDescrizioneEsitoElaborazione()
										)
				);
				
				$xml = $this->generateXmlNode($nodeName, $attributes, $value, $innerElements);
				return $xml;
				
		} catch (\Exception $ex) {
			throw $ex;
		}
		
	}

	public function deserialize($xml) {
		try {
			$xml = parent::deserialize($xml);
			$EsitoElaborazione = (string) $xml->ESITO_ELABORAZIONE;
			$DescrizioneEsitoElaborazione = (string) $xml->DESCRIZIONE_ESITO_ELABORAZIONE;
			$this->setEsitoElaborazione($EsitoElaborazione);
			$this->setDescrizioneEsitoElaborazione($DescrizioneEsitoElaborazione);
			
			return $this;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	



	// --- OPERATIONS ---

} 

?>