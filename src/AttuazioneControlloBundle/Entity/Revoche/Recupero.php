<?php

namespace AttuazioneControlloBundle\Entity\Revoche;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use AttuazioneControlloBundle\Entity\RichiestaPagamento;

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\Revoche\RecuperoRepository")
 * @ORM\Table(name="recuperi")
 */
class Recupero extends EntityLoggabileCancellabile {
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Revoche\TipoFaseRecupero")
     * @ORM\JoinColumn(nullable=true)
     * @Assert\NotBlank
     * @var TipoFaseRecupero|null
     */
    protected $tipo_fase_recupero;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Revoche\TipoSpecificaRecupero")
     * @ORM\JoinColumn(nullable=true)
     * @Assert\NotBlank
     * @var TipoSpecificaRecupero|null
     */
    protected $tipo_specifica_recupero;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Revoche\Revoca", inversedBy="recuperi")
     * @ORM\JoinColumn(nullable=false)
     * @var Revoca
     */
    protected $revoca;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     * @Assert\NotBlank
     */
    protected $contributo_corso_recupero;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $contributo_recuperato;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    protected $numero_incasso;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime|null
     */
    protected $data_incasso;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $importo_interesse_legale;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $importo_interesse_mora;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $importo_sanzione;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $contributo_restituito;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $contributo_non_recuperato;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string
     */
    protected $azioni_mancato_recupero;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Revoche\RataRecupero", mappedBy="recupero", cascade={"persist"})
     * @var Collection|RataRecupero[]
     */
    protected $rate;

    public function __construct(Revoca $revoca = null) {
        $this->rate = new ArrayCollection();
        $this->revoca = $revoca;
    }

    public function getId() {
        return $this->id;
    }

    public function getTipoFaseRecupero(): ?TipoFaseRecupero {
        return $this->tipo_fase_recupero;
    }

    public function getTipoSpecificaRecupero(): ?TipoSpecificaRecupero {
        return $this->tipo_specifica_recupero;
    }

    public function getRevoca(): Revoca {
        return $this->revoca;
    }

    public function getContributoRecuperato() {
        return $this->contributo_recuperato;
    }

    public function getNumeroIncasso() {
        return $this->numero_incasso;
    }

    public function getDataIncasso(): ?\DateTime {
        return $this->data_incasso;
    }

    public function getImportoInteresseLegale() {
        return $this->importo_interesse_legale;
    }

    public function getImportoInteresseMora() {
        return $this->importo_interesse_mora;
    }

    public function getContributoRestituito() {
        return $this->contributo_restituito;
    }

    public function getAzioniMancatoRecupero() {
        return $this->azioni_mancato_recupero;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setTipoFaseRecupero(?TipoFaseRecupero $tipo_fase_recupero): self {
        $this->tipo_fase_recupero = $tipo_fase_recupero;

        return $this;
    }

    public function setTipoSpecificaRecupero(?TipoSpecificaRecupero $tipo_specifica_recupero): self {
        $this->tipo_specifica_recupero = $tipo_specifica_recupero;

        return $this;
    }

    public function setRevoca(Revoca $revoca): self {
        $this->revoca = $revoca;

        return $this;
    }

    public function setContributoRecuperato($contributo_recuperato) {
        $this->contributo_recuperato = $contributo_recuperato;
    }

    public function setNumeroIncasso($numero_incasso) {
        $this->numero_incasso = $numero_incasso;
    }

    public function setDataIncasso(?\DateTime $data_incasso): self {
        $this->data_incasso = $data_incasso;

        return $this;
    }

    public function setImportoInteresseLegale($importo_interesse_legale) {
        $this->importo_interesse_legale = $importo_interesse_legale;
    }

    public function setImportoInteresseMora($importo_interesse_mora) {
        $this->importo_interesse_mora = $importo_interesse_mora;
    }

    public function setContributoRestituito($contributo_restituito) {
        $this->contributo_restituito = $contributo_restituito;
    }

    public function setAzioniMancatoRecupero($azioni_mancato_recupero) {
        $this->azioni_mancato_recupero = $azioni_mancato_recupero;
    }

    public function getContributoCorsoRecupero() {
        return $this->contributo_corso_recupero;
    }

    public function setContributoCorsoRecupero($contributo_corso_recupero) {
        $this->contributo_corso_recupero = $contributo_corso_recupero;
    }

    public function getContributoNonRecuperato() {
        return $this->contributo_non_recuperato;
    }

    public function setContributoNonRecuperato($contributo_non_recuperato) {
        $this->contributo_non_recuperato = $contributo_non_recuperato;
    }

    public function isRecuperoCompleto(): bool {
        return 'COMPLETO' == $this->getTipoFaseRecupero()->getCodice();
    }

    public function isMancatoRecupero(): bool {
        return 'MANCATO' == $this->getTipoFaseRecupero()->getCodice();
    }

    public function isRecuperoChiuso() {
        return $this->isRecuperoCompleto() || $this->isMancatoRecupero();
    }

    public function getRate(): Collection {
        return $this->rate;
    }

    public function setRate(Collection $rate): self {
        $this->rate = $rate;

        return $this;
	}

	public function addRate(RataRecupero $rata): self {
		$this->rate[] = $rata;

		return $this;
	}
	
	public function removeRate(RataRecupero $rata): void{
		$this->rate->removeElement($rata);
	}

    public function getImportoSanzione() {
        return $this->importo_sanzione;
    }

    public function setImportoSanzione($importo_sanzione) {
        $this->importo_sanzione = $importo_sanzione;
    }
}
