<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 05/06/17
 * Time: 15:43
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC7ProgettoComplessoRepository")
 * @ORM\Table(name="tc7_progetto_complesso")
 */
class TC7ProgettoComplesso extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     * @Assert\NotNUll
     * @Assert\Length(max=30, maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $cod_prg_complesso;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     * @Assert\Length(max=500, maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $descrizione_progetto_complesso;

    /**
     * @ORM\Column(type="string", length=4000, nullable=true)
     * @Assert\Length(max=4000, maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $cod_programma;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Assert\Length(max=10, maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $codice_tipo_complessita;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $descrizione_tipo_complessita;

    /**
     * @return mixed
     */
    public function getCodPrgComplesso() {
        return $this->cod_prg_complesso;
    }

    /**
     * @param mixed $cod_prg_complesso
     */
    public function setCodPrgComplesso($cod_prg_complesso) {
        $this->cod_prg_complesso = $cod_prg_complesso;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneProgettoComplesso() {
        return $this->descrizione_progetto_complesso;
    }

    /**
     * @param mixed $descrizione_progetto_complesso
     */
    public function setDescrizioneProgettoComplesso($descrizione_progetto_complesso) {
        $this->descrizione_progetto_complesso = $descrizione_progetto_complesso;
    }

    /**
     * @return mixed
     */
    public function getCodProgramma() {
        return $this->cod_programma;
    }

    /**
     * @param mixed $cod_programma
     */
    public function setCodProgramma($cod_programma) {
        $this->cod_programma = $cod_programma;
    }

    /**
     * @return mixed
     */
    public function getCodiceTipoComplessita() {
        return $this->codice_tipo_complessita;
    }

    /**
     * @param mixed $codice_tipo_complessita
     */
    public function setCodiceTipoComplessita($codice_tipo_complessita) {
        $this->codice_tipo_complessita = $codice_tipo_complessita;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneTipoComplessita() {
        return $this->descrizione_tipo_complessita;
    }

    /**
     * @param mixed $descrizione_tipo_complessita
     */
    public function setDescrizioneTipoComplessita($descrizione_tipo_complessita) {
        $this->descrizione_tipo_complessita = $descrizione_tipo_complessita;
    }

    public function __toString() {
        return $this->cod_prg_complesso . ' - ' . $this->descrizione_progetto_complesso;
    }
}
