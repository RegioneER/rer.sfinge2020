<?php

namespace MonitoraggioBundle\Validator\Validators\Viste;

use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Validator\Validators\AbstractValidator;
use Symfony\Component\Validator\Constraint;

class SC00_004Validator extends AbstractValidator {
    /**
     * @param Richiesta $value
     */
    public function validate($value, Constraint $constraint) {
        $dql = "SELECT 1
            FROM MonitoraggioBundle:VistaSC00 sc00_1
            INNER JOIN sc00_1.tc24_ruolo_soggetto tc24_1
            , MonitoraggioBundle:VistaSC00 sc00_2
            INNER JOIN sc00_2.tc24_ruolo_soggetto tc24_2
            WHERE sc00_1.richiesta = :richiesta 
                AND tc24_1.cod_ruolo_sog = '1'
                AND sc00_2.richiesta = :richiesta 
                AND tc24_2.cod_ruolo_sog = '2'
        ";

        $res = $this->em->createQuery($dql)
                        ->setParameter('richiesta', $value)
                        ->setMaxResults(1)
                        ->getOneOrNullResult();
        if (\is_null($res)) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('struttura', 'SC00')
                ->addViolation();
        }
    }
}
