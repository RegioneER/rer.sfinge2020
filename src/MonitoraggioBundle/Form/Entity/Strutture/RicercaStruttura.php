<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 23/06/17
 * Time: 17:00
 */

namespace MonitoraggioBundle\Form\Entity\Strutture;

use MonitoraggioBundle\Form\Entity\Strutture\BaseRicercaStruttura;


class RicercaStruttura extends BaseRicercaStruttura {

    const TYPE = 'MonitoraggioBundle\Form\Ricerca\RicercaStrutturaType';

    protected $descrizione;

    protected $codice;


    public function getType() {
        return self::TYPE;
    }

    /**
     * @return mixed
     */
    public function getDescrizione()
    {
        return $this->descrizione;
    }

    /**
     * @param mixed $descrizione
     */
    public function setDescrizione($descrizione)
    {
        $this->descrizione = $descrizione;
    }

    /**
     * @return mixed
     */
    public function getCodice()
    {
        return $this->codice;
    }

    /**
     * @param mixed $codice
     */
    public function setCodice($codice)
    {
        $this->codice = $codice;
    }


}


