<?php

namespace MonitoraggioBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\Collection;
use RichiesteBundle\Entity\IndicatoreOutput;

interface IGestoreIndicatoreOutput {
    public function __construct(ContainerInterface $container, Richiesta $richiesta);

    public function popolaIndicatoriOutput(): void;

    public function isRichiestaValida(): bool;

    public function isRendicontazioneBeneficiarioValida(): bool;

    public function isRendicontazioneIstruttoriaValida(): bool;

    public function hasIndicatoriManuali(): bool;

    public function getFormRichiestaValoriProgrammati(array $options = []): Response;

    /**
     * @return Collection|IndicatoreOutput[]
     */
    public function getIndicatoriManuali(): Collection;

    public function valorizzaIndicatoriAutomatici(): void;

    /**
     * @return Collection|IndicatoreOutput[]
     * @throws \LogicException
     */
    public function getIndicatoriAutomatici(): Collection;

    public function getMetodiCalcoloCustom(): array;

    public function valorizzaValoriProgrammatiIndicatoriAutomatici(): void;
}
