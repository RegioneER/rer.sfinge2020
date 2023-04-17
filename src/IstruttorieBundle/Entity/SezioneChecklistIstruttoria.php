<?php

namespace IstruttorieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="istruttorie_sezioni_checklist")
 */
class SezioneChecklistIstruttoria extends EntityLoggabileCancellabile {

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int|null
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="IstruttorieBundle\Entity\ChecklistIstruttoria", inversedBy="sezioni")
     * @ORM\JoinColumn(nullable=false)
     * @var ChecklistIstruttoria
     */
    protected $checklist;

    /**
     * @ORM\Column(type="text", nullable=false)
     * @var string
     */
    protected $descrizione;

    /**
     * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\ElementoChecklistIstruttoria", mappedBy="sezione_checklist", cascade={"persist"})
     * @var Collection|ElementoChecklistIstruttoria[]
     */
    protected $elementi;

    /**
     * @ORM\Column(type="integer", nullable=false)
     * @var int
     */
    private $ordinamento;

    /**
     * @ORM\Column(type="boolean", name="commento", nullable=false)
     * @var bool
     */
    protected $commento;

    /**
     * @ORM\Column(type="string", name="codice", nullable=true, length=50)
     * @var string|null
     */
    protected $codice;

    function __construct() {
        $this->elementi = new ArrayCollection();
    }

    function getId(): ?int {
        return $this->id;
    }

    function getChecklist(): ChecklistIstruttoria {
        return $this->checklist;
    }

    function getDescrizione(): ?string {
        return $this->descrizione;
    }

    /**
     * @return ElementoChecklistIstruttoria[]|Collection
     */
    function getElementi(): Collection {
        return $this->elementi;
    }

    function setId(?int $id) {
        $this->id = $id;
    }

    function setChecklist(ChecklistIstruttoria $checklist): self {
        $this->checklist = $checklist;

        return $this;
    }

    function setDescrizione($descrizione): self {
        $this->descrizione = $descrizione;

        return $this;
    }

    function setElementi(Collection $elementi): self {
        $this->elementi = $elementi;

        return $this;
    }

    function getOrdinamento(): ?int {
        return $this->ordinamento;
    }

    function setOrdinamento(int $ordinamento): self {
        $this->ordinamento = $ordinamento;

        return $this;
    }

    function getCommento(): ?bool {
        return $this->commento;
    }

    function setCommento(bool $commento): self {
        $this->commento = $commento;

        return $this;
    }

    public function getCodice(): ?string {
        return $this->codice;
    }

    public function setCodice(?string $codice): self {
        $this->codice = $codice;

        return $this;
    }

}
