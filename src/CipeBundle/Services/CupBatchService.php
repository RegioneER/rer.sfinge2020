<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace CipeBundle\Services;

use CipeBundle\Entity\CupGenerazione;
use CipeBundle\Entity\DettaglioElaborazioneCup;
use BaseBundle\Service\AdapterMemoryService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
use DocumentoBundle\Entity\DocumentoFile;

/**
 * Description of CupBatchService
 *
 * @author gaetanoborgosano
 */
class CupBatchService {
	
	const CODICE_TIPOLOGIA_DOCUMENTO_DEFAULT="cipe_batch";


	/**
	 * @var ContainerInterface
	 * @see http://cb.schema31.it/cb/issue/177624
	 */
	protected $container;
	protected function getContainer() { return $this->container; }
	protected function setContainer($container) { $this->container = $container; }
	protected function getParameter($name) { return $this->getContainer()->getParameter($name); }
	
	/**
	 * @var Registry
	 */
	protected $doctrine;
	protected function getDoctrine() { return $this->doctrine; }
	protected function setDoctrine($doctrine) { $this->doctrine = $doctrine; }
	protected function getEm() { return $this->getDoctrine()->getManager(); }
	
	/**
	 * @var \DocumentoBundle\Service\DocumentiService
	 */
	protected $DocumentService;
	function getDocumentService() { return $this->DocumentService; }
	function setDocumentService($DocumentService) { $this->DocumentService = $DocumentService; }

	/**
	 *
	 * @var AdapterMemoryService
	 */
	protected $AdapterMemoryService;
	function getAdapterMemoryService() { return $this->AdapterMemoryService; }
	function setAdapterMemoryService(AdapterMemoryService $AdapterMemoryService) { $this->AdapterMemoryService = $AdapterMemoryService; }
		
	
	protected $validator;
	function getValidator() { return $this->validator; }
	function setValidator($validator) { $this->validator = $validator; }
	
	
	protected $codice_tipologia_documento;
	function getCodice_tipologia_documento() { return $this->codice_tipologia_documento; }
	function setCodice_tipologia_documento($codice_tipologia_documento) { $this->codice_tipologia_documento = $codice_tipologia_documento; }

	
	protected $fileBasenameCupBatch;
	function getFileBasenameCupBatch() { return $this->fileBasenameCupBatch; }
	function setFileBasenameCupBatch($fileBasenameCupBatch) { $this->fileBasenameCupBatch = $fileBasenameCupBatch; }
	
	protected $fileTemporaneo;
	protected function getFileTemporaneo() { return $this->fileTemporaneo; }
	protected function setFileTemporaneo($fileTemporaneo) { $this->fileTemporaneo = $fileTemporaneo; }

	
	protected $validazioneMassiva=false;
	function getValidazioneMassiva() { return $this->validazioneMassiva; }
	function setValidazioneMassiva($validazioneMassiva) { $this->validazioneMassiva = $validazioneMassiva; }

		
	public function __construct($container, $doctrine, $DocumentService, $AdapterMemoryService, $codice_tipologia_documento_batch=null) {
		$this->setContainer($container);
		$this->setDoctrine($doctrine);
		$this->setDocumentService($DocumentService);
		$this->setAdapterMemoryService($AdapterMemoryService);
		$validator = $this->getContainer()->get("validator");
		$this->setValidator($validator);
		$codice_tipologia_documento = (!\is_null($codice_tipologia_documento_batch)) ? $codice_tipologia_documento_batch : self::CODICE_TIPOLOGIA_DOCUMENTO_DEFAULT;
		$this->setCodice_tipologia_documento($codice_tipologia_documento);
	}
	
	
	protected function openXmlTag() { return '<?xml version="1.0" encoding="UTF-8"?>'; }
	protected function openCupTag() { return "<CUP>"; }
	protected function closeCupTag() { return "</CUP>"; }
	
	/**
	 * apri tracciato cupBatch con intestazioni iniziali
	 * @param string $fileBasenameCupBatch
	 */
	public function apriCupBatch($fileBasenameCupBatch=null) {
		$this->getAdapterMemoryService()->start();
		if(!\is_null($fileBasenameCupBatch)) $this->setFileBasenameCupBatch ($fileBasenameCupBatch);
		$fileTemporaneo = $this->getDocumentService()->apriFileTemporaneo();
		$this->setFileTemporaneo($fileTemporaneo);
		$this->getDocumentService()->scriviInFileTemporaneo($fileTemporaneo, $this->openXmlTag());
		$this->getDocumentService()->scriviInFileTemporaneo($fileTemporaneo, $this->openCupTag());
	}
	
	
	/**
	 * chiudi tracciato CupBatch
	 * @throws \Exception
	 */
	public function chiudiCupBatch() {
		try {
			$fileTemporaneo = $this->getFileTemporaneo();
			$this->getDocumentService()->scriviInFileTemporaneo($fileTemporaneo, $this->closeCupTag());
			$this->getDocumentService()->chiudiFileTemporaneo($fileTemporaneo);
			$this->getAdapterMemoryService()->end();
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	
	public function startValidazioneMassiva() {
		$this->setValidazioneMassiva(true);
		$this->getAdapterMemoryService()->start();
	}
	
	public function validaCupGenerazione(CupGenerazione $CupGenerazione) {
		try {
			if($this->getValidazioneMassiva()) $this->getAdapterMemoryService()->adaptMemory();
			$errors = $this->getValidator()->validate($CupGenerazione);
				$erroriValidazione = array();
			if (count($errors) > 0) {
						foreach ($errors as $error) {
							$erroriValidazione[]="[".$error->getPropertyPath()."] errore: {$error->getMessage()}";
						}
						return $erroriValidazione;
				}
				
				return array();
			
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
	
	public function endValidazioneMassiva() {
		$this->getAdapterMemoryService()->end();
		$this->setValidazioneMassiva(false);
	}
	
	
	/**
	 * aggiunge xml CupGenerazione al tracciato CupBatch
	 * @param CupGenerazione $CupGenerazione
	 * @param boolean $valida
	 * @return bool CupGenerazione
	 * @throws \Exception
	 */
	public function aggiungiCupGenerazione(CupGenerazione $CupGenerazione, $valida=false) {
		try {
			$this->getAdapterMemoryService()->adaptMemory();
			if($valida) {
				$errors = $this->getValidator()->validate($CupGenerazione);
//				$erroriValidazione = array();
				if (count($errors) > 0) return false;
//					{
//						foreach ($errors as $error) {
//							$erroriValidazione[]="[".$error->getPropertyPath()."] errore: {$error->getMessage()}";
//						}
//						return $erroriValidazione;
//				}
			}
			$xml_cup_generazione_richiesta = $CupGenerazione->serialize();
			unset($CupGenerazione);
			$this->getDocumentService()->scriviInFileTemporaneo($this->getFileTemporaneo(), $xml_cup_generazione_richiesta);
			unset($xml_cup_generazione_richiesta);
			return true;
			
			
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
	
	/**
	 * Salva cupBatch
	 * @return DocumentoFile
	 * @throws \Exception
	 */
	public function salvaCupBatch() {
		try {
			$fileTemporaneo = $this->getFileTemporaneo();
			$filePathname = $fileTemporaneo['filename'];
			$DocumentoFile = $this->getDocumentService()->caricaDaFile($filePathname, $this->getCodice_tipologia_documento(), true, $this->getFileBasenameCupBatch(), true, false);
			return $DocumentoFile;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
	
	
	/**
	 * 
	 * @param String $xml_DettaglioElaborazioneCup
	 * @return DettaglioElaborazioneCup
	 * @throws \Exception
	 */
	public function elaboraDettaglioElaborazioneCupFromXml($xml_DettaglioElaborazioneCup) {
		try {
			$DettaglioElaborazioneCup = new DettaglioElaborazioneCup();
			$DettaglioElaborazioneCup->deserialize($xml_DettaglioElaborazioneCup);
			return $DettaglioElaborazioneCup;
			
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
	
	
	/**
	 * 
	 * @param String $xml_filename
	 * @return DettaglioElaborazioneCup
	 * @throws \Exception
	 */
	public function elaboraDettaglioElaborazioneCupFromXmlFile($xml_filename) {
		try {
			$DettaglioElaborazioneCup = new DettaglioElaborazioneCup();
			$DettaglioElaborazioneCup->setXml_load_file(true);
			$DettaglioElaborazioneCup->deserialize($xml_filename);
			return $DettaglioElaborazioneCup;
			
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
	
	
	/**
	 * 
	 * @param \SimpleXMLElement $xml
	 * @return array
	 * @throws \Exception
	 */
	public function elaboraDettaglioElaborazioneCupArray(\SimpleXMLElement $xml) {
		try {
			 $risposta = array();
			 $xml_nodi_dettaglio_cup_generazione = $xml->DETTAGLIO_CUP_GENERAZIONE;
			 foreach ($xml_nodi_dettaglio_cup_generazione as $xml_dettaglio_cup_generazione) {
			  
				$id_progetto =  (string) $xml_dettaglio_cup_generazione['id_progetto'];
				$codifica_locale = (string) $xml_dettaglio_cup_generazione['codifica_locale'];
				$xml_dati_cup =  $xml_dettaglio_cup_generazione->DATI_CUP;
				
				$codice_cup = null;
				if(!\is_null($xml_dati_cup)) {
					$codice_cup = (string) $xml_dati_cup->CODICE_CUP;
				}
				$xml_messaggi_scarto = $xml_dettaglio_cup_generazione->MESSAGGI_DI_SCARTO;
				$messaggi_scarto = array();
				foreach ($xml_messaggi_scarto as $xml_messaggio_scarto) {
					$messaggi_scarto[] = trim((string) $xml_messaggio_scarto);
				}
				$risposta[] = array(
									"id_progetto"		=> trim($id_progetto),
									"codifica_locale"	=> trim($codifica_locale),
									"codice_cup"		=> trim($codice_cup),
									"messaggi_scarto"	=> $messaggi_scarto
									);
			}
			
			return $risposta;
			
			
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
	
	
	/**
	 * 
	 * @param string $xml_DettaglioElaborazioneCup
	 * @return array
	 * @throws \Exception
	 */
	public function elaboraDettaglioElaborazioneCupArrayFromXml($xml_DettaglioElaborazioneCup) {
		try {
			 $xml = simplexml_load_string(utf8_encode($xml_DettaglioElaborazioneCup));
			 return $this->elaboraDettaglioElaborazioneCupArray($xml);
			
			
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
	
	
	/**
	 * 
	 * @param string $xml_filename
	 * @return array
	 * @throws \Exception
	 */
	public function elaboraDettaglioElaborazioneCupArrayFromXmlFile($xml_filename) {
		try {
			 $xml = simplexml_load_file($xml_filename);
			 return $this->elaboraDettaglioElaborazioneCupArray($xml);
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
	

	
}
