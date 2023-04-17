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
class TC39 extends Base{
    
    /**
     *
     * @var string
        * @RicercaFormType( ordine = 1, type = "text", label = "Codice causale pagamento")
      * @ViewElenco( ordine = 1, titolo="Codice causale pagamento" )
     */
   protected $causale_pagamento;

    /**
     * 
     *
     * @var string
        * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione causale pagamento")
      * @ViewElenco( ordine = 2, titolo="Descrizione causale pagamento" )
     */
    protected $descrizione_causale_pagamento;

    /**
     *
     * @var string
        * @RicercaFormType( ordine = 3, type = "text", label = "Tipologia del pagamento")
      * @ViewElenco( ordine = 3, titolo="Tipologia del pagamento" )
     */
    protected $tipologia_pagamento;

    /**
     * @return mixed
     */
    public function getCausalePagamento()
    {
        return $this->causale_pagamento;
    }

    /**
     * @param mixed $causale_pagamento
     */
    public function setCausalePagamento($causale_pagamento)
    {
        $this->causale_pagamento = $causale_pagamento;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneCausalePagamento()
    {
        return $this->descrizione_causale_pagamento;
    }

    /**
     * @param mixed $descrizione_causale_pagamento
     */
    public function setDescrizioneCausalePagamento($descrizione_causale_pagamento)
    {
        $this->descrizione_causale_pagamento = $descrizione_causale_pagamento;
    }

    /**
     * @return mixed
     */
    public function getTipologiaPagamento()
    {
        return $this->tipologia_pagamento;
    }

    /**
     * @param mixed $tipologia_pagamento
     */
    public function setTipologiaPagamento($tipologia_pagamento)
    {
        $this->tipologia_pagamento = $tipologia_pagamento;
    }


}
