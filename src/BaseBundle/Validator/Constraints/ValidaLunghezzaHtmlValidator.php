<?php

namespace BaseBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraints\Length;


class ValidaLunghezzaHtmlValidator extends ConstraintValidator{


	private $messaggioTroppoCorto = "Questo valore è troppo corto. Dovrebbe essere almeno di % caratteri.";
	private $messaggioTroppoLungo = "Questo valore è troppo lungo. Dovrebbe essere al massimo di % caratteri. Hai inserito # caratteri";
	private $messaggioLunghezzaErrata = "Questo valore ha una lunghezza errata. Dovrebbe essere esattamente di % caratteri. Hai inserito # caratteri";

	public function validate($testo, Constraint $constraint) {
		$testo = $this->pulisciStringa($testo);

		if(function_exists("mb_strlen")){
			$lunghezza = mb_strlen($testo, "utf-8");
		} else {
			$lunghezza = strlen($testo);
		}
		$this->addViolation($lunghezza,$constraint);
		return;

	}

	private function addViolation($lunghezza, $constraint){
		if ($constraint->min == $constraint->max && $lunghezza != $constraint->min) {
			$messaggio = str_replace("%",$constraint->min,$this->messaggioLunghezzaErrata);
			$messaggio = str_replace("#",$lunghezza,$messaggio);
			$this->context->buildViolation($messaggio)->addViolation();			
		} elseif($lunghezza < $constraint->min){
			$this->context->buildViolation(str_replace("%",$constraint->min,$this->messaggioTroppoCorto))->addViolation();
		} elseif($lunghezza > $constraint->max){
			$messaggio = str_replace("%",$constraint->max,$this->messaggioTroppoLungo);
			$messaggio = str_replace("#",$lunghezza,$messaggio);
			$this->context->buildViolation($messaggio)->addViolation();
		}
	}

	public function pulisciStringa($stringa){
		$stringa = strip_tags($stringa);
		$stringa = html_entity_decode($stringa);
		return $stringa;
	}
}
