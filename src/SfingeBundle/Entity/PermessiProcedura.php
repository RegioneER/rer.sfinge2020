<?php

namespace SfingeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * PermessiProcedura
 *
 * @ORM\Table(name="permessi_procedura")
 * @ORM\Entity(repositoryClass="SfingeBundle\Entity\PermessiProceduraRepository")
 * @UniqueEntity(fields={"utente", "procedura"}, message="L'associazione tra utente e procedura è già esistente")
 */
class PermessiProcedura extends EntityLoggabileCancellabile 
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Utente")
     * @ORM\JoinColumn(name="utente_id", referencedColumnName="id", nullable=false)
    */
    private $utente;

    /**
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Procedura", inversedBy="permessi")
     * @ORM\JoinColumn(name="procedura_id", referencedColumnName="id", nullable=false)
    */
    private $procedura;

    /**
     * @var boolean $solo_lettura
     * @ORM\Column(name="solo_lettura", type="boolean", nullable=true )
     */
    private $solo_lettura;
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set solo_lettura
     *
     * @param boolean $soloLettura
     * @return PermessiProcedura
     */
    public function setSoloLettura($soloLettura)
    {
        $this->solo_lettura = $soloLettura;

        return $this;
    }

    /**
     * Get solo_lettura
     *
     * @return boolean 
     */
    public function getSoloLettura()
    {
        return $this->solo_lettura;
    }

    /**
     * Set utente
     *
     * @param \SfingeBundle\Entity\Utente $utente
     * @return PermessiProcedura
     */
    public function setUtente(\SfingeBundle\Entity\Utente $utente)
    {
        $this->utente = $utente;

        return $this;
    }

    /**
     * Get utente
     *
     * @return \SfingeBundle\Entity\Utente 
     */
    public function getUtente()
    {
        return $this->utente;
    }

    /**
     * Set procedura
     *
     * @param \SfingeBundle\Entity\Procedura $procedura
     * @return PermessiProcedura
     */
    public function setProcedura(\SfingeBundle\Entity\Procedura $procedura)
    {
        $this->procedura = $procedura;

        return $this;
    }

    /**
     * Get procedura
     *
     * @return \SfingeBundle\Entity\Procedura 
     */
    public function getProcedura()
    {
        return $this->procedura;
    }
}
