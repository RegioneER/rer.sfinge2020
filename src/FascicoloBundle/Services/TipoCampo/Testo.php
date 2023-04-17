<?php

namespace FascicoloBundle\Services\TipoCampo;

/**
 * Description of Testo
 *
 * @author aturdo
 */
class Testo extends TipoCampo {
	
	public function getType() {
		return "Symfony\Component\Form\Extension\Core\Type\TextType";
	}

	public function validate($campo, $istanzeCampo, $checkRequired) {
		$errors = new \Symfony\Component\Validator\ConstraintViolationList();
		
		$errors->addAll(parent::validate($campo, $istanzeCampo, $checkRequired));
		
		return $errors;
	}

}
