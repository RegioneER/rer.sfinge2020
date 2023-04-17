<?php

/**
 * @author lfontana
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\MonitoraggioEsportazioneRepository")
 * @ORM\Table(name="monitoraggio_esportazioni")
 */
class MonitoraggioEsportazione extends EntityLoggabileCancellabile {
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="MonitoraggioConfigurazioneEsportazione", mappedBy="monitoraggio_esportazione", cascade={"persist", "remove"})
     */
    protected $monitoraggio_configurazione;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Date
     */
    protected $data_inizio;

    /**
     * @ORM\OneToMany(targetEntity="MonitoraggioEsportazioneLogFase", mappedBy="monitoraggio_esportazione", cascade={"persist"})
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $fasi;

    /**
     * @ORM\ManyToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile", cascade={"persist"})
     * @ORM\JoinColumn(name="documento_to_igrue_id", referencedColumnName="id", nullable=true)
     *
     * @var \DocumentoBundle\Entity\DocumentoFile
     */
    protected $documento_to_igrue;

    /**
     * @ORM\ManyToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile", cascade={"persist"})
     * @ORM\JoinColumn(name="documento_from_igrue_id", referencedColumnName="id", nullable=true)
     *
     * @var \DocumentoBundle\Entity\DocumentoFile
     */
    protected $documento_from_igrue;

    /**
     * @ORM\Column(type="bigint", name="inviati_ad_igrue", nullable=true)
     */
    protected $inviati_ad_igrue;

    /**
     * @ORM\Column(type="bigint", name="scartati_da_igrue", nullable=true)
     */
    protected $scartati_da_igrue;

    public function __construct() {
        $this->fasi = new ArrayCollection();
        $this->monitoraggio_configurazione = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set data_inizio.
     *
     * @param \DateTime $dataInizio
     *
     * @return MonitoraggioEsportazione
     */
    public function setDataInizio(\DateTime $dataInizio = null) {
        $this->data_inizio = $dataInizio;

        return $this;
    }

    /**
     * Get data_inizio.
     *
     * @return \DateTime|null
     */
    public function getDataInizio() {
        return $this->data_inizio;
    }

    public function getFasi() {
        return $this->fasi;
    }

    public function setFasi(\Doctrine\Common\Collections\ArrayCollection $fasi) {
        $this->fasi = $fasi;
    }

    /**
     * Add fasi.
     *
     * @param \MonitoraggioBundle\Entity\MonitoraggioEsportazioneLogFase $fasi
     *
     * @return MonitoraggioEsportazione
     */
    public function addFasi(\MonitoraggioBundle\Entity\MonitoraggioEsportazioneLogFase $fasi) {
        $this->fasi[] = $fasi;

        return $this;
    }

    /**
     * Remove fasi.
     *
     * @param \MonitoraggioBundle\Entity\MonitoraggioEsportazioneLogFase $fasi
     */
    public function removeFasi(\MonitoraggioBundle\Entity\MonitoraggioEsportazioneLogFase $fasi) {
        $this->fasi->removeElement($fasi);
    }

    /**
     * @return MonitoraggioEsportazioneLogFase
     */
    public function getLastFase() {
        return $this->fasi->last();
    }

    public function updateFase(\MonitoraggioBundle\Entity\MonitoraggioEsportazioneLogFase $fase) {
        foreach ($this->fasi as $key => $value) {
            if ($value->getId() == $fase->getId()) {
                $this->fasi[$key] = $fase;
            }
        }

        return true;
    }

    /**
     * Set monitoraggio_configurazione.
     *
     * @param \Doctrine\Common\Collections\Collection $configurazione
     *
     * @return MonitoraggioEsportazione
     */
    public function setMonitoraggioConfigurazione(\Doctrine\Common\Collections\Collection $configurazione) {
        $this->monitoraggio_configurazione = $configurazione;

        return $this;
    }

    /**
     * Add monitoraggio_configurazione.
     *
     * @param \MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazione $monitoraggioConfigurazione
     *
     * @return MonitoraggioEsportazione
     */
    public function addMonitoraggioConfigurazione(\MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazione $monitoragggioConfigurazione) {
        $this->monitoraggio_configurazione[] = $monitoragggioConfigurazione;
    }

    /**
     * Remove monitoraggio_configurazione.
     *
     * @param \MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazione $monitoraggioConfigurazione
     */
    public function removeMonitoraggioConfigurazione(\MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazione $monitoragggioConfigurazione) {
        $this->monitoraggio_configurazione->removeElement($monitoragggioConfigurazione);
    }

    /**
     * Get monitoraggio_configurazione.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMonitoraggioConfigurazione() {
        return $this->monitoraggio_configurazione;
    }

    public function getFaseInviato() {
        foreach ($this->getFasi() as $key => $value) {
            if (MonitoraggioEsportazioneLogFase::STATO_INVIATO == $value->getFase()) {
                return $value;
            }
        }

        return false;
    }

    public function getFaseCompletato() {
        foreach ($this->getFasi() as $key => $value) {
            if (MonitoraggioEsportazioneLogFase::STATO_COMPLETATO == $value->getFase()) {
                return $value;
            }
        }

        return false;
    }

    /**
     * Set inviati_ad_igrue
     *
     * @param int $inviatiAdIgrue
     * @return MonitoraggioEsportazione
     */
    public function setInviatiAdIgrue($inviatiAdIgrue) {
        $this->inviati_ad_igrue = $inviatiAdIgrue;

        return $this;
    }

    /**
     * Get inviati_ad_igrue
     *
     * @return int
     */
    public function getInviatiAdIgrue() {
        return $this->inviati_ad_igrue;
    }

    /**
     * Set scartati_da_igrue
     *
     * @param int $scartatiDaIgrue
     * @return MonitoraggioEsportazione
     */
    public function setScartatiDaIgrue($scartatiDaIgrue) {
        $this->scartati_da_igrue = $scartatiDaIgrue;

        return $this;
    }

    /**
     * Get scartati_da_igrue
     *
     * @return int
     */
    public function getScartatiDaIgrue() {
        return $this->scartati_da_igrue;
    }

    /**
     * Set documento_to_igrue
     *
     * @param \DocumentoBundle\Entity\DocumentoFile $documentoToIgrue
     * @return MonitoraggioEsportazione
     */
    public function setDocumentoToIgrue(\DocumentoBundle\Entity\DocumentoFile $documentoToIgrue = null) {
        $this->documento_to_igrue = $documentoToIgrue;

        return $this;
    }

    /**
     * Get documento_to_igrue
     *
     * @return \DocumentoBundle\Entity\DocumentoFile
     */
    public function getDocumentoToIgrue() {
        return $this->documento_to_igrue;
    }

    /**
     * Set documento_from_igrue
     *
     * @param \DocumentoBundle\Entity\DocumentoFile $documentoFromIgrue
     * @return MonitoraggioEsportazione
     */
    public function setDocumentoFromIgrue(\DocumentoBundle\Entity\DocumentoFile $documentoFromIgrue = null) {
        $this->documento_from_igrue = $documentoFromIgrue;

        return $this;
    }

    /**
     * Get documento_from_igrue
     *
     * @return \DocumentoBundle\Entity\DocumentoFile
     */
    public function getDocumentoFromIgrue() {
        return $this->documento_from_igrue;
    }
}
