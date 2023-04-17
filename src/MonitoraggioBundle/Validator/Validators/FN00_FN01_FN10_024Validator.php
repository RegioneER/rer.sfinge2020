<?php

namespace MonitoraggioBundle\Validator\Validators;

use MonitoraggioBundle\Entity\MonitoraggioEsportazioneLogFase;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneTavole;
use Symfony\Component\Validator\Constraint;

class FN00_FN01_FN10_024Validator extends AbstractValidator {

    /**
     * @var string
     */
    public static $QUERY_COSTO_AMMESSO = "SELECT COALESCE(fn01.importo_ammesso, 0)
        FROM MonitoraggioBundle:FN01CostoAmmesso fn01
        inner join fn01.monitoraggio_configurazione_esportazioni_tavola tavola
        INNER join tavola.monitoraggio_configurazione_esportazione configurazione
        INNER join configurazione.monitoraggio_esportazione esportazione
        INNER join esportazione.fasi fasi
        where  fn01.cod_locale_progetto = :protocollo 
            and fn01.flg_cancellazione is null 
            and ( 
                fasi.fase in (:fasi) 
                OR tavola = :tavola
            )
        ORDER BY fn01.data_creazione DESC
    ";

    /**
     * @var string
     */
    public static $QUERY_FINANZIAMENTO = "SELECT coalesce(sum(coalesce(fn00.importo, 0)), 0) 
        from MonitoraggioBundle:FN00Finanziamento fn00
        join fn00.tc33_fonte_finanziaria tc33_fonte_finanziaria
        inner join fn00.monitoraggio_configurazione_esportazioni_tavola tavola
        INNER join tavola.monitoraggio_configurazione_esportazione configurazione
        INNER join configurazione.monitoraggio_esportazione esportazione
        INNER join esportazione.fasi fasi
        where  fn00.cod_locale_progetto = :protocollo
            and fn00.flg_cancellazione is null
            and tc33_fonte_finanziaria.cod_fondo not in (:fonti)
            and ( 
                fasi.fase in (:fasi) 
                OR tavola = :tavola
            )
    ";

    /**
     * @var string
     */
    PUBLIC STATIC $QUERY_ECONOMIE = "SELECT coalesce(sum(coalesce(fn10.importo, 0)),0)
        from MonitoraggioBundle:FN10Economie fn10 
        where  fn10.cod_locale_progetto = :protocollo 
        and fn10.flg_cancellazione is null
    ";

    /**
     * @param MonitoraggioConfigurazioneEsportazioneTavole $value
     */
    public function validate($value, Constraint $constraint) {
        if (!\in_array($value->getTavolaProtocollo(), ['FN00', 'FN01', 'FN10']) || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }
        $protocollo = $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta()->getProtocollo();

        $importoCostoAmmesso = $this->em
            ->createQuery(self::$QUERY_COSTO_AMMESSO)
            ->setMaxResults(1)
            ->setParameter('protocollo', $protocollo)
            ->setParameter('tavola', $value)
            ->setParameter('fasi', [
                MonitoraggioEsportazioneLogFase::STATO_IMPORTATO,
                MonitoraggioEsportazioneLogFase::STATO_INVIATO,
            ])
            ->getOneOrNullResult() ?: [0.0];
        
        $importoCostoAmmesso = \array_pop($importoCostoAmmesso);


        $importoFinanziamento = $this->em
            ->createQuery(self::$QUERY_FINANZIAMENTO)
            ->setParameter('protocollo', $protocollo)
            ->setParameter('tavola', $value)
            ->setParameter('fasi', [
                MonitoraggioEsportazioneLogFase::STATO_IMPORTATO,
                MonitoraggioEsportazioneLogFase::STATO_INVIATO,
            ])
            ->setParameter('fonti', [
            'PRT',
            'RDR',
        ])
            ->getOneOrNullResult() ?: [0.0];
        
        $importoFinanziamento = \array_pop($importoFinanziamento);


        $economie = $this->em
            ->createQuery(self::$QUERY_ECONOMIE)
            ->setParameter('protocollo', $protocollo)
            ->getOneOrNullResult() ?: [0.0];

        $economie = \array_pop($economie);

        if ($importoCostoAmmesso > $importoFinanziamento - $economie) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
