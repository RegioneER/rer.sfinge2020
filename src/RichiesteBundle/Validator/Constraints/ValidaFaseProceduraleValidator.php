<?php

namespace RichiesteBundle\Validator\Constraints;

use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class ValidaFaseProceduraleValidator extends ConstraintValidator {

	/**
	 * @var \Doctrine\ORM\EntityManagerInterface
	 */
	protected $entityManager;
	protected $serviceContainer;

	public function __construct(Container $serviceContainer) {
		$this->serviceContainer = $serviceContainer;
		$this->entityManager = $serviceContainer->get("doctrine");
	}

	public function validate($richiesta, Constraint $constraint) {


		if (!($richiesta instanceof Richiesta)) {
			return;
		}

		$gestore = $this->serviceContainer->get("gestore_fase_procedurale")->getGestore();
		$esito = $gestore->validaFaseProceduraleRichiesta($richiesta->getId());

		if (!$esito->getEsito()) {
			foreach ($esito->getMessaggi() as $messaggio) {
				$this->context->buildViolation( $messaggio)
						->addViolation();
			}
			foreach ($esito->getMessaggiSezione() as $messaggio) {
				$this->context->buildViolation( $messaggio)
						->addViolation();
			}
		}
	}

}
