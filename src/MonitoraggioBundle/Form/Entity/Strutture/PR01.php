<?php

namespace MonitoraggioBundle\Form\Entity\Strutture;
use MonitoraggioBundle\Annotations\RicercaFormType;
use MonitoraggioBundle\Annotations\ViewElenco;

/**
 * Description of PR00
 *
 * @author gorlando
 */
class PR01 extends BaseRicercaStruttura {
	
	
	
	/**
     *
     * @var string
     * @ViewElenco( ordine = 1, titolo="Codice locale progetto" )
     * @RicercaFormType( ordine = 1, type = "text", label = "Codice locale progetto")
     */
	protected $cod_locale_progetto;
	
    /**
     *
     * @var string
     * @ViewElenco( ordine = 2, titolo="Stato progetto" )
     * @RicercaFormType( ordine = 2, type = "entity", label = "Stato progetto", options={"class": "MonitoraggioBundle\Entity\TC47StatoProgetto"})
     */
	protected $tc47_stato_progetto;

    
    /**
     *
     * @var string
     * @ViewElenco( ordine = 7, titolo="Data di riferimento", show = false  )
     * @RicercaFormType( ordine = 7, type = "birthday", label = "Data di riferimento", options= {"widget": "single_text", "input": "datetime", "format": "dd/MM/yyyy"})
     */
    protected $data_riferimento;

    
    /**
     * @return mixed
     */
    public function getTc47StatoProgetto()
    {
        return $this->tc47_stato_progetto;
    }

    /**
     * @param mixed $tc47_stato_progetto
     */
    public function setTc47StatoProgetto($tc47_stato_progetto)
    {
        $this->tc47_stato_progetto = $tc47_stato_progetto;
    }

    /**
     * @return mixed
     */
    public function getCodLocaleProgetto()
    {
        return $this->cod_locale_progetto;
    }

    /**
     * @param mixed $cod_locale_progetto
     */
    public function setCodLocaleProgetto($cod_locale_progetto)
    {
        $this->cod_locale_progetto = $cod_locale_progetto;
    }

    /**
     * @return mixed
     */
    public function getDataRiferimento()
    {
        return $this->data_riferimento;
    }

    /**
     * @param mixed $data_riferimento
     */
    public function setDataRiferimento($data_riferimento)
    {
        $this->data_riferimento = $data_riferimento;
    }

}
