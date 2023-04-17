<?php

namespace IstruttorieBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;


class ValidaValutazioneCriterioRichiestaValidator extends ConstraintValidator {

	/**
     * @var EntityManagerInterface
     */
	protected $entityManager;

	/**
	 * @var Container
	 */
	protected $serviceContainer;

    public function __construct(Container $serviceContainer)
    {
		$this->serviceContainer = $serviceContainer;
        $this->entityManager = $serviceContainer->get("doctrine");
    }
	
	
	public function validate($valutazioneCriterioRichiesta, Constraint $constraint) {
		$punteggio = $valutazioneCriterioRichiesta->getPunteggio();
		if($punteggio > $valutazioneCriterioRichiesta->getCriterio()->getPunteggioMassimo()){
			$this->context->buildViolation("Il punteggio è superiore al punteggio massimo indicato per il criterio")
						->atPath('punteggio')
						->addViolation();
					return;
		}

		if($punteggio < 0){
			$this->context->buildViolation("Il punteggio è inferiore al punteggio minimo per l'ammissibilità")
						->atPath('punteggio')
						->addViolation();
					return;
		}
	}

}
