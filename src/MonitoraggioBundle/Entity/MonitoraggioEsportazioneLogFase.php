<?php

/**
 * @author vbuscemi
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="monitoraggio_esportazioni_log_fasi")
 */
class MonitoraggioEsportazioneLogFase extends EntityLoggabileCancellabile {
    const STATO_INIZIALIZZAZIONE = 'INIZIALIZZAZIONE';
    const STATO_SCARICO = 'SCARICO';
    const STATO_ERRORE_SCARICO = 'ERRORE_SCARICO';
    const STATO_CONFIGURAZIONE = 'CONFIGURAZIONE';
    const STATO_ND = 'N/D';
    const STATO_ERRORE = 'ERRORE';
    const STATO_INVIATO = 'INVIATO';
    const STATO_COMPLETATO = 'COMPLETATO';
    const STATO_IMPORTATO = 'IMPORTATO';
    const STATO_IMPORTATO_ERRORI = 'IMPORTATO_ERRORI';
    const STATO_RESPINTO = 'RESPINTO';
    const STATO_IMPORTAZIONE_BATCH = 'MPORTAZIONE_BATCH';

    public static $FASI = [
        self::STATO_INIZIALIZZAZIONE => 'Inizializzazione',
        self::STATO_SCARICO => 'Scarico',
        self::STATO_CONFIGURAZIONE => 'Configurazione',
        self::STATO_ND => 'N/D',
        self::STATO_ERRORE => 'Errore',
        self::STATO_INVIATO => 'Inviato',
        self::STATO_COMPLETATO => 'Completato',
        self::STATO_IMPORTATO => 'Importato',
        self::STATO_ERRORE_SCARICO => 'Errore durante scarico',
        self::STATO_IMPORTATO_ERRORI => 'Importato con ritorno da IGRUE',
        self::STATO_RESPINTO => "Respinto dall'operatore",
        self::STATO_IMPORTAZIONE_BATCH => 'Richiesta importazione differita',
    ];

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"default" : "CURRENT_TIMESTAMP"})
     * @Assert\Date
     */
    protected $data_inizio;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\Date
     */
    protected $data_fine;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $fase;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioEsportazione", inversedBy="fasi")
     * @ORM\JoinColumn(name="monitorazggio_esportazione_id", referencedColumnName="id", nullable=false)
     * @var \MonitoraggioBundle\Entity\MonitoraggioEsportazione|null
     */
    protected $monitoraggio_esportazione;

    public function __construct(MonitoraggioEsportazione $esportazione = null, $fase = self::STATO_INIZIALIZZAZIONE) {
        $this->fase = $fase;
        $this->monitoraggio_esportazione = $esportazione;
        $this->data_inizio = new \DateTime();
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
     * Set data_inizio
     *
     * @param \DateTime $dataInizio
     * @return MonitoraggioEsportazioneLogFase
     */
    public function setDataInizio($dataInizio) {
        $this->data_inizio = $dataInizio;

        return $this;
    }

    /**
     * Get data_inizio
     *
     * @return \DateTime
     */
    public function getDataInizio() {
        return $this->data_inizio;
    }

    /**
     * Set data_fine
     *
     * @param \DateTime $dataFine
     * @return MonitoraggioEsportazioneLogFase
     */
    public function setDataFine($dataFine) {
        $this->data_fine = $dataFine;

        return $this;
    }

    /**
     * Get data_fine
     *
     * @return \DateTime
     */
    public function getDataFine() {
        return $this->data_fine;
    }

    /**
     * Set fase
     *
     * @param string $fase
     * @return MonitoraggioEsportazioneLogFase
     */
    public function setFase($fase) {
        $this->fase = $fase;

        return $this;
    }

    /**
     * Get fase
     *
     * @return string
     */
    public function getFase() {
        return $this->fase;
    }

    /**
     * Set monitorazggio_esportazione
     *
     * @param \MonitoraggioBundle\Entity\MonitoraggioEsportazione $monitorazggioEsportazione
     * @return MonitoraggioEsportazioneLogFase
     */
    public function setMonitoraggioEsportazione(\MonitoraggioBundle\Entity\MonitoraggioEsportazione $monitorazggioEsportazione) {
        $this->monitoraggio_esportazione = $monitorazggioEsportazione;

        return $this;
    }

    /**
     * Get monitorazggio_esportazione
     *
     * @return \MonitoraggioBundle\Entity\MonitoraggioEsportazione
     */
    public function getMonitoraggioEsportazione() {
        return $this->monitoraggio_esportazione;
    }

    public function getDescrizioneFase() {
        return self::$FASI[$this->fase];
    }
}
