<?php

namespace CipeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use CipeBundle\Entity\DettaglioEleborazione;
use Symfony\Component\Validator\Context\ExecutionContext;
use CipeBundle\Services\CipeEntityService;
use CipeBundle\Entity\DettaglioCup;
use CipeBundle\Entity\RichiestaCupGenerazione;
use CipeBundle\Entity\WsGeneraCup;

/**
 * <!ELEMENT DETTAGLIO_GENERAZIONE_CUP (ID_RICHIESTA, ID_RICHIESTA_ASSEGNATO, DETTAGLIO_ELABORAZIONE, DETTAGLIO_CUP?)>

 * @see http://cb.schema31.it/cb/issue/173219
 * @author Gaetano Borgosano <gborgosano@schema31.it>
 * @ORM\Table(name="risposte_cup_generazione")
 * @ORM\Entity()
 */
class RispostaCupGenerazione extends CipeEntityService
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
     * @var integer
	 * @ORM\Column(type="integer")	 
     */
    protected $IdRichiesta = null;
	function getIdRichiesta() { return $this->IdRichiesta; }
	function setIdRichiesta($IdRichiesta) { $this->IdRichiesta = self::setFilterParam($IdRichiesta, "integer"); }

    /**
     * @var integer
	 * @ORM\Column(type="integer")
     */
    protected $IdRichiestaAssegnato = null;
	function getIdRichiestaAssegnato() { return $this->IdRichiestaAssegnato; }
	function setIdRichiestaAssegnato($IdRichiestaAssegnato) { $this->IdRichiestaAssegnato = self::setFilterParam($IdRichiestaAssegnato, "integer"); }

	/**
	 * @var boolean
     * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $esito_ws = null;
	function getEsito_ws() { return $this->esito_ws; }
	function setEsito_ws($esito_ws) { $this->esito_ws = $esito_ws; }

		
	
    /**
	 * @var DettaglioElaborazione
	 * @ORM\OneToOne(targetEntity="CipeBundle\Entity\DettaglioElaborazione", mappedBy="RispostaCupGenerazione", cascade={"persist"} )
     */
    protected $DettaglioElaborazione = null;
	public function getDettaglioElaborazione() { return $this->DettaglioElaborazione; }
	function setDettaglioElaborazione($DettaglioElaborazione) { $this->DettaglioElaborazione = $DettaglioElaborazione; }

	/**
	 * @var WsGeneraCup
	 * @ORM\OneToOne(targetEntity="CipeBundle\Entity\WsGeneraCup", mappedBy="RispostaCupGenerazione" )
	 */
	protected $WsGeneraCup = null;
	function getWsGeneraCup() { return $this->WsGeneraCup; }
	function setWsGeneraCup(WsGeneraCup $WsGeneraCup) { $this->WsGeneraCup = $WsGeneraCup; }

		
    /**
     * Short description of attribute DettaglioCup
     * @var DettaglioCup
     */
    protected $DettaglioCup = null;
	function getDettaglioCup() { return $this->DettaglioCup; }
	function setDettaglioCup(DettaglioCup $DettaglioCup) { $this->DettaglioCup = $DettaglioCup; }

    // --- OPERATIONS ---
	

	
	public function __construct() {
		parent::__construct(null);
	$this->setXmlName("DETTAGLIO_GENERAZIONE_CUP");
	}
	
    public function validate(ExecutionContext $context) { 
		return parent::validate($context);
	}

		/**
	 * <!ELEMENT DETTAGLIO_GENERAZIONE_CUP (ID_RICHIESTA, ID_RICHIESTA_ASSEGNATO, DETTAGLIO_ELABORAZIONE, DETTAGLIO_CUP?)>
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
												"nodeName"	=> "ID_RICHIESTA",
												"value"		=> $this->getIdRichiesta()
										),
										array(
												"nodeName"	=> "ID_RICHIESTA_ASSEGNATO",
												"value"		=> $this->getIdRichiestaAssegnato()
										),
										array(
												"nodeName"	=> null,
												"value"		=> $this->getDettaglioElaborazione()
										),
										array(
												"nodeName"	=> null,
												"value"		=> $this->getDettaglioCup()
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
			$xml= parent::deserialize($xml);
			$xml->CUP = (string) $xml->DETTAGLIO_CUP->CODICE_CUP;
			$esito_ws = true;
			if ($xml->CUP == "") $esito_ws = false;
			$this->setEsito_ws($esito_ws);
			
			$IdRichiesta = (string) $xml->ID_RICHIESTA;
			$IdRichiestaAssegnato = (string) $xml->ID_RICHIESTA_ASSEGNATO;
			$this->setIdRichiesta($IdRichiesta);
			$this->setIdRichiestaAssegnato($IdRichiestaAssegnato);
			$DettaglioElaborazione = new DettaglioElaborazione();
			$DettaglioElaborazione->setRispostaCupGenerazione($this);
			$DettaglioElaborazione = $DettaglioElaborazione->deserialize($xml->DETTAGLIO_ELABORAZIONE);
			$this->setDettaglioElaborazione($DettaglioElaborazione);
			return $this;
		
		} catch (\Exception $ex) {
			throw $ex;
		}
		
		
	}


} /* end of class RispostaCupGenerazione */

?>