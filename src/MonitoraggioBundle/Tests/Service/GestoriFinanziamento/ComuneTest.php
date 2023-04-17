<?php

namespace MonitoraggioBundle\Tests\Service\GestoriFinanziamento;

use SoggettoBundle\Entity\ComuneUnione;
use MonitoraggioBundle\Service\GestoriFinanziamento\Comune;
use AttuazioneControlloBundle\Entity\ModalitaPagamento;
use GeoBundle\Entity\GeoComune;
use MonitoraggioBundle\Entity\TC33FonteFinanziaria;
use AttuazioneControlloBundle\Entity\Finanziamento;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;
use AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico;
use MonitoraggioBundle\Entity\TC16LocalizzazioneGeografica;

class ComuneTest extends TestGestoreFinanziamento {
    /**
     * @var GeoComune
     */
    protected $geoComune;

    public function setUp() {
        parent::setUp();
        $this->richiesta->getIstruttoria()->setTipologiaSoggetto('PUBBLICO');
        $this->gestore = new Comune($this->container, $this->richiesta);
        $this->geoComune = new GeoComune();

        $comune = new ComuneUnione();
        $comune->setComune($this->geoComune);
        $this->setSoggetto($comune);
        $this->geoComune->setTc16LocalizzazioneGeografica(new TC16LocalizzazioneGeografica);
    }

    public function testSenzaNecessitaModifica(): void {
        $this->setContributoAmmesso(100.0);  //contributo concesso
        $this->setCostoAmmesso(100.0); //costo ammesso

        $pagamento = $this->creaPagamento(ModalitaPagamento::UNICA_SOLUZIONE);
        $pagamento->setImportoRendicontatoAmmesso(100); // Rendicontato ammesso
        $pagamento->getMandatoPagamento()->setImportoPagato(100); //Contributo erogato
        // $this->setImportoErogabile($pagamento, 100);
        $giustificativo = $this->addGiustificativo($pagamento);
        $giustificativo->setImportoApprovato(100); //rendicontato ammesso

        $this->aggiungiFinanziamento($this->tc33UE, 50.0);
        $this->aggiungiFinanziamento($this->tc33Stato, 35.0);
        $this->aggiungiFinanziamento($this->tc33Regione, 15.0);
        $this->aggiungiFinanziamento($this->tc33Comune, 0.0);

        $this->gestore->aggiornaFinanziamento();

        $this->assertFinanziamento($this->tc33UE, 50.0);
        $this->assertFinanziamento($this->tc33Stato, 35.0);
        $this->assertFinanziamento($this->tc33Regione, 15.0);
        $this->aggiungiFinanziamento($this->tc33Comune, 0.0);
    }

    public function testMandatoSaldoSenzaVariazioniSenzaFinanziamenti(): void {
        $this->setContributoAmmesso(100.0);  //contributo concesso
        $this->setCostoAmmesso(100.0); //costo ammesso

        $pagamento = $this->creaPagamento(ModalitaPagamento::UNICA_SOLUZIONE);
        $pagamento->setImportoRendicontatoAmmesso(100); // Rendicontato ammesso
        $pagamento->getMandatoPagamento()->setImportoPagato(100); //Contributo erogato
        $giustificativo = $this->addGiustificativo($pagamento);
        $giustificativo->setImportoApprovato(100); //rendicontato ammesso

        $this->gestore->aggiornaFinanziamento();

        $this->assertFinanziamento($this->tc33UE, 50.0);
        $this->assertFinanziamento($this->tc33Stato, 35.0);
        $this->assertFinanziamento($this->tc33Regione, 15.0);
        $this->assertFinanziamento($this->tc33Comune, 0.0);
    }

    public function testMandatoSaldoSenzaVariazioni(): void {
        $this->setCasoFinanziamento();

        $this->aggiungiFinanziamento($this->tc33UE, 10.0);
        $this->aggiungiFinanziamento($this->tc33Stato, 25.0);
        $this->aggiungiFinanziamento($this->tc33Regione, 35.0);
        $this->aggiungiFinanziamento($this->tc33Comune, 440.0);

        $this->gestore->aggiornaFinanziamento(true);

        $this->assertFinanziamento($this->tc33UE, 50.0);
        $this->assertFinanziamento($this->tc33Stato, 35.0);
        $this->assertFinanziamento($this->tc33Regione, 15.0);
        $this->assertFinanziamento($this->tc33Comune, 0.0);
    }

    protected function setCasoFinanziamento(){
        $this->setContributoAmmesso(100.0);  //contributo concesso
        $this->setCostoAmmesso(100.0); //costo ammesso

        $pagamento = $this->creaPagamento(ModalitaPagamento::UNICA_SOLUZIONE);
        $pagamento->setImportoRendicontatoAmmesso(99); // Rendicontato ammesso
        $pagamento->getMandatoPagamento()->setImportoPagato(40); //Contributo erogato
        $giustificativo = $this->addGiustificativo($pagamento);
        $giustificativo->setImportoApprovato(99); //rendicontato ammesso per giustificativo
    }

    protected function assertFinanziamento(TC33FonteFinanziaria $fondo, float $importoAtteso): Finanziamento {
        $finanziamento = parent::assertFinanziamento($fondo, $importoAtteso);
        $codiceFondo = $fondo->getCodFondo();

        if ($codiceFondo == TC33FonteFinanziaria::COMUNE) {
            $this->assertEquals($this->geoComune->getTc16LocalizzazioneGeografica(), 
                                $finanziamento->getTc16LocalizzazioneGeografica(),
                                'Il comune non ha definito la propria localizzazione geografica'
            );
        } else if ($codiceFondo == TC33FonteFinanziaria::REGIONE){
			$this->assertNotNull(
                    $finanziamento->getTc16LocalizzazioneGeografica(),
                    'La regione non ha definito la propria localizzazione geografica'
            );
        }
        else{
            $this->assertNull($finanziamento->getTc16LocalizzazioneGeografica());
        }

        return $finanziamento;
    }

    protected function aggiungiFinanziamento(TC33FonteFinanziaria $fonte, float $importo): Finanziamento {
		$finanziamento = parent::aggiungiFinanziamento($fonte, $importo);
		if (TC33FonteFinanziaria::COMUNE == $fonte->getCodFondo()) {
			$finanziamento->setTc16LocalizzazioneGeografica($this->geoComune->getTc16LocalizzazioneGeografica());
		} else if(TC33FonteFinanziaria::REGIONE == $fonte->getCodFondo()){
            $finanziamento->setTc16LocalizzazioneGeografica(new TC16LocalizzazioneGeografica);
        }
		return $finanziamento;
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
