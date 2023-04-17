<?php

namespace RichiesteBundle\Ricerche;

use RichiesteBundle\Form\Entity\RicercaRichiesta;
use SfingeBundle\Entity\ProceduraPA;
use Doctrine\ORM\EntityManager;

class RicercaAssistenzaTecnica extends RicercaRichiesta
{
    public function getNomeMetodoRepository()
    {
        return 'getRichiesteAt';
    }

    public function getTipo()
    {
        return 'ASSISTENZA_TECNICA';
    }

    public function getQueryRicercaProcedura(EntityManager $em, array $options)
    {
        return $em
            ->getRepository("SfingeBundle\Entity\Procedura")
            ->getProcedureAt();
    }
}
