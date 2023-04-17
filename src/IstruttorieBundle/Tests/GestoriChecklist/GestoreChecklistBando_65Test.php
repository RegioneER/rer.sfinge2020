<?php

namespace IstruttorieBundle\Tests\GestoriChecklist;

use PHPUnit\Framework\TestCase;
use IstruttorieBundle\Entity\ValutazioneChecklistIstruttoria;
use IstruttorieBundle\GestoriChecklist\GestoreChecklistBando_65;
use BaseBundle\Tests\Service\TestBaseService;
use IstruttorieBundle\Entity\ChecklistIstruttoria;
use IstruttorieBundle\Entity\ValutazioneElementoChecklistIstruttoria;
use IstruttorieBundle\Entity\ElementoChecklistIstruttoria;
use IstruttorieBundle\Entity\SezioneChecklistIstruttoria;

class GestoreChecklistBando_65Test extends TestBaseService {
	/**
	 * @var GestoreChecklistBando_65
	 */
	protected $gestore;
	/**
	 * @var ValutazioneChecklistIstruttoria
	 */
	protected $valutazione;

	/**
	 * @var SezioneChecklistIstruttoria
	 */
	protected $sezione;
	
	public function setUp() {
		parent::setUp();
		$this->valutazione = new ValutazioneChecklistIstruttoria();
		$checklist = new ChecklistIstruttoria();
		$checklist->setCodice('griglia_65');
		$checklist->setCodice(GestoreChecklistBando_65::CODICE_SEZIONE_VALUTAZIONE);
		$this->valutazione->setChecklist($checklist);
		$this->gestore = new GestoreChecklistBando_65($this->container);
		$this->sezione = new SezioneChecklistIstruttoria();
		$this->sezione->setChecklist($checklist);
	}

    public function testIsAmmissibileOk(): void {
		$this->addElementoChecklist(ElementoChecklistIstruttoria::TIPO_CHOICE, '0');
		$this->addElementoChecklist(ElementoChecklistIstruttoria::TIPO_INTEGER, '75');
		
		$res = $this->gestore->isAmmissibile($this->valutazione);

		$this->assertTrue($res);
	}

	protected function addElementoChecklist(string $tipo, $valore): void{
		$elementoScelta = new ElementoChecklistIstruttoria();
		$elementoScelta->setSezioneChecklist($this->sezione);
		$elementoScelta->setSignificativo(true);
		$elementoScelta->setTipo($tipo);
		$scelta = new ValutazioneElementoChecklistIstruttoria();
		$scelta->setElemento($elementoScelta)
			->setValore($valore)
			->setValoreRaw($valore);
		$this->valutazione->addValutazioneElemento($scelta);
	}
	
	public function testAmmissibilePunteggioInsufficente()
	{
		$this->addElementoChecklist(ElementoChecklistIstruttoria::TIPO_CHOICE, '0');
		$this->addElementoChecklist(ElementoChecklistIstruttoria::TIPO_INTEGER, '69');
		
		$res = $this->gestore->isAmmissibile($this->valutazione);

		$this->assertFalse($res);
	}

	public function testAmmissibileFormaleNonOk() {
		$this->addElementoChecklist(ElementoChecklistIstruttoria::TIPO_CHOICE, '1');
		$this->addElementoChecklist(ElementoChecklistIstruttoria::TIPO_INTEGER, '70');
		
		$res = $this->gestore->isAmmissibile($this->valutazione);

		$this->assertFalse($res);
	}

}
