<?php

namespace MonitoraggioBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\Entity\Richiesta;
use SoggettoBundle\Entity\Soggetto;
use SoggettoBundle\Entity\ComuneUnione;

class GestoreFinanziamentoService {
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function getGestore(Richiesta $richiesta): IGestoreFinanziamento {
        if(! $richiesta->getFlagPor()){
            return new GestoriFinanziamento\Dummy($this->container, $richiesta);
        }
        if ($this->isSoggettoPrivato($richiesta)) {
            return new GestoriFinanziamento\Privato($this->container, $richiesta);
        }
        $mandatario = $richiesta->getSoggetto();
        if (\in_array($mandatario->getId(), [Soggetto::ID_REGIONE, 1026])) {
            return new GestoriFinanziamento\Regione($this->container, $richiesta);
        }
        if ($mandatario instanceof ComuneUnione) {
            return new GestoriFinanziamento\Comune($this->container, $richiesta);
        }

        return new GestoriFinanziamento\Pubblico($this->container, $richiesta);
    }

    protected function isSoggettoPrivato(Richiesta $richiesta): bool {
        $isPrivato = !$richiesta->getMonPrgPubblico();

        return $isPrivato;
    }
}
