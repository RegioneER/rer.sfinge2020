<?php

namespace RichiesteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityTipo;

/**
 * TipologiaServizio
 *
 * @ORM\Table(name="tipi_servizio")
 * @ORM\Entity(repositoryClass="RichiesteBundle\Repository\TipologiaServizioRepository")
 */
class TipologiaServizio  extends EntityTipo
{
	/**
     * @var string $nome
     *
     * @ORM\Column(name="nome", type="string", length=255, nullable=false)
     */
    protected $nome;    

    /**
     * Set nome
     *
     * @param string $nome
     * @return TipologiaServizio
     */
    public function setNome($nome)
    {
        $this->nome = $nome;

        return $this;
    }

    /**
     * Get nome
     *
     * @return string 
     */
    public function getNome()
    {
        return $this->nome;
    }
}
