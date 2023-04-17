<?php

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC42_43IndicatoriRisultatoRepository")
 * @ORM\Table(name="tc42_43__indicatori_risultato")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="tipo", type="string")
 * @ORM\DiscriminatorMap({
 *     "GENERICA" : "MonitoraggioBundle\Entity\TC42_43IndicatoriRisultato",
 *     "COMUNI" : "MonitoraggioBundle\Entity\TC42IndicatoriRisultatoComuni",
 * "PROGRAMMA" : "MonitoraggioBundle\Entity\TC43IndicatoriRisultatoProgramma"})
 */
class TC42_43IndicatoriRisultato extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=80, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=80, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     * @var string
     */
    protected $cod_indicatore;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     * @Assert\Length(max=500, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     * @var string
     */
    protected $descrizione_indicatore;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Assert\Length(max=10, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     * @var string
     */
    protected $fonte_dato;

    /**
     * @ORM\OneToMany(targetEntity="IndicatoriRisultatoObiettivoSpecifico", mappedBy="indicatoreRisultato")
     * @var Collection
     */
    protected $mappingObiettivoSpecifico;

    public function __construct() {
        $this->mappingObiettivoSpecifico = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getCodIndicatore() {
        return $this->cod_indicatore;
    }

    /**
     * @param string $cod_indicatore
     * @return self
     */
    public function setCodIndicatore($cod_indicatore) {
        $this->cod_indicatore = $cod_indicatore;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescrizioneIndicatore() {
        return $this->descrizione_indicatore;
    }

    /**
     * @param string $descrizione_indicatore
     * @return self
     */
    public function setDescrizioneIndicatore($descrizione_indicatore) {
        $this->descrizione_indicatore = $descrizione_indicatore;
        return $this;
    }

    /**
     * @return string
     */
    public function getFonteDato() {
        return $this->fonte_dato;
    }

    /**
     * @param string $fonte_dato
     * @return self
     */
    public function setFonteDato($fonte_dato) {
        $this->fonte_dato = $fonte_dato;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->cod_indicatore . ' - ' . $this->descrizione_indicatore;
    }

    /**
     * @param IndicatoriRisultatoObiettivoSpecifico $mappingObiettivoSpecifico
     * @return TC42_43IndicatoriRisultato
     */
    public function addMappingObiettivoSpecifico(IndicatoriRisultatoObiettivoSpecifico $mappingObiettivoSpecifico) {
        $this->mappingObiettivoSpecifico[] = $mappingObiettivoSpecifico;

        return $this;
    }

    /**
     * @param IndicatoriRisultatoObiettivoSpecifico $mappingObiettivoSpecifico
     */
    public function removeMappingObiettivoSpecifico(IndicatoriRisultatoObiettivoSpecifico $mappingObiettivoSpecifico) {
        $this->mappingObiettivoSpecifico->removeElement($mappingObiettivoSpecifico);
    }

    /**
     * @return Collection|IndicatoriRisultatoObiettivoSpecifico[]
     */
    public function getMappingObiettivoSpecifico() {
        return $this->mappingObiettivoSpecifico;
    }
}
