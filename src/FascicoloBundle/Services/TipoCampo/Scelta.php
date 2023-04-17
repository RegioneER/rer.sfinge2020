<?php

namespace FascicoloBundle\Services\TipoCampo;

/**
 * Description of AreaTesto
 *
 * @author aturdo
 */
class Scelta extends TipoCampo {
	
	public function getType() {
		return "Symfony\Component\Form\Extension\Core\Type\ChoiceType";
	}

	public function validate($campo, $istanzeCampo, $checkRequired) {
		$errors = new \Symfony\Component\Validator\ConstraintViolationList();
		
		$errors->addAll(parent::validate($campo, $istanzeCampo, $checkRequired));
		
		return $errors;
	}
	
	public function getTypeOptions($campo, $dato) {
		$options = array();
		
		$options['choices'] = $this->calcolaScelte($campo);
		
		if ($campo->getExpanded()) {
			$options['expanded'] = true;
			$options['placeholder'] = false;
		} else {
			$options['placeholder'] = '-';	
		}
		
		if ($campo->getMultiple()) {
			$options['multiple'] = true;
		}
		
		$options["choices_as_values"] = true;
		
		return array_merge($options, parent::getTypeOptions($campo, $dato));
	}
	
	public function getTypeData($campo, $dato) {
		$data = array();
		if ($campo->getMultiple()) {
			foreach ($dato as $valore) {
				$data[] = $valore->getValore();
			}
		} else {
			if (count($dato) > 0) {
				$data = $dato[0]->getValore();
			}
		}
		
		return $data;
	}
	
	public function calcolaValoreRaw($campo, $valore) {
		
		if (!is_null($campo->getQuery())) {
			$conn = parent::$container->get('doctrine')->getManager()->getConnection();
			$conn->setFetchMode(\PDO::FETCH_NUM);
			$results = $conn->fetchAll($campo->getQuery());
			
			foreach ($results as $result) {
				if($result[0]==$valore){
					return $result[1];
				}
			}	
		}else {
			$scelte = $campo->getScelte();
			return isset($scelte[$valore]) ? $scelte[$valore] : null;				
		}
		return null;
	}
	
	public function calcolaScelte($campo) {
		$choices = array();
		if (!is_null($campo->getQuery())) {
			$conn = parent::$container->get('doctrine')->getManager()->getConnection();
			$conn->setFetchMode(\PDO::FETCH_NUM);
			$results = $conn->fetchAll($campo->getQuery());
			
			foreach ($results as $result) {
				$choices[$result[1]] = $result[0];
			}	
		} else {
			foreach ($campo->getScelte() as $chiave => $scelta) {
				$choices[$scelta] = $chiave;
			}			
		}
		
		return $choices;
	}

}
