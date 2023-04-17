<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 05/06/17
 * Time: 15:45
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC8GrandeProgettoRepository")
 * @ORM\Table(name="tc8_grande_progetto")
 */
class TC8GrandeProgetto extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=50, maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $grande_progetto;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $descrizione_grande_progetto;

    /**
     * @ORM\ManyToOne(targetEntity="TC4Programma")
     * @Assert\NotNull
     * @ORM\JoinColumn(name="programma_id", referencedColumnName="id")
     */
    protected $programma;

    /**
     * @return mixed
     */
    public function getGrandeProgetto() {
        return $this->grande_progetto;
    }

    /**
     * @param mixed $grande_progetto
     */
    public function setGrandeProgetto($grande_progetto) {
        $this->grande_progetto = $grande_progetto;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneGrandeProgetto() {
        return $this->descrizione_grande_progetto;
    }

    /**
     * @param mixed $descrizione_grande_progetto
     */
    public function setDescrizioneGrandeProgetto($descrizione_grande_progetto) {
        $this->descrizione_grande_progetto = $descrizione_grande_progetto;
    }

    /**
     * @return mixed
     */
    public function getProgramma() {
        return $this->programma;
    }

    /**
     * @param mixed $cod_programma
     */
    public function setProgramma($cod_programma) {
        $this->programma = $cod_programma;
    }

    public function __toString() {
        return $this->grande_progetto . ' - ' . $this->descrizione_grande_progetto;
    }
}
