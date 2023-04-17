<?php

namespace MonitoraggioBundle\Tests\Service\GestoriFinanziamento;

use SoggettoBundle\Entity\OrganismoIntermedio;
use MonitoraggioBundle\Service\GestoriFinanziamento\Regione;
use AttuazioneControlloBundle\Entity\ModalitaPagamento;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;
use AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico;

class RegioneTest extends TestGestoreFinanziamento {
    public function setUp() {
        parent::setUp();
        $this->richiesta->getIstruttoria()->setTipologiaSoggetto('PUBBLICO');
        $this->setSoggetto(new OrganismoIntermedio());
        $this->gestore = new Regione($this->container, $this->richiesta);
    }

    public function testMandatoSaldoSenzaVariazioniSenzaNecessitaModifica(): void {
        $this->setContributoAmmesso(100.0);  //contributo concesso
        $this->setCostoAmmesso(100.0); //costo ammesso

        $pagamento = $this->creaPagamento(ModalitaPagamento::UNICA_SOLUZIONE);
        $pagamento->setImportoRendicontatoAmmesso(100); // Rendicontato ammesso
        $pagamento->getMandatoPagamento()->setImportoPagato(100); //Contributo erogato
        $giustificativo = $this->addGiustificativo($pagamento);
        $giustificativo->setImportoApprovato(100); //rendicontato ammesso

        $this->aggiungiFinanziamento($this->tc33UE, 50.0);
        $this->aggiungiFinanziamento($this->tc33Stato, 35.0);
        $this->aggiungiFinanziamento($this->tc33Regione, 15.0);

        $this->gestore->aggiornaFinanziamento();

        $this->assertFinanziamento($this->tc33UE, 50.0);
        $this->assertFinanziamento($this->tc33Stato, 35.0);
        $this->assertFinanziamento($this->tc33Regione, 15.0);
    }

    public function testMandatoSaldoSenzaVariazioni(): void {
        $this->setCasoFinanziamento();

        $this->aggiungiFinanziamento($this->tc33UE, 20.0);
        $this->aggiungiFinanziamento($this->tc33Stato, 15.0);
        $this->aggiungiFinanziamento($this->tc33Regione, 66.0);

        $this->gestore->aggiornaFinanziamento();

        $this->assertFinanziamento($this->tc33UE, 50.0);
        $this->assertFinanziamento($this->tc33Stato, 35.0);
        $this->assertFinanziamento($this->tc33Regione, 15.0);
    }

    protected function setCasoFinanziamento(): void {
        $this->setContributoAmmesso(100.0);  //contributo concesso
        $this->setCostoAmmesso(100.0); //costo ammesso

        $pagamento = $this->creaPagamento(ModalitaPagamento::UNICA_SOLUZIONE);
        $pagamento->setImportoRendicontatoAmmesso(99); // Rendicontato ammesso
        $pagamento->getMandatoPagamento()->setImportoPagato(40); //Contributo erogato
        $giustificativo = $this->addGiustificativo($pagamento);
        $giustificativo->setImportoApprovato(99); //rendicontato ammesso per giustificativo
    }

    public function testVerificaImportoAmmessoObiettivoSpecifico(): void {
        $this->setCasoFinanziamento();
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
}
