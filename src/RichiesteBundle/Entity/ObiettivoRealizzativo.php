<?php

namespace RichiesteBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use AttuazioneControlloBundle\Entity\Bando_7\EstensioneGiustificativoBando_7;
use SoggettoBundle\Entity\Soggetto;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="obiettivi_realizzativi")
 * @ORM\Entity(repositoryClass="RichiesteBundle\Entity\ObiettivoRealizzativoRepository")
 */
class ObiettivoRealizzativo extends EntityLoggabileCancellabile {
    /**
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Richiesta", inversedBy="obiettivi_realizzativi")
     * @ORM\JoinColumn
     * @Assert\NotNull
     * @Assert\Type("object")
     * @var Richiesta|null
     */
    protected $richiesta;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\NotNull
     * @Assert\Type("int")
     * @var int|null
     */
    private $codice_or;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\NotNull(groups={"presentazione"})
     * @Assert\Type("string")
     * @var string|null
     */
    private $titolo_or;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\NotNull(groups={"presentazione"})
     * @Assert\Type("int")
     * @Assert\Range(min="1", max="18", minMessage="Il valore deve essere compreso tra 1 e 18", 
     *                                  maxMessage="Il valore deve essere compreso tra 1 e 18", 
     *                                  groups={"presentazione_103"})
     * @var int|null
     */
    private $mese_avvio_previsto;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\NotNull(groups={"presentazione"})
     * @Assert\Type("int")
     * @Assert\Range(min="1", max="18", minMessage="Il valore deve essere compreso tra 1 e 18", 
     *                                  maxMessage="Il valore deve essere compreso tra 1 e 18", 
     *                                  groups={"presentazione_103"})     
     * @var int|null
     */
    private $mese_fine_previsto;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type("int")
     * @Assert\GreaterThan(0)
     * @var int|null
     */
    private $mese_avvio_effettivo;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type("int")
     * @Assert\GreaterThan(0)
     * @var int|null
     */
    private $mese_fine_effettivo;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\NotNull(groups={"presentazione"})
     * @Assert\Type("string")
     * @var string|null
     */
    private $obiettivi_previsti;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\NotNull(groups={"presentazione"})
     * @Assert\Type("string")
     * @var string|null
     */
    private $risultati_attesi;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\NotNull(groups={"presentazione"})
     * @Assert\Type("string")
     * @var string|null
     */
    private $attivita_previste;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Type("string")
     * @var string|null
     */
    private $attivita_svolte;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Type("string")
     * @var string|null
     */
    private $attivita_da_realizzare;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type("int")
     * @Assert\GreaterThanOrEqual(0)
     * @var int|null
     */
    private $percentuale_ri;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type("int")
     * @Assert\GreaterThanOrEqual(0)
     * @var int|null
     */
    private $percentuale_ss;

    /**
     * @ORM\ManyToOne(targetEntity="Proponente", inversedBy="obiettivi_realizzativi")
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Type("object")
     * @var Proponente|null
     */
    protected $proponente;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    private $id_sfinge_2013;

    /**
     * @ORM\ManyToMany(targetEntity="AttuazioneControlloBundle\Entity\Bando_7\EstensioneGiustificativoBando_7", mappedBy="obiettivi_realizzativi")
     * @var Collection|EstensioneGiustificativoBando_7[]
     */
    protected $estensione;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Assert\NotNull(groups={"presentazione_103"})
     * @Assert\Type("float")
     * @Assert\GreaterThanOrEqual(0)
     * @var float|null
     */
    protected $gg_uomo_interno;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Assert\NotNull(groups={"presentazione_103"})
     * @Assert\Type("float")
     * @Assert\GreaterThanOrEqual(0)
     * @var float|null
     */
    protected $gg_uomo_ausiliario;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Assert\NotNull(groups={"presentazione_103"})
     * @Assert\Type("float")
     * @Assert\GreaterThanOrEqual(0)
     * @var float|null
     */
    protected $gg_uomo_ricerca;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Assert\NotNull(groups={"presentazione_103"})
     * @Assert\Type("float")
     * @Assert\GreaterThanOrEqual(0)
     * @var float|null
     */
    protected $gg_uomo_esterno;

    /**
     * @Assert\IsTrue
     */
    public function isCodiceValid(): bool
    {
        if(\is_null($this->codice_or)){
            return true;
        }
        $codiciPresenti = $this->richiesta->getObiettiviRealizzativi()
        ->map(function(ObiettivoRealizzativo $obiettivo){
            return $obiettivo->getCodiceOr();
        })
        ->filter(function($codice){
            return $codice == $this->codice_or;
        })
        ->count();

        return $codiciPresenti < 2;
    }

    public function __construct(?Richiesta $richiesta = null, ?int $codice = null) {
        $this->richiesta = $richiesta;
        $this->codice_or = $codice;
        $this->estensione = new ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getRichiesta(): ?Richiesta {
        return $this->richiesta;
    }

    public function getCodiceOr(): ?int {
        return $this->codice_or;
    }

    public function getTitoloOr(): ?string {
        return $this->titolo_or;
    }

    public function getMeseAvvioPrevisto(): ?int {
        return $this->mese_avvio_previsto;
    }

    public function getMeseFinePrevisto(): ?int {
        return $this->mese_fine_previsto;
    }

    public function getMeseAvvioEffettivo(): ?int {
        return $this->mese_avvio_effettivo;
    }

    public function getMeseFineEffettivo(): ?int {
        return $this->mese_fine_effettivo;
    }

    public function getObiettiviPrevisti(): ?string {
        return $this->obiettivi_previsti;
    }

    public function getRisultatiAttesi(): ?string {
        return $this->risultati_attesi;
    }

    public function getAttivitaPreviste(): ?string {
        return $this->attivita_previste;
    }

    public function getAttivitaSvolte(): ?string {
        return $this->attivita_svolte;
    }

    public function getAttivitaDaRealizzare(): ?string {
        return $this->attivita_da_realizzare;
    }

    public function getPercentualeRi(): ?int {
        return $this->percentuale_ri;
    }

    public function getPercentualeSs(): ?int {
        return $this->percentuale_ss;
    }

    public function setRichiesta(Richiesta $richiesta) {
        $this->richiesta = $richiesta;
    }

    public function setCodiceOr(?int $codice_or) {
        $this->codice_or = $codice_or;
    }

    public function setTitoloOr(?string $titolo_or) {
        $this->titolo_or = $titolo_or;
    }

    public function setMeseAvvioPrevisto(?int $mese_avvio_previsto) {
        $this->mese_avvio_previsto = $mese_avvio_previsto;
    }

    public function setMeseFinePrevisto(?int $mese_fine_previsto) {
        $this->mese_fine_previsto = $mese_fine_previsto;
    }

    public function setMeseAvvioEffettivo(?int $mese_avvio_effettivo) {
        $this->mese_avvio_effettivo = $mese_avvio_effettivo;
    }

    public function setMeseFineEffettivo(?int $mese_fine_effettivo) {
        $this->mese_fine_effettivo = $mese_fine_effettivo;
    }

    public function setObiettiviPrevisti($obiettivi_previsti) {
        $this->obiettivi_previsti = $obiettivi_previsti;
    }

    public function setRisultatiAttesi($risultati_attesi) {
        $this->risultati_attesi = $risultati_attesi;
    }

    public function setAttivitaPreviste(?string $attivita_previste) {
        $this->attivita_previste = $attivita_previste;
    }

    public function setAttivitaSvolte(?string $attivita_svolte) {
        $this->attivita_svolte = $attivita_svolte;
    }

    public function setAttivitaDaRealizzare(?string $attivita_da_realizzare) {
        $this->attivita_da_realizzare = $attivita_da_realizzare;
    }

    public function setPercentualeRi(?int $percentuale_ri): self {
        $this->percentuale_ri = $percentuale_ri;

        return $this;
    }

    public function setPercentualeSs(?int $percentuale_ss): self {
        $this->percentuale_ss = $percentuale_ss;

        return $this;
    }

    public function getIdSfinge2013() {
        return $this->id_sfinge_2013;
    }

    public function setIdSfinge2013($id_sfinge_2013) {
        $this->id_sfinge_2013 = $id_sfinge_2013;
    }

    public function __toString() {
        return $this->codice_or . " - " . $this->titolo_or;
    }

    public function getProponente(): ?Proponente {
        return $this->proponente;
    }

    public function setProponente(?Proponente $proponente) {
        $this->proponente = $proponente;
    }

    public function setGgUomoInterno(?float $ggUomoInterno): self {
        $this->gg_uomo_interno = $ggUomoInterno;

        return $this;
    }

    public function getGgUomoInterno(): ?float {
        return $this->gg_uomo_interno;
    }

    public function setGgUomoAusiliario(?float $ggUomoAusiliario): self {
        $this->gg_uomo_ausiliario = $ggUomoAusiliario;

        return $this;
    }

    public function getGgUomoAusiliario(): ?float {
        return $this->gg_uomo_ausiliario;
    }

    public function setGgUomoRicerca(?float $ggUomoRicerca): self {
        $this->gg_uomo_ricerca = $ggUomoRicerca;

        return $this;
    }

    public function getGgUomoRicerca(): ?float {
        return $this->gg_uomo_ricerca;
    }

    public function setGgUomoEsterno(?float $ggUomoEsterno): self {
        $this->gg_uomo_esterno = $ggUomoEsterno;

        return $this;
    }

    public function getGgUomoEsterno(): ?float {
        return $this->gg_uomo_esterno;
    }

    public function addEstensione(EstensioneGiustificativoBando_7 $estensione): self {
        $this->estensione[] = $estensione;

        return $this;
    }

    public function removeEstensione(EstensioneGiustificativoBando_7 $estensione) {
        $this->estensione->removeElement($estensione);
    }

    public function getEstensione(): Collection {
        return $this->estensione;
    }

    public function getSoggetto(): Soggetto {
        return $this->richiesta->getSoggetto();
    }

    public function getProcedura(): Procedura {
        return $this->richiesta->getProcedura();
    }
}
