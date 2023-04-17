<?php

namespace MonitoraggioBundle\Validator\Constraints\Viste;
use MonitoraggioBundle\Validator\Constraints\SC00_004 as Constraint;
class SC00_004 extends Constraint
{
    public $message = 'sfinge.monitoraggio.sc00_004';

    public function validatedBy()
    {
        return 'monitoraggio.form.validator.validators.VistaSC00_004Validator';
    }
}