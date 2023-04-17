<?php

namespace SfingeBundle\Entity;

use BaseBundle\Entity\EntityTipo;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="tipi_amministrazione_emittente")
 */
class TipoAmministrazioneEmittente extends EntityTipo
{
    const RER = "RER";
    const BO = "BO";
    const FC = "FC";
    const PC = "PC";
    const PR = "PR";
    const RE = "RE";
    const MO = "MO";
    const FE = "FE";
    const RA = "RA";
    const RN = "RN";

    /**
	 * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC3ResponsabileProcedura")
	 * @ORM\JoinColumn(name="responsabile_procedura_id", referencedColumnName="id", nullable=true)
	 * @var \MonitoraggioBundle\Entity\TC3ResponsabileProcedura
     */
     protected $responsabile_procedura;

    /**
     * Set responsabile_procedura
     *
     * @param \MonitoraggioBundle\Entity\TC3ResponsabileProcedura $responsabileProcedura
     * @return TipoAmministrazioneEmittente
     */
    public function setResponsabileProcedura(\MonitoraggioBundle\Entity\TC3ResponsabileProcedura $responsabileProcedura = null)
    {
        $this->responsabile_procedura = $responsabileProcedura;

        return $this;
    }

    /**
     * Get responsabile_procedura
     *
     * @return \MonitoraggioBundle\Entity\TC3ResponsabileProcedura 
     */
    public function getResponsabileProcedura()
    {
        return $this->responsabile_procedura;
    }
}
