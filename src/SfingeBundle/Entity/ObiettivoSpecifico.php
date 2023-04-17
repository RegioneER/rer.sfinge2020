<?php

namespace SfingeBundle\Entity;

use BaseBundle\Entity\EntityTipo;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use MonitoraggioBundle\Entity\TC36LivelloGerarchico;
use MonitoraggioBundle\Entity\TC12Classificazione;
use MonitoraggioBundle\Entity\IndicatoriRisultatoObiettivoSpecifico;

/**
 * @ORM\Entity
 * @ORM\Table(name="obiettivi_specifici",
 *     indexes={
 *         @ORM\Index(name="idx_asse_id", columns={"asse_id"}),
 *     })
 */
class ObiettivoSpecifico extends EntityTipo {
    /**
     * @ORM\ManyToOne(targetEntity="Asse")
     * @ORM\JoinColumn(name="asse_id", referencedColumnName="id", nullable=false)
     * @var Asse|null
     */
    protected $asse;

    /**
     * @ORM\ManyToMany(targetEntity="Procedura", mappedBy="obiettivi_specifici", cascade={"all"})
     * @var Procedura[]|Collection
     */
    protected $procedure;

    /**
     * @ORM\OneToMany(targetEntity="Azione", mappedBy="obiettivo_specifico")
     * @var Azione[]|Collection
     */
    protected $azioni;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC36LivelloGerarchico", inversedBy="obiettivi_specifici")
     * @ORM\JoinColumn(nullable=true)
     * @var TC36LivelloGerarchico|null
     */
    protected $livello_gerarchico;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC12Classificazione")
     * @ORM\JoinColumn(nullable=true)
     * @var TC12Classificazione|null
     */
    protected $classificazione;

    /**
     * @ORM\OneToMany(targetEntity="MonitoraggioBundle\Entity\IndicatoriRisultatoObiettivoSpecifico", mappedBy="obiettivoSpecifico")
     * @ORM\JoinColumn(nullable=true)
     * @var IndicatoriRisultatoObiettivoSpecifico[]|Collection
     */
    protected $associazioni_indicatori_risultato;

    public function __construct() {
        $this->procedure = new ArrayCollection();
        $this->azioni = new ArrayCollection();
        $this->associazioni_indicatori_risultato = new ArrayCollection();
    }

    public function getAsse(): ?Asse {
        return $this->asse;
    }

    public function setAsse(?Asse $asse): self {
        $this->asse = $asse;

        return $this;
    }

    /**
     * @return Procedura[]|Collection
     */
    public function getProcedure(): Collection {
        return $this->procedure;
    }

    public function setProcedure(Collection $procedure): self {
        $this->procedure = $procedure;

        return $this;
    }

    public function __toString() {
        return $this->getCodice() . " - " . $this->getDescrizione();
    }

    public function addProcedure(Procedura $procedure): self {
        $this->procedure[] = $procedure;

        return $this;
    }

    public function removeProcedure(Procedura $procedure): void {
        $this->procedure->removeElement($procedure);
    }

    public function addAzioni(Azione $azioni): self {
        $this->azioni[] = $azioni;

        return $this;
    }

    public function removeAzioni(Azione $azioni): void {
        $this->azioni->removeElement($azioni);
    }

    public function getAzioni(): Collection {
        return $this->azioni;
    }

    public function setLivelloGerarchico(?TC36LivelloGerarchico $livelloGerarchico): self {
        $this->livello_gerarchico = $livelloGerarchico;

        return $this;
    }

    public function getLivelloGerarchico(): ?TC36LivelloGerarchico {
        return $this->livello_gerarchico;
    }

    public function setClassificazione(?TC12Classificazione $classificazione): self {
        $this->classificazione = $classificazione;

        return $this;
    }

    public function getClassificazione(): ?TC12Classificazione {
        return $this->classificazione;
    }

    public function addAssociazioniIndicatoriRisultato(IndicatoriRisultatoObiettivoSpecifico $associazioniIndicatoriRisultato): self {
        $this->associazioni_indicatori_risultato[] = $associazioniIndicatoriRisultato;

        return $this;
    }

    public function removeAssociazioniIndicatoriRisultato(IndicatoriRisultatoObiettivoSpecifico $associazioniIndicatoriRisultato): void {
        $this->associazioni_indicatori_risultato->removeElement($associazioniIndicatoriRisultato);
    }

    public function getAssociazioniIndicatoriRisultato(): Collection {
        return $this->associazioni_indicatori_risultato;
    }
}
