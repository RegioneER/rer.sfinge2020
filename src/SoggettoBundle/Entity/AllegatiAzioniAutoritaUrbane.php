<?php

namespace SoggettoBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="SoggettoBundle\Entity\AllegatiAzioniAutoritaUrbaneRepository")
 * @ORM\Table(name="allegati_azioni_aut_urbane")
 */
class AllegatiAzioniAutoritaUrbane extends EntityLoggabileCancellabile {

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
	private $documento_file;

	/**
	 * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\AzioneAutoritaUrbana", inversedBy="allegati")
	 * @ORM\JoinColumn(name="azione_autorita_urbana_id", referencedColumnName="id", nullable=false)
	 */
	private $azione_autorita_urbana;
	
	
	function getId() {
		return $this->id;
	}

	function getDocumentoFile() {
		return $this->documento_file;
	}

	function getAzioneAutoritaUrbana() {
		return $this->azione_autorita_urbana;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setDocumentoFile($documento_file) {
		$this->documento_file = $documento_file;
	}

	function setAzioneAutoritaUrbana($azione_autorita_urbana) {
		$this->azione_autorita_urbana = $azione_autorita_urbana;
	}


	
}
