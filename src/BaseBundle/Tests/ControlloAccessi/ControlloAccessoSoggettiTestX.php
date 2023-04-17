<?php

namespace BaseBundle\Tests\ControlloAccessi;

use PHPUnit\Framework\TestCase;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Entity\Proponente;
use SoggettoBundle\Entity\Azienda;
use BaseBundle\ControlloAccessi\ControlloAccessoSoggetti;
use AnagraficheBundle\Entity\Persona;
use SoggettoBundle\Entity\IncaricoPersona;
use SfingeBundle\Entity\Utente;
use SoggettoBundle\Entity\TipoIncarico;

class ControlloAccessoSoggettiTestX extends TestCase {
    /**
     * @var Richiesta
     */
    protected $richiesta;

    /**
     * @var ControlloAccessoSoggetti
     */
    protected $controlloAccesso;

    public function setUp() {
        $this->richiesta = new Richiesta();
        $proponente = new Proponente($this->richiesta);
        $proponente->setMandatario(true);
        $this->richiesta->addProponenti($proponente);
        $soggetto = new Azienda();
        $proponente->setSoggetto($soggetto);

        $this->controlloAccesso = new ControlloAccessoSoggetti();
    }

    public function addIncaricato(string $ruolo): Persona {
        $incarico = new IncaricoPersona();
        $soggetto = $this->richiesta->getSoggetto();
        $incarico->setSoggetto($soggetto);
        $soggetto->addIncarichiPersone($incarico);
        $persona = new Persona();
        $incarico->setIncaricato($persona);
        $utenteRichiesta = new Utente();
        $persona->setUtente($utenteRichiesta);
        $tipoIncarico = new TipoIncarico();
        $tipoIncarico->setCodice($ruolo);
        $incarico->setTipoIncarico($tipoIncarico);

        return $persona;
    }

    /**
     * @dataProvider accessoDataProviderRoleUtente
     */
    public function testAccessoRoleUtente(string $ruolo, bool $accesso): void {
        $persona = $this->addIncaricato($ruolo);
        $utente = $persona->getUtente();

        $res = $this->controlloAccesso->verificaAccesso_ROLE_UTENTE($utente, $this->richiesta, []);

        $this->assertEquals($accesso, $res);
    }

    public function accessoDataProviderRoleUtente(): array {
        return [
            /* accesso autorizzato */
            [TipoIncarico::UTENTE_PRINCIPALE, true],
            /* accesso negato */
            [TipoIncarico::DELEGATO, false],
            [TipoIncarico::LR, false],
            [TipoIncarico::AFFILIATO, false],
            [TipoIncarico::CONSULENTE, false],
            [TipoIncarico::OPERATORE, false],
            [TipoIncarico::OPERATORE_RICHIESTA, false],
        ];
    }

    public function testTentatoAccessoAltroUtente() {
        $utente = new Utente();

        $res = $this->controlloAccesso->verificaAccesso_ROLE_UTENTE($utente, $this->richiesta, []);

        $this->assertFalse($res);
    }
}
