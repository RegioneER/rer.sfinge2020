<?php

namespace SfingeBundle\Entity\Importazione774;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="SfingeBundle\Entity\Importazione774\LogImportazioneIstruttoria774Repository")
 * @ORM\Table(name="log_importazione_istruttoria_774")
 */
class LogImportazioneIstruttoria774 extends EntityLoggabileCancellabile {
	
	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

    /**
     * @ORM\Column(type="string", length=5, nullable=true, name="riga_excel")
     */
    private $riga_excel;

    /**
     * @ORM\Column(type="string", length=64, nullable=true, name="protocollo")
     */
    private $protocollo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="acronimo_laboratorio_excel")
     */
    private $acronimo_laboratorio_excel;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="acronimo_normalizzato_excel")
     */
    private $acronimo_normalizzato_excel;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="acronimo_laboratorio_sc31_excel")
     */
    private $acronimo_laboratorio_sc31_excel;	
	
    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="acronimo_laboratorio_sfinge")
     */
    private $acronimo_laboratorio_sfinge;
	
	
	
	function getId() {
		return $this->id;
	}

	function getRigaExcel() {
		return $this->riga_excel;
	}

	function getProtocollo() {
		return $this->protocollo;
	}

	function getAcronimoLaboratorioExcel() {
		return $this->acronimo_laboratorio_excel;
	}

	function getAcronimoNormalizzatoExcel() {
		return $this->acronimo_normalizzato_excel;
	}

	function getAcronimoLaboratorioSc31Excel() {
		return $this->acronimo_laboratorio_sc31_excel;
	}

	function getAcronimoLaboratorioSfinge() {
		return $this->acronimo_laboratorio_sfinge;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setRigaExcel($riga_excel) {
		$this->riga_excel = $riga_excel;
	}

	function setProtocollo($protocollo) {
		$this->protocollo = $protocollo;
	}

	function setAcronimoLaboratorioExcel($acronimo_laboratorio_excel) {
		$this->acronimo_laboratorio_excel = $acronimo_laboratorio_excel;
	}

	function setAcronimoNormalizzatoExcel($acronimo_normalizzato_excel) {
		$this->acronimo_normalizzato_excel = $acronimo_normalizzato_excel;
	}

	function setAcronimoLaboratorioSc31Excel($acronimo_laboratorio_sc31_excel) {
		$this->acronimo_laboratorio_sc31_excel = $acronimo_laboratorio_sc31_excel;
	}

	function setAcronimoLaboratorioSfinge($acronimo_laboratorio_sfinge) {
		$this->acronimo_laboratorio_sfinge = $acronimo_laboratorio_sfinge;
	}

}
