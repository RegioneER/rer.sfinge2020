<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 22/12/15
 * Time: 12:48
 */

namespace BaseBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
class EntityLoggabile {


    /**
	 * @ORM\Column(type="datetime", nullable=true, options={"default": "CURRENT_TIMESTAMP"})
	 */
	protected $data_creazione;

	/**
	 * @ORM\Column(type="datetime", nullable=true, columnDefinition="DATETIME on update CURRENT_TIMESTAMP")
	 */
	protected $data_modifica;

	/**
     * @var string $createdBy
     *
     * @Gedmo\Blameable(on="create")
     * @ORM\Column(type="string", nullable=true)
     */
	protected $creato_da;

    /**
     * @var string $updatedBy
     *
     * @Gedmo\Blameable(on="update")
     * @ORM\Column(type="string", nullable=true)
     */
	protected $modificato_da;


	function getDataCreazione() {
		return $this->data_creazione;
	}

	function getDataModifica() {
		return $this->data_modifica;
	}

	function setDataCreazione($data_creazione) {
		$this->data_creazione = $data_creazione;
	}

	function setDataModifica($data_modifica) {
		$this->data_modifica = $data_modifica;
	}


	function getCreatoDa() {
		return $this->creato_da;
	}

	function getModificatoDa() {
		return $this->modificato_da;
	}

	function setCreatoDa($creato_da) {
		$this->creato_da = $creato_da;
	}

	function setModificatoDa($modificato_da) {
		$this->modificato_da = $modificato_da;
	}

	
	 /**
     * Now we tell doctrine that before we persist or update we call the updatedTimestamps() function.
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
	public function updatedTimestamps() {
		$this->setDataModifica(new \DateTime(date('Y-m-d H:i:s')));

		if ($this->getDataCreazione() == null) {
			$this->setDataCreazione(new \DateTime(date('Y-m-d H:i:s')));
		}
	}

}