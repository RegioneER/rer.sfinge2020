<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Entity\TC39CausalePagamento;
use Doctrine\Common\Collections\Collection;
use AttuazioneControlloBundle\Entity\Revoche\RataRecupero;

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Repository\RichiestaPagamentoRepository")
 * @ORM\Table(name="richieste_pagamenti")
 */
class RichiestaPagamento extends EntityLoggabileCancellabile {
    const PAGAMENTO = 'P';
    const PAGAMENTO_TRASFERIMENTO = 'P-TR';
    const RETTIFICA = 'R';
    const RETTIFICA_TRASFERIMENTO = 'R-TR';

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="mon_richieste_pagamento")
     * @ORM\JoinColumn(name="richiesta_id", nullable=false)
     * @Assert\NotNull
     * @var Richiesta
     */
    protected $richiesta;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @var string|null
     */
    protected $codice;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Pagamento")
     * @ORM\JoinColumn(name="pagamento_id", nullable=true)
     * @var Pagamento
     */
    protected $pagamento;

    /**
     * @ORM\Column(type="string", length=5, nullable=false)
     * @Assert\NotNull
     * @var string
     */
    protected $tipologia_pagamento;

    /**
     * @ORM\Column(type="date", nullable=false)
     * @Assert\NotNull
     * @Assert\Date
     * @var \DateTime
     */
    protected $data_pagamento;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=false)
     * @Assert\NotNull
     * @Assert\GreaterThan(value=0, message="sfinge.monitoraggio.greaterThan")
     * @var float
     */
    protected $importo;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC39CausalePagamento")
     * @ORM\JoinColumn(name="causale_pagamento_id", nullable=true)
     * @var TC39CausalePagamento
     */
    protected $causale_pagamento;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     * @Assert\Length(max=1000, maxMessage="sfinge.monitoraggio.maxLength")
     * @var string|null
     */
    protected $note;

    /**
     * @ORM\OneToMany(targetEntity="PagamentoAmmesso", mappedBy="richiesta_pagamento", cascade={"persist", "remove"})
     * @var Collection|PagamentoAmmesso
     */
    protected $pagamenti_ammessi;

    /**
     * @ORM\OneToMany(targetEntity="PagamentiPercettori", mappedBy="pagamento", cascade={"persist"})
     * @var Collection|PagamentiPercettori[]
     */
    protected $percettori;

    /**
     * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\Revoche\RataRecupero", inversedBy="pagamento_monitoraggio")
     * @ORM\JoinColumn(nullable=true)
     * @var RataRecupero
     */
    protected $rata_recupero;

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    public function setTipologiaPagamento(string $tipologiaPagamento): self {
        $this->tipologia_pagamento = $tipologiaPagamento;

        return $this;
    }

    public function getTipologiaPagamento(): ?string {
        return $this->tipologia_pagamento;
    }

    public function setDataPagamento(? \DateTime $dataPagamento): self {
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

    public function setRichiesta(Richiesta $richiesta): self {
        $this->richiesta = $richiesta;

        return $this;
    }

    public function getRichiesta(): ?Richiesta {
        return $this->richiesta;
    }

    public function setPagamenti(Pagamento $pagamenti = null): self {
        $this->pagamento = $pagamenti;

        return $this;
    }

    public function getPagamenti(): ?Pagamento {
        return $this->pagamento;
    }

    public function setCausalePagamento(TC39CausalePagamento $causalePagamento = null): self {
        $this->causale_pagamento = $causalePagamento;

        return $this;
    }

    public function getCausalePagamento(): ?TC39CausalePagamento {
        return $this->causale_pagamento;
    }

    public function __construct(Pagamento $pagamento = null, string $tipologia = self::PAGAMENTO) {
        $this->pagamenti_ammessi = new ArrayCollection();
        $this->percettori = new ArrayCollection();
        $this->tipologia_pagamento = $tipologia;
        $this->pagamento = $pagamento;
        if ($pagamento) {
            $this->richiesta = $pagamento->getRichiesta();
            $this->codice = $this->generaCodice();
            $this->causale_pagamento = $pagamento->getModalitaPagamento()->getCausale();
            $this->data_pagamento = $pagamento->getDataInvio();
            $this->importo = $pagamento->getImportoPagamento();
            $this->codice = $pagamento->getId();
        }
    }

    public function generaCodice(): string {
        $count = $this->richiesta->getMonRichiestePagamento()->count() + 1;
        $protocollo = $this->richiesta->getProtocollo() ?: '-';
        $protocollo = $protocollo == '-' ? $this->richiesta->getId() : $protocollo;

        return "{$protocollo}_{$this->tipologia_pagamento}_$count";
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

    public function addPercettori(PagamentiPercettori $percettori): self {
        $this->percettori[] = $percettori;

        return $this;
    }

    public function removePercettori(PagamentiPercettori $percettori): void {
        $this->percettori->removeElement($percettori);
    }

    /**
     * @return Collection|PagamentiPercettori[]
     */
    public function getPercettori(): Collection {
        return $this->percettori;
    }

    public static function createFromPagamento(Pagamento $pagamento): self {
        $res = new self();
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();

        $res->setPagamenti($pagamento)
        ->setRichiesta($richiesta);

        $mandato = $pagamento->getMandatoPagamento();
        if (\is_null($mandato)) {
            return $res;
        }
        $importo = $mandato->getImportoPagato();
        $res->setTipologiaPagamento($importo ? 'P' : 'R')
        ->setImporto(\abs($importo));

        return $res;
    }

    public function setCodice(string $codice): self {
        $this->codice = $codice;

        return $this;
    }

    public function getCodice(): ?string {
        return $this->codice;
    }

    public function setRataRecupero(RataRecupero $rata): self {
        $this->rata_recupero = $rata;
        $this->richiesta = $rata->getRecupero()->getRevoca()->getRichiesta();

        return $this;
    }

    public function getRataRecupero(): ?RataRecupero {
        return $this->rata_recupero;
    }
}
