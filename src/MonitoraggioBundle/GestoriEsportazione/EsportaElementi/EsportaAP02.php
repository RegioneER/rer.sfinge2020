<?php

namespace MonitoraggioBundle\GestoriEsportazione\EsportaElementi;

use MonitoraggioBundle\Exception\EsportazioneException;
use MonitoraggioBundle\Entity\AP02InformazioniGenerali;
use RichiesteBundle\Entity\Richiesta;

/**
 * @author vbuscemi
 */
class EsportaAP02 extends Esporta {
    /**
     * @return AP02InformazioniGenerali
     */
    public function execute(Richiesta $richiesta, $tavola, $enable_ctrl = false) {
        if ($enable_ctrl) {
            if (!$this->em->getRepository('MonitoraggioBundle:AP02InformazioniGenerali')->isEsportabile($tavola->getMonitoraggioConfigurazioneEsportazione())) {
                throw EsportazioneException::richiestaNonEsportabile('AP02', $richiesta);
            }
        }
        $res = new AP02InformazioniGenerali();
        $res->setCodLocaleProgetto($richiesta->getProtocollo());
        $res->setTc7ProgettoComplesso($richiesta->getMonProgettoComplesso());
        $res->setTc8GrandeProgetto($richiesta->getMonGrandeProgetto());
        $res->setGeneratoreEntrate($richiesta->getMonGeneratoreEntrate());
        $res->setTc9TipoLivelloIstituzione($richiesta->getMonLivIstituzioneStrFin());
        $res->setFondoDiFondi($richiesta->getMonFondoDiFondi());

        $tipoLocalizzazione = $richiesta->getMonTipoLocalizzazione();
        if (\is_null($tipoLocalizzazione)) {
            throw EsportazioneException::richiestaSenzaTipoLocalizzazione($richiesta);
        }
        $res->setTc10TipoLocalizzazione($tipoLocalizzazione);

        $gruppoVulnerabile = $richiesta->getMonGruppoVulnerabile();
        if (\is_null($gruppoVulnerabile)) {
            throw EsportazioneException::richiestaSenzaGruppoVulnerabile($richiesta);
        }
        $res->setTc13GruppoVulnerabileProgetto($gruppoVulnerabile);
        $res->setFlgCancellazione(self::bool2SN($richiesta->getDataCancellazione()));
        $res->setEsportazioneStrutture($tavola);
        return $res;
    }

    public function importa($input_array) {
        if (is_null($input_array) || !is_array($input_array) || 9 != count($input_array)) {
            throw new EsportazioneException("AP02: Input_array non valido");
        }

        $res = new AP02InformazioniGenerali();
        $res->setCodLocaleProgetto($input_array[0]);
        $tc7 = $this->em->getRepository('MonitoraggioBundle:TC7ProgettoComplesso')->findOneBy(['cod_prg_complesso' => $input_array[1]]);
        $res->setTc7ProgettoComplesso($tc7);
        $tc8 = $this->em->getRepository('MonitoraggioBundle:TC8GrandeProgetto')->findOneBy(['grande_progetto' => $input_array[2]]);
        $res->setTc8GrandeProgetto($tc8);
        $res->setGeneratoreEntrate($input_array[3]);
        $tc9 = $this->em->getRepository('MonitoraggioBundle:TC9TipoLivelloIstituzione')->findOneBy(['liv_istituzione_str_fin' => $input_array[4]]);
        $res->setTc9TipoLivelloIstituzione($tc9);
        $res->setFondoDiFondi($input_array[5]);
        $tc10 = $this->em->getRepository('MonitoraggioBundle:TC10TipoLocalizzazione')->findOneBy(['tipo_localizzazione' => $input_array[6]]);
        if (\is_null($tc10)) {
            throw new EsportazioneException("Tipo localizzazione non valida");
        }
        $res->setTc10TipoLocalizzazione($tc10);
        $tc13 = $this->em->getRepository('MonitoraggioBundle:TC13GruppoVulnerabileProgetto')->findOneBy(['cod_vulnerabili' => $input_array[7]]);
        if (\is_null($tc13)) {
            throw new EsportazioneException("Gruppo vulnerabile progetto non valido");
        }
        $res->setTc13GruppoVulnerabileProgetto($tc13);
        $res->setFlgCancellazione($input_array[8]);
        return $res;
    }
}
