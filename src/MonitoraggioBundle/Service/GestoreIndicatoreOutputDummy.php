<?php

namespace MonitoraggioBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class GestoreIndicatoreOutputDummy implements IGestoreIndicatoreOutput {
    public function __construct(ContainerInterface $container, Richiesta $richiesta) {
    }

    public function popolaIndicatoriOutput(): void {
    }

    public function isRichiestaValida(): bool {
        return true;
    }

    public function isRendicontazioneBeneficiarioValida(): bool {
        return true;
    }

    public function isRendicontazioneIstruttoriaValida(): bool {
        return true;
    }

    public function hasIndicatoriManuali(): bool {
        return false;
    }

    public function getFormRichiestaValoriProgrammati(array $options = []): Response {
        return new Response();
    }

    /**
     * @return Collection|IndicatoreOutput[]
     */
    public function getIndicatoriManuali(): Collection {
        return new ArrayCollection([]);
    }

    public function valorizzaIndicatoriAutomatici(): void {
    }

    /**
     * @return Collection|IndicatoreOutput[]
     * @throws \LogicException
     */
    public function getIndicatoriAutomatici(): Collection {
        return new ArrayCollection([]);
    }

    public function getMetodiCalcoloCustom(): array {
        return [];
    }

    public function valorizzaValoriProgrammatiIndicatoriAutomatici(): void {
    }
}
