<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:14
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC18ContenutoFormativoRepository")
 * @ORM\Table(name="tc18_contenuto_formativo")
 */
class TC18ContenutoFormativo extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=10, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $cod_contenuto_formativo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizione_contenuto_formativo;

    /**
     * @ORM\Column(type="string", length=4, nullable=true)
     * @Assert\Length(max=4, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $codice_settore;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizione_settore;

    /**
     * @return mixed
     */
    public function getCodContenutoFormativo() {
        return $this->cod_contenuto_formativo;
    }

    /**
     * @param mixed $cod_contenuto_formativo
     */
    public function setCodContenutoFormativo($cod_contenuto_formativo) {
        $this->cod_contenuto_formativo = $cod_contenuto_formativo;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneContenutoFormativo() {
        return $this->descrizione_contenuto_formativo;
    }

    /**
     * @param mixed $descrizione_contenuto_formativo
     */
    public function setDescrizioneContenutoFormativo($descrizione_contenuto_formativo) {
        $this->descrizione_contenuto_formativo = $descrizione_contenuto_formativo;
    }

    /**
     * @return mixed
     */
    public function getCodiceSettore() {
        return $this->codice_settore;
    }

    /**
     * @param mixed $codice_settore
     */
    public function setCodiceSettore($codice_settore) {
        $this->codice_settore = $codice_settore;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneSettore() {
        return $this->descrizione_settore;
    }

    /**
     * @param mixed $descrizione_settore
     */
    public function setDescrizioneSettore($descrizione_settore) {
        $this->descrizione_settore = $descrizione_settore;
    }
}
