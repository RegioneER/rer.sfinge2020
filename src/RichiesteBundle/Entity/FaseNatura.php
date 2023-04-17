<?php

namespace RichiesteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use MonitoraggioBundle\Entity\TC46FaseProcedurale;

/**
 * @ORM\Table(name="fasi_natura")
 * @ORM\Entity
 */
class FaseNatura {
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $codice;

    /**
     * @ORM\Column(type="string", length=1024, nullable=false)
     */
    protected $descrizione;

    /**
     * @ORM\ManyToOne(targetEntity="CipeBundle\Entity\Classificazioni\CupNatura", inversedBy="fasi_natura")
     * @ORM\JoinColumn(name="natura_id", referencedColumnName="id", nullable=false)
     */
    protected $natura;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC46FaseProcedurale")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $definizione;

    public function getId() {
        return $this->id;
    }

    public function getCodice() {
        return $this->codice;
    }

    public function getDescrizione() {
        return $this->descrizione;
    }

    public function getNatura() {
        return $this->natura;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setCodice($codice) {
        $this->codice = $codice;
    }

    public function setDescrizione($descrizione) {
        $this->descrizione = $descrizione;
    }

    public function setNatura($natura) {
        $this->natura = $natura;
    }

    public function setDefinizione(?TC46FaseProcedurale $definizione): self {
        $this->definizione = $definizione;

        return $this;
    }

    public function getDefinizione(): ?TC46FaseProcedurale {
        return $this->definizione;
    }
}
