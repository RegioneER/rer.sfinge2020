<?php

namespace AttuazioneControlloBundle\Entity\Controlli;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Symfony\Component\Validator\Constraints as Assert;
use AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto;
use RichiesteBundle\Entity\Richiesta;

/**
 * @ORM\Entity
 * @ORM\Table(name="valutazioni_checklist_controlli")
 */
class ValutazioneChecklistControllo extends EntityLoggabileCancellabile
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
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto", inversedBy="valutazioni_checklist")
	 * @ORM\JoinColumn(nullable=false)
	 * @var ControlloProgetto
	 */
	protected $controllo_progetto;
	
	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Controlli\ChecklistControllo", inversedBy="valutazioni_checklist")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $checklist;

	/**
	 * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Utente")
	 * @ORM\JoinColumn(nullable=true)
	 *
	 */
	protected $valutatore;
	
	/**
	 * @ORM\Column(type="boolean", nullable=false)
	 */
	protected $validata;
	
	/**
	 * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Controlli\ValutazioneElementoChecklistControllo", mappedBy="valutazione_checklist", cascade={"persist"})
	 * @Assert\Valid
	 */
	private $valutazioni_elementi;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */	
	protected $data_validazione;
	
	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Controlli\TipoFaseControllo")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $tipo_fase_controllo;
			
	function __construct() {
		$this->valutazioni_elementi = new \Doctrine\Common\Collections\ArrayCollection();
	}
	
	function getId() {
		return $this->id;
	}

	function getChecklist() {
		return $this->checklist;
	}

	function getValutatore() {
		return $this->valutatore;
	}

	function getValidata() {
		return $this->validata;
	}

	function getValutazioniElementi() {
		return $this->valutazioni_elementi;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setChecklist($checklist) {
		$this->checklist = $checklist;
	}

	function setValutatore($valutatore) {
		$this->valutatore = $valutatore;
	}

	function setValidata($validata) {
		$this->validata = $validata;
	}

	function setValutazioniElementi($valutazioni_elementi) {
		$this->valutazioni_elementi = $valutazioni_elementi;
	}
	
	function addValutazioneElemento($valutazione_elemento) {
		$this->valutazioni_elementi->add($valutazione_elemento);
		$valutazione_elemento->setValutazioneChecklist($this);
	}

	function getDataValidazione() {
		return $this->data_validazione;
	}

	function setDataValidazione($data_validazione) {
		$this->data_validazione = $data_validazione;
	}
    
    function getControlloProgetto() {
        return $this->controllo_progetto;
    }

    function setControlloProgetto($controllo_progetto) {
        $this->controllo_progetto = $controllo_progetto;
        return $this;
    }

	/**
	 * @return Richiesta
	 */
	public function getRichiesta() {
		return $this->controllo_progetto->getRichiesta();
	}
		
	public function __toString() {
		$descrizione = $this->getChecklist()->getNome();
		
		return $descrizione;
	}
	
	public function getDescrizioneValutazione() {
		$descrizione = $this->getChecklist()->getNome();
		return $descrizione;		
	}
	
	public function getTipoFaseControllo() {
		return $this->tipo_fase_controllo;
	}

	public function setTipoFaseControllo($tipo_fase_controllo) {
		$this->tipo_fase_controllo = $tipo_fase_controllo;
	}

}
