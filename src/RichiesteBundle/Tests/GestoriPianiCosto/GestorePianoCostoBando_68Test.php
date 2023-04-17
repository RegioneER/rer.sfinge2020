<?php

namespace RichiesteBundle\Tests\GestoriPianiCosto;

use BaseBundle\Tests\Service\TestBaseService;
use RichiesteBundle\GestoriPianiCosto\GestorePianoCostoBando_68;
use RichiesteBundle\Entity\ProponenteRepository;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Entity\Bando4\OggettoTipologia;
use SfingeBundle\Entity\Bando;
use Doctrine\Common\Collections\ArrayCollection;
use RichiesteBundle\Entity\PianoCosto;
use RichiesteBundle\Entity\PianoCostoRepository;
use RichiesteBundle\Entity\VocePianoCosto;
use PHPUnit\Framework\MockObject\MockObject;
use RichiesteBundle\GestoriRichieste\GestoreRichiesteBando_68;
use RichiesteBundle\Service\GestoreRichiestaService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Form;
use RichiesteBundle\Service\GestoreModalitaFinanziamentoService;
use RichiesteBundle\GestoriModalitaFinanziamento\GestoreModalitaFinanziamentoBando_68;
use SfingeBundle\Entity\Procedura;
use RichiesteBundle\Utility\EsitoValidazione;
use RichiesteBundle\Entity\PrioritaProponente;
use SfingeBundle\Entity\SistemaProduttivo;

class GestorePianoCostoBando_68Test extends TestBaseService {
    /**
     * @var GestorePianoCostoBando_68
     */
    protected $gestore;

    /**
     * @var Proponente
     */
    protected $proponente;

    /**
     * @var OggettoTipologia
     */
    protected $oggetto;

    /**
     * @var ProponenteRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $proponenteRepository;

    /**
     * @var PianoCostoRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pianoCostiRepository;

    /**
     * @var GestoreRichiesteBando_68|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $gestoreRichieste;

    /**
     * @var GestoreModalitaFinanziamentoBando_68
     */
    protected $gestoreModalitaFinanziamento;

    public function setUp() {
        parent::setUp();

        $richiesta = new Richiesta();
        $this->proponente = new Proponente();
        $this->proponente->setRichiesta($richiesta);
        $this->proponente->setMandatario(true);
        $this->proponente->setId(2);
        $this->oggetto = new OggettoTipologia($richiesta);
        $richiesta->addOggettiRichiestum($this->oggetto);
        $procedura = new Bando();
        $procedura->setId(1);
        $richiesta->setProcedura($procedura);

        $this->proponenteRepository = $this->createMock(ProponenteRepository::class);
        $this->proponenteRepository->method('find')->willReturn($this->proponente);

        $this->pianoCostiRepository = $this->createMock(PianoCostoRepository::class);
        $this->em->method('getRepository')->will(
            $this->returnValueMap([
                ['RichiesteBundle:Proponente', $this->proponenteRepository],
                ['RichiesteBundle:PianoCosto', $this->pianoCostiRepository],
        ]));

        $this->gestoreRichieste = new GestoreRichiesteBando_68($this->container);
        $richiesteServiceFactory = $this->createMock(GestoreRichiestaService::class);
        $richiesteServiceFactory->method('getGestore')
            ->with($this->equalTo($procedura))
            ->willReturn($this->gestoreRichieste);
        $this->container->set('gestore_richieste', $richiesteServiceFactory);

        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $this->requestStack->push($request);

        $modalitaFinanziamentoService = $this->createMock(GestoreModalitaFinanziamentoService::class);
        $this->gestoreModalitaFinanziamento = $this->createMock(GestoreModalitaFinanziamentoBando_68::class);
        $modalitaFinanziamentoService->method('getGestore')
        ->with($this->isInstanceOf(Procedura::class))
        ->willReturn($this->gestoreModalitaFinanziamento);

        $this->container->set('gestore_modalita_finanziamento', $modalitaFinanziamentoService);

        $this->gestore = new GestorePianoCostoBando_68($this->container);
    }

    /**
     * @dataProvider tipologiaDataProvider
     */
    public function testGeneraPianoCosto($tipologia) {
        $this->oggetto->setTipologia($tipologia);

        $pianoCosto = new PianoCosto();
        $piano = new ArrayCollection([
            $pianoCosto,
        ]);

        $this->pianoCostiRepository->method('getVociDaProceduraSezione')
        ->with(
            $this->equalTo(1),
            $this->equalTo($tipologia)
        )
        ->willReturn($piano);

        $this->em->expects($this->atLeastOnce())
        ->method('persist')
        ->with(
            $this->logicalAnd(
                $this->isInstanceOf(VocePianoCosto::class),
                $this->attributeEqualTo('proponente', $this->proponente),
                $this->attributeEqualTo('piano_costo', $pianoCosto)
            ));

        $this->em->expects($this->never())->method('flush');

        $res = $this->gestore->generaPianoDeiCostiProponente(0);
        $this->assertTrue($res);
    }

    public function tipologiaDataProvider() {
        return [
            [OggettoTipologia::TIPOLOGIA_A],
            [OggettoTipologia::TIPOLOGIA_B],
        ];
    }

    /**
     * @dataProvider tipologiaDataProvider
     */
    public function testGetAnnualita($tipologia) {
        $this->oggetto->setTipologia($tipologia);
        $res = $this->gestore->getAnnualita(1);
        $this->assertEquals(
           $this->getTipologia($tipologia), $res);
    }

    public static function getTipologia($tipologia): array {
        return OggettoTipologia::TIPOLOGIA_A == $tipologia ?
        GestorePianoCostoBando_68::ANNUALITA_TIPOLOGIA_A :
        GestorePianoCostoBando_68::ANNUALITA_TIPOLOGIA_B;
    }

    /**
     * @dataProvider tipologiaDataProvider
     */
    public function testAggiornaPianoDeiCostiProponente($tipologia) {
        $this->router->method('generate')->willReturn('qualche stringa');
        $this->oggetto->setTipologia($tipologia);
        $form = $this->createMock(Form::class);
        $form->expects($this->once())->method('isValid')->willReturn(true);
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);

        $this->em->expects($this->at(2))->method('persist')
       ->with($this->isInstanceOf(Proponente::class));
       $this->em->expects($this->at(3))->method('persist')
       ->with($this->isInstanceOf(Richiesta::class));

        $this->em->expects($this->atLeastOnce())->method('flush');
        $this->formFactory->method('create')
       ->with($this->equalTo('RichiesteBundle\Form\Bando68\PianoCostiBando68Type'),
        $this->equalTo($this->proponente),
        $this->logicalAnd(
            $this->arrayHasKey('url_indietro'),
            $this->arrayHasKey('disabled'),
            $this->arrayHasKey('modalita_finanziamento_attiva'),
            $this->arrayHasKey('annualita'),
            $this->arrayHasKey('labels_anno'),
            $this->arrayHasKey('totale'),
            $this->callback([$this, 'labelsAnnoCallBack'])
        ))
       ->willReturn($form);

       $this->addVoce('dummy', 99999999);
       $this->addVoce(GestorePianoCostoBando_68::VOCE_TOTALE, 99999999);

        $this->gestore->aggiornaPianoDeiCostiProponente(1);
    }

    public static function labelsAnnoCallBack(array $value) {
        $labels = $value['labels_anno'];
        return \array_key_exists('importo_anno_1', $labels) &&
        \array_key_exists('importo_anno_2', $labels);
    }

    public function testSpeseAffittoTroppoAlte() {
        $this->oggetto->setTipologia(OggettoTipologia::TIPOLOGIA_A);
        $this->gestoreModalitaFinanziamento->method('validaModalitaFinanziamentoRichiesta')->willReturn(new EsitoValidazione(true));

        $this->addVoce(GestorePianoCostoBando_68::VOCE_B_TIPO_A, 4000);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_D_TIPO_A, 1000);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_TOTALE, 5000);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_E_TIPO_A, 0);

        $res = $this->gestore->validaPianoDeiCostiProponente(1);

        $this->assertFalse($res->getEsito());
        $this->assertContains('La voce B è superiore al 20% del totale', $res->getTuttiMessaggi());
    }

    protected function addVoce(string $codice, float $importo) {
        $piano = new PianoCosto();
        $piano->setCodice($codice);

        $voceSpese = new VocePianoCosto();
        $voceSpese->setProponente($this->proponente);
        $voceSpese->setPianoCosto($piano);
        $voceSpese->setImportoAnno1($importo);
        $this->proponente->addVociPianoCosto($voceSpese);
    }

    public function testSpeseCostituzioneTroppoAlte() {
        $this->oggetto->setTipologia(OggettoTipologia::TIPOLOGIA_A);
        $this->gestoreModalitaFinanziamento->method('validaModalitaFinanziamentoRichiesta')->willReturn(new EsitoValidazione(true));

        $this->addVoce(GestorePianoCostoBando_68::VOCE_B_TIPO_A, 0);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_D_TIPO_A, 4000);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_E_TIPO_A, 0);

        $this->addVoce(GestorePianoCostoBando_68::VOCE_TOTALE, 4000);

        $res = $this->gestore->validaPianoDeiCostiProponente(1);

        $this->assertFalse($res->getEsito());
        $this->assertContains('La voce D è superiore a 2.000,00 €', $res->getTuttiMessaggi());
    }

    public function testSpesePromozionaliTroppoAlte() {
        $this->oggetto->setTipologia(OggettoTipologia::TIPOLOGIA_A);
        $this->gestoreModalitaFinanziamento->method('validaModalitaFinanziamentoRichiesta')->willReturn(new EsitoValidazione(true));

        $this->addVoce(GestorePianoCostoBando_68::VOCE_B_TIPO_A, 0);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_E_TIPO_A, 50000.01);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_D_TIPO_A, 0);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_TOTALE, 50000.01);

        $res = $this->gestore->validaPianoDeiCostiProponente(1);

        $this->assertFalse($res->getEsito());
        $this->assertContains('La voce E è superiore a 25.000,00 €', $res->getTuttiMessaggi());
    }

    public function testTotNonCorrisponde() {
        $this->oggetto->setTipologia(OggettoTipologia::TIPOLOGIA_A);
        $this->gestoreModalitaFinanziamento->method('validaModalitaFinanziamentoRichiesta')->willReturn(new EsitoValidazione(true));

        $this->addVoce(GestorePianoCostoBando_68::VOCE_B_TIPO_A, 0);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_E_TIPO_A, 0);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_D_TIPO_A, 0);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_TOTALE, 1000);

        $res = $this->gestore->validaPianoDeiCostiProponente(1);

        $this->assertFalse($res->getEsito());
        $this->assertContains('Il totale non corrisponde alla somma delle voci', $res->getTuttiMessaggi());
    }

    public function testTotaleNegativo() {
        $this->oggetto->setTipologia(OggettoTipologia::TIPOLOGIA_A);
        $this->gestoreModalitaFinanziamento->method('validaModalitaFinanziamentoRichiesta')->willReturn(new EsitoValidazione(true));

        $this->addVoce(GestorePianoCostoBando_68::VOCE_B_TIPO_A, 0);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_E_TIPO_A, -1000);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_D_TIPO_A, 0);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_TOTALE, -1000);

        $res = $this->gestore->validaPianoDeiCostiProponente(1);

        $this->assertFalse($res->getEsito());
        $this->assertContains('Il totale non può essere negativo', $res->getTuttiMessaggi());
    }

    public function testOkTipologiaA() {
        $this->oggetto->setTipologia(OggettoTipologia::TIPOLOGIA_A);
        $this->gestoreModalitaFinanziamento->method('validaModalitaFinanziamentoRichiesta')->willReturn(new EsitoValidazione(true));

        $this->addVoce(GestorePianoCostoBando_68::VOCE_B_TIPO_A, 0);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_E_TIPO_A, 1000);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_D_TIPO_A, 0);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_TOTALE, 1000);

        $res = $this->gestore->validaPianoDeiCostiProponente(1);

        $this->assertTrue($res->getEsito());
        $this->assertEmpty($res->getTuttiMessaggi());
    }

    public function testAcquisizioneSedi() {
        $this->oggetto->setTipologia(OggettoTipologia::TIPOLOGIA_B);
        $this->gestoreModalitaFinanziamento->method('validaModalitaFinanziamentoRichiesta')->willReturn(new EsitoValidazione(true));

        $this->addVoce(GestorePianoCostoBando_68::VOCE_A_TIPO_B, 1000);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_E_TIPO_B, 0);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_C_TIPO_B, 0);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_TOTALE, 1000);

        $res = $this->gestore->validaPianoDeiCostiProponente(1);

        $this->assertFalse($res->getEsito());
        $this->assertContains('La voce A è superiore al 50% del totale', $res->getTuttiMessaggi());
    }

    public function testSpeseAffittoB() {
        $this->oggetto->setTipologia(OggettoTipologia::TIPOLOGIA_B);
        $this->gestoreModalitaFinanziamento->method('validaModalitaFinanziamentoRichiesta')->willReturn(new EsitoValidazione(true));

        $this->addVoce(GestorePianoCostoBando_68::VOCE_A_TIPO_B, 1000);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_C_TIPO_B, 2000);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_E_TIPO_B, 0);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_TOTALE, 3000);

        $res = $this->gestore->validaPianoDeiCostiProponente(1);

        $this->assertFalse($res->getEsito());
        $this->assertContains('La voce C è superiore al 20% del totale', $res->getTuttiMessaggi());
    }

    public function testSpesePromozionaliB() {
        $this->oggetto->setTipologia(OggettoTipologia::TIPOLOGIA_B);
        $this->gestoreModalitaFinanziamento->method('validaModalitaFinanziamentoRichiesta')->willReturn(new EsitoValidazione(true));

        $this->addVoce(GestorePianoCostoBando_68::VOCE_A_TIPO_B, 0);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_C_TIPO_B, 0);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_E_TIPO_B, 1000);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_TOTALE, 1000);

        $res = $this->gestore->validaPianoDeiCostiProponente(1);

        $this->assertFalse($res->getEsito());
        $this->assertContains('La voce E è superiore al 10% del totale', $res->getTuttiMessaggi());
    }

    public function testOkTipologiaB() {
        $this->oggetto->setTipologia(OggettoTipologia::TIPOLOGIA_B);
        $this->gestoreModalitaFinanziamento->method('validaModalitaFinanziamentoRichiesta')->willReturn(new EsitoValidazione(true));

        $this->addVoce(GestorePianoCostoBando_68::VOCE_A_TIPO_B, 50);
        $this->addVoce('dummy', 20);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_C_TIPO_B, 20);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_E_TIPO_B, 10);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_TOTALE, 100);

        $res = $this->gestore->validaPianoDeiCostiProponente(1);

        $this->assertTrue($res->getEsito());
    }

    /**
     * @dataProvider totaleTotaleTroppoBassoTipologiaADataProvider
     */
    public function testTotaleTroppoBassoTipologiaA($sistemaProduttivo, $importo, $minimo){
        $this->oggetto->setTipologia(OggettoTipologia::TIPOLOGIA_A);
        $this->addSistemaProduttivo($sistemaProduttivo);
        $this->gestoreModalitaFinanziamento->method('validaModalitaFinanziamentoRichiesta')->willReturn(new EsitoValidazione(true));

        $this->addVoce(GestorePianoCostoBando_68::VOCE_B_TIPO_A, 0);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_E_TIPO_A, 1000);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_D_TIPO_A, 0);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_TOTALE, 1000);

        $res = $this->gestore->validaPianoDeiCostiProponente(1);

        $this->assertFalse($res->getEsito());
        $this->assertContains("Il totale deve essere minimo ".\number_format($minimo,2, ',','.' )." €", $res->getTuttiMessaggi());
    }

    public function totaleTotaleTroppoBassoTipologiaADataProvider(){
        return [
            ['A.1', 79999, 80000],
            ['A.2', 79999, 80000],
            ['A.3', 79999, 80000],
            ['B.1', 49999, 50000],
            ['B.2', 49999, 50000],
            ['D.1', 49999, 50000],
        ];
    }

    /**
     * @dataProvider totaleTroppoBassoTipologiaBDataProvider
     */
    public function testTotaleTroppoBassoTipologiaB($sistemaProduttivo, $importo, $minimo){
        $this->oggetto->setTipologia(OggettoTipologia::TIPOLOGIA_B);
        $this->addSistemaProduttivo($sistemaProduttivo);

        $this->gestoreModalitaFinanziamento->method('validaModalitaFinanziamentoRichiesta')->willReturn(new EsitoValidazione(true));

        $this->addVoce(GestorePianoCostoBando_68::VOCE_A_TIPO_B, 0);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_C_TIPO_B, 0);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_E_TIPO_B, 0);
        $this->addVoce('dummy', $importo);
        $this->addVoce(GestorePianoCostoBando_68::VOCE_TOTALE, $importo);

        $res = $this->gestore->validaPianoDeiCostiProponente(1);

        $this->assertFalse($res->getEsito());
        $this->assertContains("Il totale deve essere minimo ".\number_format($minimo,2, ',','.' )." €", $res->getTuttiMessaggi());
    }

    public function totaleTroppoBassoTipologiaBDataProvider(): array{
        return[
            ['A.1', 149999, 150000],
            ['A.2', 149999, 150000],
            ['A.3', 149999, 150000],
            ['B.1',79999, 80000],
            ['B.2', 79999, 80000],
            ['D.1', 79999, 80000],
        ];
    }

    protected function addSistemaProduttivo(string $codice):void{
        $priorita = new PrioritaProponente();
        $priorita->setProponente($this->proponente);
        $this->proponente->addPriorita($priorita);
        $sistemaProduttivo = new SistemaProduttivo();
        $sistemaProduttivo->setCodice($codice);
        $priorita->setSistemaProduttivo($sistemaProduttivo);
    }
}
