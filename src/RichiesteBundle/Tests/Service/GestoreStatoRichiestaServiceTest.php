<?php

namespace RichiesteBundle\Tests\Service;

use RichiesteBundle\Service\GestoreStatoRichiestaService;
use BaseBundle\Tests\Service\TestBaseService;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Service\IGestoreStatoRichiesta;

class GestoreStatoRichiestaServiceTest extends TestBaseService {
    public function testGestore() {
        $service = new GestoreStatoRichiestaService($this->container);
        $richiesta = new Richiesta();

        $result = $service->getGestore($richiesta);

        $this->assertInstanceOf(IGestoreStatoRichiesta::class, $result);
    }
}
