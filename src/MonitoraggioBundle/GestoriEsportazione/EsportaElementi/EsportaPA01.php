<?php

namespace MonitoraggioBundle\GestoriEsportazione\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\Esporta;
use MonitoraggioBundle\Exception\EsportazioneException;
use SfingeBundle\Entity\Procedura;
use MonitoraggioBundle\Entity\PA01ProgrammiCollegatiProceduraAttivazione;

class EsportaPA01 extends Esporta {

    /**
     * @return PA01ProgrammiCollegatiProceduraAttivazione
     */
    public function execute(Procedura $procedura, $tavola, $enable_ctrl = false) {
        if ($enable_ctrl) {
            if (!$this->em->getRepository('MonitoraggioBundle:PA01ProgrammiCollegatiProceduraAttivazione')->isEsportabile($tavola)) {
                throw new EsportazioneException("Esportazione della procedura non necessaria");
            }
        }
        $procedureAttivazione = $procedura->getMonProcAtt();
        if(\is_null($procedureAttivazione)){
            throw EsportazioneException::proceduraAttivazioneNonDefinita();
        }

        $res = new PA01ProgrammiCollegatiProceduraAttivazione();
        $res->setCodProcAtt($procedureAttivazione ? $procedureAttivazione->getCodProcAtt() : NULL );
        $proceduraProgramma = $procedura->getMonProcedureProgrammi()->first();
        if (!$proceduraProgramma) {
            throw new EsportazioneException('Nessun programma definito per la procedura');
        }
        $res->setTc4Programma($proceduraProgramma->getTc4Programma());
        $res->setImporto($proceduraProgramma->getImporto());
        $res->setFlgCancellazione(self::bool2SN($proceduraProgramma->getDataCancellazione()));
        $res->setEsportazioneStrutture($tavola);
        return $res;
    }

    /**
     * @return PA01ProgrammiCollegatiProceduraAttivazione
     */
    public function importa($input_array) {
        if (\count($input_array) != 4) {
            throw new EsportazioneException("PA01: Input_array non valido");
        }

        $res = new PA01ProgrammiCollegatiProceduraAttivazione();
        $res->setCodProcAtt($input_array[0]);
        $tc4 = $this->em->getRepository('MonitoraggioBundle:TC4Programma')->findOneBy(array('cod_programma' => $input_array[1]));
        if (\is_null($tc4)) {
            throw new EsportazioneException("Programma non valido");
        }
        $res->setTc4Programma($tc4);
        $res->setImporto($input_array[2]);
        $res->setFlgCancellazione($input_array[3]);
        return $res;
    }

}
