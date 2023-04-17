<?php

namespace CipeBundle\Entity;

use Symfony\Component\Validator\Context\ExecutionContext;
use CipeBundle\Services\CipeEntityService;
use CipeBundle\Entity\ConcessioneContributiNoUnitaProduttive;
use CipeBundle\Entity\ConcessioneIncentiviUnitaProduttive;
use CipeBundle\Entity\CupGenerazione;
use CipeBundle\Entity\AcquistoBeni;
use CipeBundle\Entity\LavoriPubblici;
use CipeBundle\Entity\RealizzAcquistoServiziRicerca;
use CipeBundle\Entity\RealizzAcquistoServiziNoFormazioneRicerca;
use CipeBundle\Entity\RealizzAcquistoServiziFormazione;
use CipeBundle\Entity\PartecipAzionarieConferimCapitale;

/**
 * Descrizione
 * @see http://cb.schema31.it/cb/issue/173201
 * <!ELEMENT DESCRIZIONE (LAVORI_PUBBLICI | CONCESSIONE_INCENTIVI_UNITA_PRODUTTIVE | REALIZZ_ACQUISTO_SERVIZI_RICERCA | REALIZZ_ACQUISTO_SERVIZI_FORMAZIONE | REALIZZ_ACQUISTO_SERVIZI_NO_FORMAZIONE_RICERCA | ACQUISTO_BENI | PARTECIP_AZIONARIE_CONFERIM_CAPITALE | CONCESSIONE_CONTRIBUTI_NO_UNITA_PRODUTTIVE | CUP_CUMULATIVO)>
 * 
 * @author Gaetano Borgosano <gborgosano@schema31.it>
 */
class Descrizione extends CipeEntityService
{
    /**
     * ConcessioneIncentiviNoUnitaProduttive
     * @see http://cb.schema31.it/cb/issue/173202
     * @var ConcessioneContributiNoUnitaProduttive
     */
    protected $ConcessioneContributiNoUnitaProduttive = null;
	function getConcessioneContributiNoUnitaProduttive() { return $this->ConcessioneContributiNoUnitaProduttive; }
	function setConcessioneContributiNoUnitaProduttive(ConcessioneContributiNoUnitaProduttive $ConcessioneContributiNoUnitaProduttive=null) { $this->ConcessioneContributiNoUnitaProduttive = $ConcessioneContributiNoUnitaProduttive; }

		
    /**
     * ConcessioneIncentiviUnitaProduttive
	 * @see http://cb.schema31.it/cb/issue/173243
     * @var ConcessioneIncentiviUnitaProduttive
     */
    protected $ConcessioneIncentiviUnitaProduttive = null;
	function getConcessioneIncentiviUnitaProduttive() { return $this->ConcessioneIncentiviUnitaProduttive; }
	function setConcessioneIncentiviUnitaProduttive(ConcessioneIncentiviUnitaProduttive $ConcessioneIncentiviUnitaProduttive=null) { $this->ConcessioneIncentiviUnitaProduttive = $ConcessioneIncentiviUnitaProduttive; }

	/**
	 * @var AcquistoBeni
	 */
	protected $AcquistoBeni;
	function getAcquistoBeni() { return $this->AcquistoBeni; }
	function setAcquistoBeni(AcquistoBeni $AcquistoBeni=null) { $this->AcquistoBeni = $AcquistoBeni; }

	/**
	 * @var LavoriPubblici
	 */
	protected $LavoriPubblici;
	function getLavoriPubblici() { return $this->LavoriPubblici; }
	function setLavoriPubblici(LavoriPubblici $LavoriPubblici=null) { $this->LavoriPubblici = $LavoriPubblici; }

	/**
	 *
	 * @var RealizzAcquistoServiziRicerca
	 */
	protected $RealizzAcquistoServiziRicerca;
	function getRealizzAcquistoServiziRicerca() { return $this->RealizzAcquistoServiziRicerca; }
	function setRealizzAcquistoServiziRicerca(RealizzAcquistoServiziRicerca $RealizzAcquistoServiziRicerca=null) { $this->RealizzAcquistoServiziRicerca = $RealizzAcquistoServiziRicerca; }

	/**
	 *
	 * @var RealizzAcquistoServiziNoFormazioneRicerca
	 */		
	protected $RealizzAcquistoServiziNoFormazioneRicerca;
	function getRealizzAcquistoServiziNoFormazioneRicerca() { return $this->RealizzAcquistoServiziNoFormazioneRicerca; }
	function setRealizzAcquistoServiziNoFormazioneRicerca(RealizzAcquistoServiziNoFormazioneRicerca $RealizzAcquistoServiziNoFormazioneRicerca=null) { $this->RealizzAcquistoServiziNoFormazioneRicerca = $RealizzAcquistoServiziNoFormazioneRicerca; }
	
	
	/**
	 *
	 * @var RealizzAcquistoServiziFormazione
	 */
	protected $RealizzAcquistoServiziFormazione;
	function getRealizzAcquistoServiziFormazione() { return $this->RealizzAcquistoServiziFormazione; }
	function setRealizzAcquistoServiziFormazione(RealizzAcquistoServiziFormazione $RealizzAcquistoServiziFormazione) { $this->RealizzAcquistoServiziFormazione = $RealizzAcquistoServiziFormazione; }

		
	/**
	 * @var PartecipAzionarieConferimCapitale
	 */
	protected $PartecipAzionarieConferimCapitale;
	function getPartecipAzionarieConferimCapitale() { return $this->PartecipAzionarieConferimCapitale; }
	function setPartecipAzionarieConferimCapitale(PartecipAzionarieConferimCapitale $PartecipAzionarieConferimCapitale) { $this->PartecipAzionarieConferimCapitale = $PartecipAzionarieConferimCapitale; }

			
    // --- OPERATIONS ---
	
	public function __construct() {
		parent::__construct(null);
		$this->setXmlName("DESCRIZIONE");
	}
	
    public function validate(ExecutionContext $context) { 
		$type = "inner";
		$this->setValidateStatus("CONCESSIONE_CONTRIBUTI_NO_UNITA_PRODUTTIVE"		, $this->validateIfNotNull($this->getConcessioneContributiNoUnitaProduttive())		, $type);
		$this->setValidateStatus("CONCESSIONE_INCENTIVI_UNITA_PRODUTTIVE"			, $this->validateIfNotNull($this->getConcessioneIncentiviUnitaProduttive())			, $type);
		$this->setValidateStatus("ACQUISTO_BENI"									, $this->validateIfNotNull($this->getAcquistoBeni())								, $type);
		$this->setValidateStatus("LAVORI PUBBLICI"									, $this->validateIfNotNull($this->getLavoriPubblici())								, $type);
		$this->setValidateStatus("REALIZZ_ACQUISTO_SERVIZI_RICERCA"					, $this->validateIfNotNull($this->getRealizzAcquistoServiziRicerca())				, $type);
		$this->setValidateStatus("REALIZZ_ACQUISTO_SERVIZI_NO_FORMAZIONE_RICERCA"	, $this->validateIfNotNull($this->getRealizzAcquistoServiziNoFormazioneRicerca())	, $type);
		$this->setValidateStatus("REALIZZ_ACQUISTO_SERVIZI_FORMAZIONE"				, $this->validateIfNotNull($this->getRealizzAcquistoServiziFormazione())			, $type);
		$this->setValidateStatus("PARTECIP_AZIONARIE_CONFERIM_CAPITALE"				, $this->validateIfNotNull($this->getPartecipAzionarieConferimCapitale())			, $type);

		$st = parent::validate($context);
		$countElems = 0;
		if(!\is_null($this->getConcessioneContributiNoUnitaProduttive())) $countElems++;
		if(!\is_null($this->getConcessioneIncentiviUnitaProduttive())) $countElems++;
		if(!\is_null($this->getAcquistoBeni())) $countElems++;
		if(!\is_null($this->getLavoriPubblici())) $countElems++;
		if(!\is_null($this->getRealizzAcquistoServiziRicerca())) $countElems++;
		if(!\is_null($this->getRealizzAcquistoServiziNoFormazioneRicerca())) $countElems++;
		if(!\is_null($this->getRealizzAcquistoServiziFormazione())) $countElems++;
		if(!\is_null($this->getPartecipAzionarieConferimCapitale())) $countElems++;

		return $st && ( ($countElems == 1)  ? true : false);
	}
	
	public function serialize() {
		try {
			parent::serialize();
			$nodeName = $this->getXmlName();
			$value=null;
			$attributes = array();
			$innerElements = array(
										array(
												"nodeName"	=> null,
												"value"		=> $this->serializeIfNotNull($this->getConcessioneContributiNoUnitaProduttive())
										),
										array(
												"nodeName"	=> null,
												"value"		=> $this->serializeIfNotNull($this->getConcessioneIncentiviUnitaProduttive())
										),array(
												"nodeName"	=> null,
												"value"		=> $this->serializeIfNotNull($this->getAcquistoBeni())
										),array(
												"nodeName"	=> null,
												"value"		=> $this->serializeIfNotNull($this->getLavoriPubblici())
										),array(
												"nodeName"	=> null,
												"value"		=> $this->serializeIfNotNull($this->getRealizzAcquistoServiziRicerca())
										),array(
												"nodeName"	=> null,
												"value"		=> $this->serializeIfNotNull($this->getRealizzAcquistoServiziNoFormazioneRicerca())
										),array(
												"nodeName"	=> null,
												"value"		=> $this->serializeIfNotNull($this->getRealizzAcquistoServiziFormazione())
										),array(
												"nodeName"	=> null,
												"value"		=> $this->serializeIfNotNull($this->getPartecipAzionarieConferimCapitale())
										),
							);
			$xml = $this->generateXmlNode($nodeName, $attributes, $value, $innerElements);
			return $xml;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

}