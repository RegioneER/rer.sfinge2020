<?php

namespace AttuazioneControlloBundle\Entity\Controlli;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use BaseBundle\Entity\EntityLoggabileCancellabile;

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\Controlli\ControlloProceduraRepository")
 * @ORM\Table(name="controlli_procedure")
 */
class ControlloProcedura extends EntityLoggabileCancellabile {

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Procedura", inversedBy="controlli")
     * @ORM\JoinColumn(name="procedura_id", referencedColumnName="id", nullable=false)
     */
    protected $procedura;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Controlli\DocumentoControlloProcedura", mappedBy="controllo_procedura", cascade={"persist"})
     */
    protected $documenti_controllo;
    protected $percentuale_coperta;
    protected $imprese_campionate;
    protected $imprese_controllate;
    protected $spesa_controllata;
    protected $spesa_irregolare;
    protected $rettifiche;
    protected $campione_revoche;
    protected $cl_non_ammesse;
    protected $cl_rend_ammesse;
    protected $cl_loco_ammesse;

    public function __construct() {
        $this->documenti_controllo = new ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getProcedura() {
        return $this->procedura;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setProcedura($procedura) {
        $this->procedura = $procedura;
    }

    public function getDocumentiControllo() {
        return $this->documenti_controllo;
    }

    public function setDocumentiControllo($documenti_controllo) {
        $this->documenti_controllo = $documenti_controllo;
    }

    public function getPercentualeCoperta() {
        return $this->percentuale_coperta;
    }

    public function getImpreseCampionate() {
        return $this->imprese_campionate;
    }

    public function getImpreseControllate() {
        return $this->imprese_controllate;
    }

    public function getSpesaControllata() {
        return $this->spesa_controllata;
    }

    public function getSpesaIrregolare() {
        return $this->spesa_irregolare;
    }

    public function getRettifiche() {
        return $this->rettifiche;
    }

    public function getCampioneRevoche() {
        return $this->campione_revoche;
    }

    public function setPercentualeCoperta($percentuale_coperta) {
        $this->percentuale_coperta = $percentuale_coperta;
    }

    public function setImpreseCampionate($imprese_campionate) {
        $this->imprese_campionate = $imprese_campionate;
    }

    public function setImpreseControllate($imprese_controllate) {
        $this->imprese_controllate = $imprese_controllate;
    }

    public function setSpesaControllata($spesa_controllata) {
        $this->spesa_controllata = $spesa_controllata;
    }

    public function setSpesaIrregolare($spesa_irregolare) {
        $this->spesa_irregolare = $spesa_irregolare;
    }

    public function setRettifiche($rettifiche) {
        $this->rettifiche = $rettifiche;
    }

    public function setCampioneRevoche($campione_revoche) {
        $this->campione_revoche = $campione_revoche;
    }

    public function addDocumentoControllo($documento_controllo) {
        $documento_controllo->setControlloProcedura($this);
        $this->documenti_controllo->add($documento_controllo);
    }

    public function getClNonAmmesse() {
        return $this->cl_non_ammesse;
    }

    public function setClNonAmmesse($cl_non_ammesse) {
        $this->cl_non_ammesse = $cl_non_ammesse;
    }

    public function getClRendAmmesse() {
        return $this->cl_rend_ammesse;
    }

    public function getClLocoAmmesse() {
        return $this->cl_loco_ammesse;
    }

    public function setClRendAmmesse($cl_rend_ammesse) {
        $this->cl_rend_ammesse = $cl_rend_ammesse;
    }

    public function setClLocoAmmesse($cl_loco_ammesse) {
        $this->cl_loco_ammesse = $cl_loco_ammesse;
    }

}
