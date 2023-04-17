<?php
namespace SoggettoBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;
	
/**
 * @ORM\Entity()
 */
class AziendaVersion extends SoggettoVersion
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


	public function getFatturato() {
		return $this->fatturato;
	}

	public function getBilancio() {
		return $this->bilancio;
	}

	public function getCcia() {
		return $this->ccia;
	}

	public function getDataCcia() {
		return $this->data_ccia;
	}

	public function getRea() {
		return $this->rea;
	}

	public function getDataRea() {
		return $this->data_rea;
	}

	public function getRegistroEquivalente() {
		return $this->registro_equivalente;
	}

	public function setFatturato($fatturato) {
		$this->fatturato = $fatturato;
	}

	public function setBilancio($bilancio) {
		$this->bilancio = $bilancio;
	}

	public function setCcia($ccia) {
		$this->ccia = $ccia;
	}

	public function setDataCcia($data_ccia) {
		$this->data_ccia = $data_ccia;
	}

	public function setRea($rea) {
		$this->rea = $rea;
	}

	public function setDataRea($data_rea) {
		$this->data_rea = $data_rea;
	}

	public function setRegistroEquivalente($registro_equivalente) {
		$this->registro_equivalente = $registro_equivalente;
	}





}