<?php

namespace AttuazioneControlloBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use AttuazioneControlloBundle\Entity\VariazioneDatiBancariProponente;
use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use AttuazioneControlloBundle\Entity\DatiBancari;
use AttuazioneControlloBundle\Entity\VariazioneDatiBancari;
use RichiesteBundle\Entity\Richiesta;

class VariazioneDatiBancariProponenteTest extends TestCase {
    /**
     * @var VariazioneDatiBancariProponente
     */
    private $entity;

    public function setUp() {
        $richiesta = new Richiesta();
        $atc = new AttuazioneControlloRichiesta();
        $atc->setRichiesta($richiesta);
        $variazione = new VariazioneDatiBancari($atc);
        $datiBancari = new DatiBancari();

        $this->entity = new VariazioneDatiBancariProponente($variazione, $datiBancari);
    }

    public function testApplica() {
        $this->entity->setBanca('banca')
        ->setIntestatario('intestatario')
        ->setAgenzia('agenzia')
        ->setIban('iban')
        ->setContoTesoreria('conto');

        $this->entity->applica();

        $datiBancari = $this->entity->getDatiBancari();
        $this->assertEquals('banca', $datiBancari->getBanca());
        $this->assertEquals('intestatario', $datiBancari->getIntestatario());
        $this->assertEquals('agenzia', $datiBancari->getAgenzia());
        $this->assertEquals('iban', $datiBancari->getIban());
        $this->assertEquals('conto', $datiBancari->getContoTesoreria());
    }
}
