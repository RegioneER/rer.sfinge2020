<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:19
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC22MotivoAssenzaCIGRepository")
 * @ORM\Table(name="tc22_motivo_assenza_cig")
 */
class TC22MotivoAssenzaCIG extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=3, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $motivo_assenza_cig;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $desc_motivo_assenza_cig;

    /**
     * @return mixed
     */
    public function getMotivoAssenzaCig() {
        return $this->motivo_assenza_cig;
    }

    /**
     * @param mixed $motivo_assenza_cig
     */
    public function setMotivoAssenzaCig($motivo_assenza_cig) {
        $this->motivo_assenza_cig = $motivo_assenza_cig;
    }

    /**
     * @return mixed
     */
    public function getDescMotivoAssenzaCig() {
        return $this->desc_motivo_assenza_cig;
    }

    /**
     * @param mixed $desc_motivo_assenza_cig
     */
    public function setDescMotivoAssenzaCig($desc_motivo_assenza_cig) {
        $this->desc_motivo_assenza_cig = $desc_motivo_assenza_cig;
    }

    public function __toString() {
        return $this->getMotivoAssenzaCig() . ' - ' . $this->getDescMotivoAssenzaCig();
    }
}
