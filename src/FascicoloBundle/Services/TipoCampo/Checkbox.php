<?php

namespace FascicoloBundle\Services\TipoCampo;

/**
 * Description of Checkbox
 *
 * @author aturdo
 */
class Checkbox extends TipoCampo {
	
	public function getType() {
		return "Symfony\Component\Form\Extension\Core\Type\CheckboxType";
	}

	public function validate($campo, $istanzeCampo, $checkRequired) {
		$errors = new \Symfony\Component\Validator\ConstraintViolationList();
		
		$errors->addAll(parent::validate($campo, $istanzeCampo, $checkRequired));
		
		return $errors;
	}
	
	public function getTypeData($campo, $dato) {
		if (count($dato) > 0) {
			$booleano = boolval($dato[0]->getValore());
			return $booleano;
		} else {
			return null;
		}
	}	

}
