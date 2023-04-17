<?php

namespace IstruttorieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Symfony\Component\Validator\Constraints as Assert;
use SfingeBundle\Entity\Utente;
use RichiesteBundle\Entity\Proponente;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use SfingeBundle\Entity\Procedura;
use RichiesteBundle\Entity\Richiesta;

/**
 * @ORM\Entity
 * @ORM\Table(name="istruttorie_valutazioni_checklist")
 */
class ValutazioneChecklistIstruttoria extends EntityLoggabileCancellabile {
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="IstruttorieBundle\Entity\IstruttoriaRichiesta", inversedBy="valutazioni_checklist")
     * @ORM\JoinColumn(name="istruttoria_id", referencedColumnName="id", nullable=false)
     * @var IstruttoriaRichiesta
     */
    protected $istruttoria;

    /**
     * @ORM\ManyToOne(targetEntity="IstruttorieBundle\Entity\ChecklistIstruttoria", inversedBy="valutazioni_checklist")
     * @ORM\JoinColumn(nullable=false)
     * @var ChecklistIstruttoria
     */
    protected $checklist;

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Utente")
     * @ORM\JoinColumn(nullable=true)
     * @var ?Utente
     */
    protected $valutatore;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var bool
     */
    protected $validata;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @var bool|null
     */
    protected $ammissibile;

    /**
     * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\ValutazioneElementoChecklistIstruttoria", mappedBy="valutazione_checklist", cascade={"persist"})
     * @Assert\Valid
     * @var Collection|ValutazioneElementoChecklistIstruttoria[]
     */
    private $valutazioni_elementi;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Proponente")
     * @ORM\JoinColumn(nullable=true)
     * @var Proponente|null
     */
    protected $proponente;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $punteggio;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime|null
     */
    protected $data_validazione;

    /**
     * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\IntegrazioneIstruttoria", mappedBy="valutazione_checklist")
     * @var Collection|IntegrazioneIstruttoria[]
     */
    protected $integrazioni;

    public function __construct() {
        $this->valutazioni_elementi = new ArrayCollection();
        $this->integrazioni = new ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getIstruttoria(): IstruttoriaRichiesta {
        return $this->istruttoria;
    }

    public function getChecklist(): ChecklistIstruttoria {
        return $this->checklist;
    }

    public function getValutatore(): ?Utente {
        return $this->valutatore;
    }

    public function getValidata(): bool {
        return $this->validata;
    }

    public function getAmmissibile(): ?bool {
        return $this->ammissibile;
    }

    /**
     * @return Collection|ValutazioneElementoChecklistIstruttoria[]
     */
    public function getValutazioniElementi(): Collection {
        return $this->valutazioni_elementi;
    }

    public function setId($id): self {
        $this->id = $id;

        return $this;
    }

    public function setIstruttoria(IstruttoriaRichiesta $istruttoria): self {
        $this->istruttoria = $istruttoria;

        return $this;
    }

    public function setChecklist(ChecklistIstruttoria $checklist): self {
        $this->checklist = $checklist;

        return $this;
    }

    public function setValutatore(?Utente $valutatore): self {
        $this->valutatore = $valutatore;

        return $this;
    }

    public function setValidata(bool $validata): self {
        $this->validata = $validata;

        return $this;
    }

    public function setAmmissibile(?bool $ammissibile): self {
        $this->ammissibile = $ammissibile;

        return $this;
    }

    public function setValutazioniElementi(Collection $valutazioni_elementi): self {
        $this->valutazioni_elementi = $valutazioni_elementi;
        return $this;
    }

    public function addValutazioneElemento(ValutazioneElementoChecklistIstruttoria $valutazione_elemento): self {
        $this->valutazioni_elementi->add($valutazione_elemento);
        $valutazione_elemento->setValutazioneChecklist($this);

        return $this;
    }

    public function getProponente(): ?Proponente {
        return $this->proponente;
    }

    public function setProponente(?Proponente $proponente): self {
        $this->proponente = $proponente;

        return $this;
    }

    public function getPunteggio() {
        return $this->punteggio;
    }

    public function setPunteggio($punteggio): self {
        $this->punteggio = $punteggio;

        return $this;
    }

    public function getDataValidazione(): ?\DateTime {
        return $this->data_validazione;
    }

    public function setDataValidazione(?\DateTime $data_validazione): self {
        $this->data_validazione = $data_validazione;

        return $this;
    }

    public function getRichiesta(): Richiesta {
        return $this->getIstruttoria()->getRichiesta();
    }

    public function getIntegrazioni(): Collection {
        return $this->integrazioni;
    }

    public function setIntegrazioni(Collection $integrazioni): self {
        $this->integrazioni = $integrazioni;

        return $this;
    }

    public function __toString() {
        $descrizione = $this->getChecklist()->getNome();

        return $descrizione;
    }

    public function getDescrizioneValutazione(): string {
        $descrizione = $this->getChecklist()->getNome();

        if ($this->getChecklist()->getProponente() && !is_null($this->getProponente())) {
            $descrizione .= " / " . $this->getProponente()->getSoggettoVersion();
        }

        if (!is_null($this->punteggio)) {
            $valore = number_format($this->getPunteggio(), 2, ",", ".");
            $descrizione .= (" / Punteggio: " . $valore);
        }

        return $descrizione;
    }

    public function getProcedura(): Procedura {
        $procedura = $this->istruttoria->getProcedura();
        return $procedura;
    }
    /**
     * @return Collection|ValutazioneElementoChecklistIstruttoria[]
     */
    public function getElementiSezione(SezioneChecklistIstruttoria $sezione): Collection {
        return $this->valutazioni_elementi->filter(function(ValutazioneElementoChecklistIstruttoria $elemento) use($sezione){
            return $elemento->getElemento()->getSezioneChecklist() == $sezione;
        });
    }
}
