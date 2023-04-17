<?php

namespace SoggettoBundle\Tests\Entity;

use GeoBundle\Entity\GeoComune;
use GeoBundle\Entity\GeoStato;
use PHPUnit\Framework\TestCase;
use SoggettoBundle\Entity\Ateco;
use SoggettoBundle\Entity\Sede;
use SoggettoBundle\Entity\Soggetto;

class SoggettoTest extends TestCase {
    /** @var Soggetto */
    private $soggetto;

    public function setUp() {
        parent::setUp();
        $this->soggetto = new Soggetto();
    }

    public function testGetSedeTornaUnOggettoSede(): void {
        $res = $this->soggetto->getSede();

        $this->assertNotNull($res);
        $this->assertInstanceOf(Sede::class, $res);
        $this->assertEquals($this->soggetto, $res->getSoggetto());
    }

    public function testgetSedeIndirizzo(): void {
        $this->soggetto->setCap('90011');
        $this->soggetto->setCivico('11');
        $this->soggetto->setVia('via dei pazzi');
        $this->soggetto->setLocalita('localita');
        $stato = new GeoStato();
        $this->soggetto->setStato($stato);
        $comune = new GeoComune();
        $this->soggetto->setComune($comune);
        $this->soggetto->setComuneEstero('comune');
        $this->soggetto->setProvinciaEstera('provincia');

        $res = $this->soggetto->getSede();
        $indirizzo = $res->getIndirizzo();

        $this->assertNotNull($indirizzo);
        $this->assertEquals('90011', $indirizzo->getCap());
        $this->assertEquals('11', $indirizzo->getNumeroCivico());
        $this->assertEquals('via dei pazzi', $indirizzo->getVia(), 'Indirizzo');
        $this->assertEquals('localita', $indirizzo->getLocalita(), 'Località');
        $this->assertSame($stato, $indirizzo->getStato(), 'stato');
        $this->assertSame($comune, $indirizzo->getComune(), 'comune');
        $this->assertEquals('comune', $indirizzo->getComuneEstero(), 'comune estero');
        $this->assertEquals('provincia', $indirizzo->getProvinciaEstera(), 'provincia estero');
    }

    public function testSedeSoggetto(): void {
        $this->soggetto->setDenominazione('soggetto');
        $ateco = new Ateco();
        $this->soggetto->setCodiceAteco($ateco);
        $this->soggetto->setCodiceAtecoSecondario($ateco);

        $res = $this->soggetto->getSede();

        $this->assertEquals('soggetto', $res->getDenominazione());
        $this->assertSame($ateco,$res->getAteco());
        $this->assertSame($ateco,$res->getAtecoSecondario());
    }
}
