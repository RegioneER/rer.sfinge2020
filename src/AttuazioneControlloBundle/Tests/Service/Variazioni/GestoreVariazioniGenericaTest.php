<?php

namespace AttuazioneControlloBundle\Tests\Service\Variazioni;

use AttuazioneControlloBundle\Service\Variazioni\GestoreVariazioniGenerica;
use AttuazioneControlloBundle\Entity\VariazionePianoCosti;
use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use AttuazioneControlloBundle\Entity\StatoVariazione;
use RichiesteBundle\Entity\Richiesta;
use SfingeBundle\Entity\Bando;
use RichiesteBundle\Entity\Proponente;
use BaseBundle\Tests\Service\TestBaseService;

class GestoreVariazioniGenericaTest extends TestBaseService
{
        /**
     * @var VariazionePianoCosti
     */
    protected $variazione;

    /**
     * @var GestoreVariazioniGenerica
     */
    protected $service;

    public function setUp() {
        parent::setUp();
        $atc = new AttuazioneControlloRichiesta();
        $this->variazione = new VariazionePianoCosti($atc);
        $stato = new StatoVariazione();
        $stato->setCodice(StatoVariazione::VAR_INSERITA);
        $this->variazione->setStato($stato);
        $richiesta = new Richiesta();
        $atc->setRichiesta($richiesta);
        $procedura = new Bando();
        $richiesta->setProcedura($procedura);
        $proponente = new Proponente($richiesta);
        $proponente->setMandatario(true);
        $richiesta->addProponenti($proponente);
        $this->service = new GestoreVariazioniGenerica($this->variazione, $this->container);
    }
}