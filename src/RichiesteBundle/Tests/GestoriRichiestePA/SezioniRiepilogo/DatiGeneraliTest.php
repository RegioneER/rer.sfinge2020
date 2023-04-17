<?php

namespace RichiesteBundle\Tests\GestoriRichiestePA\SezioniRiepilogo;

use BaseBundle\Tests\Service\TestBaseService;
use RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\DatiGenerali;
use RichiesteBundle\GestoriRichiestePA\IRiepilogoRichiesta;
use SfingeBundle\Entity\ProceduraPA;
use RichiesteBundle\Entity\Richiesta;
use SfingeBundle\Entity\Procedura;
use RichiesteBundle\Form\DatiGeneraliType;
use Symfony\Component\Form\Form;
use RichiesteBundle\Service\GestoreRichiestaService;
use RichiesteBundle\Service\IGestoreRichiesta;
use RichiesteBundle\Utility\EsitoValidazione;

class DatiGeneraliTest extends TestBaseService{
    
        
    /**
     * @var IRiepilogoRichiesta|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $riepilogo;

    /**
     * @var Procedura
     */
    protected $procedura;

    /**
     * @var Richiesta
     */
    protected $richiesta;

    /**
     * @var DatiGenerali
     */
    protected $datiGenerali;
    
    /**
     * @var IGestoreRichiesta|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $gestoreRichieste;

    public function setUp(){
        parent::setUp();
        $this->riepilogo = $this->createMock(IRiepilogoRichiesta::class);
        $this->procedura = new ProceduraPA();
        $this->richiesta = new Richiesta();
        $this->richiesta->setProcedura($this->procedura);
        $this->procedura->addRichieste($this->richiesta);
        $security = $this->container->get('security.authorization_checker');
        $security->method('isGranted')->willReturn(true);
        $this->riepilogo->method('getRichiesta')->willReturn($this->richiesta);
        $this->datiGenerali = new DatiGenerali($this->container, $this->riepilogo);

        $this->gestoreRichieste = $this->createMock(IGestoreRichiesta::class);
        $gestoreService = $this->createMock(GestoreRichiestaService::class);
        $gestoreService->method('getGestore')->willReturn($this->gestoreRichieste);
        $this->gestoreRichieste->method('validaDatiGenerali')->willReturn(new EsitoValidazione(true));
        $this->container->set('gestore_richieste', $gestoreService);
    }
    
    protected function effettuaValidazione():array{
        $this->datiGenerali->valida();
        return $this->datiGenerali->getMessaggi();
    }
    
    public function testCorrettaConEsenzioneBollo(){
        $this->procedura->setMarcaDaBollo(true);
        $this->procedura->setEsenzioneMarcaBollo(true);

        $this->richiesta->setEsenteMarcaDaBollo(true);
        $this->richiesta->setRiferimentoNormativoEsenzione('blablabla');

        $msgs = $this->effettuaValidazione();
        $this->assertEmpty($msgs);
    }


    public function testMarcaBolloCorretta(){
        $this->procedura->setMarcaDaBollo(true);

        $this->richiesta->setDataMarcaDaBollo(new \DateTime());
        $this->richiesta->setNumeroMarcaDaBollo('12345678901234');

        $msgs= $this->effettuaValidazione();
        $this->assertEmpty($msgs);
    }


    public function testVisualizzaSezione(){
        $form = $this->createMock(Form::class);
        $this->formFactory->method('create')->willReturn($form);

        $this->datiGenerali->visualizzaSezione([]);
    }

    public function testInviaFormCorrettamente(){
        $form = $this->createMock(Form::class);
        $form->expects($this->atLeastOnce())->method('isSubmitted')->willReturn(true);
        $form->expects($this->atLeastOnce())->method('isValid')->willReturn(true);
        $this->formFactory->method('create')->willReturn($form);

        $this->em->expects($this->atLeastOnce())
        ->method('persist')
        ->with($this->isInstanceOf(Richiesta::class));

        $this->em->expects($this->once())->method('flush');

        $this->datiGenerali->visualizzaSezione([]);
    }

    public function testInviaFormNonCorretto(){
        $form = $this->createMock(Form::class);
        $form->expects($this->atLeastOnce())->method('isSubmitted')->willReturn(true);
        $form->expects($this->atLeastOnce())->method('isValid')->willReturn(false);
        $this->formFactory->method('create')->willReturn($form);

        $this->em->expects($this->never())
        ->method('persist');

        $this->em->expects($this->never())->method('flush');

        $this->datiGenerali->visualizzaSezione([]);
    }

    public function testInviaFormErroreSalvataggio(){
        $form = $this->createMock(Form::class);
        $form->expects($this->atLeastOnce())->method('isSubmitted')->willReturn(true);
        $form->expects($this->atLeastOnce())->method('isValid')->willReturn(true);
        $this->formFactory->method('create')->willReturn($form);

        $this->em->expects($this->atLeastOnce())
        ->method('persist')
        ->with($this->isInstanceOf(Richiesta::class));

        $this->em->expects($this->once())->method('flush')->will($this->throwException(new \Exception('eccezione salvataggio')));
        $this->datiGenerali->visualizzaSezione([]);
        
        $errori = $this->flashBag->get('error');
        $this->assertNotEmpty($errori);
        $this->assertContains("Errore durante il salvataggio delle informazioni", $errori);
    }
}