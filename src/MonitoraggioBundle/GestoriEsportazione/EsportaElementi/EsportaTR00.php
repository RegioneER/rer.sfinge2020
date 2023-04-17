<?php

namespace MonitoraggioBundle\GestoriEsportazione\EsportaElementi;

use MonitoraggioBundle\Exception\EsportazioneException;
use MonitoraggioBundle\Entity\Trasferimento;
use MonitoraggioBundle\Entity\TR00Trasferimenti;

/**
 * @author vbuscemi
 */
class EsportaTR00 extends Esporta {
    /**
     * @return TR00Trasferimenti
     */
    public function execute(Trasferimento $trasferimento, $tavola, $enable_ctrl = false) {
        if ($enable_ctrl) {
            if (!$this->em->getRepository('MonitoraggioBundle:TR00Trasferimenti')->isEsportabile($tavola->getMonitoraggioConfigurazioneEsportazione())) {
                $cod_trasferimento = $trasferimento->getCodTrasferimento();
                throw new EsportazioneException("Il trasferimento '$cod_trasferimento' non può essere esportato");
            }
        }
        $soggetto = $trasferimento->getSoggetto();
        if (\is_null($soggetto)) {
            throw new EsportazioneException("Soggetto non definito per il trasferimento");
        }
        if (\is_null($soggetto->getFormaGiuridica())) {
            throw new EsportazioneException('Forma giuridica non definito per il soggetto');
        }

        $res = new TR00Trasferimenti();
        $res->setCodTrasferimento($trasferimento->getCodTrasferimento());
        $res->setDataTrasferimento($trasferimento->getDataTrasferimento());
        $res->setTc4Programma($trasferimento->getProgramma());
        $res->setTc49CausaleTrasferimento($trasferimento->getCausaleTrasferimento());
        $res->setImportoTrasferimento($trasferimento->getImportoTrasferimento());
        $res->setCfSogRicevente($soggetto->getCodiceFiscale());
        $res->setFlagSoggettoPubblico($soggetto->getFormaGiuridica()->getSoggettoPubblico() ? "S" : "N");
        $res->setFlgCancellazione(self::bool2SN($trasferimento->getDataCancellazione()));
        $res->setEsportazioneStrutture($tavola);
        return $res;
    }

    /**
     * @return TR00Trasferimenti
     */
    public function importa($input_array) {
        if (is_null($input_array) || !is_array($input_array) || 8 != count($input_array)) {
            throw new EsportazioneException("TR00: Input_array non valido");
        }

        $res = new TR00Trasferimenti();
        $res->setCodTrasferimento($input_array[0]);
        $data = $this->createFromFormatV2($input_array[1]);
        $res->setDataTrasferimento($data);
        $tc4 = $this->em->getRepository('MonitoraggioBundle:TC4Programma')->findOneBy(['cod_programma' => $input_array[2]]);
        if (\is_null($tc4)) {
            throw new EsportazioneException("Programma non valido");
        }
        $res->setTc4Programma($tc4);
        $tc49 = $this->em->getRepository('MonitoraggioBundle:TC49CausaleTrasferimento')->findOneBy(['causale_trasferimento' => $input_array[3]]);
        if (\is_null($tc49)) {
            throw new EsportazioneException("Causale trasferimento non valido");
        }
        $res->setTc49CausaleTrasferimento($tc49);
        $res->setImportoTrasferimento($input_array[4]);
        $res->setCfSogRicevente($input_array[5]);
        $res->setFlagSoggettoPubblico($input_array[6]);
        $res->setFlgCancellazione($input_array[7]);
        return $res;
    }

}
