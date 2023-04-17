<?php

namespace RichiesteBundle\Tests\GestoriRichiestePA\SezioniRiepilogo;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\DatiProgetto;
use Doctrine\ORM\EntityManager;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\Container;
use RichiesteBundle\GestoriRichiestePA\Riepilogo\Riepilogo_Base;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Validation;
use SfingeBundle\Entity\Bando;
class DatiProgettoTest extends TestCase{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @dataProvider generateValidaTestSet
     */
    public function testValida(Richiesta $richiesta, $esito){

        $em = $this->createMock(EntityManager::class);
        $doctrine = $this->createMock(Registry::class);
        $doctrine->method('getManager')
            ->willReturn($em);

        $logger = $this->createMock(Logger::class);
        $logger->method('debug')
            ->willReturn(null);

        $router = $this->createMock(Router::class);
        $router->method('generate')
        ->willReturn(NULL);

        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();

        $container = $this->createMock(Container::class);
        $container->method('get')
        ->will($this->returnValueMap(array(
            array('logger', Container::EXCEPTION_ON_INVALID_REFERENCE, $logger),
            array('doctrine', Container::EXCEPTION_ON_INVALID_REFERENCE, $doctrine),
            array('router', Container::EXCEPTION_ON_INVALID_REFERENCE, $router),
            array('validator', Container::EXCEPTION_ON_INVALID_REFERENCE, $validator),
        )));

        $riepilogoMock = $this->createMock(Riepilogo_Base::class);
        $riepilogoMock->method('getRichiesta')->willReturn($richiesta);


        $sezione = new DatiProgetto($container, $riepilogoMock);
        $sezione->valida();
        $this->assertSame($esito, $sezione->isValido());
    }

    public function generateValidaTestSet(){
        return array(
            array(self::generateRichiesta(null, null), false),
            array(self::generateRichiesta('titolo', NULL), false),
            array(self::generateRichiesta(null, 'abstract'), false),
            array(self::generateRichiesta('c', 'abstartc'), false),
            array(self::generateRichiesta('titolo', 'abstract'), true),
        );
    }

    /**
     * @return Richiesta
     */
    private static function generateRichiesta( $titolo, $abstract){
        $procedura = new Bando();
        $r = new Richiesta();
        $r->setProcedura($procedura);
        $r->setTitolo($titolo)
        ->setAbstract($abstract);
        return $r;
    }
}