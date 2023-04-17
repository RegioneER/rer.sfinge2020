<?php

/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 07/06/17
 * Time: 12:55
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use BaseBundle\Entity\Id;

/**
 * @ORM\Table(name="pg00_procedura_aggiudicazione")
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\PG00ProcedureAggiudicazioneRepository")
 */
class PG00ProcedureAggiudicazione extends EntityEsportazione {
    use StrutturaCancellabile;
    use Id;
    use HasCodLocaleProgetto;

    const CODICE_TRACCIATO = "PG00";
    const SEPARATORE = "|";

    /**
     * @ORM\ManyToOne(targetEntity="TC22MotivoAssenzaCIG")
     * @ORM\JoinColumn(name="motivo_assenza_cig_id", referencedColumnName="id", nullable=true)
     */
    protected $tc22_motivo_assenza_cig;

    /**
     * @ORM\ManyToOne(targetEntity="TC23TipoProceduraAggiudicazione")
     * @ORM\JoinColumn(name="tipo_proc_agg_id", referencedColumnName="id", nullable=true)
     */
    protected $tc23_tipo_procedura_aggiudicazione;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Length(max="60", maxMessage="sfinge.monitoraggio.maxLength", groups={"esportazione_monitoraggio", "Default"})
     */
    protected $cod_locale_progetto;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Length(max="60", maxMessage="sfinge.monitoraggio.maxLength", groups={"esportazione_monitoraggio", "Default"})
     */
    protected $cod_proc_agg;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Length(max="10", maxMessage="sfinge.monitoraggio.maxLength", groups={"esportazione_monitoraggio", "Default"})
     */
    protected $cig;

    /**
     * @ORM\Column(type="string", length=1500, nullable=true)
     * @Assert\Length(max="1500", maxMessage="sfinge.monitoraggio.maxLength")
     */
    protected $descr_procedura_agg;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     */
    protected $importo_procedura_agg;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Date
     */
    protected $data_pubblicazione;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     */
    protected $importo_aggiudicato;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Date
     */
    protected $data_aggiudicazione;

    /**
     * @Assert\Callback(message="Tipo procedura di aggiudicazione obbligatorio")
     */
    public function validationCallback(ExecutionContextInterface $context) {
        if ("9999" == $this->cig) {
            if (is_null($this->tc23_tipo_procedura_aggiudicazione)) {
                $context->buildViolation('Tipo procedura aggiudicazione obbligatorio')->atPath('tc23_tipo_procedura_aggiudicazione')->addViolation();
            }
            if (is_null($this->tc22_motivo_assenza_cig)) {
                $context->buildViolation('Motivo assenza cig obbligatorio')->atPath('tc22_motivo_assenza_cig')->addViolation();
            }
            if (is_null($this->descr_procedura_agg)) {
                $context->buildViolation('Descrizione procedura aggiudicazione obbligatoria')->atPath('descr_procedura_agg')->addViolation();
            }
            if (is_null($this->importo_procedura_agg)) {
                $context->buildViolation('Importo procedura aggiudicazione obbligatorio')->atPath('importo_procedura_agg')->addViolation();
            }
            if (is_null($this->data_pubblicazione)) {
                $context->buildViolation('Data pubblicazione obbligatoria')->atPath('data_pubblicazione')->addViolation();
            }
            if (is_null($this->importo_aggiudicato)) {
                $context->buildViolation('Importo aggiudicato obbligatorio')->atPath('importo_aggiudicato')->addViolation();
            }
            if (is_null($this->data_aggiudicazione)) {
                $context->buildViolation('Data aggiudicazione obbligatoria')->atPath('data_aggiudicazione')->addViolation();
            }
        }
        return true;
    }

    /**
     * @param string $codProcAgg
     * @return PG00ProcedureAggiudicazione
     */
    public function setCodProcAgg($codProcAgg) {
        $this->cod_proc_agg = $codProcAgg;

        return $this;
    }

    /**
     * @return string
     */
    public function getCodProcAgg() {
        return $this->cod_proc_agg;
    }

    /**
     * @param string $cig
     * @return PG00ProcedureAggiudicazione
     */
    public function setCig($cig) {
        $this->cig = $cig;

        return $this;
    }

    /**
     * @return string
     */
    public function getCig() {
        return $this->cig;
    }

    /**
     * Set descr_procedura_agg
     *
     * @param string $descrProceduraAgg
     * @return PG00ProcedureAggiudicazione
     */
    public function setDescrProceduraAgg($descrProceduraAgg) {
        $this->descr_procedura_agg = $descrProceduraAgg;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescrProceduraAgg() {
        return $this->descr_procedura_agg;
    }

    /**
     * @param string $importoProceduraAgg
     * @return PG00ProcedureAggiudicazione
     */
    public function setImportoProceduraAgg($importoProceduraAgg) {
        $importo_pulito = str_replace(',', '.', $importoProceduraAgg);
        $this->importo_procedura_agg = (float) $importo_pulito;

        return $this;
    }

    /**
     * @return string
     */
    public function getImportoProceduraAgg() {
        return $this->importo_procedura_agg;
    }

    /**
     * Set data_pubblicazione
     *
     * @param \DateTime $dataPubblicazione
     * @return PG00ProcedureAggiudicazione
     */
    public function setDataPubblicazione($dataPubblicazione) {
        $this->data_pubblicazione = $dataPubblicazione;

        return $this;
    }

    /**
     * Get data_pubblicazione
     *
     * @return \DateTime
     */
    public function getDataPubblicazione() {
        return $this->data_pubblicazione;
    }

    /**
     * Set importo_aggiudicato
     *
     * @param string $importoAggiudicato
     * @return PG00ProcedureAggiudicazione
     */
    public function setImportoAggiudicato($importoAggiudicato) {
        $importo_pulito = str_replace(',', '.', $importoAggiudicato);
        $this->importo_aggiudicato = (float) $importo_pulito;

        return $this;
    }

    /**
     * Get importo_aggiudicato
     *
     * @return string
     */
    public function getImportoAggiudicato() {
        return $this->importo_aggiudicato;
    }

    /**
     * Set data_aggiudicazione
     *
     * @param \DateTime $dataAggiudicazione
     * @return PG00ProcedureAggiudicazione
     */
    public function setDataAggiudicazione($dataAggiudicazione) {
        $this->data_aggiudicazione = $dataAggiudicazione;

        return $this;
    }

    /**
     * Get data_aggiudicazione
     *
     * @return \DateTime
     */
    public function getDataAggiudicazione() {
        return $this->data_aggiudicazione;
    }

    /**
     * @param TC22MotivoAssenzaCIG $tc22MotivoAssenzaCig
     */
    public function setTc22MotivoAssenzaCig(TC22MotivoAssenzaCIG $tc22MotivoAssenzaCig = null): self {
        $this->tc22_motivo_assenza_cig = $tc22MotivoAssenzaCig;

        return $this;
    }

    /**
     * Get tc22_motivo_assenza_cig
     *
     * @return \MonitoraggioBundle\Entity\TC22MotivoAssenzaCIG
     */
    public function getTc22MotivoAssenzaCig() {
        return $this->tc22_motivo_assenza_cig;
    }

    /**
     * @param \MonitoraggioBundle\Entity\TC23TipoProceduraAggiudicazione $tc23TipoProceduraAggiudicazione
     */
    public function setTc23TipoProceduraAggiudicazione(TC23TipoProceduraAggiudicazione $tc23TipoProceduraAggiudicazione = null): self {
        $this->tc23_tipo_procedura_aggiudicazione = $tc23TipoProceduraAggiudicazione;

        return $this;
    }

    /**
     * @return \MonitoraggioBundle\Entity\TC23TipoProceduraAggiudicazione
     */
    public function getTc23TipoProceduraAggiudicazione() {
        return $this->tc23_tipo_procedura_aggiudicazione;
    }

    public function getTracciato() {
        return (\is_null($this->getCodLocaleProgetto()) ? "" : $this->getCodLocaleProgetto())
            . $this::SEPARATORE .
            (\is_null($this->getCodProcAgg()) ? "" : $this->getCodProcAgg())
            . $this::SEPARATORE .
            (\is_null($this->getCig()) ? "" : $this->getCig())
            . $this::SEPARATORE .
            (\is_null($this->getTc22MotivoAssenzaCig()) ? "" : $this->getTc22MotivoAssenzaCig()->getMotivoAssenzaCig())
            . $this::SEPARATORE .
            (\is_null($this->getDescrProceduraAgg()) ? "" : $this->getDescrProceduraAgg())
            . $this::SEPARATORE .
            (\is_null($this->getTc23TipoProceduraAggiudicazione()) ? "" : $this->getTc23TipoProceduraAggiudicazione()->getTipoProcAgg())
            . $this::SEPARATORE .
            (\is_null($this->getImportoProceduraAgg()) ? "" : \number_format($this->getImportoProceduraAgg(), 2, ',', ''))
            . $this::SEPARATORE .
            (\is_null($this->getDataPubblicazione()) ? "" : $this->getDataPubblicazione()->format('d/m/Y'))
            . $this::SEPARATORE .
            (\is_null($this->getImportoAggiudicato()) ? "" : \number_format($this->getImportoAggiudicato(), 2, ',', ''))
            . $this::SEPARATORE .
            (\is_null($this->getDataAggiudicazione()) ? "" : $this->getDataAggiudicazione()->format('d/m/Y'))
            . $this::SEPARATORE .
            (\is_null($this->getFlgCancellazione()) ? "" : $this->getFlgCancellazione());
    }
}
