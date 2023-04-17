<?php

namespace MonitoraggioBundle\GestoriEsportazione\EsportaElementi;

use MonitoraggioBundle\Exception\EsportazioneException;
use Doctrine\Common\Collections\ArrayCollection;
use MonitoraggioBundle\Entity\PG00ProcedureAggiudicazione;

/**
 * @author vbuscemi
 */
class EsportaPG00 extends Esporta {
    /**
     * @return PG00ProcedureAggiudicazione[]|ArrayCollection
     */
    public function execute(\RichiesteBundle\Entity\Richiesta $richiesta, $tavola, $enable_ctrl = false) {
        if ($enable_ctrl) {
            if (!$this->em->getRepository('MonitoraggioBundle:PG00ProcedureAggiudicazione')->isEsportabile($tavola->getMonitoraggioConfigurazioneEsportazione(), $richiesta)) {
                throw EsportazioneException::richiestaNonEsportabile('PG00', $richiesta);
            }
        }
        $resCollection = new ArrayCollection();

        foreach ($richiesta->getMonProcedureAggiudicazione() as $value) {
            $res = new PG00ProcedureAggiudicazione();
            $res->setCodLocaleProgetto($richiesta->getProtocollo());
            $res->setCodProcAgg("{$richiesta->getProtocollo()}_{$value->getProgressivo()}");
            $res->setCig($value->getCig());
            $res->setTc22MotivoAssenzaCig($value->getMotivoAssenzaCig());
            $res->setDescrProceduraAgg($value->getDescrizioneProceduraAggiudicazione());
            $res->setTc23TipoProceduraAggiudicazione($value->getTipoProceduraAggiudicazione());
            $res->setImportoProceduraAgg($value->getImportoProceduraAggiudicazione());
            $res->setDataPubblicazione($value->getDataPubblicazione());
            $res->setImportoAggiudicato($value->getImportoAggiudicato());
            $res->setDataAggiudicazione($value->getDataAggiudicazione());
            $res->setFlgCancellazione(self::bool2SN($value->getDataCancellazione()));
            $res->setEsportazioneStrutture($tavola);
            $resCollection->add($res);
        }

        return $resCollection;
    }

    public function importa(array $input_array) {
        if (11 != \count($input_array)) {
            throw new EsportazioneException("PG00: Input_array non valido");
        }

        $res = new PG00ProcedureAggiudicazione();
        $res->setCodLocaleProgetto($input_array[0]);
        $res->setCodProcAgg($input_array[1]);
        $cig = $input_array[2];
        $res->setCig($cig);
        $tc22 = $this->em->getRepository('MonitoraggioBundle:TC22MotivoAssenzaCIG')->findOneBy(['motivo_assenza_cig' => $input_array[3]]);
        if (\is_null($tc22) && (!$cig || 9999 == $cig)) {
            throw new EsportazioneException("Motivo assenza CIG non valido");
        }
        $res->setTc22MotivoAssenzaCig($tc22);
        $res->setDescrProceduraAgg($input_array[4]);
        $tc23 = $this->em->getRepository('MonitoraggioBundle:TC23TipoProceduraAggiudicazione')->findOneBy(['tipo_proc_agg' => $input_array[5]]);
        if (\is_null($tc23) && (!$cig || 9999 == $cig)) {
            throw new EsportazioneException("Tipo procedura aggiudicazione non valida");
        }
        $res->setTc23TipoProceduraAggiudicazione($tc23);
        $res->setImportoProceduraAgg($input_array[6]);
        $data_pubblicazione = $this->createFromFormatV2($input_array[7]);
        $res->setDataPubblicazione($data_pubblicazione);
        $res->setImportoAggiudicato($input_array[8]);
        $data_aggiudicazione = $this->createFromFormatV2($input_array[9]);
        $res->setDataAggiudicazione($data_aggiudicazione);
        $res->setFlgCancellazione($input_array[10]);
        return $res;
    }
}
