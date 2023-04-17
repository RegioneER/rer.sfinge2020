<?php

namespace MonitoraggioBundle\Service;

use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\DependencyInjection\ContainerInterface;
use SfingeBundle\Entity\Procedura;

abstract class AGestorePianoCosto implements IGestorePianoCosto {
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Richiesta
     */
    protected $richiesta;

    protected function getAnnualita(?Procedura $procedura = null) {
        $procedura = is_null($procedura) ? $this->richiesta->getProcedura() : $procedura;

        return $this->container->get('gestore_piano_costo')
            ->getGestore($procedura)
            ->getAnnualita($this->richiesta->getMandatario());
    }

    public function __construct(ContainerInterface $container, Richiesta $richiesta) {
        $this->container = $container;
        $this->richiesta = $richiesta;
    }

    abstract public function generaArrayPianoCostoTotaleRealizzato(): iterable;
}
