<?php

namespace MonitoraggioBundle\GestoriEsportazione\EsportaElementi;

use Doctrine\Common\Collections\ArrayCollection;
use MonitoraggioBundle\Exception\EsportazioneException;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Entity\FN07PagamentiAmmessi;

/**
 * @author vbuscemi
 */
class EsportaFN07 extends Esporta {
    public function execute(Richiesta $richiesta, $tavola, $enable_ctrl = false) {
        if ($enable_ctrl) {
            if (!$this->em->getRepository('MonitoraggioBundle:FN07PagamentiAmmessi')->isEsportabile($tavola->getMonitoraggioConfigurazioneEsportazione(), $richiesta)) {
                throw EsportazioneException::richiestaNonEsportabile('FN07', $richiesta);
            }
        }
        $arrayRisultato = new ArrayCollection();
        foreach ($richiesta->getMonRichiestePagamento() as $pagamento) {
            foreach ($pagamento->getPagamentiAmmessi() as $pagamento_ammesso) {
                if (\is_null($pagamento_ammesso->getCausale())) {
                    throw new EsportazioneException("Causale non presente");
                }
                if (\is_null($pagamento_ammesso->getLivelloGerarchico())) {
                    throw new EsportazioneException("Causale non presente");
                }

                $res = new FN07PagamentiAmmessi();

                $res->setCodLocaleProgetto($richiesta->getProtocollo());
                $res->setCodPagamento($pagamento_ammesso->getRichiestaPagamento()->getId());
                $res->setTipologiaPag($pagamento_ammesso->getRichiestaPagamento()->getTipologiaPagamento());
                $res->setDataPagamento($pagamento_ammesso->getRichiestaPagamento()->getDataPagamento());
                $res->setTc4Programma($pagamento_ammesso->getLivelloGerarchico()->getRichiestaProgramma()->getTc4Programma());
                $res->setTc36LivelloGerarchico($pagamento_ammesso->getLivelloGerarchico()->getTc36LivelloGerarchico());
                $res->setDataPagAmm($pagamento_ammesso->getDataPagamento());
                $res->setTipologiaPagAmm($pagamento_ammesso->getCausale()->getTipologiaPagamento());
                $res->setTc39CausalePagamento($pagamento_ammesso->getCausale());
                $res->setImportoPagAmm($pagamento_ammesso->getImporto());
                $res->setNotePag($pagamento_ammesso->getNote());
                $res->setFlgCancellazione(self::bool2SN(!is_null($pagamento_ammesso->getDataCancellazione())));
                $res->setEsportazioneStrutture($tavola);
                $arrayRisultato->add($res);
            }
        }
        return $arrayRisultato;
    }

    /**
     * @return FN07PagamentiAmmessi
     */
    public function importa($input_array) {
        if (is_null($input_array) || !is_array($input_array) || 12 != count($input_array)) {
            throw new EsportazioneException("FN07: Input_array non valido");
        }

        $res = new FN07PagamentiAmmessi();
        $res->setCodLocaleProgetto($input_array[0]);
        $res->setCodPagamento($input_array[1]);
        $res->setTipologiaPag($input_array[2]);
        $data_pagamento = $this->createFromFormatV2($input_array[3]);
        $res->setDataPagamento($data_pagamento);
        $tc4 = $this->em->getRepository('MonitoraggioBundle:TC4Programma')->findOneBy(['cod_programma' => $input_array[4]]);
        if (\is_null($tc4)) {
            throw new EsportazioneException("Programma non valido");
        }
        $res->setTc4Programma($tc4);
        $tc36 = $this->em->getRepository('MonitoraggioBundle:TC36LivelloGerarchico')->findOneBy(['cod_liv_gerarchico' => $input_array[5]]);
        if (\is_null($tc36)) {
            throw new EsportazioneException("Livello gerarchico non valido");
        }
        $res->setTc36LivelloGerarchico($tc36);
        $data_pagamento_ammesso = $this->createFromFormatV2($input_array[6]);
        $res->setDataPagAmm($data_pagamento_ammesso);
        $res->setTipologiaPagAmm($input_array[7]);
        $tc39 = $this->em->getRepository('MonitoraggioBundle:TC39CausalePagamento')->findOneBy(['causale_pagamento' => $input_array[8]]);
        if (\is_null($tc39)) {
            throw new EsportazioneException("Causale pagamento non valido");
        }
        $res->setTc39CausalePagamento($tc39);
        $res->setImportoPagAmm($input_array[9]);
        $res->setNotePag($input_array[10]);
        $res->setFlgCancellazione($input_array[11]);
        return $res;
    }
}
