<?php

namespace RichiesteBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidaDatiGenerali extends Constraint {

	public function validatedBy() {
		return "valida_dati_generali";
	}

	public function getTargets() {
		return self::CLASS_CONSTRAINT;
	}

}
