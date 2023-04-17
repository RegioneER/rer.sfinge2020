<?php
/**
* @author lfontana
*/

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class MonitoraggioConfigurazioneEsportazioneTrasferimento extends MonitoraggioConfigurazioneEsportazione {
    public static $TAVOLE = [
        'TR00',
    ];

    public function __construct($trasferimento = null, $monitoraggio_esportazione = null, $TR00 = false) {
        parent::__construct($monitoraggio_esportazione);
        $this->trasferimento = $trasferimento;

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
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\Trasferimento")
     * @ORM\JoinColumn(name="trasferimento_id", referencedColumnName="id", nullable=true)
     * @var Trasferimento
     */
    protected $trasferimento;

    /**
     * @return Trasferimento
     */
    public function getTrasferimento() {
        return $this->trasferimento;
    }

    /**
     * @param Trasferimento $trasferimento
     * @return self
     */
    public function setTrasferimento(Trasferimento $trasferimento) {
        $this->trasferimento = $trasferimento;
        return $this;
    }

    public function inizializzaTavole() {
        foreach (self::$TAVOLE as $struttura => $value) {
            $tavola = new MonitoraggioConfigurazioneEsportazioneTavole();
            $tavola->setTavolaProtocollo($struttura);
            $tavola->setFlagEsportazione(true);
            $tavola->setMonitoraggioConfigurazioneEsportazione($this);
            $this->addMonitoraggioConfigurazioneEsportazioneTavole($tavola);
        }
    }

    /**
     * @return Trasferimento|null
     */
    public function getElemento() {
        return $this->trasferimento;
    }

    /**
     * @param Trasferimento|null $procedura
     * @param mixed $trasferimento
     * @return self
     */
    public function setElemento($trasferimento) {
        $this->trasferimento = $trasferimento;
        return $this;
    }
}
