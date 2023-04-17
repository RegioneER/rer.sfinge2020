<?php

namespace RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo;

use RichiesteBundle\GestoriRichiestePA\ASezioneRichiesta;
use RichiesteBundle\GestoriRichiestePA\IRiepilogoRichiesta;
use PaginaBundle\Services\Pagina;
use RichiesteBundle\Utility\EsitoValidazione;
use RichiesteBundle\Form\DatiGeneraliType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraints\Valid;

class DatiGenerali extends ASezioneRichiesta {
    const TITOLO = 'Dati generali progetto';
    const SOTTOTITOLO = 'Pagina per i dati generali della richiesta';
    const VALIDATION_GROUP = 'dati_generali';

    const NOME_SEZIONE = 'dati_generali';

    protected $validations_groups = [];

    protected $formType = DatiGeneraliType::class;

    /**
     * @var array
     */
    protected $formOptions = [];

    public function __construct(ContainerInterface $container, IRiepilogoRichiesta $riepilogo) {
        parent::__construct($container, $riepilogo);
        $this->formOptions = $this->getDefaultFormOptions();
    }

    public function getTitolo() {
        return self::TITOLO;
    }

    public function valida() {
		$esito = new EsitoValidazione(true);

		$richiesta = $this->richiesta;
		$procedura = $richiesta->getProcedura();
        $validationList = $this->validator->validate($this->richiesta,  new Valid(), $this->getValidationsGroups()); 

		foreach($validationList as $validationElement ){    /** @var \Symfony\Component\Validator\ConstraintViolationInterface $validationElement */
            $this->listaMessaggi[] = $validationElement->getMessage();
        }
	}

    /**
     * @return string[]
     */
    public function getValidationsGroups() {
        return \count($this->validations_groups) ? $this->validations_groups : [self::VALIDATION_GROUP];
    }

    /**
     * @param string[] $validazioni
     */
    public function setValidationsGroups(array $validazioni) {
        $this->validations_groups = $validazioni;
    }

    public function getUrl() {
        return $this->generateUrl(self::ROTTA, [
            'id_richiesta' => $this->richiesta->getId(),
            'nome_sezione' => self::NOME_SEZIONE,
        ]);
    }

    public function setFormType($formType): void {
        $this->formType = $formType;
    }

    public function setFormOptions(array $options): void {
        $this->formOptions = $options;
    }

    public function visualizzaSezione(array $parametri) {
        $procedura = $this->richiesta->getProcedura();
        $this->setupPagina(self::TITOLO, self::SOTTOTITOLO);
        
        $form = $this->createForm(DatiGeneraliType::class, $this->richiesta, $this->getDefaultFormOptions());
        $form->handleRequest( $this->getCurrentRequest());
        if($form->isSubmitted() && $form->isValid()){
            try{
                $em = $this->getEm();
                $em->persist($this->richiesta);
                $em->flush();
                $this->addFlash('success', 'Informazioni salvate correttamente');
            } catch (\Exception $e) {
                $this->container->get('logger')->error($e->getMessage());
                $this->addError('Errore durante il salvataggio delle informazioni');
            }
        }
        
        $procedura = $this->richiesta->getProcedura();
		$dati = [
		    'form' => $form->createView(),
            'esenzione_marca_bollo' => $procedura->getEsenzioneMarcaBollo(),
        ];
		
        return $this->render('RichiesteBundle:Richieste:datiGenerali.html.twig', $dati);
    }

    protected function getDefaultFormOptions(): array {
        $procedura = $this->richiesta->getProcedura();
        return array(
            'url_indietro' => $this->riepilogo->getUrl(),
            'disabled' => $this->riepilogo->isRichiestaDisabilitata(),

            'esenzione_marca_bollo' => $procedura->getEsenzioneMarcaBollo(),
            'marca_da_bollo' => $procedura->getMarcaDaBollo(),
			'rating' => $procedura->getRating(),
			'requisiti_rating' => $procedura->getRequisitiRating(),
			'femminile' => $procedura->getFemminile(),
			'giovanile' => $procedura->getGiovanile(),
			'stelle' => $procedura->getStelle(),
        );
    }
}
