<?php

namespace FascicoloBundle\Services\TipoVincolo;

/**
 * Description of Espressione
 *
 * @author aturdo
 */
class Espressione extends TipoVincolo {

	public function addTypeParameters($builder) {
		$builder->add('expression', 'Symfony\Component\Form\Extension\Core\Type\TextType' , array('required' => true, 'label' => 'Expressione'));
		$builder->add('message', 'Symfony\Component\Form\Extension\Core\Type\TextType' , array('required' => true, 'label' => 'Messaggio di errore'));
	}
	
	public function getParametersFields() {
		return array("expression", "message");
	}
	
	public function validate($vincolo, $istanzeCampo) {	
		$parametri = $vincolo->getParametri();
		if (substr($parametri["expression"], 0, 5) != "value") {
			$parametri["expression"] = "value ".$parametri["expression"];
		}
		
		$constraint = new \Symfony\Component\Validator\Constraints\Expression($parametri);
		
		$errors = new \Symfony\Component\Validator\ConstraintViolationList();
		
		foreach ($istanzeCampo as $istanzaCampo) {
			$errors->addAll(parent::$container->get('validator')->validate($istanzaCampo->getValore(), $constraint));
		}
		
		return $errors;
	}
	
	public function validaVincolo($vincolo, $form) {
		return;
	}
	
}
