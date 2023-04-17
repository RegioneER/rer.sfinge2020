<?php

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="vista_pa00")
 */
class VistaPA00 {
    use StrutturaProceduraTrait;

    /**
     * @ORM\ManyToOne(targetEntity="TC2TipoProceduraAttivazione")
     * @ORM\JoinColumn(nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio"})
     * @var TC2TipoProceduraAttivazione
     */
    protected $tc2_tipo_procedura_attivazione;

    /**
     * @ORM\ManyToOne(targetEntity="TC3ResponsabileProcedura")
     * @ORM\JoinColumn(nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio"})
     * @var TC3ResponsabileProcedura
     */
    protected $tc3_responsabile_procedura;

    /**
     * @ORM\ManyToOne(targetEntity="TC1ProceduraAttivazione")
     * @Assert\NotNull(groups={"esportazione_monitoraggio"})
     * @Assert\Length(max=30, maxMessage="sfinge.monitoraggio.maxLength", groups={"esportazione_monitoraggio", "Default"})
     * @var TC1ProceduraAttivazione
     */
    protected $tc1_cod_proc_att;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio"})
     * @Assert\Length(max=30, maxMessage="sfinge.monitoraggio.maxLength", groups={"esportazione_monitoraggio", "Default"})
     * @var string
     */
    protected $cod_proc_att_locale;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     * @Assert\Length(max=30, maxMessage="sfinge.monitoraggio.maxLength", groups={"esportazione_monitoraggio", "Default"})
     * @var string|null
     */
    protected $cod_aiuto_rna;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio"})
     * @Assert\Length(max=1, maxMessage="sfinge.monitoraggio.maxLength", groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Regex(pattern="/^(S|N)$/", match=true, message="sfinge.monitoraggio.invalidValue")
     * @var string
     */
    protected $flag_aiuti;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio"})
     * @Assert\Length(max=255, maxMessage="sfinge.monitoraggio.maxLength", groups={"esportazione_monitoraggio", "Default"})
     * @var string
     */
    protected $descr_procedura_att;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio"})
     * @Assert\Length(max=255, maxMessage="sfinge.monitoraggio.maxLength", groups={"esportazione_monitoraggio", "Default"})
     * @var string
     */
    protected $denom_resp_proc;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Date(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\NotNull(groups={"esportazione_monitoraggio"})
     * @var \DateTime|null
     */
    protected $data_avvio_procedura;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Date(groups={"esportazione_monitoraggio", "Default"})
     * @var \DateTime|null
     */
    protected $data_fine_procedura;

    public function getTc2TipoProceduraAttivazione(): ?TC2TipoProceduraAttivazione {
        return $this->tc2_tipo_procedura_attivazione;
    }

    public function setTc2TipoProceduraAttivazione(TC2TipoProceduraAttivazione $tc2_tipo_procedura_attivazione): self {
        $this->tc2_tipo_procedura_attivazione = $tc2_tipo_procedura_attivazione;
        return $this;
    }

    public function getTc3ResponsabileProcedura(): ?TC3ResponsabileProcedura {
        return $this->tc3_responsabile_procedura;
    }

    public function setTc3ResponsabileProcedura(TC3ResponsabileProcedura $tc3_responsabile_procedura): self {
        $this->tc3_responsabile_procedura = $tc3_responsabile_procedura;
        return $this;
    }

    public function getTc1CodProcAtt(): ?TC1ProceduraAttivazione {
        return $this->tc1_cod_proc_att;
    }

    public function setTC1CodProcAtt(TC1ProceduraAttivazione $cod_proc_att): self {
        $this->tc1_cod_proc_att = $cod_proc_att;
        return $this;
    }

    public function getCodProcAttLocale(): ?string {
        return $this->cod_proc_att_locale;
    }

    public function setCodProcAttLocale(?string $cod_proc_att_locale): self {
        $this->cod_proc_att_locale = $cod_proc_att_locale;
        return $this;
    }

    public function getCodAiutoRna(): ?string {
        return $this->cod_aiuto_rna;
    }

    public function setCodAiutoRna(?string $cod_aiuto_rna): self {
        $this->cod_aiuto_rna = $cod_aiuto_rna;
        return $this;
    }

    public function getFlagAiuti(): ?string {
        return $this->flag_aiuti;
    }

    public function setFlagAiuti(string $flag_aiuti): self {
        $this->flag_aiuti = $flag_aiuti;
        return $this;
    }

    public function getDescrProceduraAtt(): ?string {
        return $this->descr_procedura_att;
    }

    public function setDescrProceduraAtt(string $descr_procedura_att): self {
        $this->descr_procedura_att = $descr_procedura_att;
        return $this;
    }

    public function getDenomRespProc(): ?string {
        return $this->denom_resp_proc;
    }

    public function setDenomRespProc(string $denom_resp_proc): self {
        $this->denom_resp_proc = $denom_resp_proc;
        return $this;
    }

    public function getDataAvvioProcedura(): ?\DateTime {
        return $this->data_avvio_procedura;
    }

    public function setDataAvvioProcedura(?\DateTime $data_avvio_procedura): self {
        $this->data_avvio_procedura = $data_avvio_procedura;
        return $this;
    }

    public function getDataFineProcedura(): ?\DateTime {
        return $this->data_fine_procedura;
    }

    public function setDataFineProcedura(?\DateTime $data_fine_procedura): self {
        $this->data_fine_procedura = $data_fine_procedura;
        return $this;
    }
}
