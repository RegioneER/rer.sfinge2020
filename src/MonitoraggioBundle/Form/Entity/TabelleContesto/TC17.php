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
 * Description of TC17
 *
 * @author lfontana
 */
class TC17 extends Base{
 
    /**
     *
     * @var string
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice modalità formativa")
     * @ViewElenco( ordine = 1, titolo="Codice modalità formativa" )
     */
    protected $cod_modalita_formativa;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 2, type = "text", label = "Descrizione sottoclasse modalità formativa")
     * @ViewElenco( ordine = 2, titolo="Descrizione sottoclasse modalità formativa" )
     */
    protected $descrizione_modalita_formativa_sottoclasse;

   /**
     *
     * @var string
     * @RicercaFormType( ordine = 3, type = "text", label = "Descrizione classe modalità formativa")
     * @ViewElenco( ordine = 3, titolo="Descrizione classe modalità formativa" )
     */
    protected $descrizione_classe;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 4, type = "text", label = "Descrizione macroclasse modalità formativa")
     * @ViewElenco( ordine = 4, titolo="Descrizione macroclasse modalità formativa" )
     */
    protected $descrizione_macro_categoria;

    /**
     * @return mixed
     */
    public function getCodModalitaFormativa()
    {
        return $this->cod_modalita_formativa;
    }

    /**
     * @param mixed $cod_modalita_formativa
     */
    public function setCodModalitaFormativa($cod_modalita_formativa)
    {
        $this->cod_modalita_formativa = $cod_modalita_formativa;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneModalitaFormativaSottoclasse()
    {
        return $this->descrizione_modalita_formativa_sottoclasse;
    }

    /**
     * @param mixed $descrizione_modalita_formativa_sottoclasse
     */
    public function setDescrizioneModalitaFormativaSottoclasse($descrizione_modalita_formativa_sottoclasse)
    {
        $this->descrizione_modalita_formativa_sottoclasse = $descrizione_modalita_formativa_sottoclasse;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneClasse()
    {
        return $this->descrizione_classe;
    }

    /**
     * @param mixed $descrizione_classe
     */
    public function setDescrizioneClasse($descrizione_classe)
    {
        $this->descrizione_classe = $descrizione_classe;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneMacroCategoria()
    {
        return $this->descrizione_macro_categoria;
    }

    /**
     * @param mixed $descrizione_macro_categoria
     */
    public function setDescrizioneMacroCategoria($descrizione_macro_categoria)
    {
        $this->descrizione_macro_categoria = $descrizione_macro_categoria;
    }
}
