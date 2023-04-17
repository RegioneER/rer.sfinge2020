<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 28/01/16
 * Time: 16:17
 */

namespace RichiesteBundle\Ricerche;


use AnagraficheBundle\Form\Entity\RicercaPersone;

class RicercaPersonaReferente extends RicercaPersone
{

    public function getType()
    {
        return "RichiesteBundle\Form\RicercaPersonaReferenteType";
    }

    public function mostraNumeroElementi()
    {
        return false;
    }	

}