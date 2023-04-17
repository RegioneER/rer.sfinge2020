<?php

namespace AttuazioneControlloBundle\Tests\CalcoloIndicatori;

use PHPUnit\Framework\TestCase;
use RichiesteBundle\Entity\Richiesta;
use BaseBundle\Tests\Service\TestBaseService;
use SfingeBundle\Entity\Bando;

class TestIndicatore extends TestBaseService {
    /**
     * @var Richiesta
     */
    protected $richiesta;

    public function setUp(){
        parent::setUp();
        $this->richiesta = new Richiesta();
        $procedura = new Bando();
        $this->richiesta->setProcedura($procedura);
    }
}