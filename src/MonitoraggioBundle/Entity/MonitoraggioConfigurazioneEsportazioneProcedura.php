<?php

/**
 * @author lfontana
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use SfingeBundle\Entity\Procedura;

/**
 * @ORM\Entity
 */
class MonitoraggioConfigurazioneEsportazioneProcedura extends MonitoraggioConfigurazioneEsportazione {
    public static $TAVOLE = [
        'PA00',
        'PA01',
    ];

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Procedura")
     * @ORM\JoinColumn(name="procedura_id", referencedColumnName="id", nullable=true)
     * @var Procedura
     */
    protected $procedura;

    public function __construct($procedura = null, $monitoraggio_esportazione = null, $PA00 = false, $PA01 = false) {
        parent::__construct($monitoraggio_esportazione);
        $this->procedura = $procedura;

        foreach (self::$TAVOLE as $struttura) {
            $tavola = new MonitoraggioConfigurazioneEsportazioneTavole();
            $tavola->setTavolaProtocollo($struttura);
            if ($$struttura) {
                $tavola->setFlagEsportazione(true);
            }
            $tavola->setMonitoraggioConfigurazioneEsportazione($this);
            $this->addMonitoraggioConfigurazioneEsportazioneTavole($tavola);
        }
    }

    /**
     * @return Procedura
     */
    public function getProcedura() {
        return $this->procedura;
    }

    /**
     * @param Procedura $procedura
     * @return self
     */
    public function setProcedura(Procedura $procedura) {
        $this->procedura = $procedura;
        return $this;
    }

    /**
     * @return Procedura|null
     */
    public function getElemento() {
        return $this->procedura;
    }

    /**
     * @param Procedura $procedura
     * @return self
     */
    public function setElemento($procedura) {
        $this->procedura = $procedura;
        return $this;
    }
}
