<?php

namespace RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo;

use BaseBundle\Exception\SfingeException;
use RichiesteBundle\GestoriRichiestePA\ASezioneRichiesta;
use RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\Proponente\Dettaglio;

class Proponente extends ASezioneRichiesta {
    const TITOLO = 'Gestione proponenti';
    const SOTTOTITOLO = 'mostra elenco dei proponenti del progetto';
    const VALIDATION_GROUP = 'dati_progetto';
    const NOME_SEZIONE = 'proponente';

    /**
     * @var bool
     */
    private $aggiungiReferente = false;

    public function getTitolo() {
        return self::TITOLO;
    }

    public function valida() {
        $esito = $this->getGestoreProponenti()->validaProponenti($this->richiesta->getId());
        $this->listaMessaggi = $esito->getMessaggiSezione();
    }

    public function getUrl() {
        $primoProponente = $this->richiesta->getProponenti()->first();
        if (1 == $this->richiesta->getProcedura()->getNumeroProponenti() && $primoProponente) {
            return $this->generateUrl(self::ROTTA, [
                'id_richiesta' => $this->richiesta->getId(),
                'nome_sezione' => self::NOME_SEZIONE,
                'parametro1' => $primoProponente->getId(),
            ]);
        }
        return $this->generateUrl(self::ROTTA, [
            'id_richiesta' => $this->richiesta->getId(),
            'nome_sezione' => self::NOME_SEZIONE,
        ]);
    }

    public function visualizzaSezione(array $parametri) {
        $this->setupPagina(self::TITOLO, self::SOTTOTITOLO);
        $parametri = \array_diff($parametri, [null]);

        $sezione = $this->istanziaSezione($parametri);
        return $sezione == $this ? $this->visualizzaSezioneCorrente() : $sezione->visualizzaSezione($parametri);
    }

    protected function visualizzaSezioneCorrente() {
        $proponenti = $this->richiesta->getProponenti();
        $procedura = $this->richiesta->getProcedura();

        $isRichiestaDisabilitata = $this->getGestoreRichiesta()->isRichiestaDisabilitata();
        $maxProponenti = $procedura->getNumeroProponenti();

        $documenti = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")
                    ->findBy([
                        "procedura" => $procedura,
                        "tipologia" => 'proponente',
                    ]);
        $this->valida();
        $dati = [
            "proponenti" => $proponenti,
            "richiesta" => $this->richiesta,
            "abilita_aggiungi_proponenti" => count($proponenti) < $maxProponenti && !$isRichiestaDisabilitata,
            "has_documenti" => count($documenti) > 0,
            'esito' => $this->getEsito(),
        ];

        return $this->render("RichiesteBundle:ProcedurePA:elencoProponenti.html.twig", $dati);
    }

    /**
     * @return ASezioneRichiesta
     */
    protected function istanziaSezione(array &$parametri) {
        $sezione = null;
        if (\count($parametri) > 0) {
            $id_proponente = \array_shift($parametri);
            switch ($id_proponente) {
                case "cerca_proponente":
                    $sezione = new Proponente\CercaProponente($this->container, $this->riepilogo, $this, []);
                    break;
                case "aggiungi_proponente":
                    $sezione = new Proponente\InserisciProponente($this->container, $this->riepilogo, $this, []);
                    break;
                default:
                    $sezione = new Dettaglio($this->container, $this->riepilogo, $this, $id_proponente);
                    $sezione->setAggiungiPersonaReferente($this->aggiungiReferente);
            }
        } else {
            $sezione = $this;
        }
        return $sezione;
    }

    /**
     * @throws SfingeException
     */
    public function checkRichiesta(\RichiesteBundle\Entity\Richiesta $richiesta) {
        if ($this->richiesta != $richiesta) {
            $this->container->get('logger')->error('Tentato accesso alla richiesta non autorizzato', [
                'Richiesta base' => $this->richiesta,
                'Tentativo' => $richiesta,
            ]);
            throw new SfingeException('Tentato accesso non autorizzato');
        }
        $this->checkProcedura($richiesta->getProcedura());
    }

    public function aggiungiReferente(bool $value): void {
        $this->aggiungiReferente($value);
    }
}
