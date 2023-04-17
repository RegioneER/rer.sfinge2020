<?php

namespace MonitoraggioBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MonitoraggioBundle\Entity\MonitoraggioEsportazioneLogFase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneErrore;
use MonitoraggioBundle\Exception\IgrueException;
use Doctrine\Common\Collections\ArrayCollection;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneTavole;

class MonitoraggioScaricoCommand extends ContainerAwareCommand {
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var int
     */
    protected $monitoraggio_esportazione_id;

    /**
     * @var ContainerInterface
     */
    protected $container;

    protected function configure() {
        $this
            ->setName('sfinge:monitoraggio:scarico')
            ->setDescription('Esporta le strutture per IGRUE')
            ->addArgument('monitoraggio_esportazione_id', InputArgument::REQUIRED, 'Monitoraggio esportazione ID');
    }

    public function __construct($name = null) {
        parent::__construct($name);
    }

    protected function initialize(InputInterface $input, OutputInterface $output) {
        ini_set('memory_limit', '8192M');
        $this->container = $this->getContainer();
        $this->em = $this->container->get('doctrine')->getManager();

        $this->monitoraggio_esportazione_id = $input->getArgument('monitoraggio_esportazione_id');
        $this->container->get('translator')->setLocale('it_IT');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln('<info>Avvio scarico ' . $this->monitoraggio_esportazione_id . '</info>');
        $esportazione = $this->em->getRepository('MonitoraggioBundle:MonitoraggioEsportazione')->findOneById($this->monitoraggio_esportazione_id);
        if (\is_null($esportazione)) {
            $output->writeln('<info>L\'ID indicato in input non è associato ad alcuna esportazione</info>');

            return 888;
        }
        $scarico = \array_reduce($esportazione->getFasi()->toArray(), function ($carry, MonitoraggioEsportazioneLogFase $fase) {
            if (MonitoraggioEsportazioneLogFase::STATO_SCARICO == $fase->getFase()) {
                return $carry || \is_null($fase->getDataFine());
            }

            return $carry || false;
        }, false);
        if ($scarico) {
            $output->writeln('<error>E\' in corso una esportazione</error>');

            return 999;
        }

        $connection = $this->em->getConnection();
        $filter = $this->em->getFilters()->getEnabledFilters();
        if (array_key_exists('softdeleteable', $filter)) {
            $this->em->getFilters()->disable('softdeleteable');
        }
        $this->resetScarico($esportazione);

        $fase_scarico = new MonitoraggioEsportazioneLogFase($esportazione);
        $fase_scarico->setFase(MonitoraggioEsportazioneLogFase::STATO_SCARICO);
        $fase_scarico->setDataInizio(new \DateTime());
        try {
            $esportazione->addFasi($fase_scarico);
            $this->em->persist($fase_scarico);
            $this->em->flush($fase_scarico);

            $connection->beginTransaction();
            /** @var MonitoraggioConfigurazioneEsportazioneTavole[] $tavole */
            $tavole = $this->em->getRepository('MonitoraggioBundle:MonitoraggioEsportazione')->getAllTavoleByEsportazione($esportazione);
            foreach ($tavole as $tavola) {
                $configurazione = $tavola->getMonitoraggioConfigurazioneEsportazione();
                $elemento = $configurazione->getElemento();
                $strutturaEsportata = $this->eseguiSingolaEsportazione($tavola);

                if ($strutturaEsportata) {
                    if ($strutturaEsportata instanceof \Traversable || is_array($strutturaEsportata)) {
                        foreach ($strutturaEsportata as $singolaStruttura) {
                            $this->em->persist($singolaStruttura);
                            $this->em->flush($singolaStruttura);
                        }
                    } else {
                        $this->em->persist($strutturaEsportata);
                        $this->em->flush($strutturaEsportata);
                    }
                }
            }

            $connection->commit();

            $connection->beginTransaction();

            //controlli incrociati
            $elementiDaCancellare = [];
            foreach ($this->em->getRepository('MonitoraggioBundle:MonitoraggioEsportazione')->getAllTavoleByEsportazione($esportazione) as $tavola) {
                $errors = $this->container->get('validator')->validate($tavola, null, ['monitoraggioCrossControls']);
                if (\count($errors) > 0) {
                    //Procedo a cancellazione strutture che sono collegate al controllo
                    $elementiDaCancellare[] = $tavola;
                }
                foreach ($errors as $errore) {
                    $this->addError($errore->getMessage(), $tavola, $tavola->getMonitoraggioConfigurazioneEsportazione(), $errore->getConstraint()->getCodiceIgrue());
                }
            }

            foreach ($elementiDaCancellare as $elementoDaCancellare) {
                $strutture = $this->em->getRepository('MonitoraggioBundle:MonitoraggioConfigurazioneEsportazioneTavole')->findStruttureByTavola($elementoDaCancellare);
                foreach ($strutture as $struttura) {
                    $this->em->remove($struttura);
                    $this->em->flush($struttura);
                    $this->em->detach($struttura);
                }
            }

            $fase_scarico->setDataFine(new \DateTime());
            $this->em->persist($fase_scarico);
            $this->em->flush($fase_scarico);

            $this->em->persist($esportazione);
            $this->em->flush($esportazione);

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            if ($connection->isConnected()) {
                $this->em->remove($fase_scarico);
                $this->em->flush($fase_scarico);
                $stato_errore = new MonitoraggioEsportazioneLogFase($esportazione, MonitoraggioEsportazioneLogFase::STATO_ERRORE_SCARICO);
                $stato_errore->setDataFine(new \DateTime());
                $esportazione->addFasi($stato_errore);
                $this->em->persist($stato_errore);
                $this->em->flush($stato_errore);
            }
            $output->writeln('<error>Errore durante lo scarico dell\'esportazione</error>');
            $this->container->get('monolog.logger.schema31')->error($e->getMessage());
            return 999;
        }

        return 0;
    }

    protected function eseguiSingolaEsportazione(MonitoraggioConfigurazioneEsportazioneTavole $tavola) {
        $nomeStruttura = strtoupper($tavola->getTavolaProtocollo());
        try {
            $funcEsporta = '\MonitoraggioBundle\GestoriEsportazione\EsportaElementi\Esporta' . $nomeStruttura;

            $objectElem = new $funcEsporta($this->container);
           
            $res = $objectElem->execute($tavola->getMonitoraggioConfigurazioneEsportazione()->getElemento(), $tavola);

            if ($res instanceof \Traversable || \is_array($res)) { //Esportazione Multipla
                $arrayTmp = new ArrayCollection();
                foreach ($res as $singolaRes) {
                    $validazione = $this->validaSingoloElemento($singolaRes);
                    if (0 == \count($validazione)) {
                        $arrayTmp->add($singolaRes);
                    } else {
                        foreach ($validazione as $errore) {
                            $this->addError($errore->getPropertyPath() . ': ' . $errore->getMessage(), $tavola);
                        }
                    }
                }

                return \count($arrayTmp) > 0 ? $arrayTmp : false;
            } else { //Esportazion Singola
                $validazione = $this->validaSingoloElemento($res);
                if (0 == \count($validazione)) {
                    return $res;
                } else {
                    foreach ($validazione as $errore) {
                        $this->addError($errore->getPropertyPath() . ': ' . $errore->getMessage(), $tavola);
                    }
                    return false;
                }
            }
        } catch (IgrueException $e) {
            $this->addError($e->getMessage(), $tavola, null, $e->getIgrueCode());

            return false;
        } catch (\Exception $e) {
            $this->addError($e->getMessage(), $tavola);

            return false;
        }
        $this->addError('Struttura non esportabile', $tavola);
        return false;
    }

    protected function validaSingoloElemento($elemento) {
        return $this->container->get('validator')->validate($elemento, null, ['Default', 'esportazione_monitoraggio']);
    }

    public function resetScarico($esportazione) {
        return $this->em->getRepository('MonitoraggioBundle:MonitoraggioEsportazione')->resetScarico($esportazione);
    }

    public function addError($message, MonitoraggioConfigurazioneEsportazioneTavole $tavola = null, $configurazione = null, $errorCode = null) {
        $errorLog = new MonitoraggioConfigurazioneEsportazioneErrore();
        $errorLog->setErrore($message);
        if (is_null($configurazione)) {
            //Configurazione è managed e presente a DB
            $configurazione = $tavola->getMonitoraggioConfigurazioneEsportazione();
            $tavola->setMonitoraggioConfigurazioneEsportazione($configurazione);

            $errorLog->setCodiceErroreIgrue($errorCode);
            $errorLog->setMonitoraggioConfigurazioneEsportazione($configurazione);
            $errorLog->setMonitoraggioConfigurazioneEsportazioneTavole($tavola);
            $this->em->persist($errorLog);
            $this->em->flush($errorLog);

            $tavola->addMonitoraggioConfigurazioneEsportazioneErrori($errorLog);
            $tavola->setFlagErrore(true);
            $this->em->persist($tavola);
            $this->em->flush($tavola);
        } else {
            $configurazione = $this->em->merge($configurazione);
            $errorLog->setMonitoraggioConfigurazioneEsportazione($configurazione);
            $this->em->persist($errorLog);
            $this->em->flush($errorLog);
        }

        $configurazione->addmonitoraggioConfigurazioneEsportazioneErrori($errorLog);
        $configurazione->setFlagErrore(true);
        $this->em->persist($configurazione);
        $this->em->flush($configurazione);
    }
}
