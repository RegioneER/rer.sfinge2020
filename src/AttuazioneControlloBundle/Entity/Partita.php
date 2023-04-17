<?php
namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use SoggettoBundle\Entity\Soggetto;

/**
 * @ORM\Entity
 * @ORM\Table(name="partite")
 */
class Partita extends EntityLoggabileCancellabile
{
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    protected $id;

    /**
     * 
     * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\Soggetto", inversedBy="partite")
     * @var Soggetto|null
     */
    protected $soggetto;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta", inversedBy="partite")
     * @ORM\JoinColumn(name="attuazione_controllo_richiesta_id", referencedColumnName="id", nullable=false)
     * @var AttuazioneControlloRichiesta
     */
    protected $attuazione_controllo_richiesta;

    /**
     * @ORM\Column(name="numero_partita", type="string", length=15, nullable=false)
     * @var string|null
     */
    protected $numero_partita;

    /**
     * @ORM\Column(name="importo_partita", type="decimal", precision=10, scale=2, nullable=true)
     * @var float|null
     */
    protected $importo_partita;


    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return AttuazioneControlloRichiesta
     */
    public function getAttuazioneControlloRichiesta(): AttuazioneControlloRichiesta
    {
        return $this->attuazione_controllo_richiesta;
    }

    /**
     * @param AttuazioneControlloRichiesta $attuazione_controllo_richiesta
     */
    public function setAttuazioneControlloRichiesta(AttuazioneControlloRichiesta $attuazione_controllo_richiesta): void
    {
        $this->attuazione_controllo_richiesta = $attuazione_controllo_richiesta;
    }

    /**
     * @return string|null
     */
    public function getNumeroPartita(): ?string
    {
        return $this->numero_partita;
    }

    /**
     * @param string|null $numero_partita
     */
    public function setNumeroPartita(?string $numero_partita): void
    {
        $this->numero_partita = $numero_partita;
    }

    /**
     * @return float|null
     */
    public function getImportoPartita(): ?float
    {
        return $this->importo_partita;
    }

    /**
     * @param float|null $importo_partita
     */
    public function setImportoPartita(?float $importo_partita): void
    {
        $this->importo_partita = $importo_partita;
    }

    public function getSoggetto(): ?Soggetto
    {
        return $this->soggetto;
    }
}
