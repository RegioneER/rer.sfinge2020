<?php

namespace IstruttorieBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="documenti_integrazione")
 */
class DocumentoIntegrazioneIstruttoria extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $documento_file;

	/**
	 * @ORM\ManyToOne(targetEntity="IstruttorieBundle\Entity\RispostaIntegrazioneIstruttoria", inversedBy="documenti")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $risposta_integrazione;
	
    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Proponente")
     * @ORM\JoinColumn(nullable=true)
     */
    private $proponente;	

	function getId() {
		return $this->id;
	}

	function setId($id) {
		$this->id = $id;
	}
	
	function getDocumentoFile() {
		return $this->documento_file;
	}

	function setDocumentoFile($documento_file) {
		$this->documento_file = $documento_file;
	}

	function getRispostaIntegrazione() {
		return $this->risposta_integrazione;
	}

	function setRispostaIntegrazione($risposta_integrazione) {
		$this->risposta_integrazione = $risposta_integrazione;
	}
	
	function getProponente() {
		return $this->proponente;
	}

	function setProponente($proponente) {
		$this->proponente = $proponente;
	}

}
