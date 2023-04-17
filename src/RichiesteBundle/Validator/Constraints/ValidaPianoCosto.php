<?php

namespace RichiesteBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidaPianoCosto extends Constraint {

	public function validatedBy() {
		return 'valida_piano';
	}

	public function getTargets() {
		return self::CLASS_CONSTRAINT;
	}

}
