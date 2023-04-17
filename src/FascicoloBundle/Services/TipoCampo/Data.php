<?php

namespace FascicoloBundle\Services\TipoCampo;

/**
 * Description of Data
 *
 * @author abuffa
 */
class Data extends TipoCampo {
	
	public function getType() {
		return "Symfony\Component\Form\Extension\Core\Type\BirthdayType";
	}

	
	public function validate($campo, $istanzeCampo, $checkRequired) {		
		$errors = new \Symfony\Component\Validator\ConstraintViolationList();
		
		$errors->addAll(parent::validate($campo, $istanzeCampo, $checkRequired));
		
		return $errors;
	}
	
	public function getTypeOptions($campo, $dato) {
		$options = array('widget' => 'single_text','input' => 'datetime','format' => 'dd/MM/yyyy');
		return array_merge($options, parent::getTypeOptions($campo, $dato));
	}
	
	public function getTypeData($campo, $dato) {
		if (count($dato) > 0) {
			$data = \DateTime::createFromFormat("d/m/Y", $dato[0]->getValore());
			return $data == false ? null : $data;
		} else {
			return null;
		}
	}	
	
}
