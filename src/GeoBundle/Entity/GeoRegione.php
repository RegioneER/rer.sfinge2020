<?php

namespace GeoBundle\Entity;


use Doctrine\ORM\Mapping as orm;


/**
 * @orm\Entity(repositoryClass="GeoBundle\Entity\GeoRegioneRepository")
 * @orm\Table(name="geo_regioni", indexes={@orm\Index(name="codice_idx", columns={"codice"}), @orm\Index(name="codice_completo_idx", columns={"codice_completo"})})
 */
class GeoRegione extends Geo {

    /**
     * @orm\Column(type="string", length=4)
     */
    protected $codice_nuts;

    /**
     * @orm\ManyToOne(targetEntity="GeoAreaGeografica", inversedBy="regioni")
     * @orm\JoinColumn(name="area_geografica_id", referencedColumnName="id")
     */
    protected $area_geografica;

    /**
     * @orm\ManyToOne(targetEntity="GeoStato", inversedBy="regioni")
     * @orm\JoinColumn(name="stato_id", referencedColumnName="id")
     */
    protected $stato;

    /**
     * @orm\OneToMany(targetEntity="GeoProvincia", mappedBy="regione")
     */
    protected $province;

    public function getCodiceNuts() {
        return $this->codice_nuts;
    }

    public function setCodiceNuts($codice_nuts) {
        $this->codice_nuts = $codice_nuts;
    }

    public function getStato() {
        return $this->stato;
    }

    public function setStato($stato) {
        $this->stato = $stato;
    }

    public function getAreaGeografica() {
        return $this->area_geografica;
    }

    public function setAreaGeografica($area_geografica) {
        $this->area_geografica = $area_geografica;
    }

    public function getProvince() {
        return $this->province;
    }

    public function setProvince($province) {
        $this->province = $province;
    }

}

?>
