<?php

namespace RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\Proponente;

use BaseBundle\Exception\SfingeException;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\GestoriRichiestePA\ASezioneRichiesta;
use RichiesteBundle\GestoriRichiestePA\IRiepilogoRichiesta;
use RichiesteBundle\Ricerche\RicercaPersonaReferente;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CercaReferente extends ASezioneRichiesta {
    const TITOLO = 'Cerca referente';
    const SOTTOTITOLO = 'Seleziona i referenti per il proponente';

    const NOME_SEZIONE = 'proponente';

    /**
     * @var Proponente
     */
    protected $proponente;

    /**
     * @var bool
     */
    protected $aggiungiReferente = true;

    /**
     * @var \RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\Proponente
     */
    protected $parent;

    public function __construct(
        ContainerInterface $container,
        IRiepilogoRichiesta $riepilogo,
        ASezioneRichiesta $parent) {
        parent::__construct($container, $riepilogo);
        $this->proponente = $parent->getProponente();
        $this->parent = $parent;
        $parent->checkRichiesta($this->proponente->getRichiesta());
    }

    public function getTitolo() {
        return self::TITOLO;
    }

    public function valida() {
    }

    public function getUrl() {
        return $this->generateUrl(self::ROTTA, [
            'id_richiesta' => $this->richiesta->getId(),
            'nome_sezione' => self::NOME_SEZIONE,
            'parametro1' => $this->proponente->getId(),
            'parametro2' => 'referente',
        ]);
    }

    public function visualizzaSezione(array $parametri) {
        $this->setupPagina(self::TITOLO, self::SOTTOTITOLO);

        $isRichiestaDisabilitata = $this->getGestoreRichiesta()->isRichiestaDisabilitata();

        if ($isRichiestaDisabilitata) {
            throw new SfingeException('Impossibile effettuare questa operazione');
        }

        $ricercaPersone = new RicercaPersonaReferente();
        $ricercaPersone->setConsentiRicercaVuota(false);
        $risultato = $this->container->get('ricerca')->ricerca($ricercaPersone);

        $dati = [
            'persone' => $risultato['risultato'],
            'form' => $risultato['form_ricerca'],
            'filtro_attivo' => $risultato['filtro_attivo'],
            'richiesta' => $this->richiesta,
            'proponente' => $this->proponente,
            'url_indietro' => $this->parent->getUrl(),
            'aggiungi_referente' => $this->aggiungiReferente,
        ];

        return $this->render('RichiesteBundle:ProcedurePA:cercaReferente.html.twig', $dati);
    }

    public function setAggiungiReferente(bool $value): void {
        $this->aggiungiReferente = $value;
    }
}
