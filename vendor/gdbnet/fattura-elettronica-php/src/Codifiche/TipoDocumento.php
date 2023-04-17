<?php

namespace Gdbnet\FatturaElettronica\Codifiche;

abstract class TipoDocumento
{
    const Fattura = 'TD01';
    const AccontoSuFattura = 'TD02';
    const AccontoSuParcella = 'TD03';
    const NotaDiCredito = 'TD04';
    const NotaDiDebito = 'TD05';
    const Parcella = 'TD06';

    const CODIFICHE = [
        'TD01' => 'Fattura',
        'TD02' => 'acconto/anticipo su fattura',
        'TD03' => 'acconto/anticipo su parcella',
        'TD04' => 'nota di credito',
        'TD05' => 'nota di debito',
        'TD06' => 'parcella'
    ];
}
