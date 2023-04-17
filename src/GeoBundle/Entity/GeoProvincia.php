<?php

namespace GeoBundle\Entity;

use Doctrine\ORM\Mapping as orm;
use Doctrine\Common\Collections\Collection;

/**
 * @orm\Entity(repositoryClass="GeoBundle\Entity\GeoProvinciaRepository")
 * @orm\Table(name="geo_province", indexes={@orm\Index(name="codice_idx", columns={"codice"}), @orm\Index(name="codice_completo_idx", columns={"codice_completo"})})
 */
class GeoProvincia extends Geo {
    /**
     * @orm\ManyToOne(targetEntity="GeoRegione", inversedBy="province")
     * @orm\JoinColumn(name="regione_id", referencedColumnName="id")
     *
     * @var GeoRegione|null
     */
    protected $regione;

    /**
     * @orm\Column(type="string", length=5, nullable=true)
     * @var string|null
     */
    protected $codice_nuts;

    /**
     * @orm\Column(type="string", length=2)
     * @var string|null
     */
    protected $sigla_automobilistica;

    /**
     * @orm\OneToMany(targetEntity="GeoComune", mappedBy="provincia")
     * @var Collection
     */
    protected $comuni;

    /**
     * @orm\Column(type="boolean")
     */
    protected $cessata;

    public function getRegione(): ?GeoRegione {
        return $this->regione;
    }

    public function setRegione(GeoRegione $regione): self {
        $this->regione = $regione;

        return $this;
    }

    public function getCodiceNuts() {
        return $this->codice_nuts;
    }

    public function setCodiceNuts($codice_nuts) {
        $this->codice_nuts = $codice_nuts;
    }

    public function getSiglaAutomobilistica() {
        return $this->sigla_automobilistica;
    }

    public function setSiglaAutomobilistica($sigla_automobilistica) {
        $this->sigla_automobilistica = $sigla_automobilistica;
    }

    public function getComuni(): Collection {
        return $this->comuni;
    }

    public function setComuni(Collection $comuni): self {
        $this->comuni = $comuni;

        return $this;
    }

    public function getCessata() {
        return $this->cessata;
    }

    public function setCessata($cessata) {
        $this->cessata = $cessata;
    }
}
