<?php
namespace SoggettoBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="SoggettoBundle\Entity\AtecoRepository")
 * @ORM\Table(name="ateco2007")
 */
class Ateco
{
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32, nullable=true, name="codice")
     */
    private $codice;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="descrizione")
     */
    private $descrizione;

    /**
     * @ORM\Column(type="string", length=32, nullable=true, name="codice_area")
     */
    private $codice_area;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="descrizione_area")
     */
    private $descrizione_area;

    /**
     * @ORM\Column(type="string", length=32, nullable=true, name="codice_macro_settore")
     */
    private $codice_macro_settore;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="descrizione_macro_settore")
     */
    private $descrizione_macro_settore;

    /**
     * @ORM\OneToMany(targetEntity="SoggettoBundle\Entity\Sede", mappedBy="ateco")
     */
    private $sede;
	

	function getId() {
		return $this->id;
	}

	function getCodice() {
		return $this->codice;
	}

	function getDescrizione() {
		return $this->descrizione;
	}

	function getSede() {
		return $this->sede;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setCodice($codice) {
		$this->codice = $codice;
	}

	function setDescrizione($descrizione) {
		$this->descrizione = $descrizione;
	}

	function setSede($sede) {
		$this->sede = $sede;
	}


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->sede = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @param string $codiceArea
     *
     * @return self
     */
    public function setCodiceArea($codiceArea)
    {
        $this->codice_area = $codiceArea;

        return $this;
    }

    /**
     * @return string
     */
    public function getCodiceArea()
    {
        return $this->codice_area;
    }

    /**
     * @param string $descrizioneArea
     *
     * @return Ateco
     */
    public function setDescrizioneArea($descrizioneArea)
    {
        $this->descrizione_area = $descrizioneArea;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescrizioneArea()
    {
        return $this->descrizione_area;
    }

    /**
     * @param string $codiceMacroSettore
     *
     * @return Ateco
     */
    public function setCodiceMacroSettore($codiceMacroSettore)
    {
        $this->codice_macro_settore = $codiceMacroSettore;

        return $this;
    }

    /**
     * @return string
     */
    public function getCodiceMacroSettore()
    {
        return $this->codice_macro_settore;
    }

    /**
     * @param string $descrizioneMacroSettore
     *
     * @return Ateco
     */
    public function setDescrizioneMacroSettore($descrizioneMacroSettore)
    {
        $this->descrizione_macro_settore = $descrizioneMacroSettore;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescrizioneMacroSettore()
    {
        return $this->descrizione_macro_settore;
    }

    /**
     * @param \SoggettoBundle\Entity\Sede $sede
     *
     * @return Ateco
     */
    public function addSede(\SoggettoBundle\Entity\Sede $sede)
    {
        $this->sede[] = $sede;

        return $this;
    }

    /**
     * @param \SoggettoBundle\Entity\Sede $sede
     */
    public function removeSede(\SoggettoBundle\Entity\Sede $sede)
    {
        $this->sede->removeElement($sede);
    }
	
	public function __toString() {
		return $this->codice." ".$this->descrizione;
	}
}
