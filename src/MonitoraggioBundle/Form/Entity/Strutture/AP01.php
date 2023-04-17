<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 29/06/17
 * Time: 17:07
 */


namespace MonitoraggioBundle\Form\Entity\Strutture;

use MonitoraggioBundle\Annotations\RicercaFormType;
use MonitoraggioBundle\Annotations\ViewElenco;



class AP01 extends BaseRicercaStruttura
{
    /**
     *
     * @var string
     * @ViewElenco( ordine = 1, titolo="Codice Locale Progetto" )
     */
    protected $cod_locale_progetto;

     /**
     *
     * @var string
     * @ViewElenco( ordine = 4, titolo="Procedura Attivazione" )
     */
    protected $tc1_procedura_attivazione;

    protected $flg_cancellazione;



    /**
     * @return mixed
     */
    public function getCodLocaleProgetto()
    {
        return $this->cod_locale_progetto;
    }

    /**
     * @param mixed $codice_locale_progetto
     */
    public function setCodLocaleProgetto($cod_locale_progetto)
    {
        $this->cod_locale_progetto = $cod_locale_progetto;
    }

    /**
     * @return mixed
     */
    public function getTc1ProceduraAttivazione()
    {
        return $this->tc1_procedura_attivazione;
    }

    /**
     * @param mixed $tc1_procedura_attivazione
     */
    public function setTc1ProceduraAttivazione($tc1_procedura_attivazione)
    {
        $this->tc1_procedura_attivazione = $tc1_procedura_attivazione;
    }

    /**
     * @return mixed
     */
    public function getFlgCancellazione()
    {
        return $this->flg_cancellazione;
    }

    /**
     * @param mixed $flg_cancellazione
     */
    public function setFlgCancellazione($flg_cancellazione)
    {
        $this->flg_cancellazione = $flg_cancellazione;
    }


}














