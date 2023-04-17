<?php

namespace GeoBundle\Entity;


use Doctrine\ORM\Mapping as orm;


/**
 * @orm\Entity(repositoryClass="GeoBundle\Entity\GeoAreaGeopoliticaRepository")
 * @orm\Table(name="geo_aree_geopolitiche")
 */
class GeoAreaGeopolitica {

    /**
     * @orm\Id
     * @orm\Column(type="bigint")
     * @orm\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @orm\ManyToOne(targetEntity="GeoContinente", inversedBy="aree")
     * @orm\JoinColumn(name="continente_id", referencedColumnName="id")
     */
    protected $continente;

    /**
     * @orm\Column(type="string", length=2, unique=true)
     */
    protected $codice;

    /**
     * @orm\Column(type="string", length=32)
     */
    protected $area;

    /**
     * @orm\OneToMany(targetEntity="GeoStato", mappedBy="area_geopolitica")
     */
    protected $stati;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }
    
    public function getContinente() {
        return $this->continente;
    }

    public function setContinente($continente) {
        $this->continente = $continente;
    }

    public function getCodice() {
        return $this->codice;
    }

    public function setCodice($codice) {
        $this->codice = $codice;
    }

    public function getArea() {
        return $this->area;
    }

    public function setArea($area) {
        $this->area = $area;
    }

    public function getStati() {
        return $this->stati;
    }

    public function setStati($stati) {
        $this->stati = $stati;
    }

    
}

?>
