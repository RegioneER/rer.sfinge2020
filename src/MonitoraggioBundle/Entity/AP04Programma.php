<?php

/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 07/06/17
 * Time: 12:48
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\AP04ProgrammaRepository")
 * @ORM\Table(name="ap04_programma")
 */
class AP04Programma extends EntityEsportazione {
    use Id;

    const CODICE_TRACCIATO = "AP04";
    const SEPARATORE = "|";

    /**
     * @ORM\ManyToOne(targetEntity="TC4Programma")
     * @ORM\JoinColumn(name="programma_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc4_programma;

    /**
     * @ORM\ManyToOne(targetEntity="TC14SpecificaStato")
     * @ORM\JoinColumn(name="specifica_stato_id", referencedColumnName="id", nullable=true)
     */
    protected $tc14_specifica_stato;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Length(max="60", maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     */
    protected $cod_locale_progetto;

    /**
     * @ORM\Column(type="string", length=1, nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Length(max="1", maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Regex(pattern="(1|2)", match=true, message="Valore flag cancellazione non valido", groups={"esportazione_monitoraggio", "Default"})
     */
    protected $stato;

    /**
     * @return mixed
     */
    public function getTc4Programma() {
        return $this->tc4_programma;
    }

    /**
     * @param mixed $tc4_programma
     * @return AP04Programma
     */
    public function setTc4Programma($tc4_programma) {
        $this->tc4_programma = $tc4_programma;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTc14SpecificaStato() {
        return $this->tc14_specifica_stato;
    }

    /**
     * @param mixed $tc14_specifica_stato
     * @return AP04Programma
     */
    public function setTc14SpecificaStato($tc14_specifica_stato) {
        $this->tc14_specifica_stato = $tc14_specifica_stato;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCodLocaleProgetto() {
        return $this->cod_locale_progetto;
    }

    /**
     * @param mixed $cod_locale_progetto
     * @return AP04Programma
     */
    public function setCodLocaleProgetto($cod_locale_progetto) {
        $this->cod_locale_progetto = $cod_locale_progetto;
        return $this;
    }

    /**
     * @return mixed
     * @return AP04Programma
     */
    public function getStato() {
        return $this->stato;
        return $this;
    }

    /**
     * @param mixed $stato
     * @return AP04Programma
     */
    public function setStato($stato) {
        $this->stato = $stato;
        return $this;
    }

    public function getTracciato() {
        // TODO: Implement getTracciato() method.
        return (\is_null($this->cod_locale_progetto) ? "" : $this->cod_locale_progetto)
            . $this::SEPARATORE . (\is_null($this->tc4_programma) ? "" : $this->tc4_programma->getCodProgramma())
            . $this::SEPARATORE . (\is_null($this->stato) ? "" : $this->stato)
            . $this::SEPARATORE . (\is_null($this->tc14_specifica_stato) ? "" : $this->tc14_specifica_stato->getSpecificaStato());
    }
}
