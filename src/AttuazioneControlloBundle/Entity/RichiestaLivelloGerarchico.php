<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use MonitoraggioBundle\Entity\TC36LivelloGerarchico;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Repository\RichiestaLivelloGerarchicoRepository")
 * @ORM\Table(name="richieste_livelli_gerarchici",
 * uniqueConstraints={
 *      @ORM\UniqueConstraint(columns={"richiesta_programma_id", "tc36_livello_gerarchico_id", "data_cancellazione"})
 * }))
 * 
 * @author lfontana
 */
class RichiestaLivelloGerarchico extends EntityLoggabileCancellabile {
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="RichiestaProgramma", inversedBy="mon_livelli_gerarchici")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     * @var RichiestaProgramma
     */
    protected $richiesta_programma;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC36LivelloGerarchico")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     */
    protected $tc36_livello_gerarchico;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     * @Assert\GreaterThan(value=0)
     */
    protected $importo_costo_ammesso;

    /**
     * @ORM\OneToMany(targetEntity="ImpegniAmmessi", mappedBy="richiesta_livello_gerarchico")
     * @var Collection|ImpegniAmmessi[]
     */
    protected $impegni_ammessi;

    /**
     * @ORM\OneToMany(targetEntity="PagamentoAmmesso", mappedBy="livello_gerarchico")
     * @var Collection|PagamentoAmmesso[]
     */
    protected $pagamenti_ammessi;

    public function __construct(RichiestaProgramma $richiestaProgramma = null, TC36LivelloGerarchico $livelloGerarchico = null) {
        $this->richiesta_programma = $richiestaProgramma;
        $this->tc36_livello_gerarchico = $livelloGerarchico;
        $this->impegni_ammessi = new ArrayCollection();
        $this->pagamenti_ammessi = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param string $importoCostoAmmesso
     */
    public function setImportoCostoAmmesso($importoCostoAmmesso): self {
        $this->importo_costo_ammesso = $importoCostoAmmesso;

        return $this;
    }

    /**
     * @return string
     */
    public function getImportoCostoAmmesso() {
        return $this->importo_costo_ammesso;
    }

    public function setRichiestaProgramma(RichiestaProgramma $richiestaProgramma): self {
        $this->richiesta_programma = $richiestaProgramma;

        return $this;
    }

    public function getRichiestaProgramma(): ?RichiestaProgramma {
        return $this->richiesta_programma;
    }

    public function setTc36LivelloGerarchico(TC36LivelloGerarchico $tc36LivelloGerarchico): self {
        $this->tc36_livello_gerarchico = $tc36LivelloGerarchico;

        return $this;
    }

    public function getTc36LivelloGerarchico(): ?TC36LivelloGerarchico {
        return $this->tc36_livello_gerarchico;
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->tc36_livello_gerarchico->__toString();
    }

    public function addImpegniAmmessi(ImpegniAmmessi $impegniAmmessi): self {
        $this->impegni_ammessi[] = $impegniAmmessi;

        return $this;
    }

    public function removeImpegniAmmessi(ImpegniAmmessi $impegniAmmessi): void {
        $this->impegni_ammessi->removeElement($impegniAmmessi);
    }

    /**
     * @return Collection|ImpegniAmmessi[]
     */
    public function getImpegniAmmessi(): Collection {
        return $this->impegni_ammessi;
    }

    public function addPagamentiAmmessi(PagamentoAmmesso $pagamentiAmmessi): self {
        $this->pagamenti_ammessi[] = $pagamentiAmmessi;

        return $this;
    }

    public function removePagamentiAmmessi(PagamentoAmmesso $pagamentiAmmessi): void {
        $this->pagamenti_ammessi->removeElement($pagamentiAmmessi);
    }

    /**
     * @return Collection|PagamentoAmmesso[]
     */
    public function getPagamentiAmmessi(): Collection {
        return $this->pagamenti_ammessi;
    }
}
