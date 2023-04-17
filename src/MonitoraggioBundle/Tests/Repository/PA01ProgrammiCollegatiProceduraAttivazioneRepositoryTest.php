<?php

namespace MonitoraggioBundle\Tests\Repository;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneTavole;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneProcedura;
use MonitoraggioBundle\Repository\PA01ProgrammiCollegatiProceduraAttivazioneRepository;

class PA01ProgrammiCollegatiProceduraAttivazioneRepositoryTest extends KernelTestCase {
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        self::bootKernel();
        $this->em = static::$kernel->getContainer()
                ->get('doctrine')
                ->getManager();
        $this->container = static::$kernel->getContainer();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown() {
        parent::tearDown();

        $this->em->close();
        $this->em = null; // avoid memory leaks
    }

    public function testIsEsportabile():void{
        $procedura = $this->em->getRepository('SfingeBundle:Bando')->find(15);
        $configurazione = new MonitoraggioConfigurazioneEsportazioneProcedura($procedura);
        $tavola = new MonitoraggioConfigurazioneEsportazioneTavole($configurazione);

        /** @var PA01ProgrammiCollegatiProceduraAttivazioneRepository $repo */
        $repo = $this->em->getRepository('MonitoraggioBundle:PA01ProgrammiCollegatiProceduraAttivazione');
        $res = $repo->isEsportabile($tavola);

        $this->assertTrue(\is_bool($res));
    }
}
