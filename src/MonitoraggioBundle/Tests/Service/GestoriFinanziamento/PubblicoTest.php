<?php

namespace MonitoraggioBundle\Tests\Service\GestoriFinanziamento;

use MonitoraggioBundle\Service\GestoriFinanziamento\Pubblico;
use AttuazioneControlloBundle\Entity\ModalitaPagamento;
use AttuazioneControlloBundle\Entity\Pagamento;
use AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneChecklistPagamento;
use AttuazioneControlloBundle\Entity\Istruttoria\ChecklistPagamento;
use AttuazioneControlloBundle\Entity\Istruttoria\ElementoChecklistPagamento;
use AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneElementoChecklistPagamento;
use AttuazioneControlloBundle\Entity\Istruttoria\SezioneChecklistPagamento;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;
use AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico;

class PubblicoTest extends TestGestoreFinanziamento {
    public function setUp() {
        parent::setUp();
        $this->richiesta->getIstruttoria()->setTipologiaSoggetto('PUBBLICO');
        $this->gestore = new Pubblico($this->container, $this->richiesta);
    }

    public function testSenzaNecessitaModifica(): void {
        $this->setContributoAmmesso(100);  //contributo concesso
        $this->setCostoAmmesso(100); //costo ammesso

        $pagamento = $this->creaPagamento(ModalitaPagamento::UNICA_SOLUZIONE);
        $pagamento->setImportoRendicontatoAmmesso(100); // Rendicontato ammesso
        $pagamento->getMandatoPagamento()->setImportoPagato(100); //Contributo erogato
        // $this->setImportoErogabile($pagamento, 100);
        $giustificativo = $this->addGiustificativo($pagamento);
        $giustificativo->setImportoApprovato(100); //rendicontato ammesso

        $this->aggiungiFinanziamento($this->tc33UE, 50);
        $this->aggiungiFinanziamento($this->tc33Stato, 35);
        $this->aggiungiFinanziamento($this->tc33Regione, 15);
        $this->aggiungiFinanziamento($this->tc33AltroPubblico, 0);

        $this->gestore->aggiornaFinanziamento();

        $this->assertFinanziamento($this->tc33UE, 50);
        $this->assertFinanziamento($this->tc33Stato, 35);
        $this->assertFinanziamento($this->tc33Regione, 15);
        $this->aggiungiFinanziamento($this->tc33AltroPubblico, 0);
    }

    public function testSenzaFinanziamenti(): void {
        $this->setContributoAmmesso(100);  //contributo concesso
        $this->setCostoAmmesso(100); //costo ammesso

        $pagamento = $this->creaPagamento(ModalitaPagamento::UNICA_SOLUZIONE);
        $pagamento->setImportoRendicontatoAmmesso(100); // Rendicontato ammesso
        $pagamento->getMandatoPagamento()->setImportoPagato(100); //Contributo erogato
        // $this->setImportoErogabile($pagamento, 100);
        $giustificativo = $this->addGiustificativo($pagamento);
        $giustificativo->setImportoApprovato(100); //rendicontato ammesso

        $this->gestore->aggiornaFinanziamento();

        $this->assertFinanziamento($this->tc33UE, 50);
        $this->assertFinanziamento($this->tc33Stato, 35);
        $this->assertFinanziamento($this->tc33Regione, 15);
        $this->aggiungiFinanziamento($this->tc33AltroPubblico, 0);
    }

    public function testFinanziamentoErrato(): void {
        $this->setCasoFinanziamento();
        $this->aggiungiFinanziamento($this->tc33UE, 30);
        $this->aggiungiFinanziamento($this->tc33Stato, 35);
        $this->aggiungiFinanziamento($this->tc33Regione, 15);
        $this->aggiungiFinanziamento($this->tc33AltroPubblico, 10);

        $this->gestore->aggiornaFinanziamento();

        $this->assertFinanziamento($this->tc33UE, 50);
        $this->assertFinanziamento($this->tc33Stato, 35);
        $this->assertFinanziamento($this->tc33Regione, 15);
        $this->assertFinanziamento($this->tc33AltroPubblico, 0);
    }

    protected function setCasoFinanziamento(): void {
        $this->setContributoAmmesso(100);  //contributo concesso
        $this->setCostoAmmesso(100); //costo ammesso

        $pagamento = $this->creaPagamento(ModalitaPagamento::UNICA_SOLUZIONE);
        $pagamento->setImportoRendicontatoAmmesso(100); // Rendicontato ammesso
        $pagamento->getMandatoPagamento()->setImportoPagato(100); //Contributo erogato
        // $this->setImportoErogabile($pagamento, 100);
        $giustificativo = $this->addGiustificativo($pagamento);
        $giustificativo->setImportoApprovato(100); //rendicontato ammesso per giustificativo
    }

    public function test(): void {
        $this->setContributoAmmesso(100);  //contributo concesso
        $this->setCostoAmmesso(100); //costo ammesso

        $pagamento = $this->creaPagamento(ModalitaPagamento::UNICA_SOLUZIONE);
        $pagamento->setImportoRendicontatoAmmesso(99); // Rendicontato ammesso
        $pagamento->getMandatoPagamento()->setImportoPagato(40); //Contributo erogato
        // $this->setImportoErogabile($pagamento, 40);
        $giustificativo = $this->addGiustificativo($pagamento);
        $giustificativo->setImportoApprovato(99); //rendicontato ammesso per giustificativo

        $this->aggiungiFinanziamento($this->tc33UE, 20);
        $this->aggiungiFinanziamento($this->tc33Stato, 14);
        $this->aggiungiFinanziamento($this->tc33Regione, 6);
        $this->aggiungiFinanziamento($this->tc33AltroPubblico, 60);

        $this->gestore->aggiornaFinanziamento();

        $this->assertFinanziamento($this->tc33UE, 50);
        $this->assertFinanziamento($this->tc33Stato, 35);
        $this->assertFinanziamento($this->tc33Regione, 15);
        $this->assertFinanziamento($this->tc33AltroPubblico, 0);
    }

    public function testConVariazioniSenzaNecessitaModifica(): void {
        $this->setContributoAmmesso(50);  //contributo concesso
        $this->setCostoAmmesso(50); //costo ammesso

        $this->aggiungiVariazione(100, 100);

        $pagamento = $this->creaPagamento(ModalitaPagamento::UNICA_SOLUZIONE);
        $pagamento->setImportoRendicontatoAmmesso(100); // Rendicontato ammesso
        $pagamento->getMandatoPagamento()->setImportoPagato(100); //Contributo erogato
        $this->setImportoErogabile($pagamento, 100);
        $giustificativo = $this->addGiustificativo($pagamento);
        $giustificativo->setImportoApprovato(100); //rendicontato ammesso

        $this->aggiungiFinanziamento($this->tc33UE, 50);
        $this->aggiungiFinanziamento($this->tc33Stato, 35);
        $this->aggiungiFinanziamento($this->tc33Regione, 15);
        $this->aggiungiFinanziamento($this->tc33AltroPubblico, 0);

        $this->gestore->aggiornaFinanziamento();

        $this->assertFinanziamento($this->tc33UE, 50);
        $this->assertFinanziamento($this->tc33Stato, 35);
        $this->assertFinanziamento($this->tc33Regione, 15);
        $this->assertFinanziamento($this->tc33AltroPubblico, 0);
    }

    protected function creaPagamento(string $modalita): Pagamento {
        $pagamento = parent::creaPagamento($modalita);
        $checklistPrincipale = new ChecklistPagamento();
        $checklistPrincipale->setTipologia('PRINCIPALE');
        $checklist = new ValutazioneChecklistPagamento();
        $checklist
        ->setChecklist($checklistPrincipale)
        ->setValidata(true)
        ->setAmmissibile(true);

        $sezioneChecklist = new SezioneChecklistPagamento();
        $sezioneChecklist->setChecklist($checklist);
        $sezioneChecklist->setDescrizione('IMPORTI');

        $elementoChecklist = new ElementoChecklistPagamento();
        $elementoChecklist->setCodice('CONTRIBUTO_EROGABILE');
        $elementoChecklist->setSezioneChecklist($sezioneChecklist);
        $sezioneChecklist->addElementi($elementoChecklist);

        $istanzaElemento = new ValutazioneElementoChecklistPagamento();
        $istanzaElemento->setElemento($elementoChecklist);
        $checklist->addValutazioneElemento($istanzaElemento);

        $pagamento->addValutazioneChecklist($checklist);
        return $pagamento;
    }

    public function setImportoErogabile(Pagamento $pagamento, float $importo): void {
        /** @var ValutazioneChecklistPagamento $valutazione */
        $valutazione = $pagamento->getValutazioniChecklist()->first();
        /** @var ValutazioneElementoChecklistPagamento $elemento */
        $elemento = $valutazione->getValutazioniElementi()->filter(function (ValutazioneElementoChecklistPagamento $el) {
            return 'CONTRIBUTO_EROGABILE' == $el->getElemento()->getCodice();
        })->first();

        $elemento->setValore($importo);
        $elemento->setValoreRaw($importo);
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
