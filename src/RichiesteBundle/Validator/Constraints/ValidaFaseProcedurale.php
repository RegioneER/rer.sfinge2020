<?php

namespace RichiesteBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidaFaseProcedurale extends Constraint {

	public function validatedBy() {
		return 'valida_fase_procedurale';
	}

	public function getTargets() {
		return self::CLASS_CONSTRAINT;
	}

}
