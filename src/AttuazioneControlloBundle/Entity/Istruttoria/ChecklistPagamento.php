<?php

namespace AttuazioneControlloBundle\Entity\Istruttoria;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="checklist_pagamenti")
 */
class ChecklistPagamento
{
	
	const TIPOLOGIA_PRINCIPALE = 'PRINCIPALE';
	const TIPOLOGIA_POST_CONTROLLO_LOCO= 'POST_CONTROLLO_LOCO';
	const TIPOLOGIA_APPALTI_PUBBLICI= 'APPALTI_PUBBLICI';
	const TIPOLOGIA_ANTICIPI = 'ANTICIPI';

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
 	/**
	 * @ORM\ManyToMany(targetEntity="SfingeBundle\Entity\Procedura", inversedBy="checklist_pagamento")
	 * @ORM\JoinTable(name="checklist_pagamento_procedure")
	 */
	protected $procedura;   

	/**
	 * @ORM\Column(type="string", nullable=false)
	 */
	protected $codice;
		
	/**
	 * @ORM\Column(type="string", nullable=false)
	 */
	protected $nome;	
	
	/**
	 * @ORM\Column(type="string", nullable=false)
	 */
	protected $ruolo;
	
	/**
	 * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\SezioneChecklistPagamento", mappedBy="checklist")
	 * @ORM\OrderBy({"ordinamento" = "ASC"})
	 */
	private $sezioni;
	
	/**
	 * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneChecklistPagamento", mappedBy="checklist")
	 */
	protected $valutazioni_checklist;	
	
	/**
	 * serve gestire a categorizzare le checklist ed implementare comportamenti comuni alla tipologia
	 * ad esempio differenziare tra checklist principale e checklist post controllo in loco 
	 * @ORM\Column(name="tipologia", type="string", nullable=false)
	 */
	protected $tipologia;
	
	function __construct() {
		$this->sezioni = new \Doctrine\Common\Collections\ArrayCollection();
		$this->valutazioni_checklist = new \Doctrine\Common\Collections\ArrayCollection();
	}
	
    function getId() {
        return $this->id;
    }

    function getCodice() {
        return $this->codice;
    }

    function getNome() {
        return $this->nome;
    }

    function getRuolo() {
        return $this->ruolo;
    }

    function getSezioni() {
        return $this->sezioni;
    }

    function getValutazioniChecklist() {
        return $this->valutazioni_checklist;
    }

    function setId($id) {
        $this->id = $id;
        return $this;
    }

    function setCodice($codice) {
        $this->codice = $codice;
        return $this;
    }

    function setNome($nome) {
        $this->nome = $nome;
        return $this;
    }

    function setRuolo($ruolo) {
        $this->ruolo = $ruolo;
        return $this;
    }

    function setSezioni($sezioni) {
        $this->sezioni = $sezioni;
        return $this;
    }

    function setValutazioniChecklist($valutazioni_checklist) {
        $this->valutazioni_checklist = $valutazioni_checklist;
        return $this;
    }
	
	function __toString() {
		return $this->nome;
	}

	public function getTipologia() {
		return $this->tipologia;
	}

	public function setTipologia($tipologia) {
		$this->tipologia = $tipologia;
	}

	public function isTipologiaPrincipale(){
		return $this->tipologia == self::TIPOLOGIA_PRINCIPALE;
	}
	
	public function isTipologiaPostControlloLoco(){
		return $this->tipologia == self::TIPOLOGIA_POST_CONTROLLO_LOCO;
	}
	
	public function isTipologiaAppaltiPubblici(){
		return $this->tipologia == self::TIPOLOGIA_APPALTI_PUBBLICI;
	}
	
	public function  isChecklistDiLiquidabilita(){
		return $this->isTipologiaPrincipale() || $this->isTipologiaPostControlloLoco();
	}
	
	public function isTipologiaAnticipi(){
		return $this->tipologia == self::TIPOLOGIA_ANTICIPI;
	}
}
