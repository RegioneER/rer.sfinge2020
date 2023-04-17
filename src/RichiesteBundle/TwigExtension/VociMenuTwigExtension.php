<?php

namespace RichiesteBundle\TwigExtension;

use MonitoraggioBundle\Service\IGestoreIndicatoreOutput;
use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigTest;

class VociMenuTwigExtension extends AbstractExtension {
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function getName() {
        return 'base_dati_menu';
    }

    public function vociMenuPresentazione($id_richiesta) {
        /** @var Richiesta $richiesta */
        $richiesta = $this->container->get("doctrine")->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);

        if (true == $richiesta->isProceduraParticolare()) {
            $vociMenu = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->dammiVociMenuElencoRichiesteProcedureParticolari($id_richiesta);
        } else {
            if ('PROCEDURA_PA' == $richiesta->getProcedura()->getTipo()) {
                $vociMenu = $this->container->get('gestore_richiesta_pa')->getGestore($richiesta)->getVociMenu();
            } else {
                $vociMenu = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->dammiVociMenuElencoRichieste($id_richiesta);
            }
            if (!\is_null($richiesta->getAttuazioneControllo())) {
                $vocIGestoreStatoRichiesta = $this->container->get("stato_richiesta")->getGestore($richiesta)->getVociMenu();
                $vociMenu = \array_merge($vociMenu, $vocIGestoreStatoRichiesta);
            }
        }

        return $vociMenu;
    }

    public function vociMenuProroga($id_proroga) {
        $proroga = $this->container->get("doctrine")->getRepository("AttuazioneControlloBundle:Proroga")->find($id_proroga);
        $richiesta = $this->container->get("doctrine")->getRepository("RichiesteBundle:Richiesta")->find($proroga->getRichiesta()->getId());
        $vociMenu = $this->container->get("gestore_proroghe")->getGestore($richiesta->getProcedura())->dammiVociMenuElencoRichieste($id_proroga);

        return $vociMenu;
    }

    public function getFunctions() {
        return [
            new TwigFunction('voci_menu_presenzazione', [$this, 'vociMenuPresentazione'], ['is_safe' => ['html']]),
            new TwigFunction('voci_menu_proroga', [$this, 'vociMenuProroga'], ['is_safe' => ['html']]),
        ];
    }

    public function getTests() {
        return [
            new TwigTest('indicatoriManuali', [$this, 'hasIndicatoriManuali']),
        ];
    }

    public function hasIndicatoriManuali(Richiesta $richiesta): bool {
        /** @var IGestoreIndicatoreOutput $service */
        $service = $this->container->get('monitoraggio.indicatori_output')->getGestore($richiesta);
        return $service->hasIndicatoriManuali();
    }
}
