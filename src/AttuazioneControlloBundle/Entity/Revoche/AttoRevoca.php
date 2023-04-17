<?php

namespace AttuazioneControlloBundle\Entity\Revoche;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\Revoche\AttoRevocaRepository")
 * @ORM\Table(name="atti_revoche")
 */
class AttoRevoca extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string", length=255, nullable=false)
	 * @Assert\NotBlank
	 */
	protected $numero;

	/**
	 * @ORM\Column(type="datetime", nullable=false)
	 * @Assert\NotBlank
	 */
	protected $data;

	/**
	 * @ORM\Column(type="text", nullable=false)
	 * @Assert\NotBlank
	 */
	protected $descrizione;
	
	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Revoche\TipoRevoca")
	 * @ORM\JoinColumn(nullable=true)
	 * @Assert\NotBlank
	 */
	protected $tipo;
	
	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Revoche\TipoMotivazioneRevoca")
	 * @ORM\JoinColumn(nullable=true)
	 * @Assert\NotBlank
	 */
	protected $tipo_motivazione;
	
	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Revoche\TipoOrigineRevoca")
	 * @ORM\JoinColumn(nullable=true)
	 * @Assert\NotBlank
	 */
	protected $tipo_origine_revoca;
	
	 /**
     * @ORM\ManyToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile",cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     * @Assert\NotNull
     */
    protected $documento;

	
	function getId() {
		return $this->id;
	}

	function getNumero() {
		return $this->numero;
	}

	function getData() {
		return $this->data;
	}

	function getDescrizione() {
		return $this->descrizione;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setNumero($numero) {
		$this->numero = $numero;
	}

	function setData($data) {
		$this->data = $data;
	}

	function setDescrizione($descrizione) {
		$this->descrizione = $descrizione;
	}

	function __toString() {
		return $this->numero . ': ' .$this->descrizione;
	}

	public function getTipo() {
		return $this->tipo;
	}

	public function getTipoMotivazione() {
		return $this->tipo_motivazione;
	}

	public function setTipo($tipo) {
		$this->tipo = $tipo;
	}

	public function setTipoMotivazione($tipo_motivazione) {
		$this->tipo_motivazione = $tipo_motivazione;
	}

	public function getDocumento() {
		return $this->documento;
	}

	public function setDocumento($documento) {
		$this->documento = $documento;
	}
	
	public function getTipoOrigineRevoca() {
		return $this->tipo_origine_revoca;
	}

	public function setTipoOrigineRevoca($tipo_origine_revoca) {
		$this->tipo_origine_revoca = $tipo_origine_revoca;
	}

}
