<?php

namespace AttuazioneControlloBundle\Service;

use Exception;
use ReflectionClass;
use ReflectionException;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GestoreIncrementoOccupazionaleService
{
    protected $container;

    /**
     * GestoreIncrementoOccupazionaleService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Procedura|null $procedura
     * @return object|IGestoreIncrementoOccupazionale
     * @throws Exception
     */
    public function getGestore(Procedura $procedura = null)
    {
        if (!is_null($procedura)) {
            $id_procedura = $procedura->getId();
        } else {
            $id_richiesta = $this->container->get('request_stack')->getCurrentRequest()->getSession()->get('id_richiesta');
            if (is_null($id_richiesta)) {
                throw new Exception('Nessun id_richiesta indicato');
            }
            
            $richiesta = $this->container->get('doctrine')->getRepository('RichiesteBundle:Richiesta')->find($id_richiesta);
            if (is_null($richiesta)) {
                throw new Exception('Nessuna richiesta trovata');
            }
            
            $id_procedura = $richiesta->getProcedura()->getId();
        }

        // Cerco un gestore per quel bando
        $nomeClasse = 'AttuazioneControlloBundle\IncrementoOccupazionale\GestoreIncrementoOccupazionaleBando_' . $id_procedura;
        try {
            $gestoreBandoReflection = new ReflectionClass($nomeClasse);
            return $gestoreBandoReflection->newInstance($this->container);
        } catch (ReflectionException $ex) {

        }

        return new GestoreIncrementoOccupazionaleBase($this->container);
    }
}
