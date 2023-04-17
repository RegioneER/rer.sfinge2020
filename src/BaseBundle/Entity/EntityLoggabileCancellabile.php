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
 * @Gedmo\SoftDeleteable(fieldName="data_cancellazione", timeAware=false)
 * @ORM\HasLifecycleCallbacks
 */
class EntityLoggabileCancellabile {


	/**
	 * @ORM\Column(name="data_cancellazione", type="datetime", nullable=true)
	 */
	protected $data_cancellazione;

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

	/**
	 * @return string
	 */
	public function getCreatoDa()
	{
		return $this->creato_da;
	}

	/**
	 * @param string $creato_da
	 */
	public function setCreatoDa($creato_da)
	{
		$this->creato_da = $creato_da;
	}

	/**
	 * @return mixed
	 */
	public function getDataCancellazione()
	{
		return $this->data_cancellazione;
	}

	/**
	 * @param mixed $data_cancellazione
	 */
	public function setDataCancellazione($data_cancellazione)
	{
		$this->data_cancellazione = $data_cancellazione;
	}

	/**
	 * @return mixed
	 */
	public function getDataCreazione()
	{
		return $this->data_creazione;
	}

	/**
	 * @param mixed $data_creazione
	 */
	public function setDataCreazione($data_creazione)
	{
		$this->data_creazione = $data_creazione;
	}

	/**
	 * @return mixed
	 */
	public function getDataModifica()
	{
		return $this->data_modifica;
	}

	/**
	 * @param mixed $data_modifica
	 */
	public function setDataModifica($data_modifica)
	{
		$this->data_modifica = $data_modifica;
	}

	/**
	 * @return string
	 */
	public function getModificatoDa()
	{
		return $this->modificato_da;
	}

	/**
	 * @param string $modificato_da
	 */
	public function setModificatoDa($modificato_da)
	{
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
    
	public function __clone() {	
//        if ($this->id) {
//			parent::__clone();
//            $this->setDataCreazione(null);
//            $this->setDataModifica(null);
//        }
    }

}