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
class EntityCancellabile {

    /**
     * @ORM\Column(name="data_cancellazione", type="datetime", nullable=true)
     */
    protected $data_cancellazione;

	/**
     * @return mixed
     */
	public function getDataCancellazione() {
        return $this->data_cancellazione;
    }

	
    /**
     * @param mixed $data_cancellazione
     */
	public function setDataCancellazione($data_cancellazione) {
        $this->data_cancellazione = $data_cancellazione;
    }
	
}