<?php

namespace AttuazioneControlloBundle\Service\Istruttoria\Variazioni;

use AttuazioneControlloBundle\Entity\VariazioneSedeOperativa;
use AttuazioneControlloBundle\Service\Istruttoria\AGestoreVariazioni;
use MonitoraggioBundle\Entity\LocalizzazioneGeografica;
use RichiesteBundle\Entity\SedeOperativa;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class GestoreVariazioniSedeOperativaBase extends AGestoreVariazioni implements IGestoreVariazioniSedeOperativa {
    /**
     * @var VariazioneSedeOperativa
     */
    protected $variazione;

    public function __construct(VariazioneSedeOperativa $variazione, ContainerInterface $container) {
        $this->variazione = $variazione;
        $this->container = $container;
    }

    public function dettaglioSedeOperativa(): Response {
        return $this->render('AttuazioneControlloBundle:Istruttoria\Variazioni:dettaglioSedeOperativa.html.twig', [
            'variazione' => $this->variazione,
            'menu' => 'sede_operativa',
        ]);
    }

    protected function applicaVariazione(): void {
        $richiesta = $this->variazione->getRichiesta();
        /** @var SedeOperativa|bool $sedeOperativa */
        $sedeOperativa = $richiesta->getMandatario()->getSedi()->filter(function (SedeOperativa $operativa) {
            return $operativa->getSede() == $this->variazione->getSedeOperativa();
        })->last();
        if (false === $sedeOperativa) {
            $proponente = $richiesta->getMandatario();
            $sedeOperativa = new SedeOperativa($proponente);
            $proponente->addSedi($sedeOperativa);
            $this->getEm()->persist($sedeOperativa);
        }

        $sedeOperativa->setSede($this->variazione->getSedeOperativaVariata());

        //Gestione separata per la sede version
        $versioning = $this->container->get("soggetto.versioning");
        $sedeVersion = $versioning->creaSedeVersion($this->variazione->getSedeOperativaVariata());
        $sedeOperativa->setSedeVersion($sedeVersion);

        // Lato monitoraggio
        switch ($richiesta->getMonLocalizzazioneGeografica()->count()) {
            case 0:
                // Inserisco la localizzazione geografica
                $localizzazione = new LocalizzazioneGeografica($richiesta);
                $this->setDatiLocalizzazione($localizzazione);
                $richiesta->addMonLocalizzazioneGeografica($localizzazione);
                break;
            case 1:
                // Aggiorno la localizzazione geografica sempre
                /** @var LocalizzazioneGeografica $localizzazione */
                $localizzazione = $richiesta->getMonLocalizzazioneGeografica()->first();
                $this->setDatiLocalizzazione($localizzazione);
                break;
            default:
                // Aggiorno la localizzazione geografica tra molte
                //Se non presente l'aggiungo
                /** @var LocalizzazioneGeografica|bool $localizzazione */
                $localizzazione = $richiesta->getMonLocalizzazioneGeografica()->filter(function (LocalizzazioneGeografica $l) {
                    $sede = $this->variazione->getSedeOperativa();
                    $stessoComune = false;
                    $stessoIndirizzo = false;
                    $stessoCAP = false;
                    if($sede){
                        $indirizzo = $sede->getIndirizzo();
                        $stessoComune = $l->getLocalizzazione() == $indirizzo->getComune()->getTc16LocalizzazioneGeografica();
                        $stessoIndirizzo = $l->getIndirizzo() == "{$indirizzo->getVia()}, {$indirizzo->getNumeroCivico()}";
                        $stessoCAP = $l->getCap() == $indirizzo->getCap();
                    }
                    else{
                        $soggetto = $this->variazione->getRichiesta()->getSoggetto();
                        $stessoComune = $l->getLocalizzazione() == $soggetto->getComune()->getTc16LocalizzazioneGeografica();
                        $stessoIndirizzo = $l->getIndirizzo() == "{$soggetto->getVia()}, {$soggetto->getCivico()}";
                        $stessoCAP = $l->getCap() == $soggetto->getCap();
                    }
                    return $stessoComune && $stessoIndirizzo && $stessoCAP;
                })->first();
                if(false === $localizzazione){
                    $localizzazione = new LocalizzazioneGeografica($richiesta);
                    $richiesta->addMonLocalizzazioneGeografica($localizzazione);
                }
                $this->setDatiLocalizzazione($localizzazione);
                break;
        }
    }

    private function setDatiLocalizzazione(LocalizzazioneGeografica &$localizzazione) {
        $indirizzo = $this->variazione->getSedeOperativaVariata()->getIndirizzo();
        $localizzazione->setCap($indirizzo->getCap());
        $localizzazione->setIndirizzo("{$indirizzo->getVia()}, {$indirizzo->getNumeroCivico()}");

        $comune = $indirizzo->getComune()->getTc16LocalizzazioneGeografica();
        $localizzazione->setLocalizzazione($comune);

        return $localizzazione;
    }
}
