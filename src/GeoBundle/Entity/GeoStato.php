<?php

namespace GeoBundle\Entity;


use Doctrine\ORM\Mapping as orm;

/**
 * @orm\Entity(repositoryClass="GeoBundle\Entity\GeoStatoRepository")
 * @orm\Table(name="geo_stati", indexes={@orm\Index(name="codice_idx", columns={"codice"}), @orm\Index(name="codice_completo_idx", columns={"codice_completo"})})
 */
class GeoStato extends Geo {
    const COD_ITALIA = '101';

    /**
	 *
	 * @orm\ManyToOne(targetEntity="GeoAreaGeopolitica", inversedBy="stati")
     * @orm\JoinColumn(name="area_geopolitica_id", referencedColumnName="id")
     */
    protected $area_geopolitica;

    /**
     * @orm\OneToMany(targetEntity="GeoRegione", mappedBy="stato")
     */
    protected $regioni;

    /**
     * @var string
     * @ORM\Column(name="codice_fiscale", type="string", length=4, nullable=false)
     */
    protected $codiceFiscale;


    public function getAreaGeopolitica() {
        return $this->area_geopolitica;
    }

    public function setAreaGeopolitica($area_geopolitica) {
        $this->area_geopolitica = $area_geopolitica;
    }

    public function getRegioni() {
        return $this->regioni;
    }

    public function setRegioni($regioni) {
        $this->regioni = $regioni;
    }

    /**
     * @return string
     */
    public function getCodiceFiscale(): string
    {
        return $this->codiceFiscale;
    }

    /**
     * @param string $codiceFiscale
     */
    public function setCodiceFiscale(string $codiceFiscale): void
    {
        $this->codiceFiscale = $codiceFiscale;
    }
}
