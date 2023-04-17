<?php

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="vista_fn09")
 */
class VistaFN09 {
    use StrutturaRichiestaTrait;
    use HasCodLocaleProgetto;

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
     * @ORM\Column(type="datetime", nullable=false)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Date(groups={"esportazione_monitoraggio", "Default"})
     */
    protected $data_pagamento;
    
    /**
     * @ORM\ManyToOne(targetEntity="TC36LivelloGerarchico")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc36_livello_gerarchico;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\GreaterThan(value=0, groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.greaterThan")
     * @var string
     */
    protected $importo_totale;
    
     /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\GreaterThan(value=0, groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.greaterThan")
     * @var string
     */
    protected $importo_spesa_pubblica;
    
    public function getCodPagamento(): string {
        return $this->cod_pagamento;
    }

    public function getTipologiaPag(): string {
        return $this->tipologia_pag;
    }

    public function getDataPagamento(): \DateTime {
        return $this->data_pagamento;
    }

    public function getTc36LivelloGerarchico() {
        return $this->tc36_livello_gerarchico;
    }

    public function getImportoTotale(): string {
        return $this->importo_totale;
    }

    public function getImportoSpesaPubblica(): string {
        return $this->importo_spesa_pubblica;
    }

    public function setCodPagamento(string $cod_pagamento): void {
        $this->cod_pagamento = $cod_pagamento;
    }

    public function setTipologiaPag(string $tipologia_pag): void {
        $this->tipologia_pag = $tipologia_pag;
    }

    public function setDataPagamento(\DateTime $data_pagamento): void {
        $this->data_pagamento = $data_pagamento;
    }

    public function setTc36LivelloGerarchico($tc36_livello_gerarchico): void {
        $this->tc36_livello_gerarchico = $tc36_livello_gerarchico;
    }

    public function setImportoTotale(string $importo_totale): void {
        $this->importo_totale = $importo_totale;
    }

    public function setImportoSpesaPubblica(string $importo_spesa_pubblica): void {
        $this->importo_spesa_pubblica = $importo_spesa_pubblica;
    }



}
