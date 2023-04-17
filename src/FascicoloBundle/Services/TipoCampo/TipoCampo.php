<?php

namespace FascicoloBundle\Services\TipoCampo;

/**
 *
 * @author aturdo
 */
abstract class TipoCampo implements TipoCampoInteface {
	
	protected static $container;
	
	public function __construct($container) {
		self::$container = $container;
	}
	
	public function getTypeData($campo, $dato) {
		if (count($dato) > 0) {
			return $dato[0]->getValore();
		} else {
			return null;
		}
	}
	
	public function getTypeOptions($campo, $dato) {
		return array('label' => $campo->getLabel(), 'required' => $campo->getRequired(), "data" => $this->getTypeData($campo, $dato));
	}
	
	public function checkRequired($campo, $istanzeCampo) {
		$errors = new \Symfony\Component\Validator\ConstraintViolationList();
		if ($campo->getRequired()) {
			$constraint = new \Symfony\Component\Validator\Constraints\NotBlank();
			$constraint->message = 'Valore obbligatorio';
			foreach ($istanzeCampo as $istanzaCampo) {
				$errors->addAll(self::$container->get('validator')->validate($istanzaCampo->getValore(), $constraint));
			}
			return $errors;
		}	
		
		return new \Symfony\Component\Validator\ConstraintViolationList();
	}
	
	public function validate($campo, $istanzeCampo, $checkRequired) {
		$errors = new \Symfony\Component\Validator\ConstraintViolationList();
		
		if ($checkRequired) {
			$errors->addAll($this->checkRequired($campo, $istanzeCampo));
			if ($errors->count() > 0) {
				return $errors;
			}
		}
		
		foreach ($campo->getVincoli() as $vincolo) {
			$servizio = "fascicolo.vincolo.".$vincolo->getTipoVincolo()->getCodice();
			$errors->addAll(self::$container->get($servizio)->validate($vincolo, $istanzeCampo));
		}
		
		return $errors;
	}	
	
	public function calcolaValoreRaw($campo, $valore){
		return $valore;
	}
}
