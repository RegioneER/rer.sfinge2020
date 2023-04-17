<?php

namespace RichiesteBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Fornitore
 *
 * @ORM\Table(name="fornitori")
 * @ORM\Entity(repositoryClass="RichiesteBundle\Entity\FornitoreRepository")
 */
class Fornitore extends EntityLoggabileCancellabile
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
     * @ORM\ManyToOne(targetEntity="TipologiaFornitore")
     * @ORM\JoinColumn(name="tipologia_fornitore_id", referencedColumnName="id")
     * @Assert\NotNull(message = "Selezionare una tipologia")
     */
    protected $tipologia_fornitore;

    /**
     * @ORM\ManyToOne(targetEntity="Richiesta", inversedBy="fornitori")
     * @ORM\JoinColumn(name="richiesta_id", referencedColumnName="id")
     * @Assert\NotNull(message = "Fornitore non associato alla richiesta")
     */
    protected $richiesta;

    /**
     * @var string
     *
     * @ORM\Column(name="denominazione", type="string", length=255)
     * @Assert\NotNull(message = "Selezionare una tipologia")
     */
    private $denominazione;

    /**
     * @ORM\ManyToOne(targetEntity="BaseBundle\Entity\Indirizzo", cascade={"persist"})
     * @ORM\JoinColumn(name="indirizzo_id", referencedColumnName="id", nullable=false)
     * @Assert\Valid()
     */
    private $indirizzo;

    /**
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\FornitoreServizio", mappedBy="fornitore")
     */
    private $servizi;

    /**
     * @var string
     *
     * @ORM\Column(name="codice_fiscale", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Length(min=2, max=16, groups={"statoItalia"})
     */
    private $codice_fiscale;
    
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
     * Set denominazione
     *
     * @param string $denominazione
     * @return Fornitore
     */
    public function setDenominazione($denominazione)
    {
        $this->denominazione = $denominazione;

        return $this;
    }

    /**
     * Get denominazione
     *
     * @return string 
     */
    public function getDenominazione()
    {
        return $this->denominazione;
    }

    /**
     * Set tipologia_fornitore
     *
     * @param \RichiesteBundle\Entity\TipologiaFornitore $tipologiaFornitore
     * @return Fornitore
     */
    public function setTipologiaFornitore(\RichiesteBundle\Entity\TipologiaFornitore $tipologiaFornitore = null)
    {
        $this->tipologia_fornitore = $tipologiaFornitore;

        return $this;
    }

    /**
     * Get tipologia_fornitore
     *
     * @return \RichiesteBundle\Entity\TipologiaFornitore 
     */
    public function getTipologiaFornitore()
    {
        return $this->tipologia_fornitore;
    }

    /**
     * Set richiesta
     *
     * @param \RichiesteBundle\Entity\Richiesta $richiesta
     * @return Fornitore
     */
    public function setRichiesta(\RichiesteBundle\Entity\Richiesta $richiesta = null)
    {
        $this->richiesta = $richiesta;

        return $this;
    }

    /**
     * Get richiesta
     *
     * @return \RichiesteBundle\Entity\Richiesta 
     */
    public function getRichiesta()
    {
        return $this->richiesta;
    }

    /**
     * Set indirizzo
     *
     * @param \BaseBundle\Entity\Indirizzo $indirizzo
     * @return Fornitore
     */
    public function setIndirizzo(\BaseBundle\Entity\Indirizzo $indirizzo)
    {
        $this->indirizzo = $indirizzo;

        return $this;
    }

    /**
     * Get indirizzo
     *
     * @return \BaseBundle\Entity\Indirizzo 
     */
    public function getIndirizzo()
    {
        return $this->indirizzo;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->servizi = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add servizi
     *
     * @param \RichiesteBundle\Entity\FornitoreServizio $servizi
     * @return Fornitore
     */
    public function addServizi(\RichiesteBundle\Entity\FornitoreServizio $servizi)
    {
        $this->servizi[] = $servizi;

        return $this;
    }

    /**
     * Remove servizi
     *
     * @param \RichiesteBundle\Entity\FornitoreServizio $servizi
     */
    public function removeServizi(\RichiesteBundle\Entity\FornitoreServizio $servizi)
    {
        $this->servizi->removeElement($servizi);
    }

    /**
     * Get servizi
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getServizi()
    {
        return $this->servizi;
    }

    /**
     * Set codice_fiscale
     *
     * @param string $codiceFiscale
     * @return Fornitore
     */
    public function setCodiceFiscale($codiceFiscale)
    {
        $this->codice_fiscale = $codiceFiscale;

        return $this;
    }

    /**
     * Get codice_fiscale
     *
     * @return string 
     */
    public function getCodiceFiscale()
    {
        return $this->codice_fiscale;
    }
}
