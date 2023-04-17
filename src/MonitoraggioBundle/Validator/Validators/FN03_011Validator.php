<?php
namespace MonitoraggioBundle\Validator\Validators;

use Symfony\Component\Validator\Constraint;




class FN03_011Validator extends AbstractValidator
{
    public function validate($value, Constraint $constraint)
    {

        if ($value->getTavolaProtocollo() != 'FN03' || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }
        $dql = "SELECT 1 risultato
            FROM MonitoraggioBundle:FN03PianoCosti c 
            INNER JOIN c.monitoraggio_configurazione_esportazioni_tavola tavola 
            WHERE tavola = :tavola AND c.flg_cancellazione is null 
            ";

        $res = $this->em
            ->createQuery($dql)
            ->setParameter('tavola', $value)
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if (is_null($res) || $res['risultato'] != 1) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}