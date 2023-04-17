<?php

namespace AttuazioneControlloBundle\Tests\Service\Istruttoria;

use BaseBundle\Tests\Service\TestBaseService;
use RichiesteBundle\Entity\Richiesta;
use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use MonitoraggioBundle\Entity\TC33FonteFinanziaria;
use AttuazioneControlloBundle\Entity\Finanziamento;
use AttuazioneControlloBundle\Service\Istruttoria\GestoreVariazioniBase;
use AttuazioneControlloBundle\Entity\VariazioneVocePianoCosto;
use RichiesteBundle\Entity\PianoCosto;
use RichiesteBundle\Entity\VocePianoCosto;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use MonitoraggioBundle\Service\GestoreFinanziamentoService;
use MonitoraggioBundle\Service\IGestoreFinanziamento;
use AttuazioneControlloBundle\Entity\VariazionePianoCosti;
use AttuazioneControlloBundle\Service\Istruttoria\Variazioni\GestoreVariazioniPianoCostiBase;

class GestoreVariazioniPianoCostiBaseTest extends TestBaseService
{
	/**
	 * @var Richiesta
	 */
	protected $richiesta;
	
	/**
	 * @var VariazionePianoCosti
	 */
	protected $variazione;

	/**
	 * @var GestoreVariazioniPianoCostiBase
	 */
	protected $gestore;
	
	public function setUp() {
		parent::setUp();

		$this->richiesta = new Richiesta();
		$atc = new AttuazioneControlloRichiesta();
		$atc->setRichiesta($this->richiesta);

		$this->variazione = new VariazionePianoCosti();
		$this->variazione->setAttuazioneControlloRichiesta($atc);

		$this->gestore = new GestoreVariazioniPianoCostiBase($this->variazione, $this->container);
	}

	public function testValidazioneRichiestaFinanziamento(): void {
		$fesr = $this->inserisciFinanziamento(TC33FonteFinanziaria::FESR, 0);
		$stato = $this->inserisciFinanziamento(TC33FonteFinanziaria::STATO, 0);
		$regione = $this->inserisciFinanziamento(TC33FonteFinanziaria::REGIONE, 0);
		$beneficiario = $this->inserisciFinanziamento(TC33FonteFinanziaria::PRIVATO, 0);

		$this->variazione->setContributoAmmesso(100);
		$this->variazione->setEsitoIstruttoria(true);
		$variazioneVocePiano = new VariazioneVocePianoCosto();
		$variazioneVocePiano->setImportoVariazioneAnno1(150);
		$variazioneVocePiano->setImportoApprovatoAnno1(150);
		$variazioneVocePiano->setVariazione($this->variazione);
		$this->variazione->addVocePianoCosto($variazioneVocePiano);
		$pianoCosto = new PianoCosto();
		$vocePianoCosti = new VocePianoCosto();
		$vocePianoCosti->setPianoCosto($pianoCosto);

		$variazioneVocePiano->setVocePianoCosto($vocePianoCosti);

		$form = $this->createMock(Form::class);
		$innerForm = $this->createMock(Form::class);
		$form->method('get')->willReturn($innerForm);
		$form->expects($this->atLeastOnce())->method('isValid')->willReturn(true);
		$form->expects($this->atLeastOnce())->method('isSubmitted')->willReturn(true);
		$this->formFactory->method('create')->willReturn($form);
		$request = new Request();
		$request->setMethod(Request::METHOD_POST);
		$this->requestStack->push($request);

		$serviceFinanziamento = $this->createMock(GestoreFinanziamentoService::class);
		$this->container->set('monitoraggio.gestore_finanziamento', $serviceFinanziamento);
		
		$gestoreFinanziamento = $this->createMock(IGestoreFinanziamento::class);
		$serviceFinanziamento->method('getGestore')->with($this->richiesta)->willReturn($gestoreFinanziamento);
		$gestoreFinanziamento->expects($this->once())->method('aggiornaFinanziamento');
		$gestoreFinanziamento->expects($this->once())->method('persistFinanziamenti');

		$this->gestore->esitoFinale($this->variazione);
	}

	protected function inserisciFinanziamento(string $fondo, float $importo): Finanziamento {
		$voce = new VariazioneVocePianoCosto();
		$fin = new Finanziamento($this->richiesta);
		$fin->setTc33FonteFinanziaria(new TC33FonteFinanziaria($fondo))
		->setImporto($importo);

		$this->richiesta->addMonFinanziamenti($fin);

		return $fin;
	}
	

	protected function assertImportoFinanziamento(string $fondo, float $expected){
		/** @var Finanziamento $finanziamento */
		$finanziamento = $this->richiesta->getMonFinanziamenti($fondo)->first();

		$this->assertEquals($expected, $finanziamento->getImporto());
	}

}