<?php

namespace IstruttorieBundle\Tests\GestoriPianiCosto;

use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use IstruttorieBundle\GestoriPianoCosto\GestorePianoCostoBando_69;
use IstruttorieBundle\GestoriPianoCosto\GestorePianoCostoBando_104;

class GestorePianoCostoBando_104Test extends GestorePianoCostoBando_69Test {
    /** @var IstruttoriaRichiesta */
    protected $istruttoria;

    /** @var GestorePianoCostoBando_69 */
    protected $gestore;

    public function setUp() {
        parent::setUp();

        $this->gestore = new GestorePianoCostoBando_104($this->container);
    }
}
