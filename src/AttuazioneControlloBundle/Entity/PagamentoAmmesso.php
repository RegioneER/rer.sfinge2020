<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use MonitoraggioBundle\Entity\TC39CausalePagamento;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Repository\PagamentoAmmessoRepository")
 * @ORM\Table(name="pagamenti_ammessi")
 */
class PagamentoAmmesso extends EntityLoggabileCancellabile {
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="RichiestaPagamento", inversedBy="pagamenti_ammessi")
     * @ORM\JoinColumn(name="richiesta_pagamento_id", nullable=false)
     * @Assert\NotNull
     * @var RichiestaPagamento
     */
    protected $richiesta_pagamento;

    /**
     * @ORM\ManyToOne(targetEntity="RichiestaLivelloGerarchico", inversedBy="pagamenti_ammessi", cascade={"persist"})
     * @ORM\JoinColumn(name="livello_gerarchico_id", nullable=false)
     * @Assert\NotNull
     * @var RichiestaLivelloGerarchico
     */
    protected $livello_gerarchico;

    /**
     * @ORM\Column(type="date", nullable=false)
     * @Assert\NotNull
     * @Assert\Date
     * @var \DateTime
     */
    protected $data_pagamento;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC39CausalePagamento")
     * @ORM\JoinColumn(name="causale_id", nullable=false)
     * @var \MonitoraggioBundle\Entity\TC39CausalePagamento
     */
    protected $causale;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=false)
     * @Assert\NotNull
     * @Assert\GreaterThan(value=0, message="sfinge.monitoraggio.greaterThan")
     * @var float
     */
    protected $importo;

    /**
     * @ORM\Column(type="string", nullable=false, length=5)
     * @Assert\NotNull
     * @Assert\Regex("/^(P|R|P-TR|R-TR)$/")
     */
    protected $tipologia_pagamento;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     * @Assert\Length(max=1000, maxMessage="sfinge.monitoraggio.maxLength")
     * @var string
     */
    protected $note;

    public function __construct(?RichiestaPagamento $pagamento = null, ?RichiestaLivelloGerarchico $liv = null) {
        $this->richiesta_pagamento = $pagamento;
        if(\is_null($liv)){
            $programmi = $pagamento->getRichiesta()->getMonProgrammi();
            if(! $programmi->isEmpty()){
                /** @var RichiestaProgramma $programma */
                $programma = $programmi->first();
                $liv = $programma->getLivelliGerarchiciObiettivoSpecifico()->first() ?: null;
            }
        }
        $this->livello_gerarchico = $liv;
        if(\is_null($pagamento)){
            return;
        }
        $this->aggiornaDaPagamento();

        if(\is_null($liv)){
            $livelli = $pagamento->getRichiesta()->getProcedura()->getLivelliGerarchici();
            $liv = $livelli->first() ?: null;
        }

        $this->livello_gerarchico = $liv;
    }

    public function aggiornaDaPagamento(): void {
        $this->importo = $this->richiesta_pagamento->getImporto();
        $this->data_pagamento = $this->richiesta_pagamento->getDataPagamento();
        $this->tipologia_pagamento = $this->richiesta_pagamento->getTipologiaPagamento();
        $this->causale = $this->richiesta_pagamento->getCausalePagamento();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    public function setDataPagamento(\DateTime $dataPagamento = null): self {
        $this->data_pagamento = $dataPagamento;

        return $this;
    }

    public function getDataPagamento(): ?\DateTime {
        return $this->data_pagamento;
    }

    /**
     * @param string $importo
     */
    public function setImporto($importo): self {
        $this->importo = $importo;

        return $this;
    }

    /**
     * @return string
     */
    public function getImporto() {
        return $this->importo;
    }

    public function setNote(?string $note): self {
        $this->note = $note;

        return $this;
    }

    public function getNote(): ?string {
        return $this->note;
    }

    public function setRichiestaPagamento(?RichiestaPagamento $richiestaPagamento = null): self {
        $this->richiesta_pagamento = $richiestaPagamento;

        return $this;
    }

    public function getRichiestaPagamento(): ?RichiestaPagamento {
        return $this->richiesta_pagamento;
    }

    public function setLivelloGerarchico(RichiestaLivelloGerarchico $livelloGerarchico = null): self {
        $this->livello_gerarchico = $livelloGerarchico;

        return $this;
    }

    public function getLivelloGerarchico(): ?RichiestaLivelloGerarchico {
        return $this->livello_gerarchico;
    }

    public function setCausale(?TC39CausalePagamento $causale = null): self {
        $this->causale = $causale;

        return $this;
    }

    public function getCausale(): ?TC39CausalePagamento {
        return $this->causale;
    }

    

    public function setTipologiaPagamento(string $tipologiaPagamento): self {
        $this->tipologia_pagamento = $tipologiaPagamento;

        return $this;
    }

    public function getTipologiaPagamento(): ?string {
        return $this->tipologia_pagamento;
    }
}
