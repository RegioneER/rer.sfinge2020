<?php

namespace RichiesteBundle\Ricerche;

use RichiesteBundle\Form\Entity\RicercaRichiesta;
use SfingeBundle\Entity\ProceduraPA;
use Doctrine\ORM\EntityManager;

class RicercaProcedurePA extends RicercaRichiesta
{
    public function getNomeMetodoRepository()
    {
        return 'getQueryRichiesteProcedurePA';
    }

    public function getTipo()
    {
        return ProceduraPA::TIPO;
    }

    public function getQueryRicercaProcedura(EntityManager $em, array $options){
        return $em
        ->getRepository("SfingeBundle:ProceduraPA")
        ->getProcedureVisibiliPA($this->getUtente());
    }

	public function getType()
    {
        return "RichiesteBundle\Form\RicercaRichiestaProceduraPAType";
    }
}
