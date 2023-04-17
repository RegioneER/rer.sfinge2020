<?php

namespace MonitoraggioBundle\GestoriEsportazione\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\Esporta;
use Doctrine\Common\Collections\ArrayCollection;
use MonitoraggioBundle\Exception\EsportazioneException;
use MonitoraggioBundle\Entity\FN01CostoAmmesso;
use RichiesteBundle\Entity\Richiesta;


/**
 * @author vbuscemi
 */
class EsportaFN01 extends Esporta {

    /**
     * @return ArrayCollection
     */
    public function execute(Richiesta $richiesta, $tavola, $enable_ctrl = false) {
        if ($enable_ctrl) {
            if (!$this->em->getRepository('MonitoraggioBundle:FN01CostoAmmesso')->isEsportabile($tavola->getMonitoraggioConfigurazioneEsportazione(), $richiesta)) {
                throw EsportazioneException::richiestaNonEsportabile('FN01',$richiesta);
            }
        }
        $array = new ArrayCollection();
        foreach ($richiesta->getMonProgrammi() as $programma) {
            if ($programma->getDataCancellazione()) {
                continue;
            }

            foreach ($programma->getMonLivelliGerarchici() as $livelloGerarchico) {

                $res = new FN01CostoAmmesso();
                $res->setCodLocaleProgetto($richiesta->getProtocollo());
                $res->setTc4Programma($programma->getTc4Programma());
                $res->setTc36LivelloGerarchico($livelloGerarchico->getTc36LivelloGerarchico());
                $res->setImportoAmmesso($livelloGerarchico->getImportoCostoAmmesso());
                $res->setFlgCancellazione(self::bool2SN($livelloGerarchico->getDataCancellazione()));
                $res->setEsportazioneStrutture($tavola);
                $array->add($res);
            }
        }
        return $array;
    }

    /**
     * @return FN01CostoAmmesso
     */
    public function importa($input_array) {
        if (is_null($input_array) || !is_array($input_array) || count($input_array) != 5) {
            throw new EsportazioneException("FN01: Input_array non valido");
        }

        $res = new FN01CostoAmmesso();
        $res->setCodLocaleProgetto($input_array[0]);
        $tc4 = $this->em->getRepository('MonitoraggioBundle:TC4Programma')->findOneBy(array('cod_programma' => $input_array[1]));
        if (\is_null($tc4)) {
            throw new EsportazioneException("Programma non valido");
        }
        $res->setTc4Programma($tc4);
        $tc36 = $this->em->getRepository('MonitoraggioBundle:TC36LivelloGerarchico')->findOneBy(array('cod_liv_gerarchico' => $input_array[2]));
        if (\is_null($tc36)) {
            throw new EsportazioneException("Livello gerarchico non valido");
        }
        $res->setTc36LivelloGerarchico($tc36);
        $res->setImportoAmmesso($input_array[3]);
        $res->setFlgCancellazione($input_array[4]);
        return $res;
    }

}
