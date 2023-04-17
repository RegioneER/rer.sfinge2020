<?php

namespace AttuazioneControlloBundle\Service;

use SfingeBundle\Entity\Bando;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\Service\IGestoreRichiesta;

class GestoreRichiesteATCService
{
    protected $container;
	
    /**
     * GestoreRichiestaATCService constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    /**
     * @param Procedura $procedura
     * @return IGestoreRichiesta
     * @throws \Exception
     */
    public function getGestore(Procedura $procedura = null){
        //cerco un gestore per quel bando
        if(is_null($procedura)) {
            return new GestoreRichiesteATCBase($this->container);
        }
        $nomeClasse = "AttuazioneControlloBundle\GestoriRichiesteATC\GestoreRichiesteATCBando_".$procedura->getId();
        try{
            $gestoreBandoReflection = new \ReflectionClass($nomeClasse);
            return $gestoreBandoReflection->newInstance($this->container);
        } catch (\ReflectionException $ex){

        }

        return new GestoreRichiesteATCBase($this->container);
    }
}