<?php

namespace RichiesteBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="RichiesteBundle\Entity\PianoCostoRepository")
 * @ORM\Table(name="piani_costo",
 *  indexes={
 *      @ORM\Index(name="idx_procedura_piano_costo_id", columns={"procedura_id"}),
 * 		@ORM\Index(name="idx_sezione_piano_costo_id", columns={"sezione_id"}),
 * 		@ORM\Index(name="idx_tipo_voce_id", columns={"tipo_voce_id"}),
 *  })
 */
class PianoCosto extends EntityLoggabileCancellabile {

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Procedura", inversedBy="piani_costo")
     * @ORM\JoinColumn(name="procedura_id", referencedColumnName="id", nullable=true)
     */
    protected $procedura;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\SezionePianoCosto", inversedBy="piani_costo")
     * @ORM\JoinColumn(name="sezione_id", referencedColumnName="id", nullable=false)
     */
    protected $sezione_piano_costo;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\TipoVoceSpesa")
     * @ORM\JoinColumn(name="tipo_voce_id", referencedColumnName="id", nullable=false)
     */
    protected $tipo_voce_spesa;

    /**
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\VocePianoCosto", mappedBy="piano_costo")
     */
    protected $voci_piano_costo;

    /**
     * @ORM\Column(type="integer", name="ordinamento", nullable=false)
     */
    protected $ordinamento;

    /**
     * @ORM\Column(type="string", name="titolo", nullable=false)
     */
    protected $titolo;

    /**
     * @ORM\Column(type="string", length=25, nullable=false)
     */
    protected $codice;

    /**
     * @ORM\Column(type="string", length=25, nullable=false)
     * serve per indicare univocamente la voce spesa nel piano dei costi, è utile nel caso di multi sezione,
     * serve a capire che la voce è la stessa anche se la sezione è diversa
     */
    protected $identificativo_pdf;

    /**
     * @ORM\Column(type="string", length=25, nullable=false)
     * questa la deve scrivere vincenzo
     */
    protected $identificativo_html;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC37VoceSpesa")
     * @ORM\JoinColumn(name="voce_spesa_id", referencedColumnName="id", nullable=true)
     */
    protected $mon_voce_spesa;

    /**
     * 
     * @ORM\Column(name="voce_spesa_generale", type="boolean", nullable=false, options={"default": 0})
     */
    protected $voce_spesa_generale;

    /**
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\InterventoSede", mappedBy="piano_costo")
     */
    protected $interventi_sede;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getProcedura() {
        return $this->procedura;
    }

    /**
     * @return SezionePianoCosto
     */
    public function getSezionePianoCosto() {
        return $this->sezione_piano_costo;
    }

    /**
     * @return TipoVoceSpesa
     */
    public function getTipoVoceSpesa() {
        return $this->tipo_voce_spesa;
    }

    public function getVociPianoCosto() {
        return $this->voci_piano_costo;
    }

    public function setProcedura($procedura) {
        $this->procedura = $procedura;
    }

    public function setSezionePianoCosto($sezione_piano_costo) {
        $this->sezione_piano_costo = $sezione_piano_costo;
    }

    public function setTipoVoceSpesa($tipo_voce_spesa) {
        $this->tipo_voce_spesa = $tipo_voce_spesa;
    }

    public function setVociPianoCosto($voci_piano_costo) {
        $this->voci_piano_costo = $voci_piano_costo;
    }

    public function getOrdinamento() {
        return $this->ordinamento;
    }

    public function setOrdinamento($ordinamento) {
        $this->ordinamento = $ordinamento;
    }

    public function getTitolo() {
        return $this->titolo;
    }

    public function setTitolo($titolo) {
        $this->titolo = $titolo;
    }

    public function getCodice() {
        return $this->codice;
    }

    public function setCodice($codice) {
        $this->codice = $codice;
    }

    public function getIdentificativoPdf() {
        return $this->identificativo_pdf;
    }

    public function setIdentificativoPdf($identificativo_pdf) {
        $this->identificativo_pdf = $identificativo_pdf;
    }

    public function getIdentificativoHtml() {
        return $this->identificativo_html;
    }

    public function setIdentificativoHtml($identificativo_html) {
        $this->identificativo_html = $identificativo_html;
    }

    /**
     * @return \MonitoraggioBundle\Entity\TC37VoceSpesa
     */
    function getMonVoceSpesa() {
        return $this->mon_voce_spesa;
    }

    function setMonVoceSpesa($mon_voce_spesa) {
        $this->mon_voce_spesa = $mon_voce_spesa;
    }

    public function getVoceSpesaGenerale() {
        return $this->voce_spesa_generale;
    }

    public function setVoceSpesaGenerale($voce_spesa_generale) {
        $this->voce_spesa_generale = $voce_spesa_generale;
    }

    public function isVoceSpesaGenerale() {
        return $this->voce_spesa_generale == true;
    }

    public function getInterventiSede() {
        return $this->interventi_sede;
    }

    public function setInterventiSede($interventi_sede) {
        $this->interventi_sede = $interventi_sede;
    }

    /**
     * @return string
     */
    public function getCodiceTitolo(): string {
        return $this->codice . ') ' . $this->titolo;
    }
    
    public function getSezioneTitolo() {
        return $this->sezione_piano_costo->getTitoloSezione() .') ' . $this->titolo;
    }
    
    public function getSezioneCodiceTitolo() {
        return $this->sezione_piano_costo->getTitoloSezione() .', voce ' .$this->codice. ') ' .$this->titolo;
    }
    
    public function getSezioneCodiceTitoloColor() {
        return '<strong>'.$this->sezione_piano_costo->getTitoloSezione() .'</strong>, <span style="color:blue">voce ' .$this->codice. ')</span> ' .$this->titolo;
    }

}
