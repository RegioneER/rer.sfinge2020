<?php

namespace CertificazioniBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;

/**
 * @ORM\Entity(repositoryClass="CertificazioniBundle\Entity\RegistroDebitoriRepository")
 * @ORM\Table(name="registri_debitori")
 */
class RegistroDebitori extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="registro")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $richiesta;
	
	/**
     * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\Revoche\Revoca", inversedBy="registro", cascade={"persist"})
     * @ORM\JoinColumn(name="revoca_id", referencedColumnName="id")
     */
    protected $revoca;
	
	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $sospetta_frode;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $olaf;
	
	/**
	 * @ORM\OneToMany(targetEntity="CertificazioniBundle\Entity\DocumentoRegistro", mappedBy="registro", cascade={"persist"})
	 */
	protected $documenti_registro;
	
	/**
	 * @ORM\ManyToOne(targetEntity="CertificazioniBundle\Entity\TipoIterRecupero")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $tipo_iter_recupero;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $nota_iter;
	
	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $restituzione_rateizzata;
	
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $spesa_irregolare;
	
	public function getId() {
		return $this->id;
	}

	public function getRichiesta() {
		return $this->richiesta;
	}

	public function getSospettaFrode() {
		return $this->sospetta_frode;
	}

	public function getOlaf() {
		return $this->olaf;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setRichiesta($richiesta) {
		$this->richiesta = $richiesta;
	}

	public function setSospettaFrode($sospetta_frode) {
		$this->sospetta_frode = $sospetta_frode;
	}

	public function setOlaf($olaf) {
		$this->olaf = $olaf;
	}

	public function getDocumentiRegistro() {
		return $this->documenti_registro;
	}

	public function setDocumentiRegistro($documenti_registro) {
		$this->documenti_registro = $documenti_registro;
	}
	
	public function getTipoIterRecupero() {
		return $this->tipo_iter_recupero;
	}

	public function setTipoIterRecupero($tipo_iter_recupero) {
		$this->tipo_iter_recupero = $tipo_iter_recupero;
	}

	public function getSpesaIrregolare() {
		return $this->spesa_irregolare;
	}

	public function setSpesaIrregolare($spesa_irregolare) {
		$this->spesa_irregolare = $spesa_irregolare;
	}
	
	public function getNotaIter() {
		return $this->nota_iter;
	}

	public function getRestituzioneRateizzata() {
		return $this->restituzione_rateizzata;
	}

	public function setNotaIter($nota_iter) {
		$this->nota_iter = $nota_iter;
	}

	public function setRestituzioneRateizzata($restituzione_rateizzata) {
		$this->restituzione_rateizzata = $restituzione_rateizzata;
	}
	
	public function getRevoca() {
		return $this->revoca;
	}

	public function setRevoca($revoca) {
		$this->revoca = $revoca;
	}

}
