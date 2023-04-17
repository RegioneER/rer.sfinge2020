<?php

namespace RichiesteBundle\Ricerche;

use RichiesteBundle\Form\Entity\RicercaRichiesta;
use SfingeBundle\Entity\ProceduraPA;
use Doctrine\ORM\EntityManager;

class RicercaAcquisizione extends RicercaRichiesta
{
    public function getNomeMetodoRepository()
    {
        return 'getRichiesteAcquisizione';
    }

    public function getTipo()
    {
        return 'ACQUISIZIONI';
    }

    public function getQueryRicercaProcedura(EntityManager $em, array $options)
    {
        return $em
            ->getRepository("SfingeBundle\Entity\Procedura")
            ->getProcedureAt();
    }
}
