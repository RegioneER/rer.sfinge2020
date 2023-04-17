<?php

namespace MonitoraggioBundle\Validator\Validators\Viste;

use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Validator\Validators\AbstractValidator;
use Symfony\Component\Validator\Constraint;

class AP05_002Validator extends AbstractValidator {
    /**
     * @param Richiesta $value
     */
    public function validate($value, Constraint $constraint) {
        $dql = "SELECT count(ap05)
            FROM MonitoraggioBundle:VistaAP05 ap05
            WHERE ap05.richiesta = :richiesta 
        ";

        $res = $this->em->createQuery($dql)
                        ->setParameter('richiesta', $value)
                        ->getSingleScalarResult();
        if (0 == $res) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('struttura', 'AP05')
                ->addViolation();
        }
    }
}
