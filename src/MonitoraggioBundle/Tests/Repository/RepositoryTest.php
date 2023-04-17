<?php
namespace MonitoraggioBundle\Tests\Repository;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Repository\MonitoraggioEsportazioneRepository;
use Doctrine\ORM\Internal\Hydration\IterableResult;

class RepositoryTest extends KernelTestCase{

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var MonitoraggioConfigurazioneEsportazioneRichiesta
     */
    private $configurazione;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        self::bootKernel();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->container = static::$kernel->getContainer();

        $this->configurazione = $this->em->createQueryBuilder()
        ->select('configurazione')
        ->from('MonitoraggioBundle:MonitoraggioConfigurazioneEsportazioneRichiesta', 'configurazione')
        ->setMaxResults(1)
        ->getQuery()
        ->getSingleResult();
    }
    
    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->em->close();
        $this->em = null; // avoid memory leaks
    }

    /**
     * @dataProvider getListStruttureRepository
     */
    public function testMethodIsEsportabileWorkRichiestaRepository(string $repository){
        $res = $this->em->getRepository("MonitoraggioBundle:$repository")->isEsportabile($this->configurazione);
        $this->assertTrue($res, $repository);
        $this->assertInternalType('bool', $res, $repository);
    }

    public function getListStruttureRepository(){
        return array_map(function($repo){
            return array($repo);
        },MonitoraggioEsportazioneRepository::$ENTITY_REPOSITORY);
    }

     /**
     * @dataProvider getListStruttureRepository
     */
    public function testFindAllEsportabili(string $repository)
    {
        $res = $this->em->getRepository("MonitoraggioBundle:$repository")->findAllEsportabili(new \DateTime('0000-00-00'));
        $this->assertInstanceOf(IterableResult::class, $res);
    }
}