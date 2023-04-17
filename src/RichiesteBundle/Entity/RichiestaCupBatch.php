<?php

namespace RichiesteBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use DocumentoBundle\Entity\DocumentoFile;
use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity()
 * @ORM\Table(name="richieste_cup_batch",
 *  indexes={
 *       @ORM\Index(name="idx_cup_batch_documento_richiesta_id", columns={"cup_batch_documento_richiesta_id"}),
 *		 @ORM\Index(name="idx_cup_batch_documento_risposta_id", columns={"cup_batch_documento_risposta_id"})
 *  })
 
 */
class RichiestaCupBatch extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	
	/**
	 * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile")
	 * @ORM\JoinColumn(name="cup_batch_documento_richiesta_id", referencedColumnName="id", nullable=true)
	 */
	protected $cupBatchDocumentoRichiesta;

	/**
	 * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile")
	 * @ORM\JoinColumn(name="cup_batch_documento_risposta_id", referencedColumnName="id", nullable=true)
	 */
	protected $cupBatchDocumentoRisposta;
	
	
	/**
	 * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile")
	 * @ORM\JoinColumn(name="cup_batch_documento_scarto_id", referencedColumnName="id", nullable=true)
	 */
	protected $cupBatchDocumentoScarto;
	
	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $dataRisposta;

	
	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $dataScarto;
	
	/**
	 * @ORM\Column(type="json_array", nullable=true)
	 */
	protected $esiti;
	
	
	/**
	 * @ORM\Column(type="json_array", nullable=true)
	 */
	protected $scarti;
	
	/**
	 * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\IstruttoriaRichiesta", mappedBy="UltimaRichiestaCupBatch")
	 */
	protected $IstruttorieGenerate;
	
	/**
	 * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\IstruttoriaRichiesta", mappedBy="RichiestaCupBatchRisposta")
	 */
	protected $IstruttorieValorizzate;

	/**
	 * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\IstruttoriaRichiesta", mappedBy="UltimaRichiestaCupBatchScarto")
	 */
	protected $IstruttorieScartate;
	
	
	
	
	protected $salvaEsiti=true;
	
	
	protected $salvaScarti=true;
	
	
	function getId() { return $this->id; }

	function getCupBatchDocumentoRichiesta() {
		return $this->cupBatchDocumentoRichiesta;
	}

	function getCupBatchDocumentoRisposta() {
		return $this->cupBatchDocumentoRisposta;
	}

	function getDataRisposta() {
		return $this->dataRisposta;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setCupBatchDocumentoRichiesta($cupBatchDocumentoRichiesta) {
		$this->cupBatchDocumentoRichiesta = $cupBatchDocumentoRichiesta;
	}

	function setCupBatchDocumentoRisposta($cupBatchDocumentoRisposta) {
		$this->cupBatchDocumentoRisposta = $cupBatchDocumentoRisposta;
	}

	function setDataRisposta($dataRisposta) {
		$this->dataRisposta = $dataRisposta;
	}

	
	function getCupBatchDocumentoScarto() {
		return $this->cupBatchDocumentoScarto;
	}

	function getDataScarto() {
		return $this->dataScarto;
	}

	function setCupBatchDocumentoScarto($cupBatchDocumentoScarto) {
		$this->cupBatchDocumentoScarto = $cupBatchDocumentoScarto;
	}

	function setDataScarto($dataScarto) {
		$this->dataScarto = $dataScarto;
	}

		
	function getEsiti() {
		return $this->esiti;
	}

	function setEsiti($esiti) {
		$this->esiti = $esiti;
	}
	
	function hasSalvaEsiti() {
		$Esiti = $this->getEsiti();
		if(!\is_null($Esiti) && count($Esiti) >0) return true;
		return false;
	}
	
	function getSalvaEsiti() { 
		return $this->salvaEsiti;
	}

	function setSalvaEsiti($salvaEsiti) {
		$this->salvaEsiti = $salvaEsiti;
	}
	
	
	function getScarti() {
		return $this->scarti;
	}

	function setScarti($scarti) {
		$this->scarti = $scarti;
	}

	
	function hasSalvaScarti() {
		$Scarti = $this->getScarti();
		if(!\is_null($Scarti) && count($Scarti) >0) return true;
		return false;
	}
	
	function getSalvaScarti() {
		return $this->salvaScarti;
	}

	function setSalvaScarti($salvaScarti) {
		$this->salvaScarti = $salvaScarti;
	}

	
	protected function getScartiByKeyValue($key, $value) {
		$scarti = $this->getScarti();
		foreach ($scarti as $scarto) {
			if(\array_key_exists("$key", $scarto) && $scarto['$key'] = $value ) return $scarto;
		}
		return null;
		
	}
	
	function getScartiOfIdProgetto($id_progetto) {
		return $this->getScartiByKeyValue("id_progetto", $id_progetto);
	}

	
	function getScartiOfCodificaLocale($codifica_locale) {
		return $this->getScartiByKeyValue("codifica_locale", $codifica_locale);
	}
	
	function getIstruttorieGenerate() {
		return $this->IstruttorieGenerate;
	}

	function getIstruttorieValorizzate() {
		return $this->IstruttorieValorizzate;
	}

	function getIstruttorieScartate() {
		return $this->IstruttorieScartate;
	}

	function setIstruttorieGenerate($IstruttorieGenerate) {
		$this->IstruttorieGenerate = $IstruttorieGenerate;
	}

	function setIstruttorieValorizzate($IstruttorieValorizzate) {
		$this->IstruttorieValorizzate = $IstruttorieValorizzate;
	}

	function setIstruttorieScartate($IstruttorieScartate) {
		$this->IstruttorieScartate = $IstruttorieScartate;
	}

	
	public function __construct() {
		$this->IstruttorieGenerate = new ArrayCollection();
		$this->IstruttorieScartate = new ArrayCollection();
		$this->IstruttorieValorizzate = new ArrayCollection();
	}
	
}
