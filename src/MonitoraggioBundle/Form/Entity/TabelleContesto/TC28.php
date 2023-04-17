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
class TC28 extends Base{
    
    /**
     *
     * @var string
        * @RicercaFormType( ordine = 1, type = "text", label = "Codice titolo di studio")
      * @ViewElenco( ordine = 1, titolo="Codice titolo di studio" )
     */
   protected $titolo_studio;

    /**
     *
     * @var string
        * @RicercaFormType( ordine = 2, type = "text", label = "Codice cittadinanza")
      * @ViewElenco( ordine = 2, titolo="Codice cittadinanza" )
     */
    protected $descrizione_titolo_studio;

    /**
     *
     * @var string
        * @RicercaFormType( ordine = 3, type = "text", label = "Livello ISCED")
      * @ViewElenco( ordine = 3, titolo="Livello ISCED" )
     */
    protected $isced;

     /**
     * @return mixed
     */
    public function getTitoloStudio()
    {
        return $this->titolo_studio;
    }

    /**
     * @param mixed $titolo_studio
     */
    public function setTitoloStudio($titolo_studio)
    {
        $this->titolo_studio = $titolo_studio;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneTitoloStudio()
    {
        return $this->descrizione_titolo_studio;
    }

    /**
     * @param mixed $descrizione_titolo_studio
     */
    public function setDescrizioneTitoloStudio($descrizione_titolo_studio)
    {
        $this->descrizione_titolo_studio = $descrizione_titolo_studio;
    }

    /**
     * @return mixed
     */
    public function getIsced()
    {
        return $this->isced;
    }

    /**
     * @param mixed $isced
     */
    public function setIsced($isced)
    {
        $this->isced = $isced;
    }


}
