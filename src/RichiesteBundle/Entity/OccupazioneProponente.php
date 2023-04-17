<?php

namespace RichiesteBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="occupazione_proponenti",
 *     indexes={
 *         @ORM\Index(name="idx_proponente_id", columns={"proponente_id"})
 *     }
 * )
 */
class OccupazioneProponente extends EntityLoggabileCancellabile {
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="RichiesteBundle\Entity\Proponente", inversedBy="occupazione")
     * @ORM\JoinColumn(name="proponente_id", referencedColumnName="id", nullable=false)
     * @var Proponente|null
     */
    private $proponente;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var int|null
     */
    protected $numero_dipendenti_attuale;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var int|null
     */
    protected $numero_dipendenti_da_assumere;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var bool
     */
    protected $richiesta_maggiorazione_contributo;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @var bool
     */
    protected $spin_off_universitario;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @var bool
     */
    protected $sviluppo_rete_sistema;

    public function __construct(Proponente $proponente = null) {
        $this->proponente = $proponente;
    }

    public function getId() {
        return $this->id;
    }

    public function getProponente(): ?Proponente {
        return $this->proponente;
    }

    public function getNumeroDipendentiAttuale(): ?int {
        return $this->numero_dipendenti_attuale;
    }

    public function getRichiestaMaggiorazioneContributo() {
        return $this->richiesta_maggiorazione_contributo;
    }

    public function setProponente(Proponente $proponente) {
        $this->proponente = $proponente;
    }

    public function setNumeroDipendentiAttuale($numero_dipendenti_attuale) {
        $this->numero_dipendenti_attuale = $numero_dipendenti_attuale;
    }

    public function setRichiestaMaggiorazioneContributo($richiesta_maggiorazione_contributo) {
        $this->richiesta_maggiorazione_contributo = $richiesta_maggiorazione_contributo;
    }

    /**
     * @param int $numeroDipendentiDaAssumere
     */
    public function setNumeroDipendentiDaAssumere($numeroDipendentiDaAssumere): self {
        $this->numero_dipendenti_da_assumere = $numeroDipendentiDaAssumere;

        return $this;
    }

    /**
     * @return int
     */
    public function getNumeroDipendentiDaAssumere() {
        return $this->numero_dipendenti_da_assumere;
    }

    public function setSpinOffUniversitario(?bool $spinOffUniversitario): self {
        $this->spin_off_universitario = $spinOffUniversitario;

        return $this;
    }

    public function getSpinOffUniversitario(): ?bool {
        return $this->spin_off_universitario;
    }

    public function setSviluppoReteSistema(?bool $sviluppoReteSistema): self {
        $this->sviluppo_rete_sistema = $sviluppoReteSistema;

        return $this;
    }

    public function getSviluppoReteSistema(): ?bool {
        return $this->sviluppo_rete_sistema;
    }
    
    public function hasMaggiorazione() {
        return $this->richiesta_maggiorazione_contributo == true;
    }
}
