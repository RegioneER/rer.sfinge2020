<?php

namespace BaseBundle\Service;

use DocumentoBundle\Entity\DocumentoFile;
use Exception;
use PhpOffice\PhpSpreadsheet\Calculation\Functions;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class SpreadsheetFactory extends IOFactory {
    public function __construct() {
        Functions::setReturnDateType(Functions::RETURNDATE_PHP_OBJECT);
    }

    public function getSpreadSheet(): Spreadsheet {
        return new Spreadsheet();
    }

    const EXCEL_EXTENSIONS = [
        'xlsx' => 'Xlsx',
        'xls' => 'Xls',
        'csv' => 'Csv',
        'ods' => 'Ods',
    ];

    const MIME_FILE_TYPE = [
        'application/vnd.ms-excel' => 'Xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'Xlsx',
        'application/vnd.oasis.opendocument.spreadsheet' => 'Ods',
        'text/csv' => 'Csv',
    ];

    public function createResponse(Spreadsheet $spreadsheet, string $fileName = 'file.xlsx', int $status = 200, array $headers = []): Response {
        $formato = $this->getFormato($fileName);

        $file = $this->getFile($spreadsheet, $formato);

        $response = new BinaryFileResponse($file, $status, $headers);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $fileName,
            iconv('UTF-8', 'ASCII//TRANSLIT', $fileName)
        );

        return $response;
    }

    /**
     * @return string|bool
     */
    public function getFile(Spreadsheet $spreadsheet, string $formato) {
        $writer = $this->createWriter($spreadsheet, self::EXCEL_EXTENSIONS[$formato]);
        $file = @tempnam(sys_get_temp_dir(), 'phpxltmp');
        $writer->save($file);

        return $file;
    }

    private function getFormato(string $filename): ?string {
        $matches = [];
        preg_match('/.*\.(\w*)/', $filename, $matches);
        $formato = $matches[1] ?? null;

        if (\is_null($formato) || !\array_key_exists($formato, self::EXCEL_EXTENSIONS)) {
            throw new \Exception('Formato file non trovato o formato dati non gestito');
        }

        return $formato;
    }

    public function readDocumento(DocumentoFile $documento): Spreadsheet {
        $formato = $this->getformatoDocumento($documento);
        $reader = $this->createReader(self::EXCEL_EXTENSIONS[$formato]);
        $spreadsheet = $reader->load($documento->getPath() . '/' . $documento->getNome());

        return $spreadsheet;
    }

    protected function getformatoDocumento(DocumentoFile $documento): string {
        try {
            return $this->getFormato($documento->getNome());
        } catch (\Exception $e) {
            return 'xlsx';
        }
    }

    public function readFile(File $file): Spreadsheet {
        $mime = $file->getMimeType();
        if(!\array_key_exists($mime, self::MIME_FILE_TYPE)){
            throw new \Exception("Il mime $mime non è supportato");
        }
        $tipoFactory = self::MIME_FILE_TYPE[$mime];
        $reader = $this->createReader($tipoFactory);
        $spreadsheet = $reader->load($file->getPathname());

        return $spreadsheet;
    }

    public function readUploadedFile(UploadedFile $file): Spreadsheet {
        $mime = $file->getMimeType();
        $tipoFactory = self::MIME_FILE_TYPE[$mime];
        $reader = $this->createReader($tipoFactory);
        $spreadsheet = $reader->load($file->getPathname());

        return $spreadsheet;
    }
}
