<?php

namespace AttuazioneControlloBundle\Service\Istruttoria\Variazioni;

use AttuazioneControlloBundle\Entity\VariazioneRichiesta;
use AttuazioneControlloBundle\Service\Istruttoria\AGestoreVariazioni;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GestoreVariazioniGenericheBase extends AGestoreVariazioni {
    /**
     * @var VariazioneRichiesta
     */
    protected $variazione;

    public function __construct(VariazioneRichiesta $variazione, ContainerInterface $container) {
        $this->variazione = $variazione;
        $this->container = $container;
    }

    protected function applicaVariazione(): void {
    }
}
