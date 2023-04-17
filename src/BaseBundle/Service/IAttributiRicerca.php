<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 05/01/16
 * Time: 18:33
 */

namespace BaseBundle\Service;

/**
 * Interface IAttributiRicerca
 * Interfaccia che vincola la ricerca da utilizzare con il servizio di ricerca
 *
 * Un esempio di utilizzo è in AziendaController -> elencoAziendeAction
 *
 * @package BaseBundle\Service
 */
interface IAttributiRicerca
{
    /**
     * Deve ritornare il nome della classe compreso di namespace del form type che renderizza la ricerca
     * @return string
     */
    public function getType();

    /**
     * Deve ritornare il nome del repository su cui viene invocato il metodo di ricerca
     * @return string
     */
    public function getNomeRepository();

    /**
     * Deve tornare il nome del metodo nel reposotory precedente che si occupa di fare la ricerca.
     * Il metodo deve accettare il modello su cui sono mappati i dati della ricerca e restituire un istanza di Query.
     *
     * Nel caso si debbano aggiungere altri parametri alla ricerca(ex valori di default) possono essere messi come attributi
     * all'oggetto modello del form type e valorizzati nel controller o messi come attributi hidden
     * @return string
     */
    public function getNomeMetodoRepository();

    /**
     * Indica il numero di elementi per pagina da mostrare nel caso il valore sia nullo viene preso quello di default
     * settato nel parameters.ini
     * @return int|null
     */
    public function getNumeroElementiPerPagina();

    /**
     * Nome del parametro nella query url che mappa il parametro della pagina
     * @return string
     */
    public function getNomeParametroPagina();

    public function getNumeroElementi();

    public function setNumeroElementi($numero_elementi);

    public function getFiltroAttivo();

    public function setFiltroAttivo($filtro_attivo);

    public function mostraNumeroElementi();
	
	public function isRicercaVuota();
	
	public function getConsentiRicercaVuota();

	public function setConsentiRicercaVuota($consenti_ricerca_vuota);
	
	public function mergeFreshData($freshData);	
}