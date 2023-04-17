<?php

namespace GeoBundle\Entity;


use Doctrine\ORM\Mapping as orm;


/**
 * @orm\Entity(repositoryClass="GeoBundle\Entity\GeoAreaGeograficaRepository")
 * @orm\Table(name="geo_aree_geografica")
 */
class GeoAreaGeografica {

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
     * @orm\Column(type="string", length=10)
     */
    protected $area;

    /**
     * @orm\Column(type="string", length=3, unique=true)
     */
    protected $codice_nuts;

    /**
     * @orm\OneToMany(targetEntity="GeoRegione", mappedBy="area_geografica")
     */
    protected $regioni;

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

    public function getArea() {
        return $this->area;
    }

    public function setArea($area) {
        $this->area = $area;
    }
    
    public function getCodiceNuts() {
        return $this->codice_nuts;
    }

    public function setCodiceNuts($codice_nuts) {
        $this->codice_nuts = $codice_nuts;
    }

    public function getRegioni() {
        return $this->regioni;
    }

    public function setRegioni($regioni) {
        $this->regioni = $regioni;
    }
    
}

?>
