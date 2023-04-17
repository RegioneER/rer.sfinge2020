<?php

namespace AttuazioneControlloBundle\Tests\Service\Istruttoria;

use BaseBundle\Tests\Service\TestBaseService;
use RichiesteBundle\Entity\Richiesta;
use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use AttuazioneControlloBundle\Entity\VariazionePianoCosti;
use AttuazioneControlloBundle\Service\Istruttoria\Variazioni\GestoreVariazioniDatiBancariBase;
use AttuazioneControlloBundle\Entity\VariazioneDatiBancari;
use RichiesteBundle\Entity\Proponente;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Constraint\IsType;

class GestoreVariazioniDatiBancariTest extends TestBaseService {
    /**
     * @var Richiesta
     */
    protected $richiesta;

    /**
     * @var VariazionePianoCosti
     */
    protected $variazione;

    /**
     * @var GestoreVariazioniDatiBancariBase
     */
    protected $gestore;

    public function setUp() {
        parent::setUp();

        $this->richiesta = new Richiesta();
        $atc = new AttuazioneControlloRichiesta();
		$atc->setRichiesta($this->richiesta);
		$mandatario = new Proponente($this->richiesta);
		$mandatario->setMandatario(true);
		$this->richiesta->addProponenti($mandatario);

        $this->variazione = new VariazioneDatiBancari($atc);
        $this->variazione->setAttuazioneControlloRichiesta($atc);

        $this->gestore = new GestoreVariazioniDatiBancariBase($this->variazione, $this->container);
	}
	
	public function testDettaglioDatibancari(): void
	{
		$proponente = $this->richiesta->getMandatario();
		/** @var MockObject $templateMock */
		$templateMock = $this->container->get('templating');
		$templateMock->expects($this->once())
		->method('renderResponse')
		->with(new IsType(IsType::TYPE_STRING),
		$this->logicalAnd(
			$this->logicalNot($this->isEmpty()),
			$this->contains($this->variazione)
		),
		$this->any()
	);
		$this->gestore->dettaglioDatiBancari($proponente);
	}
}
