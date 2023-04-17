<?php

namespace AttuazioneControlloBundle\Entity\Revoche;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use RichiesteBundle\Entity\Richiesta;
use AttuazioneControlloBundle\Entity\RichiestaImpegni;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\Revoche\RevocaRepository")
 * @ORM\Table(name="revoche")
 */
class Revoca extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta", inversedBy="revoca")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $attuazione_controllo_richiesta;
	
	/**
     * @ORM\OneToOne(targetEntity="CertificazioniBundle\Entity\RegistroDebitori", mappedBy="revoca")
     */
    protected $registro;

	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Revoche\AttoRevoca")
	 * @ORM\JoinColumn(nullable=true)
	 * 
	 * @var AttoRevoca|null
	 */
	protected $atto_revoca;

	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 * questo a seguito di controllo sogei per semplicita nelle modifiche
	 * è diventato il contributo da recuperare anche il nome è fuorviante
	 * è il CONTRUBUTO DA RECUPERARE
	 * Di fatto andrà sempre a coincidere con la quota di contributo da recuperare.
	 * ATTENZIONE contributo da recupera e contributo della revoca non è detto che coincidano,
	 * in genere può succedere per le revoche parziali con recupero ma è solo una casistica.
	 */
	protected $contributo;
	
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 * Questo è il contributo della quota contributo che viene revocata dal progetto
	 * In caso di revoca totale coincide con il contributo ammesso in concessione.
	 * ATTENZIONE contributo da recupera e contributo della revoca non è detto che coincidano,
	 * in genere può succedere per le revoche parziali con recupero ma è solo una casistica.
	 */
	protected $contributo_revocato;

	/**
	 * @ORM\ManyToMany(targetEntity="AttuazioneControlloBundle\Entity\Revoche\TipoIrregolaritaRevoca")
	 * @ORM\JoinTable(name="revoche_tipi_irregolarita_revoca") 
	 */
	protected $tipo_irregolarita;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $altro;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $nota_invio_conti;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $con_penalita;

	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $importo_penalita;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $data_corresponsione;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $con_ritiro;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $con_recupero;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $invio_conti;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $taglio_ada;
	
	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $articolo_137;
	
	
	/*
	 * Nel caso di taglio ada e invio nei conti l'unico modo per andare direttamente in una chiusura  
	 * è solo aggiungere un collegamento con questa
	 */

	/**
	 * @ORM\ManyToOne(targetEntity="CertificazioniBundle\Entity\CertificazioneChiusura", inversedBy="revoche_invio_conti")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $chiusura;

	/**
	 * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Revoche\Recupero", mappedBy="revoca", cascade={"persist"})
	 */
	protected $recuperi;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $specificare;

	/**
	 * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\RichiestaImpegni", mappedBy="revoca", cascade={"persist", "remove"})
	 * @var RichiestaImpegni|null
	 */
	protected $impegno;
	
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 * In caso di taglio ada rappresenta la quota di contributo del taglio ada
	 */
	protected $contributo_ada;

	public function __construct() {
		$this->recuperi = new ArrayCollection();
	}

	public function getId() {
		return $this->id;
	}

	public function getAttuazioneControlloRichiesta() {
		return $this->attuazione_controllo_richiesta;
	}

	public function getAttoRevoca(): ?AttoRevoca {
		return $this->atto_revoca;
	}

	public function getContributo() {
		return $this->contributo;
	}

	public function getTipoIrregolarita() {
		return $this->tipo_irregolarita;
	}

	public function getAltro() {
		return $this->altro;
	}

	public function getConPenalita() {
		return $this->con_penalita;
	}

	public function getImportoPenalita() {
		return $this->importo_penalita;
	}

	public function getDataCorresponsione() {
		return $this->data_corresponsione;
	}

	public function getConRitiro() {
		return $this->con_ritiro;
	}

	public function getConRecupero() {
		return $this->con_recupero;
	}

	public function getRecuperi() {
		return $this->recuperi;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setAttuazioneControlloRichiesta($attuazione_controllo_richiesta) {
		$this->attuazione_controllo_richiesta = $attuazione_controllo_richiesta;
	}

	public function setAttoRevoca(?AttoRevoca $atto_revoca) {
		$this->atto_revoca = $atto_revoca;
	}

	public function setContributo($contributo) {
		$this->contributo = $contributo;
	}

	public function setTipoIrregolarita($tipo_irregolarita) {
		$this->tipo_irregolarita = $tipo_irregolarita;
	}

	public function setAltro($altro) {
		$this->altro = $altro;
	}

	public function setConPenalita($con_penalita) {
		$this->con_penalita = $con_penalita;
	}

	public function setImportoPenalita($importo_penalita) {
		$this->importo_penalita = $importo_penalita;
	}

	public function setDataCorresponsione($data_corresponsione) {
		$this->data_corresponsione = $data_corresponsione;
	}

	public function setConRitiro($con_ritiro) {
		$this->con_ritiro = $con_ritiro;
	}

	public function setConRecupero($con_recupero) {
		$this->con_recupero = $con_recupero;
	}

	public function getInvioConti() {
		return $this->invio_conti;
	}

	public function setInvioConti($invio_conti) {
		$this->invio_conti = $invio_conti;
	}

	public function setRecuperi($recuperi) {
		$this->recuperi = $recuperi;
	}

	public function addRecuperi(\AttuazioneControlloBundle\Entity\Revoche\Recupero $recuperi) {
		$this->recuperi[] = $recuperi;

		return $this;
	}

	public function removeRecuperi(\AttuazioneControlloBundle\Entity\Revoche\Recupero $recuperi) {
		$this->recuperi->removeElement($recuperi);
	}

	public function getNotaInvioConti() {
		return $this->nota_invio_conti;
	}

	public function setNotaInvioConti($nota_invio_conti) {
		$this->nota_invio_conti = $nota_invio_conti;
	}

	public function getChiusura() {
		return $this->chiusura;
	}

	public function setChiusura($chiusura) {
		$this->chiusura = $chiusura;
	}

	public function getTaglioAda() {
		return $this->taglio_ada;
	}

	public function setTaglioAda($taglio_ada) {
		$this->taglio_ada = $taglio_ada;
	}
	
	public function getArticolo137() {
		return $this->articolo_137;
	}

	public function setArticolo137($articolo_137) {
		$this->articolo_137 = $articolo_137;
	}

	public function hasInvioContiLavorabileONullo() {
		if (is_null($this->chiusura)) {
			return true;
		} elseif ($this->chiusura->getStato()->getCodice() == 'CHI_LAVORAZIONE') {
			return true;
		} else {
			return false;
		}
	}

	public function hasRecuperoCompleto() {
		if (count($this->recuperi) == 0) {
			return false;
		} else {
			$ultimoRecupero = $this->recuperi->last();
			return $ultimoRecupero ? $ultimoRecupero->isRecuperoCompleto() : false;
		}
	}
	
	public function getSpecificare() {
		return $this->specificare;
	}

	public function setSpecificare($specificare) {
		$this->specificare = $specificare;
	}
	
	public function hasPenalita() {
		return $this->con_penalita == true;
	}
	
	public function hasTipoIrregolaritaAltro() {
		foreach ($this->tipo_irregolarita as $tipo) {
			if($tipo->getCodice() == '22') return true;
		}
		return false;
	}
	
	public function getRichiesta(): ?Richiesta {
		return $this->attuazione_controllo_richiesta->getRichiesta();
	}

	public function getRegistro() {
		return $this->registro;
	}

	public function setRegistro($registro) {
		$this->registro = $registro;
	}

	public function getImpegno(): ?RichiestaImpegni{
		return $this->impegno;
	}

	public function setImpegno(?RichiestaImpegni $impegno): self{
		$this->impegno = $impegno;

		return $this;
	}
	
	public function getContributoRevocato() {
		return $this->contributo_revocato;
	}

	public function setContributoRevocato($contributo_revocato) {
		$this->contributo_revocato = $contributo_revocato;
	}
    
	public function getContributoAda() {
		return $this->contributo_ada;
	}

	public function setContributoAda($contributo_ada) {
		$this->contributo_ada = $contributo_ada;
	}
}
