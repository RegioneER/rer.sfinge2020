<?php

namespace AttuazioneControlloBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\Common\Collections\Collection;
use RichiesteBundle\Entity\Proponente;

/**
 * @ORM\Entity
 * @ORM\Table(name="dati_bancari")
 * @Assert\Callback(callback="validate")
 */
class DatiBancari extends EntityLoggabileCancellabile {
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="banca", type="string", length=1024, nullable=true)
	 * @var string|null
     */
    protected $banca;

    /**
     * @ORM\Column(name="intestatario", type="string", length=1024, nullable=true)
	 * @var string|null
     */
    protected $intestatario;

    /**
     * @ORM\Column(name="agenzia", type="string", length=1024, nullable=true)
	 * @var string|null
     */
    protected $agenzia;

    /**
     * @ORM\Column(name="iban", type="string", length=1024, nullable=true)
     * @Assert\Iban
     * @Assert\Length(min="27", max="27", exactMessage="L'IBAN deve contenere {{ limit }} caratteri, assicurati di non aver inserito spazi")
	 * @var string|null
     */
    protected $iban;

    /**
     * @ORM\Column(name="conto_tesoreria", type="string", length=1024, nullable=true)
	 * @var string|null
     */
    protected $contoTesoreria;

    /**
     * @ORM\Column(name= "flag_iban_sap", type="boolean", nullable=true)
     */
    protected $flag_iban_sap;

    /**
     * @ORM\Column(name="data_creazione_iban_sap", type="datetime", nullable=true)
     * @var DateTime|null
     */
    protected $data_creazione_iban_sap;

    /**
     * @ORM\Column(name="progressivo_iban_sap", type="string", length=15, nullable=true)
     * @var string|null
     */
    protected $progressivo_iban_sap;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Proponente", inversedBy="datiBancari")
     * @ORM\JoinColumn(name="proponente_id", nullable=false)
	 * @var Proponente|null
     */
    protected $proponente;

    /**
     * @ORM\OneToMany(targetEntity="VariazioneDatiBancariProponente", mappedBy="dati_bancari")
     * @var Collection|VariazioneDatiBancariProponente[]
     */
    protected $variazioni;

    public function __construct() {
        $this->variazioni = new ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getBanca(): ?string {
        return $this->banca;
	}
	
	public function getBancaCorrente(?\DateTime $datarif = null): ?string
	{
		$variazione = $this->getUltimaVariazioneDatiBacariProponenteValida($datarif);
		if(\is_null($variazione)){
			return $this->banca;
		}
		return $variazione->getBanca();
	}

    public function getIntestatario(): ?string {
        return $this->intestatario;
    }

    public function getAgenzia(): ?string {
        return $this->agenzia;
    }

    public function getIban() {
        return $this->iban;
    }

    public function getProponente(): ?Proponente {
        return $this->proponente;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setBanca($banca) {
        $this->banca = $banca;
    }

    public function setIntestatario($intestatario) {
        $this->intestatario = $intestatario;
    }

    public function setAgenzia($agenzia) {
        $this->agenzia = $agenzia;
    }

    public function setIban($iban) {
        $this->iban = $iban;
    }

    public function setProponente($proponente) {
        $this->proponente = $proponente;
    }

    public function getContoTesoreria() {
        return $this->contoTesoreria;
    }

    public function setContoTesoreria($contoTesoreria) {
        $this->contoTesoreria = $contoTesoreria;
    }

    /**
     * @return mixed
     */
    public function getFlagIbanSap()
    {
        return $this->flag_iban_sap;
    }

    /**
     * @param mixed $flag_iban_sap
     */
    public function setFlagIbanSap($flag_iban_sap): void
    {
        $this->flag_iban_sap = $flag_iban_sap;
    }

    /**
     * @return DateTime|null
     */
    public function getDataCreazioneIbanSap(): ?DateTime
    {
        return $this->data_creazione_iban_sap;
    }

    /**
     * @param DateTime|null $data_creazione_iban_sap
     */
    public function setDataCreazioneIbanSap(?DateTime $data_creazione_iban_sap): void
    {
        $this->data_creazione_iban_sap = $data_creazione_iban_sap;
    }

    /**
     * @return string|null
     */
    public function getProgressivoIbanSap(): ?string
    {
        return $this->progressivo_iban_sap;
    }

    /**
     * @param string|null $progressivo_iban_sap
     */
    public function setProgressivoIbanSap(?string $progressivo_iban_sap): void
    {
        $this->progressivo_iban_sap = $progressivo_iban_sap;
    }

    public function validate(\Symfony\Component\Validator\Context\ExecutionContextInterface $context) {
        $iban = $this->getIban();
        $contoTesoreria = $this->getContoTesoreria();

        // XNOR
        if (!(empty($iban) xor empty($contoTesoreria))) {
            $context->buildViolation('Inserire o l\'IBAN o il conto di tesoreria')
                    ->atPath('iban')
                    ->addViolation();
            $context->buildViolation('Inserire o l\'IBAN o il conto di tesoreria')
                    ->atPath('contoTesoreria')
                    ->addViolation();
        }
    }

    public function addVariazioni(VariazioneDatiBancariProponente $variazioni): self {
        $this->variazioni[] = $variazioni;

        return $this;
    }

    public function removeVariazioni(VariazioneDatiBancariProponente $variazioni): void {
        $this->variazioni->removeElement($variazioni);
    }

    /**
     * @return Collection|VariazioneDatiBancariProponente[]
     */
    public function getVariazioni(): Collection {
        return $this->variazioni;
    }

    public function getUltimaVariazioneDatiBacariProponenteValida(\DateTime $dataRiferimento = null): ?VariazioneDatiBancariProponente {
		$dataRiferimento = $dataRiferimento ?? new \DateTime();
		$variazioni = $this->variazioni->filter(function(VariazioneDatiBancariProponente $variazioneDatiBancari) use ($dataRiferimento){
			$variazione = $variazioneDatiBancari->getVariazione();
			$dataVariazione = $variazione->getDataInvio();
			$esito = $variazione->getEsitoIstruttoria();

			return $esito && ($dataVariazione ?? new \DateTime()) < $dataRiferimento;
		});
		/** @var VariazioneDatiBancariProponente|null $ultimaVariazione */
		$ultimaVariazione = \array_reduce($variazioni->toArray(), 
			function(?VariazioneDatiBancariProponente $carry, VariazioneDatiBancariProponente $value) {
				if(\is_null($carry) || \is_null($carry->getDataInvioVariazione())){
					return $value;
				}
				
				if(\is_null($value->getDataInvioVariazione())){
					return $value;
				}
				
				if($value->getDataInvioVariazione() > $carry->getDataInvioVariazione()){
					return $value;
				}

				return $carry;
			}, 
			null
		);
		
		return $ultimaVariazione;
    }
}
