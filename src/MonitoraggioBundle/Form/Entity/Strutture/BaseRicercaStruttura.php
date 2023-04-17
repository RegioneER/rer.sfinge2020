<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 30/06/17
 * Time: 10:33
 */

namespace MonitoraggioBundle\Form\Entity\Strutture;

use BaseBundle\Service\AttributiRicerca;
use MonitoraggioBundle\Entity\ElencoStruttureProtocollo;


abstract class BaseRicercaStruttura extends AttributiRicerca{
    const BASE_FORM_TYPE = 'Base';
    const BASE_METODO_RICERCA = 'findAllFiltered';
    const NAMESPACE_RICERCA = 'MonitoraggioBundle\Form\Ricerca\\';

    protected $numeroElementiPerPagina;
    protected $struttura;

    public function __construct(ElencoStruttureProtocollo $struttura = null){
        $this->struttura = $struttura;
    }

    public function getNomeParametroPagina() {

    }

    public function getNumeroElementiPerPagina() {
        return $this->numeroElementiPerPagina;
    }

    /*
     * Restituisce il Nome File del FormType della ricerca
     */
    public function getType() {
        if(!is_null($this->struttura)){
            $classe = self::NAMESPACE_RICERCA . $this->struttura->getCodice() .'Type';
            if( class_exists($classe)){
                return $classe;
            }
        }
        return self::NAMESPACE_RICERCA.  self::BASE_FORM_TYPE .'Type';
    }

    public function setNumeroElementiPerPagina($numeroElementiPerPagina) {
        $this->numeroElementiPerPagina = $numeroElementiPerPagina;
    }

    public function getNomeMetodoRepository() {
        return self::BASE_METODO_RICERCA;
    }

    public function getNomeRepository() {
        return 'MonitoraggioBundle:' . (is_null($this->struttura) ? 'ElencoStruttureProtocollo' :  $this->struttura->getClasseEntity());
    }

}















