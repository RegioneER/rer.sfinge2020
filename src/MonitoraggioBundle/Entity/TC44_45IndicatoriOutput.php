<?php

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * TC44_45IndicatoriOutput
 *
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC44_45IndicatoriOutputRepository")
 * @ORM\Table(name="tc44_45__indicatori_output")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="tipo", type="string")
 * @ORM\DiscriminatorMap({
 *     "GENERICA" : "MonitoraggioBundle\Entity\TC44_45IndicatoriOutput",
 *     "COMUNI" : "MonitoraggioBundle\Entity\TC44IndicatoriOutputComuni",
 * "PROGRAMMA" : "MonitoraggioBundle\Entity\TC45IndicatoriOutputProgramma"})
 */
class TC44_45IndicatoriOutput extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=80, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=80, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $cod_indicatore;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     * @Assert\Length(max=500, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizione_indicatore;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Assert\Length(max=5, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $unita_misura;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $desc_unita_misura;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Assert\Length(max=10, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $fonte_dato;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @var bool
     */
    protected $responsabilita_utente;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @var bool
     */
    protected $documentazione_obbligatoria;

    /**
     * @return mixed
     */
    public function getCodIndicatore() {
        return $this->cod_indicatore;
    }

    public function setCodIndicatore(?string $cod_indicatore): self {
        $this->cod_indicatore = $cod_indicatore;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneIndicatore() {
        return $this->descrizione_indicatore;
    }

    /**
     * @param mixed $descrizione_indicatore
     */
    public function setDescrizioneIndicatore($descrizione_indicatore) {
        $this->descrizione_indicatore = $descrizione_indicatore;
    }

    /**
     * @return mixed
     */
    public function getUnitaMisura() {
        return $this->unita_misura;
    }

    /**
     * @param mixed $unita_misura
     */
    public function setUnitaMisura($unita_misura) {
        $this->unita_misura = $unita_misura;
    }

    /**
     * @return mixed
     */
    public function getDescUnitaMisura() {
        return $this->desc_unita_misura;
    }

    /**
     * @param mixed $desc_unita_misura
     */
    public function setDescUnitaMisura($desc_unita_misura) {
        $this->desc_unita_misura = $desc_unita_misura;
    }

    /**
     * @return mixed
     */
    public function getFonteDato() {
        return $this->fonte_dato;
    }

    /**
     * @param mixed $fonte_dato
     */
    public function setFonteDato($fonte_dato) {
        $this->fonte_dato = $fonte_dato;
    }

    public function __toString(): string {
        return $this->cod_indicatore . ' - ' . $this->descrizione_indicatore;
    }

    public function setResponsabilitaUtente(bool $responsabilitaUtente): self {
        $this->responsabilita_utente = $responsabilitaUtente;

        return $this;
    }

    public function getResponsabilitaUtente(): ?bool {
        return $this->responsabilita_utente;
    }

    public function isAutomatico(): bool {
        return 0 == $this->responsabilita_utente;
    }

    public function setDocumentazioneObbligatoria(bool $documentazioneObbligatoria): self {
        $this->documentazione_obbligatoria = $documentazioneObbligatoria;

        return $this;
    }

    public function getDocumentazioneObbligatoria(): ?bool {
        return $this->documentazione_obbligatoria;
    }
}
