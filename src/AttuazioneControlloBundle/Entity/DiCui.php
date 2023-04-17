<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\DiCuiRepository")
 * @ORM\Table(name="di_cui")
 */
class DiCui extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo", inversedBy="di_cui")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $voce_piano_costo_giustificativo;
	
	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", inversedBy="di_cui_provenienza")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $pagamento_provenienza;
	
	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", inversedBy="di_cui_destinazione")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $pagamento_destinazione;
	
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $importo;
    
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $importo_approvato;  

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */    
    protected $nota;
            
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $importo_non_ammesso_per_superamento_massimali; 
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */ 
	protected $nota_superamento_massimali;
	
	function getId() {
		return $this->id;
	}

	function getVocePianoCostoGiustificativo() {
		return $this->voce_piano_costo_giustificativo;
	}

	function getPagamentoProvenienza() {
		return $this->pagamento_provenienza;
	}

	function getPagamentoDestinazione() {
		return $this->pagamento_destinazione;
	}

	function getImporto() {
		return $this->importo;
	}

	function getImportoApprovato() {
		return $this->importo_approvato;
	}

	function getNota() {
		return $this->nota;
	}

	function getImportoNonAmmessoPerSuperamentoMassimali() {
		return $this->importo_non_ammesso_per_superamento_massimali;
	}

	function getNotaSuperamentoMassimali() {
		return $this->nota_superamento_massimali;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setVocePianoCostoGiustificativo($voce_piano_costo_giustificativo) {
		$this->voce_piano_costo_giustificativo = $voce_piano_costo_giustificativo;
	}

	function setPagamentoProvenienza($pagamento_provenienza) {
		$this->pagamento_provenienza = $pagamento_provenienza;
	}

	function setPagamentoDestinazione($pagamento_destinazione) {
		$this->pagamento_destinazione = $pagamento_destinazione;
	}

	function setImporto($importo) {
		$this->importo = $importo;
	}

	function setImportoApprovato($importo_approvato) {
		$this->importo_approvato = $importo_approvato;
	}

	function setNota($nota) {
		$this->nota = $nota;
	}

	function setImportoNonAmmessoPerSuperamentoMassimali($importo_non_ammesso_per_superamento_massimali) {
		$this->importo_non_ammesso_per_superamento_massimali = $importo_non_ammesso_per_superamento_massimali;
	}

	function setNotaSuperamentoMassimali($nota_superamento_massimali) {
		$this->nota_superamento_massimali = $nota_superamento_massimali;
	}

}
