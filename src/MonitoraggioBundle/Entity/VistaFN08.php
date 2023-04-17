<?php

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="vista_fn08")
 */
class VistaFN08 {
    use StrutturaRichiestaTrait;
    use HasCodLocaleProgetto;

    /**
     * @ORM\ManyToOne(targetEntity="TC40TipoPercettore")
     * @ORM\JoinColumn(nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @var TC40TipoPercettore
     */
    protected $tc40_tipo_percettore;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Length(max=20, maxMessage="sfinge.monitoraggio.maxLength")
     * @var string
     */
    protected $cod_pagamento;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Length(max=20, maxMessage="sfinge.monitoraggio.maxLength")
     * @Assert\Regex(pattern="/^(P|P-TR|R|R-TR)$/", match=true, message="sfinge.monitoraggio.invalidValue")
     * @var string
     */
    protected $tipologia_pag;

    /**
     * @ORM\Id
     * @ORM\Column(type="date", nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Date(groups={"esportazione_monitoraggio", "Default"})
     * @var \DateTime
     */
    protected $data_pagamento;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=16, nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Length(min=1, minMessage="sfinge.monitoraggio.minLength")
     * @var string
     */
    protected $codice_fiscale;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     * @Assert\Regex(pattern="/^(S|N)$/", match=true, message="sfinge.monitoraggio.invalidValue")
     * @var string
     */
    protected $flag_soggetto_pubblico;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\GreaterThan(value=0, groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.greaterThan")
     * @var string
     */
    protected $importo;

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

    public function setCodiceFiscale(string $codiceFiscale): self {
        $this->codice_fiscale = $codiceFiscale;

        return $this;
    }

    public function getCodiceFiscale(): ?string {
        return $this->codice_fiscale;
    }

    /**
     * @param string $flagSoggettoPubblico
     */
    public function setFlagSoggettoPubblico($flagSoggettoPubblico): self {
        $this->flag_soggetto_pubblico = $flagSoggettoPubblico;

        return $this;
    }

    public function getFlagSoggettoPubblico(): ?string {
        return $this->flag_soggetto_pubblico;
    }

    public function setImporto($importo): self {
        $importo_pulito = str_replace(',', '.', $importo);
        $this->importo = $importo_pulito;

        return $this;
    }

    /**
     * @return string|float
     */
    public function getImporto() {
        return $this->importo;
    }

    public function setTc40TipoPercettore(?TC40TipoPercettore $tc40TipoPercettore): self {
        $this->tc40_tipo_percettore = $tc40TipoPercettore;

        return $this;
    }

    public function getTc40TipoPercettore(): ?TC40TipoPercettore {
        return $this->tc40_tipo_percettore;
    }
}
