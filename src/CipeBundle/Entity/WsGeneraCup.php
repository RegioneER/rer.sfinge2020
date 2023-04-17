<?php

namespace CipeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CipeBundle\Entity\RichiestaCupGenerazione;
use CipeBundle\Entity\RispostaCupGenerazione;

/**
 * @author Gaetano Borgosano <gborgosano@schema31.it>
 * @ORM\Table(name="ws_genera_cup")
 * @ORM\Entity(repositoryClass="CipeBundle\Entity\WsGeneraCupRepository")
 */
class WsGeneraCup
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
	 * @ORM\Column(name="id_richiesta", type="integer")
	 */
	protected $idRichiesta = null;
	function getIdRichiesta() { return $this->idRichiesta; }
	function setIdRichiesta($idRichiesta) { $this->idRichiesta = $idRichiesta; }

	/**
	 * @ORM\Column(name="id_progetto", type="integer")
	 */
	protected $idProgetto = null;
	function getIdProgetto() { return $this->idProgetto; }
	function setIdProgetto($idProgetto) { $this->idProgetto = $idProgetto; }
	
	/**
     * @ORM\Column(name="richiesta_valida", type="boolean", nullable=true)
	 */
	protected $richiestaValida = null;
	function getRichiestaValida() { return $this->richiestaValida; }
	function setRichiestaValida($richiestaValida) { $this->richiestaValida = $richiestaValida; }
	
	
    /**
     * @ORM\Column(name="text_richiesta_cup_generazione", type="text", nullable=true)
     */
    protected $textRichiestaCupGenerazione = null;
	function getTextRichiestaCupGenerazione() { return $this->textRichiestaCupGenerazione; }
	function setTextRichiestaCupGenerazione($textRichiestaCupGenerazione) { $this->textRichiestaCupGenerazione =trim($textRichiestaCupGenerazione); }

	
	/**
     * @ORM\Column(name="errori_validazione", type="text", nullable=true)
     */
	protected $erroriValidazione = null;
	function getErroriValidazione() { 
		if(\is_null($this->erroriValidazione)) return array();
		return json_decode($this->erroriValidazione); 
	}

	function setErroriValidazione($erroriValidazione) {	
		if(\is_null($erroriValidazione) || (\is_array($erroriValidazione) && count($erroriValidazione) == 0)) {
			$erroriValidazione = null;
		} else {
			$erroriValidazione = json_encode($erroriValidazione); 
		}
		$this->erroriValidazione = $erroriValidazione;
		
	}

	function elabRichiestaValidaErroriValidazione() { 
		$richiestaValida = true;
		$erroriValidazione = $this->getErroriValidazione();
		if(count($erroriValidazione) > 0) $richiestaValida = false;
		$this->setRichiestaValida($richiestaValida);
	}

	/**
	 * @ORM\Column(name="curl_http_status_code", type="string", length=3, nullable=true)
	 */
	protected $curlHttpStatusCode = null;
	function getCurlHttpStatusCode() { return $this->curlHttpStatusCode; }
	function setCurlHttpStatusCode($curlHttpStatusCode) { $this->curlHttpStatusCode = $curlHttpStatusCode; }

	/**
     * @ORM\Column(name="curl_error", type="boolean", nullable=true)
	 */
	protected $curlError = null;
	function getCurlError() { return $this->curlError; }
	function setCurlError($curlError) { $this->curlError = $curlError; }

		
	/**
     * @ORM\Column(name="curl_response", type="text", nullable=true)
     */
	protected $curlResponse = null;
	function getCurlResponse() { return $this->curlResponse; }
	function setCurlResponse($curlResponse) { $this->curlResponse = $curlResponse; }

	/**
     * @ORM\Column(name="curl_error_messages", type="text", nullable=true)
     */
	protected $curlErrorMessages = null;
	function getCurlErrorMessages() { return $this->curlErrorMessages; }
	function setCurlErrorMessages($curlErrorMessages) { $this->curlErrorMessages = $curlErrorMessages; }

		
	
	/**
     * @ORM\Column(name="text_risposta_cup_generazione", type="text", nullable=true)
     */
    protected $TextRispostaCupGenerazione = null;
	function getTextRispostaCupGenerazione() { return $this->TextRispostaCupGenerazione; }
	function setTextRispostaCupGenerazione($TextRispostaCupGenerazione) { $this->TextRispostaCupGenerazione = trim($TextRispostaCupGenerazione); }

    /**
     * @ORM\Column(name="time_stamp_richiesta", type="datetime", nullable=false)
     */
    protected $timeStampRichiesta = null;
	function getTimeStampRichiesta() { return $this->timeStampRichiesta; }
	function setTimeStampRichiesta($TimeStampRichiesta) { $this->timeStampRichiesta = $TimeStampRichiesta; $this->initTimestampRichiestaCupGenerazione($TimeStampRichiesta); }
	function initTimestampRichiestaCupGenerazione($TimeStampRichiesta) {
		$this->getRichiestaCupGenerazione()->setTimestampUltimaRichiesta($TimeStampRichiesta);
	}
	
	/**
     * @ORM\Column(name="time_stamp_risposta", type="datetime", nullable=true)
	 */
	protected $timeStampRisposta = null;
	function getTimeStampRisposta() { return $this->timeStampRisposta; }
	function setTimeStampRisposta($TimeStampRisposta) { $this->timeStampRisposta = $TimeStampRisposta; }

	/**
     * @ORM\Column(name="esito", type="boolean", nullable=true)
	 */
	protected $esito = false;
	function getEsito() { return $this->esito; }
	function setEsito($esito) { $this->esito = $esito; }

	
	/**
     * @var RichiestaCupGenerazione
     */
    protected $RichiestaCupGenerazione = null;
	function getRichiestaCupGenerazione() { return $this->RichiestaCupGenerazione; }
	function setRichiestaCupGenerazione(RichiestaCupGenerazione $RichiestaCupGenerazione) { $this->RichiestaCupGenerazione = $RichiestaCupGenerazione; }

    /**
	 * @var RispostaCupGenerazione
	 * @ORM\OneToOne(targetEntity="CipeBundle\Entity\RispostaCupGenerazione", inversedBy="WsGeneraCup")
     * @ORM\JoinColumn(name="risposta_cup_generazione_id", referencedColumnName="id", nullable=true)
	 */
	protected $RispostaCupGenerazione = null;
	function getRispostaCupGenerazione() { return $this->RispostaCupGenerazione; }
	function setRispostaCupGenerazione(RispostaCupGenerazione $RispostaCupGenerazione) { $this->RispostaCupGenerazione = $RispostaCupGenerazione; }
	
	/**
     * @ORM\Column(name="error_message", type="text", nullable=true)
     */
    protected $errorMessage = null;
	function getErrorMessage() { return $this->errorMessage; }
	function setErrorMessage($errorMessage) { $this->errorMessage = $errorMessage; }


	
} /* end of class WsGeneraCup */

?>