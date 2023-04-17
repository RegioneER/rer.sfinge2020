<?php

namespace FascicoloBundle\Services\TipoCampo;

/**
 * Description of Email
 *
 * @author aturdo
 */
class Email extends TipoCampo {
	
	public function getType() {
		return "Symfony\Component\Form\Extension\Core\Type\EmailType";
	}

	public function validate($campo, $istanzeCampo, $checkRequired) {
		$constraint = new \Symfony\Component\Validator\Constraints\Email();
		$constraint->message = 'Indirizzo email non valido';
		$errors = new \Symfony\Component\Validator\ConstraintViolationList();
		
		foreach ($istanzeCampo as $istanzaCampo) {
			$errors->addAll(parent::$container->get('validator')->validate($istanzaCampo->getValore(), $constraint));
		}
		
		$errors->addAll(parent::validate($campo, $istanzeCampo, $checkRequired));
		
		return $errors;
	}

}
