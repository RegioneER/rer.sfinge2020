<?php

namespace Performer\PayERBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Performer\PayERBundle\Exception\AcquistoMarcaDaBolloInviata;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AcquistoMarcaDaBollo
 *
 * @ORM\Entity()
 * @ORM\Table(name="payer_ebollo_acquisto_marca_da_bollo")
 */
class AcquistoMarcaDaBollo
{
    /**
     * @var string|null
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(name="id", type="guid", nullable=false)
     */
    protected $id;

    /**
     * @var RichiestaAcquistoMarcaDaBollo|null
     *
     * @ORM\ManyToOne(targetEntity="RichiestaAcquistoMarcaDaBollo", inversedBy="acquistoMarcaDaBollos")
     * @ORM\JoinColumn(name="richiesta_id", referencedColumnName="id", nullable=true)
     */
    protected $richiesta;

    /**
     * @var string|null
     *
     * @ORM\Column(name="identificativo_pagatore", type="string", length=16, nullable=true)
     * @Assert\Length(max="16")
     */
    protected $identificativoPagatore;

    /**
     * @var string|null
     *
     * @ORM\Column(name="denominazione_pagatore", type="string", length=255, nullable=true)
     * @Assert\Length(max="255")
     */
    protected $denominazionePagatore;

    /**
     * @var string|null
     *
     * @ORM\Column(name="email_pagatore", type="string", length=50, nullable=true)
     * @Assert\Length(max="50")
     */
    protected $emailPagatore;

    /**
     * @var string|null
     *
     * @ORM\Column(name="provincia_residenza_pagatore", type="string", length=2, nullable=false)
     * @Assert\Length(min="2")
     */
    protected $provinciaResidenzaPagatore;

    /**
     * @var MarcaDaBollo|null
     *
     * @ORM\ManyToOne(targetEntity="Performer\PayERBundle\Entity\MarcaDaBollo")
     * @ORM\JoinColumn(name="marca_da_bollo_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNUll
     */
    protected $marcaDaBollo;

    /**
     * @var string|null
     *
     * @ORM\Column(name="nome_documento", type="string", length=255, nullable=true)
     * @Assert\Length(max="255")
     */
    protected $nomeDocumento;

    /**
     * @var string|null
     *
     * @ORM\Column(name="hash_documento", type="string", length=256, nullable=false)
     * @Assert\NotBlank
     * @Assert\Length(max="256")
     */
    protected $hashDocumento;

    /**
     * @var string|null
     *
     * @ORM\Column(name="iuv", type="string", length=255, nullable=true)
     */
    protected $iuv;

    /**
     * @var string|null
     *
     * @ORM\Column(name="id_transazione", type="string", length=255, nullable=true)
     */
    protected $idTransazione;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="data_transazione", type="datetime", nullable=true)
     */
    protected $dataTransazione;

    /**
     * @var EsitoPagamento|null
     *
     * @ORM\ManyToOne(targetEntity="Performer\PayERBundle\Entity\EsitoPagamento")
     * @ORM\JoinColumn(name="esito_pagamento_id", referencedColumnName="id", nullable=true)
     */
    protected $esitoPagamento;

    /**
     * @var string
     *
     * @ORM\Column(name="codice_fiscale_psp", type="string", nullable=true)
     */
    protected $codiceFiscalePsp;

    /**
     * @var string
     *
     * @ORM\Column(name="denominazione_psp", type="string", nullable=true)
     */
    protected $denominazionePsp;

    /**
     * @var string|null
     *
     * @ORM\Column(name="rt", type="text", nullable=true)
     */
    protected $rt;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return self
     */
    public function setId(?string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return RichiestaAcquistoMarcaDaBollo|null
     */
    public function getRichiesta(): ?RichiestaAcquistoMarcaDaBollo
    {
        return $this->richiesta;
    }

    /**
     * @param RichiestaAcquistoMarcaDaBollo|null $richiesta
     * @return self
     * @throws AcquistoMarcaDaBolloInviata
     */
    public function setRichiesta(?RichiestaAcquistoMarcaDaBollo $richiesta): self
    {
        if ($this->isRichiestaInviata()) {
            throw new AcquistoMarcaDaBolloInviata($this);
        }

        $this->richiesta = $richiesta;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIdentificativoPagatore(): ?string
    {
        return $this->identificativoPagatore;
    }

    /**
     * @param string|null $identificativoPagatore
     * @return self
     */
    public function setIdentificativoPagatore(?string $identificativoPagatore): self
    {
        $this->identificativoPagatore = $identificativoPagatore;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDenominazionePagatore(): ?string
    {
        return $this->denominazionePagatore;
    }

    /**
     * @param string|null $denominazionePagatore
     * @return self
     */
    public function setDenominazionePagatore(?string $denominazionePagatore): self
    {
        // 70 caratteri è il limite di Payer
        $this->denominazionePagatore = mb_substr($denominazionePagatore, 0, 70);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmailPagatore(): ?string
    {
        return $this->emailPagatore;
    }

    /**
     * @param string|null $emailPagatore
     * @return self
     */
    public function setEmailPagatore(?string $emailPagatore): self
    {
        $this->emailPagatore = $emailPagatore;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getProvinciaResidenzaPagatore(): ?string
    {
        return $this->provinciaResidenzaPagatore;
    }

    /**
     * @param string|null $provinciaResidenzaPagatore
     * @return self
     */
    public function setProvinciaResidenzaPagatore(?string $provinciaResidenzaPagatore): self
    {
        $this->provinciaResidenzaPagatore = $provinciaResidenzaPagatore;
        return $this;
    }

    /**
     * @return MarcaDaBollo|null
     */
    public function getMarcaDaBollo(): ?MarcaDaBollo
    {
        return $this->marcaDaBollo;
    }

    /**
     * @param MarcaDaBollo|null $marcaDaBollo
     * @return self
     */
    public function setMarcaDaBollo(?MarcaDaBollo $marcaDaBollo): self
    {
        $this->marcaDaBollo = $marcaDaBollo;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNomeDocumento(): ?string
    {
        return $this->nomeDocumento;
    }

    /**
     * @param string|null $nomeDocumento
     * @return self
     */
    public function setNomeDocumento(?string $nomeDocumento): self
    {
        $this->nomeDocumento = $nomeDocumento;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHashDocumento(): ?string
    {
        return $this->hashDocumento;
    }

    /**
     * @param string|null $hashDocumento
     * @return self
     */
    public function setHashDocumento(?string $hashDocumento): self
    {
        $this->hashDocumento = $hashDocumento;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIuv(): ?string
    {
        return $this->iuv;
    }

    /**
     * @param string|null $iuv
     * @return self
     */
    public function setIuv(?string $iuv): self
    {
        $this->iuv = $iuv;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIdTransazione(): ?string
    {
        return $this->idTransazione;
    }

    /**
     * @param string|null $idTransazione
     * @return self
     */
    public function setIdTransazione(?string $idTransazione): self
    {
        $this->idTransazione = $idTransazione;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getDataTransazione(): ?DateTime
    {
        return $this->dataTransazione;
    }

    /**
     * @param DateTime|null $dataTransazione
     * @return self
     */
    public function setDataTransazione(?DateTime $dataTransazione): self
    {
        $this->dataTransazione = $dataTransazione;
        return $this;
    }

    /**
     * @return EsitoPagamento|null
     */
    public function getEsitoPagamento(): ?EsitoPagamento
    {
        return $this->esitoPagamento;
    }

    /**
     * @param EsitoPagamento|null $esitoPagamento
     * @return self
     */
    public function setEsitoPagamento(?EsitoPagamento $esitoPagamento): self
    {
        $this->esitoPagamento = $esitoPagamento;
        return $this;
    }

    /**
     * @return string
     */
    public function getCodiceFiscalePsp(): string
    {
        return $this->codiceFiscalePsp;
    }

    /**
     * @param string $codiceFiscalePsp
     * @return self
     */
    public function setCodiceFiscalePsp(string $codiceFiscalePsp): self
    {
        $this->codiceFiscalePsp = $codiceFiscalePsp;
        return $this;
    }

    /**
     * @return string
     */
    public function getDenominazionePsp(): string
    {
        return $this->denominazionePsp;
    }

    /**
     * @param string $denominazionePsp
     * @return self
     */
    public function setDenominazionePsp(string $denominazionePsp): self
    {
        $this->denominazionePsp = $denominazionePsp;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRt(): ?string
    {
        return $this->rt;
    }

    /**
     * @param string|null $rt
     * @return self
     */
    public function setRt(?string $rt): self
    {
        $this->rt = $rt;
        return $this;
    }

    /**
     * @return bool
     */
    public function isRichiestaInviata(): bool
    {
        return $this->richiesta && $this->richiesta->isInviata();
    }

    /**
     * @return bool
     */
    public function hasEsitoPagamento(): bool
    {
        return $this->esitoPagamento !== null;
    }

    /**
     * @return bool
     */
    public function isPagamentoEseguito(): bool
    {
        return $this->esitoPagamento !== null
            && $this->esitoPagamento->isEseguito()
        ;
    }

    /**
     * @return bool
     */
    public function isPagamentoFallito(): bool
    {
        return
            $this->isRichiestaInviata()
            && ($this->richiesta->hasErroreInvio() || ($this->hasEsitoPagamento() && !$this->isPagamentoEseguito()))
        ;
    }

    /**
     * @return bool
     */
    public function isInAttesaEsitoPagamento(): bool
    {
        return
            $this->isRichiestaInviata()
            && !$this->richiesta->isInTimeout()
            && !$this->richiesta->hasErroreInvio()
            && !$this->hasEsitoPagamento()
        ;
    }
}