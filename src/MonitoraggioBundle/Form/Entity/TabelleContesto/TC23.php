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
/**
 * Description of TC16
 *
 * @author lfontana
 */
class TC23 extends Base{
    
    /**
     *
     * @var string
     * @RicercaFormType( ordine = 1, type = "text", label = "Tipologia procedura aggiudicazione")
      * @ViewElenco( ordine = 1, titolo="Tipologia procedura aggiudicazione" )
     */
   protected $tipo_proc_agg;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione tipologia procedura aggiudicazione")
      * @ViewElenco( ordine = 2, titolo="Descrizione tipologia procedura aggiudicazione" )
     */
    protected $descrizione_tipologia_procedura_aggiudicazione;


    /**
     * @return mixed
     */
    public function getTipoProcAgg()
    {
        return $this->tipo_proc_agg;
    }

    /**
     * @param mixed $tipo_proc_agg
     */
    public function setTipoProcAgg($tipo_proc_agg)
    {
        $this->tipo_proc_agg = $tipo_proc_agg;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneTipologiaProceduraAggiudicazione()
    {
        return $this->descrizione_tipologia_procedura_aggiudicazione;
    }

    /**
     * @param mixed $descrizione_tipologia_procedura_aggiudicazione
     */
    public function setDescrizioneTipologiaProceduraAggiudicazione($descrizione_tipologia_procedura_aggiudicazione)
    {
        $this->descrizione_tipologia_procedura_aggiudicazione = $descrizione_tipologia_procedura_aggiudicazione;
    }


}
