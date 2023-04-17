<?php

namespace IstruttorieBundle\Tests\Entity;

use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use IstruttorieBundle\Entity\IstruttoriaVocePianoCosto;
use PHPUnit\Framework\TestCase;
use RichiesteBundle\Entity\PianoCosto;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Entity\TipoVoceSpesa;
use RichiesteBundle\Entity\VocePianoCosto;

class IstruttoriaRichiestaTest extends TestCase {
    /**
     * @var IstruttoriaRichiesta
     */
    private $istruttoria;

    public function setUp() {
        parent::setUp();

        $richiesta = new Richiesta();
        $this->istruttoria = new IstruttoriaRichiesta();
        $this->istruttoria->setRichiesta($richiesta);
        $richiesta->setIstruttoria($this->istruttoria);
    }

    public function testTotaleAmmesso(): void {
        $tipoVoceSpesa = new TipoVoceSpesa();
        $tipoVoceSpesa->setCodice(TipoVoceSpesa::TOTALE);
        $pianoCosto = new PianoCosto();
        $pianoCosto->setTipoVoceSpesa($tipoVoceSpesa);

        $voceSpesa = new VocePianoCosto();
        $voceSpesa->setPianoCosto($pianoCosto);
        $richiesta = $this->istruttoria->getRichiesta();
        $voceSpesa->setRichiesta($richiesta);
        $richiesta->addVociPianoCosto($voceSpesa);

        $voceIstruttoria = new IstruttoriaVocePianoCosto();
        $voceIstruttoria->setVocePianoCosto($voceSpesa);
        $voceSpesa->setIstruttoria($voceIstruttoria);

        $voceIstruttoria->setImportoAmmissibileAnno1(null);
        $voceIstruttoria->setImportoAmmissibileAnno2(1.0);
        $voceIstruttoria->setImportoAmmissibileAnno3(2.0);
        $voceIstruttoria->setImportoAmmissibileAnno4(3.0);
        $voceIstruttoria->setImportoAmmissibileAnno5(4);
        $voceIstruttoria->setImportoAmmissibileAnno6(5);
        $voceIstruttoria->setImportoAmmissibileAnno7(6);

        $res = $this->istruttoria->getTotaleAmmesso();

        $this->assertEquals(21.0, $res);
    }
}
