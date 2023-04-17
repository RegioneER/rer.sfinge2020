<?php

namespace FascicoloBundle\Services\TipoVincolo;

/**
 * Description of LunghezzaTesto
 *
 * @author aturdo
 */
class LunghezzaTestoHtml extends TipoVincolo {

	public function addTypeParameters($builder) {
		$builder->add('min', 'Symfony\Component\Form\Extension\Core\Type\IntegerType' , array('required' => false));
		$builder->add('max', 'Symfony\Component\Form\Extension\Core\Type\IntegerType' , array('required' => false));
	}
	
	public function getParametersFields() {
		return array("min", "max");
	}
	
	public function validate($vincolo, $istanzeCampo) {
		$parametri = $vincolo->getParametri();
		$constraint = new \BaseBundle\Validator\Constraints\ValidaLunghezzaHtml($parametri);
				
		$errors = new \Symfony\Component\Validator\ConstraintViolationList();
		
		foreach ($istanzeCampo as $istanzaCampo) {
			$errors->addAll(parent::$container->get('validator')->validate($istanzaCampo->getValore(), $constraint));
		}
		
		return $errors;
	}
	
	public function validaVincolo($vincolo, $form) {
		$parametri = $vincolo->getParametri();
		
		if (is_null($parametri["min"]) && is_null($parametri["max"])) {
			$form->get("min")->addError(new \Symfony\Component\Form\FormError("Uno tra min e max devono essere valorizzati"));
			$form->get("max")->addError(new \Symfony\Component\Form\FormError("Uno tra min e max devono essere valorizzati"));
			return;
		}	
	}
}
