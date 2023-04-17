<?php

namespace AttuazioneControlloBundle\Service\ControlliStabilita;

use SfingeBundle\Entity\Bando;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\Service\IGestoreRichiesta;

class GestoreControlliStabilitaService
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
        if(!is_null($procedura)){
            $id_procedura = $procedura->getId();
        }else {
           return new GestoreControlliStabilitaBase($this->container);
        }
        
        //cerco un gestore per quel bando
        $nomeClasse = "AttuazioneControlloBundle\GestoriControlli\GestoreControlliStabilitaBando_".$id_procedura;
        try{
            $gestoreBandoReflection = new \ReflectionClass($nomeClasse);
            return $gestoreBandoReflection->newInstance($this->container);
        } catch (\ReflectionException $ex){

        }

        return new GestoreControlliStabilitaBase($this->container);
    }
}