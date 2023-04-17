<?php

namespace RichiesteBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use BaseBundle\Entity\Indirizzo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * SedeOperativaRichiesta
 *
 * @ORM\Table(name="sedi_operative_richiesta")
 * @ORM\Entity(repositoryClass="RichiesteBundle\Entity\SedeOperativaRichiestaRepository")
 */
class SedeOperativaRichiesta extends EntityLoggabileCancellabile
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     * @var string|null
     */
    private $denominazione;

    /**
     * @ORM\ManyToOne(targetEntity="BaseBundle\Entity\Indirizzo", cascade={"persist"})
     * @ORM\JoinColumn(name="indirizzo_id", referencedColumnName="id", nullable=false)
     * @Assert\Valid
     * @var Indirizzo|null
     */
    private $indirizzo;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="sedi_operative")
     * @ORM\JoinColumn(name="richiesta_id", referencedColumnName="id", nullable=false)
     *
     * @var Richiesta
     */
    private $richiesta;

    /**
     * @ORM\Column(type="decimal", precision=15, scale=2, name="importo_finanziamento", nullable=true)
     */
    private $importo_finanziamento;

    /**
     * @ORM\Column(type="decimal", precision=15, scale=2, name="importo_sede", nullable=true)
     */
    private $importo_sede;

    /**
     * @ORM\Column(type="decimal", precision=15, scale=2, name="contributo_sede", nullable=true)
     */
    private $contributo_sede;
    
    /**
     * @ORM\Column(type="boolean", nullable=true, name="autodichiarazione")
     */
    private $autodichiarazione;

    /**
     * @ORM\Column(type="string", length=50,  name="tipologia", nullable=true)
     */
    private $tipologia;
    
    
    /**
    * @param Richiesta $richiesta
    */
    public function __construct(Richiesta $richiesta = null)
    {
        $this->richiesta = $richiesta;
    }

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
     * @return string|null
     */
    public function getDenominazione(): ?string
    {
        return $this->denominazione;
    }

    /**
     * @param string|null $denominazione
     */
    public function setDenominazione(?string $denominazione): void
    {
        $this->denominazione = $denominazione;
    }
    
    /**
     * @return Indirizzo|null
     */
    public function getIndirizzo(): ?Indirizzo
    {
        return $this->indirizzo;
    }

    /**
     * @param Indirizzo|null $indirizzo
     */
    public function setIndirizzo(?Indirizzo $indirizzo): void
    {
        $this->indirizzo = $indirizzo;
    }

    /**
     * @return Richiesta
     */
    public function getRichiesta(): Richiesta
    {
        return $this->richiesta;
    }

    /**
     * @param Richiesta $richiesta
     */
    public function setRichiesta(Richiesta $richiesta): void
    {
        $this->richiesta = $richiesta;
    }

    /**
     * @return mixed
     */
    public function getImportoFinanziamento()
    {
        return $this->importo_finanziamento;
    }

    /**
     * @param mixed $importo_finanziamento
     */
    public function setImportoFinanziamento($importo_finanziamento): void
    {
        $this->importo_finanziamento = $importo_finanziamento;
    }
    
    /**
     * @return mixed
     */
    public function getImportoSede()
    {
        return $this->importo_sede;
    }

    /**
     * @param mixed $importo_sede
     */
    public function setImportoSede($importo_sede): void
    {
        $this->importo_sede = $importo_sede;
    }

    /**
     * @return mixed
     */
    public function getContributoSede()
    {
        return $this->contributo_sede;
    }

    /**
     * @param mixed $contributo_sede
     */
    public function setContributoSede($contributo_sede): void
    {
        $this->contributo_sede = $contributo_sede;
    }

    /**
     * @return mixed
     */
    public function getAutodichiarazione()
    {
        return $this->autodichiarazione;
    }

    /**
     * @param mixed $autodichiarazione
     */
    public function setAutodichiarazione($autodichiarazione): void
    {
        $this->autodichiarazione = $autodichiarazione;
    }

    /**
     * @return mixed
     */
    public function getTipologia()
    {
        return $this->tipologia;
    }

    /**
     * @param mixed $tipologia
     */
    public function setTipologia($tipologia): void
    {
        $this->tipologia = $tipologia;
    }
}
