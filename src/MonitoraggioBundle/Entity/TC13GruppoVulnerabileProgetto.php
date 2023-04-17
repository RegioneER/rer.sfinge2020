<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 10:20
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC13GruppoVulnerabileProgettoRepository")
 * @ORM\Table(name="tc13_gruppo_vulnerabile_progetto")
 */
class TC13GruppoVulnerabileProgetto extends EntityLoggabileCancellabile {
    use Id;
    const NO_VULNERABILE = '03';

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=3, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $cod_vulnerabili;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="descr_vulnerabili")
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $desc_vulnerabili;

    /**
     * @return mixed
     */
    public function getCodVulnerabili() {
        return $this->cod_vulnerabili;
    }

    /**
     * @param mixed $cod_vulnerabili
     */
    public function setCodVulnerabili($cod_vulnerabili) {
        $this->cod_vulnerabili = $cod_vulnerabili;
    }

    /**
     * @return mixed
     */
    public function getDescVulnerabili() {
        return $this->desc_vulnerabili;
    }

    /**
     * @param mixed $desc_vulnerabili
     */
    public function setDescVulnerabili($desc_vulnerabili) {
        $this->desc_vulnerabili = $desc_vulnerabili;
    }

    public function __toString() {
        return $this->cod_vulnerabili . ' - ' . $this->desc_vulnerabili;
    }
}
