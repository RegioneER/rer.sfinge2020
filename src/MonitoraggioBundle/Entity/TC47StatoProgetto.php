<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:56
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC47StatoProgettoRepository")
 * @ORM\Table(name="tc47_stato_progetto")
 */
class TC47StatoProgetto extends EntityLoggabileCancellabile {
    use Id;

    const CODICE_IN_CORSO_ESECUZIONE = 2;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=3, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $stato_progetto;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descr_stato_prg;

    /**
     * @return mixed
     */
    public function getStatoProgetto() {
        return $this->stato_progetto;
    }

    /**
     * @param mixed $stato_progetto
     */
    public function setStatoProgetto($stato_progetto) {
        $this->stato_progetto = $stato_progetto;
    }

    /**
     * @return mixed
     */
    public function getDescrStatoPrg() {
        return $this->descr_stato_prg;
    }

    /**
     * @param mixed $descr_stato_prg
     */
    public function setDescrStatoPrg($descr_stato_prg) {
        $this->descr_stato_prg = $descr_stato_prg;
    }

    public function __toString() {
        return $this->descr_stato_prg;
    }
}
