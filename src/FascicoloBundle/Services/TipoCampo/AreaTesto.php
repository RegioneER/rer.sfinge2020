<?php

namespace FascicoloBundle\Services\TipoCampo;

/**
 * Description of AreaTesto
 *
 * @author aturdo
 */
class AreaTesto extends TipoCampo {
	
	public function getType() {
		return "Symfony\Component\Form\Extension\Core\Type\TextareaType";
	}

	public function validate($campo, $istanzeCampo, $checkRequired) {
		$errors = new \Symfony\Component\Validator\ConstraintViolationList();
		
		$errors->addAll(parent::validate($campo, $istanzeCampo, $checkRequired));
		
		return $errors;
	}
	
			
	public function getTypeOptions($campo, $dato) {
		$array_merge = parent::getTypeOptions($campo, $dato);
		if(!is_null($campo->getRigheTextArea()) && $campo->getRigheTextArea()>0){
			$options = array('attr' => array('rows' => $campo->getRigheTextArea()));
			$array_merge = array_merge($options,$array_merge);
		}
		return $array_merge;
	}

}
