<?php

namespace IstruttorieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * IstruttoriaAtcLog
 *
 * @ORM\Table(name="istruttorie_atc_log")
 * @ORM\Entity
 */
class IstruttoriaAtcLog {

	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="IstruttorieBundle\Entity\IstruttoriaRichiesta", inversedBy="istruttorie_atc_log")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $istruttoria_richiesta;

	/**
	 * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Utente")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $utente;

	/**
	 * @ORM\Column(type="datetime", nullable=false)
	 */
	protected $data;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 * @Assert\NotNull(groups={"avanzamento_atc"})
	 */
	protected $ammissibilita_atto;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 * @Assert\NotNull(groups={"avanzamento_atc"})
	 */
	protected $concessione;

	/**
	 * @ORM\Column(name="contributo_ammesso", type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $contributo_ammesso;

	/**
	 * @ORM\Column(name="impegno_ammesso", type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $impegno_ammesso;

	/**
	 * @ORM\Column(type="date", name="data_contributo", nullable=true)
	 * @Assert\Date()
	 */
	protected $data_contributo;

	/**
	 * @ORM\Column(type="date", name="data_impegno", nullable=true)
	 * @Assert\Date()
	 */
	protected $data_impegno;

	/**
	 * @ORM\Column(type="string", nullable=false)
	 */
	protected $oggetto;
	
	/**
	 * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Atto")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $atto_modifica_concessione_atc;

	public function getId() {
		return $this->id;
	}

	public function getIstruttoriaRichiesta() {
		return $this->istruttoria_richiesta;
	}

	public function getUtente() {
		return $this->utente;
	}

	public function getData() {
		return $this->data;
	}

	public function getAmmissibilitaAtto() {
		return $this->ammissibilita_atto;
	}

	public function getConcessione() {
		return $this->concessione;
	}

	public function getContributoAmmesso() {
		return $this->contributo_ammesso;
	}

	public function getImpegnoAmmesso() {
		return $this->impegno_ammesso;
	}

	public function getDataContributo() {
		return $this->data_contributo;
	}

	public function getDataImpegno() {
		return $this->data_impegno;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setIstruttoriaRichiesta($istruttoria_richiesta) {
		$this->istruttoria_richiesta = $istruttoria_richiesta;
	}

	public function setUtente($utente) {
		$this->utente = $utente;
	}

	public function setData($data) {
		$this->data = $data;
	}

	public function setAmmissibilitaAtto($ammissibilita_atto) {
		$this->ammissibilita_atto = $ammissibilita_atto;
	}

	public function setConcessione($concessione) {
		$this->concessione = $concessione;
	}

	public function setContributoAmmesso($contributo_ammesso) {
		$this->contributo_ammesso = $contributo_ammesso;
	}

	public function setImpegnoAmmesso($impegno_ammesso) {
		$this->impegno_ammesso = $impegno_ammesso;
	}

	public function setDataContributo($data_contributo) {
		$this->data_contributo = $data_contributo;
	}

	public function setDataImpegno($data_impegno) {
		$this->data_impegno = $data_impegno;
	}

	public function getOggetto() {
		return $this->oggetto;
	}

	public function setOggetto($oggetto) {
		$this->oggetto = $oggetto;
	}
	
	public function getAttoModificaConcessioneAtc() {
		return $this->atto_modifica_concessione_atc;
	}

	public function setAttoModificaConcessioneAtc($atto_modifica_concessione_atc) {
		$this->atto_modifica_concessione_atc = $atto_modifica_concessione_atc;
	}

}
