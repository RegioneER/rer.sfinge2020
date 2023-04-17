<?php

namespace RichiesteBundle\Ricerche;

use RichiesteBundle\Form\Entity\RicercaRichiesta;
use Doctrine\ORM\EntityManager;
use RichiesteBundle\Form\RicercaRichiestaLatoPAType;
use SfingeBundle\Entity\ProceduraPA;

class RicercaRichiestaLatoPA extends RicercaRichiesta {
    /**
     * @var int|null
     */
    protected $id;

    public function getId(): ?int {
        return $this->id;
    }

    public function setId($id): self {
        $this->id = $id;
        return $this;
    }

    public function getTipo() {
        return ProceduraPA::TIPO;
    }

    public function getNomeRepository() {
        return "RichiesteBundle:Richiesta";
    }

    public function getNomeMetodoRepository() {
        return "getRichiesteVisibiliPA";
    }

    public function getQueryRicercaProcedura(EntityManager $em, array $options) {
        return $em
            ->getRepository("SfingeBundle\Entity\Procedura")
            ->getProcedureVisibiliPA($this->getUtente());
    }

    public function getType() {
        return RicercaRichiestaLatoPAType::class;
    }
}
