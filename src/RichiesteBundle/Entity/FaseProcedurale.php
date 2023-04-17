<?php

namespace RichiesteBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use SfingeBundle\Entity\Procedura;

/**
 * @ORM\Entity(repositoryClass="RichiesteBundle\Entity\FaseProceduraleRepository")
 * @ORM\Table(name="fasi_procedurali",
 *     indexes={
 *         @ORM\Index(name="idx_procedura_fase_procedurale_id", columns={"procedura_id"}),
 * 		@ORM\Index(name="idx_fase_natura_id", columns={"fase_natura_id"}),
 *     })
 */
class FaseProcedurale extends EntityLoggabileCancellabile {
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Procedura", inversedBy="piani_costo")
     * @ORM\JoinColumn(name="procedura_id", referencedColumnName="id", nullable=false)
     */
    protected $procedura;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\FaseNatura")
     * @ORM\JoinColumn(name="fase_natura_id", referencedColumnName="id", nullable=false)
     */
    protected $fase_natura;

    /**
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\VoceFaseProcedurale", mappedBy="fase_procedurale")
     */
    protected $voci_fase_procedurale;

    /**
     * @ORM\Column(type="integer", name="ordinamento", nullable=false)
     */
    protected $ordinamento;

    /**
     * @ORM\Column(type="string", name="titolo", nullable=true)
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

    public function __construct() {
        $this->voci_fase_procedurale = new ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getProcedura() {
        return $this->procedura;
    }

    /**
     * @return FaseNatura
     */
    public function getFaseNatura() {
        return $this->fase_natura;
    }

    public function getVociFaseProcedurale() {
        return $this->voci_fase_procedurale;
    }

    public function getOrdinamento() {
        return $this->ordinamento;
    }

    public function getTitolo() {
        return $this->titolo;
    }

    public function getCodice() {
        return $this->codice;
    }

    public function getIdentificativoPdf() {
        return $this->identificativo_pdf;
    }

    public function getIdentificativoHtml() {
        return $this->identificativo_html;
    }

    public function setId($id): self {
        $this->id = $id;
        return $this;
    }

    public function setProcedura(Procedura $procedura): self {
        $this->procedura = $procedura;
        return $this;
    }

    public function setFaseNatura(FaseNatura $fase_natura): self {
        $this->fase_natura = $fase_natura;
        return $this;
    }

    public function setVociFaseProcedurale(Collection $voci_fase_procedurale): self {
        $this->voci_fase_procedurale = $voci_fase_procedurale;

        return $this;
    }

    public function setOrdinamento($ordinamento): self {
        $this->ordinamento = $ordinamento;

        return $this;
    }

    public function setTitolo(?string $titolo): self {
        $this->titolo = $titolo;

        return $this;
    }

    public function setCodice(?string $codice): self {
        $this->codice = $codice;

        return $this;
    }

    public function setIdentificativoPdf(?string $identificativo_pdf): self {
        $this->identificativo_pdf = $identificativo_pdf;

        return $this;
    }

    public function setIdentificativoHtml(?string $identificativo_html): self {
        $this->identificativo_html = $identificativo_html;

        return $this;
    }

    public function addVociFaseProcedurale(VoceFaseProcedurale $vociFaseProcedurale): self {
        $this->voci_fase_procedurale[] = $vociFaseProcedurale;

        return $this;
    }

    public function removeVociFaseProcedurale(VoceFaseProcedurale $vociFaseProcedurale) {
        $this->voci_fase_procedurale->removeElement($vociFaseProcedurale);
    }
}
