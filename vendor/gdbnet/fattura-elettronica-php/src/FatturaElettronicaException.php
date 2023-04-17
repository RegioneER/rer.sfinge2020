<?php

namespace Gdbnet\FatturaElettronica;

use Exception;
use Throwable;

class FatturaElettronicaException extends Exception
{
    /**
     * FatturaElettronicaException constructor.
     * @param string $message
     * @param string $tag
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $tag = "", $code = 0, Throwable $previous = null)
    {
        if (empty($tag)) {
            $tag = 'FatturaElettronicaXml :: ';
        } else {
            $tag = 'FatturaElettronicaXml :: ' . $tag . ' :: ';
        }

        parent::__construct($tag . $message, $code, $previous);
    }
}