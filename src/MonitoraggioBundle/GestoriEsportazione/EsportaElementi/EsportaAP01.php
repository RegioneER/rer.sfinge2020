<?php

namespace MonitoraggioBundle\GestoriEsportazione\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\Esporta;
use MonitoraggioBundle\Exception\EsportazioneException;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Entity\AP01AssociazioneProgettiProcedura;



/**
 * @author vbuscemi
 */
class EsportaAP01 extends Esporta {

    /**
     * @param \RichiesteBundle\Entity\Richiesta $richiesta
     * @return AP01AssociazioneProgettiProcedura
     */
    public function execute(Richiesta $richiesta, $tavola, $enable_ctrl = false) {
        if ($enable_ctrl) {
            if (!$this->em->getRepository('MonitoraggioBundle:AP01AssociazioneProgettiProcedura')->isEsportabile($tavola->getMonitoraggioConfigurazioneEsportazione())) {
                throw EsportazioneException::richiestaNonEsportabile('AP01', $richiesta);
            }
        }
        $res = new AP01AssociazioneProgettiProcedura();
        $res->setCodLocaleProgetto($richiesta->getProtocollo());
        $res->setTc1ProceduraAttivazione($richiesta->getProcedura()->getMonProcAtt());
        $res->setFlgCancellazione(self::bool2SN($richiesta->getDataCancellazione()));
        $res->setEsportazioneStrutture($tavola);
        return $res;
    }

    /**
     * @param array $input_array
     * @return AP01AssociazioneProgettiProcedura
     */
    public function importa($input_array) {
        if (is_null($input_array) || !is_array($input_array) || count($input_array) != 3) {
            throw new EsportazioneException("AP01: Input_array non valido");
        }

        $res = new AP01AssociazioneProgettiProcedura();
        $res->setCodLocaleProgetto($input_array[0]);
        $tc1 = $this->em->getRepository('MonitoraggioBundle:TC1ProceduraAttivazione')->findOneBy(array('cod_proc_att' => $input_array[1]));
        if (\is_null($tc1)) {
            throw new EsportazioneException("Procedura attivazione non valida");
        }
        $res->setTc1ProceduraAttivazione($tc1);
        $res->setFlgCancellazione($input_array[2]);
        return $res;
    }

}
