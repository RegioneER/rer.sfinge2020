<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 10:23
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC14SpecificaStatoRepository")
 * @ORM\Table(name="tc14_specifica_stato")
 */
class TC14SpecificaStato extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=10, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $specifica_stato;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="descr_specifica_stato")
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $desc_specifica_stato;

    /**
     * @return mixed
     */
    public function getSpecificaStato() {
        return $this->specifica_stato;
    }

    /**
     * @param mixed $specifica_stato
     */
    public function setSpecificaStato($specifica_stato) {
        $this->specifica_stato = $specifica_stato;
    }

    /**
     * @return mixed
     */
    public function getDescSpecificaStato() {
        return $this->desc_specifica_stato;
    }

    /**
     * @param mixed $desc_specifica_stato
     */
    public function setDescSpecificaStato($desc_specifica_stato) {
        $this->desc_specifica_stato = $desc_specifica_stato;
    }

    public function __toString() {
        return $this->specifica_stato . ' - ' . $this->desc_specifica_stato;
    }
}
