<?php

namespace RichiesteBundle\Validator\Constraints;

use RichiesteBundle\Entity\Proponente;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Doctrine\ORM\EntityManagerInterface;
use RichiesteBundle\Service\IGestorePianoCosto;

class ValidaPianoCostoValidator extends ConstraintValidator {

	/**
	 * @var EntityManagerInterface
	 */
	protected $entityManager;
	protected $serviceContainer;

	public function __construct(Container $serviceContainer) {
		$this->serviceContainer = $serviceContainer;
		$this->entityManager = $serviceContainer->get("doctrine");
	}

	public function validate($proponente, Constraint $constraint) {


		if (!($proponente instanceof Proponente)) {
			return;
		}
		/** @var IGestorePianoCosto $gestore */
		$gestore = $this->serviceContainer->get("gestore_piano_costo")->getGestore($proponente->getRichiesta()->getProcedura());
		$esito = $gestore->validaPianoDeiCostiProponente($proponente, array(), false);

		if (!$esito->getEsito()) {
			foreach ($esito->getMessaggi() as $messaggio) {
				$this->context->buildViolation( $messaggio)
						->atPath('importo')
						->addViolation();
			}
			foreach ($esito->getMessaggiSezione() as $messaggio) {
				$this->context->buildViolation( $messaggio)
						->atPath('importo')
						->addViolation();
			}
		}
	}

}
