<?php

namespace AttuazioneControlloBundle\Entity\Controlli;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="checklist_controlli")
 */
class ChecklistControllo {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $codice;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $nome;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Controlli\SezioneChecklistControllo", mappedBy="checklist")
     * @ORM\OrderBy({"ordinamento" = "ASC"})
     */
    private $sezioni;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Controlli\ValutazioneChecklistControllo", mappedBy="checklist")
     */
    protected $valutazioni_checklist;

    /**
     * @ORM\ManyToMany(targetEntity="SfingeBundle\Entity\Procedura", inversedBy="checklist_controllo")
     * @ORM\JoinTable(name="checklist_controllo_procedure")    
     */
    protected $procedure;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ordinamento;

    function __construct() {
        $this->sezioni = new \Doctrine\Common\Collections\ArrayCollection();
        $this->valutazioni_checklist = new \Doctrine\Common\Collections\ArrayCollection();
    }

    function getId() {
        return $this->id;
    }

    function getCodice() {
        return $this->codice;
    }

    function getNome() {
        return $this->nome;
    }

    function getSezioni() {
        return $this->sezioni;
    }

    function getValutazioniChecklist() {
        return $this->valutazioni_checklist;
    }

    function setId($id) {
        $this->id = $id;
        return $this;
    }

    function setCodice($codice) {
        $this->codice = $codice;
        return $this;
    }

    function setNome($nome) {
        $this->nome = $nome;
        return $this;
    }

    function setSezioni($sezioni) {
        $this->sezioni = $sezioni;
        return $this;
    }

    function setValutazioniChecklist($valutazioni_checklist) {
        $this->valutazioni_checklist = $valutazioni_checklist;
        return $this;
    }

    function getProcedure() {
        return $this->procedure;
    }

    function setProcedure($procedure) {
        $this->procedure = $procedure;
        return $this;
    }

    function __toString() {
        return $this->nome;
    }

    public function getOrdinamento() {
        return $this->ordinamento;
    }

    public function setOrdinamento($ordinamento) {
        $this->ordinamento = $ordinamento;
    }

}
