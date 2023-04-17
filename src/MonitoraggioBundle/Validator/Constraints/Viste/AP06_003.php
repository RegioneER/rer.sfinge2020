<?php

namespace MonitoraggioBundle\Validator\Constraints\Viste;
use MonitoraggioBundle\Validator\Constraints\AP03_001 as Constraint;
class AP06_003 extends Constraint
{
    public function validatedBy()
    {
        return 'monitoraggio.form.validator.validators.VistaAP06_003Validator';
    }
}