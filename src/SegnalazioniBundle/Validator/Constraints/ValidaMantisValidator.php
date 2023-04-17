<?php

namespace SegnalazioniBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraints as Assert;


class ValidaMantisValidator extends ConstraintValidator {


	private $messaggioNull = "Questo valore non dovrebbe essere vuoto.";

	public function validate($mantis, Constraint $constraint) {

		if($mantis->getObbligatorio()){
			if(is_null($mantis->getNumeroBando())){
				$this->context->buildViolation($this->messaggioNull)
						->atPath('numero_bando')
						->addViolation();
			}
			if(is_null($mantis->getProcesso())){
				$this->context->buildViolation($this->messaggioNull)
						->atPath('processo')
						->addViolation();
			}
		}
	}

}
