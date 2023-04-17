<?php

namespace SfingeBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * PermessiAsse
 *
 * @ORM\Table(name="permessi_asse")
 * @ORM\Entity(repositoryClass="SfingeBundle\Entity\PermessiAsseRepository")
 * @UniqueEntity(fields={"utente", "asse"}, message="L'associazione tra utente e asse è già esistente")
 */
class PermessiAsse extends EntityLoggabileCancellabile 
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
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Asse", inversedBy="permessi")
     * @ORM\JoinColumn(name="asse_id", referencedColumnName="id", nullable=false)
    */
    private $asse;

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
     * Set utente
     *
     * @param \SfingeBundle\Entity\Utente $utente
     * @return PermessiAsse
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
     * Set asse
     *
     * @param \SfingeBundle\Entity\Asse $asse
     * @return PermessiAsse
     */
    public function setAsse(\SfingeBundle\Entity\Asse $asse)
    {
        $this->asse = $asse;

        return $this;
    }

    /**
     * Get asse
     *
     * @return \SfingeBundle\Entity\Asse 
     */
    public function getAsse()
    {
        return $this->asse;
    }

 

    /**
     * Set solo_lettura
     *
     * @param boolean $soloLettura
     * @return PermessiAsse
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

    
}
