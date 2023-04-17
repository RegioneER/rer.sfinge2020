<?php

namespace MonitoraggioBundle\GestoriEsportazione\EsportaElementi;

use Doctrine\Common\Collections\ArrayCollection;
use MonitoraggioBundle\Exception\EsportazioneException;
use MonitoraggioBundle\Entity\FN05ImpegniAmmessi;
use RichiesteBundle\Entity\Richiesta;

class EsportaFN05 extends Esporta {
    public function execute(Richiesta $richiesta, $tavola, $enable_ctrl = false) {
        if ($enable_ctrl) {
            if (!$this->em->getRepository('MonitoraggioBundle:FN04Impegni')->isEsportabile($tavola->getMonitoraggioConfigurazioneEsportazione(), $richiesta)) {
                throw EsportazioneException::richiestaNonEsportabile('FN05', $richiesta);
            }
        }
        $arrayRisultato = new ArrayCollection();
        foreach ($richiesta->getMonImpegni() as $impegno) {
            foreach ($impegno->getMonImpegniAmmessi() as $impegnoAmmesso) {
                $livelloGerarchico = $impegnoAmmesso->getRichiestaLivelloGerarchico();
                $res = new FN05ImpegniAmmessi();

                $res->setCodLocaleProgetto($richiesta->getProtocollo());
                $res->setCodImpegno($impegno->getId());
                $res->setTc4Programma($livelloGerarchico->getRichiestaProgramma()->getTc4Programma());
                $res->setTc36LivelloGerarchico($livelloGerarchico->getTc36LivelloGerarchico());
                $res->setTipologiaImpegno($impegno->getTipologiaImpegno());
                $res->setDataImpegno($impegno->getDataImpegno());
                $res->setDataImpAmm($impegnoAmmesso->getDataImpAmm());
                $res->setTipologiaImpAmm($impegnoAmmesso->getTipologiaImpAmm());
                $res->setTc38CausaleDisimpegnoAmm($impegnoAmmesso->getTc38CausaleDisimpegnoAmm());
                $res->setImportoImpAmm($impegnoAmmesso->getImportoImpAmm());
                $res->setNoteImp($impegnoAmmesso->getNoteImp());
                $res->setFlgCancellazione(self::bool2SN(!is_null($impegnoAmmesso->getDataCancellazione())));
                $res->setEsportazioneStrutture($tavola);
                $arrayRisultato->add($res);
            }
        }
        return $arrayRisultato;
    }

    public function importa($input_array) {
        if (is_null($input_array) || !is_array($input_array) || 12 != count($input_array)) {
            throw new EsportazioneException("FN05: Input_array non valido");
        }

        $res = new FN05ImpegniAmmessi();
        $res->setCodLocaleProgetto($input_array[0]);
        $res->setCodImpegno($input_array[1]);
        $res->setTipologiaImpegno($input_array[2]);
        $data_impegno = $this->createFromFormatV2($input_array[3]);
        $res->setDataImpegno($data_impegno);
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
        $data_impegno_ammesso = $this->createFromFormatV2($input_array[6]);
        $res->setDataImpAmm($data_impegno_ammesso);
        $res->setTipologiaImpAmm($input_array[7]);
        $tc38 = $this->em->getRepository('MonitoraggioBundle:TC38CausaleDisimpegno')->findOneBy(['causale_disimpegno' => $input_array[8]]);
        if (\is_null($tc38) && \in_array($res->getTipologiaImpAmm(), ['D', 'D-TR'])) {
            throw new EsportazioneException("Causale disimpegno non valido");
        }
        $res->setTc38CausaleDisimpegnoAmm($tc38);
        $res->setImportoImpAmm(self::convertNumberFromString($input_array[9]));
        $res->setNoteImp($input_array[10]);
        $res->setFlgCancellazione($input_array[11]);
        return $res;
    }
}
