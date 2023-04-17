<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 04/01/16
 * Time: 15:52
 */

namespace BaseBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class EntityTipo
{

    /**
     * @var string
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="codice", type="string", length=50)
     */
    protected $codice;

    /**
     * @var string
     *
     * @ORM\Column(name="descrizione", type="string", length=1000)
     */
    protected $descrizione;


    public function getId()
    {
        return $this->id;
    }

    public function setCodice(?string $codice): self
    {
        $this->codice = $codice;

        return $this;
    }

    public function getCodice(): ?string
    {
        return $this->codice;
    }

    public function setDescrizione(?string $descrizione): self
    {
        $this->descrizione = $descrizione;

        return $this;
    }

    public function getDescrizione(): ?string
    {
        return $this->descrizione;
    }

    public function uguale(?string $value): bool {
        return $this->getCodice()==$value;
    }

    public function __toString() {
        return $this->getCodice();
    }

}