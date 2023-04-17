<?php

namespace RichiesteBundle\Entity\Autodichiarazioni;

use Doctrine\ORM\Mapping AS ORM;



/**
 * @ORM\Entity(repositoryClass="RichiesteBundle\Entity\Autodichiarazioni\ElencoProceduraRichiestaRepository")
 * @ORM\Table(name="atd_elenchi_procedure_richieste")
 */
class ElencoProceduraRichiesta {

	/**
	 *
	 * @ORM\Column(name="id", type="bigint")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Procedura")
	 * @ORM\JoinColumn(name="procedura_id", nullable=false)
	 */
	protected $procedura;

	
	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Autodichiarazioni\Elenco")
	 * @ORM\JoinColumn(name="elenco_id", nullable=false)
	 */
	protected $elenco;
	
	public function getId() {
		return $this->id;
	}

	public function getProcedura() {
		return $this->procedura;
	}

	public function getElenco() {
		return $this->elenco;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setProcedura($procedura) {
		$this->procedura = $procedura;
	}

	public function setElenco($elenco) {
		$this->elenco = $elenco;
	}


}
