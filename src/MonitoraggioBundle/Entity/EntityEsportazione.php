<?php

/**
 * @author lfontana, vbuscemi
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
class EntityEsportazione extends EntityLoggabileCancellabile {
    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioConfigurazioneEsportazioneTavole")
     * @ORM\JoinColumn(name="monitoraggio_configurazione_esportazioni_tavola_id", referencedColumnName="id", nullable=false)
     */
    protected $monitoraggio_configurazione_esportazioni_tavola;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default" : 0})
     */
    protected $flag_errore_igrue = false;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    protected $progressivo_puc;

    public function setEsportazioneStrutture(?MonitoraggioConfigurazioneEsportazioneTavole $esportazioneStrutture = null): self {
        $this->monitoraggio_configurazione_esportazioni_tavola = $esportazioneStrutture;

        return $this;
    }

    /**
     * @return MonitoraggioEsportazione
     */
    public function getEsportazioneStrutture() {
        return $this->monitoraggio_configurazione_esportazioni_tavola;
    }

    /**
     * @return MonitoraggioConfigurazioneEsportazioneTavole
     */
    public function getMonitoraggioConfigurazioneEsportazioniTavola() {
        return $this->monitoraggio_configurazione_esportazioni_tavola;
    }

    public function setMonitoraggioConfigurazioneEsportazioniTavola(MonitoraggioConfigurazioneEsportazioneTavole $monitoraggioConfigurazioneEsportazioniTavola): self {
        $this->monitoraggio_configurazione_esportazioni_tavola = $monitoraggioConfigurazioneEsportazioniTavola;

        return $this;
    }

    /**
     * @param bool $flagErroreIgrue
     * @return FN03PianoCosti
     */
    public function setFlagErroreIgrue($flagErroreIgrue) {
        $this->flag_errore_igrue = $flagErroreIgrue;

        return $this;
    }

    /**
     * @return bool
     */
    public function getFlagErroreIgrue() {
        return $this->flag_errore_igrue;
    }

    /**
     * @param int $progresivoPuc
     * @param mixed $progressivoPuc
     */
    public function setProgressivoPuc($progressivoPuc): self {
        $this->progressivo_puc = $progressivoPuc;

        return $this;
    }

    /**
     * @return int
     */
    public function getProgressivoPuc() {
        return $this->progressivo_puc;
    }
}
