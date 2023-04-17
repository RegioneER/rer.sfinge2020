<?php

namespace GeoBundle\Entity;


use Doctrine\ORM\Mapping as orm;


/**
 * @orm\Entity(repositoryClass="GeoBundle\Entity\GeoContinenteRepository")
 * @orm\Table(name="geo_continenti")
 */
class GeoContinente {

    /**
     * @orm\Id
     * @orm\Column(type="bigint")
     * @orm\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @orm\Column(type="string", length=1, unique=true)
     */
    protected $codice;

    /**
     * @orm\Column(type="string", length=16)
     */
    protected $continente;

    /**
     * @orm\OneToMany(targetEntity="GeoAreaGeopolitica", mappedBy="continente")
     */
    protected $aree;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }
    
    public function getCodice() {
        return $this->codice;
    }

    public function setCodice($codice) {
        $this->codice = $codice;
    }

    public function getContinente() {
        return $this->continente;
    }

    public function setContinente($continente) {
        $this->continente = $continente;
    }
    
    public function getAree() {
        return $this->aree;
    }

    public function setAree($aree) {
        $this->aree = $aree;
    }

}

?>
