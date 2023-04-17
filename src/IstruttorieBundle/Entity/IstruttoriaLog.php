<?php

namespace IstruttorieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * IstruttoriaLog
 *
 * @ORM\Table(name="istruttorie_log")
 * @ORM\Entity
 */
class IstruttoriaLog {
	
	/**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
	
	/**
     * @ORM\ManyToOne(targetEntity="IstruttorieBundle\Entity\IstruttoriaRichiesta", inversedBy="istruttorie_log")
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
	 * @ORM\Column(type="string", nullable=false)
	 */
	protected $oggetto;	
	
	function getId() {
		return $this->id;
	}

	function getIstruttoriaRichiesta() {
		return $this->istruttoria_richiesta;
	}

	function getUtente() {
		return $this->utente;
	}

	function getData() {
		return $this->data;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setIstruttoriaRichiesta($istruttoria_richiesta) {
		$this->istruttoria_richiesta = $istruttoria_richiesta;
	}

	function setUtente($utente) {
		$this->utente = $utente;
	}

	function setData($data) {
		$this->data = $data;
	}
	
	function getOggetto() {
		return $this->oggetto;
	}

	function setOggetto($oggetto) {
		$this->oggetto = $oggetto;
	}

}
