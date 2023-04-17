<?php

namespace MonitoraggioBundle\GestoriEsportazione\EsportaElementi;

use MonitoraggioBundle\Exception\EsportazioneException;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Entity\IN01IndicatoriOutput;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author vbuscemi
 */
class EsportaIN01 extends Esporta {
    /**
     * @return ArrayCollection
     */
    public function execute(Richiesta $richiesta, $tavola, $enable_ctrl = false) {
        if ($enable_ctrl) {
            if (!$this->em->getRepository('MonitoraggioBundle:IN01IndicatoriOutput')->isEsportabile($tavola->getMonitoraggioConfigurazioneEsportazione(), $richiesta)) {
                throw EsportazioneException::richiestaNonEsportabile('IN01', $richiesta);
            }
        }
        $resCollection = new ArrayCollection();

        foreach ($richiesta->getMonIndicatoreOutput() as $value) {
            $res = new IN01IndicatoriOutput();
            $res->setCodLocaleProgetto($richiesta->getProtocollo());
            $res->setTipoIndicatoreDiOutput("COMUNI" == $value->getIndicatore()->getTipo() ? "COM" : "DPR");
            $res->setIndicatoreId($value->getIndicatore());
            $res->setValProgrammato($value->getValProgrammato());
            $valValidato = $value->getValoreValidato();
            $realizzato = $valValidato ?? ($value->getValoreRealizzato());
            $res->setValoreRealizzato($realizzato);
            $res->setFlgCancellazione(self::bool2SN($value->getDataCancellazione()));
            $res->setEsportazioneStrutture($tavola);
            $resCollection->add($res);
        }

        return $resCollection;
    }

    /**
     * @return IN01IndicatoriOutput
     */
    public function importa(array $input_array) {
        if ( 6 != \count($input_array)) {
            throw new EsportazioneException("IN01: Input_array non valido");
        }

        $res = new IN01IndicatoriOutput();
        $res->setCodLocaleProgetto($input_array[0]);
        $res->setTipoIndicatoreDiOutput($input_array[1]);
        $tc44_45 = $this->em->getRepository('MonitoraggioBundle:' . ('COM' == $input_array[1] ? 'TC44IndicatoriOutputComuni' : 'TC45IndicatoriOutputProgramma'))->findOneBy(['cod_indicatore' => $input_array[2]]);
        if (\is_null($tc44_45)) {
            throw new EsportazioneException("Indicatore output non valido");
        }
        $res->setIndicatoreId($tc44_45);
        $res->setValProgrammato($input_array[3]);
        $res->setValoreRealizzato($input_array[4]);
        $res->setFlgCancellazione($input_array[5]);
        return $res;
    }
}
