<?php

namespace MonitoraggioBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class FN04_032 extends Constraint
{
    public $message = 'sfinge.monitoraggio.fn04_032';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'monitoraggio.form.validator.validators.FN04_032Validator';
    }

    public function __construct($options = null){
        parent::__construct($options);
        $this->payload = array(
            'codice_igrue' => '032'
        );
    }

    public function getCodiceIgrue()
    {
        return $this->payload['codice_igrue'];
    }
    
}