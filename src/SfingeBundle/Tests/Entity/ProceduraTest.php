<?php

namespace SfingeBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use SfingeBundle\Entity\Bando;
use SfingeBundle\Entity\Procedura;
use SfingeBundle\Entity\Azione;
use SfingeBundle\Entity\ObiettivoSpecifico;
use MonitoraggioBundle\Entity\TC42_43IndicatoriRisultato;
use MonitoraggioBundle\Entity\IndicatoriRisultatoObiettivoSpecifico;
use SfingeBundle\Entity\Asse;
use MonitoraggioBundle\Entity\TC44_45IndicatoriOutput;
use MonitoraggioBundle\Entity\IndicatoriOutputAzioni;

class ProceduraTest extends TestCase {
    /**
     * @var Procedura
     */
    protected $procedura;

    public function setUp() {
        parent::setUp();
        $this->procedura = new Bando();
    }

    public function testIndicatoriRisultatoUnIndicatore(): void {
        $obiettivo = $this->creaObiettivoSpecifico();
        $indicatoreRisultato = new TC42_43IndicatoriRisultato();
        $this->associaObiettivoIndicatore($obiettivo, $indicatoreRisultato);

        $indicatori = $this->procedura->getIndicatoriRisultato();

        $this->assertCount(1, $indicatori);
        $this->assertContains($indicatoreRisultato, $indicatori);
    }

    protected function creaObiettivoSpecifico(): ObiettivoSpecifico {
        $azione = new Azione();
        $obiettivoSpecifico = new ObiettivoSpecifico();
        $azione->setObiettivoSpecifico($obiettivoSpecifico);
        $obiettivoSpecifico->addAzioni($azione);

        $this->procedura->addAzioni($azione);

        return $obiettivoSpecifico;
    }

    protected function associaObiettivoIndicatore(ObiettivoSpecifico $obiettivo, TC42_43IndicatoriRisultato $indicatoreRisultato): IndicatoriRisultatoObiettivoSpecifico {
        $associazione = new IndicatoriRisultatoObiettivoSpecifico($obiettivo, $indicatoreRisultato);
        $obiettivo->addAssociazioniIndicatoriRisultato($associazione);
        $indicatoreRisultato->addMappingObiettivoSpecifico($associazione);

        return $associazione;
    }

    public function testIndicatoriRisultatoNonPresenti(): void {
        $this->creaObiettivoSpecifico();

        $indicatori = $this->procedura->getIndicatoriRisultato();

        $this->assertEmpty($indicatori);
    }

    public function testIndicatoriRisultatoConPiuObiettivi(): void {
        $obiettivo1 = $this->creaObiettivoSpecifico();
        $obiettivo2 = $this->creaObiettivoSpecifico();

        $indicatoreRisultato1 = new TC42_43IndicatoriRisultato();
        $indicatoreRisultato1->setCodIndicatore('1');
        $this->associaObiettivoIndicatore($obiettivo1, $indicatoreRisultato1);

        $indicatoreRisultato2 = new TC42_43IndicatoriRisultato();
        $indicatoreRisultato2->setCodIndicatore('2');
        $this->associaObiettivoIndicatore($obiettivo2, $indicatoreRisultato2);

        $indicatori = $this->procedura->getIndicatoriRisultato();

        $this->assertContains($indicatoreRisultato1, $indicatori);
        $this->assertContains($indicatoreRisultato2, $indicatori);
        $this->assertCount(2, $indicatori);
    }

    public function testIndicatoriRisultatoDuplicati(): void {
        $obiettivo = $this->creaObiettivoSpecifico();
        $indicatoreRisultato = new TC42_43IndicatoriRisultato();
        $this->associaObiettivoIndicatore($obiettivo, $indicatoreRisultato);
        $this->associaObiettivoIndicatore($obiettivo, $indicatoreRisultato);
        $this->associaObiettivoIndicatore($obiettivo, $indicatoreRisultato);

        $indicatori = $this->procedura->getIndicatoriRisultato();

        $this->assertContains($indicatoreRisultato, $indicatori);
        $this->assertCount(1, $indicatori);
    }

    public function testIndicatoriAssociatiIdentici(): void
    {
        $asse = new Asse();
        $this->procedura->setAsse($asse);
        $defIndicatore = new TC44_45IndicatoriOutput();
        $azione1 = new Azione();
        $indicatoreAzione1 = new IndicatoriOutputAzioni($defIndicatore, $azione1);
        $azione1->addIndicatoriOutputAzioni($indicatoreAzione1);
        $indicatoreAzione1->setAsse($asse);
        $this->procedura->addAzioni($azione1);

        $azione2 = new Azione();
        $indicatoreAzione2 = new IndicatoriOutputAzioni($defIndicatore, $azione2);
        $indicatoreAzione2->setAsse($asse);
        $azione1->addIndicatoriOutputAzioni($indicatoreAzione2);
        $this->procedura->addAzioni($azione2);

        $ref = new \DateTime();
        $res = $this->procedura->getIndicatoriAssociati($ref);

        $this->assertCount(1, $res);
    }

    public function testIndicatoriAssociatiDifferenti(): void
    {
        $asse = new Asse();
        $this->procedura->setAsse($asse);
        $defIndicatore1 = new TC44_45IndicatoriOutput();
        $defIndicatore1->setCodIndicatore('1');
        $azione1 = new Azione();
        $indicatoreAzione1 = new IndicatoriOutputAzioni($defIndicatore1, $azione1);
        $azione1->addIndicatoriOutputAzioni($indicatoreAzione1);
        $indicatoreAzione1->setAsse($asse);
        $this->procedura->addAzioni($azione1);

        $defIndicatore2 = new TC44_45IndicatoriOutput();
        $defIndicatore2->setCodIndicatore('2');
        $azione2 = new Azione();
        $indicatoreAzione2 = new IndicatoriOutputAzioni($defIndicatore2, $azione2);
        $indicatoreAzione2->setAsse($asse);
        $azione1->addIndicatoriOutputAzioni($indicatoreAzione2);
        $this->procedura->addAzioni($azione2);


        $res = $this->procedura->getIndicatoriAssociati();

        $this->assertCount(2, $res);
    }
}
