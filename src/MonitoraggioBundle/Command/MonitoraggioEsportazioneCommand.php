<?php
namespace MonitoraggioBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use MonitoraggioBundle\Entity\MonitoraggioEsportazioneLogFase;
use Doctrine\ORM\EntityManagerInterface;
/**
 * @author lfontana
 */
class MonitoraggioEsportazioneCommand extends ContainerAwareCommand
{
    /** @var EntityManagerInterface */
    protected $em;
    /** @var int */
    protected $monitoraggio_esportazione_id;

    protected function configure()
    {
        $this
            ->setName('sfinge:monitoraggio:esportazione')
            ->setDescription('Analizza le strutture oggetto di esportazione')
            ->addArgument('monitoraggio_esportazione_id', InputArgument::REQUIRED, 'Monitoraggio esportazione ID');
    }

    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    
    protected function initialize(InputInterface $input, OutputInterface $output) {
        ini_set("memory_limit", "1.5G");
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->em->getFilters()->disable('softdeleteable');
        $this->monitoraggio_esportazione_id = $input->getArgument('monitoraggio_esportazione_id');
        $this->getContainer()->get('translator')->setLocale('it_IT');
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln('<info>Avvio esportazione '.$this->monitoraggio_esportazione_id.'</info>');
        $esportazione = $this->em->getRepository('MonitoraggioBundle:MonitoraggioEsportazione')->findOneById($this->monitoraggio_esportazione_id);
        if (is_null($esportazione)) {
            $output->writeln('<info>L\'ID indicato in input non è associato ad alcuna esportazione</info>');
            return;
        }

        $connection = $this->em->getConnection();
        try {
            $connection->beginTransaction();

            $esportazioneConfRichieste = $this->em->getRepository('MonitoraggioBundle:MonitoraggioEsportazione')->findRichieste($esportazione);
            
            $esportazioneConfProcedure = $this->em->getRepository('MonitoraggioBundle:MonitoraggioEsportazione')->findProcedure($esportazione);
            foreach ($esportazioneConfProcedure as $esportazioneConfProcedura) {
                $esportazione->addMonitoraggioConfigurazione($esportazioneConfProcedura);
                $this->em->persist($esportazioneConfProcedura);
                $this->em->flush($esportazioneConfProcedura);
            }
            $esportazioneConfTrasferimenti = $this->em->getRepository('MonitoraggioBundle:MonitoraggioEsportazione')->findTrasferimenti($esportazione);
            foreach ($esportazioneConfTrasferimenti as $esportazioneConfTrasferimento) {
                $esportazione->addMonitoraggioConfigurazione($esportazioneConfTrasferimento);
                $this->em->persist($esportazioneConfTrasferimento);
                $this->em->flush($esportazioneConfTrasferimento);
            }

            $fase_inizializzazione = $esportazione->getLastFase();
            if (!$fase_inizializzazione) {
                throw new \Exception('Non è stata definita una fase per l\'esportazione');
            }
            $fase_inizializzazione->setDataFine(new \DateTime());
            $esportazione->updateFase($fase_inizializzazione);
            $this->em->persist($fase_inizializzazione);
            $this->em->flush($fase_inizializzazione);

            if (count($esportazioneConfRichieste) + count($esportazioneConfProcedure) + count($esportazioneConfTrasferimenti) > 0) {
                $this->em->persist($esportazione);
                $this->em->flush($esportazione);
                $connection->commit();
                $output->writeln('<info>Inizializzazione effettuata con successo</info>');
            } else {
                $fase_ND = new MonitoraggioEsportazioneLogFase($esportazione);
                $fase_ND->setFase(MonitoraggioEsportazioneLogFase::STATO_ND);
                $fase_ND->setDataInizio(new \DateTime());
                $fase_ND->setDataFine(new \DateTime());
                $esportazione->addFasi($fase_ND);

                $this->em->persist($esportazione);
                $this->em->flush($esportazione);
                $connection->commit();

                $output->writeln('<info>Nessuna esportazione da effettuare</info>');
                return;
            }
        } catch (\Exception $e) {
            if($connection->isTransactionActive()){
                $connection->rollBack();
            }
            $output->writeln('<error>Errore durante l\'inizializzazione dell\'esportazione</error>');
            throw $e;
            ;
        }

        // SCARICO
        $command = $this->getApplication()->find('sfinge:monitoraggio:scarico');

        $arguments = array(
            'command' => 'sfinge:monitoraggio:scarico',
            'monitoraggio_esportazione_id' => $this->monitoraggio_esportazione_id,
        );

        $greetInput = new \Symfony\Component\Console\Input\ArrayInput($arguments);
        $deb = $command->run($greetInput, $output);

        if ($deb == 0) {
            $output->writeln('<info>Scarico effettuato con successo</info>');
        }
        else {
            $output->writeln('<error>Errore durante la fase di scarico</error>' . $deb);
        }

        return;
    }

}
