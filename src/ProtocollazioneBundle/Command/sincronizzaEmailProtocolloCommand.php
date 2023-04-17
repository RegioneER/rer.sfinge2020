<?php

namespace ProtocollazioneBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of sincronizzaEmailProtocolloCommand
 *
 * @author gdisparti
 */
class sincronizzaEmailProtocolloCommand extends ContainerAwareCommand {

	protected function configure() {
		$this->setName('egrammata:sincronizzaEmailProtocollo')->setDescription('');
		//$this->addArgument('id_risorsa', InputArgument::REQUIRED, 'tipo di risorsa da trasmettere');
	}	
	
	protected function execute(InputInterface $input, OutputInterface $output) {
		
		$container = $this->getContainer();
		
		// memory limit
		$memory_limit = 0.90 * $this->return_bytes(ini_get('memory_limit'));
		
		// dimensione lotto
		$lotto = 20;
		
		// numero massimo di iterazioni del while
		$numeroMassimoIterazioni = 20;
		
		$egrammata = $this->getContainer()->get('egrammata_ws');
		$doctrine = $container->get('doctrine');
		$em = $doctrine->getManager();
		
		$repo = $em->getRepository("ProtocollazioneBundle\Entity\EmailProtocollo");
		
		$output->writeln("<comment>********** Inizio procedura di sincronizzazione EmailProtocollo **********</comment>");
		
		$emailProcessate = 0;
		$emailProcessate_OK = 0;
		$emailProcessate_KO = 0;
		
		$memoria_esaurita = false;
		$ids = array();
		$numeroIterazione = 0;
		
		do {
			$numeroIterazione++;
			
			$emailProtocolloDaSincronizzare = $repo->getEmailProtocolloDaSincronizzare($lotto, $ids);
			
			foreach ($emailProtocolloDaSincronizzare as $emailProtocollo) {
				
				try {
					$ids[] = $emailProtocollo->getId();
					$emailProcessate++;
					
					$messaggio = "EmailProtocollo con id {$emailProtocollo->getId()} processata";
					
					$resp = $egrammata->aggiornaEmailProtocollo($emailProtocollo);
					if($resp){
						$emailProcessate_OK++;
					}else{
						$emailProcessate_KO++;	
						$messaggio .= " con errore";
					}
										
					$output->writeln("<comment>$messaggio</comment>");
					
				} catch(\Exception $e) {
					$output->writeln("<comment>Eccezione durante la procedura di sincronizzazione EmailProtocollo con id {$emailProtocollo->getId()}: ".$e->getMessage()."</comment>");
				}
				
				if (\memory_get_usage() > $memory_limit) {
					$output->writeln("<comment>Memoria esaurita</comment>");
					$memoria_esaurita = true;
					break;
				}				
			}
			
		} while (count($emailProtocolloDaSincronizzare) > 0 && !$memoria_esaurita && $numeroIterazione <= $numeroMassimoIterazioni);
		
		$output->writeln("<comment>Totale EmailProtocollo processate: {$emailProcessate} di cui OK: {$emailProcessate_OK} e KO: {$emailProcessate_KO}</comment>");

		$output->writeln("<comment>********** Procedura di sincronizzazione EmailProtocollo completata **********</comment>");
	}
	
    protected function return_bytes($val) {
        if($val < 0){
            $val = '768M';
        }
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
		$val = (int)$val;
        switch($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    } 

}