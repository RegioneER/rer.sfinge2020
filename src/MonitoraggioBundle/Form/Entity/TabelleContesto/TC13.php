<?php

namespace MonitoraggioBundle\Form\Entity\TabelleContesto;

use MonitoraggioBundle\Annotations\RicercaFormType;
use MonitoraggioBundle\Annotations\ViewElenco;

class TC13 extends Base{
    /**
     *
     * @var string
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice gruppo vulnerabile")
      * @ViewElenco( ordine = 1, titolo="Codice" )
     */
    protected $cod_vulnerabili;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione gruppo vulnerabile")
      * @ViewElenco( ordine = 2, titolo="Descrizione" )
     */
    protected $desc_vulnerabili;

    
    /**
     * @return mixed
     */
    public function getCodVulnerabili()
    {
        return $this->cod_vulnerabili;
    }

    /**
     * @param mixed $cod_vulnerabili
     */
    public function setCodVulnerabili($cod_vulnerabili)
    {
        $this->cod_vulnerabili = $cod_vulnerabili;
    }

    /**
     * @return mixed
     */
    public function getDescVulnerabili()
    {
        return $this->desc_vulnerabili;
    }

    /**
     * @param mixed $desc_vulnerabili
     */
    public function setDescVulnerabili($desc_vulnerabili)
    {
        $this->desc_vulnerabili = $desc_vulnerabili;
    }
}