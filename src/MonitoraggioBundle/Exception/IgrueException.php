<?php

namespace MonitoraggioBundle\Exception;

/**
 * {@inheritdoc}
 */
class IgrueException extends \Exception
{
    /**
     * @var int
     */
    protected $codice_igrue;

    /**
     * @param string          $message
     * @param int             $igrue    codice errore IGRUE
     * @param int             $code     codice errore exception
     * @param \Throwable|null $previous
     */
    public function __construct($message = '', $igrue, $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->codice_igrue = $igrue;
    }

    /**
     * @return int
     */
    public function getIgrueCode()
    {
        return $this->codice_igrue;
    }
}
