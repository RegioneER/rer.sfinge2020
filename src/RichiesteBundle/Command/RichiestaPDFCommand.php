<?php

namespace RichiesteBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\HttpFoundation\Session\Session;
use BaseBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use BaseBundle\Entity\StatoRichiesta;

class RichiestaPDFCommand extends ContainerAwareCommand {
    /**
     * @var EntityManagerInterface
     */
    protected $em;
    /**
     * @var ContainerInterface
     */
    protected $container;
    protected $logger;

    /**
     * @var Session
     */
    protected $session;

    protected function configure() {
        $this
            ->setName('sfinge:richiesta:pdf')
            ->setDescription('Importa le esportazioni IGRUE')
            ->addArgument('id_richiesta', InputArgument::REQUIRED, 'ID richiesta');
    }

    public function __construct($name = null) {
        parent::__construct($name);
    }

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $this->container = $this->getContainer();
        $this->em = $this->container->get('doctrine')->getManager();
        $this->logger = $this->container->get('monolog.logger.schema31');
        $this->container->get('translator')->setLocale('it_IT');

        $sessionStorage = new MockArraySessionStorage();
        /* @var Session $session */
        $this->session = new Session($sessionStorage);
        $this->container->set('session', $this->session);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $id_richiesta = $input->getArgument('id_richiesta');
        $this->setRichiestaId($id_richiesta);
        /** @var Richiesta $richiesta */
        $richiesta = $this->em->getRepository(Richiesta::class)->find($id_richiesta);
        $stato = $this->em->getRepository(StatoRichiesta::class)->findOneBy(['codice' => StatoRichiesta::PRE_INSERITA]);
        $richiesta->setStato($stato);
        $this->setSoggetto($richiesta);

        $gestoreRichiesta = $this->container->get('gestore_richieste')->getGestore($richiesta->getProcedura());

        $pdf = $gestoreRichiesta->generaPdf($richiesta, false, false);

        $output->write($pdf, false, Output::OUTPUT_RAW);
    }

    private function setRichiestaId($id_richiesta): void {
        /** @var RequestStack $stack */
        $stack = $this->container->get('request_stack');

        $request = new Request([
            'id_richiesta' => $id_richiesta,
        ]);
        $request->setSession($this->session);
        
        $stack->push($request);
    }

    private function setSoggetto(Richiesta $richiesta): void {
        $soggetto = $richiesta->getSoggetto();

        $this->session->set(BaseController::SESSIONE_SOGGETTO, $soggetto);
    }
}
