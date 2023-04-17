<?php

namespace MonitoraggioBundle\GestoriEsportazione\EsportaElementi;

use MonitoraggioBundle\Exception\EsportazioneException;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Entity\AP05StrumentoAttuativo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author vbuscemi
 */
class EsportaAP05 extends Esporta {
    /**
     * @return ArrayCollection
     */
    public function execute(Richiesta $richiesta, $tavola, $enable_ctrl = false) {
        if ($enable_ctrl) {
            if (!$this->em->getRepository('MonitoraggioBundle:AP05StrumentoAttuativo')->isEsportabile($tavola->getMonitoraggioConfigurazioneEsportazione(), $richiesta)) {
                throw  EsportazioneException::richiestaNonEsportabile('AP05', $richiesta);
            }
        }
        if ($richiesta->getMonStrumentiAttuativi()->isEmpty()) {
            throw EsportazioneException::richiestaSenzaStrumentoAttuativo($richiesta);
        }
        $resStrAttuativi = new ArrayCollection();
        foreach ($richiesta->getMonStrumentiAttuativi() as $strAtt) {
            $res = new AP05StrumentoAttuativo();
            $res->setCodLocaleProgetto($richiesta->getProtocollo());
            $res->setTc15StrumentoAttuativo($strAtt->getTc15StrumentoAttuativo());
            $res->setFlgCancellazione(self::bool2SN($richiesta->getDataCancellazione()));
            $res->setEsportazioneStrutture($tavola);
            $resStrAttuativi->add($res);
        }

        return $resStrAttuativi;
    }

    public function importa($input_array) {
        if (is_null($input_array) || !is_array($input_array) || 3 != count($input_array)) {
            throw new EsportazioneException("AP05: Input_array non valido");
        }

        $res = new AP05StrumentoAttuativo();
        $res->setCodLocaleProgetto($input_array[0]);
        $tc15 = $this->em->getRepository('MonitoraggioBundle:TC15StrumentoAttuativo')->findOneBy(['cod_stru_att' => $input_array[1]]);
        if (\is_null($tc15)) {
            throw new EsportazioneException("Strumento attuativo non valido");
        }
        $res->setTc15StrumentoAttuativo($tc15);
        $res->setFlgCancellazione($input_array[2]);
        return $res;
    }
}
