<?php

namespace SfingeBundle\Entity;

use BaseBundle\Entity\EntityTipo;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity()
 * @ORM\Table(name="tipi_operazioni")
 */
class TipoOperazione extends EntityTipo
{
    /**
     *
     * @ORM\ManyToMany(targetEntity="Procedura", mappedBy="tipi_operazioni", cascade={"all"})
     */
    protected $procedure;

    /**
     * TipoOperazione constructor.
     * @param $procedure
     */
    public function __construct()
    {
        $this->procedure = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getProcedure()
    {
        return $this->procedure;
    }

    /**
     * @param mixed $procedure
     */
    public function setProcedure($procedure)
    {
        $this->procedure = $procedure;
    }

    public function __toString(){
        return $this->getCodice() . " - " . $this->getDescrizione();
    }

    /**
     * @param mixed $procedura
     */
    public function addProcedure($procedura)
    {
        $this->procedure->add($procedura);
    }
}
