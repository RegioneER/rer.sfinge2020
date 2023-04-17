<?php

namespace FascicoloBundle\Services\TipoCampo;

/**
 * Description of Url
 *
 * @author aturdo
 */
class Url extends TipoCampo {
	
	public function getType() {
		return "Symfony\Component\Form\Extension\Core\Type\UrlType";
	}

	public function validate($campo, $istanzeCampo, $checkRequired) {
		$constraint = new \Symfony\Component\Validator\Constraints\Url();
		$constraint->message = 'Url non valido';
		$errors = new \Symfony\Component\Validator\ConstraintViolationList();
		
		foreach ($istanzeCampo as $istanzaCampo) {
			$errors->addAll(parent::$container->get('validator')->validate($istanzaCampo->getValore(), $constraint));
		}
		
		$errors->addAll(parent::validate($campo, $istanzeCampo, $checkRequired));
		
		return $errors;
	}

}
