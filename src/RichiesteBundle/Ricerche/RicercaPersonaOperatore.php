<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 28/01/16
 * Time: 16:17
 */

namespace RichiesteBundle\Ricerche;


use AnagraficheBundle\Form\Entity\RicercaPersone;

class RicercaPersonaOperatore extends RicercaPersone
{

    public function getType()
    {
        return "AttuazioneControlloBundle\Form\RicercaPersonaOperatoreType";
    }

    public function mostraNumeroElementi()
    {
        return false;
    }	

}