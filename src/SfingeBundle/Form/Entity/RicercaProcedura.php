<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 05/01/16
 * Time: 16:09
 */

namespace SfingeBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

class RicercaProcedura extends AttributiRicerca
{

    private $titolo;

    private $atto;

    private $tipo;

    private $responsabile;

    private $asse;

    private $amministrazione_emittente;

    private $anno_programmazione;
	
	private $utente;
	
	private $admin;
	
	protected $responsabili = array();

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
    public function getResponsabile()
    {
        return $this->responsabile;
    }

    /**
     * @param mixed $responsabile
     */
    public function setResponsabile($responsabile)
    {
        $this->responsabile = $responsabile;
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
    public function getAmministrazioneEmittente()
    {
        return $this->amministrazione_emittente;
    }

    /**
     * @param mixed $amministrazione_emittente
     */
    public function setAmministrazioneEmittente($amministrazione_emittente)
    {
        $this->amministrazione_emittente = $amministrazione_emittente;
    }

    /**
     * @return mixed
     */
    public function getAnnoProgrammazione()
    {
        return $this->anno_programmazione;
    }

    /**
     * @param mixed $anno_programmazione
     */
    public function setAnnoProgrammazione($anno_programmazione)
    {
        $this->anno_programmazione = $anno_programmazione;
    }
	
	public function getUtente() {
		return $this->utente;
	}

	public function setUtente($utente) {
		$this->utente = $utente;
	}
	
	function getAdmin() {
		return $this->admin;
	}

	function setAdmin($admin) {
		$this->admin = $admin;
	}
	
    public function getType()
    {
        return "SfingeBundle\Form\RicercaProceduraType";
    }

    public function getNomeRepository()
    {
        return "SfingeBundle:Procedura";
    }

    public function getNomeMetodoRepository()
    {
        return "cercaProcedure";
    }

    public function getNumeroElementiPerPagina()
    {
        return null;
    }

    public function getNomeParametroPagina()
    {
        return "page";
    }
	
	public function getResponsabili() {
		return $this->responsabili;
	}

	public function setResponsabili($responsabili) {
		$this->responsabili = $responsabili;
	}
	
	public function mergeFreshData($freshData) {
		$this->setUtente($freshData->getUtente());
		$this->setResponsabili($freshData->getResponsabili());
	}

}