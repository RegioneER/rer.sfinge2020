<?php

namespace MonitoraggioBundle\Service;

use MonitoraggioBundle\Entity\MonitoraggioEsportazione;
use MonitoraggioBundle\Repository\MonitoraggioEsportazioneRepository;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneErrore;
use MonitoraggioBundle\Exception\EsportazioneException;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\Session\Session;
use MonitoraggioBundle\Exception\RigaAP3NonTrovata;

class GestoreEsportazioneIgrueService implements IGestoreEsportazioneIgrueService
{
    const CARRIAGE_RETURN = "\r\n";
    const SEPARATORE_CAMPO = '#';
    const HEADER = 'HH#0#23EM#PUC#';

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     *  @var \MonitoraggioBundle\Repository\MonitoraggioConfigurazioneEsportazioneTavoleRepository|null
     */
    private $tavoleRepository;

    /**
     * @var array|null
     */
    private $mappaErrori;

    /**
     * @var Session
     */
    protected $session;

    public function __construct(EntityManager $em, Logger $logger, Session $session )
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->mappaErrori = array();
        $this->tavoleRepository = $this->em->getRepository('MonitoraggioBundle:MonitoraggioConfigurazioneEsportazioneTavole');
        $this->session = $session;
    }

    /**
     * @return string
     */
    public function generaStreamFile(MonitoraggioEsportazione $esportazione)
    {
        ini_set('memory_limit', '512M');

        $tavoleRepository = $this->em->getRepository('MonitoraggioBundle:MonitoraggioConfigurazioneEsportazioneTavole');
        $records = $this->em->getRepository('MonitoraggioBundle:MonitoraggioEsportazione')->findAllStruttureByEsportazione($esportazione);
        $stream = \fopen('php://temp', 'r+');
        \fwrite($stream, self::HEADER . self::CARRIAGE_RETURN);
        //$records->rewind();
        $righe = 0;
        while ($records->valid()) {
            ++$righe;
            $record = $records->current();
            \fwrite(
                $stream,
                $record[0]::CODICE_TRACCIATO
                    . self::SEPARATORE_CAMPO . $righe
                    . self::SEPARATORE_CAMPO . $record[0]->getTracciato()
                    . self::SEPARATORE_CAMPO . self::CARRIAGE_RETURN
            );

            //Associo a tavola valore record file
            $tavola = $record[0]->getMonitoraggioConfigurazioneEsportazioniTavola();

            $record[0]->setProgressivoPuc($righe);

            $this->em->persist($record[0]);
            $this->em->flush($record[0]);

            $this->em->persist($tavola);
            $this->em->flush($tavola);

            //Rimuovo elemento da memoria
            $this->em->detach($tavola);
            $this->em->detach($record[0]);
            $records->next();
        }
        \fwrite(
            $stream,
            'FF' . self::SEPARATORE_CAMPO . ($righe + 1) . self::SEPARATORE_CAMPO . $righe . self::SEPARATORE_CAMPO . self::CARRIAGE_RETURN
        );
        $esportazione->setInviatiAdIgrue($righe);
        $this->em->persist($esportazione);
        $this->em->flush($esportazione);

        \rewind($stream);

        return $stream;
    }

    /**
     * @param MonitoraggioEsportazione $esportazione
     * @return resource
     */
    public function generaFile(MonitoraggioEsportazione $esportazione)
    {
        return \stream_get_contents($this->generaStreamFile($esportazione));
    }

    /**
     * @param MonitoraggioEsportazione &$esportazione
     */
    public function importaFileRisposta(MonitoraggioEsportazione &$esportazione)
    {
        \ini_set('memory_limit', '512M');
        /**
         * @var array
         */
        $mappaCodiciStrutturaEntity = MonitoraggioEsportazioneRepository::GetAllStrutture();

        /**
         * @var \DocumentoBundle\Entity\DocumentoFile
         */
        $documento = $esportazione->getDocumentoFromIgrue();

        $headerPresente = false;
        $footerPresente = false;
        $righeAnalizzate = 0;

        try {
            $file = \fopen($documento->getPath() . $documento->getNome(), 'r');
            while (!\feof($file)) {
                ++$righeAnalizzate;
                $rigaRaw = \rtrim(\fgets($file), " \t\n\r\0\x0B");
                $riga = array();
                \preg_match_all('/(?<=^|#)([^#]+)(?>#|#$)/', $rigaRaw, $riga);
                $rigaCorrente = $riga[1];
                if (!$rigaCorrente) {
                    $this->logger->warning('E\' presente una riga vuota nel file importato', array(
                        'contenuto_riga' => $rigaRaw,
                        'esportazione_id' => $esportazione->getId(),
                    ));
                    --$righeAnalizzate;
                    continue;
                }
                $codiceStruttura = $rigaCorrente[0];
                switch ($codiceStruttura) {
                //Inizio del file
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
                    $headerPresente = true;
                    break;
                //Fine del file
                case 'FF':
                    if (!$headerPresente || $footerPresente) {
                        throw new EsportazioneException('Errore durante il parsing del file');
                    }
                    if (\count($rigaCorrente) < 3 || $rigaCorrente[2] != ($righeAnalizzate - 2)) {
                        throw new EsportazioneException('Il numero di record non coincide. Risultato importazione monitoraggio su IGRUE con quelle riportate nel record FF');
                    }
                    $footerPresente = true;
                    break;
                //Legenda degli errori
                case 'TIPOERRORE':
                    if (!$headerPresente || $footerPresente) {
                        throw new EsportazioneException('Errore durante il parsing del file');
                    }
                    if (\count($rigaCorrente) < 3) {
                        throw new EsportazioneException('Riga FF non formattata correttamente');
                    }
                    $match = array();
                    \preg_match_all('/(\d+)\|([^\|]*)/', $rigaCorrente[2], $match);
                    //Costruisco dizionario
                    foreach ($match[1] as $idx => $tipoerrore) {
                        $this->mappaErrori[$tipoerrore] = $match[2][$idx];
                    }
                    break;
                default:
                        if (\count($rigaCorrente) < 4) {
                            $this->logger->error('Riga non formatta correttamente', array('record' => $rigaRaw));
                            throw new EsportazioneException('Riga non formattata correttamente', 1);
                        }
                        if (!$headerPresente || $footerPresente) {
                            throw new EsportazioneException('Errore durante il parsing del file');
                        }
                        try {
                            foreach ($this->analizzaSingoloRecord($esportazione, $rigaCorrente[0], $rigaCorrente[1], $rigaCorrente[2], $rigaCorrente[3]) as $errore) {
                                $this->em->persist($errore);
                                $this->em->flush($errore);
                                $this->em->detach($errore);
                            }
                        } 
                        catch (RigaAP3NonTrovata $e){
                            $this->addFlash('error', $e->getMessage());
                        }
                        catch (EsportazioneException $e) {
                            throw new EsportazioneException("Riga $righeAnalizzate: " . $e->getMessage(), 1, $e);
                        }
                        

                    break;
                }
            }
            $esportazione->setScartatiDaIgrue($righeAnalizzate-3);
            $this->em->persist($esportazione);
            $this->em->flush();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            if($file){
                \fclose($file);
            }
            throw $e;
        }
        \fclose($file);
    }

    /**
     * @param string $codiceStruttura codice della struttura es: AP01 FN10
     * @param int    $progressivo     progressivo assegnato nella precedente fase di esportazione
     * @param int    $codiceErrore    valore apri a 1 se errore se violazione regola della struttura, 2 se errore da controllo incrociato
     * @param string $valore          stringa di valore separati dal segno pipe |
     * @param MonitoraggioConfigurazioneEsportazioneErrore ritorna errore
     */
    protected function analizzaSingoloRecord($esportazione, $codiceStruttura, $progressivo, $codiceErrore, $valore)
    {
        //Errori delle strutture
        $tavola = $this->tavoleRepository->findByProgressivoEsportazione($progressivo, $codiceStruttura, $esportazione);
        if (is_null($tavola)) {
            $this->logger->error(
                'Errore durante importazione errori IGRUE: informazioni non trovate',
                array(
                    'esportazione' => $esportazione->getId(),
                    'progressivo' => $progressivo, 
                )
            );
            throw $codiceStruttura == 'AP03' ? 
                new RigaAP3NonTrovata($progressivo):
                new EsportazioneException("Record non associabile a nessun dato esportato", 1);
            
        }
        $match = array();
        $risultato = array();
        \preg_match_all('/(\d+)/', $valore, $match);
        foreach ($match[0] as $idx => $errore) {
            if (0 == $errore) { //Nessun errore per il campo
                continue;
            }
            $erroreIgrue = new MonitoraggioConfigurazioneEsportazioneErrore($tavola);
            $erroreIgrue->setErrore($this->mappaErrori[$errore]);
            $erroreIgrue->setCodiceErroreIgrue($errore);
            $erroreIgrue->setErroreDaIgrue(true);
            $risultato[] = $erroreIgrue;
        }
        $this->tavoleRepository->updateStrutturaErroreIgrue($tavola, $codiceStruttura, $progressivo);
        return $risultato;
    }

    protected function addFlash($type, $message) {
		$this->session->getFlashBag()->add($type, $message);
	}

}
