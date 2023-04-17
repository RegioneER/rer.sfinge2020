<?php

namespace MonitoraggioBundle\GestoriEsportazione\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\Esporta;
use Doctrine\Common\Collections\ArrayCollection;
use MonitoraggioBundle\Exception\EsportazioneException;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Entity\PR01StatoAttuazioneProgetto;


/**
 * @author vbuscemi
 */
class EsportaPR01 extends Esporta {

    /**
     * @return ArrayCollection|PR01StatoAttuazioneProgetto[]
     */
    public function execute(Richiesta $richiesta, $tavola, $enable_ctrl = false) {
        if ($enable_ctrl) {
            if (!$this->em->getRepository('MonitoraggioBundle:PR01StatoAttuazioneProgetto')->isEsportabile($tavola->getMonitoraggioConfigurazioneEsportazione(), $richiesta)) {
                throw EsportazioneException::richiestaNonEsportabile('PR01', $richiesta);
            }
        }
        $arrayRisultato = new ArrayCollection();
        foreach ($richiesta->getMonStatoProgetti() as $stato_progetto) {
            $res = new PR01StatoAttuazioneProgetto();

            $res->setCodLocaleProgetto($richiesta->getProtocollo());
            $res->setTc47StatoProgetto($stato_progetto->getStatoProgetto());
            $res->setDataRiferimento($stato_progetto->getDataRiferimento());
            $res->setFlgCancellazione(self::bool2SN(!is_null($stato_progetto->getDataCancellazione())));
            $res->setEsportazioneStrutture($tavola);
            $arrayRisultato->add($res);
        }
        return $arrayRisultato;
    }

    /**
     * @return PR01StatoAttuazioneProgetto
     */
    public function importa(array $input_array) {
        if (\count($input_array) != 4) {
            throw new EsportazioneException("PR01: Input_array non valido");
        }

        $res = new PR01StatoAttuazioneProgetto();
        $res->setCodLocaleProgetto($input_array[0]);
        $tc47 = $this->em->getRepository('MonitoraggioBundle:TC47StatoProgetto')->findOneBy(array('stato_progetto' => $input_array[1]));
        if (\is_null($tc47)) {
            throw new EsportazioneException("Stato progetto non valid0");
        }
        $res->setTc47StatoProgetto($tc47);
        $data = $this->createFromFormatV2($input_array[2]);
        $res->setDataRiferimento($data);
        $res->setFlgCancellazione($input_array[3]);
        return $res;
    }

}
