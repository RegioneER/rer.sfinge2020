<?php

namespace RichiesteBundle\Validator\Constraints;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use RichiesteBundle\Entity\Richiesta;

class ValidaDatiGeneraliValidator extends ConstraintValidator {


	private $container;
	/**
	 * ValidaDatiGeneraliValidator constructor.
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function validate($richiesta, Constraint $constraint) {

		if(!($richiesta instanceof Richiesta)){
			return;
		}

		$esitoValidazione = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->validaDatiGenerali($richiesta);

		if(!$esitoValidazione->getEsito()){
			foreach($esitoValidazione->getMessaggi() as $campo => $messaggio){
				$this->context->buildViolation($messaggio)
					->atPath($campo)
					->addViolation();
			}
			foreach($esitoValidazione->getMessaggiSezione() as $campo => $messaggio){
				$this->context->buildViolation($messaggio)
					->atPath($campo)
					->addViolation();
			}

		}


	}
	
}
