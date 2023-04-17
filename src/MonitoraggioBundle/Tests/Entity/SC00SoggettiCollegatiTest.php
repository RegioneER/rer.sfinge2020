<?php

namespace MonitoraggioBundle\Tests\Entity;

use MonitoraggioBundle\Entity\TC24RuoloSoggetto;
use MonitoraggioBundle\Entity\TC25FormaGiuridica;
use MonitoraggioBundle\Entity\TC26Ateco;
use MonitoraggioBundle\Entity\SC00SoggettiCollegati;
use PHPUnit\Framework\TestCase;

class SC00SoggettiCollegatiTest extends TestCase {
    /**
     * @var SC00SoggettiCollegati
     */
    protected $entity;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        $this->entity = new SC00SoggettiCollegati();
    }

    public function testGetTracciato() {
        $tc24 = new TC24RuoloSoggetto();
        $tc24->setCodRuoloSog('1');

        $tc25 = new TC25FormaGiuridica();
        $tc25->setFormaGiuridica('1.1.10');

        $tc26 = new TC26Ateco();
        $tc26->setCodAtecoAnno('01_2007');

        $this->entity
            ->setCodLocaleProgetto(321)
            ->setTc24RuoloSoggetto($tc24)
            ->setTc25FormaGiuridica($tc25)
            ->setTc26Ateco($tc26)
            ->setCodiceFiscale('asdfgasdfg')
            ->setFlagSoggettoPubblico('S')
            ->setCodUniIpa('setCodUniIpa')
            ->setDenominazioneSog('DENOMINAZIONE_SOG')
            ->setNote('Note')
            ->setFlgCancellazione('S');

        $tracciato = $this->entity->getTracciato();
        $this->assertNotNull($tracciato);
        $match = [];
        preg_match_all('/(?<=\||^)([^\|]*)(?=\||$)/', $tracciato, $match);

        $this->assertEquals(\count($match[0]), 10);
        $this->assertEquals($match[0][0], '321');
        $this->assertEquals($match[0][1], '1');
        $this->assertEquals($match[0][2], 'asdfgasdfg');
        $this->assertEquals($match[0][3], 'S');
        $this->assertEquals($match[0][4], 'setCodUniIpa');
        $this->assertEquals($match[0][5], 'DENOMINAZIONE_SOG');
        $this->assertEquals($match[0][6], '1.1.10');
        $this->assertEquals($match[0][7], '01_2007');
        $this->assertEquals($match[0][8], 'Note');
        $this->assertEquals($match[0][9], 'S');
    }

    /**
     * @dataProvider codUniIpaValidDataProvider
     */
    public function testCodUniIpaValidSoggettoPubblicoOk(string $pubblico, ?string $codUniIpa, bool $esito): void {
        $this->entity
            ->setFlagSoggettoPubblico($pubblico)
            ->setCodUniIpa($codUniIpa);
        $this->assertSame($esito, $this->entity->isCodUniIpaValid());
    }

    public function codUniIpaValidDataProvider() {
        return [
            ['S', null, false],
            ['S', '', false],
            ['N', null, true],
            ['S', 'cod_uni_ipa', true],
        ];
    }

    /**
     * @dataProvider denominazioneValidDataProvider
     */
    public function testDenominazioneValid(string $pubblico, ?string $cf, ?string $denominazione, bool $esito): void {
        $this->entity
            ->setFlagSoggettoPubblico($pubblico)
            ->setDenominazioneSog($denominazione)
            ->setCodiceFiscale($cf);

        $this->assertSame($esito, $this->entity->isDenominazioneValid());
    }

    public function denominazioneValidDataProvider() {
        return [
            ['S', null, null, false],
            ['S', null, 'denominazione', true],
            ['N', '12345678901234', null, true],
            ['N', '123456789012345*', null, false],
            ['N', '123456789012345*', 'denominazione', true],
        ];
    }

    /**
     * @dataProvider atecoValidDataProvider
     */
    public function testAtecoValid(?TC26Ateco $ateco, string $cf, bool $esito): void {
        $this->entity->setTc26Ateco($ateco)
        ->setCodiceFiscale($cf);

        $this->assertSame($esito, $this->entity->isTc26AtecoValid());
    }

    public function atecoValidDataProvider() {
        $ateco = new TC26Ateco();
        return [
            [null, '12345678912345', true],
            [null, '123456789012345*', false],
            [$ateco, '123456789012345*', true],
        ];
    }
}
