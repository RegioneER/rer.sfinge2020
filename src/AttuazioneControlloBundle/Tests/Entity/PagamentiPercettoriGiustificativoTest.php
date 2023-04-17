<?php

namespace AttuazioneControlloBundle\Tests\Entity;
use AttuazioneControlloBundle\Entity\PagamentiPercettoriGiustificativo;
use AttuazioneControlloBundle\Entity\GiustificativoPagamento;
use AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo;
use PHPUnit\Framework\TestCase;
class PagamentiPercettoriGiustificativoTest extends TestCase {

	/**
	 * @var GiustificativoPagamento
	 */
	protected $giustificativo;

	/**
	 * @var PagamentiPercettoriGiustificativo
	 */
	protected $percettore;

    public function setUp() {
		parent::setUp();
		
		$this->giustificativo = new GiustificativoPagamento();
		$this->percettore = new PagamentiPercettoriGiustificativo();
		$this->percettore->setGiustificativoPagamento($this->giustificativo);
		$this->giustificativo->addPagamentiPercettori($this->percettore);
	}
	
	public function testRiferimenti(): void
	{
		$this->assertSame($this->percettore, $this->giustificativo->getPagamentiPercettori()->first());
		$this->assertSame($this->giustificativo, $this->percettore->getGiustificativoPagamento());
	}

	public function testGiustificativoNonPresente(): void
	{
		$percettore = new PagamentiPercettoriGiustificativo();
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Giustificativo non presente');

		$percettore->aggiornaDaGiustificativo();
	}
    public function testCodiceFiscaleAggiornaDagiustificativo(): void {
		$this->giustificativo->setCodiceFiscaleFornitore('codice_fiscale');

		$this->percettore->aggiornaDaGiustificativo();

		$this->assertEquals('codice_fiscale', $this->percettore->getCodiceFiscale());
	}

	public function testImportoAggiornaDagiustificativo(): void {
		$voce = new VocePianoCostoGiustificativo();
		$voce->setGiustificativoPagamento($this->giustificativo);
		$voce->setImportoApprovato(1000); 
		$this->giustificativo->addVociPianoCosto($voce);

		$this->percettore->aggiornaDaGiustificativo();

		$this->assertEquals(1000, $this->percettore->getImporto());
	}
	
}
