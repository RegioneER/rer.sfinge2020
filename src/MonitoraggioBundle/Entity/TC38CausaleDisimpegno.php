<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:40
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC38CausaleDisimpegnoRepository")
 * @ORM\Table(name="tc38_causale_disimpegno")
 */
class TC38CausaleDisimpegno extends EntityLoggabileCancellabile {
    use Id;

    const REVOCA = '01';
    const MINORI_SPESE = '02';

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=3, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $causale_disimpegno;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizione_causale_disimpegno;

    /**
     * @return mixed
     */
    public function getCausaleDisimpegno() {
        return $this->causale_disimpegno;
    }

    /**
     * @param mixed $causale_disimpegno
     */
    public function setCausaleDisimpegno($causale_disimpegno) {
        $this->causale_disimpegno = $causale_disimpegno;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneCausaleDisimpegno() {
        return $this->descrizione_causale_disimpegno;
    }

    /**
     * @param mixed $descrizione_causale_disimpegno
     */
    public function setDescrizioneCausaleDisimpegno($descrizione_causale_disimpegno) {
        $this->descrizione_causale_disimpegno = $descrizione_causale_disimpegno;
    }

    public function __toString() {
        return $this->causale_disimpegno . ' - ' . $this->descrizione_causale_disimpegno;
    }
}
