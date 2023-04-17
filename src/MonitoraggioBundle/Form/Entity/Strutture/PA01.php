<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 12/07/17
 * Time: 14:53
 */


namespace MonitoraggioBundle\Form\Entity\Strutture;
use MonitoraggioBundle\Annotations\RicercaFormType;
use MonitoraggioBundle\Annotations\ViewElenco;


class PA01 extends BaseRicercaStruttura
{


    /**
     * @ViewElenco( ordine = 1, titolo="Codice procedura attivazione" )
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice procedura attivazione")
     */
    protected $cod_proc_att;

    /**
     * @ViewElenco( ordine = 2, titolo="Programma" )
     * @RicercaFormType( ordine = 2, type = "entity", label = "Programma", options={"class": "MonitoraggioBundle\Entity\TC4Programma"})
     */
    protected $tc4_programma;

    /**
     * @ViewElenco( ordine = 3, titolo="Importo" )
     * @RicercaFormType( ordine = 3, type = "moneta", label = "Importo")
     */
    protected $importo;

    /**
     * @ViewElenco( ordine = 4, titolo="Flag cancellazione" )
     * @RicercaFormType( ordine = 4, type = "text", label = "Flag cancellazione")
     */
    protected $flg_cancellazione;




    /**
     * @return mixed
     */
    public function getTc4Programma()
    {
        return $this->tc4_programma;
    }

    /**
     * @param mixed $tc4_programma
     */
    public function setTc4Programma($tc4_programma)
    {
        $this->tc4_programma = $tc4_programma;
    }

    /**
     * @return mixed
     */
    public function getCodProcAtt()
    {
        return $this->cod_proc_att;
    }

    /**
     * @param mixed $cod_proc_att
     */
    public function setCodProcAtt($cod_proc_att)
    {
        $this->cod_proc_att = $cod_proc_att;
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

    /**
     * @return mixed
     */
    public function getImporto()
    {
        return $this->importo;
    }

    /**
     * @param mixed $importo
     */
    public function setImporto($importo)
    {
        $this->importo = $importo;
    }





}