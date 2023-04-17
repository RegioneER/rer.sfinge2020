<?php

/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 07/06/17
 * Time: 12:47
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\AP03ClassificazioniRepository")
 * @ORM\Table(name="ap03_classificazioni")
 */
class AP03Classificazioni extends EntityEsportazione {
    use StrutturaCancellabile;
    use Id;

    const CODICE_TRACCIATO = "AP03";
    const SEPARATORE = "|";

    /**
     * @ORM\ManyToOne(targetEntity="TC4Programma")
     * @ORM\JoinColumn(name="programma_id", referencedColumnName="id", nullable=false)
     */
    protected $tc4_programma;

    /**
     * @ORM\ManyToOne(targetEntity="TC11TipoClassificazione")
     * @ORM\JoinColumn(name="tipo_class_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc11_tipo_classificazione;

    /**
     * @ORM\Column(type="string", length=60, nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Length(max="60", maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     */
    protected $cod_locale_progetto;

    /**
     * @ORM\ManyToOne(targetEntity="TC12Classificazione")
     * @ORM\JoinColumn(name="classificazione_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $classificazione;

    /**
     * @return mixed
     */
    public function getTc4Programma() {
        return $this->tc4_programma;
    }

    /**
     * @param mixed $tc4_programma
     * @return AP03Classificazioni
     */
    public function setTc4Programma($tc4_programma) {
        $this->tc4_programma = $tc4_programma;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTc11TipoClassificazione() {
        return $this->tc11_tipo_classificazione;
    }

    /**
     * @param mixed $tc11_tipo_classificazione
     */
    public function setTc11TipoClassificazione($tc11_tipo_classificazione) {
        $this->tc11_tipo_classificazione = $tc11_tipo_classificazione;
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
     * @return AP03Classificazioni
     */
    public function setCodLocaleProgetto($cod_locale_progetto) {
        $this->cod_locale_progetto = $cod_locale_progetto;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getClassificazione() {
        return $this->classificazione;
    }

    /**
     * @param mixed $cod_classificazione
     * @return AP03Classificazioni
     */
    public function setClassificazione($cod_classificazione) {
        $this->classificazione = $cod_classificazione;
        return $this;
    }

    public function getTracciato() {
        // TODO: Implement getTracciato() method.

        return (\is_null($this->getCodLocaleProgetto()) ? "" : $this->getCodLocaleProgetto())
            . $this::SEPARATORE . (\is_null($this->getTc4Programma()->getCodProgramma()) ? "" : $this->getTc4Programma()->getCodProgramma())
            . $this::SEPARATORE . (\is_null($this->getTc11TipoClassificazione()) ? "" : $this->getTc11TipoClassificazione()->getTipoClass())
            . $this::SEPARATORE . (\is_null($this->classificazione) ? "" : $this->classificazione->getCodice())
            . $this::SEPARATORE . (\is_null($this->getFlgCancellazione()) ? "" : $this->getFlgCancellazione());
    }
}
