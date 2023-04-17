<?php

namespace AuditBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="audit_campioni")
 * @ORM\Entity()
 */
class AuditCampione {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="AuditRequisito", inversedBy="campioni")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $audit_requisito;

	/**
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="audit_campioni")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $richiesta;
	protected $selezionato;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $esito;

	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $decurtazione_finanziaria;
	
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $importo_irregolare_pre_contr;
	
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $spesa_publ_irregolare_pre_contr;

	/**
	 * @ORM\ManyToOne(targetEntity="FollowUp")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $follow_up;

	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $importo_post_contraddittorio;
	
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $importo_irregolare;
	
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $spesa_pub_irregolare;

	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $proposta_decertificazione;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $note;

	/**
	 * @ORM\OneToMany(targetEntity="DocumentoCampioneRequisito", mappedBy="audit_campione")
	 */
	protected $documenti_campione_requisito;
	
	protected $documento;

	public function getId() {
		return $this->id;
	}

	public function getAuditRequisito() {
		return $this->audit_requisito;
	}

	public function getRichiesta() {
		return $this->richiesta;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setAuditRequisito($audit_requisito) {
		$this->audit_requisito = $audit_requisito;
	}

	public function setRichiesta($richiesta) {
		$this->richiesta = $richiesta;
	}

	public function getSelezionato() {
		return $this->selezionato;
	}

	public function setSelezionato($selezionato) {
		$this->selezionato = $selezionato;
		return $this;
	}

	public function getFollowUp() {
		return $this->follow_up;
	}

	public function setFollowUp($follow_up) {
		$this->follow_up = $follow_up;
	}

	public function getDocumentiCampioneRequisito() {
		return $this->documenti_campione_requisito;
	}

	public function setDocumentiCampioneRequisito($documenti_campione_requisito) {
		$this->documenti_campione_requisito = $documenti_campione_requisito;
	}

	public function getEsito() {
		return $this->esito;
	}

	public function setEsito($esito) {
		$this->esito = $esito;
	}

	public function getDecurtazioneFinanziaria() {
		return $this->decurtazione_finanziaria;
	}

	public function getImportoPostContraddittorio() {
		return $this->importo_post_contraddittorio;
	}

	public function getPropostaDecertificazione() {
		return $this->proposta_decertificazione;
	}

	public function getNote() {
		return $this->note;
	}

	public function setDecurtazioneFinanziaria($decurtazione_finanziaria) {
		$this->decurtazione_finanziaria = $decurtazione_finanziaria;
	}

	public function setImportoPostContraddittorio($importo_post_contraddittorio) {
		$this->importo_post_contraddittorio = $importo_post_contraddittorio;
	}

	public function setPropostaDecertificazione($proposta_decertificazione) {
		$this->proposta_decertificazione = $proposta_decertificazione;
	}

	public function setNote($note) {
		$this->note = $note;
	}

	public function getDocumento() {
		return $this->documento;
	}

	public function setDocumento($documento) {
		$this->documento = $documento;
	}
	
	public function getImportoIrregolarePreContr() {
		return $this->importo_irregolare_pre_contr;
	}

	public function getSpesaPublIrregolarePreContr() {
		return $this->spesa_publ_irregolare_pre_contr;
	}

	public function getImportoIrregolare() {
		return $this->importo_irregolare;
	}

	public function getSpesaPubIrregolare() {
		return $this->spesa_pub_irregolare;
	}

	public function setImportoIrregolarePreContr($importo_irregolare_pre_contr) {
		$this->importo_irregolare_pre_contr = $importo_irregolare_pre_contr;
	}

	public function setSpesaPublIrregolarePreContr($spesa_publ_irregolare_pre_contr) {
		$this->spesa_publ_irregolare_pre_contr = $spesa_publ_irregolare_pre_contr;
	}

	public function setImportoIrregolare($importo_irregolare) {
		$this->importo_irregolare = $importo_irregolare;
	}

	public function setSpesaPubIrregolare($spesa_pub_irregolare) {
		$this->spesa_pub_irregolare = $spesa_pub_irregolare;
	}



}
