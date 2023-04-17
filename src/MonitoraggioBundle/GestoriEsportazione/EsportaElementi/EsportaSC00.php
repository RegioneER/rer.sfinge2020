<?php

namespace MonitoraggioBundle\GestoriEsportazione\EsportaElementi;

use MonitoraggioBundle\Exception\EsportazioneException;
use RichiesteBundle\Entity\Richiesta;
use Doctrine\Common\Collections\ArrayCollection;
use MonitoraggioBundle\Entity\SC00SoggettiCollegati;

class EsportaSC00 extends Esporta {
    /**
     * @return \MonitoraggioBundle\Entity\SC00SoggettiCollegati
     */
    public function execute(Richiesta $richiesta, $tavola, $enable_ctrl = false) {
        if ($enable_ctrl) {
            if (!$this->em->getRepository('MonitoraggioBundle:SC00SoggettiCollegati')->isEsportabile($tavola->getMonitoraggioConfigurazioneEsportazione(), $richiesta)) {
                throw EsportazioneException::richiestaNonEsportabile('SC00', $richiesta);
            }
        }
        $res = new ArrayCollection();
        foreach ($richiesta->getMonSoggettiCorrelati() as $soggettoCorrelato) {
            $soggetto = $soggettoCorrelato->getSoggetto();
            $elemento = new SC00SoggettiCollegati();
            $formaGiuridica = $soggetto->getFormaGiuridica();
            $elemento->setTc24RuoloSoggetto($soggettoCorrelato->getTc24RuoloSoggetto())
                    ->setTc25FormaGiuridica(is_null($formaGiuridica) ? null : $formaGiuridica->getTc25FormaGiuridica())
                    ->setTc26Ateco($soggetto->getCodiceAteco())
                    ->setCodLocaleProgetto($richiesta->getProtocollo())
                    ->setCodiceFiscale($soggetto->getCodiceFiscale())
                    ->setFlagSoggettoPubblico(self::bool2SN(\is_null($formaGiuridica) ? null : $formaGiuridica->getSoggettoPubblico(), true))
                    ->setCodUniIpa($soggettoCorrelato->getCodUniIpa())
                    ->setDenominazioneSog($soggetto->getDenominazione())
                    ->setNote($soggettoCorrelato->getNote())
                    ->setFlgCancellazione(self::bool2SN(!is_null($soggettoCorrelato->getDataCancellazione())))
                    ->setEsportazioneStrutture($tavola);
            $res->add($elemento);
        }

        return $res;
    }

    public function importa($input_array) {
        if (is_null($input_array) || !is_array($input_array) || 10 != count($input_array)) {
            throw new EsportazioneException("SC00: Input_array non valido");
        }

        $res = new SC00SoggettiCollegati();
        $res->setCodLocaleProgetto($input_array[0]);
        $tc24 = $this->em->getRepository('MonitoraggioBundle:TC24RuoloSoggetto')->findOneBy(['cod_ruolo_sog' => $input_array[1]]);
        if (\is_null($tc24)) {
            throw new EsportazioneException("Ruolo soggetto non valido");
        }
        $res->setTc24RuoloSoggetto($tc24);
        $res->setCodiceFiscale($input_array[2]);
        $res->setFlagSoggettoPubblico($input_array[3]);
        $res->setCodUniIpa($input_array[4]);
        $res->setDenominazioneSog($input_array[5]);
        if (!$res->getDenominazioneSog() && ('S' == $res->getFlagSoggettoPubblico() || '*' == substr($res->getCodiceFiscale(), 15, 1))) {
            throw new EsportazioneException("Denominazione non specificata");
        }
        $tc25 = $this->em->getRepository('MonitoraggioBundle:TC25FormaGiuridica')->findOneBy(['forma_giuridica' => $input_array[6]]);
        if (\is_null($tc25)) {
            throw new EsportazioneException("Forma giuridica non valida");
        }
        $res->setTc25FormaGiuridica($tc25);
        $tc26 = $this->em->getRepository('MonitoraggioBundle:TC26Ateco')->findOneBy(['cod_ateco_anno' => $input_array[7]]);
        if ('*' == substr($res->getCodiceFiscale(), 15, 1) && \is_null($tc26)) {
            throw new EsportazioneException("Ateco non valido");
        }
        $res->setTc26Ateco($tc26);
        $res->setNote($input_array[8]);
        $res->setFlgCancellazione($input_array[9]);
        return $res;
    }
}
