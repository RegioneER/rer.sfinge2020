<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 29/06/17
 * Time: 17:07
 */


namespace MonitoraggioBundle\Form\Entity\Strutture;
use MonitoraggioBundle\Annotations\RicercaFormType;
use MonitoraggioBundle\Annotations\ViewElenco;


class PA00 extends BaseRicercaStruttura
{

    /**
     * @ViewElenco( ordine = 1, titolo="Codice procedura attivazione" )
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice procedura attivazione")
     */
    protected $cod_proc_att;

    /**
     * @ViewElenco( ordine = 2, titolo="Codice procedura attivazione locale" )
     * @RicercaFormType( ordine = 2, type = "text", label = "Codice procedura attivazione locale")
     */
    protected $cod_proc_att_locale;

    /**
     * @ViewElenco( ordine = 3, titolo="Codice aiuto RNA" )
     * @RicercaFormType( ordine = 3, type = "text", label = "Codice aiuto RNA")
     */
    protected $cod_aiuto_rna;

    /**
     * @ViewElenco( ordine = 4, titolo="Tipo operazione" )
     * @RicercaFormType( ordine = 4, type = "entity", label = "Tipo operazione", options={"class": "MonitoraggioBundle\Entity\TC2TipoProceduraAttivazione"})
     */
    protected $tc2_tipo_procedura_attivazione;

    /**
     * @ViewElenco( ordine = 5, titolo="Flag aiuti" )
     * @RicercaFormType( ordine = 5, type = "text", label = "Flag aiuti")
     */
    protected $flag_aiuti;

    /**
     * @ViewElenco( ordine = 6, titolo="Descrizione procedura attivazione" )
     * @RicercaFormType( ordine = 6, type = "text", label = "Descrizione procedura attivazione")
     */
    protected $descr_procedura_att;

    /**
     * @ViewElenco( ordine = 7, titolo="Responsabile procedura" )
     * @RicercaFormType( ordine = 7, type = "entity", label = "Responsabile procedura", options={"class": "MonitoraggioBundle\Entity\TC3ResponsabileProcedura"})
     */
    protected $tc3_responsabile_procedura;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 8, titolo="Denominazione responsabile procedura" )
     * @RicercaFormType( ordine = 8, type = "text", label = "Denominazione responsabile procedura")
     */
    protected $denom_resp_proc;

    /**
     * @ViewElenco( ordine = 9, titolo="Data avvio procedura")
     * @RicercaFormType( ordine = 9, type = "birthday", label = "Data avvio procedura", options= {"widget": "single_text", "input": "datetime", "format": "dd/MM/yyyy"})
     */
    protected $data_avvio_procedura;

    /**
     * @ViewElenco( ordine = 10, titolo="Data fine procedura")
     * @RicercaFormType( ordine = 10, type = "birthday", label = "Data fine procedura", options= {"widget": "single_text", "input": "datetime", "format": "dd/MM/yyyy"})
     */
    protected $data_fine_procedura;

    /**
     * @ViewElenco( ordine = 11, titolo="Flag cancellazione" )
     * @RicercaFormType( ordine = 11, type = "text", label = "Flag cancellazione")
     */
    protected $flg_cancellazione;


    /**
     * @return mixed
     */
    public function getCodProcAtt()
    {
        return $this->cod_proc_att;
    }

    /**
     * @param mixed $cod_proc_att
     */
    public function setCodProcAtt($cod_proc_att)
    {
        $this->cod_proc_att = $cod_proc_att;
    }

    /**
     * @return mixed
     */
    public function getCodProcAttLocale()
    {
        return $this->cod_proc_att_locale;
    }

    /**
     * @param mixed $cod_proc_att_locale
     */
    public function setCodProcAttLocale($cod_proc_att_locale)
    {
        $this->cod_proc_att_locale = $cod_proc_att_locale;
    }

    /**
     * @return mixed
     */
    public function getCodAiutoRna()
    {
        return $this->cod_aiuto_rna;
    }

    /**
     * @param mixed $cod_aiuto_rna
     */
    public function setCodAiutoRna($cod_aiuto_rna)
    {
        $this->cod_aiuto_rna = $cod_aiuto_rna;
    }

    /**
     * @return mixed
     */
    public function getTc2TipoProceduraAttivazione()
    {
        return $this->tc2_tipo_procedura_attivazione;
    }

    /**
     * @param mixed $tc2_tipo_procedura_attivazione
     */
    public function setTc2TipoProceduraAttivazione($tc2_tipo_procedura_attivazione)
    {
        $this->tc2_tipo_procedura_attivazione = $tc2_tipo_procedura_attivazione;
    }

    /**
     * @return mixed
     */
    public function getFlagAiuti()
    {
        return $this->flag_aiuti;
    }

    /**
     * @param mixed $flag_aiuti
     */
    public function setFlagAiuti($flag_aiuti)
    {
        $this->flag_aiuti = $flag_aiuti;
    }

    /**
     * @return mixed
     */
    public function getDescrProceduraAtt()
    {
        return $this->descr_procedura_att;
    }

    /**
     * @param mixed $descr_procedura_att
     */
    public function setDescrProceduraAtt($descr_procedura_att)
    {
        $this->descr_procedura_att = $descr_procedura_att;
    }

    /**
     * @return mixed
     */
    public function getTc3ResponsabileProcedura()
    {
        return $this->tc3_responsabile_procedura;
    }

    /**
     * @param mixed $tc3_responsabile_procedura
     */
    public function setTc3ResponsabileProcedura($tc3_responsabile_procedura)
    {
        $this->tc3_responsabile_procedura = $tc3_responsabile_procedura;
    }



    /**
     * @return mixed
     */
    public function getDenomRespProc()
    {
        return $this->denom_resp_proc;
    }

    /**
     * @param mixed $denom_resp_proc
     */
    public function setDenomRespProc($denom_resp_proc)
    {
        $this->denom_resp_proc = $denom_resp_proc;
    }

    /**
     * @return mixed
     */
    public function getDataAvvioProcedura()
    {
        return $this->data_avvio_procedura;
    }

    /**
     * @param mixed $data_avvio_procedura
     */
    public function setDataAvvioProcedura($data_avvio_procedura)
    {
        $this->data_avvio_procedura = $data_avvio_procedura;
    }

    /**
     * @return mixed
     */
    public function getDataFineProcedura()
    {
        return $this->data_fine_procedura;
    }

    /**
     * @param mixed $data_fine_procedura
     */
    public function setDataFineProcedura($data_fine_procedura)
    {
        $this->data_fine_procedura = $data_fine_procedura;
    }

    /**
     * @return mixed
     */
    public function getFlgCancellazione()
    {
        return $this->flg_cancellazione;
    }

    /**
     * @param mixed $flg_cancellazione
     */
    public function setFlgCancellazione($flg_cancellazione)
    {
        $this->flg_cancellazione = $flg_cancellazione;
    }

}














