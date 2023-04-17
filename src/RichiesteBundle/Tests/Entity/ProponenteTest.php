<?php

namespace RichiesteBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Entity\VocePianoCosto;
use RichiesteBundle\Entity\PianoCosto;
use Doctrine\Common\Collections\Collection;

class ProponenteTest extends TestCase
{
    /**
     * @var Proponente
     */
    protected $proponente;

    public function setUp()
    {
        $this->proponente = new Proponente();
    }
    public function testOrdinaVociPianoCosto()
    {
        $this->addVoce('2', 2);
        $this->addVoce('1', 1);
        $this->addVoce('0', 0);

        $this->proponente->ordinaVociPianoCosto();
        $c = 0;
        foreach($this->proponente->getVociPianoCosto() as $voce){
            $this->assertEquals($c, $voce->getPianoCosto()->getOrdinamento());
            $c++;
        }

        $this->assertInstanceOf(Collection::class, $this->proponente->getVociPianoCosto());
    }

    protected function addVoce($codice, $ordinamento){
        $piano = new PianoCosto();
        $piano->setOrdinamento($ordinamento);
        $piano->setCodice($codice);
        $voce = new VocePianoCosto();
        $voce->setProponente($this->proponente);
        $voce->setPianoCosto($piano);
        $this->proponente->addVociPianoCosto($voce);
    }
}