<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use MonitoraggioBundle\Entity\TC22MotivoAssenzaCIG;
use MonitoraggioBundle\Entity\TC23TipoProceduraAggiudicazione;
use RichiesteBundle\Entity\Richiesta;
use SoggettoBundle\Entity\Soggetto;
/**
 * @ORM\Table(name="richiesta_procedura_aggiudicazione")
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Repository\ProceduraAggiudicazioneRepository")
 * @Assert\Callback(callback="validateImportazione",groups={"sanita"})
 */

class ProceduraAggiudicazione extends EntityLoggabileCancellabile {
    /**
     * @var int
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Richiesta
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", cascade={"persist"}, inversedBy="mon_procedure_aggiudicazione")
     * @ORM\JoinColumn(nullable=false, name="richiesta_id")
     * @Assert\NotNull
     */
    protected $richiesta;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Assert\NotNull(message="Se il CIG non è presente, inserire 9999")
     * @Assert\Length(max=10, maxMessage="Il cig deve avere 10 caratteri")
     */
    protected $cig;

    /**
     * @var TC22MotivoAssenzaCIG|null
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC22MotivoAssenzaCIG")
     * @ORM\JoinColumn(nullable=true, name="tc22_motivo_assenza_cig_id")
     */
    protected $motivo_assenza_cig;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=1500, nullable=true)
     * @Assert\Length(max=1500, maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $descrizione_procedura_aggiudicazione;

    /**
     * @var TC23TipoProceduraAggiudicazione|null
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC23TipoProceduraAggiudicazione")
     * @ORM\JoinColumn(nullable=true, name="tc23_tipo_procedura_aggiudicazione_id")
     */
    protected $tipo_procedura_aggiudicazione;

    /**
     * @var float|null
     * @ORM\Column(type="decimal", precision=15, scale=2, nullable=true)
     * @Assert\Regex(pattern="/^\d+.?\d*$/", match=true, message="Formato non valido")
     */
    protected $importo_procedura_aggiudicazione;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="date", nullable=true)
     * @Assert\date
     */
    protected $data_pubblicazione;

    /**
     * @var float|null
     * @ORM\Column(type="decimal", precision=15, scale=2, nullable=true)
     * @Assert\Regex(pattern="/^\d+.?\d*$/", match=true, message="Formato non valido")
     */
    protected $importo_aggiudicato;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="date", nullable=true)
     * @Assert\date
     */
    protected $data_aggiudicazione;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Assert\Length(max=10, maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $cig_validato;

    /**
     * @var TC22MotivoAssenzaCIG|null
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC22MotivoAssenzaCIG", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, name="tc22_motivo_assenza_cig_validato_id")
     */
    protected $motivo_assenza_cig_validato;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=1500, nullable=true)
     * @Assert\Length(max=1500, maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $descrizione_procedura_aggiudicazione_validato;

    /**
     * @var TC23TipoProceduraAggiudicazione|null
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC23TipoProceduraAggiudicazione", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, name="tc23_tipo_procedura_aggiudicazione_validato_id")
     */
    protected $tipo_procedura_aggiudicazione_validato;

    /**
     * @var float|null
     * @ORM\Column(type="decimal", precision=15, scale=2, nullable=true)
     * @Assert\Regex(pattern="/^\d+.?\d*$/", match=true, message="Formato non valido")
     */
    protected $importo_procedura_aggiudicazione_validato;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="date", nullable=true)
     * @Assert\date
     */
    protected $data_pubblicazione_validato;

    /**
     * @var float|null
     * @ORM\Column(type="decimal", precision=15, scale=2, nullable=true)
     * @Assert\Regex(pattern="/^\d+.?\d*$/", match=true, message="Formato non valido")
     */
    protected $importo_aggiudicato_validato;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="date", nullable=true)
     * @Assert\date
     */
    protected $data_aggiudicazione_validato;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable = true)
     */
    protected $progressivo;

    /**
     * @Assert\IsFalse(message="In caso di CIG pari a 9999 è necessario compilare tuttti i campi")
     */
    public function isRecordValid():bool {
        return 9999 == $this->cig && (
                is_null($this->motivo_assenza_cig) ||
                is_null($this->descrizione_procedura_aggiudicazione) ||
                is_null($this->tipo_procedura_aggiudicazione) ||
                is_null($this->importo_procedura_aggiudicazione) ||
                is_null($this->data_pubblicazione) ||
                is_null($this->importo_aggiudicato) ||
                is_null($this->data_aggiudicazione)
                );
    }

    public function setCig(?string $cig): self {
        $this->cig = $cig;

        return $this;
    }

    public function getCig(): ?string {
        return $this->cig;
    }

    public function setDescrizioneProceduraAggiudicazione(?string $descrizioneProceduraAggiudicazione): self {
        $this->descrizione_procedura_aggiudicazione = $descrizioneProceduraAggiudicazione;

        return $this;
    }

    public function getDescrizioneProceduraAggiudicazione(): ?string {
        return $this->descrizione_procedura_aggiudicazione;
    }

    public function setImportoProceduraAggiudicazione(?string $importoProceduraAggiudicazione): self {
        $this->importo_procedura_aggiudicazione = $importoProceduraAggiudicazione;

        return $this;
    }

    public function getImportoProceduraAggiudicazione(): ?string {
        return $this->importo_procedura_aggiudicazione;
    }

    public function setDataPubblicazione(?\DateTime $dataPubblicazione): self {
        $this->data_pubblicazione = $dataPubblicazione;

        return $this;
    }

    public function getDataPubblicazione(): ?\DateTime {
        return $this->data_pubblicazione;
    }

    public function setImportoAggiudicato(?string $importoAggiudicato): self {
        $this->importo_aggiudicato = $importoAggiudicato;

        return $this;
    }

    public function getImportoAggiudicato(): ?string {
        return $this->importo_aggiudicato;
    }

    public function setDataAggiudicazione(?\DateTime $dataAggiudicazione): self {
        $this->data_aggiudicazione = $dataAggiudicazione;

        return $this;
    }

    public function getDataAggiudicazione(): ?\DateTime {
        return $this->data_aggiudicazione;
    }

    public function setRichiesta(Richiesta $richiesta): self {
        $this->richiesta = $richiesta;

        return $this;
    }

    public function getRichiesta(): Richiesta {
        return $this->richiesta;
    }

    public function setMotivoAssenzaCig(?TC22MotivoAssenzaCIG $motivoAssenzaCig): self {
        $this->motivo_assenza_cig = $motivoAssenzaCig;

        return $this;
    }

    public function getMotivoAssenzaCig(): ?TC22MotivoAssenzaCIG {
        return $this->motivo_assenza_cig;
    }

    public function setTipoProceduraAggiudicazione(?TC23TipoProceduraAggiudicazione $tipoProceduraAggiudicazione): self {
        $this->tipo_procedura_aggiudicazione = $tipoProceduraAggiudicazione;

        return $this;
    }

    public function getTipoProceduraAggiudicazione(): ?TC23TipoProceduraAggiudicazione {
        return $this->tipo_procedura_aggiudicazione;
    }

    public function __construct(?Richiesta $richiesta = null, ?int $progressivo= null) {
        $this->richiesta = $richiesta;
        $this->progressivo = $progressivo;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getSoggetto(): Soggetto {
        return $this->richiesta->getSoggetto();
    }

    public function setCigValidato(?string $cigValidato): self {
        $this->cig_validato = $cigValidato;

        return $this;
    }

    public function getCigValidato(): ?string {
        return $this->cig_validato;
    }

    public function setDescrizioneProceduraAggiudicazioneValidato(?string $descrizioneProceduraAggiudicazioneValidato): self {
        $this->descrizione_procedura_aggiudicazione_validato = $descrizioneProceduraAggiudicazioneValidato;

        return $this;
    }

    public function getDescrizioneProceduraAggiudicazioneValidato(): ?string {
        return $this->descrizione_procedura_aggiudicazione_validato;
    }

    public function setImportoProceduraAggiudicazioneValidato(?string $importoProceduraAggiudicazioneValidato): self {
        $this->importo_procedura_aggiudicazione_validato = $importoProceduraAggiudicazioneValidato;

        return $this;
    }

    public function getImportoProceduraAggiudicazioneValidato(): ?string {
        return $this->importo_procedura_aggiudicazione_validato;
    }

    public function setDataPubblicazioneValidato(?\DateTime $dataPubblicazioneValidato): self {
        $this->data_pubblicazione_validato = $dataPubblicazioneValidato;

        return $this;
    }

    public function getDataPubblicazioneValidato(): ?\DateTime {
        return $this->data_pubblicazione_validato;
    }

    public function setImportoAggiudicatoValidato(?string $importoAggiudicatoValidato): self {
        $this->importo_aggiudicato_validato = $importoAggiudicatoValidato;

        return $this;
    }

    public function getImportoAggiudicatoValidato(): ?string {
        return $this->importo_aggiudicato_validato;
    }

    public function setDataAggiudicazioneValidato(?\DateTime $dataAggiudicazioneValidato): self {
        $this->data_aggiudicazione_validato = $dataAggiudicazioneValidato;

        return $this;
    }

    public function getDataAggiudicazioneValidato(): ?\DateTime {
        return $this->data_aggiudicazione_validato;
    }

    public function setMotivoAssenzaCigValidato(?TC22MotivoAssenzaCIG $motivoAssenzaCigValidato): self {
        $this->motivo_assenza_cig_validato = $motivoAssenzaCigValidato;

        return $this;
    }

    public function getMotivoAssenzaCigValidato(): ?TC22MotivoAssenzaCIG {
        return $this->motivo_assenza_cig_validato;
    }

    public function setTipoProceduraAggiudicazioneValidato(?TC23TipoProceduraAggiudicazione $tipoProceduraAggiudicazioneValidato): self {
        $this->tipo_procedura_aggiudicazione_validato = $tipoProceduraAggiudicazioneValidato;

        return $this;
    }

    public function getTipoProceduraAggiudicazioneValidato(): ?TC23TipoProceduraAggiudicazione {
        return $this->tipo_procedura_aggiudicazione_validato;
    }

    public function isValidato(): bool
    {
        return $this->cig_validato || $this->motivo_assenza_cig_validato;
    }

    public function isCigAssenteBeneficiario():bool
    {
        return !\is_null($this->cig) && '9999' == $this->cig;
    }

    public function normalizzaCampiBeneficiario(){
        if( !$this->isCigAssenteBeneficiario() ){
            $this->motivo_assenza_cig = null;
            $this->descrizione_procedura_aggiudicazione = null;
            $this->tipo_procedura_aggiudicazione = null;
            $this->importo_procedura_aggiudicazione = null;
            $this->data_pubblicazione = null;
            $this->importo_aggiudicato = null;
            $this->data_aggiudicazione = null;
        }
    }

    /**
     * @param integer $progressivo
     */
    public function setProgressivo($progressivo): self
    {
        $this->progressivo = $progressivo;

        return $this;
    }

    /**
     * @return integer
     */
    public function getProgressivo()
    {
        return $this->progressivo;
    }
    
    public function validateImportazione(\Symfony\Component\Validator\Context\ExecutionContextInterface $context) {
        
        $violationCigNull = true;
        if($this->valoreVuoto($this->getCig()) || $this->getCig()== '9999'){
            if($this->valoreVuoto($this->getMotivoAssenzaCig())){
                $context->buildViolation('motivo_assenza_cig non valorizzato')
                        ->atPath('procedura_aggiudicazione')
                        ->addViolation();
                $violationCigNull = false;
            }
            if($this->valoreVuoto($this->getDescrizioneProceduraAggiudicazione())){
                $context->buildViolation('descrizione non valorizzato')
                        ->atPath('procedura_aggiudicazione')
                        ->addViolation();
                $violationCigNull = false;
            }
            if($this->valoreVuoto($this->getTipoProceduraAggiudicazione())){
                $context->buildViolation('tipo non valorizzato')
                        ->atPath('procedura_aggiudicazione')
                        ->addViolation();
                $violationCigNull = false;
            }
            if($this->valoreVuoto($this->getImportoProceduraAggiudicazione())){
                $context->buildViolation('importo_procedura non valorizzato')
                        ->atPath('procedura_aggiudicazione')
                        ->addViolation();
                $violationCigNull = false;
            }
            if($this->valoreVuoto($this->getDataPubblicazione())){
                $context->buildViolation('data_pubblicazione non valorizzato')
                        ->atPath('procedura_aggiudicazione')
                        ->addViolation();
                $violationCigNull = false;
            }
            if($this->valoreVuoto($this->getImportoAggiudicato())){
                $context->buildViolation('importo_aggiudicato non valorizzato')
                        ->atPath('procedura_aggiudicazione')
                        ->addViolation();
                $violationCigNull = false;
            }
            if($this->valoreVuoto($this->getDataAggiudicazione())){
                $context->buildViolation('data_aggiudicazione non valorizzato')
                        ->atPath('procedura_aggiudicazione')
                        ->addViolation();
                $violationCigNull = false;
            }
            /*if($violationCigNull){
                $context->buildViolation('cig non valorizzato')
                        ->atPath('procedura_aggiudicazione')
                        ->addViolation();
            }*/
        } 

    }
    
    public function valoreVuoto($var){
        return (\is_null($var) || $var == '' || $var == ' ');
    }
}
