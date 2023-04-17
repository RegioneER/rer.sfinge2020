<?php

namespace MonitoraggioBundle\Validator\Constraints\Viste;
use MonitoraggioBundle\Validator\Constraints\AP03_001 as Constraint;
class AP05_002 extends Constraint
{
    public function validatedBy()
    {
        return 'monitoraggio.form.validator.validators.VistaAP05_002Validator';
    }
}