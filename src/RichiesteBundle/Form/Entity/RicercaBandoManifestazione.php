<?php

namespace RichiesteBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

class RicercaBandoManifestazione extends AttributiRicerca
{

    private $stato;

    private $titolo;

    private $atto;

    private $tipo;

    private $asse;

    private $statoProcedura;
	
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
    public function getAtto()
    {
        return $this->atto;
    }

    /**
     * @param mixed $atto
     */
    public function setAtto($atto)
    {
        $this->atto = $atto;
    }

    /**
     * @return mixed
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * @param mixed $tipo
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
    }

    /**
     * @return mixed
     */
    public function getAsse()
    {
        return $this->asse;
    }

    /**
     * @param mixed $asse
     */
    public function setAsse($asse)
    {
        $this->asse = $asse;
    }

    /**
     * @return mixed
     */
    public function getStato()
    {
        return $this->stato;
    }

    /**
     * @param mixed $stato
     */
    public function setStato($stato)
    {
        $this->stato = $stato;
    }
	
	public function getStatoProcedura() {
		return $this->statoProcedura;
	}

	public function setStatoProcedura($statoProcedura) {
		$this->statoProcedura = $statoProcedura;
	}

    public function getType()
    {
        return "RichiesteBundle\Form\RicercaBandoManifestazioneType";
    }

    public function getNomeRepository()
    {
        return "SfingeBundle:Procedura";
    }

    public function getNomeMetodoRepository()
    {
        return "cercaBandiManifestazioni";
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