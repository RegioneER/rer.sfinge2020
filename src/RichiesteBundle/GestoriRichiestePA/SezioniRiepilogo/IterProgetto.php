<?php

namespace RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo;

use RichiesteBundle\GestoriRichiestePA\ASezioneRichiesta;
use MonitoraggioBundle\Service\IGestoreIterProgetto;
use PaginaBundle\Services\Pagina;

class IterProgetto extends ASezioneRichiesta {
    const TITOLO = 'Gestione iter di progetto';
    const SOTTOTITOLO = 'mostra le fasi procedurali del progetto';
    const VALIDATION_GROUP = 'iter_progetto';

    const NOME_SEZIONE = 'iter_progetto';

    public function getTitolo() {
        return self::TITOLO;
    }

    public function valida() {
        $validationList = $this->validator->validate($this->richiesta->getMonIterProgetti(), null, "presentazione_richiesta");
        if ($validationList->count() > 0) {
            $this->listaMessaggi = ['La sezione non è completa'];
        }
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
        /** @var Pagina $paginaService */
        $paginaService = $this->container->get('pagina');
        $paginaService->setTitolo('Iter di progetto');
        $paginaService->setSottoTitolo('Gestione delle fasi procedurali per il progetto');

        /** @var IGestoreIterProgetto $iterProgettoService */
        $iterProgettoService = $this->container->get('monitoraggio.iter_progetto')->getIstanza($this->richiesta);
        return $iterProgettoService->modificaIterFaseRichiesta([
            'form_options' => ['indietro' => $this->riepilogo->getUrl(),]
        ]);
    }
}
