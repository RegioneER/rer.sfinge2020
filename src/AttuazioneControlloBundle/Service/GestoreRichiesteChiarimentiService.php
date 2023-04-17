<?php

namespace AttuazioneControlloBundle\Service;

use SfingeBundle\Entity\Bando;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GestoreRichiesteChiarimentiService
{
    protected $container;
	
    /**
     * GestoreRichiestaService constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    /**
	 * @param string $bundle
     * @param Procedura $procedura
	 * 
     * return IGestoreRichiesta
     * @throws \Exception
     */
    public function getGestore($bundle = null){

		$nomeClasse = is_null($bundle) ? "AttuazioneControlloBundle" : $bundle;
		
        $nomeClasse .= "\GestoriRichiesteChiarimenti\GestoreRichiesteChiarimenti";
		
        try
		{
            $gestoreBandoReflection = new \ReflectionClass($nomeClasse);
            return $gestoreBandoReflection->newInstance($this->container);
        } catch (\ReflectionException $ex){
			
        }

        return new GestoreRichiesteChiarimentiBase($this->container);
    }
}