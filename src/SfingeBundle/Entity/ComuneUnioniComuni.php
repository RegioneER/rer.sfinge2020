<?php

namespace SfingeBundle\Entity;

use BaseBundle\Entity\EntityTipo;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

/**
 *
 * TABELLA STATICA CON LA LISTA DEI COMUNI E LE UNIONI DEI COMUNI
 *
 * @ORM\Entity()
 * @ORM\Table(name="comuni_unioni_comuni")
 */
class ComuneUnioniComuni extends EntityTipo{

    /**
     * @var integer
     * @ORM\Column(name="popolazione", type="bigint")
     */
    protected $popolazione;

    /**
     * @var ComuneUnioniComuni
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\ComuneUnioniComuni")
     * @ORM\JoinColumn(name="unione_comune_id", referencedColumnName="id", nullable=true)
     */

    protected $unione_di_appartenenza;

    /**
     * @return int
     */
    public function getPopolazione()
    {
        return $this->popolazione;
    }

    /**
     * @param int $popolazione
     */
    public function setPopolazione($popolazione)
    {
        $this->popolazione = $popolazione;
    }

    /**
     * @return ComuneUnioniComuni
     */
    public function getUnioneDiAppartenenza()
    {
        return $this->unione_di_appartenenza;
    }

    /**
     * @param ComuneUnioniComuni $unione_di_appartenenza
     */
    public function setUnioneDiAppartenenza($unione_di_appartenenza)
    {
        $this->unione_di_appartenenza = $unione_di_appartenenza;
    }


}