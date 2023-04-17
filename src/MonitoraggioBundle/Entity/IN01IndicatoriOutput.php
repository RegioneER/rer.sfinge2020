<?php

/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 07/06/17
 * Time: 15:11
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\IN01IndicatoriOutputRepository")
 * @ORM\Table(name="in01_indicatori_output")
 */
class IN01IndicatoriOutput extends EntityEsportazione {
    use StrutturaCancellabile;
    use Id;
    use HasCodLocaleProgetto;

    const CODICE_TRACCIATO = "IN01";
    const SEPARATORE = "|";

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     * @Assert\Length(max=60, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"esportazione_monitoraggio", "Default"})
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     */
    protected $cod_locale_progetto;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     * @Assert\Length(max=3, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"esportazione_monitoraggio", "Default"})
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     */
    protected $tipo_indicatore_di_output;

    /**
     * @ORM\ManyToOne(targetEntity="TC44_45IndicatoriOutput")
     * @ORM\JoinColumn(name="indicatore_id", referencedColumnName="id")
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     */
    protected $indicatore_id;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\GreaterThan(value=0, groups={"esportazione_monitoraggio", "Default"})
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     */
    protected $val_programmato;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     */
    protected $valore_realizzato;

    /**
     * @param string $tipoIndicatoreDiOutput
     * @return IN01IndicatoriOutput
     */
    public function setTipoIndicatoreDiOutput($tipoIndicatoreDiOutput) {
        $this->tipo_indicatore_di_output = $tipoIndicatoreDiOutput;

        return $this;
    }

    /**
     * @return string
     */
    public function getTipoIndicatoreDiOutput() {
        return $this->tipo_indicatore_di_output;
    }

    /**
     * @param string $valProgrammato
     * @return IN01IndicatoriOutput
     */
    public function setValProgrammato($valProgrammato) {
        $importo_pulito = str_replace(',', '.', $valProgrammato);
        $this->val_programmato = (float) $importo_pulito;

        return $this;
    }

    /**
     * @return string
     */
    public function getValProgrammato() {
        return $this->val_programmato;
    }

    /**
     * @param string $valoreRealizzato
     * @return IN01IndicatoriOutput
     */
    public function setValoreRealizzato($valoreRealizzato) {
        $importo_pulito = str_replace(',', '.', $valoreRealizzato);
        $this->valore_realizzato = (float) $importo_pulito;

        return $this;
    }

    /**
     * @return string
     */
    public function getValoreRealizzato() {
        return $this->valore_realizzato;
    }

    /**
     * @param \MonitoraggioBundle\Entity\TC44_45IndicatoriOutput $indicatoreId
     * @return IN01IndicatoriOutput
     */
    public function setIndicatoreId(\MonitoraggioBundle\Entity\TC44_45IndicatoriOutput $indicatoreId = null) {
        $this->indicatore_id = $indicatoreId;

        return $this;
    }

    /**
     * @return \MonitoraggioBundle\Entity\TC44_45IndicatoriOutput
     */
    public function getIndicatoreId() {
        return $this->indicatore_id;
    }

    public function getTracciato() {
        return (\is_null($this->getCodLocaleProgetto()) ? "" : $this->getCodLocaleProgetto())
            . $this::SEPARATORE . (\is_null($this->getTipoIndicatoreDiOutput()) ? "" : $this->getTipoIndicatoreDiOutput())
            . $this::SEPARATORE . (\is_null($this->getIndicatoreId()) ? "" : $this->getIndicatoreId()->getCodIndicatore())
            . $this::SEPARATORE . (\number_format($this->val_programmato, 2, ',', ''))
            . $this::SEPARATORE . (\number_format($this->valore_realizzato, 2, ',', ''))
            . $this::SEPARATORE . (\is_null($this->getFlgCancellazione()) ? "" : $this->getFlgCancellazione());
    }
}
