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
class TC36 extends Base{
    
    /**
     *
     * @var string
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice livello gerarchico")
      * @ViewElenco( ordine = 1, titolo="Codice livello gerarchico" )
     */
    protected $cod_liv_gerarchico;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 2, type = "text", label = "Valore livello gerarchico")
      * @ViewElenco( ordine = 2, titolo="Valore livello gerarchico" )
     */
    protected $valore_dati_rilevati;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 3, type = "text", label = "Descrizione livello gerarchico")
      * @ViewElenco( ordine = 3, titolo="Descrizione livello gerarchico" )
     */
    protected $descrizione_codice_livello_gerarchico;

    /**
     *
     * @var string
     * @RicercaFormType( ordine = 4, type = "text", label = "Codice struttura protocollo")
      * @ViewElenco( ordine = 4, titolo="Codice struttura protocollo" )
     */
    protected $cod_struttura_prot;

    /**
     * @return mixed
     */
    public function getCodLivGerarchico()
    {
        return $this->cod_liv_gerarchico;
    }

    /**
     * @param mixed $cod_liv_gerarchico
     */
    public function setCodLivGerarchico($cod_liv_gerarchico)
    {
        $this->cod_liv_gerarchico = $cod_liv_gerarchico;
    }

    /**
     * @return mixed
     */
    public function getValoreDatiRilevati()
    {
        return $this->valore_dati_rilevati;
    }

    /**
     * @param mixed $valore_dati_rilevati
     */
    public function setValoreDatiRilevati($valore_dati_rilevati)
    {
        $this->valore_dati_rilevati = $valore_dati_rilevati;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneCodiceLivelloGerarchico()
    {
        return $this->descrizione_codice_livello_gerarchico;
    }

    /**
     * @param mixed $descrizione_codice_livello_gerarchico
     */
    public function setDescrizioneCodiceLivelloGerarchico($descrizione_codice_livello_gerarchico)
    {
        $this->descrizione_codice_livello_gerarchico = $descrizione_codice_livello_gerarchico;
    }

    /**
     * @return mixed
     */
    public function getCodStrutturaProt()
    {
        return $this->cod_struttura_prot;
    }

    /**
     * @param mixed $cod_struttura_prot
     */
    public function setCodStrutturaProt($cod_struttura_prot)
    {
        $this->cod_struttura_prot = $cod_struttura_prot;
    }
}
