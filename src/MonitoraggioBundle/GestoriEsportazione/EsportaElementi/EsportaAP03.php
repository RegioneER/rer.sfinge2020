<?php

namespace MonitoraggioBundle\GestoriEsportazione\EsportaElementi;

use MonitoraggioBundle\Exception\EsportazioneException;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Entity\AP03Classificazioni;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author vbuscemi
 */
class EsportaAP03 extends Esporta {
    public function execute(Richiesta $richiesta, $tavola, $enable_ctrl = false) {
        if ($enable_ctrl) {
            if (!$this->em->getRepository('MonitoraggioBundle:AP03Classificazioni')->isEsportabile($tavola->getMonitoraggioConfigurazioneEsportazione(), $richiesta)) {
                throw EsportazioneException::richiestaNonEsportabile('AP03', $richiesta);
            }
        }
        if ($richiesta->getMonProgrammi()->isEmpty()) {
            throw EsportazioneException::richiestaSenzaProgramma($richiesta);
        }
        $resClassificazioni = new ArrayCollection();
        foreach ($richiesta->getMonProgrammi() as $programma) {
            foreach ($programma->getClassificazioni() as $classificazione) {
                $res = new AP03Classificazioni();
                $res->setCodLocaleProgetto($richiesta->getProtocollo());
                $res->setTc4Programma($programma->getTc4Programma());
                $res->setTc11TipoClassificazione($classificazione->getClassificazione()->getTipoClassificazione());
                $res->setClassificazione($classificazione->getClassificazione());
                $res->setFlgCancellazione(self::bool2SN($richiesta->getDataCancellazione()));
                $res->setEsportazioneStrutture($tavola);
                $resClassificazioni->add($res);
            }
        }

        return $resClassificazioni;
    }

    public function importa($input_array) {
        if (is_null($input_array) || !is_array($input_array) || 5 != count($input_array)) {
            throw new EsportazioneException('AP03: Input_array non valido');
        }

        $res = new AP03Classificazioni();
        $res->setCodLocaleProgetto($input_array[0]);
        $tc4 = $this->em->getRepository('MonitoraggioBundle:TC4Programma')->findOneBy(['cod_programma' => $input_array[1]]);
        if (\is_null($tc4)) {
            throw new EsportazioneException('Programma non valido');
        }
        $res->setTc4Programma($tc4);
        $tc11 = $this->em->getRepository('MonitoraggioBundle:TC11TipoClassificazione')->findOneBy(['tipo_class' => $input_array[2]]);
        if (\is_null($tc11)) {
            throw new EsportazioneException('Tipo classificazione non valida');
        }
        $res->setTc11TipoClassificazione($tc11);
        $tc12 = $this->em->getRepository('MonitoraggioBundle:TC12Classificazione')->findOneBy(['codice' => $input_array[3], 'tipo_classificazione' => $tc11]);
        if (\is_null($tc12)) {
            throw new EsportazioneException('Classificazione non valida');
        }
        $res->setClassificazione($tc12);
        $res->setFlgCancellazione($input_array[4]);

        return $res;
    }
}
