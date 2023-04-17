<?php

namespace ProtocollazioneBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of cancellaLogServiceCommand
 *
 * @author gdisparti
 */
class cancellaLogServiceCommand extends ContainerAwareCommand {

	protected function configure() {
		$this->setName('logService:cancella')->setDescription('');
	}	
	
	protected function execute(InputInterface $input, OutputInterface $output) {
		
		$container = $this->getContainer();

		$doctrine = $container->get('doctrine');
		$em = $doctrine->getManager();
		$connection = $em->getConnection();
		
		$now = new \DateTime();
		$limit = $now->modify('-3 months');
		$limit = $limit->format('Y-m-d 00:00:00');
		
		$output->writeln("<comment>********** Inizio procedura di cancellazione log_service **********</comment>");
		
		$sql = 'DELETE FROM log_service where log_time < ?';
		
		/*@var $connection \Doctrine\DBAL\Connection */
		try{
			$statement = $connection->prepare($sql);
			$statement->bindParam(1, $limit);
			$affected = $statement->execute();
			
			if($affected == true){
				$output->writeln("<info>{$statement->rowCount()} righe cancellate</info>");
			}else{
				$output->writeln("<error>Si è verificato un errore durante la cancellazione</error>");
			}
			
		}catch(\Exception $e){
			$output->writeln("<error>Si è verificato un errore {$e->getMessage()}</error>");
		}
		
		$output->writeln("<comment>********** Fine Procedura di cancellazione log_service **********</comment>");
	}

}