<?php

namespace FascicoloBundle\Services\TipoCampo;

/**
 * Description of Integer
 *
 * @author aturdo
 */
class Integer extends TipoCampo {
	
	public function getType() {
		return "Symfony\Component\Form\Extension\Core\Type\TextType";
	}

	public function validate($campo, $istanzeCampo, $checkRequired) {
		$constraint = new \Symfony\Component\Validator\Constraints\Regex(array("pattern" => "/^(-?\d+|\d*)$/"));
		$constraint->message = 'Questo valore non è valido.';
		$errors = new \Symfony\Component\Validator\ConstraintViolationList();
		
		foreach ($istanzeCampo as $istanzaCampo) {
			$errors->addAll(parent::$container->get('validator')->validate($istanzaCampo->getValore(), $constraint));
		}
		
		$errors->addAll(parent::validate($campo, $istanzeCampo, $checkRequired));
		
		return $errors;
	}

}
