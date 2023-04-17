<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 28/01/16
 * Time: 12:57
 */

namespace RichiesteBundle\Ricerche;


use SoggettoBundle\Form\Entity\RicercaSoggetto;
use RichiesteBundle\Entity\Richiesta;

class RicercaSoggettoProponente extends RicercaSoggetto
{

    public function getType()
    {
        return "RichiesteBundle\Form\RicercaProponenteType";
    }

    public function getNomeRepository()
    {
        return "SoggettoBundle:Soggetto";
    }

    public function getNomeMetodoRepository()
    {
        return "cercaPerProponente";
    }

    public function getNumeroElementiPerPagina()
    {
        return null;
    }

    public function getNomeParametroPagina()
    {
        return "page";
    }

    /**
     * @var Richiesta
     */
    public $richiesta = NULL;
}