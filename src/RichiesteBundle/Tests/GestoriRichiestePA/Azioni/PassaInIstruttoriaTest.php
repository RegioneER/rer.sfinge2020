<?php

namespace RichiesteBundle\Tests\GestoriRichiestePA\Azioni;

use RichiesteBundle\GestoriRichiestePA\Azioni\PassaInIstruttoria;
use RichiesteBundle\GestoriRichiestePA\Riepilogo\Riepilogo_Base;
use RichiesteBundle\Entity\VocePianoCosto;
use IstruttorieBundle\Entity\IstruttoriaVocePianoCosto;
use RichiesteBundle\Entity\Richiesta;
use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use RichiesteBundle\Entity\Proponente;
use SoggettoBundle\Entity\Azienda;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use IstruttorieBundle\Service\GestoreIstruttoriaBase;
use IstruttorieBundle\Service\GestoreIstruttoriaService;
use BaseBundle\Service\StatoService;
use BaseBundle\Entity\StatoRichiesta;
use Symfony\Component\HttpFoundation\RedirectResponse;
use BaseBundle\Tests\Service\TestBaseService;
use SfingeBundle\Entity\Bando;
use IstruttorieBundle\Service\IGestoreIstruttoria;
use Symfony\Component\Security\Csrf\CsrfToken;

class PassaInIstruttoriaTest extends TestBaseService {
    /**
     * @var Riepilogo_Base
     */
    protected $riepilogo;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var IGestoreIstruttoria
     */
    protected $gestoreIstruttoria;

    /**
     * @StatoSfinge
     */
    protected $statiSfinge;

    public function setUp() {
        parent::setUp();
        $this->request = new Request();
        $this->requestStack->push($this->request);

        $parameterBag = $this->createMock(ParameterBag::class);

        $parameterBag
            ->method('get')
            ->with($this->equalTo(PassaInIstruttoria::TOKEN_CSRF_NAME))
            ->willReturn('token');

        $this->request->query = $parameterBag;

        $istruttoriaService = $this->createMock(GestoreIstruttoriaService::class);
        $this->gestoreIstruttoria = $this->createMock(GestoreIstruttoriaBase::class);
        $istruttoriaService
        ->method('getGestore')
        ->willReturn($this->gestoreIstruttoria);

        $this->statiSfinge = $this->createMock(StatoService::class);
        $this->riepilogo = $this->createMock(Riepilogo_Base::class);
    }

    /**
     * @dataProvider risultatoEsecuzioneDataProvider
     */
    public function testRisultatoEsecuzione(Richiesta $richiesta) {
        $this->setupConnectionMock();
        $this->setupRiepilogoMock($richiesta);
        $this->setupEmMock($richiesta);

        $this->statiSfinge->expects($this->once())
        ->method('avanzaStato')
        ->with(
            $this->isInstanceOf(Richiesta::class),
            StatoRichiesta::PRE_PROTOCOLLATA
        );
        $this->container->set('sfinge.stati', $this->statiSfinge);

        $this->gestoreIstruttoria
        ->expects($this->once())
        ->method('aggiornaIstruttoriaRichiesta');
        $gestoreIstruttoriaService = $this->createMock(GestoreIstruttoriaService::class);
        $gestoreIstruttoriaService->method('getGestore')
        ->willReturn($this->gestoreIstruttoria);

        $this->container->set('gestore_istruttoria', $gestoreIstruttoriaService);

        $tokenManager = $this->container->get('security.csrf.token_manager');
        $token = new CsrfToken(PassaInIstruttoria::TOKEN_ID, PassaInIstruttoria::TOKEN_CSRF_NAME);
        $tokenManager->method('getToken')->willReturn($token);
        $tokenManager->method('isTokenValid')->with($token)->willReturn(true);

        $azione = new PassaInIstruttoria($this->container, $this->riepilogo);
        $res = $azione->getRisultatoEsecuzione();   /* @var RedirectResponse $res */

        $this->assertInstanceOf(RedirectResponse::class, $res);
        $this->assertEquals('url_redirect', $res->getTargetUrl());
        $this->assertContains('Richiesta validata con successo', $this->flashBag->get('success'));
    }

    public function risultatoEsecuzioneDataProvider() {
        $procedura = new Bando();
        $richiesta = new Richiesta();
        $richiesta->setProcedura($procedura);
        $mandatario = new Proponente();
        $mandatario->setMandatario(true);
        $mandatario->setRichiesta($richiesta);
        $soggetto = new Azienda();
        $soggetto->addProponenti($mandatario);
        $mandatario->setSoggetto($soggetto);
        $voce = new VocePianoCosto();
        for ($i = 1; $i < 8; ++$i) {
            $voce->{'setImportoAnno' . $i}($i);
        }
        $mandatario->addVociPianoCosto($voce);
        $richiesta->addProponenti($mandatario);

        return [
            [$richiesta],
        ];
    }

    protected function setupRiepilogoMock(Richiesta $richiesta) {
        $this->riepilogo
        ->method('getRichiesta')
        ->willReturn($richiesta);

        $this->riepilogo
        ->expects($this->atLeastOnce())
        ->method('isValido')
        ->willReturn(true);

        $this->riepilogo
        ->expects($this->once())
        ->method('getUrl')
        ->willReturn('url_redirect');
    }

    private function setupConnectionMock() {
        $this->connection
            ->expects($this->once())
            ->method('beginTransaction')
            ->willReturn(null);

        $this->connection
            ->expects($this->once())
            ->method('commit')
            ->willReturn(null);
    }

    protected function setupEmMock(Richiesta $richiesta) {
        $this->em
        ->expects($this->at(1))
        ->method('persist')
        ->with($this->callBackIstruttoriaVocePianoCosto());

        $this->em
        ->expects($this->at(2))
        ->method('persist')
        ->with($this->isInstanceOf(VocePianoCosto::class));

        $this->em
        ->expects($this->at(3))
        ->method('persist')
        ->with($this->callBackProponente($richiesta));

        $this->em
        ->expects($this->at(4))
        ->method('persist')
        ->with($this->isInstanceOf(Richiesta::class));

        $this->em
        ->expects($this->at(5))
        ->method('persist')
        ->with($this->callBackIstruttoria($richiesta));

        $this->em
        ->expects($this->once())
        ->method('flush');

        return $this->em;
    }

    protected function callBackProponente(Richiesta $richiesta) {
        return  $this->logicalAnd(
            $this->isInstanceOf(Proponente::class),
            $this->callback(function ($proponente) use ($richiesta) {
                $richiestaProponente = $proponente->getRichiesta();
                $res = !\is_null($richiestaProponente);
                $res = $res && $richiestaProponente instanceof Richiesta;
                $res = $res && $richiesta == $richiestaProponente;

                return $res;
            }));
    }

    protected function callBackIstruttoria(Richiesta $richiesta) {
        return $this->logicalAnd(
            $this->isInstanceOf(IstruttoriaRichiesta::class),
            $this->callback(function ($istruttoria) use ($richiesta) {
                return $richiesta == $istruttoria->getRichiesta();
            }));
    }

    protected function callBackIstruttoriaVocePianoCosto() {
        return  $this->logicalAnd(
            $this->isInstanceOf(IstruttoriaVocePianoCosto::class),
                $this->callback(function (IstruttoriaVocePianoCosto $istruttoria) {
                    $res = true;
                    for ($i = 1; $i < 8; ++$i) {
                        $res = $res && 0 == $istruttoria->{'getTaglioAnno' . $i}();
                        $res = $res && $istruttoria->{'getImportoAmmissibileAnno' . $i}() == $i;
                    }

                    return $res;
                })
        );
    }

    public function testIsVisibile() {
        $richiesta = new Richiesta();

        $this->riepilogo
            ->method('getRichiesta')
            ->willReturn($richiesta);

        $this->riepilogo
            ->method('isValido')
            ->willReturn(true);
        
        $token = new CsrfToken(PassaInIstruttoria::TOKEN_ID, PassaInIstruttoria::TOKEN_CSRF_NAME);
        $tokenManager = $this->container->get('security.csrf.token_manager');
        $tokenManager->method('getToken')->willReturn($token);

        $azione = new PassaInIstruttoria($this->container, $this->riepilogo);
        $this->assertTrue($azione->isVisibile());
    }

    public function testIsNotVisibile() {
        $richiesta = new Richiesta();
        $this->riepilogo
            ->method('getRichiesta')
            ->willReturn($richiesta);

        $this->riepilogo
        ->method('isValido')
        ->willReturn(false);
    
        $token = new CsrfToken(PassaInIstruttoria::TOKEN_ID, PassaInIstruttoria::TOKEN_CSRF_NAME);
        $tokenManager = $this->container->get('security.csrf.token_manager');
        $tokenManager->method('getToken')->willReturn($token);

        $azione = new PassaInIstruttoria($this->container, $this->riepilogo);
        $this->assertFalse($azione->isVisibile());
    }
}
