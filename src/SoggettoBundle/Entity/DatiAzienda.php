<?php

namespace SoggettoBundle\Entity;

class DatiAzienda {

    /**
     * @var \SoggettoBundle\Entity\Soggetto
     */
    protected $soggetto;

    /**
     * @var \SoggettoBundle\Entity\Sede
     */
    protected $sede;


    /**
     * @return \SoggettoBundle\Entity\Soggetto
     */
    
    public function getSoggetto() {
        return $this->soggetto;
    }

    /**
     * @param \SoggettoBundle\Entity\Soggetto
     */
    public function setSoggetto($soggetto) {
        $this->soggetto = $soggetto;
    }

	/**
     * @param \SoggettoBundle\Entity\Sede
     */
    public function setSede($sede) {
        $this->sede = $sede;
    }
	
	/**
     * @return \SoggettoBundle\Entity\Sede
     */
    public function getSede() {
        return $this->sede;
    }
}