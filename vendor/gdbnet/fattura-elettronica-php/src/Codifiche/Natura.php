<?php

namespace Gdbnet\FatturaElettronica\Codifiche;

abstract class Natura
{
    const EscluseArt15 = 'N1';
    const NonSoggette = 'N2';
    const NonImponibili = 'N3';
    const Esenti = 'N4';
    const RegimeDelMargine = 'N5';
    const InversioneContabile = 'N6';
    const IvaAssoltaUe = 'N7';

    const CODIFICHE = [
        'N1' => 'Escluse ex art. 15',
        'N2' => 'Non soggette',
        'N3' => 'Non imponibili',
        'N4' => 'Esenti',
        'N5' => 'Regime del margine / IVA non esposta in fattura',
        'N6' => 'Inversione contabile (per le operazioni in reverse charge ovvero nei casi di autofatturazione per acquisti extra UE di servizi ovvero per importazioni di beni nei soli casi previsti)',
        'N7' => 'IVA assolta in altro stato UE (vendite a distanza ex art. 40 c. 3 e 4 e art. 41 c. 1 lett. b,  DL 331/93; prestazione di servizi di telecomunicazioni, tele-radiodiffusione ed elettronici ex art. 7-sexies lett. f, g, art. 74-sexies DPR 633/72)'
    ];
}
