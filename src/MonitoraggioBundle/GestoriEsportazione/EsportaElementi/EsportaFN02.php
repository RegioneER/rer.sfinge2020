<?php

namespace MonitoraggioBundle\GestoriEsportazione\EsportaElementi;

use Doctrine\Common\Collections\ArrayCollection;
use MonitoraggioBundle\Exception\EsportazioneException;
use RichiesteBundle\Entity\Richiesta;
use AttuazioneControlloBundle\Service\IGestoreRichiesteATC;
use MonitoraggioBundle\Entity\FN02QuadroEconomico;

/**
 * @author vbuscemi
 */
class EsportaFN02 extends Esporta {
    /**
     * @param Richiesta $richiesta
     */
    public function execute(Richiesta $richiesta, $tavola, $enable_ctrl = false) {
        if ($enable_ctrl) {
            if (!$this->em->getRepository('MonitoraggioBundle:FN02QuadroEconomico')->isEsportabile($tavola->getMonitoraggioConfigurazioneEsportazione())) {
                throw EsportazioneException::richiestaNonEsportabile('FN02', $richiesta);
            }
        }

        /** @var IGestoreRichiesteATC $gestoreATC */
        $gestoreATC = $this->container->get('gestore_richieste_atc')->getGestore($richiesta->getProcedura());
        $vociQuadroEconomico = $gestoreATC->getQuadroEconomico($richiesta);

        $vociEsportabili = [];
        foreach ($vociQuadroEconomico as $voce) {
            $voceSpesa = new FN02QuadroEconomico();
            $voceSpesa->setCodLocaleProgetto($richiesta->getProtocollo());
            $voceSpesa->setTc37VoceSpesa($voce['voce']);
            $voceSpesa->setImporto($voce['importo']);
            $voceSpesa->setEsportazioneStrutture($tavola);
            $vociEsportabili[] = $voceSpesa;
        }
        $vociDaCancellare = $this->aggiungiVociDaCancellare($vociEsportabili);
        $tot = \array_merge($vociEsportabili, $vociDaCancellare);
        return new ArrayCollection($tot);
    }

    /**
     * @param FN02QuadroEconomico[] $vociEsportabili
     * @return FN02QuadroEconomico[]
     */
    private function aggiungiVociDaCancellare($vociEsportabili) {
        $vociNonPresenti = $this->em->getRepository('MonitoraggioBundle:FN02QuadroEconomico')->findVociNonPresenti($vociEsportabili);
        $vociDaCancellare = \array_map(function (FN02QuadroEconomico $record) {
            $cancellazione = new FN02QuadroEconomico();
            $cancellazione->setCodLocaleProgetto($record->getCodLocaleProgetto())
                    ->setFlgCancellazione('S')
                    ->setImporto($record->getImporto())
                    ->setTc37VoceSpesa($record->getTc37VoceSpesa());
            return $cancellazione;
        },
            $vociNonPresenti);
        return $vociDaCancellare;
    }

    public function importa($input_array) {
        if (is_null($input_array) || !is_array($input_array) || 4 != count($input_array)) {
            throw new EsportazioneException("FN02: Input_array non valido");
        }

        $res = new FN02QuadroEconomico();
        $res->setCodLocaleProgetto($input_array[0]);
        $tc37 = $this->em->getRepository('MonitoraggioBundle:TC37VoceSpesa')->findOneBy(['voce_spesa' => $input_array[1]]);
        if (\is_null($tc37)) {
            throw new EsportazioneException("Voce spesa non valida");
        }
        $res->setTc37VoceSpesa($tc37);
        $res->setImporto($input_array[2]);
        $res->setFlgCancellazione($input_array[3]);
        return $res;
    }
}
