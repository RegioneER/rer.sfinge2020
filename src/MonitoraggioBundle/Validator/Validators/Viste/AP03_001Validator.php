<?php

namespace MonitoraggioBundle\Validator\Validators\Viste;

use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Validator\Validators\AbstractValidator;
use Symfony\Component\Validator\Constraint;

class AP03_001Validator extends AbstractValidator {
    /**
     * @param Richiesta $value
     */
    public function validate($value, Constraint $constraint) {
        $dql = "SELECT count(ap03)
            FROM MonitoraggioBundle:VistaAP03 ap03
            INNER JOIN ap03.tc11_tipo_classificazione as tc11
            WHERE ap03.richiesta = :richiesta and tc11.tipo_class = 'RA'
        ";

        $res = $this->em->createQuery($dql)
                        ->setParameter('richiesta', $value)
                        ->getSingleScalarResult();
        if (0 == $res) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('struttura', 'AP03')
                ->addViolation();
        }
    }
}
