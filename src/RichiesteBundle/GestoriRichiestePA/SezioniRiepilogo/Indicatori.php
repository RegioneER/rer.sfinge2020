<?php

namespace RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo;

use RichiesteBundle\Entity\IndicatoreOutput;
use RichiesteBundle\GestoriRichiestePA\ASezioneRichiesta;
use RichiesteBundle\GestoriRichiestePA\IRiepilogoRichiesta;

class Indicatori extends ASezioneRichiesta {
    const TITOLO = 'Indicatori';
    const SOTTOTITOLO = "indicatori monitoraggio";
    const NOME_SEZIONE = 'indicatori';

    /**
     * {@inheritdoc}
     */
    public function getTitolo() {
        return $this->richiesta->getMonIndicatoreOutput()->isEmpty() ? false : self::TITOLO;
    }

    public function valida() {
        $this->generaIndicatori();
        /** @var GestoreIndicatoreService */
        $factory = $this->container->get('monitoraggio.indicatori_output');
        $indicatoriService = $factory->getGestore($this->richiesta);
        $res = $indicatoriService->isRichiestaValida();
        if (!$res) {
            $this->listaMessaggi = \array_merge($this->listaMessaggi, ['Sezione non completa']);
        }
    }

    protected function generaIndicatori(): void {
        if ($this->richiesta->getMonIndicatoreOutput()->count() > 0 || $this->getGestoreRichiesta()->isRichiestaDisabilitata($this->richiesta)) {
            return;
        }
        $procedura = $this->richiesta->getProcedura();
        $dataRef = $this->richiesta->getDataCreazione();
        foreach ($procedura->getIndicatoriAssociati($dataRef) as $defIndicatore) {
            $ind_rich = new IndicatoreOutput();
            $ind_rich->setRichiesta($this->richiesta);
            $ind_rich->setIndicatore($defIndicatore);
            $this->richiesta->addMonIndicatoreOutput($ind_rich);
        }
        $em = $this->getEm();
        $em->persist($this->richiesta);
        $em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl() {
        return $this->generateUrl(self::ROTTA, [
            'id_richiesta' => $this->richiesta->getId(),
            'nome_sezione' => self::NOME_SEZIONE,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function visualizzaSezione(array $parametri) {
        $this->setupPagina(self::TITOLO, self::SOTTOTITOLO);

        $url = $this->generateUrl(IRiepilogoRichiesta::ROTTA, ['id_richiesta' => $this->richiesta->getId()]);
        /** @var GestoreIndicatoreService */
        $factory = $this->container->get('monitoraggio.indicatori_output');
        $indicatoriService = $factory->getGestore($this->richiesta);

        return $indicatoriService->getFormRichiestaValoriProgrammati([
            'url_indietro' => $url,
            'return_url' => $url,
            'disabled' => $this->isRichiestaDisabilitata(),
        ]);
    }
}
