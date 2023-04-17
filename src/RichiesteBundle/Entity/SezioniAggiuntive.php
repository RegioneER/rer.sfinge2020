<?php

namespace RichiesteBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;


/**
 * @ORM\Entity(repositoryClass="RichiesteBundle\Entity\SezioniAggiuntiveRepository")
 * @ORM\Table(name="sezioni_aggiuntive",
 *  indexes={
 *      @ORM\Index(name="idx_procedura_sezione_id", columns={"procedura_id"})
 *  })
 */
class SezioniAggiuntive extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Procedura",inversedBy="sezioni_aggiuntive")
	 * @ORM\JoinColumn(name="procedura_id", referencedColumnName="id", nullable=false)
	 */
	private $procedura;
	
	/**
	 * @ORM\Column(name="nome", type="string", length=50)
	 */
	private $nome_sezione;

	function getId() {
		return $this->id;
	}

	function getProcedura() {
		return $this->procedura;
	}

	function getNomeSezione() {
		return $this->nome_sezione;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setProcedura($procedura) {
		$this->procedura = $procedura;
	}

	function setNomeSezione($nome_sezione) {
		$this->nome_sezione = $nome_sezione;
	}


}
