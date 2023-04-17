<?php

namespace MonitoraggioBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;
use MonitoraggioBundle\Entity\MonitoraggioEsportazione;

class RicercaTavolaEsportata extends AttributiRicerca
{
    /**
     * @var MonitoraggioEsportazione
     */
    protected $esportazione;

    /**
     * @var string
     */
    protected $struttura;
    /**
     * @var string
     */
    protected $progressivo;

    /**
     * @var int
     */
    protected $numElementiPagina;

   
    public function __construct(MonitoraggioEsportazione $esportazione)
    {
        $this->esportazione = $esportazione;
    }

    /**
     * @return MonitoraggioEsportazione
     */
    public function getEsportazione()
    {
        return $this->esportazione;
    }

    
    public function setEsportazione(MonitoraggioEsportazione $esportazione): self
    {
        $this->esportazione = $esportazione;

        return $this;
    }

    /**
     * @return string
     */
    public function getStruttura()
    {
        return $this->struttura;
    }

    /**
     * @param $struttura
     *
     * @return self
     */
    public function setStruttura($struttura)
    {
        $this->struttura = $struttura;

        return $this;
    }

    /**
     * @return integer
     */
    public function getProgressivo()
    {
        return $this->progressivo;
    }

    public function setProgressivo($progressivo)
    {
        $this->progressivo = $progressivo;

        return $this;
    }

    /**
     * Deve ritornare il nome della classe compreso di namespace del form type che renderizza la ricerca.
     *
     * @return string
     */
    public function getType()
    {
        return 'MonitoraggioBundle\Form\Ricerca\ErroriEsportazioneType';
    }

    /**
     * Deve ritornare il nome del repository su cui viene invocato il metodo di ricerca.
     *
     * @return string
     */
    public function getNomeRepository()
    {
        return 'MonitoraggioBundle:MonitoraggioConfigurazioneEsportazioneErrore';
    }

    /**
     * Deve tornare il nome del metodo nel reposotory precedente che si occupa di fare la ricerca.
     * Il metodo deve accettare il modello su cui sono mappati i dati della ricerca e restituire un istanza di Query.
     *
     * Nel caso si debbano aggiungere altri parametri alla ricerca(ex valori di default) possono essere messi come attributi
     * all'oggetto modello del form type e valorizzati nel controller o messi come attributi hidden
     *
     * @return string
     */
    public function getNomeMetodoRepository()
    {
        return 'ricercaErrori';
    }

    /**
     * Indica il numero di elementi per pagina da mostrare nel caso il valore sia nullo viene preso quello di default
     * settato nel parameters.ini
     * @return int|null
     */
    public function getNumeroElementiPerPagina(){
        return $this->numElementiPagina;
    }

    public function setNumeroElementiPerPagina($numElementi = null){
        return $this->numElementiPagina = $numElementi;
    }

        /**
     * Nome del parametro nella query url che mappa il parametro della pagina
     * @return string
     */
    public function getNomeParametroPagina(){
        return 'page';
    }
}
