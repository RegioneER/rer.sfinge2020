<?php

namespace FascicoloBundle\Services\TipoCampo;

/**
 * Description of Integer
 *
 * @author aturdo
 */
class Numero extends TipoCampo {
	
	public function getType() {
		return "Symfony\Component\Form\Extension\Core\Type\TextType";
	}

	public function validate($campo, $istanzeCampo, $checkRequired) {		
		$constraint = new \Symfony\Component\Validator\Constraints\Regex(array("pattern" => "/^(-?\d+|\d*)(\.\d{".$campo->getPrecisione()."})?$/"));
		$constraint->message = 'Questo valore non è valido.';
		$errors = new \Symfony\Component\Validator\ConstraintViolationList();
		
		$validator = parent::$container->get('validator');
		foreach ($istanzeCampo as $istanzaCampo) {
			$istanzaCampo->setValore(str_replace(",", ".", $istanzaCampo->getValore()));
			$istanzaCampo->setValoreRaw($this->calcolaValoreRaw($campo, $istanzaCampo->getValore()));
			$errors->addAll($validator->validate($istanzaCampo->getValore(), $constraint));
		}
		
		$errors->addAll(parent::validate($campo, $istanzeCampo, $checkRequired));
		
		return $errors;
	}
	
	
	public function getTypeOptions($campo, $dato) {
		$options = array();
		//$options['scale'] = $campo->getPrecisione();
		return array_merge($options, parent::getTypeOptions($campo, $dato));
	}

}
