<?php

namespace RichiesteBundle\Ricerche;

use RichiesteBundle\Form\Entity\RicercaRichiesta;
use SfingeBundle\Entity\ProceduraPA;
use Doctrine\ORM\EntityManager;

class RicercaIngegneriaFinanziaria extends RicercaRichiesta
{
    public function getNomeMetodoRepository()
    {
        return 'getRichiesteIngIf';
    }

    public function getTipo()
    {
        return 'INGEGNERIA_FINANZIARIA';
    }

    public function getQueryRicercaProcedura(EntityManager $em, array $options)
    {
        return $em
            ->getRepository("SfingeBundle\Entity\Procedura")
            ->getProcedureIngFin();
    }
}
