<?php

namespace MonitoraggioBundle\GestoriEsportazione\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\Esporta;
use MonitoraggioBundle\Exception\EsportazioneException;
use Doctrine\Common\Collections\ArrayCollection;
use MonitoraggioBundle\Entity\IN00IndicatoriRisultato;
use MonitoraggioBundle\Entity\TC42IndicatoriRisultatoComuni;
use RichiesteBundle\Entity\Richiesta;

/**
 * @author vbuscemi
 */
class EsportaIN00 extends Esporta {

    /**
     * @return ArrayCollection|IN00IndicatoriRisultato[]
     */
    public function execute(Richiesta $richiesta, $tavola, $enable_ctrl = false) {
        if ($enable_ctrl) {
            if (!$this->em->getRepository('MonitoraggioBundle:IN00IndicatoriRisultato')->isEsportabile($tavola->getMonitoraggioConfigurazioneEsportazione(), $richiesta)) {
                throw EsportazioneException::richiestaNonEsportabile('IN00', $richiesta);
            }
        }
        $resCollection = new ArrayCollection();

        foreach ($richiesta->getMonIndicatoreRisultato() as $value) {
            $indicatore = $value->getIndicatore();
            $res = new IN00IndicatoriRisultato();
            $res->setCodLocaleProgetto($richiesta->getProtocollo());
            $res->setTipoIndicatoreDiRisultato($indicatore instanceof TC42IndicatoriRisultatoComuni ? "COM" : "DPR");
            $res->setIndicatoreId($indicatore);
            $res->setFlgCancellazione(self::bool2SN($value->getDataCancellazione()));
            $res->setEsportazioneStrutture($tavola);
            $resCollection->add($res);
        }

        return $resCollection;
    }

    /**
     * @return IN00IndicatoriRisultato
     */
    public function importa($input_array) {
        if (is_null($input_array) || !is_array($input_array) || count($input_array) != 4) {
            throw new EsportazioneException("IN00: Input_array non valido");
        }

        $res = new IN00IndicatoriRisultato();
        $res->setCodLocaleProgetto($input_array[0]);
        $res->setTipoIndicatoreDiRisultato($input_array[1]);
        $tc42_43 = $this->em->getRepository('MonitoraggioBundle:' . ($input_array[1] == 'COM' ? 'TC42IndicatoriRisultatoComuni' : 'TC43IndicatoriRisultatoProgramma'))->findOneBy(array('cod_indicatore' => $input_array[2]));
        if (\is_null($tc42_43)) {
            throw new EsportazioneException("Indicatore risultato non valido");
        }
        $res->setIndicatoreId($tc42_43);
        $res->setFlgCancellazione($input_array[3]);
        return $res;
    }

}
