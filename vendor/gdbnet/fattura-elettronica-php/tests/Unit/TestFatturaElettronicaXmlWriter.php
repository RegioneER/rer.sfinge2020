<?php

namespace Gdbnet\FatturaElettronica\Tests\Unit;

use Gdbnet\FatturaElettronica\Body\DatiBeniServizi;
use Gdbnet\FatturaElettronica\Body\DatiGenerali;
use Gdbnet\FatturaElettronica\Body\DatiGeneraliDocumento;
use Gdbnet\FatturaElettronica\Body\DatiPagamento;
use Gdbnet\FatturaElettronica\Body\DatiRiepilogo;
use Gdbnet\FatturaElettronica\Body\DatiTrasporto;
use Gdbnet\FatturaElettronica\Body\DettaglioLinea;
use Gdbnet\FatturaElettronica\Body\DettaglioPagamento;
use Gdbnet\FatturaElettronica\Body\FatturaElettronicaBody;
use Gdbnet\FatturaElettronica\FatturaElettronica;
use Gdbnet\FatturaElettronica\FatturaElettronicaXmlWriter;
use Gdbnet\FatturaElettronica\Header\CedentePrestatore;
use Gdbnet\FatturaElettronica\Header\CessionarioCommittente;
use Gdbnet\FatturaElettronica\Header\DatiTrasmissione;
use Gdbnet\FatturaElettronica\Header\FatturaElettronicaHeader;
use Gdbnet\FatturaElettronica\Structures\Anagrafica;
use Gdbnet\FatturaElettronica\Structures\DatiRiferimento;
use Gdbnet\FatturaElettronica\Structures\Fiscale;
use Gdbnet\FatturaElettronica\Structures\Indirizzo;
use Gdbnet\FatturaElettronica\XmlValidator;
use PHPUnit\Framework\TestCase;

class TestFatturaElettronicaXmlWriter extends TestCase
{
    public function test_class_fiscale_is_created()
    {
        $fiscale = new Fiscale('IT', '01234567890');

        $this->assertInstanceOf(Fiscale::class, $fiscale);

        return $fiscale;
    }

    /**
     * @depends test_class_fiscale_is_created
     * @param Fiscale $fiscale
     * @return DatiTrasmissione
     * @throws \Gdbnet\FatturaElettronica\FatturaElettronicaException
     */
    public function test_class_dati_trasmissione_is_created(Fiscale $fiscale)
    {
        $datiTrasmissione = new DatiTrasmissione(
            $fiscale,
            '00001',
            'FPA12',
            'AAAAAA'
        );

        $this->assertInstanceOf(DatiTrasmissione::class, $datiTrasmissione);

        return $datiTrasmissione;
    }

    /**
     * @depends test_class_fiscale_is_created
     */
    public function test_class_cedente_prestatore_is_created($fiscale)
    {
        $indirizzo = new Indirizzo('VIALE ROMA 543', 'SASSARI', '07100', 'IT');
        $indirizzo->setProvincia('SS');
        $anagrafica = new Anagrafica('ALPHA SRL');

        $cedentePrestatore = new CedentePrestatore($fiscale, $anagrafica, $indirizzo, 'RF01');

        $this->assertInstanceOf(CedentePrestatore::class, $cedentePrestatore);

        return $cedentePrestatore;
    }

    public function test_class_cessionario_committente_is_created()
    {
        $indirizzo = new Indirizzo('VIA TORINO 38-B', 'ROMA', '00145', 'IT');
        $indirizzo->setProvincia('RM');
        $anagrafica = new Anagrafica('AMMINISTRAZIONE BETA');
        $cessionarioCommittente = new CessionarioCommittente($anagrafica, $indirizzo);
        $cessionarioCommittente->setCodiceFiscale('09876543210');

        $this->assertInstanceOf(CessionarioCommittente::class, $cessionarioCommittente);

        return $cessionarioCommittente;
    }

    /**
     * @depends test_class_dati_trasmissione_is_created
     * @depends test_class_cedente_prestatore_is_created
     * @depends test_class_cessionario_committente_is_created
     * @return FatturaElettronicaHeader
     * @throws \Gdbnet\FatturaElettronica\FatturaElettronicaException
     */
    public function test_class_fattura_elettronica_header_is_created(
        DatiTrasmissione $datiTrasmissione,
        CedentePrestatore $cedentePrestatore,
        CessionarioCommittente $cessionarioCommittente
    )
    {
        $fatturaElettronicaHeader = new FatturaElettronicaHeader(
            $datiTrasmissione,
            $cedentePrestatore,
            $cessionarioCommittente
        );

        $this->assertInstanceOf(FatturaElettronicaHeader::class, $fatturaElettronicaHeader);

        return $fatturaElettronicaHeader;
    }

    public function test_class_dati_generali_documento_is_created()
    {
        $datiGeneraliDocumento = new DatiGeneraliDocumento(
            'TD01',
            '2017-01-18',
            '123'
        );
        $datiGeneraliDocumento->addCausale('LA FATTURA FA RIFERIMENTO AD UNA OPERAZIONE AAAA BBBBBBBBBBBBBBBBBB CCC DDDDDDDDDDDDDDD E FFFFFFFFFFFFFFFFFFFF GGGGGGGGGG HHHHHHH II LLLLLLLLLLLLLLLLL MMM NNNNN OO PPPPPPPPPPP QQQQ RRRR SSSSSSSSSSSSSS')
            ->addCausale('SEGUE DESCRIZIONE CAUSALE NEL CASO IN CUI NON SIANO STATI SUFFICIENTI 200 CARATTERI AAAAAAAAAAA BBBBBBBBBBBBBBBBB');

        $this->assertInstanceOf(DatiGeneraliDocumento::class, $datiGeneraliDocumento);

        return $datiGeneraliDocumento;
    }

    public function test_class_dati_trasporto_is_created()
    {
        $datiTrasporto = new DatiTrasporto();
        $fiscale = new Fiscale('IT', '24681012141');
        $anagrafica = new Anagrafica('Trasporto spa');
        $datiTrasporto->setIdFiscaleIVA($fiscale)
            ->setAnagrafica($anagrafica)
            ->setDataOraConsegna('2017-01-10T16:46:12.000+02:00');

        $this->assertInstanceOf(DatiTrasporto::class, $datiTrasporto);

        return $datiTrasporto;
    }

    /**
     * @depends test_class_dati_generali_documento_is_created
     * @depends test_class_dati_trasporto_is_created
     * @return DatiGenerali
     */
    public function test_class_dati_generali_is_created(
        DatiGeneraliDocumento $datiGeneraliDocumento,
        DatiTrasporto $datiTrasporto
    )
    {
        $datiGenerali = new DatiGenerali($datiGeneraliDocumento);

        $datiOrdineAcquisto = new DatiRiferimento('66685');
        $datiOrdineAcquisto->setRiferimentoNumeroLinea([1])
            ->setNumItem(1)
            ->setCodiceCUP('123abc')
            ->setCodiceCIG('456def');
        $datiGenerali->addDatiOrdineAcquisto($datiOrdineAcquisto);

        $datiContratto = new DatiRiferimento('123');
        $datiContratto->setRiferimentoNumeroLinea([1])
            ->setNumItem(5)
            ->setData('2016-09-01')
            ->setCodiceCUP('123abc')
            ->setCodiceCIG('456def');
        $datiGenerali->addDatiContratto($datiContratto);

        $datiConvenzione = new DatiRiferimento('456');
        $datiConvenzione->setRiferimentoNumeroLinea([1])
            ->setNumItem(5)
            ->setCodiceCUP('123abc')
            ->setCodiceCIG('456def');
        $datiGenerali->addDatiConvenzione($datiConvenzione);

        $datiRicezione = new DatiRiferimento('789');
        $datiRicezione->setRiferimentoNumeroLinea([1])
            ->setNumItem(5)
            ->setCodiceCUP('123abc')
            ->setCodiceCIG('456def');
        $datiGenerali->addDatiRicezione($datiRicezione);

        $datiGenerali->setDatiTrasporto($datiTrasporto);

        $this->assertInstanceOf(DatiGenerali::class, $datiGenerali);

        return $datiGenerali;
    }

    public function test_class_dati_beni_servizi_is_created()
    {
        $datiBeniServizi = new DatiBeniServizi();
        $dettaglio = new DettaglioLinea(
            1,
            'LA DESCRIZIONE DELLA FORNITURA PUO\' SUPERARE I CENTO CARATTERI CHE RAPPRESENTAVANO IL PRECEDENTE LIMITE DIMENSIONALE. TALE LIMITE NELLA NUOVA VERSIONE E\' STATO PORTATO A MILLE CARATTERI',
            1,
            5,
            22
        );
        $dettaglio->setPrezzoUnitario(1, 2)->setPrezzoTotale(5, 2);
        $dettaglio->setQuantita(5, 2);
        $datiBeniServizi->addDettaglioLinea($dettaglio);
        $dettaglio = new DettaglioLinea(
            2,
            'FORNITURE VARIE PER UFFICIO',
            2,
            20,
            22
        );
        $dettaglio->setQuantita(10, 2)
            ->setPrezzoUnitario(2, 2)
            ->setPrezzoTotale(20, 2);
        $datiBeniServizi->addDettaglioLinea($dettaglio);

        $datiRiepilogo = new DatiRiepilogo(22, 25, 5.5);
        $datiRiepilogo->setEsigibilitaIVA('D');
        $datiBeniServizi->addDatiRiepilogo($datiRiepilogo);

        $this->assertInstanceOf(DatiBeniServizi::class, $datiBeniServizi);

        return $datiBeniServizi;
    }

    public function test_class_dati_pagamento_is_created()
    {
        $datiPagamento = new DatiPagamento('TP01');
        $dettaglioPagamento = new DettaglioPagamento('MP01', 30.5);
        $dettaglioPagamento->setDataScadenzaPagamento('2017-03-30');
        $datiPagamento->addDettaglioPagamento($dettaglioPagamento);

        $this->assertInstanceOf(DatiPagamento::class, $datiPagamento);

        return $datiPagamento;
    }

    /**
     * @depends test_class_dati_generali_is_created
     * @depends test_class_dati_beni_servizi_is_created
     * @depends test_class_dati_pagamento_is_created
     * @return FatturaElettronicaBody
     */
    public function test_class_fattura_elettronica_body_is_created(
        DatiGenerali $datiGenerali,
        DatiBeniServizi $datiBeniServizi,
        DatiPagamento $datiPagamento
    )
    {
        $fatturaElettronicaBody = new FatturaElettronicaBody($datiGenerali, $datiBeniServizi);
        $fatturaElettronicaBody->addDatiPagamento($datiPagamento);

        $this->assertInstanceOf(FatturaElettronicaBody::class, $fatturaElettronicaBody);

        return $fatturaElettronicaBody;
    }

    /**
     * @depends test_class_fattura_elettronica_header_is_created
     * @depends test_class_fattura_elettronica_body_is_created
     * @return FatturaElettronica
     */
    public function test_class_fattura_elettronica_is_created(
        FatturaElettronicaHeader $fatturaElettronicaHeader,
        FatturaElettronicaBody $fatturaElettronicaBody
    )
    {
        $fattura = new FatturaElettronica($fatturaElettronicaHeader, $fatturaElettronicaBody);

        $this->assertInstanceOf(FatturaElettronica::class, $fattura);

        return $fattura;
    }

    /**
     * @depends test_class_fattura_elettronica_is_created
     */
    public function test_writer_outputs_correct_xml(FatturaElettronica $fattura)
    {
        $writer = new FatturaElettronicaXmlWriter($fattura);
        $xml = $writer->encodeXml();

        $validator = new XmlValidator();
        $this->assertTrue($validator->validate($xml));

        $expected = str_replace([" ", "\n"],"", file_get_contents(dirname(__FILE__) . '/../samples/IT01234567890_FPA02.xml'));
        $actual = str_replace([" ", "\n"],"", $xml);

        $this->assertEquals($expected, $actual);
    }
}