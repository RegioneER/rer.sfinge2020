<?php
namespace SoggettoBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use MonitoraggioBundle\Entity\TC25FormaGiuridica;

/**
 * @ORM\Entity(repositoryClass="SoggettoBundle\Entity\FormaGiuridicaRepository")
 * @ORM\Table(name="forme_giuridiche")
 */
class FormaGiuridica
{
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=8, nullable=true, name="codice")
     */
    private $codice;

    /**
     * @ORM\Column(type="string", length=256, nullable=true, name="descrizione")
     */
    private $descrizione;

    /**
     * @var boolean $soggetto_pubblico
     * @ORM\Column(type="boolean", name="soggetto_pubblico", nullable=true)
     */
    protected $soggetto_pubblico;

    /**
     * @ORM\OneToMany(targetEntity="SoggettoBundle\Entity\Soggetto", mappedBy="forma_giuridica")
     */
    private $soggetto;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC25FormaGiuridica")
     * @ORM\JoinColumn(name="tc25_forma_giuridica_id", referencedColumnName="id")
     */
    private $tc25_forma_giuridica;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=2, nullable=true, name="codice_rna")
     */
    private $codice_rna;

    /**
     * @var int|null
     * @ORM\Column(type="smallint", nullable=true, name="categoria_economica_sap")
     */
    protected $categoria_economica_sap;
    
        
    protected static $codiciSenzaObbligoPIVA = ['1.1.30', '1.8.10', '1.7.10'];
    protected static $codiceProfessionista = '1.1.30';
    protected static $codiciAsdSsd = ['1.7.10', '1.8.10', '1.8.20', '1.4.30', '1.7.40', '1.7.20', '1.7.50'];

    function getId()
    {
        return $this->id;
    }

    function getCodice()
    {
        return $this->codice;
    }

    function getDescrizione()
    {
        return $this->descrizione;
    }

    function getSoggetto()
    {
        return $this->soggetto;
    }

    function setId($id)
    {
        $this->id = $id;
    }

    function setCodice($codice)
    {
        $this->codice = $codice;
    }

    function setDescrizione($descrizione)
    {
        $this->descrizione = $descrizione;
    }

    function setSoggetto($soggetto)
    {
        $this->soggetto = $soggetto;
    }

    function __toString()
    {
        return $this->getCodice() . " - " . $this->getDescrizione();
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->soggetto = new ArrayCollection();
    }

    /**
     * Set soggetto_pubblico
     *
     * @param boolean $soggettoPubblico
     * @return FormaGiuridica
     */
    public function setSoggettoPubblico($soggettoPubblico)
    {
        $this->soggetto_pubblico = $soggettoPubblico;

        return $this;
    }

    /**
     * Get soggetto_pubblico
     *
     * @return boolean 
     */
    public function getSoggettoPubblico()
    {
        return $this->soggetto_pubblico;
    }

    /**
     * Add soggetto
     *
     * @param Soggetto $soggetto
     * @return FormaGiuridica
     */
    public function addSoggetto(Soggetto $soggetto)
    {
        $this->soggetto[] = $soggetto;

        return $this;
    }

    /**
     * Remove soggetto
     *
     * @param Soggetto $soggetto
     */
    public function removeSoggetto(Soggetto $soggetto)
    {
        $this->soggetto->removeElement($soggetto);
    }
    
    public function hasObbligoPartitaIva(): bool
    {
        return !in_array( $this->codice, self::$codiciSenzaObbligoPIVA);
    }
    
    public function isProfessionista(): bool
    {
        return $this->codice == self::$codiceProfessionista;
    }

    public function isAsdSsd(): bool
    {
        return in_array( $this->codice, self::$codiciAsdSsd);
    }

    /**
     * Set tc25_forma_giuridica
     *
     * @param TC25FormaGiuridica $tc25FormaGiuridica
     * @return FormaGiuridica
     */
    public function setTc25FormaGiuridica(TC25FormaGiuridica $tc25FormaGiuridica = null)
    {
        $this->tc25_forma_giuridica = $tc25FormaGiuridica;

        return $this;
    }

    /**
     * Get tc25_forma_giuridica
     *
     * @return TC25FormaGiuridica 
     */
    public function getTc25FormaGiuridica()
    {
        return $this->tc25_forma_giuridica;
    }

    /**
     * @return string|null
     */
    public function getCodiceRna(): ?string
    {
        return $this->codice_rna;
    }

    /**
     * @param string|null $codice_rna
     */
    public function setCodiceRna(?string $codice_rna): void
    {
        $this->codice_rna = $codice_rna;
    }

    /**
     * @return int|null
     */
    public function getCategoriaEconomicaSap(): ?int
    {
        return $this->categoria_economica_sap;
    }

    /**
     * @param int|null $categoria_economica_sap
     */
    public function setCategoriaEconomicaSap(?int $categoria_economica_sap): void
    {
        $this->categoria_economica_sap = $categoria_economica_sap;
    }
    
    /**
     * @return bool
     */
    public function isPrivato() : bool
    {
        return !$this->soggetto_pubblico;
    }
}
