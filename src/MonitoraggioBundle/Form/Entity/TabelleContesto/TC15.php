<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form\Entity\TabelleContesto;


use MonitoraggioBundle\Annotations\RicercaFormType;
use MonitoraggioBundle\Annotations\ViewElenco;
/**
 * Description of TC14
 *
 * @author lfontana
 */
class TC15 extends Base{
    
    /**
     *
     * @var string
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice strumento attuativo")
     * @ViewElenco( ordine = 1, titolo="Codice" )
     */
     protected $cod_stru_att;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione strumento attuativo")
      * @ViewElenco( ordine = 2, titolo="Descrizione" )
     */
    protected $desc_strumento_attuativo;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 3, type = "text", label = "Denominazione soggetto responsabile")
      * @ViewElenco( ordine = 3, titolo="Soggetto responsabile" )
     */
    protected $denom_resp_stru_att;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 4, type = "birthday", label = "Data approvazione", options= {"widget": "single_text", "input": "datetime", "format": "dd/MM/yyyy"})
      * @ViewElenco( ordine = 4, titolo="Data approvazione" )
     */
    protected $data_approv_stru_att;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 5, type = "text", label = "Codice tipologia strumento attuativo")
     * @ViewElenco( ordine = 5, titolo="Codice tipologia" )
     */
    protected $cod_tip_stru_att;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 6, type = "text", label = "Descrizione tipologia strumento attuativo")
      * @ViewElenco( ordine = 6, titolo="Descrizione tipologia" )
     */
    protected $desc_tip_stru_att;

    
    /**
     * @return mixed
     */
    public function getCodStruAtt()
    {
        return $this->cod_stru_att;
    }

    /**
     * @param mixed $cod_stru_att
     */
    public function setCodStruAtt($cod_stru_att)
    {
        $this->cod_stru_att = $cod_stru_att;
    }

    /**
     * @return mixed
     */
    public function getDescStrumentoAttuativo()
    {
        return $this->desc_strumento_attuativo;
    }

    /**
     * @param mixed $desc_strumento_attuativo
     */
    public function setDescStrumentoAttuativo($desc_strumento_attuativo)
    {
        $this->desc_strumento_attuativo = $desc_strumento_attuativo;
    }

    /**
     * @return mixed
     */
    public function getDenomRespStruAtt()
    {
        return $this->denom_resp_stru_att;
    }

    /**
     * @param mixed $denom_resp_stru_att
     */
    public function setDenomRespStruAtt($denom_resp_stru_att)
    {
        $this->denom_resp_stru_att = $denom_resp_stru_att;
    }

    /**
     * @return mixed
     */
    public function getDataApprovStruAtt()
    {
        return $this->data_approv_stru_att;
    }

    /**
     * @param mixed $data_approv_stru_att
     */
    public function setDataApprovStruAtt($data_approv_stru_att)
    {
        $this->data_approv_stru_att = $data_approv_stru_att;
    }

    /**
     * @return mixed
     */
    public function getCodTipStruAtt()
    {
        return $this->cod_tip_stru_att;
    }

    /**
     * @param mixed $cod_tip_stru_att
     */
    public function setCodTipStruAtt($cod_tip_stru_att)
    {
        $this->cod_tip_stru_att = $cod_tip_stru_att;
    }

    /**
     * @return mixed
     */
    public function getDescTipStruAtt()
    {
        return $this->desc_tip_stru_att;
    }

    /**
     * @param mixed $desc_tip_stru_att
     */
    public function setDescTipStruAtt($desc_tip_stru_att)
    {
        $this->desc_tip_stru_att = $desc_tip_stru_att;
    }


}
