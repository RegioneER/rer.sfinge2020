<?php

namespace MonitoraggioBundle\Service;

use BaseBundle\Service\BaseServiceTrait;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Entity\IndicatoreOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use RichiesteBundle\Form\IndicatoriOutputBeneficiarioType;
use Doctrine\Common\Collections\Collection;
use MonitoraggioBundle\Entity\TC44_45IndicatoriOutput;
use AttuazioneControlloBundle\Service\ICalcolaValoreRealizzatoIndicatoreOutput;

class GestoreIndicatoreOutputBase implements IGestoreIndicatoreOutput {
    use BaseServiceTrait;

    const NAMESPACE_CALCOLO_INDICATORI = "AttuazioneControlloBundle\CalcoloIndicatori";
    const PREFIX_CLASSE_CALCOLO_INDICATORI = 'Indicatore_';
    /**
     * @var Richiesta
     */
    protected $richiesta;

   

    public function __construct(ContainerInterface $container, Richiesta $richiesta) {
        $this->richiesta = $richiesta;
        $this->container = $container;
    }

    public function popolaIndicatoriOutput(): void {
        if ($this->isNotPorFESR()) {
            return;
        }
        $procedura = $this->richiesta->getProcedura();
        $dataRef = $this->richiesta->getDataCreazione();
        foreach ($procedura->getIndicatoriAssociati($dataRef) as $defIndicatore) {
            if ($this->richiesta->isIndicatorePresente($defIndicatore)) {
                continue;
            }
            $indicatore = new IndicatoreOutput($this->richiesta);
            $indicatore->setIndicatore($defIndicatore);
            $this->richiesta->addMonIndicatoreOutput($indicatore);
        }
    }

    protected function isNotPorFESR(): bool {
        return $this->richiesta->getFlagPor() == false;
    }

    public function isRichiestaValida(): bool {
        return $this->validaIndicatori([ 
            'Default',
            'presentazione_beneficiario',
        ]);
    }

    protected function validaIndicatori(array $validationGroups =['Default']){
        if ($this->isNotPorFESR()) {
            return true;
        }

        $indicatori = $this->getIndicatoriManuali();
        /** @var ValidatorInterface $validator */
        $validator = $this->container->get('validator');
        $valido = \array_reduce($indicatori->toArray(), function (bool $carry, IndicatoreOutput $indicatore) use ($validator, $validationGroups) {
            $esito = 0 == $validator->validate($indicatore, null, $validationGroups)->count();
            return $carry && $esito;
        }, true);

        return $valido;
    }

    

    /**
     * @return Collection|IndicatoreOutput[]
     */
    public function getIndicatoriManuali(): Collection {
        $closure = \Closure::fromCallable([$this, 'isIndicatoreManuale']);
        return $this->richiesta->getMonIndicatoreOutput()->filter($closure);
    }

    protected function isIndicatoreManuale(IndicatoreOutput $indicatore): bool {
        return true == $indicatore->getIndicatore()->getResponsabilitaUtente();
    }

    public function hasIndicatoriManuali(): bool {
        if ($this->isNotPorFESR()) {
            return false;
        }
        return $this->getIndicatoriManuali()->count() > 0;
    }

    public function getFormRichiestaValoriProgrammati(array $options = []): Response {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefaults([
            'twig' => 'RichiesteBundle:Richieste:dettaglioIndicatoreOutput.html.twig',
            'url_indietro' => $this->generateUrl("dettaglio_richiesta", ["id_richiesta" => $this->richiesta->getId()]),
            'return_url' => $this->generateUrl('elenco_indicatori_richiesta', ['id_richiesta' => $this->richiesta->getId(), ]),
            'twig_data' => ['richiesta' =>  $this->richiesta],
            'disabled' => false,
        ]);

        $opzioni = $optionsResolver->resolve($options);

        $indicatoriRichiesta = $this->richiesta->getMonIndicatoreOutput();
        $indicatoriForm = $this->getIndicatoriManuali();

        $this->richiesta->setMonIndicatoreOutput($indicatoriForm);

        $formOptions = [
            'url_indietro' => $opzioni['url_indietro'],
            'disabled' => $opzioni['disabled'],
        ];

        $form = $this->createForm(IndicatoriOutputBeneficiarioType::class, $this->richiesta, $formOptions);
        $form->handleRequest($this->getCurrentRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->getEm()->flush();
                $this->addSuccess('Dati salvati correttamente');
                return $this->redirect($opzioni['return_url']);
            } catch (\Exception $e) {
                $this->container->get('logger')->error($e->getMessage(), ['id_richiesta' => $this->richiesta->getId()]);
                $this->addError('Errore durante il salvataggio dei dati');
            }
        }
        $dati = \array_merge($opzioni['twig_data'], [
            'form' => $form->createView(),
            'is_nuova_programmazione' => ($this->richiesta->getProcedura()->getAnnoProgrammazione() >= 2023 ? true : false),
        ]);
        $this->richiesta->setMonIndicatoreOutput($indicatoriRichiesta);
        $response = $this->render($opzioni['twig'], $dati);

        return $response;
    }

    public function isRendicontazioneBeneficiarioValida(): bool{
        return $this->validaIndicatori(['Default', 'rendicontazione_beneficiario']);
    }

    public function isRendicontazioneIstruttoriaValida(): bool{
        return $this->validaIndicatori(['Default', 'rendicontazione_istruttoria']);
    }

    public function valorizzaIndicatoriAutomatici(): void  {
        foreach($this->getIndicatoriAutomatici() as $indicatore){
            $this->valorizzaInicatoreOutputRealizzato($indicatore);
        }
    }

    public function valorizzaValoriProgrammatiIndicatoriAutomatici(): void  {
        foreach($this->getIndicatoriAutomatici() as $indicatore){
            $this->valorizzaIndicatoreOutputProgrammato($indicatore);
        }
    }

    /**
     * @return Collection|IndicatoreOutput[]
     */
    public function getIndicatoriAutomatici(): Collection {
        $closure = \Closure::fromCallable( function(IndicatoreOutput $indicatore){
            return ! $this->isIndicatoreManuale($indicatore);
        });

        return $this->richiesta->getMonIndicatoreOutput()->filter($closure);
    }

    protected function valorizzaInicatoreOutputRealizzato(IndicatoreOutput $indicatore) : void {
		$valoreRealizzato = $this->getValoreIndicatore($indicatore);

		$indicatore->setValoreRealizzato((string)$valoreRealizzato);
    }

    protected function valorizzaIndicatoreOutputProgrammato(IndicatoreOutput $indicatore) : void {
		$valoreRealizzato = $this->getValoreIndicatore($indicatore);

		$indicatore->setValProgrammato((string)$valoreRealizzato);
    }

    protected function getValoreIndicatore(IndicatoreOutput $indicatore): string {
        $codice = $indicatore->getIndicatore()->getCodIndicatore();
        $command = \array_key_exists($codice, $this->getMetodiCalcoloCustom()) ?
                                    $this->getCalcoloIndicatoreSpecifico($indicatore):
                                    $this->getCalcoloIndicatoreStandard($indicatore);

		$valore = $command->getValore();

        return $valore;
    }

    protected function getCalcoloIndicatoreSpecifico(IndicatoreOutput $indicatore): ICalcolaValoreRealizzatoIndicatoreOutput{
        $codice = $indicatore->getIndicatore()->getCodIndicatore();
        $closure = $this->getClosure($codice);
        $command = new CalcoloIndicatoreDaClosure($this->container, $this->richiesta);
        $command->setClosure($closure);

        return $command;
    }

    private function getClosure(string $codice): \Closure {
        $metodi = $this->getMetodiCalcoloCustom();
        $elemento = $metodi[$codice];
        if(\is_numeric($elemento)){
            return function()use($elemento){return $elemento;};
        }
        return $elemento;
    }

    protected function getCalcoloIndicatoreStandard(IndicatoreOutput $indicatore): ICalcolaValoreRealizzatoIndicatoreOutput {        
        $classe = $this->inferisciNomeClasse($indicatore->getIndicatore());
        /** @var \AttuazioneControlloBundle\Service\ICalcolaValoreRealizzatoIndicatoreOutput $command */
        $object = new $classe($this->container, $this->richiesta);

        return $object;
    }

    public function getMetodiCalcoloCustom() : array
    {
        return [];
    }
    
    /**
     * @throws \LogicException
     */
    protected function inferisciNomeClasse(TC44_45IndicatoriOutput $indicatore): string {
        $codiceIndicatore = \str_replace('.','_',$indicatore->getCodIndicatore());
        $classe = self::NAMESPACE_CALCOLO_INDICATORI . 
                  '\\' .
                  self::PREFIX_CLASSE_CALCOLO_INDICATORI . 
                  $codiceIndicatore;
        if(!\class_exists($classe)){
			throw new \LogicException("Classe $classe per popolamento automatico non esistente.");
        }
        
        return $classe;
    }
}

