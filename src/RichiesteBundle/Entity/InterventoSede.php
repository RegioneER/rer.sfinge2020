<?php

namespace RichiesteBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Validator\Constraints\ValidaLunghezza;
use RichiesteBundle\Entity\Richiesta;

/**
 * InterventoSede
 *
 * @ORM\Table(name="interventi_sede")
 * @ORM\Entity(repositoryClass="RichiesteBundle\Entity\InterventoSedeRepository")
 */
class InterventoSede extends EntityLoggabileCancellabile {

	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
    
	/**
	 * @ORM\Column(type="text", nullable=true)
	 * @ValidaLunghezza(min=5, max=250, groups={"bando61", "bando180"})
	 * @ValidaLunghezza(min=5, max=1000, groups={"bando95", "bando99", "bando109", "bando160"})
	 * @ValidaLunghezza(min=5, max=5000, groups={"bando129", "bando130", "bando152"})
	 */
	protected $descrizione;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 * @ValidaLunghezza(min=5, max=1000, groups={"bando95"})
	 */
	protected $ulteriore_descrizione;

	/**
	 * @ORM\Column(name="costo", type="decimal", precision=10, scale=2, nullable=true)
	 * @Assert\NotNull(groups={"bando61", "bando95", "bando99", "bando109", "bando129", "bando130", "bando152", "bando160", "bando180"})
	 */
	protected $costo;
	
	/**
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\PianoCosto", inversedBy="interventi_sede")
	 * @ORM\JoinColumn(name="piano_costo_id", referencedColumnName="id", nullable=true)
	 * @Assert\NotNull(groups={"bando61", "bando95", "bando99", "bando109", "bando129", "bando130", "bando152", "bando160", "bando180"})
	 */
	protected $piano_costo;
	
	/**
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\SedeOperativa", inversedBy="interventi_sede")
	 * @ORM\JoinColumn(name="sede_operativa_id", referencedColumnName="id", nullable=true)
	 * @var SedeOperativa
	 */
	protected $sede_operativa;
	
	/**
	 * @ORM\Column(type="integer", name="annualita", nullable=true)
	 * @Assert\NotNull(groups={"bando61"})
	 */
	protected $annualita;
    
    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="intervento_sede")
     * @ORM\JoinColumn(name="richiesta_id", referencedColumnName="id", nullable=true)
	 * 
	 * @var Richiesta
     */
    private $richiesta;
	
	public function getId() {
		return $this->id;
	}

	public function getDescrizione() {
		return $this->descrizione;
	}

	public function getCosto() {
		return $this->costo;
	}

	public function getPianoCosto() {
		return $this->piano_costo;
	}

	public function getSedeOperativa() {
		return $this->sede_operativa;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setDescrizione($descrizione) {
		$this->descrizione = $descrizione;
	}

	public function setCosto($costo) {
		$this->costo = $costo;
	}

	public function setPianoCosto($piano_costo) {
		$this->piano_costo = $piano_costo;
	}

	public function setSedeOperativa($sede_operativa) {
		$this->sede_operativa = $sede_operativa;
	}
	
	public function getAnnualita() {
		return $this->annualita;
	}

	public function setAnnualita($annualita) {
		$this->annualita = $annualita;
	}
    
    public function getRichiesta(): Richiesta {
        return $this->richiesta;
    }

    public function setRichiesta(Richiesta $richiesta) {
        $this->richiesta = $richiesta;
    }
	
	/**
	 * @return mixed
	 */
	public function getUlterioreDescrizione()
	{
		return $this->ulteriore_descrizione;
	}
    
	/**
	 * @param mixed $ulteriore_descrizione
	 */
	public function setUlterioreDescrizione($ulteriore_descrizione): void
	{
		$this->ulteriore_descrizione = $ulteriore_descrizione;
	}
}
