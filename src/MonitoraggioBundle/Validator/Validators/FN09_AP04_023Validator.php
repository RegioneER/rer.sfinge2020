<?php
namespace MonitoraggioBundle\Validator\Validators;



class FN09_AP04_023Validator extends AbstractValidator
{
    public function validate($value, \Symfony\Component\Validator\Constraint $constraint)
    {

        if ( !\in_array($value->getTavolaProtocollo(), array('FN09', 'AP04')) || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }
        $protocollo = $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo();

        $dql = 'select 1 '
            . 'from MonitoraggioBundle:FN09SpeseCertificate fn09 '
            . 'join fn09.tc36_livello_gerarchico tc36_livello_gerarchico '
            . "join MonitoraggioBundle:TC4Programma programma with tc36_livello_gerarchico.valore_dati_rilevati like concat(programma.cod_programma, '%' ) "
            . 'left join  MonitoraggioBundle:AP04Programma ap04 '
                    .' with ap04.tc4_programma = programma  '
                    .' and ap04.stato = 1 and ap04.data_cancellazione is null '
                    .'and  ap04.cod_locale_progetto = fn09.cod_locale_progetto '
            // .'left join ap04.tc4_programma tc4 with tc4 = programma '

            . 'where  fn09.cod_locale_progetto = :protocollo and ap04.id is null';

        $res = $this->em
            ->createQuery($dql)
            ->setParameter('protocollo', $protocollo)
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if (!\is_null($res)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}