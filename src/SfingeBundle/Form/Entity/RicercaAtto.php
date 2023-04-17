<?php


namespace SfingeBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

class RicercaAtto extends AttributiRicerca
{

    private $titolo;

    private $numero;


    /**
     * @return mixed
     */
    public function getTitolo()
    {
        return $this->titolo;
    }

    /**
     * @param mixed $titolo
     */
    public function setTitolo($titolo)
    {
        $this->titolo = $titolo;
    }

    /**
     * @return mixed
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * @param mixed $numero
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;
    }
    

    public function getType()
    {
        return "SfingeBundle\Form\RicercaAttoType";
    }

    public function getNomeRepository()
    {
        return "SfingeBundle:Atto";
    }

    public function getNomeMetodoRepository()
    {
        return "cercaAtto";
    }


    public function getNumeroElementiPerPagina()
    {
        return null;
    }

    public function getNomeParametroPagina()
    {
        return "page";
    }

}