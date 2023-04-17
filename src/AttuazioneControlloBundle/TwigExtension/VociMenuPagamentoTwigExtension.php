<?php

namespace AttuazioneControlloBundle\TwigExtension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Service\GestoreImpegniService;

class VociMenuPagamentoTwigExtension extends \Twig_Extension {
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function getName() {
        return 'base_dati_menu_pag';
    }

    public function vociMenuPagamento($id_pagamento) {
        $pagamento = $this->container->get("doctrine")->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);

        $vociMenu = $this->container->get("gestore_pagamenti")->getGestore($pagamento->getAttuazioneControlloRichiesta()->getRichiesta()->getProcedura())->dammiVociMenuElencoPagamenti($id_pagamento);

        return $vociMenu;
    }

    public function getFunctions() {
        return [
            new \Twig_SimpleFunction('voci_menu_pagamento', [$this, 'vociMenuPagamento'], ['is_safe' => ['html']]),
        ];
    }

    public function getTests() {
        return [
            new \Twig\TwigTest('impegniVisibili', [$this, 'isSezioneImpegniVisibile']),
        ];
    }

    public function isSezioneImpegniVisibile(Richiesta $richiesta): bool {
        /** @var GestoreImpegniService $factory */
        $factory = $this->container->get('monitoraggio.impegni');
        $service = $factory->getGestore($richiesta);

        return $service->mostraSezionePagamento();
    }
}
