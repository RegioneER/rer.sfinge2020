<?php

namespace RichiesteBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="RichiesteBundle\Entity\DnshProceduraRepository")
 * @ORM\Table(name="dnsh_procedura")
 */
class DnshProcedura extends EntityLoggabileCancellabile {

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Procedura", inversedBy="dnsh_procedura")
     * @ORM\JoinColumn(name="procedura_id", referencedColumnName="id", nullable=true)
     */
    protected $procedura;

    /**
     * @ORM\Column(type="string", length=25, nullable=false)
     * Lo prevediamo in modo che se per lo stesso bando ci sono più triplette in base ad altre costanti almeno è gestibile
     */
    protected $codice;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    protected $testo_non_arreca;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    protected $testo_adotta_misure;
    
     /**
     * @ORM\Column(type="text", nullable=false)
     */
    protected $testo_specifica_documentazione;

    
    public function getId() {
        return $this->id;
    }

    public function getProcedura() {
        return $this->procedura;
    }

    public function getCodice() {
        return $this->codice;
    }

    public function getTestoNonArreca() {
        return $this->testo_non_arreca;
    }

    public function getTestoAdottaMisure() {
        return $this->testo_adotta_misure;
    }

    public function getTestoSpecificaDocumentazione() {
        return $this->testo_specifica_documentazione;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setProcedura($procedura): void {
        $this->procedura = $procedura;
    }

    public function setCodice($codice): void {
        $this->codice = $codice;
    }

    public function setTestoNonArreca($testo_non_arreca): void {
        $this->testo_non_arreca = $testo_non_arreca;
    }

    public function setTestoAdottaMisure($testo_adotta_misure): void {
        $this->testo_adotta_misure = $testo_adotta_misure;
    }

    public function setTestoSpecificaDocumentazione($testo_specifica_documentazione): void {
        $this->testo_specifica_documentazione = $testo_specifica_documentazione;
    }


}
