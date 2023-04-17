<?php

namespace MonitoraggioBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class FN00_FN03_FN10_039 extends Constraint {
    public $message = 'sfinge.monitoraggio.fn00_fn03_fn10_039';

    public function getTargets() {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy() {
        return 'monitoraggio.form.validator.validators.FN00_FN03_FN10_039Validator';
    }

    public function __construct($options = null) {
        parent::__construct($options);
        $this->payload = [
            'codice_igrue' => '039',
        ];
    }

    public function getCodiceIgrue() {
        return $this->payload['codice_igrue'];
    }
}
