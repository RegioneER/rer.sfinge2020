<?php

namespace IstruttorieBundle\Service;

use SfingeBundle\Entity\Bando;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GestoreComunicazioneProgettoService
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

		$nomeClasse = is_null($bundle) ? "IstruttorieBundle" : $bundle;
		
        $nomeClasse .= "\GestoriComunicazione\GestoreComunicazioneProgetto";
		
        try
		{
            $gestoreBandoReflection = new \ReflectionClass($nomeClasse);
            return $gestoreBandoReflection->newInstance($this->container);
        } catch (\ReflectionException $ex){
			
        }

        return new GestoreComunicazioneProgettoBase($this->container);
    }
}