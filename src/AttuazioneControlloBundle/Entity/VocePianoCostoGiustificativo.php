<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use RichiesteBundle\Entity\VocePianoCosto;

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativoRepository")
 * @ORM\Table(name="voci_piano_costo_giustificativi")
 * @Assert\Callback(callback="validateImportazione",groups={"sanita"})
 */
class VocePianoCostoGiustificativo extends EntityLoggabileCancellabile {

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\GiustificativoPagamento", inversedBy="voci_piano_costo")
     * @ORM\JoinColumn(nullable=false)
     * @var GiustificativoPagamento
     */
    protected $giustificativo_pagamento;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\VocePianoCosto", inversedBy="voci_giustificativi")
     * @ORM\JoinColumn(nullable=false)
     * @var VocePianoCosto|null
     */
    protected $voce_piano_costo;
    
    /**
     * @ORM\ManyToOne(targetEntity="IstruttorieBundle\Entity\IstruttoriaVocePianoCosto")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $voce_piano_costo_istruttoria;
    
    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     * @Assert\GreaterThan(0)
     */
    protected $importo;
    
    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $importo_approvato;    
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */    
    protected $annualita;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */    
    protected $nota;
            
    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $importo_non_ammesso_per_superamento_massimali; 
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */ 
    protected $nota_superamento_massimali;
    
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */ 
    protected $spesa_soggetta_limite_30;
    
    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\DiCui", mappedBy="voce_piano_costo_giustificativo")
     */
    protected $di_cui;
    
    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\DiCui")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $creato_da_di_cui;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $importo_pagamento_successivo;

    
    function __construct() {
        $this->di_cui = new ArrayCollection();
    }
    
    function getId() {
        return $this->id;
    }

    function getGiustificativoPagamento(): ?GiustificativoPagamento {
        return $this->giustificativo_pagamento;
    }

    function getVocePianoCosto(): ?VocePianoCosto {
        return $this->voce_piano_costo;
    }

    function getImporto() {
        return $this->importo;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setGiustificativoPagamento($giustificativo_pagamento) {
        $this->giustificativo_pagamento = $giustificativo_pagamento;
    }

    function setVocePianoCosto(?VocePianoCosto $voce_piano_costo) {
        $this->voce_piano_costo = $voce_piano_costo;
    }

    function setImporto($importo) {
        $this->importo = $importo;
    }
    
    function getAnnualita() {
        return $this->annualita;
    }

    function setAnnualita($annualita) {
        $this->annualita = $annualita;
    }
    
    public function getVocePianoCostoIstruttoria() {
        return $this->voce_piano_costo_istruttoria;
    }

    public function setVocePianoCostoIstruttoria($voce_piano_costo_istruttoria) {
        $this->voce_piano_costo_istruttoria = $voce_piano_costo_istruttoria;
    }

    public function getDenominazioneVoce() {
        return $this->voce_piano_costo->getPianoCosto()->getTitolo();
    }

    public function getSoggetto() {
        return $this->getGiustificativoPagamento()->getSoggetto();
    }
    
    function getImportoApprovato() {
        return $this->importo_approvato;
    }

    public function setImportoApprovato($importo_approvato) {
        $this->importo_approvato = $importo_approvato;
        return $this;
    }
    
    function getNota() {
        return $this->nota;
    }

    function setNota($nota) {
        $this->nota = $nota;
    }
    
    public function __clone() {
        if ($this->id) {
			$this->id = null;
			$this->modificato_da = null;
			$this->creato_da = null;
        }
    }

    function getImportoNonAmmessoPerSuperamentoMassimali() {
        return $this->importo_non_ammesso_per_superamento_massimali;
    }

    function setImportoNonAmmessoPerSuperamentoMassimali($importo_non_ammesso_per_superamento_massimali) {
        $this->importo_non_ammesso_per_superamento_massimali = $importo_non_ammesso_per_superamento_massimali;
    }
    
    function getNotaSuperamentoMassimali() {
        return $this->nota_superamento_massimali;
    }

    function setNotaSuperamentoMassimali($nota_superamento_massimali) {
        $this->nota_superamento_massimali = $nota_superamento_massimali;
    }
    
    function getSpesaSoggettaLimite30() {
        return $this->spesa_soggetta_limite_30;
    }

    function setSpesaSoggettaLimite30($spesa_soggetta_limite_30) {
        $this->spesa_soggetta_limite_30 = $spesa_soggetta_limite_30;
    }

	public function getPagamento(): ?Pagamento {
        return $this->giustificativo_pagamento->getPagamento();
    }
    
    function getDiCui() {
        return $this->di_cui;
    }

    function setDiCui($di_cui) {
        $this->di_cui = $di_cui;
    }

    function addDiCui(DiCui $di_cui){
        $this->di_cui[] = $di_cui;
        return $this;
    }
    
    /**
     * 
     * @return float|null
     */
    function calcolaImportoNonAmmesso() {
        $importoNonAmmesso = null;
        if (!is_null($this->importo_approvato)) {
            $importoNonAmmesso = round($this->importo, 2, PHP_ROUND_HALF_UP) - round($this->importo_approvato, 2, PHP_ROUND_HALF_UP);
            $importoNonAmmesso = round($importoNonAmmesso, 2, PHP_ROUND_HALF_UP);
        }
        
        return $importoNonAmmesso;
    }
    
    public function getRichiesta() {
        return $this->getPagamento()->getRichiesta();
    }
    
    public function getVocePianoCostoGiustificativo() {
        return $this;
    }
    
    
    /*** NON MAPPATI A DB: DI SUPPORTO PER STAMPARE IL PDF SENZA FLUSH DEI DATI ***/
    protected $importo_di_cui;
    protected $importo_approvato_di_cui; 
    protected $nota_di_cui;
    protected $importo_non_ammesso_super_massimali_di_cui;
    
    function getImportoDiCui() {
        return $this->importo_di_cui;
    }

    function getImportoApprovatoDiCui() {
        return $this->importo_approvato_di_cui;
    }

    function getNotaDiCui() {
        return $this->nota_di_cui;
    }

    function setImportoDiCui($importo_di_cui) {
        $this->importo_di_cui = $importo_di_cui;
    }

    function setImportoApprovatoDiCui($importo_approvato_di_cui) {
        $this->importo_approvato_di_cui = $importo_approvato_di_cui;
    }

    function setNotaDiCui($nota_di_cui) {
        $this->nota_di_cui = $nota_di_cui;
    }
    
    
    function getImportoNonAmmessoSuperMassimaliDiCui() {
        return $this->importo_non_ammesso_super_massimali_di_cui;
    }

    function setImportoNonAmmessoSuperMassimaliDiCui($importo_non_ammesso_super_massimali_di_cui) {
        $this->importo_non_ammesso_super_massimali_di_cui = $importo_non_ammesso_super_massimali_di_cui;
    }

    function getCreatoDaDiCui() {
        return $this->creato_da_di_cui;
    }

    function setCreatoDaDiCui($creato_da_di_cui) {
        $this->creato_da_di_cui = $creato_da_di_cui;
    }

    /**
     * @return mixed
     */
    public function getImportoPagamentoSuccessivo()
    {
        return $this->importo_pagamento_successivo;
    }

    /**
     * @param mixed $importo_pagamento_successivo
     */
    public function setImportoPagamentoSuccessivo($importo_pagamento_successivo): void
    {
        $this->importo_pagamento_successivo = $importo_pagamento_successivo;
    }
    
    public function validateImportazione(\Symfony\Component\Validator\Context\ExecutionContextInterface $context) {
        if (\is_null($this->getVocePianoCosto())) {
            $context->buildViolation('voce_spesa non valorizzato')
                    ->atPath('voce_giustificativo')
                    ->addViolation();
        }
        
        if (\is_null($this->getImporto())) {
            $context->buildViolation('importo non valorizzato')
                    ->atPath('voce_giustificativo')
                    ->addViolation();
        }

    }
}
