<?php

namespace MonitoraggioBundle\GestoriEsportazione\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\Esporta;
use Doctrine\Common\Collections\ArrayCollection;
use MonitoraggioBundle\Exception\EsportazioneException;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Entity\PR00IterProgetto;

/**
 * @author vbuscemi
 */
class EsportaPR00 extends Esporta {

    /**
     * @return ArrayCollection|PR00IterProgetto[]
     */
    public function execute(Richiesta $richiesta, $tavola, $enable_ctrl = false) {
        if ($enable_ctrl) {
            if (!$this->em->getRepository('MonitoraggioBundle:PR00IterProgetto')->isEsportabile($tavola->getMonitoraggioConfigurazioneEsportazione(), $richiesta)) {
                throw EsportazioneException::richiestaNonEsportabile('PR00', $richiesta);
            }
        }
        $arrayRisultato = new ArrayCollection();
        foreach ($richiesta->getVociFaseProcedurale() as $fase) {
            $res = new PR00IterProgetto();
            $tc46 = $fase->getFaseProcedurale()->getFaseNatura()->getDefinizione();
            if(\is_null($tc46)){
                throw new EsportazioneException("Definizione fase procedura mancancante");
            }
            $res->setCodLocaleProgetto($richiesta->getProtocollo());
            $res->setTc46FaseProcedurale($tc46);
            $res->setDataInizioPrevista($fase->getDataAvvioPrevista());
            $res->setDataInizioEffettiva($fase->getDataAvvioEffettivo());
            $res->setDataFinePrevista($fase->getDataConclusionePrevista());
            $res->setDataFineEffettiva($fase->getDataConclusioneEffettiva());
            $res->setFlgCancellazione(self::bool2SN(!is_null($fase->getDataCancellazione())));
            $res->setEsportazioneStrutture($tavola);
            $arrayRisultato->add($res);
        }
        return $arrayRisultato;
    }

    /**
     * @return PR00IterProgetto
     */
    public function importa($input_array) {
        if (is_null($input_array) || !is_array($input_array) || count($input_array) != 7) {
            throw new EsportazioneException("PR00: Input_array non valido");
        }

        $res = new PR00IterProgetto();
        $res->setCodLocaleProgetto($input_array[0]);
        $tc46 = $this->em->getRepository('MonitoraggioBundle:TC46FaseProcedurale')->findOneBy(array('cod_fase' => $input_array[1]));
        if (\is_null($tc46)) {
            throw new EsportazioneException("Fase procedurale non valida");
        }
        $res->setTc46FaseProcedurale($tc46);
        $data_inizio_prevista = $this->createFromFormatV2($input_array[2]);
        $res->setDataInizioPrevista($data_inizio_prevista);
        $data_inizio_effettiva = $this->createFromFormatV2($input_array[3]);
        $res->setDataInizioEffettiva($data_inizio_effettiva);
        $data_fine_prevista = $this->createFromFormatV2($input_array[4]);
        $res->setDataFinePrevista($data_fine_prevista);
        $data_fine_effettiva = $this->createFromFormatV2($input_array[5]);
        $res->setDataFineEffettiva($data_fine_effettiva);
        $res->setFlgCancellazione($input_array[6]);
        return $res;
    }

}
