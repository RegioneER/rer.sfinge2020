<?php
namespace RichiesteBundle\Validator\Constraints;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use RichiesteBundle\Entity\Richiesta;

class ValidaDatiMarcaDaBolloValidator extends ConstraintValidator
{
	private $container;
	/**
	 * ValidaDatiMarcaDaBolloValidator constructor.
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function validate($richiesta, Constraint $constraint)
    {
		if (!($richiesta instanceof Richiesta)) {
			return;
		}

		$esitoValidazione = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->validaDatiMarcaDaBollo($richiesta);

		if (!$esitoValidazione->getEsito()) {
			foreach ($esitoValidazione->getMessaggi() as $campo => $messaggio) {
				$this->context->buildViolation($messaggio)
					->atPath($campo)
					->addViolation();
			}
			foreach ($esitoValidazione->getMessaggiSezione() as $campo => $messaggio) {
				$this->context->buildViolation($messaggio)
					->atPath($campo)
					->addViolation();
			}
		}
	}
}
