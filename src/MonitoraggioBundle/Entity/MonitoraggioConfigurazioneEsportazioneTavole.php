<?php

/**
 * @author lfontana
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Symfony\Component\Validator\Constraints as Assert;
use MonitoraggioBundle\Validator\Constraints as MonitoraggioAssert;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\MonitoraggioConfigurazioneEsportazioneTavoleRepository")
 * @ORM\Table(name="monitoraggio_configurazione_esportazioni_tavole")
 * @MonitoraggioAssert\AP03_001(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\AP05_002(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\AP06_003(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\SC00_004(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN00_005(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN01_006(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\IN00_007(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\IN01_008(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\PR00_009(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\PR01_010(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN03_011(groups={"monitoraggioCrossControls" })
 * MonitoraggioAssert\AP00_012( groups={"monitoraggioCrossControls" } )
 * @MonitoraggioAssert\AP01_013(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\AP03_014(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\AP03_015(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\AP03_016(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\AP03_017(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN02_AP00_018(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN04_FN06_019(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN01_AP04_020(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN05_AP04_021(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN07_AP04_022(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN09_AP04_023(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN00_FN01_FN10_024(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN04_FN05_025(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN06_FN07_026(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN00_FN04_FN10_027(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN04_FN06_028(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN01_FN05_029(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN05_FN07_030(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\TR00_031(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN04_032(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN06_033(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN05_034(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN07_035(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\PR01_036(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN06_FN08_037(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN00_FN10_038(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN00_FN03_FN10_039(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\PR00_IN01_040(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\PR00_041(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\PR00_042(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\AP04_IN00_043(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\AP04_IN01_044(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN02_AP00_045(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\PR00_AP00_046(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN04_FN05_047(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN06_FN07_048(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN06_FN08_049(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN04_050(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN05_051(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN06_052(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN07_053(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN04_FN05_054(groups={"monitoraggioCrossControls" })
 * @MonitoraggioAssert\FN06_FN07_055(groups={"monitoraggioCrossControls" })
 */
class MonitoraggioConfigurazioneEsportazioneTavole extends EntityLoggabileCancellabile {
    public static $SORT_ORDER = [
        //Procedure
        'PA00' => 0,
        'PA01' => 1,

        //Trasferimenti
        'TR00' => 0,

        //Richieste
        'AP00' => 0,
        'AP01' => 1,
        'AP02' => 2,
        'AP03' => 3,
        'AP04' => 4,
        'AP05' => 5,
        'AP06' => 6,
        'FN00' => 7,
        'FN01' => 8,
        'FN02' => 9,
        'FN03' => 10,
        'FN04' => 11,
        'FN05' => 12,
        'FN06' => 13,
        'FN07' => 14,
        'FN08' => 15,
        'FN09' => 16,
        'FN10' => 17,
        'SC00' => 18,
        'PG00' => 19,
        'PR00' => 20,
        'PR01' => 21,
        'IN00' => 22,
        'IN01' => 23,
    ];

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazione", inversedBy="monitoraggio_configurazione_esportazione_tavole", cascade={"persist"})
     * @ORM\JoinColumn(name="monitoraggio_configurazione_esportazione_id", referencedColumnName="id", nullable=false)
     * @var MonitoraggioConfigurazioneEsportazione|null
     */
    protected $monitoraggio_configurazione_esportazione;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default" : "0"})
     */
    protected $flag_esportazione = false;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default" : "0"})
     */
    protected $flag_errore = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\Date
     */
    protected $data_conferma_esportazione;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $flag_conferma_esportazione;

    /**
     * @ORM\OneToMany(targetEntity="MonitoraggioConfigurazioneEsportazioneErrore", mappedBy="monitoraggio_configurazione_esportazione_tavole")
     */
    protected $monitoraggio_configurazione_esportazione_errori;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    protected $tavola_protocollo;

    /**
     * @param MonitoraggioConfigurazioneEsportazione|null $configurazione
     */
    public function __construct(MonitoraggioConfigurazioneEsportazione $configurazione = null) {
        $this->monitoraggio_configurazione_esportazione_errori = new ArrayCollection();
        $this->monitoraggio_configurazione_esportazione = $configurazione;
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
     * Set flag_esportazione
     *
     * @param bool $flagEsportazione
     * @return MonitoraggioConfigurazioneEsportazioneTavole
     */
    public function setFlagEsportazione($flagEsportazione) {
        $this->flag_esportazione = $flagEsportazione;

        return $this;
    }

    /**
     * Get flag_esportazione
     *
     * @return bool
     */
    public function getFlagEsportazione() {
        return $this->flag_esportazione;
    }

    /**
     * Set flag_errore
     *
     * @param bool $flagErrore
     * @return MonitoraggioConfigurazioneEsportazioneTavole
     */
    public function setFlagErrore($flagErrore) {
        $this->flag_errore = $flagErrore;

        return $this;
    }

    /**
     * Get flag_errore
     *
     * @return bool
     */
    public function getFlagErrore() {
        return $this->flag_errore;
    }

    /**
     * Set flag_conferma_esportazione
     *
     * @param bool $flagConfermaEsportazione
     * @return MonitoraggioConfigurazioneEsportazioneTavole
     */
    public function setDataConfermaEsportazione(\DateTime $dataConfermaEsportazione) {
        $this->data_conferma_esportazione = $dataConfermaEsportazione;

        return $this;
    }

    /**
     * Get data_conferma_esportazione
     *
     * @return \DateTime
     */
    public function getDataConfermaEsportazione() {
        return $this->data_conferma_esportazione;
    }

    /**
     * Set monitoraggio_configurazione_esportazione
     *
     * @param \MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazione $monitoraggioConfigurazioneEsportazione
     * @return MonitoraggioConfigurazioneEsportazioneTavole
     */
    public function setMonitoraggioConfigurazioneEsportazione(\MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazione $monitoraggioConfigurazioneEsportazione) {
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
     * Add monitoraggio_configurazione_esportazione_errori
     *
     * @param \MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneErrore $monitoraggioConfigurazioneEsportazioneErrori
     * @return MonitoraggioConfigurazioneEsportazioneTavole
     */
    public function addMonitoraggioConfigurazioneEsportazioneErrori(\MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneErrore $monitoraggioConfigurazioneEsportazioneErrori) {
        $this->monitoraggio_configurazione_esportazione_errori[] = $monitoraggioConfigurazioneEsportazioneErrori;

        return $this;
    }

    /**
     * Remove monitoraggio_configurazione_esportazione_errori
     *
     * @param \MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneErrore $monitoraggioConfigurazioneEsportazioneErrori
     */
    public function removeMonitoraggioConfigurazioneEsportazioneErrori(\MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneErrore $monitoraggioConfigurazioneEsportazioneErrori) {
        $this->monitoraggio_configurazione_esportazione_errori->removeElement($monitoraggioConfigurazioneEsportazioneErrori);
    }

    /**
     * Get monitoraggio_configurazione_esportazione_errori
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMonitoraggioConfigurazioneEsportazioneErrori() {
        return $this->monitoraggio_configurazione_esportazione_errori;
    }

    public function getTavolaProtocollo() {
        return $this->tavola_protocollo;
    }

    public function setTavolaProtocollo($tavola_protocollo) {
        $this->tavola_protocollo = $tavola_protocollo;
    }

    public function getFlagConfermaEsportazione() {
        return $this->flag_conferma_esportazione;
    }

    public function setFlagConfermaEsportazione($flag_conferma_esportazione) {
        $this->flag_conferma_esportazione = $flag_conferma_esportazione;
    }

    public function getSortOrder() {
        if (!in_array($this->tavola_protocollo, self::$SORT_ORDER)) {
            throw new \Exception('Richiesta sortOrder a tavola ' . $this->tavola_protocollo . ' non inserita');
        }
        return self::$SORT_ORDER[$this->tavola_protocollo];
    }
}
