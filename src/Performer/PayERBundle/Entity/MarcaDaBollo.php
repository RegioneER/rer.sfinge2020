<?php

namespace Performer\PayERBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class MarcaDaBollo
 *
 * @ORM\Entity()
 * @ORM\Table(name="payer_marca_da_bollo")
 */
class MarcaDaBollo
{
    public const MDB_16_00 = "MDB_16_00";

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(name="id", type="string", length=50, nullable=false)
     */
    protected $id;

    /**
     * @var float
     *
     * @ORM\Column(name="importo", type="float", nullable=false)
     */
    protected $importo;

    /**
     * @var TipoMarcaDaBollo
     *
     * @ORM\ManyToOne(targetEntity="Performer\PayERBundle\Entity\TipoMarcaDaBollo")
     * @ORM\JoinColumn(name="tipo_id", referencedColumnName="id", nullable=false)
     */
    protected $tipo;

    /**
     * @var bool
     *
     * @ORM\Column(name="attiva", type="boolean", nullable=false)
     */
    protected $attiva;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getImporto(): float
    {
        return $this->importo;
    }

    /**
     * @return TipoMarcaDaBollo
     */
    public function getTipo(): TipoMarcaDaBollo
    {
        return $this->tipo;
    }

    /**
     * @return bool
     */
    public function isAttiva(): bool
    {
        return $this->attiva;
    }
}