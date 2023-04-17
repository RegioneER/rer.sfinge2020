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
class TC41 extends Base{
    
    /**
     *
     * @var string
        * @RicercaFormType( ordine = 1, type = "text", label = "Codice domanda pagamento")
      * @ViewElenco( ordine = 1, titolo="Codice domanda pagamento" )
     */
   protected $id_domanda_pagamento;

    /**
     *
     * @var string
        * @RicercaFormType( ordine = 2, type = "entity", label = "Programma", options={"class": "MonitoraggioBundle\Entity\TC4Programma"})
      * @ViewElenco( ordine = 2, titolo="Programma" )
     */
    protected $programma;

   /**
     *
     * @var string
        * @RicercaFormType( ordine = 3, type = "entity", label = "Fonte finanziaria", options={"class": "MonitoraggioBundle\Entity\TC33FonteFinanziaria"})
      * @ViewElenco( ordine = 3, titolo="Fonte finanziaria" )
     */
    protected $fondo;

    
    /**
     * @return mixed
     */
    public function getIdDomandaPagamento()
    {
        return $this->id_domanda_pagamento;
    }

    /**
     * @param mixed $id_domanda_pagamento
     */
    public function setIdDomandaPagamento($id_domanda_pagamento)
    {
        $this->id_domanda_pagamento = $id_domanda_pagamento;
    }

    /**
     * @return mixed
     */
    public function getProgramma()
    {
        return $this->programma;
    }

    /**
     * @param mixed $programma
     */
    public function setProgramma($programma)
    {
        $this->programma = $programma;
    }

    /**
     * @return mixed
     */
    public function getFondo()
    {
        return $this->fondo;
    }

    /**
     * @param mixed $fondo
     */
    public function setFondo($fondo)
    {
        $this->fondo = $fondo;
    }





}
