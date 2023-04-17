<?php

namespace Performer\PayERBundle\Exception;

use Exception;
use Performer\PayERBundle\Entity\AcquistoMarcaDaBollo;

class AcquistoMarcaDaBolloInviata extends Exception
{
    protected $message = "Richiesta acquisto marca da bollo già inviata (id {{ id }})";

    public function __construct(AcquistoMarcaDaBollo $acquistoMarcaDaBollo)
    {
        $this->message = str_replace(['{{ id }}'], [$acquistoMarcaDaBollo->getId()], $this->message);
        parent::__construct();
    }
}