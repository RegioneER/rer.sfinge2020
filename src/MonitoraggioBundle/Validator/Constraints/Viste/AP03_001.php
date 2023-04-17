<?php

namespace MonitoraggioBundle\Validator\Constraints\Viste;
use MonitoraggioBundle\Validator\Constraints\AP03_001 as Constraint;
class AP03_001 extends Constraint
{
    public function validatedBy()
    {
        return 'monitoraggio.form.validator.validators.VistaAP03_001Validator';
    }
}