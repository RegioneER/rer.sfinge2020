<?php
namespace IstruttorieBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;
use IstruttorieBundle\Entity\PropostaImpegno;
use IstruttorieBundle\Form\RicercaPropostaImpegnoType;

class RicercaPropostaImpegno extends AttributiRicerca
{
    private $procedura;

    public function getType(): string
    {
        return RicercaPropostaImpegnoType::class;
    }

    public function getNomeRepository(): string
    {
        return PropostaImpegno::class;
    }

    public function getNomeMetodoRepository(): string
    {
        return "getProposteImpegnoByProcedura";
    }

    public function getNumeroElementiPerPagina()
    {
        return null;
    }

    public function getNomeParametroPagina(): string
    {
        return "page";
    }

    /**
     * @return mixed
     */
    public function getProcedura()
    {
        return $this->procedura;
    }

    /**
     * @param mixed $procedura
     */
    public function setProcedura($procedura): void
    {
        $this->procedura = $procedura;
    }
}
