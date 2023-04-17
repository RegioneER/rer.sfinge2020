<?php

namespace RichiesteBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Validator\Constraints\ValidaLunghezza;
use Doctrine\ORM\Mapping as ORM;

/**
 * FornitoreServizio
 *
 * @ORM\Table(name="fornitore_servizi")
 * @ORM\Entity(repositoryClass="RichiesteBundle\Entity\FornitoreServizioRepository")
 */
class FornitoreServizio extends EntityLoggabileCancellabile
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
     * @ORM\ManyToOne(targetEntity="TipologiaServizio")
     * @ORM\JoinColumn(name="tipologia_servizio_id", referencedColumnName="id")
     * @Assert\NotNull(message = "Selezionare una tipologia")
     */
    protected $tipologia_servizio;

    /**
     * @ORM\ManyToOne(targetEntity="Fornitore", inversedBy="servizi")
     * @ORM\JoinColumn(name="fornitore_id", referencedColumnName="id", onDelete="CASCADE")
     * 
     */
    protected $fornitore;

    /**
     * @var string
     *
     * @ORM\Column(name="descrizione", type="text")
     * @Assert\NotNull(message = "Campo obbligatorio")
	 * @ValidaLunghezza(min=5, max=500)
     */
    private $descrizione;

    /**
     * @var string
     *
     * @ORM\Column(name="costo", type="string", length=255)
     * @Assert\NotNull(message = "Campo obbligatorio")
     */
    private $costo;

    /**
     * @var string
     *
     * @ORM\Column(name="responsabile", type="string", length=255)
     * @Assert\NotNull(message = "Campo obbligatorio")
     * 
     */
    private $responsabile;

    /**
     * @var string
     *
     * @ORM\Column(type="integer", name="giornate_uomo", nullable=false)
     * @Assert\NotNull(message = "Campo obbligatorio")
     * @Assert\Type("numeric")
     */

    protected $giornate_uomo;


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
     * Set descrizione
     *
     * @param string $descrizione
     * @return FornitoreServizio
     */
    public function setDescrizione($descrizione)
    {
        $this->descrizione = $descrizione;

        return $this;
    }

    /**
     * Get descrizione
     *
     * @return string 
     */
    public function getDescrizione()
    {
        return $this->descrizione;
    }

    /**
     * Set costo
     *
     * @param string $costo
     * @return FornitoreServizio
     */
    public function setCosto($costo)
    {
        $this->costo = $costo;

        return $this;
    }

    /**
     * Get costo
     *
     * @return string 
     */
    public function getCosto()
    {
        return $this->costo;
    }

    /**
     * Set tipologia_servizio
     *
     * @param \RichiesteBundle\Entity\TipologiaServizio $tipologiaServizio
     * @return FornitoreServizio
     */
    public function setTipologiaServizio(\RichiesteBundle\Entity\TipologiaServizio $tipologiaServizio = null)
    {
        $this->tipologia_servizio = $tipologiaServizio;

        return $this;
    }

    /**
     * Get tipologia_servizio
     *
     * @return \RichiesteBundle\Entity\TipologiaServizio 
     */
    public function getTipologiaServizio()
    {
        return $this->tipologia_servizio;
    }

    /**
     * Set fornitore
     *
     * @param \RichiesteBundle\Entity\Fornitore $fornitore
     * @return FornitoreServizio
     */
    public function setFornitore(\RichiesteBundle\Entity\Fornitore $fornitore = null)
    {
        $this->fornitore = $fornitore;

        return $this;
    }

    /**
     * Get fornitore
     *
     * @return \RichiesteBundle\Entity\Fornitore 
     */
    public function getFornitore()
    {
        return $this->fornitore;
    }

    /**
     * Set responsabile
     *
     * @param string $responsabile
     * @return FornitoreServizio
     */
    public function setResponsabile($responsabile)
    {
        $this->responsabile = $responsabile;

        return $this;
    }

    /**
     * Get responsabile
     *
     * @return string 
     */
    public function getResponsabile()
    {
        return $this->responsabile;
    }

    /**
     * Set giornate_uomo
     *
     * @param integer $giornateUomo
     * @return FornitoreServizio
     */
    public function setGiornateUomo($giornateUomo)
    {
        $this->giornate_uomo = $giornateUomo;

        return $this;
    }

    /**
     * Get giornate_uomo
     *
     * @return integer 
     */
    public function getGiornateUomo()
    {
        return $this->giornate_uomo;
    }
}
