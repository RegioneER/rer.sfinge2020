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
 * @author lfontana
 */
class TC24 extends Base{
    
    /**
     *
     * @var string
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice ruolo soggetto")
      * @ViewElenco( ordine = 1, titolo="Codice ruolo soggetto" )
     */
   protected $cod_ruolo_sog;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione ruolo soggetto")
      * @ViewElenco( ordine = 2, titolo="Descrizione ruolo soggetto" )
     */
    protected $descrizione_ruolo_soggetto;

    /**
     * @return mixed
     */
    public function getCodRuoloSog()
    {
        return $this->cod_ruolo_sog;
    }

    /**
     * @param mixed $cod_ruolo_sog
     */
    public function setCodRuoloSog($cod_ruolo_sog)
    {
        $this->cod_ruolo_sog = $cod_ruolo_sog;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneRuoloSoggetto()
    {
        return $this->descrizione_ruolo_soggetto;
    }

    /**
     * @param mixed $descrizione_ruolo_soggetto
     */
    public function setDescrizioneRuoloSoggetto($descrizione_ruolo_soggetto)
    {
        $this->descrizione_ruolo_soggetto = $descrizione_ruolo_soggetto;
    }


}
