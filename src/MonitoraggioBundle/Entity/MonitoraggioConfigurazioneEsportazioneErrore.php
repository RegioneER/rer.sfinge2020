<?php

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\MonitoraggioConfigurazioneEsportazioneErroreRepository")
 * @ORM\Table(name="monitoraggio_configurazione_esportazione_errori")
 */
class MonitoraggioConfigurazioneEsportazioneErrore extends EntityLoggabileCancellabile {
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioConfigurazioneEsportazione", inversedBy="monitoraggio_configurazione_esportazione_errori")
     * @ORM\JoinColumn(name="monitoraggio_configurazione_esportazione_id", referencedColumnName="id")
     */
    protected $monitoraggio_configurazione_esportazione;

    /**
     * @ORM\Column(type="string", length=250, nullable=true)
     */
    protected $errore;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioConfigurazioneEsportazioneTavole", inversedBy="monitoraggio_configurazione_esportazione_errori", cascade={"persist"})
     * @ORM\JoinColumn(name="monitoraggio_configurazione_esportazione_tavole_id", referencedColumnName="id")
     */
    protected $monitoraggio_configurazione_esportazione_tavole;

    /**
     * @ORM\Column(type="integer",  nullable=true)
     */
    protected $codice_errore_igrue;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default" : 0})
     */
    protected $errore_da_igrue = false;

    /**
     * @param MonitoraggioConfigurazioneEsportazioneTavole|null $tavola
     */
    public function __construct(MonitoraggioConfigurazioneEsportazioneTavole $tavola = null) {
        $this->monitoraggio_configurazione_esportazione_tavole = $tavola;
        if (\is_null($tavola)) {
            return;
        }
        $this->monitoraggio_configurazione_esportazione = $tavola->getMonitoraggioConfigurazioneEsportazione();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set errore
     *
     * @param string $errore
     * @return MonitoraggioConfigurazioneEsportazioneErrore
     */
    public function setErrore($errore) {
        $this->errore = $errore;

        return $this;
    }

    /**
     * Get errore
     *
     * @return string
     */
    public function getErrore() {
        return $this->errore;
    }

    /**
     * Set monitoraggio_configurazione_esportazione
     *
     * @param \MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazione $monitoraggioConfigurazioneEsportazione
     * @return MonitoraggioConfigurazioneEsportazioneErrore
     */
    public function setMonitoraggioConfigurazioneEsportazione(\MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazione $monitoraggioConfigurazioneEsportazione = null) {
        $this->monitoraggio_configurazione_esportazione = $monitoraggioConfigurazioneEsportazione;

        return $this;
    }

    /**
     * Get monitoraggio_configurazione_esportazione
     *
     * @return \MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazione
     */
    public function getMonitoraggioConfigurazioneEsportazione() {
        return $this->monitoraggio_configurazione_esportazione;
    }

    /**
     * Set monitoraggio_configurazione_esportazione_tavole
     *
     * @param \MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneTavole $monitoraggioConfigurazioneEsportazioneTavole
     * @return MonitoraggioConfigurazioneEsportazioneErrore
     */
    public function setMonitoraggioConfigurazioneEsportazioneTavole(\MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneTavole $monitoraggioConfigurazioneEsportazioneTavole = null) {
        $this->monitoraggio_configurazione_esportazione_tavole = $monitoraggioConfigurazioneEsportazioneTavole;

        return $this;
    }

    /**
     * Get monitoraggio_configurazione_esportazione_tavole
     *
     * @return \MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneTavole
     */
    public function getMonitoraggioConfigurazioneEsportazioneTavole() {
        return $this->monitoraggio_configurazione_esportazione_tavole;
    }

    /**
     * Set codice_errore_igrue
     *
     * @param int $codiceErroreIgrue
     * @return MonitoraggioConfigurazioneEsportazioneErrore
     */
    public function setCodiceErroreIgrue($codiceErroreIgrue) {
        $this->codice_errore_igrue = $codiceErroreIgrue;

        return $this;
    }

    /**
     * Get codice_errore_igrue
     *
     * @return int
     */
    public function getCodiceErroreIgrue() {
        return $this->codice_errore_igrue;
    }

    /**
     * Set errore_da_igrue
     *
     * @param bool $erroreDaIgrue
     * @return MonitoraggioConfigurazioneEsportazioneErrore
     */
    public function setErroreDaIgrue($erroreDaIgrue) {
        $this->errore_da_igrue = $erroreDaIgrue;

        return $this;
    }

    /**
     * Get errore_da_igrue
     *
     * @return bool
     */
    public function getErroreDaIgrue() {
        return $this->errore_da_igrue;
    }
}
