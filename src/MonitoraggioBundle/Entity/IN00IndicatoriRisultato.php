<?php

/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 07/06/17
 * Time: 15:09
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\IN00IndicatoriRisultatoRepository")
 * @ORM\Table(name="in00_indicatori_risultato")
 */
class IN00IndicatoriRisultato extends EntityEsportazione {
    use StrutturaCancellabile;
    use Id;
    use HasCodLocaleProgetto;

    const CODICE_TRACCIATO = "IN00";
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
    protected $tipo_indicatore_di_risultato;

    /**
     * @ORM\ManyToOne(targetEntity="TC42_43IndicatoriRisultato")
     * @ORM\JoinColumn(name="indicatore_id", referencedColumnName="id")
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     */
    protected $indicatore_id;

    /**
     * @param string $tipoIndicatoreDiRisultato
     * @return IN00IndicatoriRisultato
     */
    public function setTipoIndicatoreDiRisultato($tipoIndicatoreDiRisultato) {
        $this->tipo_indicatore_di_risultato = $tipoIndicatoreDiRisultato;

        return $this;
    }

    /**
     * @return string
     */
    public function getTipoIndicatoreDiRisultato() {
        return $this->tipo_indicatore_di_risultato;
    }

    /**
     * @param \MonitoraggioBundle\Entity\TC42_43IndicatoriRisultato $indicatoreId
     * @return IN00IndicatoriRisultato
     */
    public function setIndicatoreId(\MonitoraggioBundle\Entity\TC42_43IndicatoriRisultato $indicatoreId = null) {
        $this->indicatore_id = $indicatoreId;

        return $this;
    }

    /**
     * @return \MonitoraggioBundle\Entity\TC42_43IndicatoriRisultato
     */
    public function getIndicatoreId() {
        return $this->indicatore_id;
    }

    public function getTracciato() {
        // TODO: Implement getTracciato() method.
        return (\is_null($this->getCodLocaleProgetto()) ? "" : $this->getCodLocaleProgetto())
            . $this::SEPARATORE .
            (\is_null($this->getTipoIndicatoreDiRisultato()) ? "" : $this->getTipoIndicatoreDiRisultato())
            . $this::SEPARATORE .
            (\is_null($this->getIndicatoreId()) ? "" : $this->getIndicatoreId()->getCodIndicatore())
            . $this::SEPARATORE .
            (\is_null($this->getFlgCancellazione()) ? "" : $this->getFlgCancellazione());
    }
}
