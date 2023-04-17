<?php

namespace MonitoraggioBundle\Service;

use MonitoraggioBundle\Exception\ImportazioneException;
use BaseBundle\Service\BaseService;
use BaseBundle\Exception\SfingeException;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use BaseBundle\Service\SpreadsheetFactory;
use DocumentoBundle\Entity\DocumentoFile;

class GestoreImportazioneMonitoraggio extends BaseService implements IGestoreImportazioneMonitoraggio {
    const BASE = '\\MonitoraggioBundle\\Service\\GestoriSheetImpegniProcedure';
    /** @var array */
    protected $warnings = [];

    public function importaImpegniPagamentiEnteGestore(DocumentoFile $documento): void {
        /** @var SpreadsheetFactory $factory */
        $factory = $this->container->get('phpoffice.spreadsheet');
        $excel = $factory->readDocumento($documento);
        $nomiSheet = $excel->getSheetNames();
        /** @var Worksheet $sheet */
        foreach ($excel->getAllSheets() as $sheet) {
            try {
                $this->elaboraSheet($sheet);
                $this->addSuccess("Caricamento '" . $sheet->getTitle() . "' effettuato con successo");
            } catch (SfingeException $e) {
                $this->container->get('logger')->error($e->getMessage());
                $this->addError($e->getMessage());
            }
        }
    }

    public function getWarnings(): array {
        return $this->warnings;
    }

    protected function elaboraSheet(Worksheet $sheet) {
        $em = $this->getEm();
        $connection = $em->getConnection();
        
        try {
            $gestore = $this->getGestoreSheet($sheet);
            $tuple = $gestore->elabora();
            $this->warnings[strtoupper($sheet->getTitle())] = $gestore->getWarnings();$connection->beginTransaction();
            foreach ($tuple as $tupla) {
                $em->persist($tupla);
                $em->flush($tupla);
            }
            $connection->commit();
        }
        catch(ImportazioneException $e){
            $this->container->get('logger')->error($e->getTraceAsString());
            $this->addError($e->getMessage());
        }
        catch(SfingeException $e){
            $this->container->get('logger')->error($e->getTraceAsString());
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }
            throw $e;
        }
        catch (\Exception $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }
            $this->container->get('logger')->error($e->getTraceAsString());
            throw new SfingeException("Errore durante il salvataggio dei dati", 0, $e);
        }
    }

    /**
     * @throws SfingeException
     */
    protected function getGestoreSheet(Worksheet $sheet): IGestoreSheetImpegniProcedure {
        $classe = self::BASE . '\\' . \strtoupper($sheet->getTitle());
        if (\class_exists($classe)) {
            return new $classe($this->container, $sheet);
        }
        throw new SfingeException('Gestore non trovato per il foglio ' . $sheet->getTitle());
    }
}
