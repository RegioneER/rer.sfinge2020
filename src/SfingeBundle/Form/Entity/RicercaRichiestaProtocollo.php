<?php

namespace SfingeBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;
use ProtocollazioneBundle\Entity\RichiestaProtocollo;
use SfingeBundle\Form\RicercaRichiestaProtocolloType;

class RicercaRichiestaProtocollo extends AttributiRicerca
{
    private $registro_pg;
    private $anno_pg;
    private $num_pg;

    public function getType(): string
    {
        return RicercaRichiestaProtocolloType::class;
    }

    public function getNomeRepository(): string
    {
        return RichiestaProtocollo::class;
    }

    public function getNomeMetodoRepository(): string
    {
        return "getRichiestaProtocolloByProtocollo";
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
    public function getRegistroPg()
    {
        return $this->registro_pg;
    }

    /**
     * @param mixed $registro_pg
     */
    public function setRegistroPg($registro_pg): void
    {
        $this->registro_pg = $registro_pg;
    }

    /**
     * @return mixed
     */
    public function getAnnoPg()
    {
        return $this->anno_pg;
    }

    /**
     * @param mixed $anno_pg
     */
    public function setAnnoPg($anno_pg): void
    {
        $this->anno_pg = $anno_pg;
    }

    /**
     * @return mixed
     */
    public function getNumPg()
    {
        return $this->num_pg;
    }

    /**
     * @param mixed $num_pg
     */
    public function setNumPg($num_pg): void
    {
        $this->num_pg = $num_pg;
    }
}
