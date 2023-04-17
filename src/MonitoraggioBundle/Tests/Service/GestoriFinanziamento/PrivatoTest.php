<?php

namespace MonitoraggioBundle\Tests\Service\GestoriFinanziamento;

use AttuazioneControlloBundle\Entity\ModalitaPagamento;
use MonitoraggioBundle\Service\GestoriFinanziamento\Privato;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;
use AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico;

class PrivatoTest extends TestGestoreFinanziamento {
    public function setUp() {
        parent::setUp();
        $this->richiesta->getIstruttoria()->setTipologiaSoggetto('PRIVATO');
        $this->gestore = new Privato($this->container, $this->richiesta);
    }

    public function testMandatoSaldoSenzaVariazioniSenzaNecessitaModifica(): void {
        $this->setContributoAmmesso(100);  //contributo concesso
        $this->setCostoAmmesso(100); //costo ammesso

        $pagamento = $this->creaPagamento(ModalitaPagamento::UNICA_SOLUZIONE);
        $pagamento->setImportoRendicontatoAmmesso(100); // Rendicontato ammesso
        $pagamento->getMandatoPagamento()->setImportoPagato(100); //Contributo erogato
        $giustificativo = $this->addGiustificativo($pagamento);
        $giustificativo->setImportoApprovato(100); //rendicontato ammesso

        $this->aggiungiFinanziamento($this->tc33UE, 50);
        $this->aggiungiFinanziamento($this->tc33Stato, 35);
        $this->aggiungiFinanziamento($this->tc33Regione, 15);
        $this->aggiungiFinanziamento($this->tc33Privato, 0);

        $this->gestore->aggiornaFinanziamento();

        $this->assertFinanziamento($this->tc33UE, 50);
        $this->assertFinanziamento($this->tc33Stato, 35);
        $this->assertFinanziamento($this->tc33Regione, 15);
        $this->assertFinanziamento($this->tc33Privato, 0);
    }

    public function testMandatoSaldoSenzaVariazioniSenzaFinanziamenti(): void {
        $this->setContributoAmmesso(100);  //contributo concesso
        $this->setCostoAmmesso(100); //costo ammesso

        $pagamento = $this->creaPagamento(ModalitaPagamento::UNICA_SOLUZIONE);
        $pagamento->setImportoRendicontatoAmmesso(100); // Rendicontato ammesso
        $pagamento->getMandatoPagamento()->setImportoPagato(100); //Contributo erogato
        $giustificativo = $this->addGiustificativo($pagamento);
        $giustificativo->setImportoApprovato(100); //rendicontato ammesso

        $this->gestore->aggiornaFinanziamento();

        $this->assertFinanziamento($this->tc33UE, 50);
        $this->assertFinanziamento($this->tc33Stato, 35);
        $this->assertFinanziamento($this->tc33Regione, 15);
        $this->assertFinanziamento($this->tc33Privato, 0);
    }

    public function testMandatoSaldoSenzaVariazioniFinanziamentoErrato(): void {
        $this->setContributoAmmesso(100);  //contributo concesso
        $this->setCostoAmmesso(100); //costo ammesso

        $pagamento = $this->creaPagamento(ModalitaPagamento::UNICA_SOLUZIONE);
        $pagamento->setImportoRendicontatoAmmesso(100); // Rendicontato ammesso
        $pagamento->getMandatoPagamento()->setImportoPagato(100); //Contributo erogato
        $giustificativo = $this->addGiustificativo($pagamento);
        $giustificativo->setImportoApprovato(100); //rendicontato ammesso per giustificativo

        $this->aggiungiFinanziamento($this->tc33UE, 30);
        $this->aggiungiFinanziamento($this->tc33Stato, 35);
        $this->aggiungiFinanziamento($this->tc33Regione, 15);
        $this->aggiungiFinanziamento($this->tc33Privato, 10);

        $this->gestore->aggiornaFinanziamento();

        $this->assertFinanziamento($this->tc33UE, 50);
        $this->assertFinanziamento($this->tc33Stato, 35);
        $this->assertFinanziamento($this->tc33Regione, 15);
        $this->assertFinanziamento($this->tc33Privato, 0);
    }

    public function testMandatoSaldoSenzaVariazioni(): void {
        $this->setContributoAmmesso(100);  //contributo concesso
        $this->setCostoAmmesso(100); //costo ammesso

        $pagamento = $this->creaPagamento(ModalitaPagamento::UNICA_SOLUZIONE);
        $pagamento->setImportoRendicontatoAmmesso(99); // Rendicontato ammesso
        $pagamento->getMandatoPagamento()->setImportoPagato(40); //Contributo erogato
        $giustificativo = $this->addGiustificativo($pagamento);
        $giustificativo->setImportoApprovato(99); //rendicontato ammesso per giustificativo

        $this->aggiungiFinanziamento($this->tc33UE, 40);
        $this->aggiungiFinanziamento($this->tc33Stato, 35);
        $this->aggiungiFinanziamento($this->tc33Regione, 6);
        $this->aggiungiFinanziamento($this->tc33Privato, 60);

        $this->gestore->aggiornaFinanziamento();

        $this->assertFinanziamento($this->tc33UE, 50);
        $this->assertFinanziamento($this->tc33Stato, 35);
        $this->assertFinanziamento($this->tc33Regione, 15);
        $this->assertFinanziamento($this->tc33Privato, 0);
    }

    public function testMandatoSaldoConVariazioniSenzaNecessitaModifica(): void {
        $this->setContributoAmmesso(50);  //contributo concesso
        $this->setCostoAmmesso(50); //costo ammesso

        $this->aggiungiVariazione(100, 100);

        $pagamento = $this->creaPagamento(ModalitaPagamento::UNICA_SOLUZIONE);
        $pagamento->setImportoRendicontatoAmmesso(100); // Rendicontato ammesso
        $pagamento->getMandatoPagamento()->setImportoPagato(100); //Contributo erogato
        $giustificativo = $this->addGiustificativo($pagamento);
        $giustificativo->setImportoApprovato(100); //rendicontato ammesso

        $this->aggiungiFinanziamento($this->tc33UE, 50);
        $this->aggiungiFinanziamento($this->tc33Stato, 35);
        $this->aggiungiFinanziamento($this->tc33Regione, 15);
        $this->aggiungiFinanziamento($this->tc33Privato, 0);

        $this->gestore->aggiornaFinanziamento();

        $this->assertFinanziamento($this->tc33UE, 50);
        $this->assertFinanziamento($this->tc33Stato, 35);
        $this->assertFinanziamento($this->tc33Regione, 15);
        $this->assertFinanziamento($this->tc33Privato, 0);
    }

    public function testVerificaImportoAmmessoObiettivoSpecifico(): void {
        $this->setDatiCasoFinanziamento();
        $this->gestore->aggiornaFinanziamento();

        

        /** @var RichiestaProgramma $programma */
        $programma = $this->richiesta->getMonProgrammi()->first();
        $this->assertInstanceOf(RichiestaProgramma::class, $programma);
        
        $livelli = $programma->getLivelliGerarchiciObiettivoSpecifico();
        $this->assertCount(1, $livelli);

        /** @var RichiestaLivelloGerarchico $livello */
        $livello = $livelli->first();
        $this->assertInstanceOf(RichiestaLivelloGerarchico::class, $livello);

        $this->assertEquals(100, $livello->getImportoCostoAmmesso(), '', 0.1);

    }

    /**
     * Imposta il caso per i seguenti valori
     * UE       =>  20
     * Stato    =>  14
     * Regione   =>  6
     * Privato   =>  60
     */
    protected function setDatiCasoFinanziamento(): void{
        $this->setContributoAmmesso(100);  //contributo concesso
        $this->setCostoAmmesso(100); //costo ammesso

        $pagamento = $this->creaPagamento(ModalitaPagamento::UNICA_SOLUZIONE);
        $pagamento->setImportoRendicontatoAmmesso(99); // Rendicontato ammesso
        $pagamento->getMandatoPagamento()->setImportoPagato(40); //Contributo erogato
        $giustificativo = $this->addGiustificativo($pagamento);
        $giustificativo->setImportoApprovato(99); //rendicontato ammesso per giustificativo

    }
}
