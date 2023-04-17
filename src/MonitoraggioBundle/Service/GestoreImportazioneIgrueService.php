<?php

namespace MonitoraggioBundle\Service;

use MonitoraggioBundle\Entity\MonitoraggioEsportazione;
use Symfony\Bridge\Monolog\Logger;
use DocumentoBundle\Entity\DocumentoFile;
use MonitoraggioBundle\Repository\MonitoraggioEsportazioneRepository;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneTavole;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazione;
use MonitoraggioBundle\Entity\Trasferimento;
use MonitoraggioBundle\Entity\MonitoraggioEsportazioneLogFase;
use MonitoraggioBundle\Exception\EsportazioneException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneProcedura;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneTrasferimento;

class GestoreImportazioneIgrueService {
    const CARRIAGE_RETURN = "\r\n";
    const SEPARATORE_CAMPO = '#';
    const HEADER = 'HH#0#SISIGRUE#';
    const MAX_FILE_ROWS = 1000;
    const NOME_FILE_LOG = 'importazione_igrue';
    const CONSOLE_COMMAND = 'sfinge:monitoraggio:importazione';

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var array|null
     */
    private $mappaErrori;

    /**
     * @var \RichiesteBundle\Entity\RichiestaRepository
     */
    protected $richiesteRepository;

    /**
     * @var \SfingeBundle\Entity\ProceduraRepository
     */
    protected $proceduraRepository;

    /**
     * @var \MonitoraggioBundle\Repository\TrasferimentoRepository
     */
    protected $trasferimentoRepository;

    /**
     * @var MonitoraggioConfigurazioneEsportazioneTavole
     */
    protected $tavolaGenerica;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        \ini_set('memory_limit', '2048M');
        $this->container = $container;
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->logger = $container->get('logger');

        $this->richiesteRepository = $this->em->getRepository('RichiesteBundle:Richiesta');
        $this->proceduraRepository = $this->em->getRepository('SfingeBundle:Procedura');
        $this->trasferimentoRepository = $this->em->getRepository('MonitoraggioBundle:Trasferimento');
    }

    /**
     * @param DocumentoFile $documento documento da importare
     *
     * @return array Ritorna un array con gli errori incontrati array vuoto => nessun errore
     */
    public function importaDocumento(DocumentoFile $documento, bool $timeout = true) : array {
        $zip = new \ZipArchive();
        try {
            $dataEsportazione = self::getDateFromFilePath($documento->getNomeOriginale());
            $zip->open($documento->getPath() . '/' . $documento->getNome());
            if ($zip->numFiles > 1) {
                throw new EsportazioneException('L\'archivio contiene più di un file');
            }
            $inputStream = $zip->getStream($zip->getNameIndex(0));
            $stream = \fopen("php://temp", 'w+');
            \stream_copy_to_stream($inputStream, $stream);
            \rewind($stream);
            $res = array();
            if ($timeout && $this->fileTroppoGrande($stream)) {
                $esportazione = $this->creaImportazioneBath($documento, $dataEsportazione);
                $this->avviaProcessoImportazione($esportazione);
                $this->container->get('session')->getFlashBag()->add('warning', 'Importazione in background: si prega di attendere.');
            } else {
                $res = $this->importaEsportazione($stream, $dataEsportazione, $documento);
            }
        } catch (EsportazioneException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \Exception('Errore durante l\'importazione del file', 1, $e);
        }
        $zip->close();
        return $res;
    }

    /**
     * @throws \Exception|EsportazioneException
     */
    public function elaboraImportazione(MonitoraggioEsportazione $esportazione): array {
        $lastFase = $esportazione->getLastFase();
        if (\is_null($lastFase) || MonitoraggioEsportazioneLogFase::STATO_IMPORTAZIONE_BATCH != $lastFase->getFase()) {
            throw new \Exception("Esportazione non presente o con stato diverso da importazione batch");
        }
        $documento = $esportazione->getDocumentoToIgrue();
        $esportazione->setDataCancellazione(new \DateTime());

        $res = $this->importaDocumento($documento, false);

        $this->em->persist($esportazione);
        $this->em->flush();

        return $res;
    }

    protected function fileTroppoGrande($stream): bool {
        $linee = $this->getFileLength($stream);
        return $linee > self::MAX_FILE_ROWS;
    }

    protected function getFileLength($stream): int {
        $linee = 0;
        while (!\feof($stream)) {
            \fgets($stream);
            ++$linee;
        }
        \rewind($stream);

        return $linee;
    }

    protected function avviaProcessoImportazione(MonitoraggioEsportazione $esportazione): void {
        $esportazioneId = $esportazione->getId();
        $appDir = $this->container->get('kernel')->getRootDir();
        $logFile = $appDir . '/logs/' . self::NOME_FILE_LOG . '_' . \date('Y-m-d') . '.log';
        $command = PHP_BINDIR . '/php -f ' . $appDir . '/console ' . self::CONSOLE_COMMAND . ' ' . $esportazioneId . ' -e ' .
            $this->container->getParameter('kernel.environment');
        $command .= ' >> ' . $logFile . ' 2>> ' . $logFile . ' &';
        \shell_exec('echo "*** Inizio importazione ID ' . $esportazioneId . ' in data ' . \date('d/m/Y H:i:s') . '" >> ' . $logFile);
        \shell_exec($command);
    }

    protected function creaImportazioneBath(DocumentoFile $documento, \DateTime $dataEsportazione): MonitoraggioEsportazione {
        $esportazione = new MonitoraggioEsportazione();
        $fase = new MonitoraggioEsportazioneLogFase($esportazione, MonitoraggioEsportazioneLogFase::STATO_IMPORTAZIONE_BATCH);

        $esportazione->setDataInizio($dataEsportazione)
            ->setDocumentoToIgrue($documento)
            ->addFasi($fase);
        $this->em->persist($esportazione);
        $this->em->flush();
        return $esportazione;
    }

    private function pulisciCacheDoctrine(): void {
        //Faccio detach delle strutture
        foreach (MonitoraggioEsportazioneRepository::GetAllStrutture() as $classeStruttura) {
            $this->em->clear('MonitoraggioBundle:' . $classeStruttura);
        }
        //Faccio detach delle tavole, configurazioni ed esportazione

        foreach (array('MonitoraggioEsportazione',
        'MonitoraggioConfigurazioneEsportazione',
        'MonitoraggioConfigurazioneEsportazioneProcedura',
        'MonitoraggioConfigurazioneEsportazioneTrasferimento',
        'MonitoraggioConfigurazioneEsportazioneProcedura',
        'MonitoraggioConfigurazioneEsportazioneTavole', )
        as $classe) {
            $this->em->clear('MonitoraggioBundle:' . $classe);
        }
    }

    /**
     * @param resource  $stream           Stream del file da importare
     * @param \DateTime $dataEsportazione data dell'esportazione
     * @param DocumentoFile|null $documento
     * @return array Elenco degli errori presenti in formato leggibile per l'utente
     */
    public function importaEsportazione($stream, \DateTime $dataEsportazione, DocumentoFile $documento = null): array {
        $res = array();
        $connection = $this->em->getConnection();
        $connection->beginTransaction();

        $headerPresente = false;
        $footerPresente = false;
        
        $esportazione = new MonitoraggioEsportazione();
        $fase = new MonitoraggioEsportazioneLogFase($esportazione, MonitoraggioEsportazioneLogFase::STATO_IMPORTATO);
        $fase->setMonitoraggioEsportazione($esportazione);
        $esportazione->setDataInizio($dataEsportazione)
            ->setDocumentoToIgrue($documento)
            ->addFasi($fase);

        try {
            $righeAnalizzate = 0;
            $righescartate = 0;
            $metodoParsingRiga = '';

            while ($r = \fgets($stream)) {
                ++$righeAnalizzate;
                $rigaRaw = \rtrim($r, " \t\n\r\0\x0B");
                $riga = array();
                \preg_match_all('/(?<=^|#)([^#]+)(?>#|#$)/', $rigaRaw, $riga);
                $rigaCorrente = $riga[1];
                if (!$rigaCorrente) {
                    ++$righescartate;
                    $this->logger->warn('E\' presente una riga vuota nel file importato', array(
                        'contenuto_riga' => $rigaRaw,
                        'esportazione_id' => $esportazione->getId(),
                    ));
                }
                $codiceStruttura = $rigaCorrente[0];
                $progressivo = $rigaCorrente[1];
                switch ($codiceStruttura) {
                case 'HH':
                if (1 != $righeAnalizzate) {
                    $this->logger->error('Riga di intestazione in posizione diversa dalla prima riga',
                    array('riga' => $righeAnalizzate, 'testo' => $rigaRaw));
                    throw new EsportazioneException('Riga di intestazione in posizione diversa dalla prima riga');
                }
                if (true == $headerPresente) {
                    $this->logger->error('Header duplicato',
                    array('riga' => $righeAnalizzate, 'testo' => $rigaRaw));
                    throw new EsportazioneException('Header duplicato');
                }
                $metodoParsingRiga = self::HEADER != $rigaRaw ? 'parsingRigaOld' : 'parsingRigaIgrue';
                $headerPresente = true;
                --$righeAnalizzate;
                break;
                case 'FF':
                if (!$headerPresente || $footerPresente) {
                    throw new EsportazioneException('Errore durante il parsing del file');
                }
                if (\count($rigaCorrente) > 2) {
                    if ($rigaCorrente[2] != ($righeAnalizzate - 2)) {
                        throw new EsportazioneException('Il numero di record importati da IGRUE non coincide con quelle riportate nel record FF');
                    }
                }
                $footerPresente = true;
                --$righeAnalizzate;
                break;
                default:
                    $valoriStruttura = null;
                    try {
                        $valoriStruttura = $this->$metodoParsingRiga($rigaCorrente);

                        //1. Cerca se richiesta - procedura - trasferimento
                        if (\array_key_exists($codiceStruttura, MonitoraggioEsportazioneRepository::$ENTITY_REPOSITORY)) {
                            //Richiesta
                            $struttura = $this->importaStrutturaRichiesta($codiceStruttura, $progressivo, $valoriStruttura, $esportazione);
                            $this->em->persist($struttura);
                            $this->em->flush($struttura);
                        } elseif (\array_key_exists($codiceStruttura, MonitoraggioEsportazioneRepository::$ENTITY_REPOSITORY_PROCEDURE)) {
                            //Procedura
                            $struttura = $this->importaStrutturaProcedura($codiceStruttura, $progressivo, $valoriStruttura, $esportazione);
                            $this->em->persist($struttura);
                            $this->em->flush($struttura);
                        } elseif (\array_key_exists($codiceStruttura, MonitoraggioEsportazioneRepository::$ENTITY_REPOSITORY_TRASFERIMENTI)) {
                            //Trasferimenti
                            $struttura = $this->importaStrutturaTrasferimento($codiceStruttura, $progressivo, $valoriStruttura, $esportazione);
                            $this->em->persist($struttura);
                            $this->em->flush($struttura);
                        } else {
                            ++$righescartate;
                            throw new EsportazioneException('Struttura non valida');
                        }
                    } catch (EsportazioneException $e) {
                        ++$righescartate;
                        $this->logger->error($e->getMessage(), array(
                            'riga' => $rigaCorrente,
                            'codiceStruttura' => $codiceStruttura,
                            'rigaRaw' => $rigaRaw,
                            'riga_parsata' => $riga,
                        ));
                        $res[] = "Errore in struttura: $codiceStruttura, riga $righeAnalizzate: " . $e->getMessage();
                        //Salto al prossimo record
                        continue;
                    }
                break;
                }
            }
            $fase->setDataFine(new \DateTime());
            $esportazione->setInviatiAdIgrue($righeAnalizzate > 0 ? $righeAnalizzate : 0);
            $esportazione->setScartatiDaIgrue($righescartate > 0 ? $righescartate : 0);

            $this->em->persist($esportazione);
            $this->em->flush();
            $connection->commit();
        } catch (EsportazioneException $e) {
            $this->logger->error($e->getMessage(), array(
                'riga' => $rigaCorrente,
                'codiceStruttura' => $codiceStruttura,
                'rigaRaw' => $rigaRaw,
                'riga_parsata' => $riga,
            ));
            throw new EsportazioneException("Errore in struttura: $codiceStruttura, riga $righeAnalizzate: " . $e->getMessage(), 1, $e);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->pulisciCacheDoctrine();
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }
            throw $e;
        }
        return $res;
    }

    /**
     * @param string $file
     *
     * @return \DateTime
     */
    protected static function getDateFromFilePath($file) {
        $match = array();
        if (!\preg_match('/^(\d{4})\-?(\d{2})\-?(\d{2}).*$/', \basename($file), $match)) {
            throw new EsportazioneException('Impossibile fare il parse della data dal nome file');
        }

        return new \DateTime("$match[1]-$match[2]-$match[3]");
    }

    protected function generaStruttura($codiceStruttura, array $parametri) {
        $classe = '\MonitoraggioBundle\GestoriEsportazione\EsportaElementi\Esporta' . $codiceStruttura;
        $oggetto = new $classe($this->container);

        return $oggetto->importa($parametri);
    }

    /**
     * @param string $codiceStruttura
     * @param int    $progressivo
     * @param array  $valoriStruttura
     *
     * @return \MonitoraggioBundle\Entity\EntityEsportazione
     */
    private function importaStrutturaRichiesta($codiceStruttura, $progressivo, array $valoriStruttura, &$esportazione) {
        $struttura = $this->generaStruttura($codiceStruttura, $valoriStruttura);
        $struttura->setProgressivoPuc($progressivo);

        $richiesta = $this->richiesteRepository->findOneByProtocollo($struttura->getCodLocaleProgetto());
        if (\is_null($richiesta)) {
            $struttura->setMonitoraggioConfigurazioneEsportazioniTavola($this->getInstanceTavolaGenerica($esportazione));

            return $struttura;
        }
        //Recupero configurazione
        $configurazione = $esportazione->getMonitoraggioConfigurazione();
        $configurazione = $configurazione->filter(function (MonitoraggioConfigurazioneEsportazione $elemento) use ($richiesta) {
            $refl = new \ReflectionObject($elemento);
            return 'MonitoraggioConfigurazioneEsportazioneRichiesta' == $refl->getShortName() && $elemento->getRichiesta() && $elemento->getRichiesta()->getId() == $richiesta->getId();
        });

        if ($configurazione->isEmpty()) {
            $configurazione = new MonitoraggioConfigurazioneEsportazioneRichiesta($richiesta);
            $configurazione->setMonitoraggioEsportazione($esportazione);
            $esportazione->addMonitoraggioConfigurazione($configurazione);
            $this->em->persist($configurazione);
            $this->em->flush($configurazione);
        } else {
            $configurazione = $configurazione->first();
        }
        $tavola = $this->getTavolaCollegata($configurazione, $codiceStruttura);
        $struttura->setMonitoraggioConfigurazioneEsportazioniTavola($tavola);

        return $struttura;
    }

    /**
     * @param MonitoraggioConfigurazioneEsportazione &$configurazione
     * @param string $codiceStruttura
     * @return MonitoraggioConfigurazioneEsportazioneTavole
     */
    public function getTavolaCollegata(MonitoraggioConfigurazioneEsportazione &$configurazione, $codiceStruttura) {
        $tavola = $configurazione->getMonitoraggioConfigurazioneEsportazioneTavole()->filter(function (MonitoraggioConfigurazioneEsportazioneTavole $elemento) use ($codiceStruttura) {
            return $elemento->getTavolaProtocollo() == $codiceStruttura;
        });
        if ($tavola->isEmpty()) {
            $tavola = new MonitoraggioConfigurazioneEsportazioneTavole($configurazione);
            $tavola->setTavolaProtocollo($codiceStruttura);
            $configurazione->addMonitoraggioConfigurazioneEsportazioneTavole($tavola);
            $this->em->persist($configurazione);
            $this->em->flush($configurazione);
        } else {
            $tavola = $tavola->first();
        }

        return $tavola;
    }

    /**
     * @param string $codiceStruttura
     * @param int    $progressivo
     * @param array  $valoriStruttura
     *
     * @return \MonitoraggioBundle\Entity\EntityEsportazione
     */
    private function importaStrutturaProcedura($codiceStruttura, $progressivo, $valoriStruttura, &$esportazione) {
        $struttura = $this->generaStruttura($codiceStruttura, $valoriStruttura);
        $struttura->setProgressivoPuc($progressivo);

        $procedura = $this->proceduraRepository->findOneBy(array('mon_proc_att' => $struttura->getCodProcAtt()));
        if (\is_null($procedura)) {
            $struttura->setMonitoraggioConfigurazioneEsportazioniTavola($this->getInstanceTavolaGenerica($esportazione));

            return $struttura;
        }

        $configurazione = $esportazione->getMonitoraggioConfigurazione()->filter(function (MonitoraggioConfigurazioneEsportazione $elemento) use ($procedura) {
            $refl = new \ReflectionObject($elemento);

            return $refl->getShortName('MonitoraggioConfigurazioneEsportazioneProcedura') && $elemento->getProcedura() == $procedura;
        });

        if ($configurazione->isEmpty()) {
            $configurazione = new MonitoraggioConfigurazioneEsportazioneProcedura($procedura);
            $configurazione->setMonitoraggioEsportazione($esportazione);
            $esportazione->addMonitoraggioConfigurazione($configurazione);
        } else {
            $configurazione = $configurazione->first();
        }

        $tavola = $this->getTavolaCollegata($configurazione, $codiceStruttura);
        $struttura->setMonitoraggioConfigurazioneEsportazioniTavola($tavola);

        return $struttura;
    }

    /**
     * @param string $codiceStruttura
     * @param int    $progressivo
     * @param array  $valoriStruttura
     *
     * @return \MonitoraggioBundle\Entity\EntityEsportazione
     */
    private function importaStrutturaTrasferimento($codiceStruttura, $progressivo, $valoriStruttura, $esportazione) {
        $struttura = $this->generaStruttura($codiceStruttura, $valoriStruttura);
        $struttura->setProgressivoPuc($progressivo);

        $trasferimento = $this->trasferimentoRepository->findOneByChiaveProtocolloIgrue($struttura->getCodTrasferimento(), $struttura->getDataTrasferimento(), $struttura->getTc4Programma());
        if (\is_null($trasferimento)) {
            $struttura->setMonitoraggioConfigurazioneEsportazioniTavola($this->getInstanceTavolaGenerica($esportazione));

            return $struttura;
        }

        $configurazione = $esportazione->getMonitoraggioConfigurazione()->filter(function (MonitoraggioConfigurazioneEsportazione $elemento) use ($trasferimento) {
            $refl = new \ReflectionObject($trasferimento);

            return $refl->getShortName('MonitoraggioConfigurazioneEsportazioneTrasferimento') && $elemento->getTrasferimento() == $trasferimento;
        });

        if ($configurazione->isEmpty()) {
            $configurazione = new MonitoraggioConfigurazioneEsportazioneTrasferimento($$trasferimento);
            $configurazione->setMonitoraggioEsportazione($esportazione);
            $esportazione->addMonitoraggioConfigurazione($configurazione);
        } else {
            $configurazione = $configurazione->first();
        }

        //Recupero tavola
        $tavola = $this->getTavolaCollegata($configurazione, $codiceStruttura);

        //Lego struttura a tavola
        $struttura->setMonitoraggioConfigurazioneEsportazioniTavola($tavola);

        return $struttura;
    }

    /**
     * @param array $riga
     *
     * @return array
     */
    protected function parsingRigaOld(array $riga) {
        $res = array();
        \preg_match_all('/(?<=\||^)([^\|]*)(?=\||$)/', $riga[3], $res);
        $res = $res[1];
        \array_unshift($res, $riga[2]);

        return $res;
    }

    /**
     * @param array $riga
     *
     * @return array
     */
    protected function parsingRigaIgrue(array $riga): array {
        $res = array();
        \preg_match_all('/(?<=\||^)([^\|]*)(?=\||$)/', $riga[2], $res);

        return $res[1];
    }

    protected function getInstanceTavolaGenerica(MonitoraggioEsportazione &$esportazione) {
        if (\is_null($this->tavolaGenerica)) {
            $configurazione = new MonitoraggioConfigurazioneEsportazione($esportazione);
            $this->tavolaGenerica = new MonitoraggioConfigurazioneEsportazioneTavole($configurazione);
            $configurazione->addMonitoraggioConfigurazioneEsportazioneTavole($this->tavolaGenerica);
            $this->em->persist($configurazione);
        }

        return $this->tavolaGenerica;
    }
}
