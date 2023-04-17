<?php
namespace MonitoraggioBundle\Validator\Validators;



class FN00_FN10_038Validator extends AbstractValidator
{
    public function validate($value,  \Symfony\Component\Validator\Constraint $constraint)
    {
       
        if ( !\in_array($value->getTavolaProtocollo(), array('FN00','FN10')) || !$this->checkDuplicateError($value,$constraint) ) {
            return;
        }
        $protocollo = $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo();
        
                $dql = 'select 1 '
                    . 'from MonitoraggioBundle:FN00Finanziamento finanziamento '
                    .'join finanziamento.tc33_fonte_finanziaria fin_fonte '
                    . ',MonitoraggioBundle:FN10Economie economie '
                    .'join economie.tc33_fonte_finanziaria eco_fonte '

                    . 'where finanziamento.cod_locale_progetto = :protocollo '
                    . 'and finanziamento.flg_cancellazione is null '
                    . 'and economie.cod_locale_progetto = finanziamento.cod_locale_progetto '
                    . 'and economie.flg_cancellazione is null '
                    . 'and eco_fonte.cod_fondo = fin_fonte.cod_fondo '

                    . 'group by finanziamento.cod_locale_progetto, economie.cod_locale_progetto '
                    . 'having sum( finanziamento.importo  ) <  sum(economie.importo)';
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