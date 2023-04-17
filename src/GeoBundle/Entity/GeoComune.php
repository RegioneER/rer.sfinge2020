<?php

namespace GeoBundle\Entity;

use Doctrine\ORM\Mapping as orm;

/**
 * @orm\Entity(repositoryClass="GeoBundle\Entity\GeoComuneRepository")
 * @orm\Table(name="geo_comuni", indexes={@orm\Index(name="codice_idx", columns={"codice"}), @orm\Index(name="codice_completo_idx", columns={"codice_completo"})})
 */
class GeoComune extends Geo {
    /**
     * @orm\Column(type="integer")
     */
    protected $capoluogo = 0;

    /**
     * @orm\Column(type="decimal", precision=7, scale=2, nullable=true)
     */
    protected $superficie;

    /**
     * @orm\ManyToOne(targetEntity="GeoProvincia", inversedBy="comuni")
     * @orm\JoinColumn(name="provincia_id", referencedColumnName="id")
     * @var GeoProvincia|null
     */
    protected $provincia;

    /**
     * @orm\Column(type="boolean")
     */
    protected $cessato = false;

    /**
     * @orm\Column(type="boolean")
     */
    protected $ceduto_legge_1989 = false;

    /**
     * @var string
     *
     * @ORM\Column(name="codice_catastale", type="string", length=4, nullable=false)
     */
    protected $codiceCatastale;

    /**
     * @orm\Column(type="boolean")
     */
    protected $area_interna = false;

    public function getCapoluogo() {
        return $this->capoluogo;
    }

    public function setCapoluogo($capoluogo) {
        $this->capoluogo = $capoluogo;
    }

    public function getSuperficie() {
        return $this->superficie;
    }

    public function setSuperficie($superficie) {
        $this->superficie = $superficie;
    }

    public function getProvincia(): ?GeoProvincia {
        return $this->provincia;
    }

    public function setProvincia(?GeoProvincia $provincia): self {
        $this->provincia = $provincia;

        return $this;
    }

    public function getCessato() {
        return $this->cessato;
    }

    public function setCessato($cessato) {
        $this->cessato = $cessato;
    }

    public function getCedutoLegge1989() {
        return $this->ceduto_legge_1989;
    }

    public function setCedutoLegge1989(bool $ceduto_legge_1989): self {
        $this->ceduto_legge_1989 = $ceduto_legge_1989;

        return $this;
    }

    public function getIstat(): string {
        return $this->provincia->getCodice() . $this->codice;
    }

    /**
     * @return string
     */
    public function getCodiceCatastale(): ?string {
        return $this->codiceCatastale;
    }

    /**
     * @param string $codiceCatastale
     */
    public function setCodiceCatastale(string $codiceCatastale): void {
        $this->codiceCatastale = $codiceCatastale;
    }

    /**
     * @return bool
     */
    public function isAreaInterna(): bool
    {
        return $this->area_interna;
    }
    
    public function hasAreaInterna(): bool
    {
        return $this->area_interna == true;
    }

    /**
     * @param bool $area_interna
     */
    public function setAreaInterna(bool $area_interna): void
    {
        $this->area_interna = $area_interna;
    }

    public function __toString() {
        $provincia = $this->provincia->getSiglaAutomobilistica();
        return "$this->denominazione ($provincia)";
    }
}
