<?php

namespace RichiesteBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;
use RichiesteBundle\Entity\ModalitaFinanziamento;
/**
 *
 * @ORM\Entity(repositoryClass="RichiesteBundle\Entity\VoceModalitaFinanziamentoRepository")
 * @ORM\Table(name="voci_modalita_finanziamenti",
 * indexes={
 *      @ORM\Index(name="idx_voce_modalita_modalita_id", columns={"modalita_finanziamento_id"}),
 * 		@ORM\Index(name="idx_voce_modalita_proponente_id", columns={"proponente_id"}),
 * 		@ORM\Index(name="idx_voce_modalita_richiesta_id", columns={"richiesta_id"})
 * })
 * 
 * 
 */
class VoceModalitaFinanziamento extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\ModalitaFinanziamento", inversedBy="voci_modalita_finanziamento")
	 * @ORM\JoinColumn(name="modalita_finanziamento_id", referencedColumnName="id", nullable=false)
	 */
	protected $modalita_finanziamento;

	/**
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Proponente", inversedBy="voci_modalita_finanziamento")
	 * @ORM\JoinColumn(name="proponente_id", referencedColumnName="id", nullable=false)
	 */
	protected $proponente;

	/**
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="voci_modalita_finanziamento")
	 * @ORM\JoinColumn(name="richiesta_id", referencedColumnName="id", nullable=false)
	 */
	protected $richiesta;

	/**
	 * @ORM\Column(name="importo", type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $importo;

	/**
	 * @ORM\Column(name="percentuale", type="decimal", precision=11, scale=8, nullable=true)
	 */
	protected $percentuale;
	protected $errore;

	public function __construct(?Proponente $proponente = null, ?ModalitaFinanziamento $modalita = null){
		$this->proponente = $proponente;
		if($proponente){
			$this->richiesta = $proponente->getRichiesta();
		}
		$this->modalita_finanziamento = $modalita;
	}

	public function getId() {
		return $this->id;
	}

	public function getProponente() {
		return $this->proponente;
	}

	public function getRichiesta() {
		return $this->richiesta;
	}

	public function getImporto() {
		return $this->importo;
	}

	public function getPercentuale() {
		return $this->percentuale;
	}

	public function getErrore() {
		return $this->errore;
	}

	public function setProponente($proponente) {
		$this->proponente = $proponente;
	}

	public function setRichiesta($richiesta) {
		$this->richiesta = $richiesta;
	}

	public function setImporto($importo) {
		$this->importo = $importo;
	}

	public function setPercentuale($percentuale) {
		$this->percentuale = $percentuale;
	}

	public function setErrore($errore) {
		$this->errore = $errore;
	}

	/**
	 * @return ModalitaFinanziamento
	 */
	public function getModalitaFinanziamento() {
		return $this->modalita_finanziamento;
	}
	
	/**
	 * @param ModalitaFinanziamento $modalita_finanziamento
	 */
	public function setModalitaFinanziamento($modalita_finanziamento) {
		$this->modalita_finanziamento = $modalita_finanziamento;
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
