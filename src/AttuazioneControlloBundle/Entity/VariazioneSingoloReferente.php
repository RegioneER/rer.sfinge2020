<?php

namespace AttuazioneControlloBundle\Entity;

use AnagraficheBundle\Entity\Persona;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Entity\Referente;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="variazioni_referenti")
 */
class VariazioneSingoloReferente extends EntityLoggabileCancellabile {
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="VariazioneReferente", inversedBy="variazioni_singolo_referente")
     * @ORM\JoinColumn(nullable=false)
     * @var VariazioneReferente
     */
    protected $variazione;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Referente")
     * @var Referente|null
     */
    protected $referenza;

    /**
     * @ORM\ManyToOne(targetEntity="AnagraficheBundle\Entity\Persona")
     * @ORM\JoinColumn(name="persona_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull
     * @var Persona|null
     */
    private $persona;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Proponente", inversedBy="referenti")
     * @ORM\JoinColumn(name="proponente_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull
     * @var Proponente
     */
    private $proponente;

    /**
     * @ORM\Column(name="qualifica", type="string", length=100, nullable=true)
     * @Assert\Length(max="100")
     * @var string|null
     */
    private $qualifica;

    /**
     * @ORM\Column(name="ruolo", type="string", length=100, nullable=true)
     * @Assert\Length(max="100")
     * @var string|null
     */
    private $ruolo;

    /**
     * @ORM\Column(name="email_pec", type="string", length=128, nullable=true)
     *
     * @Assert\NotBlank(message="Specificare l'indirizzo email PEC", groups={"bando_5", "bando_61", "bando_98", "bando_99", "bando_118", "bando_123", "bando_125"})
     * @Assert\Length(max="128")
     * @Assert\Email
     * @var string|null
     */
    protected $email_pec;

    public function __construct(VariazioneReferente $variazione, Referente $referente) {
        $this->variazione = $variazione;
        $this->referenza = $referente;
        $this->proponente = $referente->getProponente();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function setVariazione(VariazioneReferente $variazione): self {
        $this->variazione = $variazione;

        return $this;
    }

    public function getVariazione(): VariazioneReferente {
        return $this->variazione;
    }

    public function setReferenza(?Referente $referenza): self {
        $this->referenza = $referenza;

        return $this;
    }

    public function getReferenza(): ?Referente {
        return $this->referenza;
    }

    public function setQualifica(?string $qualifica): self {
        $this->qualifica = $qualifica;

        return $this;
    }

    public function getQualifica(): ?string {
        return $this->qualifica;
    }

    public function setRuolo(?string $ruolo): self {
        $this->ruolo = $ruolo;

        return $this;
    }

    public function getRuolo(): ?string {
        return $this->ruolo;
    }

    public function setEmailPec(?string $emailPec): self {
        $this->email_pec = $emailPec;

        return $this;
    }

    public function getEmailPec(): ?string {
        return $this->email_pec;
    }

    public function setPersona(?Persona $persona): self {
        $this->persona = $persona;

        return $this;
    }

    public function getPersona(): ?Persona {
        return $this->persona;
    }

    public function setProponente(?Proponente $proponente): self {
        $this->proponente = $proponente;

        return $this;
    }

    public function getProponente(): ?Proponente {
        return $this->proponente;
    }
}
