<?php

namespace MonitoraggioBundle\GestoriEsportazione\EsportaElementi;

use MonitoraggioBundle\Exception\EsportazioneException;
use MonitoraggioBundle\Entity\AP00AnagraficaProgetti;
use RichiesteBundle\Entity\Richiesta;

/**
 * @author vbuscemi
 */
class EsportaAP00 extends Esporta {
    /**
     * @param Richiesta $richiesta
     * @return AP00AnagraficaProgetti
     */
    public function execute(Richiesta $richiesta, $tavola, $enable_ctrl = false) {
        if ($enable_ctrl) {
            $repository = $this->em->getRepository('MonitoraggioBundle:AP00AnagraficaProgetti');
            if (!$repository->isEsportabile($tavola->getMonitoraggioConfigurazioneEsportazione())) {
                throw EsportazioneException::richiestaNonEsportabile('AP00', $richiesta);
            }
        }
        $res = new AP00AnagraficaProgetti();
        $res->setCodLocaleProgetto($richiesta->getProtocollo());
        $res->setTitoloProgetto(\substr($richiesta->getTitolo(), AP00AnagraficaProgetti::TITOLO_LEN) ?: $richiesta->getTitolo());
        $res->setSintesiPrg(\substr($richiesta->getAbstract(), AP00AnagraficaProgetti::SINTESI_LEN) ?: $richiesta->getAbstract());
        $res->setTc5TipoOperazione($richiesta->getMonTipoOperazione());
        $res->setCup($richiesta->getIstruttoria()->getCodiceCup());
        $res->setTc6TipoAiuto($richiesta->getMonTipoAiuto());
        $res->setDataInizio($richiesta->getAttuazioneControllo()->getDataAvvio());
        $res->setDataFinePrevista($richiesta->getAttuazioneControllo()->getDataTermine());
        $res->setDataFineEffettiva($richiesta->getAttuazioneControllo()->getDataTermineEffettivo());
        $res->setTc48TipoProceduraAttivazioneOriginaria($richiesta->getMonTipoProceduraAttOrig());
        $res->setCodiceProcAttOrig($richiesta->getMonCodProceduraAttOrig());
        $res->setFlgCancellazione(self::bool2SN($richiesta->getDataCancellazione()));
        $res->setEsportazioneStrutture($tavola);

        return $res;
    }

    public function importa($input_array) {
        if (is_null($input_array) || !is_array($input_array) || 12 != count($input_array)) {
            throw new EsportazioneException("AP00: Input_array non valido");
        }

        $res = new AP00AnagraficaProgetti();
        $res->setCodLocaleProgetto($input_array[0]);
        $res->setTitoloProgetto($input_array[1]);
        $res->setSintesiPrg($input_array[2]);
        $tc5 = $this->em->getRepository('MonitoraggioBundle:TC5TipoOperazione')->findOneBy(array('tipo_operazione' => $input_array[3]));
        if (\is_null($tc5)) {
            throw new EsportazioneException("Tipo operazione non valido");
        }
        $res->setTc5TipoOperazione($tc5);
        $res->setCup($input_array[4]);
        $tc6 = $this->em->getRepository('MonitoraggioBundle:TC6TipoAiuto')->findOneBy(array('tipo_aiuto' => $input_array[5]));
        if (\is_null($tc6)) {
            throw new EsportazioneException("Tipo aiuto non valido");
        }
        $res->setTc6TipoAiuto($tc6);
        $data_inizio = $this->createFromFormatV2($input_array[6]);
        $res->setDataInizio($data_inizio);
        $data_fine_prevista = $this->createFromFormatV2($input_array[7]);
        $res->setDataFinePrevista($data_fine_prevista);
        $data_fine_effettiva = $this->createFromFormatV2($input_array[8]);
        $res->setDataFineEffettiva($data_fine_effettiva);
        $tc48 = $this->em->getRepository('MonitoraggioBundle:TC48TipoProceduraAttivazioneOriginaria')->findOneBy(array('tip_proc_att_orig' => $input_array[9]));
        if (\is_null($tc48)) {
            throw new EsportazioneException("Tipo procedura attivazione originaria non valido");
        }
        $res->setTc48TipoProceduraAttivazioneOriginaria($tc48);
        $res->setCodiceProcAttOrig($input_array[10]);
        $res->setFlgCancellazione($input_array[11]);
        return $res;
    }
}
