<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 05/06/17
 * Time: 15:34
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity
 * @ORM\Table(name="tc4_programma")
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC4ProgrammaRepository")
 */
class TC4Programma extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=50, maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $cod_programma;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $descrizione_programma;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Assert\Length(max=100, maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $fondo;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Assert\Length(max=10, maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $codice_tipologia_programma;

    /**
     * @var TC12Classificazione
     * @ORM\OneToMany(targetEntity="TC12Classificazione", mappedBy="programma")
     */
    protected $classificazioni;

    /**
     * @var AP04Programma
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\RichiestaProgramma", mappedBy="tc4_programma")
     */
    protected $richieste_programmi;

    /**
     * @var \SfingeBundle\Entity\ProgrammaProcedura
     * @ORM\OneToMany(targetEntity="SfingeBundle\Entity\ProgrammaProcedura", mappedBy="tc4_programma")
     */
    protected $procedure;

    public function __construct() {
        $this->classificazioni = new ArrayCollection();
        $this->richieste_programmi = new ArrayCollection();
        $this->procedure = new ArrayCollection();
    }

    public function getCodProgramma() {
        return $this->cod_programma;
    }

    public function setCodProgramma($cod_programma) {
        $this->cod_programma = $cod_programma;
    }

    public function getDescrizioneProgramma() {
        return $this->descrizione_programma;
    }

    public function setDescrizioneProgramma($descrizione_programma) {
        $this->descrizione_programma = $descrizione_programma;
    }

    public function getFondo() {
        return $this->fondo;
    }

    public function setFondo($fondo) {
        $this->fondo = $fondo;
    }

    public function getCodiceTipologiaProgramma() {
        return $this->codice_tipologia_programma;
    }

    public function setCodiceTipologiaProgramma($codice_tipologia_programma) {
        $this->codice_tipologia_programma = $codice_tipologia_programma;
    }

    public function __toString() {
        return $this->getCodProgramma() . ' - ' . $this->getDescrizioneProgramma();
    }

    public function getRichiesteProgrammi() {
        return $this->richieste_programmi;
    }

    public function getProcedure() {
        return $this->procedure;
    }

    public function setRichiesteProgrammi(ArrayCollection $richieste) {
        $this->richieste_programmi = $richieste;
    }

    public function setProcedure(ArrayCollection $procedure) {
        $this->procedure = $procedure;
    }
}
