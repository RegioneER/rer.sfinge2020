<?php

namespace RichiesteBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Intervento
 *
 * @ORM\Table(name="interventi")
 * @ORM\Entity()
 */
class Intervento extends EntityLoggabileCancellabile {

	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
    
	/**
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\OggettoRichiesta", inversedBy="interventi")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $oggetto_richiesta;    

	/**
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Proponente", inversedBy="interventi")
	 * @ORM\JoinColumn(nullable=false)
     * 
     * @Assert\NotBlank()
	 */
	protected $proponente;
	
	/**
	 * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\Referente", mappedBy="intervento")
	 */
	protected $referenti;
    
	/**
	 * @ORM\ManyToOne(targetEntity="BaseBundle\Entity\Indirizzo", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 *
	 * @Assert\Valid()
	 */
	protected $indirizzo;    

	/**
	 * @ORM\Column(type="string", length=100, nullable=false)
	 * @Assert\NotBlank()
	 * @Assert\Email()
	 */
	protected $email;

	/**
	 * @ORM\Column(type="string", length=100, nullable=false)
	 * @Assert\NotBlank()
	 * @Assert\Email()
	 */
	protected $pec;

	/**
	 * @ORM\Column(type="string", length=20, nullable=false)
	 * @Assert\NotBlank()
	 */
	protected $tel;

    /**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $area_montana;

    /**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $area_107;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $zona_censuaria;


	/**
	 * @ORM\OneToOne(targetEntity="FascicoloBundle\Entity\IstanzaFascicolo")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $istanza_fascicolo;    
	
	function __construct() {
		$this->referenti = new ArrayCollection();
	}

	
    public function getId() {
        return $this->id;
    }

    public function getOggettoRichiesta() {
        return $this->oggetto_richiesta;
    }

    public function getProponente() {
        return $this->proponente;
    }

    public function getIndirizzo() {
        return $this->indirizzo;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getPec() {
        return $this->pec;
    }

    public function getTel() {
        return $this->tel;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setOggettoRichiesta($oggetto_richiesta) {
        $this->oggetto_richiesta = $oggetto_richiesta;
        return $this;
    }

    public function setProponente($proponente) {
        $this->proponente = $proponente;
        return $this;
    }

    public function setIndirizzo($indirizzo) {
        $this->indirizzo = $indirizzo;
        return $this;
    }

    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }

    public function setPec($pec) {
        $this->pec = $pec;
        return $this;
    }

    public function setTel($tel) {
        $this->tel = $tel;
        return $this;
    }

    public function getIstanzaFascicolo() {
        return $this->istanza_fascicolo;
    }

    public function setIstanzaFascicolo($istanza_fascicolo) {
        $this->istanza_fascicolo = $istanza_fascicolo;
        return $this;
    }

	function getReferenti() {
		return $this->referenti;
	}

	function setReferenti($referenti) {
		$this->referenti = $referenti;
	}

    public function getAreaMontana()
    {
        return $this->area_montana;
    }

    public function setAreaMontana($area_montana)
    {
        $this->area_montana = $area_montana;
    }

    public function getArea107()
    {
        return $this->area_107;
    }

    public function setArea107($area_107)
    {
        $this->area_107 = $area_107;
    }

    public function getZonaCensuaria()
    {
        return $this->zona_censuaria;
    }

    public function setZonaCensuaria($zona_censuaria)
    {
        $this->zona_censuaria = $zona_censuaria;
    }

    /**
     * @return bool
     */
    public function isAreaInterna(): bool
    {
        if ($this->getIndirizzo() && $this->getIndirizzo()->getComune()) {
            return $this->getIndirizzo()->getComune()->isAreaInterna();
        }
        return false;
    }
}
