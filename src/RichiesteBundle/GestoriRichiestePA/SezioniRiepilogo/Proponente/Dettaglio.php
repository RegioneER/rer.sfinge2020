<?php

namespace RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\Proponente;

use BaseBundle\Exception\SfingeException;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\GestoriRichiestePA\ASezioneRichiesta;
use RichiesteBundle\GestoriRichiestePA\IRiepilogoRichiesta;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Dettaglio extends ASezioneRichiesta {
    const TITOLO = 'Dettaglio proponente';
    const SOTTOTITOLO = 'dettaglio di un proponente associato ad una richiesta';

    /**
     * @var ASezioneRichiesta
     */
    protected $parent;

    /**
     * @var Proponente
     */
    protected $proponente;

    /**
     * @var bool
     */
    protected $aggiungiPersonaReferente = false;

    /**
     * @param \RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\Proponente $parent
     * @param int $id_proponente
     *
     * @throws SfingeException
     */
    public function __construct(ContainerInterface $container, IRiepilogoRichiesta $riepilogo, ASezioneRichiesta $parent, $id_proponente) {
        parent::__construct($container, $riepilogo);
        $this->parent = $parent;
        $this->proponente = $this->getEm()->getRepository('RichiesteBundle:Proponente')->findOneById($id_proponente);
        if (\is_null($this->proponente)) {
            throw new SfingeException('Proponente non trovato');
        }
        $parent->checkRichiesta($this->proponente->getRichiesta());
    }

    public function getTitolo() {
        return self::TITOLO;
    }

    public function getUrl() {
        return $this->generateUrl(self::ROTTA, [
            'id_richiesta' => $this->richiesta->getId(),
            'nome_sezione' => \RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\Proponente::NOME_SEZIONE,
            'parametro1' => $this->proponente->getId(),
        ]);
    }

    public function valida() {
        $esito = $this->getGestoreProponenti()->validaProponente($this->proponente->getId());
        $this->listaMessaggi = \array_merge($esito->getMessaggiSezione(), $esito->getMessaggi());
    }

    public function visualizzaSezione(array $parametri) {
        $this->setupPagina(self::TITOLO, self::SOTTOTITOLO);
        $parametri = \array_diff($parametri, [null]);

        $sezione = $this->istanziaSezione($parametri);
        return $sezione == $this ? $this->visualizzaDettaglio() : $sezione->visualizzaSezione($parametri);
    }

    /**
     * @param array &$parametri
     * @return ASezioneRichiesta
     */
    protected function istanziaSezione(array &$parametri) {
        $res = null;
        $azione = \array_shift($parametri);

        switch ($azione) {
            case DettaglioReferente::NOME_SEZIONE:
                $res = $this->istanziaReferente($parametri);
                break;
            case SedeOperativa::NOME_SEZIONE:
                $res = new SedeOperativa($this->container, $this->riepilogo, $this);
                break;
            default:
                $res = $this;
                break;
        }
        return $res;
    }

    /**
     * @param array &$parametri
     * @return ASezioneRichiesta
     */
    protected function istanziaReferente(array &$parametri) {
        $sezione = null;
        $referente = \array_shift($parametri);
        switch ($referente) {
            case 'inserisci':
                    return $this->getIstanzaCercaInserisciReferente($parametri);
                break;
            case 'elimina':
                    return new EliminaReferente($this->container, $this->riepilogo, $this, $parametri[0]);
            case 'inserisci_persona':
                    return $this->getIstanzaInserisciPersonaReferente($parametri);
            default:
                return new DettaglioReferente($this->container, $this->riepilogo, $this, $referente);
                break;
        }
        return $sezione;
    }

    /**
     * @return ASezioneRichiesta
     */
    protected function getIstanzaCercaInserisciReferente(array $parametri) {
        if (\count($parametri)) {
            return new InserimentoReferente($this->container, $this->riepilogo, $this, $parametri[0]);
        }

        $cercaReferente = new CercaReferente($this->container, $this->riepilogo, $this, $parametri);
        $cercaReferente->setAggiungiReferente($this->aggiungiPersonaReferente);

        return $cercaReferente;
    }

    protected function getIstanzaInserisciPersonaReferente(array $parametri) {
        return new NuovaPersonaReferente($this->container, $this->riepilogo, $this, $parametri);
    }

    public function visualizzaDettaglio() {
        $this->valida();

        $isRichiestaDisabilitata = $this->getGestoreRichiesta()->isRichiestaDisabilitata();

        $tipiReferenza = $this->getGestoreProponenti()->getTipiReferenzaAmmessi();
        $abilita_aggiungi_referenti = count($tipiReferenza) > 0 && $this->proponente->getMandatario() && !$isRichiestaDisabilitata;

        $opzioni = [
            'proponente' => $this->proponente,
            'richiesta' => $this->richiesta,
            'abilita_aggiungi_referenti' => $abilita_aggiungi_referenti,
            'singolo_referente' => true,
            'abilita_sedi' => !$isRichiestaDisabilitata,
            'esito' => $this->getEsito(),
            'riepilogo' => $this->riepilogo,
        ];

        return $this->render("RichiesteBundle:ProcedurePA:dettaglioProponente.html.twig", $opzioni);
    }

    public function checkRichiesta(Richiesta $richiesta) {
        return $this->parent->checkRichiesta($richiesta);
    }

    /**
     * @return Proponente
     */
    public function getProponente() {
        return $this->proponente;
    }

    public function setAggiungiPersonaReferente(bool $value): void {
        $this->aggiungiPersonaReferente = $value;
    }
}
