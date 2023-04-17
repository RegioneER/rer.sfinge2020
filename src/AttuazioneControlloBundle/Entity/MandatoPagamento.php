<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use RichiesteBundle\Entity\Richiesta;

/**
 * @ORM\Entity()
 * @ORM\Table(name="mandati_pagamenti")
 */
class MandatoPagamento extends EntityLoggabileCancellabile {
    
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
	/**
	 * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", mappedBy="mandato_pagamento")
     * @var Pagamento
	 */
	protected $pagamento;     
    
    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @Assert\NotBlank
     * @var \DateTime
     */
    protected $data_mandato;
   
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     * @Assert\NotBlank
	 */
	protected $importo_pagato;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\NotBlank
     */
    protected $numero_mandato;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $note;
    
    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     * @Assert\NotBlank
     */
    protected $quota_fesr; 
    
    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     * @Assert\NotBlank
     */
    protected $quota_regione;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     * @Assert\NotBlank
     */
    protected $quota_stato;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\AttoLiquidazione")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank
     * @var AttoLiquidazione
    */
    protected $atto_liquidazione;

    public function getId() {
        return $this->id;
    }

    public function getDataMandato() {
        return $this->data_mandato;
    }

    public function getImportoPagato() {
        return $this->importo_pagato;
    }

    public function getNumeroMandato() {
        return $this->numero_mandato;
    }

    public function getNote() {
        return $this->note;
    }

    public function getAttoLiquidazione() {
        return $this->atto_liquidazione;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setDataMandato($data_mandato) {
        $this->data_mandato = $data_mandato;
        return $this;
    }

    public function setImportoPagato($importo_pagato) {
        $this->importo_pagato = $importo_pagato;
        return $this;
    }

    public function setNumeroMandato($numero_mandato) {
        $this->numero_mandato = $numero_mandato;
        return $this;
    }

    public function setNote($note) {
        $this->note = $note;
        return $this;
    }

    public function setAttoLiquidazione($atto_liquidazione) {
        $this->atto_liquidazione = $atto_liquidazione;
        return $this;
    }

    function getPagamento(): ?Pagamento {
        return $this->pagamento;
    }

    function setPagamento($pagamento): self {
        $this->pagamento = $pagamento;
        return $this;
    }
    

    /**
     * Set quota_fesr
     *
     * @param string $quotaFesr
     * @return MandatoPagamento
     */
    public function setQuotaFesr($quotaFesr)
    {
        $this->quota_fesr = $quotaFesr;

        return $this;
    }

    /**
     * Get quota_fesr
     *
     * @return string 
     */
    public function getQuotaFesr()
    {
        return $this->quota_fesr;
    }

    /**
     * Set quota_regione
     *
     * @param string $quotaRegione
     * @return MandatoPagamento
     */
    public function setQuotaRegione($quotaRegione)
    {
        $this->quota_regione = $quotaRegione;

        return $this;
    }

    /**
     * Get quota_regione
     *
     * @return string 
     */
    public function getQuotaRegione()
    {
        return $this->quota_regione;
    }

    /**
     * Set quota_stato
     *
     * @param string $quotaStato
     * @return MandatoPagamento
     */
    public function setQuotaStato($quotaStato)
    {
        $this->quota_stato = $quotaStato;

        return $this;
    }

    /**
     * Get quota_stato
     *
     * @return string 
     */
    public function getQuotaStato()
    {
        return $this->quota_stato;
    }

    /**
     * @return Richiesta
     */
    public function getRichiesta(){
        return $this->pagamento->getRichiesta();
    }
}
