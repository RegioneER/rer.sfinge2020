<?php

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity
 * @ORM\Table(name="monitoraggio_configurazione_esportazioni")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="tipo", type="string")
 * @ORM\DiscriminatorMap({
 *     "RICHIESTA" : "MonitoraggioConfigurazioneEsportazioneRichiesta",
 *     "GENERICO" : "MonitoraggioConfigurazioneEsportazione",
 *     "PROCEDURA" : "MonitoraggioConfigurazioneEsportazioneProcedura",
 *     "TRASFERIMENTO" : "MonitoraggioConfigurazioneEsportazioneTrasferimento",
 * })
 */
class MonitoraggioConfigurazioneEsportazione extends EntityLoggabileCancellabile {
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioEsportazione", inversedBy="monitoraggio_configurazione", cascade={"persist"})
     * @ORM\JoinColumn(name="monitoraggio_esportazione_id", referencedColumnName="id")
     */
    protected $monitoraggio_esportazione;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default" : "0"})
     */
    protected $flag_errore = false;

    /**
     * @ORM\OneToMany(targetEntity="MonitoraggioConfigurazioneEsportazioneTavole", mappedBy="monitoraggio_configurazione_esportazione", cascade={"persist", "remove"})
     * @var Collection
     */
    protected $monitoraggio_configurazione_esportazione_tavole;

    /**
     * @ORM\OneToMany(targetEntity="MonitoraggioConfigurazioneEsportazioneErrore", mappedBy="monitoraggio_configurazione_esportazione", cascade={"persist"})
     * @var Collection
     */
    protected $monitoraggio_configurazione_esportazione_errori;

    public function __construct($monitoraggio_esportazione = null) {
        $this->monitoraggio_configurazione_esportazione_errori = new ArrayCollection();
        $this->monitoraggio_configurazione_esportazione_tavole = new ArrayCollection();
        $this->monitoraggio_esportazione = $monitoraggio_esportazione;
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
     * @param bool $errore
     */
    public function setFlagErrore($errore): self {
        $this->flag_errore = $errore;

        return $this;
    }

    /**
     * @return bool
     */
    public function getFlagErrore() {
        return $this->flag_errore;
    }

    /**
     * @return MonitoraggioConfigurazioneEsportazione
     */
    public function setMonitoraggioEsportazione(?MonitoraggioEsportazione $monitoraggioEsportazione = null): self {
        $this->monitoraggio_esportazione = $monitoraggioEsportazione;

        return $this;
    }

    public function getMonitoraggioEsportazione(): ?MonitoraggioEsportazione {
        return $this->monitoraggio_esportazione;
    }

    public function addmonitoraggioConfigurazioneEsportazioneErrori(MonitoraggioConfigurazioneEsportazioneErrore $monitoraggioConfigurazioneEsportazioneErrori): self {
        $this->monitoraggio_configurazione_esportazione_errori[] = $monitoraggioConfigurazioneEsportazioneErrori;

        return $this;
    }

    public function addMonitoraggioConfigurazioneEsportazioneTavole(MonitoraggioConfigurazioneEsportazioneTavole $monitoraggioConfigurazioneEsportazioneTavole): self {
        $this->monitoraggio_configurazione_esportazione_tavole[] = $monitoraggioConfigurazioneEsportazioneTavole;

        return $this;
    }

    public function removeMonitoraggioConfigurazioneEsportazioneTavole(MonitoraggioConfigurazioneEsportazioneTavole $monitoraggioConfigurazioneEsportazioneTavole): void {
        $this->monitoraggio_configurazione_esportazione_tavole->removeElement($monitoraggioConfigurazioneEsportazioneTavole);
    }

    public function getMonitoraggioConfigurazioneEsportazioneTavole(): Collection {
        return $this->monitoraggio_configurazione_esportazione_tavole;
    }

    public function removemonitoraggioConfigurazioneEsportazioneErrori(MonitoraggioConfigurazioneEsportazioneErrore $monitoraggioConfigurazioneEsportazioneErrori): void {
        $this->monitoraggio_configurazione_esportazione_errori->removeElement($monitoraggioConfigurazioneEsportazioneErrori);
    }

    public function getmonitoraggioConfigurazioneEsportazioneErrori(): Collection {
        return $this->monitoraggio_configurazione_esportazione_errori;
    }

    public function getElemento() {
        return null;
    }
}
