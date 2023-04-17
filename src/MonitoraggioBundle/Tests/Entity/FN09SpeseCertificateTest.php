<?php

namespace MonitoraggioBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use MonitoraggioBundle\Entity\FN09SpeseCertificate;
use MonitoraggioBundle\Entity\TC41DomandaPagamento;
use MonitoraggioBundle\Entity\TC36LivelloGerarchico;

class FN09SpeseCertificateTest extends TestCase {
    /**
     * @var FN09SpeseCertificate
     */
    protected $entity;

    public function setUp() {
        $this->entity = new FN09SpeseCertificate();
    }

    public function testGetTracciato() {
        $tc41 = new TC41DomandaPagamento();
        $tc41->setIdDomandaPagamento('1/2016');

        $tc36 = new TC36LivelloGerarchico();
        $tc36->setCodLivGerarchico('setCodLivGerarchico');

        $data = new \DateTime('2010-01-01');

        $this->entity
            ->setCodLocaleProgetto('setCodLocaleProgetto')
            ->setDataDomanda($data)
            ->setTipologiaImporto('setTipologiaImporto')
            ->setImportoSpesaTot('123.1')
            ->setImportoSpesaPub('99.5')
            ->setFlgCancellazione('S')
            ->setTc41DomandePagamento($tc41)
            ->setTc36LivelloGerarchico($tc36);

        $tracciato = $this->entity->getTracciato();
        $this->assertNotNull($tracciato);
        $match = [];
        \preg_match_all('/(?<=\||^)([^\|]*)(?=\||$)/', $tracciato, $match);

        $this->assertEquals(\count($match[0]), 8);
        $this->assertEquals($match[0][0], 'setCodLocaleProgetto');
        $this->assertEquals($match[0][1], '01/01/2010');
        $this->assertEquals($match[0][2], '1/2016');
        $this->assertEquals($match[0][3], 'setTipologiaImporto');
        $this->assertEquals($match[0][4], 'setCodLivGerarchico');
        $this->assertEquals($match[0][5], '123,10');
        $this->assertSame($match[0][6], '99,50');
        $this->assertEquals($match[0][7], 'S');
    }

    public function testTipologiaImporto() {
        $tipologia = 'tipologiaImporto';
        $this->entity->setTipologiaImporto($tipologia);
        $this->assertSame($tipologia, $this->entity->getTipologiaImporto());
    }

    public function testTc36(): void {
        $tc = new TC36LivelloGerarchico();
        $this->assertNull($this->entity->getTc36LivelloGerarchico());

        $this->entity->setTc36LivelloGerarchico($tc);

        $this->assertSame($tc, $this->entity->getTc36LivelloGerarchico());
    }

    public function testId() {
        $this->assertNull($this->entity->getId());
    }
}
