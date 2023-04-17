<?php

namespace MonitoraggioBundle\Validator\Validators;

class SC00_004Validator extends AbstractValidator {

    public function validate($value, \Symfony\Component\Validator\Constraint $constraint) {

        if ($value->getTavolaProtocollo() != 'SC00' || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }

        // TODO

        $dql1 = 'select 1 from MonitoraggioBundle:SC00SoggettiCollegati c '
                . 'join c.tc24_ruolo_soggetto tc24_ruolo_soggetto '
                . 'join c.monitoraggio_configurazione_esportazioni_tavola tavola '
                . 'where tc24_ruolo_soggetto.cod_ruolo_sog = 1 and tavola = :tavola and c.flg_cancellazione is null ';

        $dql2 = 'select 1 from MonitoraggioBundle:SC00SoggettiCollegati c '
                . 'join c.tc24_ruolo_soggetto tc24_ruolo_soggetto '
                . 'join c.monitoraggio_configurazione_esportazioni_tavola tavola '
                . 'where tc24_ruolo_soggetto.cod_ruolo_sog = 2 and tavola = :tavola and c.flg_cancellazione is null ';


        $res1 = $this->em
                ->createQuery($dql1)
                ->setParameter('tavola', $value)
                ->setMaxResults(1)
                ->getOneOrNullResult();


        $res2 = $this->em
                ->createQuery($dql2)
                ->setParameter('tavola', $value)
                ->setMaxResults(1)
                ->getOneOrNullResult();

        if (is_null($res1) && is_null($res2)) {
            $this->context->buildViolation($constraint->message_b)
                    ->addViolation();
        } else if (is_null($res1)) {
            $this->context->buildViolation($constraint->message_a)
                    ->setParameter('{{ codice }}', '1')
                    ->setParameter('{{ descrizione }}', 'Programmatore del progetto')
                    ->addViolation();
        } else if (is_null($res2)) {
            $this->context->buildViolation($constraint->message_a)
                    ->setParameter('{{ codice }}', '2')
                    ->setParameter('{{ descrizione }}', 'Beneficiario del progetto')
                    ->addViolation();
        }
    }

}
