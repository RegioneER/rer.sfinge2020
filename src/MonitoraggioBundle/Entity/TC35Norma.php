<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:36
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC35NormaRepository")
 * @ORM\Table(name="tc35_norma")
 */
class TC35Norma extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=5, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $cod_norma;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Assert\Length(max=5, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $tipo_norma;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     * @Assert\Length(max=500, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizione_norma;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Assert\Length(max=5, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $numero_norma;

    /**
     * @ORM\Column(type="string", length=4, nullable=true)
     * @Assert\Length(max=4, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $anno_norma;

    /**
     * @return mixed
     */
    public function getCodNorma() {
        return $this->cod_norma;
    }

    /**
     * @param mixed $cod_norma
     */
    public function setCodNorma($cod_norma) {
        $this->cod_norma = $cod_norma;
    }

    /**
     * @return mixed
     */
    public function getTipoNorma() {
        return $this->tipo_norma;
    }

    /**
     * @param mixed $tipo_norma
     */
    public function setTipoNorma($tipo_norma) {
        $this->tipo_norma = $tipo_norma;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneNorma() {
        return $this->descrizione_norma;
    }

    /**
     * @param mixed $descrizione_norma
     */
    public function setDescrizioneNorma($descrizione_norma) {
        $this->descrizione_norma = $descrizione_norma;
    }

    /**
     * @return mixed
     */
    public function getNumeroNorma() {
        return $this->numero_norma;
    }

    /**
     * @param mixed $numero_norma
     */
    public function setNumeroNorma($numero_norma) {
        $this->numero_norma = $numero_norma;
    }

    /**
     * @return mixed
     */
    public function getAnnoNorma() {
        return $this->anno_norma;
    }

    /**
     * @param mixed $anno_norma
     */
    public function setAnnoNorma($anno_norma) {
        $this->anno_norma = $anno_norma;
    }

    public function __toString() {
        return $this->descrizione_norma . ' N° ' . $this->numero_norma . '/' . $this->anno_norma;
    }
}
