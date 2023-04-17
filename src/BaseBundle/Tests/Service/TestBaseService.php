<?php

namespace BaseBundle\Tests\Service;

use Symfony\Component\DependencyInjection\Container;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Monolog\Logger;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RequestStack;
use PaginaBundle\Services\Pagina;
use Doctrine\DBAL\Connection;
use BaseBundle\Service\StatoService;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenStorage\SessionTokenStorage;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\DefaultCsrfProvider;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\HttpFoundation\Response;
use RichiesteBundle\Validator\Constraints\ValidaDatiGeneraliValidator;
use Symfony\Bundle\FrameworkBundle\Validator\ConstraintValidatorFactory;
use Symfony\Component\Security\Csrf\CsrfToken;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use MonitoraggioBundle\Service\GestoreIndicatoreService;
use MonitoraggioBundle\Service\IGestoreIndicatoreOutput;

class TestBaseService extends TestCase {
    /**
     * @var EntityManager|MockObject
     */
    protected $em;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Logger|MockObject
     */
    protected $logger;

    /**
     * @var Router|MockObject
     */
    protected $router;

    /**
     * @var Registry|MockObject
     */
    protected $doctrine;

    /**
     * @var FormFactory|MockObject
     */
    protected $formFactory;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var Pagina|MockObject
     */
    protected $paginaService;

    /**
     * @var Connection|MockObject
     */
    protected $connection;

    /**
     * @var StatoService|MockObject
     */
    protected $statiSfinge;

    /**
     * @var FlashBag
     */
    protected $flashBag;

    /**
     * @var Session|MockObject
     */
    protected $session;

    /**
     * @var TwigEngine|MockObject
     */
    protected $templating;

    protected $indicatoriService;


    protected function setUp() {
        $this->connection = $this->createMock(Connection::class);
        $this->em = $this->createMock(EntityManager::class);
        $this->em->method('getConnection')
        ->willReturn($this->connection);

        $this->doctrine = $this->createMock(Registry::class);
        $this->doctrine->method('getManager')
            ->willReturn($this->em);

        $this->logger = $this->createMock(Logger::class);
        $this->router = $this->createMock(Router::class);
        $this->container = new Container();//$this->createMock(Container::class);
        $this->formFactory = $this->createMock(FormFactory::class);
        $this->requestStack = new RequestStack();
        $this->paginaService = $this->createMock(Pagina::class);
        $this->statiSfinge = $this->createMock(StatoService::class);
        $this->flashBag = new FlashBag();
        $this->templating = $this->createMock(TwigEngine::class);

        $this->session = $this->createMock(Session::class);
        $this->session
            ->method('getFlashBag')
            ->willReturn($this->flashBag);

        $tokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $indicatoriFactory = $this->createMock(GestoreIndicatoreService::class);
        $this->indicatoriService = $this->createMock(IGestoreIndicatoreOutput::class);
        $indicatoriFactory->method('getGestore')->willReturn($this->indicatoriService);

        $this->container->set('logger', $this->logger);
        $this->container->set('doctrine', $this->doctrine);
        $this->container->set('doctrine.orm.entity_manager', $this->em);
        $this->container->set('form.factory', $this->formFactory);
        $this->container->set('request_stack', $this->requestStack);
        $this->container->set('pagina', $this->paginaService);
        $this->container->set('sfinge.stati', $this->statiSfinge);
        $this->container->set('session', $this->session);
        $this->container->set('templating', $this->templating);
        $this->container->set('router', $this->router);
        $this->container->set('security.authorization_checker', $this->createMock(AuthorizationCheckerInterface::class));
        $this->container->set('security.csrf.token_manager', $tokenManager);
        $this->container->set('security.token_storage', new TokenStorage());
        $this->container->set('validator.valida_dati_generali', new ValidaDatiGeneraliValidator($this->container));
        $this->container->set('monitoraggio.indicatori_output', $indicatoriFactory);
        
        $this->templating->method('renderResponse')->willReturn( new Response());

        $this->aggiungiServiziValidazione();
    }

    function aggiungiServiziValidazione(){        
        $validators = [
            'valida_dati_generali' => $this->container->get('validator.valida_dati_generali'),
        ];
        $constraintValidatorFactory = new ConstraintValidatorFactory($this->container, $validators);
        $validator = Validation::createValidatorBuilder()
        ->setConstraintValidatorFactory($constraintValidatorFactory)
        // ->addMethodMapping('loadValidatorMetadata')
        ->enableAnnotationMapping()
        ->getValidator();
        $this->container->set('validator', $validator);
    }
}
