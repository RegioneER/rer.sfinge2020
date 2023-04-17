<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use MonitoraggioBundle\Entity\TC40TipoPercettore;

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Repository\PagamentiPercettoriRepository")
 * @ORM\Table(name="pagamenti_percettori")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="tipo", type="string")
 * @ORM\DiscriminatorMap({"GENERICO": "PagamentiPercettori",
 *     "SOGGETTO": "PagamentiPercettoriSoggetto",
 *     "GIUSTIFICATIVO_PAGAMENTO": "PagamentiPercettoriGiustificativo"
 * })
 */
class PagamentiPercettori extends EntityLoggabileCancellabile {
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    protected $codice_fiscale;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\RichiestaPagamento", inversedBy="percettori", cascade={"persist"})
     * @ORM\JoinColumn(name="richiesta_pagamento_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull
     * @var RichiestaPagamento|null
     */
    protected $pagamento;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC40TipoPercettore")
     * @ORM\JoinColumn(name="tipo_percettore_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull
     * @var TC40TipoPercettore|null
     */
    protected $tipo_percettore;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=false)
     * @Assert\NotNull
     * @Assert\GreaterThan(value=0, message="sfinge.monitoraggio.greaterThan")
     */
    protected $importo;

    /**
     * @var bool|null
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $soggetto_pubblico;

    public function getId() {
        return $this->id;
    }

    public function getPagamento(): ?RichiestaPagamento {
        return $this->pagamento;
    }

    public function getTipoPercettore(): ?TC40TipoPercettore {
        return $this->tipo_percettore;
    }

    public function getImporto() {
        return $this->importo;
    }

    public function setId($id): self {
        $this->id = $id;

        return $this;
    }

    public function setPagamento(RichiestaPagamento $pagamento): self {
        $this->pagamento = $pagamento;

        return $this;
    }

    public function setTipoPercettore(TC40TipoPercettore $tipo_percettore): self {
        $this->tipo_percettore = $tipo_percettore;

        return $this;
    }

    public function setImporto($importo): self {
        $this->importo = $importo;

        return $this;
    }

    public function setCodiceFiscale(string $codiceFiscale): self {
        $this->codice_fiscale = $codiceFiscale;

        return $this;
    }

    public function getCodiceFiscale(): ?string {
        return $this->codice_fiscale;
    }

    public function __construct(?RichiestaPagamento $p = null, ?TC40TipoPercettore $tipo = null, ?string $cf = null, ?bool $soggetto_pubblico = null, ?float $importo = null){
        $this->pagamento = $p;
        $this->tipo_percettore = $tipo;
        $this->codice_fiscale = $cf;
        $this->soggetto_pubblico = $soggetto_pubblico;
        $this->importo = $importo;
    }

    public function setSoggettoPubblico(bool $soggettoPubblico): self
    {
        $this->soggetto_pubblico = $soggettoPubblico;

        return $this;
    }

    public function getSoggettoPubblico(): ?bool
    {
        return $this->soggetto_pubblico;
    }
}
