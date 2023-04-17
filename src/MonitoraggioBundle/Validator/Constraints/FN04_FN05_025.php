<?php

namespace MonitoraggioBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class FN04_FN05_025 extends Constraint
{
    public $message = 'sfinge.monitoraggio.fn04_fn05_025';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'monitoraggio.form.validator.validators.FN04_FN05_025Validator';
    }

    public function __construct($options = null){
        parent::__construct($options);
        $this->payload = array(
            'codice_igrue' => '025'
        );
    }

    public function getCodiceIgrue()
    {
        return $this->payload['codice_igrue'];
    }
    
}