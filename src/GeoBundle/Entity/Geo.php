<?php

namespace GeoBundle\Entity;

use Doctrine\ORM\Mapping as orm;
use MonitoraggioBundle\Entity\TC16LocalizzazioneGeografica;

abstract class Geo {
    /**
     * @orm\Id
     * @orm\Column(type="bigint")
     * @orm\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @orm\Column(type="string", length=256)
     * @var string|null
     */
    protected $denominazione;

    /**
     * @orm\Column(type="string", length=15)
     * @var string|null
     */
    protected $codice_completo;

    /**
     * @orm\Column(type="string", length=3)
     * @var string|null
     */
    protected $codice;

    /**
     * @orm\Column(type="date")
     * @var \DateTime|null
     */
    protected $data_istituzione;

    /**
     * @orm\Column(type="date", nullable=true)
     * @var \DateTime|null
     */
    protected $data_destituzione;

    /**
     * @orm\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC16LocalizzazioneGeografica")
     * @orm\JoinColumn(name="tc16_localizzazione_geografica_id", referencedColumnName="id", nullable=true)
     * @var TC16LocalizzazioneGeografica|null
     */
    protected $tc16_localizzazione_geografica;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getDenominazione() {
        return $this->denominazione;
    }

    public function setDenominazione($denominazione) {
        $this->denominazione = $denominazione;
    }

    public function getCodiceCompleto() {
        return $this->codice_completo;
    }

    public function setCodiceCompleto($codice_completo) {
        $this->codice_completo = $codice_completo;
    }

    public function getCodice() {
        return $this->codice;
    }

    public function setCodice($codice) {
        $this->codice = $codice;
    }

    public function getDataIstituzione() {
        return $this->data_istituzione;
    }

    public function setDataIstituzione($data_istituzione) {
        $this->data_istituzione = \is_string($data_istituzione) ? new \DateTime($data_istituzione) : $data_istituzione;
    }

    public function getDataDestituzione() {
        return $this->data_destituzione;
    }

    public function setDataDestituzione($data_destituzione) {
        $this->data_destituzione = \is_string($data_destituzione) ? new \DateTime($data_destituzione) : $data_destituzione;
    }

    public function __toString() {
        return $this->getDenominazione();
    }

    public function setTc16LocalizzazioneGeografica(?TC16LocalizzazioneGeografica $tc16LocalizzazioneGeografica): self {
        $this->tc16_localizzazione_geografica = $tc16LocalizzazioneGeografica;

        return $this;
    }

    public function getTc16LocalizzazioneGeografica(): ?TC16LocalizzazioneGeografica {
        return $this->tc16_localizzazione_geografica;
    }
}
