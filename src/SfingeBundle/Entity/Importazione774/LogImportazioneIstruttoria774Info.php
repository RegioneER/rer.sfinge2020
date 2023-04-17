<?php

namespace SfingeBundle\Entity\Importazione774;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="SfingeBundle\Entity\Importazione774\LogImportazioneIstruttoria774InfoRepository")
 * @ORM\Table(name="log_importazione_istruttoria_774_info")
 */
class LogImportazioneIstruttoria774Info extends EntityLoggabileCancellabile {
	
	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="info")
     */
    private $info;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="note")
     */
    private $note;

	function getId() {
		return $this->id;
	}

	function getInfo() {
		return $this->info;
	}

	function getNote() {
		return $this->note;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setInfo($info) {
		$this->info = $info;
	}

	function setNote($note) {
		$this->note = $note;
	}


}
