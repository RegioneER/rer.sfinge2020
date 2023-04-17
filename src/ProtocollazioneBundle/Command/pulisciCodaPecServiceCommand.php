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
 * @author vdamico
 */
class pulisciCodaPecServiceCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('egrammata:pulisciCodaPec')->setDescription('');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $container = $this->getContainer();

        $doctrine = $container->get('doctrine');
        $em = $doctrine->getManager();
        $repo = $em->getRepository("ProtocollazioneBundle\Entity\EmailProtocollo");


        $output->writeln("<comment>********** Inizio procedura di pulizia coda **********</comment>");
        $daNonInviare = $repo->getEmailProtocolloDaNonInviare();
        $daNonSincro = $repo->getEmailProtocolloDaNonSincronizzare();

        $now = new \DateTime();
        $limit = $now->modify('-1 months');
        $limitS = $limit->format('Y-m-d 00:00:00');

        /* @var $connection \Doctrine\DBAL\Connection */
        try {
            $em->beginTransaction();
            if (count($daNonInviare) > 0) {
                foreach ($daNonInviare as $pecInvio) {
                    $pecInvio->setStato(\ProtocollazioneBundle\Entity\EmailProtocollo::NON_INVIABILE);
                    $output->writeln("<comment>Bloccato invio pec id: {$pecInvio->getId()}</comment>");
                    $em->flush($pecInvio);
                }
            } else {
                $output->writeln("<comment>Non ci sono pec sospese precedenti al $limitS</comment>");
            }
            if (count($daNonSincro) > 0) {
                foreach ($daNonSincro as $pecSincro) {
                    $pecSincro->setStato(\ProtocollazioneBundle\Entity\EmailProtocollo::NESSUNA_NOTIFICA);
                    $output->writeln("<comment>Bloccato notifiche pec id: {$pecSincro->getId()}</comment>");
                    $em->flush($pecSincro);
                }
            } else {
                $output->writeln("<comment>Non ci sono notifiche sospese precedenti al $limitS</comment>");
            }
            $em->commit();
        } catch (\Exception $e) {
            $output->writeln("<error>Si è verificato un errore {$e->getMessage()}</error>");
        }

        $output->writeln("<comment>********** Fine Procedura di procedura di pulizia coda **********</comment>");
    }

}
