<?php

namespace DocumentoBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidaDocumento extends Constraint {

	public function validatedBy() {
		return 'valida_mime';
	}

	public function getTargets() {
		return self::CLASS_CONSTRAINT;
	}

}
