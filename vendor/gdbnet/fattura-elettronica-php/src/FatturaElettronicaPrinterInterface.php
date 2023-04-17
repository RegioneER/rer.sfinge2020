<?php


namespace Gdbnet\FatturaElettronica;

/**
 * Interface FatturaElettronicaPrinterInterface
 *
 * @package Gdbnet\FatturaElettronica
 */
interface FatturaElettronicaPrinterInterface
{
    /**
     * @param string $xml
     *
     * @return mixed
     */
    public function stampa(string $xml, string $orientation = null);
}