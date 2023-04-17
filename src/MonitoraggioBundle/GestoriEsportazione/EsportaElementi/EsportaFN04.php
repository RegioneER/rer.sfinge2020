<?php

namespace MonitoraggioBundle\GestoriEsportazione\EsportaElementi;

use Doctrine\Common\Collections\ArrayCollection;
use MonitoraggioBundle\Exception\EsportazioneException;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Entity\FN04Impegni;

class EsportaFN04 extends Esporta {
    public function execute(Richiesta $richiesta, $tavola, $enable_ctrl = false) {
        if ($enable_ctrl) {
            if (!$this->em->getRepository('MonitoraggioBundle:FN04Impegni')->isEsportabile($tavola->getMonitoraggioConfigurazioneEsportazione(), $richiesta)) {
                throw EsportazioneException::richiestaNonEsportabile('FN04', $richiesta);
            }
        }
        $arrayRisultato = new ArrayCollection();
        foreach ($richiesta->getMonImpegni() as $impegno) {
            $res = new FN04Impegni();
            $res->setCodLocaleProgetto($richiesta->getProtocollo());

            $res->setCodImpegno($impegno->getId());
            $res->setTipologiaImpegno($impegno->getTipologiaImpegno());
            $res->setDataImpegno($impegno->getDataImpegno());
            $res->setImportoImpegno($impegno->getImportoImpegno());
            $res->setTc38CausaleDisimpegno($impegno->getTc38CausaleDisimpegno());
            $res->setNoteImpegno($impegno->getNoteImpegno());
            $res->setFlgCancellazione(self::bool2SN(!is_null($impegno->getDataCancellazione())));
            $res->setEsportazioneStrutture($tavola);
            $arrayRisultato->add($res);
        }
        return $arrayRisultato;
    }

    public function importa($input_array) {
        if (is_null($input_array) || !is_array($input_array) || 8 != count($input_array)) {
            throw new EsportazioneException("FN04: Input_array non valido");
        }

        $res = new FN04Impegni();
        $res->setCodLocaleProgetto($input_array[0]);
        $res->setCodImpegno($input_array[1]);
        $res->setTipologiaImpegno($input_array[2]);
        $data = $this->createFromFormatV2($input_array[3]);
        $res->setDataImpegno($data);
        $res->setImportoImpegno(self::convertNumberFromString($input_array[4]));
        $tc38 = $this->em->getRepository('MonitoraggioBundle:TC38CausaleDisimpegno')->findOneBy(['causale_disimpegno' => $input_array[5]]);
        if (\is_null($tc38) && \in_array($res->getTipologiaImpegno(), ['D', 'D-TR'])) {
            throw new EsportazioneException("Causale disimpegno non valido");
        }
        $res->setTc38CausaleDisimpegno($tc38);
        $res->setNoteImpegno($input_array[6]);
        $res->setFlgCancellazione($input_array[7]);
        return $res;
    }
}
