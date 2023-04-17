<?php

namespace MonitoraggioBundle\Command;

/**
 * @author lfontana
 */

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MonitoraggioBundle\Entity\MonitoraggioEsportazione;
use MonitoraggioBundle\Entity\MonitoraggioEsportazioneLogFase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
class MonitoraggioImportaCommand extends ContainerAwareCommand
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;
    /**
     * @var ContainerInterface
     */
    protected $container;
    protected $logger;
    protected $fileName;

    protected function configure()
    {
        $this
            ->setName('sfinge:monitoraggio:importazione')
            ->setDescription('Importa le esportazioni IGRUE')
            ->addArgument('id_importazione', InputArgument::REQUIRED, 'ID Importazione');
    }

    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', '512M');
        $this->container = $this->getContainer();
        $this->em = $this->container->get('doctrine')->getManager();
        $this->logger = $this->container->get('monolog.logger.schema31');
        $this->container->get('translator')->setLocale('it_IT');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var MonitoraggioEsportazione $esportazione */
        $esportazione = $this->em->getRepository(MonitoraggioEsportazione::class)->find($input->getArgument('id_importazione'));
        $service = $this->container->get('gestore_importazione_igrue');
        $messaggi = $service->elaboraImportazione($esportazione, false);
        foreach ($messaggi as $messaggio) {
            $output->writeln('<info>'.$messaggio.'</info>');
        }
    }


}
