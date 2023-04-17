<?php

namespace AttuazioneControlloBundle\Entity\Controlli;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="sezioni_checklist_controlli")
 */
class SezioneChecklistControllo {

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Controlli\ChecklistControllo", inversedBy="sezioni")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $checklist;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    protected $descrizione;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Controlli\ElementoChecklistControllo", mappedBy="sezione_checklist", cascade={"persist"})
     */
    protected $elementi;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $ordinamento;

    /**
     * @ORM\Column(type="boolean", name="commento", nullable=false)
     */
    protected $commento;

    /**
     * @ORM\Column(type="boolean", name="documenti_bool", nullable=false)
     */
    protected $documenti_bool;

    /**
     * @ORM\Column(type="boolean", name="collocazione_bool", nullable=false)
     */
    protected $collocazione_bool;

    /**
     * @ORM\Column(type="boolean", name="collocazione_ben_bool", nullable=false)
     */
    protected $collocazione_ben_bool;

    function __construct() {
        $this->elementi = new \Doctrine\Common\Collections\ArrayCollection();
    }

    function getId() {
        return $this->id;
    }

    function getChecklist() {
        return $this->checklist;
    }

    function getDescrizione() {
        return $this->descrizione;
    }

    function getElementi() {
        return $this->elementi;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setChecklist($checklist) {
        $this->checklist = $checklist;
    }

    function setDescrizione($descrizione) {
        $this->descrizione = $descrizione;
    }

    function setElementi($elementi) {
        $this->elementi = $elementi;
    }

    function getOrdinamento() {
        return $this->ordinamento;
    }

    function setOrdinamento($ordinamento) {
        $this->ordinamento = $ordinamento;
    }

    function getCommento() {
        return $this->commento;
    }

    function setCommento($commento) {
        $this->commento = $commento;
    }

    public function getDocumentiBool() {
        return $this->documenti_bool;
    }

    public function getCollocazioneBool() {
        return $this->collocazione_bool;
    }

    public function setDocumentiBool($documenti_bool) {
        $this->documenti_bool = $documenti_bool;
    }

    public function setCollocazioneBool($collocazione_bool) {
        $this->collocazione_bool = $collocazione_bool;
    }

    public function getCollocazioneBenBool() {
        return $this->collocazione_ben_bool;
    }

    public function setCollocazioneBenBool($collocazione_ben_bool) {
        $this->collocazione_ben_bool = $collocazione_ben_bool;
    }

}
