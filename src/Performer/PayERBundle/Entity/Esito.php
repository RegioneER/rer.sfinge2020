<?php


namespace Performer\PayERBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Esito
 *
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="payer_ebollo_esito")
 */
class Esito
{
    public const ESITO_OK = "OK";
    public const ESITO_KO = "KO";
    public const ESITO_OP = "OP";
    public const ESITO_UK = "UK";

    // Errori
    public const ERRORE_00 = "00";
    public const ERRORE_01 = "01";
    public const ERRORE_02 = "02";
    public const ERRORE_11 = "11";
    public const ERRORE_12 = "12";

    public const ERRORES = [
        self::ERRORE_00,
        self::ERRORE_01,
        self::ERRORE_02,
        self::ERRORE_11,
        self::ERRORE_12,
    ];

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(name="id", type="string", length=2, nullable=false)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="descrizione", type="string", length=100, nullable=false)
     */
    protected $descrizione;

    /**
     * @return string
     */
    public function getId(): string
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
    public function isErrore(): bool
    {
        return in_array($this->id, self::ERRORES);
    }
}