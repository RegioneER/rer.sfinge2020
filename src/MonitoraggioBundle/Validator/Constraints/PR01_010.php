<?php

namespace MonitoraggioBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PR01_010 extends Constraint
{
    public $message = 'sfinge.monitoraggio.pr01_010';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'monitoraggio.form.validator.validators.PR01_010Validator';
    }

    public function __construct($options = null){
        parent::__construct($options);
        $this->payload = array(
            'codice_igrue' => '010'
        );
    }

    public function getCodiceIgrue()
    {
        return $this->payload['codice_igrue'];
    }
    
}