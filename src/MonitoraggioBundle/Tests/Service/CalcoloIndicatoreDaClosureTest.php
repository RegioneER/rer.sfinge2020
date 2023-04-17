<?php

namespace MonitoraggioBundle\Test\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\Entity\Richiesta;
use AttuazioneControlloBundle\Service\ICalcolaValoreRealizzatoIndicatoreOutput;

class CalcoloIndicatoreDaClosureTest extends TestCase
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var Richiesta
     */
    public $richiesta;

    /**
     * @var CalcoloIndicatoreDaClosure
     */
    private $object;

    public function setUp()
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->richiesta = new Richiesta();
        $this->object = new CalcoloIndicatoreDaClosure($this->container, $this->richiesta);
    }
    public function testInvoke()
    {
        $test = $this;
        $closure = function($richiesta) use($test){
            $test->assertEquals($test->richiesta, $richiesta);
            return 3;
        };
        
        $this->object->setClosure($closure);
        $res = $this->object->getValore();

        $this->assertEquals(3, $res);
    }
}