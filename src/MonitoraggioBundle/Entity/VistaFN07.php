<?php

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="vista_fn07")
 */
class VistaFN07 {
    use StrutturaRichiestaTrait;
    use HasCodLocaleProgetto;

    /**
     * @ORM\ManyToOne(targetEntity="TC4Programma")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc4_programma;

    /**
     * @ORM\ManyToOne(targetEntity="TC39CausalePagamento")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc39_causale_pagamento;

    /**
     * @ORM\ManyToOne(targetEntity="TC36LivelloGerarchico")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc36_livello_gerarchico;

    /**
     * @ORM\Column(type="string", length=20, nullable=false)
     * @Assert\Length(max=20, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $cod_pagamento;

    /**
     * @ORM\Column(type="string", length=5, nullable=false)
     * @Assert\Length(max=5, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"esportazione_monitoraggio", "Default"})
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tipologia_pag;

    /**
     * @ORM\Column(type="date", nullable=false)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Date(groups={"esportazione_monitoraggio", "Default"})
     */
    protected $data_pagamento;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Date(groups={"esportazione_monitoraggio", "Default"})
     */
    protected $data_pag_amm;

    /**
     * @ORM\Column(type="string", length=5, nullable=false)
     * @Assert\Length(max=5, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"esportazione_monitoraggio", "Default"})
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tipologia_pag_amm;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\GreaterThan(value=0, groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.greaterThan")
     */
    protected $importo_pag_amm;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     * @Assert\Length(max=1000, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"esportazione_monitoraggio", "Default"})
     */
    protected $note_pag;

    public function setCodPagamento(string $codPagamento): self {
        $this->cod_pagamento = $codPagamento;

        return $this;
    }

    public function getCodPagamento(): ?string {
        return $this->cod_pagamento;
    }

    public function setTipologiaPag(string $tipologiaPag): self {
        $this->tipologia_pag = $tipologiaPag;

        return $this;
    }

    public function getTipologiaPag(): ?string {
        return $this->tipologia_pag;
    }

    public function setDataPagamento(\DateTime $dataPagamento): self {
        $this->data_pagamento = $dataPagamento;

        return $this;
    }

    public function getDataPagamento(): ?\DateTime {
        return $this->data_pagamento;
    }

    public function setDataPagAmm(\DateTime $dataPagAmm): self {
        $this->data_pag_amm = $dataPagAmm;

        return $this;
    }

    public function getDataPagAmm(): ?\DateTime {
        return $this->data_pag_amm;
    }

    public function setTipologiaPagAmm(string $tipologiaPagAmm): self {
        $this->tipologia_pag_amm = $tipologiaPagAmm;

        return $this;
    }

    public function getTipologiaPagAmm(): ?string {
        return $this->tipologia_pag_amm;
    }

    public function setImportoPagAmm($importoPagAmm): self {
        $importo_pulito = str_replace(',', '.', $importoPagAmm);
        $this->importo_pag_amm = (float) $importo_pulito;

        return $this;
    }

    public function getImportoPagAmm() {
        return $this->importo_pag_amm;
    }

    public function setNotePag(?string $notePag): self {
        $this->note_pag = $notePag;

        return $this;
    }

    public function getNotePag(): ?string {
        return $this->note_pag;
    }

    public function setTc4Programma(TC4Programma $tc4Programma): self {
        $this->tc4_programma = $tc4Programma;

        return $this;
    }

    public function getTc4Programma(): ?TC4Programma {
        return $this->tc4_programma;
    }

    public function setTc39CausalePagamento(TC39CausalePagamento $tc39CausalePagamento): self {
        $this->tc39_causale_pagamento = $tc39CausalePagamento;

        return $this;
    }

    public function getTc39CausalePagamento(): ?TC39CausalePagamento {
        return $this->tc39_causale_pagamento;
    }

    public function setTc36LivelloGerarchico(TC36LivelloGerarchico $tc36LivelloGerarchico): self {
        $this->tc36_livello_gerarchico = $tc36LivelloGerarchico;

        return $this;
    }

    public function getTc36LivelloGerarchico(): ?TC36LivelloGerarchico {
        return $this->tc36_livello_gerarchico;
    }
}
