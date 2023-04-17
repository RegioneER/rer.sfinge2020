<?php

namespace MonitoraggioBundle\GestoriEsportazione\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\Esporta;
use Doctrine\Common\Collections\ArrayCollection;
use MonitoraggioBundle\Exception\EsportazioneException;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Entity\FN03PianoCosti;


/**
 * @author vbuscemi
 */
class EsportaFN03 extends Esporta {

    public function execute(Richiesta $richiesta, $tavola, $enable_ctrl = false) {
        if ($enable_ctrl) {
            if (!$this->em->getRepository('MonitoraggioBundle:FN03PianoCosti')->isEsportabile($tavola->getMonitoraggioConfigurazioneEsportazione(), $richiesta)) {
                throw EsportazioneException::richiestaNonEsportabile('FN03', $richiesta);
            }
        }
        $arrayPianoCosto = $this->container->get('gestore_voci_piano_costo_monitoraggio')->getGestore($richiesta)->generaArrayPianoCostoTotaleRealizzato();
        if (count($arrayPianoCosto) < 1) {
           throw EsportazioneException::richiestaSenzaPianoCosti($richiesta);
        }
        $arrayRisultato = new ArrayCollection();
        foreach ($arrayPianoCosto as $voceAnno) {
            $res = new FN03PianoCosti();
            $res->setCodLocaleProgetto($richiesta->getProtocollo());
            $res->setAnnoPiano($voceAnno->getAnnoPiano());
            $res->setImpDaRealizzare($voceAnno->getImportoDaRealizzare());
            $res->setImpRealizzato($voceAnno->getImportoRealizzato());
            $res->setFlgCancellazione(self::bool2SN($voceAnno->getDataCancellazione()));
            $res->setEsportazioneStrutture($tavola);
            $arrayRisultato->add($res);
        }
        return $arrayRisultato;
    }

    /**
     * @return FN03PianoCosti
     */
    public function importa($input_array) {
        if (is_null($input_array) || !is_array($input_array) || count($input_array) != 5) {
            throw new EsportazioneException("FN03: Input_array non valido");
        }

        $res = new FN03PianoCosti();
        $res->setCodLocaleProgetto($input_array[0]);
        $res->setAnnoPiano($input_array[1]);
        $res->setImpRealizzato($input_array[2]);
        $res->setImpDaRealizzare($input_array[3]);
        $res->setFlgCancellazione($input_array[4]);
        return $res;
    }

}
