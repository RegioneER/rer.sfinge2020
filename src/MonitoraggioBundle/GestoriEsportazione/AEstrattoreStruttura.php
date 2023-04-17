<?php

namespace MonitoraggioBundle\GestoriEsportazione;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use BaseBundle\Service\SpreadsheetFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use BaseBundle\Service\BaseServiceTrait;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Shared\Date;

abstract class AEstrattoreStruttura implements IEstrattoreStruttura {
    use BaseServiceTrait;

    /**
     * @var SpreadsheetFactory
     */
    private $spreadSheetService;

    /**
     * @var Spreadsheet
     */
    private $excel;

    /**
     * @var WorkSheet
     */
    protected $sheet;

    protected $title = 'sheet';

    protected $strictCompareNullCell = false;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->spreadSheetService = $this->container->get('phpoffice.spreadsheet');
        $this->excel = $this->spreadSheetService->getSpreadSheet();
        $this->sheet = $this->excel->getActiveSheet();
        $o = $this;
        $refl = new \ReflectionObject($this);
        $this->title = $refl->getShortName();
        $this->configura();
    }

    protected function configura(): void {
    }

    public function generateResult(): Response {
        $this->elabora();

        return $this->spreadSheetService->createResponse($this->excel, $this->title . '.xlsx');
    }

    private function elabora(): void {
        $this->sheet->setTitle($this->title);

        $result = $this->getQueryResult();
        $this->sheet->fromArray($this->getFirstLine());
        $countRow = 1;
        foreach ($result as $row) {
            ++$countRow;
            $rowView = $this->normalizeResult($row);
            $this->sheet->fromArray($rowView, null, "A$countRow", $this->strictCompareNullCell);
        }

        $numberFormats = $this->getColumnsNumberFormats();
        if ($countRow > 1 && \count($numberFormats) > 0) {
            foreach ($numberFormats as $column => $format) {
                $this->sheet->getStyle($column . "2:$column" . $countRow)
                      ->getNumberFormat()
                      ->setFormatCode($format);
            }
        }
    }

    protected function normalizeResult($riga): array {
        return \array_map(function ($valore) {
            if ($valore instanceof \DateTimeInterface) {
                return Date::PHPToExcel($valore);
            }
            if (\is_numeric($valore)) {
                return \floatval($valore);
            }
            return $valore;
        }, $riga);
    }

    protected function getColumnsNumberFormats(): array {
        return [];
    }

    protected function getFirstLine(): array {
        return [];
    }

    protected function getFirstLineStyle(): array {
        return [];
    }

    abstract protected function getQueryResult(): iterable;
}
