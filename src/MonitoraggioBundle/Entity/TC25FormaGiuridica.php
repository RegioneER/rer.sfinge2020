<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:23
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC25FormaGiuridicaRepository")
 * @ORM\Table(name="tc25_forma_giuridica")
 */
class TC25FormaGiuridica extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=10, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $forma_giuridica;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     * @Assert\Length(max=1000, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizione_forma_giuridica;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $divisione;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $sezione;

    /**
     * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\FormaGiuridica")
     * @ORM\JoinColumn(name="sfinge_forma_giuridica", referencedColumnName="id")
     */
    protected $sfingeFormaGiuridica;

    /**
     * @return mixed
     */
    public function getFormaGiuridica() {
        return $this->forma_giuridica;
    }

    /**
     * @param mixed $forma_giuridica
     */
    public function setFormaGiuridica($forma_giuridica) {
        $this->forma_giuridica = $forma_giuridica;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneFormaGiuridica() {
        return $this->descrizione_forma_giuridica;
    }

    /**
     * @param mixed $descrizione_forma_giuridica
     */
    public function setDescrizioneFormaGiuridica($descrizione_forma_giuridica) {
        $this->descrizione_forma_giuridica = $descrizione_forma_giuridica;
    }

    /**
     * @return mixed
     */
    public function getDivisione() {
        return $this->divisione;
    }

    /**
     * @param mixed $divisione
     */
    public function setDivisione($divisione) {
        $this->divisione = $divisione;
    }

    /**
     * @return mixed
     */
    public function getSezione() {
        return $this->sezione;
    }

    /**
     * @param mixed $sezione
     */
    public function setSezione($sezione) {
        $this->sezione = $sezione;
    }

    public function __toString() {
        return $this->forma_giuridica . ' - ' . $this->descrizione_forma_giuridica;
    }

    public function getSfingeFormaGiuridica() {
        return $this->sfingeFormaGiuridica;
    }

    public function setSfingeFormaGiuridica($sfingeFormaGiuridica) {
        $this->sfingeFormaGiuridica = $sfingeFormaGiuridica;
    }
}
