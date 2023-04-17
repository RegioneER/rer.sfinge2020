<?php

namespace MonitoraggioBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MonitoraggioBundle\Entity\MonitoraggioEsportazione;
use MonitoraggioBundle\Entity\MonitoraggioEsportazioneLogFase;

/**
 * @author lfontana
 */
class MonitoraggioCreaEsportazioneCommand extends ContainerAwareCommand
{
    protected $em;
    protected $logger;

    protected function configure()
    {
        $this
            ->setName('sfinge:monitoraggio:crea-esportazione')
            ->setDescription('Crea nuova IGRUE');
    }

    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    /**
     * 
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $this->em = $container->get('doctrine')->getManager();
        $this->logger = $container->get('monolog.logger.schema31');
        $container->get('translator')->setLocale('it_IT');
    }
    
    /**
     * 
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
       $esportazione = new MonitoraggioEsportazione();
       $fase = new MonitoraggioEsportazioneLogFase($esportazione);
       $esportazione->addFasi($fase);
       $this->em->persist($esportazione);
       $this->em->flush();
       $output->writeln('<info>Nuova sportazione creata con ID '.$esportazione->getId().'</info>');
    }


}
