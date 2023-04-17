<?php

namespace Performer\PayERBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class TipoMarcaDaBollo
 *
 * @ORM\Entity()
 * @ORM\Table(name="payer_tipo_marca_da_bollo")
 */
class TipoMarcaDaBollo
{
    const IMPOSTA_DI_BOLLO = "01";

    /**
     * @var string
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(name="id", type="string", length=2, nullable=false)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="descrizione", type="string", length=50, nullable=false)
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
}