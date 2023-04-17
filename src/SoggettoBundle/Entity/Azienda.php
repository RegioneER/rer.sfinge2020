<?php
namespace SoggettoBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;
	
/**
 * @ORM\Entity(repositoryClass="SoggettoBundle\Entity\AziendaRepository")
 */
class Azienda extends Soggetto
{
    /**
     * @Assert\Length(max = "15")
	 * @ORM\Column(type="decimal", precision=15, scale=2, nullable=true)
	 */
    private $fatturato;

    /**
	 * @ORM\Column(type="decimal", precision=15, scale=2, nullable=true)
     */
    private $bilancio;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $ccia;
	
	/**
     * @ORM\Column(type="date", nullable=true)
     */
    private $data_ccia;
	
	/**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $rea;
	
	/**
     * @ORM\Column(type="date", nullable=true)
     */
    private $data_rea;

	/**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $registro_equivalente;
	
	function getFatturato() {
		return $this->fatturato;
	}

	function getBilancio() {
		return $this->bilancio;
	}

	function getCcia() {
		return $this->ccia;
	}

	function setFatturato($fatturato) {
		$this->fatturato = $fatturato;
	}

	function setBilancio($bilancio) {
		$this->bilancio = $bilancio;
	}

	function setCcia($ccia) {
		$this->ccia = $ccia;
	}

	function getDataCcia() {
		return $this->data_ccia;
	}

	function getRea() {
		return $this->rea;
	}

	function getDataRea() {
		return $this->data_rea;
	}

	function getRegistroEquivalente() {
		return $this->registro_equivalente;
	}

	function setDataCcia($data_ccia) {
		$this->data_ccia = $data_ccia;
	}

	function setRea($rea) {
		$this->rea = $rea;
	}

	function setDataRea($data_rea) {
		$this->data_rea = $data_rea;
	}

	function setRegistroEquivalente($registro_equivalente) {
		$this->registro_equivalente = $registro_equivalente;
	}

	public function getTipo() {
        return "AZIENDA";
    }

}