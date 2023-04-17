<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:51
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use AttuazioneControlloBundle\Entity\IterProgetto;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC46FaseProceduraleRepository")
 * @ORM\Table(name="tc46_fase_procedurale")
 */
class TC46FaseProcedurale extends EntityLoggabileCancellabile {
    const FASE_INIZIALE = '01';

    const NATURA_LAVORI_PUBBLICI = '03';

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=10, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $cod_fase;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizione_fase;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Assert\Length(max=5, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $codice_natura_cup;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizione_natura_cup;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\IterProgetto", mappedBy="fase_procedurale")
     * @var Collection
     */
    protected $iter;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getCodFase(): ?string {
        return $this->cod_fase;
    }

    public function setCodFase($cod_fase): self {
        $this->cod_fase = $cod_fase;

        return $this;
    }

    public function getDescrizioneFase(): ?string {
        return $this->descrizione_fase;
    }

    public function setDescrizioneFase(string $descrizione_fase): self {
        $this->descrizione_fase = $descrizione_fase;

        return $this;
    }

    public function getCodiceNaturaCup(): ?string {
        return $this->codice_natura_cup;
    }

    public function setCodiceNaturaCup(?string $codice_natura_cup): self {
        $this->codice_natura_cup = $codice_natura_cup;

        return $this;
    }

    public function getDescrizioneNaturaCup(): ?string {
        return $this->descrizione_natura_cup;
    }

    public function setDescrizioneNaturaCup(?string $descrizione_natura_cup): self {
        $this->descrizione_natura_cup = $descrizione_natura_cup;

        return $this;
    }

    public function __toString() {
        return $this->getCodFase() . ' - ' . $this->getDescrizioneFase();
    }

    public function __construct() {
        $this->iter = new ArrayCollection();
    }

    public function addIter(IterProgetto $iter): self {
        $this->iter[] = $iter;

        return $this;
    }

    public function removeIter(IterProgetto $iter): void {
        $this->iter->removeElement($iter);
    }

    public function getIter(): Collection {
        return $this->iter;
    }
}
