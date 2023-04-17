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
 * Description of TC12_2
 *
 * @author lfontana
 */
class TC12_2 extends Base{
    
    /**
     *
     * @var string
     * @ViewElenco( ordine = 1, titolo="Codice della forma di finanziamento" )
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice della forma di finanziamento")
     */
    protected $cod_classificazione_fi;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 2, titolo="Descrizione della forma di finanziamento" )
     * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione della forma di finanziamento")
     */
    protected $desc_classificazione_fi;

    /**
     *
     * @var string
     * @ViewElenco( ordine = 3, titolo="Origine" )
     * @RicercaFormType( ordine = 3, type = "text", label = "Origine")
     */
    protected $origine_dato;

    /**
     * @return mixed
     */
    public function getCodClassificazioneFi()
    {
        return $this->cod_classificazione_fi;
    }

    /**
     * @param mixed $cod_classificazione_fi
     */
    public function setCodClassificazioneFi($cod_classificazione_fi)
    {
        $this->cod_classificazione_fi = $cod_classificazione_fi;
    }

    /**
     * @return mixed
     */
    public function getDescClassificazioneFi()
    {
        return $this->desc_classificazione_fi;
    }

    /**
     * @param mixed $desc_classificazione_fi
     */
    public function setDescClassificazioneFi($desc_classificazione_fi)
    {
        $this->desc_classificazione_fi = $desc_classificazione_fi;
    }

    /**
     * @return mixed
     */
    public function getOrigineDato()
    {
        return $this->origine_dato;
    }

    /**
     * @param mixed $origine_dato
     */
    public function setOrigineDato($origine_dato)
    {
        $this->origine_dato = $origine_dato;
    }
}
