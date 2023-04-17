<?php

namespace IstruttorieBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidaValutazioneCriterioRichiesta extends Constraint {

	public function validatedBy() {
		return 'valida_criterio';
	}

	public function getTargets() {
		return self::CLASS_CONSTRAINT;
	}

}
