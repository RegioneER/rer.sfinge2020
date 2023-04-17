<?php

namespace MonitoraggioBundle\GestoriEsportazione\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\Esporta;
use MonitoraggioBundle\Exception\EsportazioneException;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneTavole;
use MonitoraggioBundle\Entity\AP04Programma;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @author vbuscemi
 */
class EsportaAP04 extends Esporta {

    /**
     * @param \RichiesteBundle\Entity\Richiesta $richiesta
     */
    public function execute(Richiesta $richiesta, MonitoraggioConfigurazioneEsportazioneTavole $tavola, $enable_ctrl = false) {
        if ($enable_ctrl) {
            if (!$this->em->getRepository('MonitoraggioBundle:AP04Programma')->isEsportabile($tavola->getMonitoraggioConfigurazioneEsportazione(), $richiesta)) {
                throw EsportazioneException::richiestaNonEsportabile('AP04', $richiesta);
            }
        }

        if ($richiesta->getMonProgrammi()->isEmpty()) {
            throw EsportazioneException::richiestaSenzaProgramma($richiesta);
        }

        $resProgrammi = new ArrayCollection();
        foreach ($richiesta->getMonProgrammi() as $programma) {
            $res = new AP04Programma();
            $res->setCodLocaleProgetto($richiesta->getProtocollo());
            $res->setTc4Programma($programma->getTc4Programma());
            $res->setStato($programma->getStato() ? 1 : 2);
            $res->setTc14SpecificaStato($programma->getTc14SpecificaStato());
            $res->setEsportazioneStrutture($tavola);
            $resProgrammi->add($res);
        }

        return $resProgrammi;
    }

    public function importa($input_array) {
        if (is_null($input_array) || !is_array($input_array) || count($input_array) != 4) {
            throw new EsportazioneException("AP04: Input_array non valido");
        }

        $res = new AP04Programma();
        $res->setCodLocaleProgetto($input_array[0]);
        $tc4 = $this->em->getRepository('MonitoraggioBundle:TC4Programma')->findOneBy(array('cod_programma' => $input_array[1]));
        if (\is_null($tc4)) {
            throw new EsportazioneException("Programma non valido");
        }
        $res->setTc4Programma($tc4);
        $res->setStato($input_array[2]);
        $tc14 = $this->em->getRepository('MonitoraggioBundle:TC14SpecificaStato')->findOneBy(array('specifica_stato' => $input_array[3]));
        if ($res->getStato() == 2 && \is_null($tc14)) {
            throw new EsportazioneException("Specifica stato non valido");
        }
        $res->setTc14SpecificaStato($tc14);
        return $res;
    }

}
