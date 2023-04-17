<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 05/01/16
 * Time: 16:09
 */

namespace AnagraficheBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

class RicercaPersonaAdmin extends AttributiRicerca
{


    private $soggetto_incarico;
    private $soggetto_id;
    private $soggetto_denominazione;
    private $soggetto_piva;

    private $nome;
    private $cognome;
    private $codice_fiscale;

    private $ruolo;
    private $username;
    private $email;
	
	private $utente_ricercante;

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
    public function getRuolo()
    {
        return $this->ruolo;
    }

    /**
     * @param mixed $ruolo
     */
    public function setRuolo($ruolo)
    {
        $this->ruolo = $ruolo;
    }

    /**
     * @return mixed
     */
    public function getSoggettoDenominazione()
    {
        return $this->soggetto_denominazione;
    }

    /**
     * @param mixed $soggetto_denominazione
     */
    public function setSoggettoDenominazione($soggetto_denominazione)
    {
        $this->soggetto_denominazione = $soggetto_denominazione;
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
    public function getSoggettoIncarico()
    {
        return $this->soggetto_incarico;
    }

    /**
     * @param mixed $soggetto_incarico
     */
    public function setSoggettoIncarico($soggetto_incarico)
    {
        $this->soggetto_incarico = $soggetto_incarico;
    }

    /**
     * @return mixed
     */
    public function getSoggettoPiva()
    {
        return $this->soggetto_piva;
    }

    /**
     * @param mixed $soggetto_piva
     */
    public function setSoggettoPiva($soggetto_piva)
    {
        $this->soggetto_piva = $soggetto_piva;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }



    public function getType()
    {
        return "AnagraficheBundle\Form\RicercaPersonaAdminType";
    }

    public function getNomeRepository()
    {
        return "AnagraficheBundle:Persona";
    }

    public function getNomeMetodoRepository()
    {
        return "cercaSuperAdmin";
    }

    public function getNumeroElementiPerPagina()
    {
        return null;
    }

    public function getNomeParametroPagina()
    {
        return "page";
    }
	
	public function getUtenteRicercante() {
		return $this->utente_ricercante;
	}

	public function setUtenteRicercante($utente_ricercante) {
		$this->utente_ricercante = $utente_ricercante;
	}


}