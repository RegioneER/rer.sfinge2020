<?php

namespace AnagraficheBundle\Entity;

use AttuazioneControlloBundle\Entity\Pagamento;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DocumentoBundle\Entity\DocumentoFile;
use SfingeBundle\Entity\Utente;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * 
 * @ORM\Entity(repositoryClass="AnagraficheBundle\Entity\PersonaleRepository")
 * @ORM\Table(name="personale")
 */
class Personale extends EntityLoggabileCancellabile{

	/**
	 * @var integer $id
	 *
	 *
	 * @ORM\Column(name="id", type="bigint")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @var string $nome
	 *
	 * @ORM\Column(name="nome", type="string", length=50)
	 * 
	 */
	protected $nome;

	/**
	 * @var string $cognome
	 *
	 * @ORM\Column(name="cognome", type="string", length=50)
	 * 
	 */
	protected $cognome;


	/**
	 * @var \DateTime $data_assunzione
	 *
	 * @ORM\Column(name="data_assunzione", type="date", nullable=true)
	 * 
	 */
	protected $data_assunzione;
	
	/**
	 * @var \DateTime $data_assunzione
	 *
	 * @ORM\Column(name="data_cessazione", type="date", nullable=true)
	 * 
	 */
	protected $data_cessazione;
	
    /**
     * @ORM\ManyToOne(targetEntity="AnagraficheBundle\Entity\TipologiaAssunzione")
     * @ORM\JoinColumn(name="tipologia_assunzione_id", referencedColumnName="id", nullable=true)
     */
	protected $tipologia_assunzione;
	
	
	/**
	 * @var string $titolo_studio
	 *
	 * @ORM\Column(name="titolo_studio", type="string", length=250, nullable=true)
	 * 
	 */
	protected $titolo_studio;
	
	/**
	 * @var string $tipologia_laurea
	 *
	 * @ORM\Column(name="tipologia_laurea", type="string", length=250, nullable=true)
	 * 
	 */
	protected $tipologia_laurea;
	
	/**
	 * @var \DateTime $data_conseguimento
	 *
	 * @ORM\Column(name="data_conseguimento", type="date", nullable=true)
	 * 
	 */
	protected $data_conseguimento;
	
	/**
	 * @var string $universita
	 *
	 * @ORM\Column(name="universita", type="string", length=500, nullable=true)
	 * 
	 */
	protected $universita;

	/**
     * @var Pagamento
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", inversedBy = "personale")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $pagamento;
	
	/**
     * @var Collection|DocumentoPersonale[]
	 * @ORM\OneToMany(targetEntity="AnagraficheBundle\Entity\DocumentoPersonale", mappedBy="personale")
	 */
	private $documenti_personale;

	
	/**
	 * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\EstensioneGiustificativo", mappedBy="ricercatore")
	 */
	private $estensioneGiustificativo;
	
	/**
	 * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento" , cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $istruttoria_oggetto_pagamento; 	

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $rendicontata_in_sal;	
	
	public function __toString() {
		return $this->getNome() . ' ' . $this->getCognome();
	}
	
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getNome()
	{
		return $this->nome;
	}

	/**
	 * @param string $nome
	 */
	public function setNome(string $nome)
	{
		$this->nome = $nome;
	}

	/**
	 * @return string
	 */
	public function getCognome()
	{
		return $this->cognome;
	}

	/**
	 * @param string $cognome
	 */
	public function setCognome(string $cognome)
	{
		$this->cognome = $cognome;
	}
	
	public function getDataAssunzione() {
		return $this->data_assunzione;
	}

	public function getTipologiaAssunzione() {
		return $this->tipologia_assunzione;
	}

	public function getTitoloStudio() {
		return $this->titolo_studio;
	}

	public function getTipologiaLaurea() {
		return $this->tipologia_laurea;
	}

	public function getDataConseguimento() {
		return $this->data_conseguimento;
	}

	public function getUniversita() {
		return $this->universita;
	}

	public function getPagamento() {
		return $this->pagamento;
	}

	public function getDocumentiPersonale() {
		return $this->documenti_personale;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setDataAssunzione($data_assunzione) {
		$this->data_assunzione = $data_assunzione;
	}

	public function setTipologiaAssunzione($tipologia_assunzione) {
		$this->tipologia_assunzione = $tipologia_assunzione;
	}

	public function setTitoloStudio($titolo_studio) {
		$this->titolo_studio = $titolo_studio;
	}

	public function setTipologiaLaurea($tipologia_laurea) {
		$this->tipologia_laurea = $tipologia_laurea;
	}

	public function setDataConseguimento($data_conseguimento) {
		$this->data_conseguimento = $data_conseguimento;
	}

	public function setUniversita($universita) {
		$this->universita = $universita;
	}

	public function setPagamento($pagamento) {
		$this->pagamento = $pagamento;
	}

	public function setDocumentiPersonale($documenti_personale) {
		$this->documenti_personale = $documenti_personale;
	}

	public function getSoggetto() {
		return $this->pagamento->getSoggetto();
	}
	
	function getDataCessazione() {
		return $this->data_cessazione;
	}

	function setDataCessazione($data_cessazione) {
		$this->data_cessazione = $data_cessazione;
	}
	
	public function getEstensioneGiustificativo() {
		return $this->estensioneGiustificativo;
	}

	public function setEstensioneGiustificativo($estensioneGiustificativo) {
		$this->estensioneGiustificativo = $estensioneGiustificativo;
	}

	function getIstruttoriaOggettoPagamento() {
		return $this->istruttoria_oggetto_pagamento;
	}

	function setIstruttoriaOggettoPagamento($istruttoria_oggetto_pagamento) {
		$this->istruttoria_oggetto_pagamento = $istruttoria_oggetto_pagamento;
	}
	
	function getRendicontataInSal() {
		return $this->rendicontata_in_sal;
	}

	function setRendicontataInSal($rendicontata_in_sal) {
		$this->rendicontata_in_sal = $rendicontata_in_sal;
	}
	
}
