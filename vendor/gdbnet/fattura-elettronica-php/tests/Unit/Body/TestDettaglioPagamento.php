<?php

namespace Gdbnet\FatturaElettronica\Tests\Unit\Body;

use Gdbnet\FatturaElettronica\Body\DettaglioPagamento;
use PHPUnit\Framework\TestCase;

class TestDettaglioPagamento extends TestCase
{
    public function test_class_is_created()
    {
        $dettaglioPagamento = new DettaglioPagamento('MP01', 10);

        $this->assertInstanceOf(DettaglioPagamento::class, $dettaglioPagamento);

        return $dettaglioPagamento;
    }

    /**
     * @depends test_class_is_created
     * @param DettaglioPagamento $dettaglioPagamento
     * @return DettaglioPagamento
     */
    public function test_class_returns_correct_array(DettaglioPagamento $dettaglioPagamento)
    {
        $dettaglioPagamento->setBeneficiario('tester')
            ->setABI('abi')
            ->setBIC('bic')
            ->setCAB('cab')
            ->setCFQuietanzante('01234567890')
            ->setCodicePagamento('codice')
            ->setCodUfficioPostale('postale12345')
            ->setCognomeQuietanzante('testing')
            ->setNomeQuietanzante('test')
            ->setDataDecorrenzaPenale('2000-01-01')
            ->setDataLimitePagamentoAnticipato('2000-01-02')
            ->setDataRiferimentoTerminiPagamento('2000-01-03')
            ->setDataScadenzaPagamento('2000-01-04')
            ->setGiorniTerminiPagamento(10)
            ->setIBAN('IBAN')
            ->setIstitutoFinanziario('istituto')
            ->setPenalitaPagamentiRitardati(10)
            ->setScontoPagamentoAnticipato(5)
            ->setTitoloQuietanzante('titolo');

        $this->assertInstanceOf(DettaglioPagamento::class, $dettaglioPagamento);

        $array = $dettaglioPagamento->toArray();
        $this->assertArrayHasKey('Beneficiario', $array);
        $this->assertArrayHasKey('ModalitaPagamento', $array);
        $this->assertArrayHasKey('DataRiferimentoTerminiPagamento', $array);
        $this->assertArrayHasKey('GiorniTerminiPagamento', $array);
        $this->assertArrayHasKey('DataScadenzaPagamento', $array);
        $this->assertArrayHasKey('ImportoPagamento', $array);
        $this->assertArrayHasKey('CodUfficioPostale', $array);
        $this->assertArrayHasKey('CognomeQuietanzante', $array);
        $this->assertArrayHasKey('NomeQuietanzante', $array);
        $this->assertArrayHasKey('CFQuietanzante', $array);
        $this->assertArrayHasKey('TitoloQuietanzante', $array);
        $this->assertArrayHasKey('IstitutoFinanziario', $array);
        $this->assertArrayHasKey('IBAN', $array);
        $this->assertArrayHasKey('ABI', $array);
        $this->assertArrayHasKey('CAB', $array);
        $this->assertArrayHasKey('BIC', $array);
        $this->assertArrayHasKey('ScontoPagamentoAnticipato', $array);
        $this->assertArrayHasKey('DataLimitePagamentoAnticipato', $array);
        $this->assertArrayHasKey('PenalitaPagamentiRitardati', $array);
        $this->assertArrayHasKey('DataDecorrenzaPenale', $array);
        $this->assertArrayHasKey('CodicePagamento', $array);

        return $dettaglioPagamento;
    }

    /**
     * @depends test_class_returns_correct_array
     * @param DettaglioPagamento $dettaglioPagamento
     */
    public function test_class_is_created_from_array(DettaglioPagamento $dettaglioPagamento)
    {
        $array = $dettaglioPagamento->toArray();

        $dettaglioPagamento = DettaglioPagamento::fromArray($array);

        $this->assertInstanceOf(DettaglioPagamento::class, $dettaglioPagamento);
        $this->assertEquals('MP01', $dettaglioPagamento->getModalitaPagamento());
        $this->assertEquals(10, $dettaglioPagamento->getImportoPagamento());
        $this->assertEquals('2000-01-03', $dettaglioPagamento->getDataRiferimentoTerminiPagamento());
        $this->assertEquals(10, $dettaglioPagamento->getGiorniTerminiPagamento());
        $this->assertEquals('2000-01-04', $dettaglioPagamento->getDataScadenzaPagamento());
        $this->assertEquals('tester', $dettaglioPagamento->getBeneficiario());
        $this->assertEquals('postale12345', $dettaglioPagamento->getCodUfficioPostale());
        $this->assertEquals('testing', $dettaglioPagamento->getCognomeQuietanzante());
        $this->assertEquals('test', $dettaglioPagamento->getNomeQuietanzante());
        $this->assertEquals('01234567890', $dettaglioPagamento->getCFQuietanzante());
        $this->assertEquals('titolo', $dettaglioPagamento->getTitoloQuietanzante());
        $this->assertEquals('istituto', $dettaglioPagamento->getIstitutoFinanziario());
        $this->assertEquals('IBAN', $dettaglioPagamento->getIBAN());
        $this->assertEquals('abi', $dettaglioPagamento->getABI());
        $this->assertEquals('cab', $dettaglioPagamento->getCAB());
        $this->assertEquals('bic', $dettaglioPagamento->getBIC());
        $this->assertEquals(5, $dettaglioPagamento->getScontoPagamentoAnticipato());
        $this->assertEquals('2000-01-02', $dettaglioPagamento->getDataLimitePagamentoAnticipato());
        $this->assertEquals(10, $dettaglioPagamento->getPenalitaPagamentiRitardati());
        $this->assertEquals('2000-01-01', $dettaglioPagamento->getDataDecorrenzaPenale());
        $this->assertEquals('codice', $dettaglioPagamento->getCodicePagamento());
    }
}