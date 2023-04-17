<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:58
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC49CausaleTrasferimentoRepository")
 * @ORM\Table(name="tc49_causale_trasferimento")
 */
class TC49CausaleTrasferimento extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=3, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $causale_trasferimento;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizione_causale_trasferimento;

    /**
     * @return mixed
     */
    public function getCausaleTrasferimento() {
        return $this->causale_trasferimento;
    }

    /**
     * @param mixed $causale_trasferimento
     */
    public function setCausaleTrasferimento($causale_trasferimento) {
        $this->causale_trasferimento = $causale_trasferimento;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneCausaleTrasferimento() {
        return $this->descrizione_causale_trasferimento;
    }

    /**
     * @param mixed $descrizione_causale_trasferimento
     */
    public function setDescrizioneCausaleTrasferimento($descrizione_causale_trasferimento) {
        $this->descrizione_causale_trasferimento = $descrizione_causale_trasferimento;
    }

    public function __toString() {
        return $this->causale_trasferimento . ' - ' . $this->descrizione_causale_trasferimento;
    }
}
