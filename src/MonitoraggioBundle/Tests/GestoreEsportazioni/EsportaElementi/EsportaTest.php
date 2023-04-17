<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use MonitoraggioBundle\GestoriEsportazione\EsportaElementi\Esporta;

class EsportaTest extends EsportazioneBase {
    /**
     * @dataProvider convertiNumeroDaStringaDataProvider
     */
    public function testConvertiNumeroDaStringa($esito, $input) {
        $risultato = Esporta::convertNumberFromString($input);
        $this->assertSame($esito, $risultato);
    }

    public function convertiNumeroDaStringaDataProvider() {
        return array(
            array(1.2, '1,2'),
            array(3.0, '3'),
        );
    }
}
