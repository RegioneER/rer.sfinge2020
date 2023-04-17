<?php
/**
 * @author lfontana
*/

namespace MonitoraggioBundle\Form\Entity;

class Regione
{
    /**
     * @var string
     */
    protected $codice;
    /**
     * @var string
     */
    protected $descrizione;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->descrizione;
    }

    public function __construct($codice, $descrizione)
    {
        $this->codice = $codice;
        $this->descrizione = $descrizione;
    }

    /**
     * @return string
     */
    public function getCodice()
    {
        return $this->codice;
    }

    /**
     * @param string $codice
     *
     * @return Regione
     */
    public function setCodice($codice)
    {
        $this->codice = $codice;

        return $this;
    }

    /**
     * @param string $descrizione
     *
     * @return Regione
     */
    public function setDescrizione($descrizione)
    {
        $this->descrizione = $descrizione;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescrizione()
    {
        return $this->descrizione;
    }
}
