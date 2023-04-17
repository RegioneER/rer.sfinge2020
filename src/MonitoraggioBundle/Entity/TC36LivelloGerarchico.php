<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:36
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use SfingeBundle\Entity\ObiettivoSpecifico;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use SfingeBundle\Entity\Asse;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC36LivelloGerarchicoRepository")
 * @ORM\Table(name="tc36_livello_gerarchico")
 */
class TC36LivelloGerarchico extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=100, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     * @var string
     */
    protected $cod_liv_gerarchico;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     * @Assert\Length(max=1000, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     * @var string
     */
    protected $valore_dati_rilevati;

    /**
     * @ORM\Column(type="string", length=4000, nullable=true)
     * @Assert\Length(max=4000, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     * @var string
     */
    protected $descrizione_codice_livello_gerarchico;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     * @var string
     */
    protected $cod_struttura_prot;

    /**
     * @ORM\OneToMany(targetEntity="SfingeBundle\Entity\Asse", mappedBy="livello_gerarchico")
     * @var Collection|Asse[]
     */
    protected $assi;

    /**
     * @ORM\OneToMany(targetEntity="SfingeBundle\Entity\ObiettivoSpecifico", mappedBy="livello_gerarchico")
     * @var Collection|ObiettivoSpecifico[]
     */
    protected $obiettivi_specifici;

    public function getCodLivGerarchico(): ?string {
        return $this->cod_liv_gerarchico;
    }

    public function setCodLivGerarchico(?string $cod_liv_gerarchico): self {
        $this->cod_liv_gerarchico = $cod_liv_gerarchico;

        return $this;
    }

    public function getValoreDatiRilevati(): ?string {
        return $this->valore_dati_rilevati;
    }

    public function setValoreDatiRilevati(?string $valore_dati_rilevati) {
        $this->valore_dati_rilevati = $valore_dati_rilevati;
    }

    public function getDescrizioneCodiceLivelloGerarchico(): ?string {
        return $this->descrizione_codice_livello_gerarchico;
    }

    public function setDescrizioneCodiceLivelloGerarchico(?string $descrizione_codice_livello_gerarchico): self {
        $this->descrizione_codice_livello_gerarchico = $descrizione_codice_livello_gerarchico;

        return $this;
    }

    public function getCodStrutturaProt(): ?string {
        return $this->cod_struttura_prot;
    }

    public function setCodStrutturaProt(?string $cod_struttura_prot) {
        $this->cod_struttura_prot = $cod_struttura_prot;
    }

    public function __toString() {
        return $this->cod_liv_gerarchico . ' - ' . $this->descrizione_codice_livello_gerarchico;
    }

    public function __construct() {
        $this->assi = new ArrayCollection();
        $this->obiettivi_specifici = new ArrayCollection();
    }

    /**
     * @return Collection|Asse[]
     */
    public function getAssi(): Collection {
        return $this->assi;
    }

    public function setAssi(Collection $assi) {
        $this->assi = $assi;
    }

    public function addAssi(Asse $assi): self {
        $this->assi[] = $assi;

        return $this;
    }

    public function removeAssi(Asse $assi): void {
        $this->assi->removeElement($assi);
    }

    public function addObiettiviSpecifici(ObiettivoSpecifico $obiettiviSpecifici): self {
        $this->obiettivi_specifici[] = $obiettiviSpecifici;

        return $this;
    }

    public function removeObiettiviSpecifici(ObiettivoSpecifico $obiettiviSpecifici): void {
        $this->obiettivi_specifici->removeElement($obiettiviSpecifici);
    }

    /**
     * @return Collection|ObiettivoSpecifico[]
     */
    public function getObiettiviSpecifici(): Collection {
        return $this->obiettivi_specifici;
    }
}
