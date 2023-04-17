<?php

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity
 * @ORM\Table(name="tc1_procedura_attivazione")
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC1ProceduraAttivazioneRepository")
 */
class TC1ProceduraAttivazione extends EntityLoggabileCancellabile {
    use Id;

    public static $STATI = [
        'Pubblicata',
        'Aperta',
        'Scaduta',
        'Concessa',
        'Liquidata',
        'A sportello',
        'Conclusa', ];

    /**
     * @ORM\Column(type="string", length=30, nullable=false)
     * @Assert\NotNull
     * @Assert\Length(max=30, maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $cod_proc_att;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=30, maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $cod_proc_att_locale;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=30, maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $cod_aiuto_rna;

    /**
     * @ORM\ManyToOne(targetEntity="TC2TipoProceduraAttivazione")
     * @ORM\JoinTable(name="tc2_tipo_procedura_attivazione", joinColumns={@ORM\JoinColumn(name="tip_procedura_att", referencedColumnName="tip_procedura_att")})
     * @Assert\NotNull
     */
    protected $tip_procedura_att;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     * @Assert\Choice(choices={"S", "N"}, message="Valori possibili Sì o No")
     */
    protected $flag_aiuti;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=255, maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $descr_procedura_att;

    /**
     * @ORM\ManyToOne(targetEntity="TC3ResponsabileProcedura")
     * @ORM\JoinTable(name="tc3_responsabile_procedura", joinColumns={@ORM\JoinColumn(name="cod_tipo_resp_proc", referencedColumnName="cod_tipo_resp_proc")})
     * @Assert\NotNull
     */
    protected $tipo_resp_proc;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=255, maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $denom_resp_proc;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\NotNull
     * @Assert\Date
     */
    protected $data_avvio_procedura;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Date
     */
    protected $data_fine_procedura;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=1000, maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $cod_programma;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     * @Assert\Length(max=1, maxMessage="Massimo un carattere")
     */
    protected $flag_cancellazione;

    /**
     * @var int
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Procedura")
     * @ORM\JoinColumn(name="procedura_id", referencedColumnName="id", nullable=true)
     */
    protected $proceduraOperativa;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false, name="flag_fesr")
     * @Assert\NotNull
     * @Assert\Type(type="boolean", message="Il valore deve essere vero o falso")
     */
    protected $flagFesr;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true, name="stato")
     * @Assert\NotNull
     * @Assert\Choice(callback="getStati", message="Lo stato inserito non è valido")
     */
    protected $stato;

    /**
     * @return mixed
     */
    public function getCodProcAtt() {
        return $this->cod_proc_att;
    }

    /**
     * @param mixed $cod_proc_att
     *
     * @return TC1ProceduraAttivazione
     */
    public function setCodProcAtt($cod_proc_att) {
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
     *
     * @return TC1ProceduraAttivazione
     */
    public function setCodProcAttLocale($cod_proc_att_locale) {
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
     *
     * @return TC1ProceduraAttivazione
     */
    public function setCodAiutoRna($cod_aiuto_rna) {
        $this->cod_aiuto_rna = $cod_aiuto_rna;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTipProceduraAtt() {
        return $this->tip_procedura_att;
    }

    /**
     * @param mixed $tip_procedura_att
     *
     * @return TC1ProceduraAttivazione
     */
    public function setTipProceduraAtt($tip_procedura_att) {
        $this->tip_procedura_att = $tip_procedura_att;

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
     *
     * @return TC1ProceduraAttivazione
     */
    public function setFlagAiuti($flag_aiuti) {
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
     *
     * @return TC1ProceduraAttivazione
     */
    public function setDescrProceduraAtt($descr_procedura_att) {
        $this->descr_procedura_att = $descr_procedura_att;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTipoRespProc() {
        return $this->tipo_resp_proc;
    }

    /**
     * @param mixed $cod_tipo_resp_proc
     * @param mixed $tipo_resp_proc
     *
     * @return TC1ProceduraAttivazione
     */
    public function setTipoRespProc($tipo_resp_proc) {
        $this->tipo_resp_proc = $tipo_resp_proc;

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
     *
     * @return TC1ProceduraAttivazione
     */
    public function setDenomRespProc($denom_resp_proc) {
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
     *
     * @return TC1ProceduraAttivazione
     */
    public function setDataAvvioProcedura($data_avvio_procedura) {
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
     *
     * @return TC1ProceduraAttivazione
     */
    public function setDataFineProcedura($data_fine_procedura) {
        $this->data_fine_procedura = $data_fine_procedura;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCodProgramma() {
        return $this->cod_programma;
    }

    /**
     * @param mixed $cod_programma
     *
     * @return TC1ProceduraAttivazione
     */
    public function setCodProgramma($cod_programma) {
        $this->cod_programma = $cod_programma;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFlagCancellazione() {
        return $this->flag_cancellazione;
    }

    /**
     * @param mixed $flag_cancellazione
     *
     * @return TC1ProceduraAttivazione
     */
    public function setFlagCancellazione($flag_cancellazione) {
        $this->flag_cancellazione = $flag_cancellazione;

        return $this;
    }

    public function getProceduraOperativa() {
        return $this->proceduraOperativa;
    }

    /**
     * @return TC1ProceduraAttivazione
     * @param mixed $procedura
     */
    public function setProceduraOperativa($procedura) {
        $this->proceduraOperativa = $procedura;

        return $this;
    }

    public function getFlagFesr() {
        return $this->flagFesr;
    }

    public function getStato() {
        return $this->stato;
    }

    /**
     * @return TC1ProceduraAttivazione
     * @param mixed $flagFesr
     */
    public function setFlagFesr($flagFesr) {
        $this->flagFesr = $flagFesr;

        return $this;
    }

    public function setStato($stato) {
        if (!in_array($stato, self::$STATI)) {
            throw new \InvalidArgumentException('Stato non valido');
        }
        $this->stato = $stato;

        return $this;
    }

    public static function getStati() {
        return self::$STATI;
    }

    /**
     * @return TC1ProceduraAttivazione
     */
    public function setTipoProceduraAttivazione(TC2TipoProceduraAttivazione $tipoProceduraAttivazione) {
        $this->tip_procedura_att = $tipoProceduraAttivazione->getTipProceduraAtt();

        return $this;
    }

    /**
     * The __toString method allows a class to decide how it will react when it is converted to a string.
     *
     * @return string
     *
     * @see http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
     */
    public function __toString() {
        return $this->cod_proc_att_locale . (is_null($this->descr_procedura_att) ? '' : ' - ' . $this->descr_procedura_att);
    }
}
