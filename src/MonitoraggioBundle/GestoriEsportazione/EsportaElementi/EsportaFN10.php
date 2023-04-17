<?php

namespace MonitoraggioBundle\GestoriEsportazione\EsportaElementi;

use Doctrine\Common\Collections\ArrayCollection;
use MonitoraggioBundle\Exception\EsportazioneException;
use MonitoraggioBundle\Entity\FN10Economie;

/**
 * @author vbuscemi
 */
class EsportaFN10 extends Esporta {
    /**
     * @return ArrayCollection|FN10Economie[]
     */
    public function execute(\RichiesteBundle\Entity\Richiesta $richiesta, $tavola, $enable_ctrl = false) {
        if ($enable_ctrl) {
            if (!$this->em->getRepository('MonitoraggioBundle:FN10Economie')->isEsportabile($tavola->getMonitoraggioConfigurazioneEsportazione(), $richiesta)) {
                throw EsportazioneException::richiestaNonEsportabile('FN10', $richiesta);
            }
        }
        $array = new ArrayCollection();
        foreach ($richiesta->getMonEconomie() as $economia) {
            $res = new FN10Economie();
            $res->setCodLocaleProgetto($richiesta->getProtocollo());
            $res->setTc33FonteFinanziaria($economia->getTc33FonteFinanziaria());
            $res->setImporto($economia->getImporto());
            $res->setFlgCancellazione(self::bool2SN($economia->getDataCancellazione()));
            $res->setEsportazioneStrutture($tavola);
            $array->add($res);
        }

        return $array;
    }

    /**
     * @return FN10Economie
     */
    public function importa($input_array) {
        if (\is_null($input_array) || !\is_array($input_array) || 4 != \count($input_array)) {
            throw new EsportazioneException("FN10: Input_array non valido");
        }

        $res = new FN10Economie();
        $res->setCodLocaleProgetto($input_array[0]);
        $tc33 = $this->em->getRepository('MonitoraggioBundle:TC33FonteFinanziaria')->findOneBy(['cod_fondo' => $input_array[1]]);
        if (\is_null($tc33)) {
            throw new EsportazioneException("Fonte finanziaria non valida");
        }
        $res->setTc33FonteFinanziaria($tc33);
        $res->setImporto($input_array[2]);
        $res->setFlgCancellazione($input_array[3]);
        return $res;
    }
}
