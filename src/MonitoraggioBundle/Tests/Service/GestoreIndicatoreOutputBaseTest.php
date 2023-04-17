<?php

namespace MonitoraggioBundle\Tests\Service;

use BaseBundle\Tests\Service\TestBaseService;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Service\GestoreIndicatoreOutputBase;
use SfingeBundle\Entity\Azione;
use SfingeBundle\Entity\Bando;
use MonitoraggioBundle\Entity\IndicatoriOutputAzioni;
use SfingeBundle\Entity\Asse;
use MonitoraggioBundle\Entity\TC44_45IndicatoriOutput;
use MonitoraggioBundle\Service\IGestoreIndicatoreOutput;
use RichiesteBundle\Entity\IndicatoreOutput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use RichiesteBundle\Entity\Proponente;

class GestoreIndicatoreOutputBaseTest extends TestBaseService {
    /**
     * @var IGestoreIndicatoreOutput
     */
    protected $base;

    /**
     * @var Richiesta
     */
    protected $richiesta;

    public function setUp() {
        parent::setUp();
        $this->richiesta = new Richiesta();
        $this->base = new GestoreIndicatoreOutputBase($this->container, $this->richiesta);
    }

    public function testPopolaIndicatoriOutput(): void {
        $procedura = new Bando();
        $this->richiesta->setProcedura($procedura);
        $procedura->addRichieste($this->richiesta);

        $asse = new Asse();
        $procedura->setAsse($asse);

        $azione = new Azione();
        $procedura->addAzioni($azione);
        $azione->addProcedure($procedura);

        $indicatoreAzione = new IndicatoriOutputAzioni();
        $indicatoreAzione->setAsse($asse);
        $indicatoreAzione->setAzione($azione);
        $indicatoreDefinizione = new TC44_45IndicatoriOutput();
        $indicatoreAzione->setIndicatoreOutput($indicatoreDefinizione);

        $azione->addIndicatoriOutputAzioni($indicatoreAzione);

        $this->base->popolaIndicatoriOutput();

        $indicatori = $this->richiesta->getMonIndicatoreOutput();

        $this->assertCount(1, $indicatori);
        /** @var IndicatoreOutput $indicatore */
        $indicatore = $indicatori->first();
        $this->assertSame($this->richiesta, $indicatore->getRichiesta());
        $this->assertSame($indicatoreDefinizione, $indicatore->getIndicatore());
        $this->assertNull($indicatore->getValProgrammato());
        $this->assertNull($indicatore->getValoreRealizzato());
        $this->assertNull($indicatore->getValoreValidato());
    }

    public function testHasIndicatoriOutputIndicatoriAutomatici(): void {
        $defIndicatoreAutomatico = new TC44_45IndicatoriOutput();
        $defIndicatoreAutomatico->setResponsabilitaUtente(false);
        $this->addIndicatore($defIndicatoreAutomatico);

        $res = $this->base->hasIndicatoriManuali();

        $this->assertFalse($res, 'Richiesta ha solo indicatori automatici');
    }

    public function addIndicatore(TC44_45IndicatoriOutput $def, $valoreProgrammato = null, $valoreRealizzato = null): IndicatoreOutput {
        $indicatore = new IndicatoreOutput($this->richiesta, $def);
        $indicatore->setValProgrammato($valoreProgrammato);
        $indicatore->setValoreRealizzato($valoreRealizzato);
        $this->richiesta->addMonIndicatoreOutput($indicatore);

        return $indicatore;
    }

    public function testHasIndicatoriOutputIndicatoriManuali(): void {
        $defIndicatoreManuale = new TC44_45IndicatoriOutput();
        $defIndicatoreManuale->setResponsabilitaUtente(true);
        $this->addIndicatore($defIndicatoreManuale);

        $res = $this->base->hasIndicatoriManuali();

        $this->assertTrue($res, 'Richiesta ha indicatori manuali');
    }

    public function testHasIndicatoriOutputIndicatoriManualiNonPOR(): void {
        $defIndicatoreManuale = new TC44_45IndicatoriOutput();
        $defIndicatoreManuale->setResponsabilitaUtente(true);
        $this->addIndicatore($defIndicatoreManuale);
        $this->richiesta->setFlagPor(false);

        $res = $this->base->hasIndicatoriManuali();

        $this->assertFalse($res, 'Richiesta NON POR non deve avere indicatori');
    }

    /**
     * @dataProvider isRichiestaValidaValoriManualiDataProvider
     * @param mixed $programmato
     */
    public function testIsRichiestaValidaValoriManuali($programmato, bool $esito): void {
        $defIndicatoreManuale = new TC44_45IndicatoriOutput();
        $defIndicatoreManuale->setResponsabilitaUtente(true);
        $this->addIndicatore($defIndicatoreManuale, $programmato);

        $res = $this->base->isRichiestaValida();

        $this->assertEquals($esito, $res);
    }

    public function isRichiestaValidaValoriManualiDataProvider(): array {
        return [
            [null, false],
            [1.0, true],
            ['ciao', false],
            [-1.0, false],
        ];
    }

    public function testRichiestaValidaNonPOR(): void
    {
        $defIndicatoreManuale = new TC44_45IndicatoriOutput();
        $defIndicatoreManuale->setResponsabilitaUtente(true);
        $this->addIndicatore($defIndicatoreManuale, null);
        $this->richiesta->setFlagPor(false);

        $res = $this->base->isRichiestaValida();

        $this->assertTrue($res);
    }

    public function testIsRichiestaValidaValoriAutomatici(): void {
        $defIndicatoreAuto = new TC44_45IndicatoriOutput();
        $defIndicatoreAuto->setResponsabilitaUtente(false);
        $this->addIndicatore($defIndicatoreAuto);

        $res = $this->base->isRichiestaValida();

        $this->assertTrue($res);
    }

    /**
     * @dataProvider isRendicontazioneBeneficiarioValidaDataProvider
     * @param mixed $programmato
     * @param mixed $realizzato
     */
    public function testisRendicontazioneBeneficiarioValida($programmato, $realizzato, bool $esito): void {
        $defIndicatoreAuto = new TC44_45IndicatoriOutput();
        $defIndicatoreAuto->setResponsabilitaUtente(false);
        $this->addIndicatore($defIndicatoreAuto);

        $defIndicatoreManuale = new TC44_45IndicatoriOutput();
        $defIndicatoreManuale->setResponsabilitaUtente(true);
        $this->addIndicatore($defIndicatoreManuale, $programmato, $realizzato);

        $res = $this->base->isRendicontazioneBeneficiarioValida();

        $this->assertEquals($esito, $res);
    }

    public function isRendicontazioneBeneficiarioValidaDataProvider(): array {
        return [
            [null, null, false],
            [1.0, null, false],
            [null, 1.0, true],
            [1.0, 1.0, true],
            [1.0, -1.0, false],
        ];
    }

    public function testGetFormRichiestaValoriProgrammatiNonValido(): void {
        $form = $this->createMock(Form::class);
        $this->formFactory->method('create')->willReturn($form);

        $res = $this->base->getFormRichiestaValoriProgrammati();

        $this->assertInstanceOf(Response::class, $res);
    }

    public function testGetFormRichiestaValoriProgrammatiValido(): void {
        $this->router->method('generate')->willReturn('success_route');
        $form = $this->createMock(Form::class);
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isValid')->willReturn(true);
        $this->formFactory->method('create')->willReturn($form);

        $res = $this->base->getFormRichiestaValoriProgrammati();
        $success = $this->flashBag->peek('success');
        $error = $this->flashBag->peek('error');

        $this->assertInstanceOf(RedirectResponse::class, $res);
        $this->assertContains('Dati salvati correttamente', $success);
        $this->assertEmpty($error, 'Errori presenti nel flashBag');
    }

    public function testGetFormRichiestaValoriProgrammatiValidoEccezioneInSalvataggio(): void {
        $form = $this->createMock(Form::class);
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isValid')->willReturn(true);
        $this->formFactory->method('create')->willReturn($form);

        $this->em->method('flush')->will($this->throwException(new \Exception()));

        $res = $this->base->getFormRichiestaValoriProgrammati();
        $error = $this->flashBag->peek('error');
        $success = $this->flashBag->peek('success');

        $this->assertInstanceOf(Response::class, $res);
        $this->assertEmpty($success);
        $this->assertContains('Errore durante il salvataggio dei dati', $error, 'Errori presenti nel flashBag');
    }

    public function testGetIndicatoriAutomatici(): void {
        $defIndicatoreManuale = new TC44_45IndicatoriOutput();
        $defIndicatoreManuale->setResponsabilitaUtente(true);
        $manuale = $this->addIndicatore($defIndicatoreManuale);

        $defIndicatoreAutomatico = new TC44_45IndicatoriOutput();
        $defIndicatoreAutomatico->setResponsabilitaUtente(false);
        $automatico = $this->addIndicatore($defIndicatoreAutomatico);

        $res = $this->base->getIndicatoriAutomatici();

        $this->assertCount(1, $res, "L'elenco deve contenere solo 1 indicatore");
        $this->assertContains($automatico, $res);
        $this->assertNotContains($manuale, $res);
    }

    public function testValorizzazioneIndicatoriAutomaticiConClosure(): void {
        $obj = $this->getMockBuilder(GestoreIndicatoreOutputBase::class)
                ->setMethods(['getMetodiCalcoloCustom'])
                ->setConstructorArgs([$this->container, $this->richiesta])
                // ->disableOriginalConstructor()
                ->getMock();

        $obj->method('getMetodiCalcoloCustom')->willreturn([
            'indicatore' => function () {
                return 1.0;
            },
        ]);
        $def = new TC44_45IndicatoriOutput();
        $def->setCodIndicatore('indicatore')
            ->setResponsabilitaUtente(false);
        $indicatore = $this->addIndicatore($def);

        $obj->valorizzaIndicatoriAutomatici();

        $this->assertEquals(1.0, $indicatore->getValoreRealizzato());
    }

    public function testValorizzazioneIndicatoriAutomaticiConValore(): void {
        $obj = $this->getMockBuilder(GestoreIndicatoreOutputBase::class)
        ->setMethods(['getMetodiCalcoloCustom'])
        ->setConstructorArgs([$this->container, $this->richiesta])
        // ->disableOriginalConstructor()
        ->getMock();

        $obj->method('getMetodiCalcoloCustom')->willreturn([
            'indicatore' => 1,
        ]);
        $def = new TC44_45IndicatoriOutput();
        $def->setCodIndicatore('indicatore')
            ->setResponsabilitaUtente(false);
        $indicatore = $this->addIndicatore($def);

        $obj->valorizzaIndicatoriAutomatici();

        $this->assertEquals(1.0, $indicatore->getValoreRealizzato());
    }

    public function testValorizzazioneIndicatoreAutomaticoStandard(): void {
        $this->richiesta->addProponenti(new Proponente($this->richiesta));
        $def = new TC44_45IndicatoriOutput();
        $def->setCodIndicatore('101')
            ->setResponsabilitaUtente(false);
        $indicatore = $this->addIndicatore($def);

        $this->base->valorizzaIndicatoriAutomatici();

        $this->assertEquals(1.0, $indicatore->getValoreRealizzato());
    }

    public function testValorizzazioneIndicatoreAutomaticoClasseNonTrovata(): void {
        $def = new TC44_45IndicatoriOutput();
        $def->setCodIndicatore('indicatore')
            ->setResponsabilitaUtente(false);
        $indicatore = $this->addIndicatore($def);

        $this->expectException(\LogicException::class);

        $this->base->valorizzaIndicatoriAutomatici();
    }

    /**
     * @dataProvider isRendicontazioneIstruttoriaValidaDataProvider
     * @param mixed $programmato
     * @param mixed $realizzato
     */
    public function testIsRendicontazioneIstruttoriaValida($realizzato, $validato, bool $esito): void {
        $defIndicatoreAuto = new TC44_45IndicatoriOutput();
        $defIndicatoreAuto->setResponsabilitaUtente(false);
        $this->addIndicatore($defIndicatoreAuto);

        $defIndicatoreManuale = new TC44_45IndicatoriOutput();
        $defIndicatoreManuale->setResponsabilitaUtente(true);
        $indicatore = $this->addIndicatore($defIndicatoreManuale, 1.0, $realizzato);
        $indicatore->setValoreValidato($validato);

        $res = $this->base->isRendicontazioneIstruttoriaValida();

        $this->assertEquals($esito, $res);
    }

    public function isRendicontazioneIstruttoriaValidaDataProvider(): array {
        return [
            [null, null, false],
            [1.0, null, false],
            [null, 1.0, true],
            [1.0, 1.0, true],
            [1.0, -1.0, false],
        ];
    }

    public function popolaIndicatoriNonPOR(): void {
        $i = new TC44_45IndicatoriOutput();
        $azione = new Azione();
        $azione->addIndicatoriOutputAzioni(new IndicatoriOutputAzioni($i, $azione));
        $procedura = new Bando();
        $procedura->addAzioni($azione);
        $this->richiesta->setProcedura($procedura);
        $this->richiesta->setFlagPor(false);

        $this->base->popolaIndicatoriOutput();

        $this->assertEmpty($this->richiesta->getMonIndicatoreOutput());
    }


}
