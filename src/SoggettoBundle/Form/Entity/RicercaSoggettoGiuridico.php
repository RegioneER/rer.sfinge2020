<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 05/01/16
 * Time: 16:09
 */

namespace SoggettoBundle\Form\Entity;


/**
 * Class RicercaSoggettoGiuridico
 */
class RicercaSoggettoGiuridico extends RicercaSoggetto
{
    protected $persona_id;

    protected $incarico;

    protected $tipo;

    protected $denominazione;

    protected $partita_iva;

    protected $codice_fiscale;

    protected $data_costituzione_da;

    protected $data_costituzione_a;


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
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * @param mixed $tipo
     */
    public function setTipo($tipo): void
    {
        $this->tipo = $tipo;
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

    /**
     * @return mixed
     */
    public function getDataCostituzioneA()
    {
        return $this->data_costituzione_a;
    }

    /**
     * @param mixed $data_costituzione_a
     */
    public function setDataCostituzioneA($data_costituzione_a)
    {
        $this->data_costituzione_a = $data_costituzione_a;
    }

    /**
     * @return mixed
     */
    public function getDataCostituzioneDa()
    {
        return $this->data_costituzione_da;
    }

    /**
     * @param mixed $data_costituzione_da
     */
    public function setDataCostituzioneDa($data_costituzione_da)
    {
        $this->data_costituzione_da = $data_costituzione_da;
    }



    public function getType()
    {
        return "SoggettoBundle\Form\RicercaSoggettoGiuridicoType";
    }

    public function getNomeRepository()
    {
        return "SoggettoBundle:Soggetto";
    }

    public function getNomeMetodoRepository()
    {
        return "cercaSoggettoGiuridicoDaPersonaIncarico";
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