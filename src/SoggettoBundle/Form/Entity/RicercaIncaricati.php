<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 05/01/16
 * Time: 16:09
 */

namespace SoggettoBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

class RicercaIncaricati extends AttributiRicerca
{

    protected $soggetto_id;

    protected $incarico;

    protected $nome;

    protected $cognome;

    protected $codice_fiscale;

    protected $email;

    protected $denominazione;

    protected $stato_incarico;
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
    public function getCognome()
    {
        return $this->cognome;
    }

    /**
     * @param mixed $cognome
     */
    public function setCognome($cognome)
    {
        $this->cognome = $cognome;
    }

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
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * @param mixed $nome
     */
    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    /**
     * @return mixed
     */
    public function getSoggettoId()
    {
        return $this->soggetto_id;
    }

    /**
     * @param mixed $soggetto_id
     */
    public function setSoggettoId($soggetto_id)
    {
        $this->soggetto_id = $soggetto_id;
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
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getStatoIncarico()
    {
        return $this->stato_incarico;
    }

    /**
     * @param mixed $stato_incarico
     */
    public function setStatoIncarico($stato_incarico)
    {
        $this->stato_incarico = $stato_incarico;
    }


    public function getType()
    {
        return "SoggettoBundle\Form\RicercaIncaricatiType";
    }

    public function getNomeRepository()
    {
        return "AnagraficheBundle:Persona";
    }

    public function getNomeMetodoRepository()
    {
        return "cercaIncaricati";
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