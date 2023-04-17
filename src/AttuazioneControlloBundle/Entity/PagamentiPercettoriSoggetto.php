<?php

namespace AttuazioneControlloBundle\Entity;

use AttuazioneControlloBundle\Entity\PagamentiPercettori;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity()
 */
class PagamentiPercettoriSoggetto extends PagamentiPercettori
{
    /**
     * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\Soggetto")
     * @ORM\JoinColumn(name="soggetto_id", referencedColumnName="id", nullable=true)
     */
    protected $soggetto;
    
    

    /**
     * Set soggetto
     *
     * @param \SoggettoBundle\Entity\Soggetto $soggetto
     * @return PagamentiPercettoriSoggetto
     */
    public function setSoggetto(\SoggettoBundle\Entity\Soggetto $soggetto = null)
    {
        $this->soggetto = $soggetto;

        return $this;
    }

    /**
     * Get soggetto
     *
     * @return \SoggettoBundle\Entity\Soggetto 
     */
    public function getSoggetto()
    {
        return $this->soggetto;
    }
}
