<?php

namespace AttuazioneControlloBundle\Entity\Istruttoria;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="documenti_istruttoria_bando8")
 */
class DocumentoIstruttoriaBando8 extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;
	
	/**
	 * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile",cascade={"persist"})
	 * @ORM\JoinColumn(name="documento_file_id", referencedColumnName="id")
	 */
	private $documentoFile;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $data_richiesto;  	
	
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $data_ricevuto; 
	
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $data_scadenza; 	

	function getId() {
		return $this->id;
	}

	function getDocumentoFile() {
		return $this->documentoFile;
	}

	function getDataRichiesto() {
		return $this->data_richiesto;
	}

	function getDataRicevuto() {
		return $this->data_ricevuto;
	}

	function getDataScadenza() {
		return $this->data_scadenza;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setDocumentoFile($documentoFile) {
		$this->documentoFile = $documentoFile;
	}

	function setDataRichiesto($data_richiesto) {
		$this->data_richiesto = $data_richiesto;
	}

	function setDataRicevuto($data_ricevuto) {
		$this->data_ricevuto = $data_ricevuto;
	}

	function setDataScadenza($data_scadenza) {
		$this->data_scadenza = $data_scadenza;
	}

}
