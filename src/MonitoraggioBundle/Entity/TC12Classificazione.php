<?php

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use SfingeBundle\Entity\Azione;
use AttuazioneControlloBundle\Entity\RichiestaProgrammaClassificazione;
use BaseBundle\Entity\Id;

/**
 * Description of TC12Classificazione
 *
 * @author lfontana
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC12ClassificazioneRepository")
 * @ORM\Table(name="tc12_classificazione")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="tipo", type="string")
 * @ORM\DiscriminatorMap({
 *     "GENERICA" : "MonitoraggioBundle\Entity\TC12Classificazione",
 *     "AZIONE" : "MonitoraggioBundle\Entity\TC12LineaAzione",
 * "OBIETTIVO" : "MonitoraggioBundle\Entity\TC12RisultatoAtteso"})
 */
class TC12Classificazione {
    use Id;

    /**
     * @var string
     * @Assert\NotNull
     * @Assert\Length(max=200, maxMessage="Massimo {{ limit }} caratteri")
     * @ORM\Column(name="codice", type="string", length=200)
     */
    protected $codice;

    /**
     * @var string
     * @Assert\Length(max=1000, maxMessage="Massimo {{ limit }} caratteri")
     * @ORM\Column(name="descrizione", type="string", length=1000, nullable=true)
     */
    protected $descrizione;

    /**
     * @var TC11TipoClassificazione
     * @Assert\NotNull
     * @ORM\ManyToOne(targetEntity="TC11TipoClassificazione", inversedBy="classificazioni")
     * @ORM\JoinColumn(name="tipo_classificazione_id", referencedColumnName="id")
     */
    protected $tipo_classificazione;

    /**
     * @var TC4Programma
     * @ORM\ManyToOne(targetEntity="TC4Programma", inversedBy="classificazioni")
     * @ORM\JoinColumn(name="programma_id", referencedColumnName="id")
     */
    protected $programma;

    /**
     * @var string
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Assert\Length(max=10, maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $origine_dato;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\RichiestaProgrammaClassificazione", mappedBy="classificazione")
     */
    protected $richieste_classificazioni;

    /**
     * @ORM\ManyToMany(targetEntity="SfingeBundle\Entity\Azione", inversedBy="classificazioni")
     * @ORM\JoinTable(name="classificazioni_azioni_mapping")
     * @var Azione|Collection
     */
    protected $azioni;

    /**
     * @ORM\ManyToMany(targetEntity="TC36LivelloGerarchico")
     * @ORM\JoinTable(name="risultato_atteso_livello_gerarchico")
     */
    protected $livello_gerarchico;

    /**
     * @return TC11TipoClassificazione
     */
    public function getTipoClassificazione() {
        return $this->tipo_classificazione;
    }

    /**
     * @return TC4Programma
     */
    public function getProgramma() {
        return $this->programma;
    }

    public function getOrigineDato() {
        return $this->origine_dato;
    }

    /**
     * @param TC11TipoClassificazione $tipo_classificazione
     * @return self
     */
    public function setTipoClassificazione(TC11TipoClassificazione $tipo_classificazione) {
        $this->tipo_classificazione = $tipo_classificazione;
        return $this;
    }

    /**
     * @return self
     */
    public function setProgramma(TC4Programma $programma) {
        $this->programma = $programma;
        return $this;
    }

    /**
     * @return self
     * @param mixed $origine_dato
     */
    public function setOrigineDato($origine_dato) {
        $this->origine_dato = $origine_dato;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->getDescrizione();
    }

    public function __constructor() {
        $this->richieste_classificazioni = new ArrayCollection();
        $this->azioni = new ArrayCollection();
    }

    /**
     * @return Collection|RichiestaProgrammaClassificazione[]
     */
    public function getRichiesteClassificazioni() {
        return $this->richieste_classificazioni;
    }

    /**
     * @param Collection|RichiestaProgrammaClassificazione[] $classificazioni
     * @return self
     */
    public function setRichiesteClassificazioni($classificazioni) {
        $this->richieste_classificazioni = $classificazioni;
        return $this;
    }

    /**
     * @return string
     */
    public function getCodice() {
        return $this->codice;
    }

    /**
     * @return string
     */
    public function getDescrizione() {
        return $this->descrizione;
    }

    /**
     * @return self
     * @param mixed $codice
     */
    public function setCodice($codice) {
        $this->codice = $codice;
        return $this;
    }

    /**
     * @return self
     * @param mixed $descrizione
     */
    public function setDescrizione($descrizione) {
        $this->descrizione = $descrizione;
        return $this;
    }

    public function __construct() {
        $this->richieste_classificazioni = new \Doctrine\Common\Collections\ArrayCollection();
        $this->azioni = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @param RichiestaProgrammaClassificazione $richiesteClassificazioni
     * @return TC12Classificazione
     */
    public function addRichiesteClassificazioni(RichiestaProgrammaClassificazione $richiesteClassificazioni) {
        $this->richieste_classificazioni[] = $richiesteClassificazioni;

        return $this;
    }

    /**
     * @param RichiestaProgrammaClassificazione $richiesteClassificazioni
     */
    public function removeRichiesteClassificazioni(RichiestaProgrammaClassificazione $richiesteClassificazioni) {
        $this->richieste_classificazioni->removeElement($richiesteClassificazioni);
    }

    /**
     * @param Azione $azioni
     * @return TC12Classificazione
     */
    public function addAzioni(Azione $azioni) {
        $this->azioni[] = $azioni;

        return $this;
    }

    /**
     * @param Azione $azioni
     */
    public function removeAzioni(Azione $azioni) {
        $this->azioni->removeElement($azioni);
    }

    /**
     * @return Collection | Azione[]
     */
    public function getAzioni() {
        return $this->azioni;
    }
}
