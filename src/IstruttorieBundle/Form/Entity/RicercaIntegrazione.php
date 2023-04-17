<?php

namespace IstruttorieBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;
use SfingeBundle\Entity\Procedura;
use SoggettoBundle\Entity\Soggetto;

class RicercaIntegrazione extends AttributiRicerca
{
    /** @var Procedura */
    private $procedura;
    
    private $protocollo;
    
    /** @var Soggetto */
    private $soggetto;

    /** @var string */
    private $istruttore;
    
    /** @var string */
    private $tipo;
    

    function getProcedura()
    {
        return $this->procedura;
    }

    function getProtocollo()
    {
        return $this->protocollo;
    }

    function getSoggetto()
    {
        return $this->soggetto;
    }

    function setProcedura($procedura)
    {
        $this->procedura = $procedura;
    }

    function setProtocollo($protocollo)
    {
        $this->protocollo = $protocollo;
    }

    function setSoggetto($soggetto)
    {
        $this->soggetto = $soggetto;
    }

    /**
     * @return mixed
     */
    public function getIstruttore()
    {
        return $this->istruttore;
    }

    /**
     * @param mixed $istruttore
     */
    public function setIstruttore($istruttore): void
    {
        $this->istruttore = $istruttore;
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
     * @return string
     */
    public function getType()
    {
        return "IstruttorieBundle\Form\RicercaIntegrazioneType";
    }

    /**
     * @return string
     */
    public function getNomeRepository()
    {
        return "IstruttorieBundle:IntegrazioneIstruttoria";
    }

    /**
     * @return string
     */
    public function getNomeMetodoRepository()
    {
        switch ($this->tipo) {
            case 'RISPOSTE_INTEGRAZIONI':
                return "getElencoIntegrazioniConRispostaNonLetta";
            default:
                return "getElencoIntegrazioni";
        }
    }

    /**
     * @return int|null
     */
    public function getNumeroElementiPerPagina()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getNomeParametroPagina()
    {
        return "page";
    }

    /**
     * @param $freshData
     */
    function mergeFreshData($freshData) {
        $this->setSoggetto($freshData->getSoggetto());
    }
}
