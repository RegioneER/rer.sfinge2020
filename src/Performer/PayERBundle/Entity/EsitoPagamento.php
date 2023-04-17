<?php


namespace Performer\PayERBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class EsitoPagamento
 *
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="payer_ebollo_esito_pagamento")
 */
class EsitoPagamento
{
    public const ESEGUITO = 0;
    public const NON_ESEGUITO = 1;
    public const PARZIALMENTE_ESEGUITO = 2;
    public const DECORRENZA_TERMINI = 3;
    public const DECORRENZA_TERMINI_PARZIALE = 4;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="descrizione", type="string", length=100, nullable=false)
     */
    protected $descrizione;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDescrizione(): string
    {
        return $this->descrizione;
    }

    /**
     * @return bool
     */
    public function isEseguito(): bool
    {
        return $this->id === self::ESEGUITO;
    }
}