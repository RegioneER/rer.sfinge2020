<?php

namespace SegnalazioniBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidaMantis extends Constraint {

	public function validatedBy() {
		return 'valida_mantis';
	}

	public function getTargets() {
		return self::CLASS_CONSTRAINT;
	}
	
}