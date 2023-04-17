<?php

namespace AttuazioneControlloBundle\Tests\Service;

use BaseBundle\Tests\Service\TestBaseService;
use AttuazioneControlloBundle\Service\GestoreVariazioniService;
use SfingeBundle\Entity\Bando;
use RichiesteBundle\Entity\Richiesta;
use AttuazioneControlloBundle\Entity\VariazionePianoCosti;
use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use AttuazioneControlloBundle\Service\Variazioni\GestoreVariazioniPianoCostiBase;
use AttuazioneControlloBundle\Service\Variazioni\GestoreVariazioniDatiBancariBase;
use AttuazioneControlloBundle\Entity\VariazioneDatiBancari;

class GestoreVariazioniServiceTest extends TestBaseService {
    /**
     * @var GestoreVariazioniService
     */
    protected $factory;

    /**
     * @var AttuazioneControlloRichiesta
     */
    protected $atc;

    public function setUp() {
        parent::setUp();
        $this->factory = new GestoreVariazioniService($this->container);

        $richiesta = new Richiesta();
        $procedura = new Bando();
        $richiesta->setProcedura($procedura);
        $this->atc = new AttuazioneControlloRichiesta();
        $this->atc->setRichiesta($richiesta);
    }

    public function testGetGestoreVariazioniPianoCostiBase(): void {
        $variazione = new VariazionePianoCosti($this->atc);
        $res = $this->factory->getGestoreVariazione($variazione);

        $this->assertNotNull($res);
        $this->assertInstanceOf(GestoreVariazioniPianoCostiBase::class, $res);
    }

    public function testGetGestoreVariazionidatiBancariBase(): void {
        $variazione = new VariazioneDatiBancari($this->atc);
        $res = $this->factory->getGestoreVariazione($variazione);

        $this->assertNotNull($res);
        $this->assertInstanceOf(GestoreVariazioniDatiBancariBase::class, $res);
    }
}
