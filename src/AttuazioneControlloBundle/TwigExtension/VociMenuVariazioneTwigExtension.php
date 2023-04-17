<?php

namespace AttuazioneControlloBundle\TwigExtension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use AttuazioneControlloBundle\Service\GestoreVariazioniService;
use AttuazioneControlloBundle\Entity\VariazioneRichiesta;

class VociMenuVariazioneTwigExtension extends AbstractExtension {
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function vociMenuVariazione(VariazioneRichiesta $variazione): array {
        /** @var GestoreVariazioniService $factoryVariazione */
        $factoryVariazione = $this->container->get("gestore_variazioni");
        $vociMenu = $factoryVariazione->getGestoreVariazione($variazione)->dammiVociMenuElencoVariazioni();

        return $vociMenu;
    }

    public function getFunctions() {
        return [
            new TwigFunction('voci_menu_variazione', [$this, 'vociMenuVariazione'], ['is_safe' => ['html']]),
        ];
    }
}
