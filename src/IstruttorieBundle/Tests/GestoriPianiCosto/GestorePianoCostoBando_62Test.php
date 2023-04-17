<?php

namespace IstruttorieBundle\Tests\GestoriPianiCosto;

use BaseBundle\Tests\Service\TestBaseService;
use IstruttorieBundle\GestoriPianoCosto\GestorePianoCostoBando_62;
use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use RichiesteBundle\Entity\Richiesta;
use BaseBundle\Exception\SfingeException;
use RichiesteBundle\Entity\PianoCosto;
use RichiesteBundle\GestoriPianiCosto\GestorePianoCostoBando_62 as GestorePianoRichiesta;
use RichiesteBundle\Entity\VocePianoCosto;


class GestorePianoCostoBando_62Test extends TestBaseService
{
    /**
     * @var GestorePianoCostoBando_62
     */
    protected $gestore;

    /**
     * @var IstruttoriaRichiesta
     */
    protected $istruttoria;

    public function setUp()
    {
        parent::setUp();

        $this->istruttoria = new IstruttoriaRichiesta();
        $richiesta = new Richiesta();
        $this->istruttoria->setRichiesta($richiesta);
        
        $this->gestore = new GestorePianoCostoBando_62($this->container);
    }

    /**
     * @dataProvider contributoDataProvider
     */
    public function testContributo(float $totPiano, float $contributo)
    {
       $this->setTotale($totPiano);
       $calcolato = $this->gestore->calcolaContributoPianoCosto($this->istruttoria);

       $this->assertEquals($contributo, $calcolato,'',0.001);
    }

    protected function setTotale(float $totale){
        $piano = new PianoCosto();
        $piano->setCodice(GestorePianoRichiesta::TOT);
        $voce = new VocePianoCosto();
        $voce->setImportoAnno1($totale);
        $voce->setPianoCosto($piano);
        $richiesta = $this->istruttoria->getRichiesta();
        $voce->setRichiesta($richiesta);
        
        $richiesta->addVociPianoCosto($voce);
    }

    public function contributoDataProvider():array{
        return [
            [0,0],
            [1000, 500],
            [400000, 149900],
        ];
    }
}