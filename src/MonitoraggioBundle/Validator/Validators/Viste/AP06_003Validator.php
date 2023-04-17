<?php

namespace MonitoraggioBundle\Validator\Validators\Viste;

use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Validator\Validators\AbstractValidator;
use Symfony\Component\Validator\Constraint;

class AP06_003Validator extends AbstractValidator {
    /**
     * @param Richiesta $value
     */
    public function validate($value, Constraint $constraint) {
        $dql = "SELECT count(ap06)
            FROM MonitoraggioBundle:VistaAP06 ap06
            WHERE ap06.richiesta = :richiesta 
        ";

        $res = $this->em->createQuery($dql)
                        ->setParameter('richiesta', $value)
                        ->getSingleScalarResult();
        if (0 == $res) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('struttura', 'AP06')
                ->addViolation();
        }
    }
}
