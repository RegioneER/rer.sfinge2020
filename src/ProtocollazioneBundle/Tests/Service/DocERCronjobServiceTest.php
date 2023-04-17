<?php

namespace ProtocollazioneBundle\Tests\Service;

use ProtocollazioneBundle\Service\DocERLogService;
use ProtocollazioneBundle\Service\DocERCronjobService;
use ProtocollazioneBundle\Service\IntegrazioneDocERService;
use ProtocollazioneBundle\Repository\ProcessoRepository;
use ProtocollazioneBundle\Entity\Processo;
use ProtocollazioneBundle\Entity\RichiestaProtocollo;
use Doctrine\ORM\EntityRepository;
use ProtocollazioneBundle\Repository\IstanzaProcessoRepository;
use ProtocollazioneBundle\Repository\RichiestaProtocolloRepository;
use SfingeBundle\Entity\ParametroSistema;
use ProtocollazioneBundle\Entity\IstanzaProcesso;
use ProtocollazioneBundle\Repository\RichiestaProtocolloDocumentoRepository;
use ProtocollazioneBundle\Entity\RichiestaProtocolloDocumento;
use SfingeBundle\Entity\Bando;
use ProtocollazioneBundle\Entity\RichiestaProtocolloFinanziamento;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Entity\ProponenteRepository;
use SoggettoBundle\Entity\Azienda;
use ProtocollazioneBundle\Repository\FascicoloBandoAziendaRepository;
use BaseBundle\Entity\StatoRichiesta;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use SfingeBundle\Entity\Utente;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use ProtocollazioneBundle\Entity\RichiestaProtocolloIntegrazione;
use ProtocollazioneBundle\Entity\RichiestaProtocolloRispostaIntegrazione;
use AttuazioneControlloBundle\Entity\Pagamento;
use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use ProtocollazioneBundle\Service\RegistrazioneDocERService;
use BaseBundle\Tests\Service\TestBaseService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class DocERCronjobServiceTest extends TestBaseService {
    /**
     * @var DocERLogService
     */
    protected $docerLogger;

    /**
     * @var IntegrazioneDocERService
     */
    protected $integrazioneDocerService;

    /**
     * @var RegistrazioneDocERService
     */
    protected $registrazioneDocerService;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    public function setUp() {
        parent::setUp();

        $this->docerLogger = new DocERLogService($this->doctrine);
        $this->container->set('docerLogger', $this->docerLogger);

        $this->integrazioneDocerService = $this->createMock(IntegrazioneDocERService::class);
        $this->registrazioneDocerService = $this->createMock(RegistrazioneDocERService::class);
        $this->container->set('docerintegrazione', $this->integrazioneDocerService);

        $this->tokenStorage = new TokenStorage();

        $this->container->set('security.token_storage', $this->tokenStorage);
    }

    public function testProtocollazioneDomandaContributoProcessoNonPresente() {
        $codiceProcesso = 'protocollazione_domande_contributo';

        $parametriRepository = $this->createMock(EntityRepository::class);
        $repositoryProtocollazione = $this->createMock(ProcessoRepository::class);
        $this->em->method('getRepository')
        ->will($this->returnValueMap([
            ['ProtocollazioneBundle:Processo', $repositoryProtocollazione],
            ['SfingeBundle:ParametroSistema', $parametriRepository],
        ]));

        $docerService = $this->createService($codiceProcesso);
        $docerService->elabora();
        $messaggiRisposta = $docerService->getMsg_array();
        $this->assertContains(
            [
                'errore' => "<strong>Errore: </strong>il processo [" . $codiceProcesso . "] non è attivo o è impossibile determinarne l'identificativo", ], $messaggiRisposta);
    }

    public function testProtocollazioneDomandaContributoRichiestaNonPresente() {
        $codiceProcesso = 'protocollazione_domande_contributo';
        $processo = new Processo();

        $parametriRepository = $this->createMock(EntityRepository::class);

        $repositoryProtocollazione = $this->createMock(ProcessoRepository::class);
        $repositoryProtocollazione->method('findOneBy')->willReturn($processo);
        $istanzaRepository = $this->createMock(IstanzaProcessoRepository::class);
        $richiestaProtocolloRepository = $this->createMock(RichiestaProtocolloRepository::class);
        $this->em->method('getRepository')
        ->will($this->returnValueMap([
            ['ProtocollazioneBundle:Processo', $repositoryProtocollazione],
            ['SfingeBundle:ParametroSistema', $parametriRepository],
            ['ProtocollazioneBundle:IstanzaProcesso', $istanzaRepository],
            ['ProtocollazioneBundle:RichiestaProtocollo', $richiestaProtocolloRepository],
        ]));

        $docerService = $this->createService($codiceProcesso);
        $docerService->elabora();
        $messaggi = $docerService->getMsg_array();
        $this->assertContains(['avviso' => "<strong>Avviso: </strong>non vi sono richieste da protocollare per il processo [" . $codiceProcesso . "]"], $messaggi);
    }

    public function testParametroDiSistemaPresente() {
        $codiceProcesso = 'protocollazione_domande_contributo';
        $parametro = new ParametroSistema();
        $parametro->setValore(1);
        $parametriRepository = $this->createMockOverrideMethods(EntityRepository::class, ['findOneByCodice']);
        $parametriRepository
        ->expects($this->once())
        ->method('findOneByCodice')
        ->willReturn($parametro);
        $this->em->method('getRepository')->will(
            $this->returnValueMap([
                ['SfingeBundle:ParametroSistema', $parametriRepository],
            ])
            );

        $docerService = $this->createService($codiceProcesso);
    }

    protected function createMockOverrideMethods($class, array $methods) {
        $repo = $this->getMockBuilder($class)
                ->disableOriginalConstructor()
                ->setMethods($methods)
                ->getMock();
        return $repo;
    }

    public function testIstanzaProcessoPresente() {
        $codiceProcesso = 'protocollazione_domande_contributo';
        $processo = new Processo();

        $parametriRepository = $this->createMockOverrideMethods(EntityRepository::class, ['findOneByCodice']);

        $richiestaProtocollo = new RichiestaProtocollo();
        $repositoryRichiestaProtocollo = $this->createMockOverrideMethods(RichiestaProtocolloRepository::class, ['findBy']);
        $repositoryRichiestaProtocollo->method('findBy')
        ->willReturn([$richiestaProtocollo]);

        $repositoryProcesso = $this->createMock(ProcessoRepository::class);
        $repositoryProcesso->method('findOneBy')->willReturn($processo);

        $istanzaRepository = $this->createMock(IstanzaProcessoRepository::class);
        $istanzaRepository->method('cercaIstanzeByProcessoId')
        ->willReturn([new IstanzaProcesso()]);

        $this->em->method('getRepository')
        ->will($this->returnValueMap([
            ['ProtocollazioneBundle:Processo', $repositoryProcesso],
            ['ProtocollazioneBundle:RichiestaProtocollo', $repositoryRichiestaProtocollo],
            ['SfingeBundle:ParametroSistema', $parametriRepository],
            ['ProtocollazioneBundle:IstanzaProcesso', $istanzaRepository],
        ]));

        $docerService = $this->createService($codiceProcesso);
        $docerService->elabora();

        $messaggi = $docerService->getMsg_array();
        // $this->assertEquals(DocERCronjobService::IN_LAVORAZIONE, $richiestaProtocollo->getStato());
        $this->assertContains(['avviso' => "<strong>Avviso: </strong>sono presenti istanze di processo in esecuzione."], $messaggi);
    }

    public function testNessunDocumento() {
        $messaggi = $this->esaminaDocumenti([]);
        $this->assertEmpty($messaggi);
    }

    /**
     * @param RichiestaProtocolloDocumento[] $documenti
     * @param Proponente $proponente = NULL
     * @param mixed|null $findByResults
     * @return array
     */
    protected function esaminaDocumenti(array $documenti, Proponente $proponente = null, $findByResults = null, array $mockRepositories = []): array {
        $codiceProcesso = 'protocollazione_domande_contributo';
        $processo = new Processo();
        $processo->setCodice($codiceProcesso);

        $parametriRepository = $this->createMockOverrideMethods(EntityRepository::class, ['findOneByCodice']);

        $richiestaProtocollo = new RichiestaProtocollo();
        $repositoryRichiestaProtocollo = $this->createMockOverrideMethods(RichiestaProtocolloRepository::class, ['findBy']);
        $repositoryRichiestaProtocollo->method('findBy')
        ->willReturn([$richiestaProtocollo]);

        $repositoryProcesso = $this->createMock(ProcessoRepository::class);
        $repositoryProcesso->method('findOneBy')->willReturn($processo);

        $istanzaRepository = $this->createMock(IstanzaProcessoRepository::class);
        $istanzaRepository->method('cercaIstanzeByProcessoId')
        ->willReturn([]);

        $richiestaProtocolloDocumentoRepository = $this->createMock(RichiestaProtocolloDocumentoRepository::class);
        $richiestaProtocolloDocumentoRepository->method('cercaFaseByProcessoId')->willReturn($documenti);
        if ($findByResults) {
            $richiestaProtocolloDocumentoRepository->method('findBy')->will($findByResults);
        }

        $proponenteRepository = $this->createMock(ProponenteRepository::class);
        $proponenteRepository->method('findOneBy')->willReturn($proponente);

        $fascicoloBandoRepository = $this->createMock(FascicoloBandoAziendaRepository::class);

        $statoRepository = $this->createMockOverrideMethods(EntityRepository::class, ['findOneByCodice']);
        $stato = new StatoRichiesta();
        $statoRepository->method('findOneByCodice')->willReturn($stato);

        $this->em->method('getRepository')
        ->will($this->returnValueMap(\array_merge([
            ['ProtocollazioneBundle:Processo', $repositoryProcesso],
            ['ProtocollazioneBundle:RichiestaProtocollo', $repositoryRichiestaProtocollo],
            ['SfingeBundle:ParametroSistema', $parametriRepository],
            ['ProtocollazioneBundle:IstanzaProcesso', $istanzaRepository],
            ['ProtocollazioneBundle:RichiestaProtocolloDocumento', $richiestaProtocolloDocumentoRepository],
            ['RichiesteBundle:Proponente', $proponenteRepository],
            ['ProtocollazioneBundle:FascicoloBandoAzienda', $fascicoloBandoRepository],
            ['BaseBundle:Stato', $statoRepository],
        ], $mockRepositories)));

        $docerService = $this->createService($codiceProcesso);
        $docerService->elabora();

        return $docerService->getMsg_array();
    }

    public function testAvviaProcessoDummy() {
        $soggetto = new Azienda();

        $proponente = new Proponente();
        $proponente->setMandatario(true);
        $proponente->setSoggetto($soggetto);

        $procedura = new Bando();

        $richiestaProtocollo = new RichiestaProtocolloFinanziamento();
        $richiestaProtocollo->setProcedura($procedura);

        $richiesta = new Richiesta();
        $richiesta->addProponenti($proponente);
        $richiesta->setProcedura($procedura);
        $richiesta->addRichiesteProtocollo($richiestaProtocollo);
        $richiestaProtocollo->setRichiesta($richiesta);
        $procedura->addRichieste($richiesta);
        $procedura->setServizioProtocollazione('INTEGRAZIONE');

        $documento = new RichiestaProtocolloDocumento();
        $documento->setRichiestaProtocollo($richiestaProtocollo);

        $messaggi = $this->esaminaDocumenti([$documento], $proponente);
        $this->assertContains(['msg_titolo_head' => 'Avvio procedimento...'], $messaggi);
    }

    /**
     * @dataProvider getTipiProtocollazioneRichiesta
     */
    public function testAvviaProcessoDaCaricamentoDocumentiNessunAllegato(RichiestaProtocolloDocumento $documentoPrincipale) {
        /** @var RichiestaProtocolloFinanziamento $richiestaProtocollo */
        $richiestaProtocollo = $documentoPrincipale->getRichiestaProtocollo();
        $proponente = $richiestaProtocollo->getRichiesta()->getMandatario();

        $messaggi = $this->esaminaDocumenti([$documentoPrincipale], $proponente);
        $this->assertContains(['msg_titolo_head' => 'Avvio procedimento...'], $messaggi);
        $this->assertContains(['msg_fase' => 'Fase 2: caricamento allegati...'], $messaggi);
        $this->assertContains(['msg_fase' => "Fase 3: creazione unita' documentale..."], $messaggi);
        $idProtocollo = $richiestaProtocollo->getId();
        $this->assertContains(['messaggio' => "Nessun allegato associato alla richiesta di protocollo [$idProtocollo]"], $messaggi);
    }

    public function getTipiProtocollazioneRichiesta(): array {
        return [
            [$this->createRichiestaProtocolloDocumento(2, RichiestaProtocolloFinanziamento::class)],
            [$this->createRichiestaProtocolloDocumento(2, RichiestaProtocolloIntegrazione::class)],
            [$this->createRichiestaProtocolloDocumento(2, RichiestaProtocolloRispostaIntegrazione::class)],
        ];
    }

    /**
     * @dataProvider getTipiProtocollazioneRichiesta
     */
    public function testAvviaProcessoDaCaricamentoDocumentiConAllegati(RichiestaProtocolloDocumento $documentoPrincipale) {
        /** @var RichiestaProtocolloFinanziamento $richiestaProtocollo */
        $richiestaProtocollo = $documentoPrincipale->getRichiestaProtocollo();
        $proponente = $richiestaProtocollo->getRichiesta()->getMandatario();
        $docRepo = $this->createMock(RichiestaProtocolloDocumentoRepository::class);
        $docRepo->method('findBy')->willReturn(new RichiestaProtocolloDocumento());

        $messaggi = $this->esaminaDocumenti([$documentoPrincipale], $proponente, null, [['ProtocollazioneBundle:RichiestaProtocolloDocumento', $docRepo]]);
        $this->assertContains(['msg_titolo_head' => 'Avvio procedimento...'], $messaggi);
        $this->assertContains(['msg_fase' => 'Fase 2: caricamento allegati...'], $messaggi);
        $this->assertContains(['msg_fase' => "Fase 3: creazione unita' documentale..."], $messaggi);
        $idProtocollo = $richiestaProtocollo->getId();
        $this->assertContains(['messaggio' => "Nessun allegato associato alla richiesta di protocollo [$idProtocollo]"], $messaggi);
    }

    /**
     * @return RichiestaProtocolloDocumento
     * @param mixed $fase
     * @param mixed $classeProtocollo
     */
    protected function createRichiestaProtocolloDocumento($fase, $classeProtocollo = RichiestaProtocolloFinanziamento::class) {
        $soggetto = new Azienda();

        $proponente = new Proponente();
        $proponente->setMandatario(true);
        $proponente->setSoggetto($soggetto);

        $procedura = new Bando();
        $procedura->setClassifica('classifica');
        $procedura->setAnnoProtocollazione(2017);
        $procedura->setUnitaOrganizzativa('unita');
        $procedura->setFascicoloPrincipale('fascicolo');
        $procedura->setServizioProtocollazione('INTEGRAZIONE');
        $richiestaProtocollo = new $classeProtocollo();
        $richiestaProtocollo->setProcedura($procedura);
        $richiestaProtocollo->setId(919);
        $richiestaProtocollo->setFase($fase);

        $richiesta = new Richiesta();
        $richiesta->addProponenti($proponente);
        $richiesta->setProcedura($procedura);

        $statoRichiesta = new StatoRichiesta();
        $statoRichiesta->setCodice('PRE_INVIATA_PA');
        $richiesta->setStato($statoRichiesta);

        $richiestaProtocollo->setRichiesta($richiesta);
        $richiesta->addRichiesteProtocollo($richiestaProtocollo);
        $procedura->addRichieste($richiesta);
        $documento = new RichiestaProtocolloDocumento();
        $documento->setRichiestaProtocollo($richiestaProtocollo);

        $atc = new AttuazioneControlloRichiesta();
        $atc->setRichiesta($richiesta);
        $richiesta->setAttuazioneControllo($atc);

        $pagamento = new Pagamento();
        $pagamento->setAttuazioneControlloRichiesta($atc);
        $atc->addPagamenti($pagamento);

        return $documento;
    }

    public function testCaricaUnNuovoDocumento() {
        $documento = $this->createRichiestaProtocolloDocumento(2);
        /** @var RichiestaProtocolloFinanziamento $richiestaProtocollo */
        $richiestaProtocollo = $documento->getRichiestaProtocollo();
        $richiesta = $richiestaProtocollo->getRichiesta();
        $idProtocollo = $richiesta->getId();
        $documentiCaricati = [];
        $documentiDaCaricare = [$documento];
        $sottofascicolo = 12345678;
        $datiDocumento = 1;

        $findByValues = $this->onConsecutiveCalls($documentiDaCaricare, $documentiCaricati);

        $this->integrazioneDocerService->expects($this->once())->method('caricaAllegato')->willReturn(12456);
        $this->integrazioneDocerService->expects($this->once())->method('definisciUnitaDocumentale')->willReturn(9898);
        $this->integrazioneDocerService->expects($this->once())->method('creaFascicolo')->willReturn($sottofascicolo);
        $this->integrazioneDocerService->expects($this->once())->method('protocollaUnitaDocumentale')->willReturn($datiDocumento);
        $tokenStorage = new TokenStorage();
        /** @var TokenInterface $token */
        $token = $this->createMock(TokenInterface::class);
        $this->tokenStorage->setToken($token);
        $token->method('getUser')->willReturn(new Utente());

        $this->tokenStorage->setToken($token);

        /** @var RichiestaProtocolloFinanziamento $richiestaProtocollo */
        $richiestaProtocollo = $documento->getRichiestaProtocollo();
        $proponente = $richiestaProtocollo->getRichiesta()->getMandatario();
        $messaggi = $this->esaminaDocumenti($documentiDaCaricare, $proponente, $findByValues);
        // var_dump($messaggi);
        $this->assertNotContains(['messaggio' => "Nessun allegato associato alla richiesta di protocollo [$idProtocollo]"], $messaggi);
        $this->assertContains(['messaggio' => "Caricato documento allegato: <span style='color:blue'>12456</span>"], $messaggi);
        $this->assertContains(['msg_fase' => "Fase 3: creazione unita' documentale..."], $messaggi);
        $this->assertContains(['messaggio' => "Creata unita' documentale: <span style='color:blue'>" . $idProtocollo . "</span>"], $messaggi);
        $this->assertContains(['msg_fase' => "Fase 4: fascicolazione..."], $messaggi);
        $this->assertContains(['messaggio' => "Creato sottofascicolo: <span style='color:blue'>" . $sottofascicolo . "</span>"], $messaggi);
        $this->assertContains(['msg_fase' => "Fase 5: protocollazione..."], $messaggi);

        $this->assertContains(['msg_fase' => "Fase 6: post protocollazione..."], $messaggi);
        $this->assertContains(['messaggio' => "Dati correttamente settati per la post protocollazione"], $messaggi);
    }

    /**
     * @return DocERCronjobService
     * @param mixed $codiceProcesso
     */
    protected function createService($codiceProcesso) {
        return new DocERCronjobService(
            $codiceProcesso,
            $this->doctrine,
            $this->docerLogger,
            $this->container,
            $this->integrazioneDocerService,
            $this->registrazioneDocerService
        );
    }
}
