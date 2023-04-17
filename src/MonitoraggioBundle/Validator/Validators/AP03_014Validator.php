<?php
namespace MonitoraggioBundle\Validator\Validators;



class AP03_014Validator extends AbstractValidator
{

    protected  $CLASSIFICAZIONI = array(
        'CI',
        'FF',
        'TT',
        'MET',
        'AE',
    );

    // protected static $PROGRAMMI = array(
    //     '2014IT16M2OP001',
    //     '2014IT16M2OP002',
    //     '2014IT16M2OP006',
    //     '2014IT16RFOP004',
    //     '2014IT16RFOP005',
    //     '2014IT16RFOP007',
    //     '2014IT16RFOP008',
    //     '2014IT16RFOP009',
    //     '2014IT16RFOP010',
    //     '2014IT16RFOP011',
    //     '2014IT16RFOP012',
    //     '2014IT16RFOP013',
    //     '2014IT16RFOP014',
    //     '2014IT16RFOP015',
    //     '2014IT16RFOP016',
    //     '2014IT16RFOP017',
    //     '2014IT16RFOP018',
    //     '2014IT16RFOP019',
    //     '2014IT16RFOP020',
    //     '2014IT16RFOP021',
    //     '2014IT16RFOP022',
    // );
    
    protected $FONDO = 'ERDF';

    public function validate($value, \Symfony\Component\Validator\Constraint $constraint)
    {

        if (!\in_array($value->getTavolaProtocollo(), array('AP03', 'AP04', )) || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }
        $dql = 'select  COUNT(DISTINCT tipo_classificazione ) risultato '
            . 'from MonitoraggioBundle:AP04Programma programma '
            . 'join programma.tc4_programma ap04_tc4_programma '
            . 'left join MonitoraggioBundle:AP03Classificazioni ap03 with ap03.cod_locale_progetto = :protocollo_richiesta and ap03.flg_cancellazione is null '
            . 'left join ap03.tc4_programma tc4_programma with tc4_programma = ap04_tc4_programma '
            . 'left join ap03.classificazione classificazione '
            . 'left join classificazione.tipo_classificazione tipo_classificazione with tipo_classificazione.tipo_class in (:classificazioni) '
            . 'where programma.cod_locale_progetto = :protocollo_richiesta '
            . 'and programma.stato = 1 '
            . 'and ap04_tc4_programma.fondo like :fondo '
            .'group by tc4_programma '

        ;

        $classificazioni = $this->em
        ->createQuery($dql)
        ->setParameter('protocollo_richiesta', 
            $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo())
       // ->setParameter('programmi', self::$PROGRAMMI)
        ->setParameter('classificazioni', $this->CLASSIFICAZIONI)
        ->setParameter('fondo', '%'.$this->FONDO.'%')
        ->getResult();

        foreach ($classificazioni as $countClassificazioni) {
            if( $countClassificazioni['risultato'] != count( $this->CLASSIFICAZIONI )){
                $this->context->buildViolation($constraint->message)
                ->addViolation();
                return;
            }
        }
    }
}