<?php

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="vista_fn06")
 */
class VistaFN06 {
    use StrutturaRichiestaTrait;
    use HasCodLocaleProgetto;

    /**
     * @ORM\ManyToOne(targetEntity="TC39CausalePagamento")
     * @ORM\JoinColumn(nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @var TC39CausalePagamento|null
     */
    protected $tc39_causale_pagamento;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Assert\Length(max=20, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"Default", "esportazione_monitoraggio"})
     * @Assert\notNull(groups={"Default", "esportazione_monitoraggio"})
     * @var string
     */
    protected $cod_pagamento;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Assert\Length(max=5, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Regex(pattern="/^(P|R|P-TR|R-TR)$/", message="sfinge.monitoraggio.invalidValue", match=true, groups={"Default", "esportazione_monitoraggio"})
     * @var string
     */
    protected $tipologia_pag;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Date(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @var \DateTime
     */
    protected $data_pagamento;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\GreaterThan(value=0, groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.greaterThan")
     */
    protected $importo_pag;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     * @Assert\Length(max=1000, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"Default", "esportazione_monitoraggio"})
     * @var string|null
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

    public function setImportoPag($importoPag): self {
        $importo_pulito = str_replace(',', '.', $importoPag);
        $this->importo_pag = (float) $importo_pulito;

        return $this;
    }

    public function getImportoPag() {
        return $this->importo_pag;
    }

    public function setNotePag(?string $notePag): self {
        $this->note_pag = $notePag;

        return $this;
    }

    public function getNotePag(): ?string {
        return $this->note_pag;
    }

    public function setTc39CausalePagamento(?TC39CausalePagamento $tc39CausalePagamento): self {
        $this->tc39_causale_pagamento = $tc39CausalePagamento;

        return $this;
    }

    public function getTc39CausalePagamento(): ?TC39CausalePagamento {
        return $this->tc39_causale_pagamento;
    }
}
