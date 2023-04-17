<?php

/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 07/06/17
 * Time: 12:15.
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Table(name="pa00_procedure_attivazione")
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\PA00ProcedureAttivazioneRepository")
 */
class PA00ProcedureAttivazione extends EntityEsportazione {
    use StrutturaCancellabile;
    use Id;

    const CODICE_TRACCIATO = 'PA00';
    const SEPARATORE = '|';

    /**
     * @ORM\ManyToOne(targetEntity="TC2TipoProceduraAttivazione")
     * @ORM\JoinColumn(name="tip_procedura_att_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio"})
     */
    protected $tc2_tipo_procedura_attivazione;

    /**
     * @ORM\ManyToOne(targetEntity="TC3ResponsabileProcedura")
     * @ORM\JoinColumn(name="tipo_resp_proc_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio"})
     */
    protected $tc3_responsabile_procedura;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio"})
     * @Assert\Length(max=30, maxMessage="sfinge.monitoraggio.maxLength", groups={"esportazione_monitoraggio", "Default"})
     */
    protected $cod_proc_att;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio"})
     * @Assert\Length(max=30, maxMessage="sfinge.monitoraggio.maxLength", groups={"esportazione_monitoraggio", "Default"})
     */
    protected $cod_proc_att_locale;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     * @Assert\Length(max=30, maxMessage="sfinge.monitoraggio.maxLength", groups={"esportazione_monitoraggio", "Default"})
     */
    protected $cod_aiuto_rna;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio"})
     * @Assert\Length(max=1, maxMessage="sfinge.monitoraggio.maxLength", groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Regex(pattern="/^(S|N)$/", match=true, message="sfinge.monitoraggio.invalidValue")
     */
    protected $flag_aiuti;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio"})
     * @Assert\Length(max=255, maxMessage="sfinge.monitoraggio.maxLength", groups={"esportazione_monitoraggio", "Default"})
     */
    protected $descr_procedura_att;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio"})
     * @Assert\Length(max=255, maxMessage="sfinge.monitoraggio.maxLength", groups={"esportazione_monitoraggio", "Default"})
     */
    protected $denom_resp_proc;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Date(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\NotNull(groups={"esportazione_monitoraggio"})
     */
    protected $data_avvio_procedura;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Date(groups={"esportazione_monitoraggio", "Default"})
     */
    protected $data_fine_procedura;

    /**
     * @return mixed
     */
    public function getTc2TipoProceduraAttivazione() {
        return $this->tc2_tipo_procedura_attivazione;
    }

    /**
     * @param mixed $tc2_tipo_procedura_attivazione
     */
    public function setTc2TipoProceduraAttivazione($tc2_tipo_procedura_attivazione): self {
        $this->tc2_tipo_procedura_attivazione = $tc2_tipo_procedura_attivazione;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTc3ResponsabileProcedura() {
        return $this->tc3_responsabile_procedura;
    }

    /**
     * @param mixed $tc3_responsabile_procedura
     */
    public function setTc3ResponsabileProcedura($tc3_responsabile_procedura): self {
        $this->tc3_responsabile_procedura = $tc3_responsabile_procedura;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCodProcAtt() {
        return $this->cod_proc_att;
    }

    /**
     * @param mixed $cod_proc_att
     */
    public function setCodProcAtt($cod_proc_att): self {
        $this->cod_proc_att = $cod_proc_att;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCodProcAttLocale() {
        return $this->cod_proc_att_locale;
    }

    /**
     * @param mixed $cod_proc_att_locale
     */
    public function setCodProcAttLocale($cod_proc_att_locale): self {
        $this->cod_proc_att_locale = $cod_proc_att_locale;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCodAiutoRna() {
        return $this->cod_aiuto_rna;
    }

    /**
     * @param mixed $cod_aiuto_rna
     */
    public function setCodAiutoRna($cod_aiuto_rna): self {
        $this->cod_aiuto_rna = $cod_aiuto_rna;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFlagAiuti() {
        return $this->flag_aiuti;
    }

    /**
     * @param mixed $flag_aiuti
     */
    public function setFlagAiuti($flag_aiuti): self {
        $this->flag_aiuti = $flag_aiuti;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescrProceduraAtt() {
        return $this->descr_procedura_att;
    }

    /**
     * @param mixed $descr_procedura_att
     */
    public function setDescrProceduraAtt($descr_procedura_att): self {
        $this->descr_procedura_att = $descr_procedura_att;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDenomRespProc() {
        return $this->denom_resp_proc;
    }

    /**
     * @param mixed $denom_resp_proc
     */
    public function setDenomRespProc($denom_resp_proc): self {
        $this->denom_resp_proc = $denom_resp_proc;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDataAvvioProcedura() {
        return $this->data_avvio_procedura;
    }

    /**
     * @param mixed $data_avvio_procedura
     */
    public function setDataAvvioProcedura($data_avvio_procedura): self {
        $this->data_avvio_procedura = $data_avvio_procedura;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDataFineProcedura() {
        return $this->data_fine_procedura;
    }

    /**
     * @param mixed $data_fine_procedura
     */
    public function setDataFineProcedura($data_fine_procedura): self {
        $this->data_fine_procedura = $data_fine_procedura;
        return $this;
    }

    public function getTracciato() {
        return (\is_null($this->getCodProcAtt()) ? '' : $this->getCodProcAtt())
            . $this::SEPARATORE .
            (\is_null($this->getCodProcAttLocale()) ? '' : $this->getCodProcAttLocale())
            . $this::SEPARATORE .
            (\is_null($this->getCodAiutoRna()) ? '' : $this->getCodAiutoRna())
            . $this::SEPARATORE .
            (\is_null($this->getTc2TipoProceduraAttivazione()) ? '' : $this->getTc2TipoProceduraAttivazione()->getTipProceduraAtt())
            . $this::SEPARATORE .
            (\is_null($this->getFlagAiuti()) ? '' : $this->getFlagAiuti())
            . $this::SEPARATORE .
            (\is_null($this->getDescrProceduraAtt()) ? '' : $this->getDescrProceduraAtt())
            . $this::SEPARATORE .
            (\is_null($this->getTc3ResponsabileProcedura()) ? '' : $this->getTc3ResponsabileProcedura()->getCodTipoRespProc())
            . $this::SEPARATORE .
            (\is_null($this->getDenomRespProc()) ? '' : $this->getDenomRespProc())
            . $this::SEPARATORE .
            (\is_null($this->getDataAvvioProcedura()) ? '' : $this->getDataAvvioProcedura()->format('d/m/Y'))
            . $this::SEPARATORE .
            (\is_null($this->getDataFineProcedura()) ? '' : $this->getDataFineProcedura()->format('d/m/Y'))
            . $this::SEPARATORE .
            (\is_null($this->getFlgCancellazione()) ? '' : $this->getFlgCancellazione());
    }
}
