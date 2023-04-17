<?php

namespace IstruttorieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="istruttorie_valutazioni_elementi_checklist")
 */
class ValutazioneElementoChecklistIstruttoria {
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="IstruttorieBundle\Entity\ElementoChecklistIstruttoria", inversedBy="valutazioni")
     * @ORM\JoinColumn(nullable=false)
     * @var ElementoChecklistIstruttoria
     */
    protected $elemento;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    protected $valore;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    protected $valore_raw;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    protected $commento;

    /**
     * @ORM\ManyToOne(targetEntity="IstruttorieBundle\Entity\ValutazioneChecklistIstruttoria", inversedBy="valutazioni_elementi")
     * @ORM\JoinColumn(nullable=false)
     * @var ValutazioneChecklistIstruttoria
     */
    protected $valutazione_checklist;

    public function getId(): ?int {
        return $this->id;
    }

    public function getElemento(): ?ElementoChecklistIstruttoria {
        return $this->elemento;
    }

    public function getValore(): ?string {
        return $this->valore;
    }

    public function getValoreRaw(): ?string {
        return $this->valore_raw;
    }

    public function getCommento(): ?string {
        return $this->commento;
    }

    public function setId(?int $id) {
        $this->id = $id;
    }

    public function setElemento(ElementoChecklistIstruttoria $elemento): self {
        $this->elemento = $elemento;

        return $this;
    }

    public function setValore(?string $valore): self {
        $this->valore = $valore;

        return $this;
    }

    public function setValoreRaw(?string $valore_raw): self {
        $this->valore_raw = $valore_raw;

        return $this;
    }

    public function setCommento($commento): self {
        $this->commento = $commento;

        return $this;
    }

    public function getValutazioneChecklist(): ValutazioneChecklistIstruttoria {
        return $this->valutazione_checklist;
    }

    public function setValutazioneChecklist(ValutazioneChecklistIstruttoria $valutazione_checklist): self {
        $this->valutazione_checklist = $valutazione_checklist;

        return $this;
    }

    /**
     * @Assert\Callback
     */
    public function validazioneTipo(ExecutionContextInterface $context) {
        if ("integer" == $this->getElemento()->getTipo() && !preg_match("/^(-?\d+|\d*)$/", $this->getValore())) {
            $context->buildViolation('Questo valore deve essere un numero intero')
                    ->atPath('valore')
                    ->addViolation();
        } elseif (in_array($this->getElemento()->getTipo(), ["text", "textarea"])
                && !is_null($this->getElemento()->getLunghezzaMassima())
                && !is_null($this->getValore())
                && mb_strlen($this->getValore()) > $this->getElemento()->getLunghezzaMassima()) {
            $context->buildViolation("Questo valore è troppo lungo. Dovrebbe essere al massimo di {$this->getElemento()->getLunghezzaMassima()} caratteri. Hai inserito " . mb_strlen($this->getValore()) . " caratteri")
                    ->atPath('valore')
                    ->addViolation();
        }
    }

    public function getCodiceElemento(): ?string {
        return $this->getElemento()->getCodice();
    }
}
