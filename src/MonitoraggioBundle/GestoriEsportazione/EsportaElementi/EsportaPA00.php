<?php

namespace MonitoraggioBundle\GestoriEsportazione\EsportaElementi;

use MonitoraggioBundle\Exception\EsportazioneException;
use MonitoraggioBundle\Entity\PA00ProcedureAttivazione;
use MonitoraggioBundle\Entity\TC1ProceduraAttivazione;
use SfingeBundle\Entity\Procedura;

class EsportaPA00 extends Esporta
{
    public function execute(Procedura $procedura, $tavola, $enable_ctrl = false)
    {
        if ($enable_ctrl) {
            if (!$this->em->getRepository('MonitoraggioBundle:PA00ProcedureAttivazione')->isEsportabile($tavola)) {
                throw new EsportazioneException('Procedura non esportabile');
            }
        }
        $procedureAttivazione = $procedura->getMonProcAtt();
        if(\is_null($procedureAttivazione)){
            throw EsportazioneException::proceduraAttivazioneNonDefinita();
        }
        $tipoAmministrazione = $procedura->getAmministrazioneEmittente();
        if(\is_null($tipoAmministrazione)){
            throw new EsportazioneException("Amministrazione emittente non definita");
        }

        $res = new PA00ProcedureAttivazione();
        $res->setCodProcAttLocale($procedura->getId());
        $res->setCodProcAtt($procedureAttivazione ? $procedureAttivazione->getCodProcAtt(): NULL );
        $res->setCodAiutoRna($procedura->getMonCodAiutoRna());
        $res->setTc2TipoProceduraAttivazione($procedura->getMonTipoProceduraAttivazione());
        $res->setFlagAiuti(self::bool2SN($procedura->getMonFlagAiuti(), true));
        $res->setDescrProceduraAtt($procedura->getTitolo());
        $res->setTc3ResponsabileProcedura($tipoAmministrazione->getResponsabileProcedura());
        $res->setDenomRespProc($tipoAmministrazione->getDescrizione());
        $res->setDataAvvioProcedura($procedura->getMonDataAvvioProcedura());
        $res->setDataFineProcedura($procedura->getMonDataFineProcedura());
        $res->setEsportazioneStrutture($tavola);
        $res->setFlgCancellazione(self::bool2SN($procedura->getDataCancellazione($procedura)));

        return $res;
    }

    /**
     * @param PA00ProcedureAttivazione $procedura
     *
     * @return TC1ProceduraAttivazione
     */
    private function importaTC1(PA00ProcedureAttivazione $procedura)
    {
        $tc1 = $this->em->getRepository('MonitoraggioBundle:TC1ProceduraAttivazione')->findOneBy(array('cod_proc_att_locale' => $procedura->getCodProcAtt()));
        //TC1 non presente si procede ad inserimento
        if (\is_null($tc1)) {
            $tc1 = new TC1ProceduraAttivazione();
            $tc1->setCodProcAtt($procedura->getCodProcAtt())
            ->setCodProcAttLocale($procedura->getCodProcAttLocale())
            ->setCodAiutoRna($procedura->getCodAiutoRna())
            ->setTipProceduraAtt($procedura->getTc2TipoProceduraAttivazione())
            ->setFlagAiuti($procedura->getFlagAiuti())
            ->setDescrProceduraAtt($procedura->getDescrProceduraAtt())
            ->setTipoRespProc($procedura->getTc3ResponsabileProcedura())
            ->setDenomRespProc($procedura->getDenomRespProc())
            ->setDataAvvioProcedura($procedura->getDataAvvioProcedura())
            ->setDataFineProcedura($procedura->getDataFineProcedura())
            ->setFlagCancellazione($procedura->getFlgCancellazione())
            ->setFlagFesr(false);
            $this->em->persist($tc1);
            $this->em->flush($tc1);
        }

        return $tc1;
    }

    /**
     * @param array $input_array
     *
     * @return PA00ProcedureAttivazione
     */
    public function importa($input_array)
    {
        if (is_null($input_array) || !is_array($input_array) || 11 != count($input_array)) {
            throw new EsportazioneException('PA00: Input_array non valido');
        }

        $res = new PA00ProcedureAttivazione();
        $res->setCodProcAtt($input_array[0]);
        $res->setCodProcAttLocale($input_array[1]);
        $res->setCodAiutoRna($input_array[2]);
        $tc2 = $this->em->getRepository('MonitoraggioBundle:TC2TipoProceduraAttivazione')->findOneBy(array('tip_procedura_att' => $input_array[3]));
        if (\is_null($tc2)) {
            throw new EsportazioneException('Tipo procedura attivazione non valido');
        }
        $res->setTc2TipoProceduraAttivazione($tc2);
        $res->setFlagAiuti($input_array[4]);
        $res->setDescrProceduraAtt($input_array[5]);
        $tc3 = $this->em->getRepository('MonitoraggioBundle:TC3ResponsabileProcedura')->findOneBy(array('cod_tipo_resp_proc' => $input_array[6]));
        if (\is_null($tc3)) {
            throw new EsportazioneException('Responsabile procedura non valido');
        }
        $res->setTc3ResponsabileProcedura($tc3);
        $res->setDenomRespProc($input_array[7]);
        $data_avvio = $this->createFromFormatV2($input_array[8]);
        $res->setDataAvvioProcedura($data_avvio);
        $data_fine = $this->createFromFormatV2($input_array[9]);
        $res->setDataFineProcedura($data_fine);
        $res->setFlgCancellazione($input_array[10]);

        $this->importaTC1($res);

        return $res;
    }
}
