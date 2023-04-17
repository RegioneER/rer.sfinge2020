<?php

namespace RichiesteBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\Entity\Richiesta;

class GestoreObiettiviRealizzativiService
{
    const NAMESPACE = "RichiesteBundle\GestoriObiettiviRealizzativi";
    const BASE_CLASSNAME = 'GestoreObiettiviRealizzativi_';
    const SERVICE_NAME = 'gestore_obiettivi_realizzativi';

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * @throws \Exception
     */
    public function getGestore(Richiesta $richiesta): IGestoreObiettiviRealizzativi
    {
        $className = $this->resolveClassName($richiesta);
        if(\class_exists($className)){
            return new $className($this->container, $richiesta);
        }
        return new GestoreObiettiviRealizzativiBase($this->container, $richiesta);
    }

    private function resolveClassName(Richiesta $richiesta):string {
        $class = self::NAMESPACE . '\\' . self::BASE_CLASSNAME . $richiesta->getId();

        return $class;
    }
}