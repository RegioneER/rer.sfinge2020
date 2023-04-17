<?php

namespace IstruttorieBundle\Tests\GestoriPianiCosto;

use BaseBundle\Tests\Service\TestBaseService;
use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use IstruttorieBundle\GestoriPianoCosto\GestorePianoCostoBando_69;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Entity\Richiesta;
use BaseBundle\Exception\SfingeException;

class GestorePianoCostoBando_69Test extends TestBaseService {
    /** @var IstruttoriaRichiesta */
    protected $istruttoria;

    /** @var GestorePianoCostoBando_69 */
    protected $gestore;

    public function setUp() {
        parent::setUp();

        $this->gestore = new GestorePianoCostoBando_69($this->container);
        $this->istruttoria = new IstruttoriaRichiesta();
        $richiesta = new Richiesta();
        $this->istruttoria->setRichiesta($richiesta);
    }

    /**
     * @dataProvider costoAmmessoDataProvider
     */
    public function testCostoAmmesso(int $num_proponenti, float $costo, float $expected): void {
        $this->addProponenti($num_proponenti);
        $this->istruttoria->setCostoAmmesso($costo);

        $res = $this->gestore->calcolaContributoPianoCosto($this->istruttoria);
        $this->assertEquals($expected, $res, '', 0.01);
    }

    public function costoAmmessoDataProvider(): array {
        return [
            [1, 30000, 9000],
            [1, 100000, 30000],
            [2, 100000, 30000],
            [10, 100000, 30000],
        ];
    }

    protected function addProponenti(int $num_prop) {
        $richiesta = $this->istruttoria->getRichiesta();
        for ($i = 0; $i < $num_prop; ++$i) {
            $richiesta->addProponenti(new Proponente());
        }
    }

    public function testNessunProponente() {
        $this->expectException(SfingeException::class);
        $this->expectExceptionMessage('Nessun proponente definito per la richiesta');

        $this->gestore->calcolaContributoPianoCosto($this->istruttoria);
    }
}
