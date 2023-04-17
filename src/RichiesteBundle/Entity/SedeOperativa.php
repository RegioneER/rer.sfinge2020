<?php

namespace RichiesteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * SedeOperativa
 *
 * @ORM\Table(name="sedi_operative")
 * @ORM\Entity()
 */
class SedeOperativa extends EntityLoggabileCancellabile {

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\Sede", inversedBy="sedeOperativa")
     * @ORM\JoinColumn(name="sede_id", referencedColumnName="id", nullable=false)
     */
    private $sede;
    
    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Proponente", inversedBy="sedi")
     * @ORM\JoinColumn(name="proponente_id", referencedColumnName="id", nullable=false)
     */
    private $proponente;
	
    /**
     * @ORM\OneToOne(targetEntity="SoggettoBundle\Entity\SedeVersion", cascade={"persist"})
     * @ORM\JoinColumn(name="sede_version_id", nullable=true)
     */
    private $sede_version;	
	
	/**
	 * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\InterventoSede", mappedBy="sede_operativa")
	 */
	protected $interventi_sede;
	
	/**
	 * 
	 * @ORM\Column(name="sede_montana", type="boolean", nullable=true)
	 * @Assert\NotNull(groups={"bando61", "bando174", "bando179", "bando182"})
	 */
	protected $sede_montana;
	
	/**
	 * 
	 * @ORM\Column(name="sede_1073c", type="boolean", nullable=true)
	 * @Assert\NotNull(groups={"bando61", "bando174", "bando179"})
	 */
	protected $sede_1073c;

    /**
     * @var boolean|null
     * @ORM\Column(name="sede_sisma", type="boolean", nullable=true)
     */
    protected $sede_sisma;
    
     /**
     * @var boolean|null
     * @ORM\Column(name="sede_interna", type="boolean", nullable=true)
     * @Assert\NotNull(groups={"bando174"})
     */
    protected $sede_interna;
	
	/**
	 * @ORM\Column(name="zona_censuaria", type="string", length=50, nullable=true)
	 */
	protected $zona_censuaria;

    /**
     * @ORM\Column(name="zona_montana", type="string", length=50, nullable=true)
     */
    protected $zona_montana;
    
    /**
     * @ORM\Column(name="zona_interna", type="string", length=50, nullable=true)
     */
    protected $zona_interna;

    /**
     * @ORM\Column(name="note", type="text", nullable=true)
     * @Assert\NotNull(groups={"bando179"})
     */
    protected $note;
 
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
     * Set proponente
     *
     * @param \RichiesteBundle\Entity\Proponente $proponente
     * @return SedeOperativa
     */
    public function setProponente(\RichiesteBundle\Entity\Proponente $proponente)
    {
        $this->proponente = $proponente;

        return $this;
    }

    /**
     * Get proponente
     *
     * @return \RichiesteBundle\Entity\Proponente 
     */
    public function getProponente()
    {
        return $this->proponente;
    }

    /**
     * Set sede
     *
     * @param \SoggettoBundle\Entity\Sede $sede
     * @return SedeOperativa
     */
    public function setSede(\SoggettoBundle\Entity\Sede $sede = null)
    {
        $this->sede = $sede;

        return $this;
    }

    /**
     * Get sede
     *
     * @return \SoggettoBundle\Entity\Sede 
     */
    public function getSede()
    {
        return $this->sede;
    }
	
    /**
     * @return  \SoggettoBundle\Entity\SedeVersion
     */
    function getSedeVersion() {
		return $this->sede_version;
	}

	function setSedeVersion($sede_version) {
		$this->sede_version = $sede_version;
	}
	
	public function isEmiliaRomagna(){
		$comune = $this->sede->getIndirizzo()->getComune();
		if(!is_null($comune)){
			if($comune->getProvincia()->getRegione()->getId() == 8){
				return true;
			}
		}
		return false;
    }
    
    /**
    * @param \RichiesteBundle\Entity\Proponente $proponente
    */
    public function __construct( \RichiesteBundle\Entity\Proponente $proponente = null){
        $this->proponente = $proponente;
		$this->interventi_sede = new \Doctrine\Common\Collections\ArrayCollection();
    }
	
	public function getInterventiSede() {
		return $this->interventi_sede;
	}

	public function setInterventiSede($interventi_sede) {
		$this->interventi_sede = $interventi_sede;
	}

	public function addInterventoSede(\RichiesteBundle\Entity\InterventoSede $intervento)
    {
        $this->interventi_sede[] = $intervento;

        return $this;
    }
	
	public function getSedeMontana() {
		return $this->sede_montana;
	}

	public function getSede1073c() {
		return $this->sede_1073c;
	}

	public function getZonaCensuaria() {
		return $this->zona_censuaria;
	}

	public function setSedeMontana($sede_montana) {
		$this->sede_montana = $sede_montana;
	}

	public function setSede1073c($sede_1073c) {
		$this->sede_1073c = $sede_1073c;
	}

	public function setZonaCensuaria($zona_censuaria) {
		$this->zona_censuaria = $zona_censuaria;
	}

    /**
     * @return mixed
     */
    public function getZonaMontana()
    {
        return $this->zona_montana;
    }

    /**
     * @param mixed $zona_montana
     */
    public function setZonaMontana($zona_montana): void
    {
        $this->zona_montana = $zona_montana;
    }

    /**
     * @return mixed
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param mixed $note
     */
    public function setNote($note): void
    {
        $this->note = $note;
    }

    /**
     * @return bool|null
     */
    public function getSedeSisma(): ?bool
    {
        return $this->sede_sisma;
    }

    /**
     * @param bool|null $sede_sisma
     */
    public function setSedeSisma(?bool $sede_sisma): void
    {
        $this->sede_sisma = $sede_sisma;
    }
    
    public function getSedeInterna(): ?bool {
        return $this->sede_interna;
    }

    public function getZonaInterna() {
        return $this->zona_interna;
    }

    public function setSedeInterna(?bool $sede_interna): void {
        $this->sede_interna = $sede_interna;
    }

    public function setZonaInterna($zona_interna): void {
        $this->zona_interna = $zona_interna;
    }
    
    public function hasSede107() {
        return $this->sede_1073c == true;     
    }
    
    public function hasSedeMontana() {
        return $this->sede_montana == true;     
    }

    /**
     * @return bool
     */
    public function isAreaInterna(): bool
    {
        if ($this->getSede() && $this->getSede()->getIndirizzo() && $this->getSede()->getIndirizzo()->getComune()) {
            return $this->getSede()->getIndirizzo()->getComune()->isAreaInterna();
        }
        return false;
    }

}

