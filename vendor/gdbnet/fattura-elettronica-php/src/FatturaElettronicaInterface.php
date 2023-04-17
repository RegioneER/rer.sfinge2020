<?php

namespace Gdbnet\FatturaElettronica;

interface FatturaElettronicaInterface
{
    /**
     * Convert to array
     *
     * @return mixed
     */
    public function toArray();

    /**
     * Create from array
     *
     * @param array $array
     * @return mixed
     */
    public static function fromArray(array $array);
}