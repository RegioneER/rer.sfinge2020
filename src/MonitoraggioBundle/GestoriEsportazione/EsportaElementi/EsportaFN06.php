<?php

namespace MonitoraggioBundle\GestoriEsportazione\EsportaElementi;

use Doctrine\Common\Collections\ArrayCollection;
use MonitoraggioBundle\Exception\EsportazioneException;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Entity\FN06Pagamenti;

class EsportaFN06 extends Esporta {
    /**
     * @return ArrayCollection|FN06Pagamenti[]
     */
    public function execute(Richiesta $richiesta, $tavola, $enable_ctrl = false) {
        if ($enable_ctrl) {
            if (!$this->em->getRepository('MonitoraggioBundle:FN06Pagamenti')->isEsportabile($tavola->getMonitoraggioConfigurazioneEsportazione(), $richiesta)) {
                throw EsportazioneException::richiestaNonEsportabile('FN06', $richiesta);
            }
        }
        $arrayRisultato = new ArrayCollection();
        foreach ($richiesta->getMonRichiestePagamento() as $pagamento) {
            $res = new FN06Pagamenti();

            $res->setCodLocaleProgetto($richiesta->getProtocollo());
            $res->setCodPagamento($pagamento->getId());
            $res->setTipologiaPag($pagamento->getTipologiaPagamento());
            $res->setDataPagamento($pagamento->getDataPagamento());
            $res->setImportoPag($pagamento->getImporto());
            $res->setTc39CausalePagamento($pagamento->getCausalePagamento());
            $res->setNotePag($pagamento->getNote());
            $res->setFlgCancellazione(self::bool2SN(!is_null($pagamento->getDataCancellazione())));
            $res->setEsportazioneStrutture($tavola);
            $arrayRisultato->add($res);
        }
        return $arrayRisultato;
    }

    /**
     * @return FN06Pagamenti
     * @throws EsportazioneException
     */
    public function importa($input_array) {
        if (is_null($input_array) || !is_array($input_array) || 8 != count($input_array)) {
            throw new EsportazioneException("FN06: Input_array non valido");
        }

        $res = new FN06Pagamenti();
        $res->setCodLocaleProgetto($input_array[0]);
        $res->setCodPagamento($input_array[1]);
        $res->setTipologiaPag($input_array[2]);
        $data = $this->createFromFormatV2($input_array[3]);
        $res->setDataPagamento($data);
        $res->setImportoPag($input_array[4]);
        $tc39 = $this->em->getRepository('MonitoraggioBundle:TC39CausalePagamento')->findOneBy(['causale_pagamento' => $input_array[5]]);
        if (\is_null($tc39)) {
            throw new EsportazioneException("Causale pagamento non valido");
        }
        $res->setTc39CausalePagamento($tc39);
        $res->setNotePag($input_array[6]);
        $res->setFlgCancellazione($input_array[7]);
        return $res;
    }
}
