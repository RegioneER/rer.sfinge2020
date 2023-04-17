<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 05/01/16
 * Time: 16:09
 */

namespace NotizieBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

class RicercaNotiziaAdmin extends AttributiRicerca
{
    private $titolo;
    private $testo;
    private $visibilita;
    private $utente_ricercante;

	/**
     * @return mixed
     */
    public function getTitolo()
    {
        return $this->titolo;
    }

    /**
     * @param mixed $titolo
     */
    public function setTitolo($titolo)
    {
        $this->titolo = $titolo;
    }

    /**
     * @return mixed
     */
    public function getTesto()
    {
        return $this->testo;
    }

    /**
     * @param mixed $testo
     */
    public function setTesto($testo)
    {
        $this->testo = $testo;
    }

    /**
     * @return mixed
     */
    public function getVisibilita()
    {
        return $this->visibilita;
    }

    /**
     * @param mixed $visibilita
     */
    public function setVisibilita($visibilita)
    {
        $this->visibilita = $visibilita;
    }

    public function getType()
    {
        return "NotizieBundle\Form\RicercaNotizieType";
    }

    public function getNomeRepository()
    {
        return "NotizieBundle:Notizia";
    }

    public function getNomeMetodoRepository()
    {
        return "cercaNotizie";
    }

    public function getNumeroElementiPerPagina()
    {
        return null;
    }

    public function getNomeParametroPagina()
    {
        return "page";
    }

    public function getUtenteRicercante()
    {
        return $this->utente_ricercante;
    }

    public function setUtenteRicercante($utente_ricercante) {
        $this->utente_ricercante = $utente_ricercante;
    }
}