<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 05/01/16
 * Time: 16:09
 */

namespace DocumentoBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;

class RicercaDocumento extends AttributiRicerca
{

    private $id;
    private $nome;
    private $tipologia;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * @param mixed $nome
     */
    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    /**
     * @return mixed
     */
    public function getTipologia()
    {
        return $this->tipologia;
    }

    /**
     * @param mixed $tipologia
     */
    public function setTipologia($tipologia)
    {
        $this->tipologia = $tipologia;
    }


    public function getType()
    {
        return "DocumentoBundle\Form\RicercaDocumentoType";
    }

    public function getNomeRepository()
    {
        return "DocumentoBundle:Documento";
    }

    public function getNomeMetodoRepository()
    {
        return "cercaSuperAdmin";
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