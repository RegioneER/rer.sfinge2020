<?php

namespace Gdbnet\FatturaElettronica\Codifiche;

abstract class ModalitaPagamento
{
    const Contanti = 'MP01';
    const Assegno = 'MP02';
    const AssegnoCircolare = 'MP03';
    const ContantiPressoTesoreria = 'MP04';
    const Bonifico = 'MP05';
    const VagliaCambiario = 'MP06';
    const BollettinoBancario = 'MP07';
    const CartaDiPagamento = 'MP08';
    const RID = 'MP09';
    const RIDUtenze = 'MP10';
    const RIDVeloce = 'MP11';
    const RIBA = 'MP12';
    const MAV = 'MP13';
    const QuietanzaErario = 'MP14';
    const GirocontoSpeciale = 'MP15';
    const DomiciliazioneBancaria = 'MP16';
    const DomiciliazionePostale = 'MP17';
    const BollettinoPostale = 'MP18';
    const SEPA = 'MP19';
    const SEPA_CORE = 'MP20';
    const SEPA_B2B = 'MP21';
    const TrattenutaSommeRiscosse = 'MP22';

    const CODIFICHE = [
        'MP01' => 'Contanti',
        'MP02' => 'Assegno',
        'MP03' => 'Assegno circolare',
        'MP04' => 'Contanti presso Tesoreria',
        'MP05' => 'Bonifico',
        'MP06' => 'Vaglia cambiario',
        'MP07' => 'Bollettino bancario',
        'MP08' => 'Carta di pagamento',
        'MP09' => 'RID',
        'MP10' => 'RID utenze',
        'MP11' => 'RID veloce',
        'MP12' => 'RIBA',
        'MP13' => 'MAV',
        'MP14' => 'Quietanza erario',
        'MP15' => 'Giroconto su conti di contabilità speciale',
        'MP16' => 'Domiciliazione bancaria',
        'MP17' => 'Domiciliazione postale',
        'MP18' => 'Bollettino di c/c postale',
        'MP19' => 'SEPA Direct Debit',
        'MP20' => 'SEPA Direct Debit CORE',
        'MP21' => 'SEPA Direct Debit B2B',
        'MP22' => 'Trattenuta su somme già riscosse'
    ];
}
