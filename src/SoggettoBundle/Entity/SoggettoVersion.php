<?php

namespace SoggettoBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity()
 * @ORM\Table(name="soggetti_versions")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="tipo", type="string")
 * @ORM\DiscriminatorMap({"SOGGETTO"="SoggettoBundle\Entity\SoggettoVersion","AZIENDA"="SoggettoBundle\Entity\AziendaVersion","COMUNE"="SoggettoBundle\Entity\ComuneUnioneVersion"})
 */
class SoggettoVersion extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=1024, nullable=true)
	 * 
	 * @Assert\NotBlank()
	 * @Assert\Length(min=2)
	 */
	private $denominazione;

	/**
	 * @ORM\Column(type="string", length=11, nullable=true)
     * @Assert\Length(min = "11", max = "11")
	 */
	private $partita_iva;

	/**
	 * @ORM\Column(type="string", length=16, nullable=false)
	 * @Assert\NotBlank()
	 * @Assert\Length(min=2, max=32)
	 */
	private $codice_fiscale;

	/**
	 * @ORM\Column(type="datetime", nullable=false)
	 */
	private $data_registrazione;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	private $data_costituzione;

	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	private $sito_web;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	private $dimensione;

	/**
	 * @ORM\Column(type="string", length=100, nullable=false)
	 * @Assert\NotBlank()
	 * @Assert\Email()
	 */
	private $email;

	/**
	 * @ORM\Column(type="string", length=20, nullable=false)
	 * @Assert\NotBlank()
	 */
	private $tel;

	/**
	 * @ORM\Column(type="string", length=20, nullable=true)
	 */
	private $fax;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 * @Assert\NotBlank()
	 */
	private $via;

	/**
	 * @ORM\Column(type="string", length=30, nullable=true)
	 * @Assert\NotBlank()
	 */
	private $civico;

	/**
	 * @ORM\Column(type="string", length=5, nullable=true)
	 * @Assert\NotBlank()
	 * @Assert\Length(min = "5", max = "5")
	 */
	private $cap;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $localita;


	/**
	 * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\Ateco")
	 * @ORM\JoinColumn(name="codice_ateco_id", referencedColumnName="id")
	 */
	private $codice_ateco;
	
	/**
	 * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\Ateco")
	 * @ORM\JoinColumn(name="codice_ateco_secondario_id", referencedColumnName="id", nullable=true)) 
	 */
	private $codice_ateco_secondario;

	/**
	 * @var \GeoBundle\Entity\GeoStato $stato
	 *
	 * @ORM\ManyToOne(targetEntity="GeoBundle\Entity\GeoStato")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $stato;	

	/**
	 * @ORM\ManyToOne(targetEntity="GeoBundle\Entity\GeoComune")
	 * @ORM\JoinColumn(name="comune_id", referencedColumnName="id")
	 */
	private $comune;
	
	/**
	 * @var string $provinciaEstera
	 *
	 * @ORM\Column(name="provinciaEstera", type="string", length=255, nullable=true)
	 */
	protected $provinciaEstera;

	/**
	 * @var string $comuneEstero
	 *
	 * @ORM\Column(name="comuneEstero", type="string", length=255, nullable=true)
	 */
	protected $comuneEstero;	

	/**
	 * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\FormaGiuridica", inversedBy="soggetto")
	 * @ORM\JoinColumn(name="forma_giuridica_id", referencedColumnName="id")
	 *
	 * @Assert\NotBlank()
	 */
	private $forma_giuridica;

	/**
	 * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\TipoSoggetto")
	 * @ORM\JoinColumn(name="tipo_soggetto_id", referencedColumnName="id")
	 */
	private $tipo_soggetto;
	
	
	/**
	 * @ORM\Column(type="bigint", nullable=false)
	 */
	private $codice_organismo;
	

	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 * @Assert\Email()
	 */
	private $email_pec;
	
	/**
	 * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\DimensioneImpresa")
	 * @ORM\JoinColumn()
	 * 
	 * @Assert\NotBlank(groups="impresa")
	 */
	private $dimensione_impresa;	
	
	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	private $matricola_inps;
	
	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	private $impresa_iscritta_inps;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $motivazioni_non_iscrizione_inps;
	
	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	private $impresa_iscritta_inail;
	
	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	private $impresa_iscritta_inail_di;
	
	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	private $numero_codice_ditta_impresa_assicurata;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $motivazioni_non_iscrizione_inail;
	
	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	private $ccnl;
	
	/**
	 * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\Soggetto")
	 * @ORM\JoinColumn(name="soggetto_id")
	 */
	private $soggetto;

	function getId() {
		return $this->id;
	}

	function getDenominazione() {
		return $this->denominazione;
	}

	function getPartitaIva() {
		return $this->partita_iva;
	}

	function getCodiceFiscale() {
		return strtoupper($this->codice_fiscale);
	}

	function getDataRegistrazione() {
		return $this->data_registrazione;
	}

	function getDataCostituzione() {
		return $this->data_costituzione;
	}

	function getSitoWeb() {
		return $this->sito_web;
	}

	function getDimensione() {
		return $this->dimensione;
	}

	function getEmail() {
		return $this->email;
	}

	function getTel() {
		return $this->tel;
	}

	function getFax() {
		return $this->fax;
	}

	function getVia() {
		return $this->via;
	}

	function getCivico() {
		return $this->civico;
	}

	function getCap() {
		return $this->cap;
	}

	function getLocalita() {
		return $this->localita;
	}


	function getCodiceAteco() {
		return $this->codice_ateco;
	}

	function getComune() {
		return $this->comune;
	}
	

	function getFormaGiuridica() {
		return $this->forma_giuridica;
	}

	function getTipoSoggetto() {
		return $this->tipo_soggetto;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setDenominazione($denominazione) {
		$this->denominazione = $denominazione;
	}

	function setPartitaIva($partita_iva) {
		$this->partita_iva = $partita_iva;
	}

	function setCodiceFiscale($codice_fiscale) {
		$this->codice_fiscale = $codice_fiscale;
	}

	function setDataRegistrazione($data_registrazione) {
		$this->data_registrazione = $data_registrazione;
	}

	function setDataCostituzione($data_costituzione) {
		$this->data_costituzione = $data_costituzione;
	}

	function setSitoWeb($sito_web) {
		$this->sito_web = $sito_web;
	}

	function setDimensione($dimensione) {
		$this->dimensione = $dimensione;
	}

	function setEmail($email) {
		$this->email = $email;
	}

	function setTel($tel) {
		$this->tel = $tel;
	}

	function setFax($fax) {
		$this->fax = $fax;
	}

	function setVia($via) {
		$this->via = $via;
	}

	function setCivico($civico) {
		$this->civico = $civico;
	}

	function setCap($cap) {
		$this->cap = $cap;
	}

	function setLocalita($localita) {
		$this->localita = $localita;
	}


	function setCodiceAteco($codice_ateco) {
		$this->codice_ateco = $codice_ateco;
	}

	function setComune($comune) {
		$this->comune = $comune;
	}
	

	function setFormaGiuridica($forma_giuridica) {
		$this->forma_giuridica = $forma_giuridica;
	}

	function setTipoSoggetto($tipo_soggetto) {
		$this->tipo_soggetto = $tipo_soggetto;
	}

		function getCodiceOrganismo() {
		return $this->codice_organismo;
	}

	function setCodiceOrganismo($codice_organismo) {
		$this->codice_organismo = $codice_organismo;
	}

	public function __toString() {
            return $this->denominazione;       
	}

	/**
	 * @return mixed
	 */
	public function getEmailPec()
	{
		return $this->email_pec;
	}

	/**
	 * @param mixed $email_pec
	 */
	public function setEmailPec($email_pec)
	{
		$this->email_pec = $email_pec;
	}
	
	function getDimensioneImpresa() {
		return $this->dimensione_impresa;
	}

	function setDimensioneImpresa($dimensione_impresa) {
		$this->dimensione_impresa = $dimensione_impresa;
	}

	
	function getMatricolaInps() {
		return $this->matricola_inps;
	}

	function getImpresaIscrittaInps() {
		return $this->impresa_iscritta_inps;
	}

	function getMotivazioniNonIscrizioneInps() {
		return $this->motivazioni_non_iscrizione_inps;
	}

	function getImpresaIscrittaInail() {
		return $this->impresa_iscritta_inail;
	}

	function getImpresaIscrittaInailDi() {
		return $this->impresa_iscritta_inail_di;
	}

	function getNumeroCodiceDittaImpresaAssicurata() {
		return $this->numero_codice_ditta_impresa_assicurata;
	}

	function getMotivazioniNonIscrizioneInail() {
		return $this->motivazioni_non_iscrizione_inail;
	}

	function getCcnl() {
		return $this->ccnl;
	}

	function setMatricolaInps($matricola_inps) {
		$this->matricola_inps = $matricola_inps;
	}

	function setImpresaIscrittaInps($impresa_iscritta_inps) {
		$this->impresa_iscritta_inps = $impresa_iscritta_inps;
	}

	function setMotivazioniNonIscrizioneInps($motivazioni_non_iscrizione_inps) {
		$this->motivazioni_non_iscrizione_inps = $motivazioni_non_iscrizione_inps;
	}

	function setImpresaIscrittaInail($impresa_iscritta_inail) {
		$this->impresa_iscritta_inail = $impresa_iscritta_inail;
	}

	function setImpresaIscrittaInailDi($impresa_iscritta_inail_di) {
		$this->impresa_iscritta_inail_di = $impresa_iscritta_inail_di;
	}

	function setNumeroCodiceDittaImpresaAssicurata($numero_codice_ditta_impresa_assicurata) {
		$this->numero_codice_ditta_impresa_assicurata = $numero_codice_ditta_impresa_assicurata;
	}

	function setMotivazioniNonIscrizioneInail($motivazioni_non_iscrizione_inail) {
		$this->motivazioni_non_iscrizione_inail = $motivazioni_non_iscrizione_inail;
	}

	function setCcnl($ccnl) {
		$this->ccnl = $ccnl;
	}

	function getSoggetto() {
		return $this->soggetto;
	}

	function setSoggetto($soggetto) {
		$this->soggetto = $soggetto;
	}
	
	function getStato() {
		return $this->stato;
	}

	function getProvinciaEstera() {
		return $this->provinciaEstera;
	}

	function getComuneEstero() {
		return $this->comuneEstero;
	}

	function setStato($stato) {
		$this->stato = $stato;
	}

	function setProvinciaEstera($provinciaEstera) {
		$this->provinciaEstera = $provinciaEstera;
	}

	function setComuneEstero($comuneEstero) {
		$this->comuneEstero = $comuneEstero;
	}
	
	public function getProvincia() {
		return $this->getComune() ? $this->getComune()->getProvincia() : null;
	}
	
	public function getCodiceAtecoSecondario() {
		return $this->codice_ateco_secondario;
	}

	public function setCodiceAtecoSecondario($codice_ateco_secondario) {
		$this->codice_ateco_secondario = $codice_ateco_secondario;
	}

	public function setRegistroEquivalente($registro_equivalente) {
		$this->registro_equivalente = $registro_equivalente;
	}



}
