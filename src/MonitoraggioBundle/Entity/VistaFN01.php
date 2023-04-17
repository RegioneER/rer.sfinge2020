<?php

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="vista_fn01")
 */
class VistaFn01 {
    use StrutturaRichiestaTrait;
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=60, nullable=false)
     * @Assert\NotNull
     * @var string
     */
    protected $cod_locale_progetto;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="TC4Programma")
     * @ORM\JoinColumn(name="programma_id", referencedColumnName="id", nullable=false)
     * @var TC4Programma
     */
    protected $tc4_programma;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="TC36LivelloGerarchico")
     * @ORM\JoinColumn(name="liv_gerarchico_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull
     * @var TC36LivelloGerarchico
     */
    protected $tc36_livello_gerarchico;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=false)
     * @var string
     */
    protected $importo_ammesso;

    public function setCodLocaleProgetto(string $codLocaleProgetto): self {
        $this->cod_locale_progetto = $codLocaleProgetto;

        return $this;
    }

    public function getCodLocaleProgetto(): ?string {
        return $this->cod_locale_progetto;
    }

    public function setImportoAmmesso(string $importoAmmesso): self {
        $importo_pulito = str_replace(',', '.', $importoAmmesso);
        $this->importo_ammesso = (float) $importo_pulito;

        return $this;
    }

    public function getImportoAmmesso(): ?string {
        return $this->importo_ammesso;
    }

    public function setTc4Programma(TC4Programma $tc4Programma): self {
        $this->tc4_programma = $tc4Programma;

        return $this;
    }

    public function getTc4Programma(): ?TC4Programma {
        return $this->tc4_programma;
    }

    public function setTc36LivelloGerarchico(TC36LivelloGerarchico $tc36LivelloGerarchico): self {
        $this->tc36_livello_gerarchico = $tc36LivelloGerarchico;

        return $this;
    }
}
