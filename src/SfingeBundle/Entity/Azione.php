<?php

namespace SfingeBundle\Entity;

use BaseBundle\Entity\EntityTipo;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\Common\Collections\Collection;
use MonitoraggioBundle\Entity\TC12Classificazione;
use MonitoraggioBundle\Entity\IndicatoriOutputAzioni;
use SfingeBundle\Entity\Procedura;
use SfingeBundle\Entity\ObiettivoSpecifico;

/**
 * @ORM\Entity()
 * @ORM\Table(name="azioni",
 * indexes={
 *      @ORM\Index(name="idx_obiettivo_specifico_id", columns={"obiettivo_specifico_id"}),
 *  })
 */
class Azione extends EntityTipo
{

    /**
     * @ORM\ManyToOne(targetEntity="ObiettivoSpecifico", inversedBy="azioni")
     * @ORM\JoinColumn(name="obiettivo_specifico_id", referencedColumnName="id", nullable=false)
     *
     */
    protected $obiettivo_specifico;

    /**
     *
     * @ORM\ManyToMany(targetEntity="Procedura", mappedBy="azioni", cascade={"all"})
     */
    protected $procedure;

    /**
     * @ORM\OneToMany( targetEntity="MonitoraggioBundle\Entity\IndicatoriOutputAzioni", mappedBy="azione")
     */
    protected $indicatori_output_azioni;

    /**
     * @ORM\ManyToMany(targetEntity="MonitoraggioBundle\Entity\TC12Classificazione", mappedBy="azioni")
     * @var TC12Classificazione|Collection
     */
    protected $classificazioni;

    public function __construct()
    {
        $this->procedure = new ArrayCollection();
        $this->classificazioni = new ArrayCollection();
        $this->indicatori_output_azioni = new ArrayCollection();
    }

    public function getObiettivoSpecifico(): ?ObiettivoSpecifico
    {
        return $this->obiettivo_specifico;
    }

    public function setObiettivoSpecifico(?ObiettivoSpecifico $obiettivo_specifico)
    {
        $this->obiettivo_specifico = $obiettivo_specifico;
    }

    /**
     * @return Collection|Procedura[]
     */
    public function getProcedure(): Collection {
        return $this->procedure;
    }

    /**
     * @param Procedura[]|Collection $procedure
     */
    public function setProcedure(Collection $procedure) {
        $this->procedure = $procedure;
    }

    public function __toString(){
        return $this->getCodice() . " - " . $this->getDescrizione();
    }


    public function addProcedure(Procedura $procedura): self
    {
        $this->procedure[] = $procedura;

        return $this;
    }

    public function removeProcedure(Procedura $procedure): void
    {
        $this->procedure->removeElement($procedure);
    }

    public function addIndicatoriOutputAzioni(IndicatoriOutputAzioni $indicatoriOutputAzioni): self
    {
        $this->indicatori_output_azioni[] = $indicatoriOutputAzioni;

        return $this;
    }

    public function removeIndicatoriOutputAzioni(IndicatoriOutputAzioni $indicatoriOutputAzioni): void
    {
        $this->indicatori_output_azioni->removeElement($indicatoriOutputAzioni);
    }

    /**
     * @return IndicatoriOutputAzioni[]|Collection 
     */
    public function getIndicatoriOutputAzioni(\DateTimeInterface $ref = null): Collection
    {
        if($ref == null){
            $ref = new DateTime();
        }

        $res = $this->indicatori_output_azioni->filter(function(IndicatoriOutputAzioni $ia) use($ref){
            return $ia->isInCorso($ref);
        });

        return $res;
    }

    public function addClassificazioni(TC12Classificazione $classificazioni): self
    {
        $this->classificazioni[] = $classificazioni;

        return $this;
    }

    public function removeClassificazioni(TC12Classificazione $classificazioni): void
    {
        $this->classificazioni->removeElement($classificazioni);
    }

    /**
     * @return Collection|TC12Classificazione[]
     */
    public function getClassificazioni(): Collection
    {
        return $this->classificazioni;
    }
}
