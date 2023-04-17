<?php

namespace RichiesteBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use SfingeBundle\Entity\Procedura;

/**
 * @ORM\Entity
 * @ORM\Table(name="sezioni_piano_costo")
 */
class SezionePianoCosto extends EntityLoggabileCancellabile {
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @var string
     */
    private $codice;

    /**
     * @ORM\Column(type="string", length=1024, nullable=false)
     */
    private $titolo_sezione;

    /**
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\PianoCosto", mappedBy="sezione_piano_costo")
     * @var Collection|PianoCosto[]
     */
    private $piani_costo;

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Procedura", inversedBy="sezioni_piani_costo")
     * @ORM\JoinColumn(nullable=true)
     * @var Procedura|null
     */
    protected $procedura;

    public function __construct() {
        $this->piani_costo = new ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getCodice(): ?string {
        return $this->codice;
    }

    public function getTitoloSezione(): ?string {
        return $this->titolo_sezione;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setCodice(string $codice) {
        $this->codice = $codice;
    }

    public function setTitoloSezione(string $titolo_sezione) {
        $this->titolo_sezione = $titolo_sezione;
    }

    /**
     * @var PianoCosto[]
     */
    public function getPianiCosto(): Collection {
        return $this->piani_costo;
    }

    public function setPianiCosto(Collection $piani_costo): self {
        $this->piani_costo = $piani_costo;

        return $this;
    }

    public function getProcedura(): ?Procedura {
        return $this->procedura;
    }

    public function setProcedura(Procedura $procedura): self {
        $this->procedura = $procedura;

        return $this;
    }

    public function __toString() {
        return $this->titolo_sezione;
    }

    public function addPianiCosto(PianoCosto $pianiCosto): self {
        $this->piani_costo[] = $pianiCosto;

        return $this;
    }

    public function removePianiCosto(PianoCosto $pianiCosto): void {
        $this->piani_costo->removeElement($pianiCosto);
    }

    public function getPianoCostiOrdinate(): Collection {
		$voci = $this->piani_costo->toArray();
		$res = \usort($voci, function(PianoCosto $a, PianoCosto $b){
			return  $b->getOrdinamento() - $a->getOrdinamento();
		});
		
		return new ArrayCollection($voci);
    }
}
