<?php

namespace FascicoloBundle\Services\TipoVincolo;

/**
 * Description of EspressioneRegolare
 *
 * @author aturdo
 */
class EspressioneRegolare extends TipoVincolo {

	public function addTypeParameters($builder) {
		$builder->add('pattern', 'Symfony\Component\Form\Extension\Core\Type\TextType' , array('required' => true));
		$builder->add('match', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType' , array('required' => false, 'data' => true));
		$builder->add('message', 'Symfony\Component\Form\Extension\Core\Type\TextType' , array('required' => true, 'label' => 'Messaggio di errore'));
	}
	
	public function getParametersFields() {
		return array("pattern", "match", "message");
	}
	
	public function validate($vincolo, $istanzeCampo) {
		$parametri = $vincolo->getParametri();
		$constraint = new \Symfony\Component\Validator\Constraints\Regex($parametri);
				
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
