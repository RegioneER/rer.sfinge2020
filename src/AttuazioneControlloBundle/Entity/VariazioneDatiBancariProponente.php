<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use BaseBundle\Entity\Id;
use Doctrine\ORM\Mapping as ORM;
use RichiesteBundle\Entity\Proponente;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="variazioni_dati_bancari")
 */
class VariazioneDatiBancariProponente extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     * @var string|null
     */
    protected $banca;

    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     * @var string|null
     */
    protected $banca_precedente;

    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     * @var string|null
     */
    protected $intestatario;

    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     * @var string|null
     */
    protected $intestatario_precedente;

    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     * @var string|null
     */
    protected $agenzia;

    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     * @var string|null
     */
    protected $agenzia_precedente;

    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     * @Assert\Iban
     * @Assert\Length(min="27", max="27", exactMessage="L'IBAN deve contenere {{ limit }}caratteri, assicurati di non aver inserito spazi")
     * @var string|null
     */
    protected $iban;

    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     * @var string|null
     */
    protected $iban_precedente;

    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    protected $conto_tesoreria;

    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    protected $conto_tesoreria_precedente;

    /**
     * @ORM\ManyToOne(targetEntity="DatiBancari", inversedBy="variazioni")
     * @ORM\JoinColumn(nullable=false)
     * @var DatiBancari|null
     */
    protected $dati_bancari;

    /**
     * @ORM\ManyToOne(targetEntity="VariazioneDatiBancari", inversedBy="datiBancari")
     * @ORM\JoinColumn(nullable=false)
     * @var VariazioneDatiBancari
     */
    protected $variazione;

    public function __construct(VariazioneDatiBancari $variazione, DatiBancari $datiBancari) {
        $this->variazione = $variazione;
        $this->dati_bancari = $datiBancari;

        $this->banca_precedente = $datiBancari->getBanca();
        $this->intestatario_precedente = $datiBancari->getIntestatario();
        $this->agenzia_precedente = $datiBancari->getAgenzia();
        $this->iban_precedente = $datiBancari->getIban();
        $this->conto_tesoreria_precedente = $datiBancari->getContoTesoreria();
    }

    public function setBanca(?string $banca): self {
        $this->banca = $banca;

        return $this;
    }

    public function getBanca(): ?string {
        return $this->banca;
    }

    public function setIntestatario(?string $intestatario): self {
        $this->intestatario = $intestatario;

        return $this;
    }

    public function getIntestatario(): ?string {
        return $this->intestatario;
    }

    public function setAgenzia(?string $agenzia): self {
        $this->agenzia = $agenzia;

        return $this;
    }

    public function getAgenzia(): ?string {
        return $this->agenzia;
    }

    public function setIban(?string $iban): self {
        $this->iban = $iban;

        return $this;
    }

    public function getIban(): ?string {
        return $this->iban;
    }

    public function setContoTesoreria(?string $contoTesoreria): self {
        $this->conto_tesoreria = $contoTesoreria;

        return $this;
    }

    public function getContoTesoreria(): ?string {
        return $this->conto_tesoreria;
    }

    public function setVariazione(VariazioneDatiBancari $variazione): self {
        $this->variazione = $variazione;

        return $this;
    }

    public function getVariazione(): ?VariazioneDatiBancari {
        return $this->variazione;
    }

    public function setDatiBancari(DatiBancari $datiBancari): self {
        $this->dati_bancari = $datiBancari;

        return $this;
    }

    public function getDatiBancari(): ?DatiBancari {
        return $this->dati_bancari;
    }

    /**
     * @Assert\IsTrue(message="Inserire oppure l'IBAN o il conto di tesoreria")
     */
    public function isIbanValid(): bool {
        return empty($this->iban) xor empty($this->conto_tesoreria);
    }

    public function getDataInvioVariazione(): ?\DateTime {
        return $this->variazione->getDataInvio();
    }

    public function getProponente(): ?Proponente {
        return $this->dati_bancari->getProponente();
    }

    public function applica(): void {
        $this->dati_bancari->setBanca($this->banca);
        $this->dati_bancari->setIntestatario($this->intestatario);
        $this->dati_bancari->setAgenzia($this->agenzia);
        $this->dati_bancari->setIban($this->iban);
        $this->dati_bancari->setContoTesoreria($this->conto_tesoreria);
    }

    public function setBancaPrecedente(?string $bancaPrecedente): self {
        $this->banca_precedente = $bancaPrecedente;

        return $this;
    }

    public function getBancaPrecedente(): ?string {
        return $this->banca_precedente;
    }

    public function setIntestatarioPrecedente(?string $intestatarioPrecedente): self {
        $this->intestatario_precedente = $intestatarioPrecedente;

        return $this;
    }

    public function getIntestatarioPrecedente(): ?string {
        return $this->intestatario_precedente;
    }

    public function setAgenziaPrecedente(?string $agenziaPrecedente): self {
        $this->agenzia_precedente = $agenziaPrecedente;

        return $this;
    }

    public function getAgenziaPrecedente(): ?string {
        return $this->agenzia_precedente;
    }

    public function setIbanPrecedente(?string $ibanPrecedente): self {
        $this->iban_precedente = $ibanPrecedente;

        return $this;
    }

    public function getIbanPrecedente(): ?string {
        return $this->iban_precedente;
    }

    public function setContoTesoreriaPrecedente(?string $contoTesoreriaPrecedente): self {
        $this->conto_tesoreria_precedente = $contoTesoreriaPrecedente;

        return $this;
    }

    public function getContoTesoreriaPrecedente(): ?string {
        return $this->conto_tesoreria_precedente;
    }
}
