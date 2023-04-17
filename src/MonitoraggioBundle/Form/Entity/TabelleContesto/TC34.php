<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form\Entity\TabelleContesto;

use MonitoraggioBundle\Annotations\RicercaFormType;
use MonitoraggioBundle\Annotations\ViewElenco;
/**
/**
 * Description of TC16
 *
 * @author lfontana
 */
class TC34 extends Base{
    
    /**
     *
     * @var string
        * @RicercaFormType( ordine = 1, type = "text", label = "Codice delibera CIPE")
      * @ViewElenco( ordine = 1, titolo="Codice delibera CIPE" )
     */
   protected $cod_del_cipe;

    /**
     *
     * @var string
        * @RicercaFormType( ordine = 2, type = "text", label = "Numero delibera CIPE")
      * @ViewElenco( ordine = 2, titolo="Numero delibera CIPE" )
     */
    protected $numero;

    /**
     *
     * @var string
        * @RicercaFormType( ordine = 3, type = "text", label = "Anno delibera CIPE")
      * @ViewElenco( ordine = 3, titolo="Anno delibera CIPE" )
     */
    protected $anno;

    /**
     *
     * @var string
        * @RicercaFormType( ordine = 4, type = "text", label = "Tipologia Quota")
      * @ViewElenco( ordine = 4, titolo="Tipologia Quota" )
     */
    protected $tipo_quota;

    /**
     *
     * @var string
        * @RicercaFormType( ordine = 5, type = "text", label = "Descrizione Quota")
      * @ViewElenco( ordine = 5, titolo="Descrizione Quota" )
     */
    protected $descrizione_quota;


   
    /**
     * @return mixed
     */
    public function getCodDelCipe()
    {
        return $this->cod_del_cipe;
    }

    /**
     * @param mixed $cod_del_cipe
     */
    public function setCodDelCipe($cod_del_cipe)
    {
        $this->cod_del_cipe = $cod_del_cipe;
    }

    /**
     * @return mixed
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * @param mixed $numero
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;
    }

    /**
     * @return mixed
     */
    public function getAnno()
    {
        return $this->anno;
    }

    /**
     * @param mixed $anno
     */
    public function setAnno($anno)
    {
        $this->anno = $anno;
    }

    /**
     * @return mixed
     */
    public function getTipoQuota()
    {
        return $this->tipo_quota;
    }

    /**
     * @param mixed $tipo_quota
     */
    public function setTipoQuota($tipo_quota)
    {
        $this->tipo_quota = $tipo_quota;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneQuota()
    {
        return $this->descrizione_quota;
    }

    /**
     * @param mixed $descrizione_quota
     */
    public function setDescrizioneQuota($descrizione_quota)
    {
        $this->descrizione_quota = $descrizione_quota;
    }


}
