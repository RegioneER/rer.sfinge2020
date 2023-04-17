<?php

namespace RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\Proponente;

use BaseBundle\Exception\SfingeException;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\GestoriRichiestePA\ASezioneRichiesta;
use RichiesteBundle\GestoriRichiestePA\IRiepilogoRichiesta;
use Symfony\Component\DependencyInjection\ContainerInterface;

class NuovaPersonaReferente extends ASezioneRichiesta {
    const TITOLO = 'Inserisci nuova persona';
    const SOTTOTITOLO = "inserimento nuova persona all'interno del sistema";
    const NOME_SEZIONE = 'inserisci_persona';

    /**
     * @var Dettaglio
     */
    protected $parent;

    /**
     * @var Proponente
     */
    protected $proponente;

    /**
     * @param Dettaglio $parent
     * @param int $id_proponente
     *
     * @throws SfingeException
     */
    public function __construct(ContainerInterface $container, IRiepilogoRichiesta $riepilogo, ASezioneRichiesta $parent) {
        parent::__construct($container, $riepilogo);
        $this->parent = $parent;
        $this->proponente = $this->parent->getProponente();
        $parent->checkRichiesta($this->proponente->getRichiesta());
    }

    public function getTitolo() {
        return self::TITOLO;
    }

    public function getUrl() {
        return $this->generateUrl(self::ROTTA, [
            'id_richiesta' => $this->proponente->getRichiesta()->getId(),
            'nome_sezione' => \RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\Proponente::NOME_SEZIONE,
            'parametro1' => $this->proponente->getId(),
            'parametro2' => DettaglioReferente::NOME_SEZIONE,
            'parametro3' => self::NOME_SEZIONE,
        ]);
    }

    public function valida() {
    }

    public function visualizzaSezione(array $parametri) {
        $this->setupPagina(self::TITOLO, self::SOTTOTITOLO);
        return $this->container->get("inserimento_persona")->inserisciPersona(
            $this->parent->getUrl(),
            "procedura_pa_sezione",
            [
                'id_richiesta' => $this->proponente->getRichiesta()->getId(),
                'nome_sezione' => \RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\Proponente::NOME_SEZIONE,
                'parametro1' => $this->proponente->getId(),
                'parametro2' => DettaglioReferente::NOME_SEZIONE,
            ]);
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
}
