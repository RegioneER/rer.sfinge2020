<?php

namespace MonitoraggioBundle\GestoriEsportazione\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\Esporta;
use MonitoraggioBundle\Exception\EsportazioneException;
use MonitoraggioBundle\Entity\AP06LocalizzazioneGeografica;
use RichiesteBundle\Entity\Richiesta;

/**
 * @author vbuscemi
 */
class EsportaAP06 extends Esporta {

    /**
     * @return AP06LocalizzazioneGeografica
     */
    public function execute(Richiesta $richiesta, $tavola, $enable_ctrl = false) {
        if ($enable_ctrl) {
            if (!$this->em->getRepository('MonitoraggioBundle:AP06LocalizzazioneGeografica')->isEsportabile($tavola->getMonitoraggioConfigurazioneEsportazione(), $richiesta)) {
                throw EsportazioneException::richiestaNonEsportabile('AP06', $richiesta);
            }
        }
        $localizzazione = $richiesta->getMonLocalizzazioneGeografica()->get(0);
        if (is_null($localizzazione)) {
            throw EsportazioneException::richiestaSenzaLocalizzazioneGeografica($richiesta);
        }
        $res = new AP06LocalizzazioneGeografica();
        $res->setCodLocaleProgetto($richiesta->getProtocollo());

        $res->setLocalizzazioneGeografica($localizzazione->getLocalizzazione());
        $res->setIndirizzo($localizzazione->getIndirizzo());
        $res->setCodCap($localizzazione->getCap());
        $res->setFlgCancellazione(self::bool2SN($localizzazione->getDataCancellazione()));
        $res->setEsportazioneStrutture($tavola);

        return $res;
    }

    /**
     * @return AP06LocalizzazioneGeografica
     */
    public function importa($input_array) {
        if (is_null($input_array) || !is_array($input_array) || count($input_array) != 7) {
            throw new EsportazioneException("AP06: Input_array non valido");
        }

        $res = new AP06LocalizzazioneGeografica();
        $res->setCodLocaleProgetto($input_array[0]);
        $tc16 = $this->em->getRepository('MonitoraggioBundle:TC16LocalizzazioneGeografica')->findOneBy(
            array('codice_regione' => $input_array[1], 'codice_provincia' => $input_array[2], 'codice_comune' => substr($input_array[3],-3)));
        if (\is_null($tc16)) {
            throw new EsportazioneException("Localizzazione geografica non valida");
        }
        $res->setLocalizzazioneGeografica($tc16);
        $res->setIndirizzo($input_array[4]);
        $res->setCodCap($input_array[5]);
        $res->setFlgCancellazione($input_array[6]);
        return $res;
    }

}
