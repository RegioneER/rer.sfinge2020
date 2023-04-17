<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 11/10/17
 * Time: 14:25
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use SfingeBundle\Entity\ObiettivoSpecifico;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\IndicatoriRisultatoObiettivoSpecificoRepository")
 * @ORM\Table(name="indicatori_risultato_obiettivo_specifico")
 */
class IndicatoriRisultatoObiettivoSpecifico extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\ManyToOne(targetEntity="TC42_43IndicatoriRisultato", inversedBy="mappingObiettivoSpecifico")
     * @ORM\JoinColumn(name="indicatore_risultato_id", referencedColumnName="id", nullable=true)
     * @var TC42_43IndicatoriRisultato
     */
    protected $indicatoreRisultato;

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\ObiettivoSpecifico", inversedBy="associazioni_indicatori_risultato")
     * @ORM\JoinColumn(name="obiettivospecifico_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull
     * @var ObiettivoSpecifico
     */
    protected $obiettivoSpecifico;

    public function __construct(?ObiettivoSpecifico $obiettivo = null, ?TC42_43IndicatoriRisultato $indicatore = null) {
        $this->obiettivoSpecifico = $obiettivo;
        $this->indicatoreRisultato = $indicatore;
    }

    /**
     * @return TC42_43IndicatoriRisultato
     */
    public function getIndicatoreRisultato() {
        return $this->indicatoreRisultato;
    }

    /**
     * @param TC42_43IndicatoriRisultato $indicatoreRisultato
     * @return self
     */
    public function setIndicatoreRisultato($indicatoreRisultato) {
        $this->indicatoreRisultato = $indicatoreRisultato;
        return $this;
    }

    /**
     * @return ObiettivoSpecifico
     */
    public function getObiettivoSpecifico() {
        return $this->obiettivoSpecifico;
    }

    /**
     * @param ObiettivoSpecifico $obiettivoSpecifico
     */
    public function setObiettivoSpecifico($obiettivoSpecifico) {
        $this->obiettivoSpecifico = $obiettivoSpecifico;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString() {
        return (string) $this->indicatoreRisultato;
    }
}
