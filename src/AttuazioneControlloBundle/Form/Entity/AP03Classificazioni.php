<?php

namespace AttuazioneControlloBundle\Form\Entity;

use AttuazioneControlloBundle\Entity\RichiestaProgrammaClassificazione;

class AP03Classificazioni {
    protected $id;

    /**
     * @var \AttuazioneControlloBundle\Entity\RichiestaProgramma
     */
    protected $richiesta_programma;

    /**
     * @var \MonitoraggioBundle\Entity\TC11TipoClassificazione
     */
    protected $tipo_classificazione;

    /**
     * @var \MonitoraggioBundle\Entity\TC12Classificazione
     */
    protected $classificazione;

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param \AttuazioneControlloBundle\Entity\RichiestaProgramma $richiestaProgramma
     * @return RichiestaProgrammaClassificazione
     */
    public function setRichiestaProgramma(\AttuazioneControlloBundle\Entity\RichiestaProgramma $richiestaProgramma) {
        $this->richiesta_programma = $richiestaProgramma;

        return $this;
    }

    /**
     * @return \AttuazioneControlloBundle\Entity\RichiestaProgramma
     */
    public function getRichiestaProgramma() {
        return $this->richiesta_programma;
    }

    /**
     * @param \MonitoraggioBundle\Entity\TC11TipoClassificazione $tipoClassificazione
     * @return RichiestaProgrammaClassificazione
     */
    public function setTipoClassificazione(\MonitoraggioBundle\Entity\TC11TipoClassificazione $tipoClassificazione) {
        $this->tipo_classificazione = $tipoClassificazione;

        return $this;
    }

    /**
     * @return \MonitoraggioBundle\Entity\TC11TipoClassificazione
     */
    public function getTipoClassificazione() {
        return $this->tipo_classificazione;
    }

    /**
     * @param \MonitoraggioBundle\Entity\TC12Classificazione $classificazione
     * @return RichiestaProgrammaClassificazione
     */
    public function setClassificazione(\MonitoraggioBundle\Entity\TC12Classificazione $classificazione) {
        $this->classificazione = $classificazione;

        return $this;
    }

    /**
     * @return \MonitoraggioBundle\Entity\TC12Classificazione
     */
    public function getClassificazione() {
        return $this->classificazione;
    }

    public function __construct(\AttuazioneControlloBundle\Entity\RichiestaProgramma $richiesta_programma = null) {
        $this->richiesta_programma = $richiesta_programma;
    }

    public function toEntity() {
        return new RichiestaProgrammaClassificazione($this->richiesta_programma, $this->classificazione);
    }
}
