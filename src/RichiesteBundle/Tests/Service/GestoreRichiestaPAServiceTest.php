<?php

namespace RichiesteBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use RichiesteBundle\Service\GestoreRichiestaPAService;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Monolog\Logger;
use SfingeBundle\Entity\ProceduraPA;
use RichiesteBundle\Entity\Richiesta;

class GestoreRichiestaPAServiceTest extends TestCase
{
    /**
     * @var Container
     */
    protected $container;
    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    protected $logger;
    /**
     * @var GestoreRichiestaPAService
     */
    protected $service;

    public function setUp()
    {
        $em = $this->createMock(EntityManager::class);

        $logger = $this->createMock(Logger::class);
        $logger->method('debug')
        ->willReturn(null);

        $container = $this->createMock(Container::class);
        $container->method('get')
        ->will(
            $this->returnValueMap(
                array(
                    array('container', Container::EXCEPTION_ON_INVALID_REFERENCE, $container),
                    array('logger', Container::EXCEPTION_ON_INVALID_REFERENCE, $logger),
                    array('doctrine.orm.entity_manager', Container::EXCEPTION_ON_INVALID_REFERENCE, $em),
        )));

        $this->service = new GestoreRichiestaPAService($container);
    }

    /**
     * @dataProvider getValuesIstanza
     */
    public function testInstanzaGestore($id, $nomeClasse)
    {
        $procedura = new ProceduraPA();
        $procedura->setId($id);
        $richiesta = new Richiesta();
        $richiesta->setProcedura($procedura);
        $gestore = $this->service->getGestore($richiesta);
        $this->assertEquals($nomeClasse, get_class($gestore));
    }

    public function getValuesIstanza(){
        return array(
            array('AA','RichiesteBundle\GestoriRichiestePA\GestoreRichiestePA_Base'),
            array(60, 'RichiesteBundle\GestoriRichiestePA\GestoreRichiestePA_60')
        );
    }
}
