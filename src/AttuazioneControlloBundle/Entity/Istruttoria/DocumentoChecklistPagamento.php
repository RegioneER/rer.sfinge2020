<?php

namespace AttuazioneControlloBundle\Entity\Istruttoria;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="documenti_checklist_pagamenti")
 */
class DocumentoChecklistPagamento extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;
	
	/**
	 * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile",cascade={"persist", "remove"})
	 * @ORM\JoinColumn(name="documentofile_id", referencedColumnName="id")
	 */
	private $documentoFile;
	
	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneChecklistPagamento", inversedBy="documenti_checklist")
	 * @ORM\JoinColumn(name="valutazione_checklist_id", nullable=false)
	 */
	protected $valutazioneChecklistPagamento;

	public function getId() {
		return $this->id;
	}

	public function getDocumentoFile() {
		return $this->documentoFile;
	}

	public function getValutazioneChecklistPagamento() {
		return $this->valutazioneChecklistPagamento;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setDocumentoFile($documentoFile) {
		$this->documentoFile = $documentoFile;
	}

	public function setValutazioneChecklistPagamento($valutazioneChecklistPagamento) {
		$this->valutazioneChecklistPagamento = $valutazioneChecklistPagamento;
	}

}
