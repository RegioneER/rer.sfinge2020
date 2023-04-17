<?php

namespace BaseBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

/**
 * Class CfConstraint
 *
 * @Annotation
 */
class CfConstraint extends Constraint
{
    public $message = 'Il codice fiscale è errato. Non corrisponde ai dati anagrafici inseriti.';

    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }

    public function validatedBy()
    {
        return 'codice_fiscale_checks';
    }
}