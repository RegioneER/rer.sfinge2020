<?php

namespace RichiesteBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Entity(repositoryClass="RichiesteBundle\Entity\VoceFaseProceduraleRepository")
 * @ORM\Table(name="voci_fase_procedurale")
 */
class VoceFaseProcedurale extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\FaseProcedurale", inversedBy="voci_fase_procedurale")
	 * @ORM\JoinColumn(name="fase_procedurale_id", referencedColumnName="id", nullable=false)
	 */
	protected $fase_procedurale;

	/**
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="voci_fase_procedurale")
	 * @ORM\JoinColumn(name="richiesta_id", referencedColumnName="id", nullable=false)
	 */
	protected $richiesta;

	/**
	 * @ORM\Column(name="data_avvio_prevista", type="date", nullable=true)
	 */
	protected $data_avvio_prevista;

	/**
	 * @ORM\Column(name="data_conclusione_prevista", type="date", nullable=true)
	 */
	protected $data_conclusione_prevista;

	/**
	 * @ORM\Column(name="data_avvio_effettivo", type="date", nullable=true)
	 */
	protected $data_avvio_effettivo;

	/**
	 * @ORM\Column(name="data_conclusione_effettiva", type="date", nullable=true)
	 */
	protected $data_conclusione_effettiva;

	/**
	 * @ORM\Column(name="data_approvazione", type="date", nullable=true)
	 */
	protected $data_approvazione;

	/**
	 * @ORM\Column(name="data_opzionale", type="date", nullable=true)
	 */
	protected $data_opzionale;
	protected $errore;

	public function getId() {
		return $this->id;
	}

	/**
	 * @return FaseProcedurale
	 */
	public function getFaseProcedurale() {
		return $this->fase_procedurale;
	}

	public function getRichiesta() {
		return $this->richiesta;
	}

	public function getDataAvvioPrevista() {
		return $this->data_avvio_prevista;
	}

	public function getDataConclusionePrevista() {
		return $this->data_conclusione_prevista;
	}

	public function getDataAvvioEffettivo() {
		return $this->data_avvio_effettivo;
	}

	public function getDataConclusioneEffettiva() {
		return $this->data_conclusione_effettiva;
	}

	public function getDataApprovazione() {
		return $this->data_approvazione;
	}

	public function getDataOpzionale() {
		return $this->data_opzionale;
	}

	public function getErrore() {
		return $this->errore;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setFaseProcedurale($fase_procedurale) {
		$this->fase_procedurale = $fase_procedurale;
	}

	public function setRichiesta($richiesta) {
		$this->richiesta = $richiesta;
	}

	public function setDataAvvioPrevista($data_avvio_prevista) {
		$this->data_avvio_prevista = $data_avvio_prevista;
	}

	public function setDataConclusionePrevista($data_conclusione_prevista) {
		$this->data_conclusione_prevista = $data_conclusione_prevista;
	}

	public function setDataAvvioEffettivo($data_avvio_effettivo) {
		$this->data_avvio_effettivo = $data_avvio_effettivo;
	}

	public function setDataConclusioneEffettiva($data_conclusione_effettiva) {
		$this->data_conclusione_effettiva = $data_conclusione_effettiva;
	}

	public function setDataApprovazione($data_approvazione) {
		$this->data_approvazione = $data_approvazione;
	}

	public function setDataOpzionale($data_opzionale) {
		$this->data_opzionale = $data_opzionale;
	}

	public function setErrore($errore) {
		$this->errore = $errore;
	}

	public function getSoggetto() {
		$proponenti = $this->richiesta->getProponenti();
		foreach ($proponenti as $proponente) {
			if ($proponente->getMandatario()) {
				return $proponente->getSoggetto();
			}
		}
	}

}
