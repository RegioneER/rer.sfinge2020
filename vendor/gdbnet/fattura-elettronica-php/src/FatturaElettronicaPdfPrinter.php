<?php


namespace Gdbnet\FatturaElettronica;

use Dompdf\Dompdf;

/**
 * Class FatturaElettronicaPdfPrinter
 *
 * @package Gdbnet\FatturaElettronica
 */
class FatturaElettronicaPdfPrinter implements FatturaElettronicaPrinterInterface
{

    /**
     * @param string $xml
     *
     * @return mixed
     */
    public function stampa(string $xml, string $orientation = null)
    {
        $htmlPrinter = new FatturaElettronicaHtmlPrinter();
        $html = $htmlPrinter->stampa($xml);

        $pdf = new Dompdf();

        $pdf->loadHtml($html);

        if($orientation === null) {
            $pdf->setPaper('A4', 'portrait');
        } else {
            $pdf->setPaper('A4', $orientation);
        }

        $pdf->render();

        $pdf->stream();

        return true;
    }
}