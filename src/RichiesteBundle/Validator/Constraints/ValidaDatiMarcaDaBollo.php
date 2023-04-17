<?php
namespace RichiesteBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidaDatiMarcaDaBollo extends Constraint
{
	public function validatedBy()
    {
		return "valida_dati_marca_da_bollo";
	}

	public function getTargets()
    {
		return self::CLASS_CONSTRAINT;
	}

}
