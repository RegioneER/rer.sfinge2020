<?php

namespace MonitoraggioBundle\GestoriEsportazione\EsportaElementi;

use Doctrine\Common\Collections\ArrayCollection;
use MonitoraggioBundle\Exception\EsportazioneException;
use MonitoraggioBundle\Entity\TC16LocalizzazioneGeografica;
use MonitoraggioBundle\Entity\FN00Finanziamento;
use RichiesteBundle\Entity\Richiesta;
use AttuazioneControlloBundle\Entity\Finanziamento;

/**
 * @author vbuscemi
 */
class EsportaFN00 extends Esporta {
    /**
     * @return  ArrayCollection|FN00Finanziamento[]
     * @param mixed $tavola
     * @param mixed $enable_ctrl
     */
    public function execute(Richiesta $richiesta, $tavola, $enable_ctrl = false) {
        if ($enable_ctrl) {
            if (!$this->em->getRepository('MonitoraggioBundle:FN00Finanziamento')->isEsportabile($tavola->getMonitoraggioConfigurazioneEsportazione(), $richiesta)) {
                throw EsportazioneException::richiestaNonEsportabile('FN00', $richiesta);
            }
        }
        $nessunaLocalizzazione = $this->em->getRepository('MonitoraggioBundle:TC16LocalizzazioneGeografica')->findOneBy(['codice_regione' => '999']);
        $array = new ArrayCollection();
        $finanziamenti = $richiesta->getMonFinanziamenti();
        /** @var Finanziamento $finanziamento */
        foreach ($finanziamenti as $finanziamento) {
            $fin = new FN00Finanziamento();
            $fin->setCodLocaleProgetto($richiesta->getProtocollo() ?: $richiesta->getId());
            $fin->setTc33FonteFinanziaria($finanziamento->getTc33FonteFinanziaria());
            $fin->setTc35Norma($finanziamento->getTc35Norma());
            $fin->setTc34DeliberaCipe($finanziamento->getTc34DeliberaCipe());
            $localizzazione = $finanziamento->getTc16LocalizzazioneGeografica() ?: $nessunaLocalizzazione;           
            $fin->setTc16LocalizzazioneGeografica($localizzazione);
            $cofinanziatore = $finanziamento->getCofinanziatore();
            $fin->setCfCofinanz($cofinanziatore ? $cofinanziatore->getCodiceFiscale() : '9999');
            $fin->setImporto($finanziamento->getImporto());
            $fin->setFlgCancellazione(self::bool2SN($finanziamento->getDataCancellazione()));
            $fin->setEsportazioneStrutture($tavola);
            $array->add($fin);
        }

        return $array;
    }

    /**
     * @return FN00Finanziamento
     * @param mixed $input_array
     */
    public function importa($input_array) {
        if (is_null($input_array) || !is_array($input_array) || 8 != count($input_array)) {
            throw new EsportazioneException('FN00: Input_array non valido');
        }

        $res = new FN00Finanziamento();
        $res->setCodLocaleProgetto($input_array[0]);
        $tc33 = $this->em->getRepository('MonitoraggioBundle:TC33FonteFinanziaria')->findOneBy(['cod_fondo' => $input_array[1]]);
        if (\is_null($tc33)) {
            throw new EsportazioneException('Fonte finanziaria non valida');
        }
        $res->setTc33FonteFinanziaria($tc33);
        $tc35 = $this->em->getRepository('MonitoraggioBundle:TC35Norma')->findOneBy(['cod_norma' => $input_array[2]]);
        if (\is_null($tc35)) {
            throw new EsportazioneException('Norma non valida');
        }
        $res->setTc35Norma($tc35);
        $tc34 = $this->em->getRepository('MonitoraggioBundle:TC34DeliberaCIPE')->findOneBy(['cod_del_cipe' => $input_array[3]]);
        if (\is_null($tc34)) {
            throw new EsportazioneException('Delibera CIPE non valida');
        }
        $res->setTc34DeliberaCipe($tc34);

        $codice_localizzazione = $input_array[4];
        $codici = TC16LocalizzazioneGeografica::GetCodici($codice_localizzazione);
        list($codice_regione, $codice_provincia, $codice_comune) = $codici;

        $tc16 = $this->em->getRepository('MonitoraggioBundle:TC16LocalizzazioneGeografica')->findOneBy(['codice_regione' => $codice_regione, 'codice_provincia' => $codice_provincia, 'codice_comune' => $codice_comune]);
        if (\is_null($tc16)) {
            throw new EsportazioneException('Localizzazione geografica non valida');
        }
        $res->setTc16LocalizzazioneGeografica($tc16);
        $res->setCfCofinanz($input_array[5]);
        $res->setImporto(self::convertNumberFromString($input_array[6]));
        $res->setFlgCancellazione($input_array[7]);

        return $res;
    }
}
