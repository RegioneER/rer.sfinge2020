<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 05/01/16
 * Time: 16:09
 */

namespace SoggettoBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

class RicercaComuneUnione extends AttributiRicerca
{

    private $persona_id;

    private $incarico;

    private $denominazione;

    private $partita_iva;

    private $codice_fiscale;


    /**
     * @return mixed
     */
    public function getIncarico()
    {
        return $this->incarico;
    }

    /**
     * @param mixed $incarico
     */
    public function setIncarico($incarico)
    {
        $this->incarico = $incarico;
    }

    /**
     * @return mixed
     */
    public function getPersonaId()
    {
        return $this->persona_id;
    }

    /**
     * @param mixed $persona_id
     */
    public function setPersonaId($persona_id)
    {
        $this->persona_id = $persona_id;
    }



    /**
     * @return mixed
     */
    public function getCodiceFiscale()
    {
        return $this->codice_fiscale;
    }

    /**
     * @param mixed $codice_fiscale
     */
    public function setCodiceFiscale($codice_fiscale)
    {
        $this->codice_fiscale = $codice_fiscale;
    }


    /**
     * @return mixed
     */
    public function getDenominazione()
    {
        return $this->denominazione;
    }

    /**
     * @param mixed $denominazione
     */
    public function setDenominazione($denominazione)
    {
        $this->denominazione = $denominazione;
    }

    /**
     * @return mixed
     */
    public function getPartitaIva()
    {
        return $this->partita_iva;
    }

    /**
     * @param mixed $partita_iva
     */
    public function setPartitaIva($partita_iva)
    {
        $this->partita_iva = $partita_iva;
    }



    public function getType()
    {
        return "SoggettoBundle\Form\RicercaComuneUnioneType";
    }

    public function getNomeRepository()
    {
        return "SoggettoBundle:ComuneUnione";
    }

    public function getNomeMetodoRepository()
    {
        return "cercaDaPersonaIncarico";
    }


    public function getNumeroElementiPerPagina()
    {
        return null;
    }

    public function getNomeParametroPagina()
    {
        return "page";
    }

}