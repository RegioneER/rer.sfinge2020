<?php

namespace MonitoraggioBundle\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use PHPUnit_Framework_Constraint_IsType as PHPUnit_IsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use PHPUnit\Framework\Constraint\IsType;

/**
 * @author lfontana
 */
class GestoreTabelleContestoBaseTest extends KernelTestCase {
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ContainerInterface
     */
    private $container;

    private $tabelleContesto;

    protected $gestoreTabelleContestoServiceb;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        self::bootKernel();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->container = static::$kernel->getContainer();
        $this->tabelleContesto = $this->em->getRepository('MonitoraggioBundle\Entity\ElencotabelleContesto')->findAll();
        $this->gestoreTabelleContestoServiceb = $this->container->get('gestore_tabelle_contesto');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown() {
        parent::tearDown();

        $this->em->close();
        $this->em = null; // avoid memory leaks
    }

    public static function invokeMethod(&$object, $methodName, array $parameters = []) {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    public function testPresenzaFormType() {
        foreach ($this->tabelleContesto as $tabella) {
            $gestore = $this->gestoreTabelleContestoServiceb->getGestore($tabella);
            $res = self::invokeMethod($gestore, 'getEntityType');
            $this->assertTrue(class_exists($res), 'Classe non implementata: ' . $res);
        }
    }

    public function testPresenzaEntity() {
        foreach ($this->tabelleContesto as $tabella) {
            $gestore = $this->gestoreTabelleContestoServiceb->getGestore($tabella);
            $res = self::invokeMethod($gestore, 'getEntity');
            $this->assertTrue(class_exists($res), 'Classe non implementata: ' . $res);
        }
    }

    public function testTwigInserimento() {
        foreach ($this->tabelleContesto as $tabella) {
            $gestore = $this->gestoreTabelleContestoServiceb->getGestore($tabella);
            $res = self::invokeMethod($gestore, 'getFormInsertTwig');
            $this->assertTrue($this->container->get('templating')->exists($res), 'Twig non implementato: ' . $res);
        }
    }

    public function testTwigModifica() {
        foreach ($this->tabelleContesto as $tabella) {
            $gestore = $this->gestoreTabelleContestoServiceb->getGestore($tabella);
            $res = self::invokeMethod($gestore, 'getFormEditTwig');
            $this->assertTrue($this->container->get('templating')->exists($res), 'Twig non implementato: ' . $res);
        }
    }

    public function testTwigElenco() {
        $tabelleContesto = $this->em->getRepository('MonitoraggioBundle\Entity\ElencotabelleContesto')->findAll();
        foreach ($tabelleContesto as $tabella) {
            $gestore = $this->gestoreTabelleContestoServiceb->getGestore($tabella);
            $res = self::invokeMethod($gestore, 'getTwig');
            $this->assertTrue($this->container->get('templating')->exists($res), 'Twig non implementato: ' . $res);
        }
    }

    public function testPresenzaFormTypeRicerca() {
        foreach ($this->tabelleContesto as $tabella) {
            $gestore = $this->gestoreTabelleContestoServiceb->getGestore($tabella);
            $res = self::invokeMethod($gestore, 'getClasseFormModelView');
            $this->assertTrue(class_exists($res), 'Classe non implementata: ' . $res);
        }
    }

    public function testPresenzaRepository() {
        $reader = new \Doctrine\Common\Annotations\AnnotationReader();
        foreach ($this->tabelleContesto as $tabella) {
            $gestore = $this->gestoreTabelleContestoServiceb->getGestore($tabella);
            $entity = new \ReflectionClass(self::invokeMethod($gestore, 'getEntity'));
            $classAnnotations = $reader->getClassAnnotations($entity);

            $flagRepository = false;
            foreach ($classAnnotations as $annot) {
                if ($annot instanceof \Doctrine\ORM\Mapping\Entity && !is_null($annot->repositoryClass)) {
                    $flagRepository = $flagRepository || true;
                    $repository = $annot->repositoryClass;

                    $this->assertTrue(class_exists($repository), 'Classe non implementata: ' . $annot->repositoryClass . ' in ' . $entity->name);

                    $repositoryClass = new \ReflectionClass($repository);
                    $this->assertTrue($repositoryClass->isSubclassOf('Doctrine\ORM\EntityRepository'), 'Il repository ' . $annot->repositoryClass . ' Non estende EntityRepository');
                    $method = $gestore->getOggettoFormModelView()->getNomeMetodoRepository();
                    $this->assertTrue($repositoryClass->hasMethod($method), 'La classe ' . $annot->repositoryClass . ' non implementa il metodo ' . $method);
                }
            }
            $this->assertTrue($flagRepository, 'Repository non definito in ' . $entity->name);

            // Testa il funzionamento del repository
            $ricerca = $gestore->getOggettoFormModelView();
            $metodo = $ricerca->getNomeMetodoRepository();
            $risultato = $this->em->getRepository($ricerca->getNomeRepository())->$metodo($ricerca);
            $this->assertInternalType(IsType::TYPE_OBJECT, $risultato, 'Il risultato tornato dal repository non è un array');
        }
    }
}
