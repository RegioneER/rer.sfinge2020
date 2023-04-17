<?php

namespace AttuazioneControlloBundle\Service\Istruttoria;

use SfingeBundle\Entity\Bando;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AttuazioneControlloBundle\Service\GestoreRelazioneTecnicaBase;
use RichiesteBundle\Service\IGestoreRichiesta;

class GestoreRelazioneTecnicaService
{
    protected $container;
	
   
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
            $id_richiesta = $this->container->get("request_stack")->getCurrentRequest()->getSession()->get("id_richiesta");
            if (is_null($id_richiesta)) {
                throw new \Exception("Nessun id_richiesta indicato");
            }
            $richiesta = $this->container->get("doctrine")->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
            if (is_null($richiesta)) {
                throw new \Exception("Nessuna richiesta trovata");
            }
            $id_procedura = $richiesta->getProcedura()->getId();
        }
        
        //cerco un gestore per quel bando
        $nomeClasse = "AttuazioneControlloBundle\Istruttoria\GestoriRelazioniTecniche\GestoreRelazioneTecnicaBando_".$id_procedura;
        try{
            $gestoreBandoReflection = new \ReflectionClass($nomeClasse);
            return $gestoreBandoReflection->newInstance($this->container);
        } catch (\ReflectionException $ex){

        }

        return new GestoreRelazioneTecnicaBase($this->container);
    }
}