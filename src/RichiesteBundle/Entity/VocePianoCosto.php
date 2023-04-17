<?php

namespace RichiesteBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;
use IstruttorieBundle\Entity\IstruttoriaVocePianoCosto;
use AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo;
use AttuazioneControlloBundle\Entity\VariazioneVocePianoCosto;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
/**
 *
 * @ORM\Entity(repositoryClass="RichiesteBundle\Entity\VocePianoCostoRepository")
 * @ORM\Table(name="voci_piani_costo",
 * indexes={
 *      @ORM\Index(name="idx_piano_costo_id", columns={"piano_costo_id"}),
 * 		@ORM\Index(name="idx_piano_costo_proponente_id", columns={"proponente_id"}),
 * 		@ORM\Index(name="idx_piano_costo_richiesta_id", columns={"richiesta_id"})
 * })
 * 
 * 
 */
class VocePianoCosto extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\PianoCosto", inversedBy="voci_piano_costo")
	 * @ORM\JoinColumn(name="piano_costo_id", referencedColumnName="id", nullable=false)
	 */
	protected $piano_costo;

	/**
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Proponente", inversedBy="voci_piano_costo")
	 * @ORM\JoinColumn(name="proponente_id", referencedColumnName="id", nullable=false)
	 */
	protected $proponente;

	/**
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="voci_piano_costo")
	 * @ORM\JoinColumn(name="richiesta_id", referencedColumnName="id", nullable=false)
	 */
	protected $richiesta;

	/**
	 * @ORM\Column(name="importo_anno_1", type="decimal", precision=10, scale=2, nullable=false, options={"default" = "0.0"})
	 */
	protected $importo_anno_1 = 0.0;

	/**
	 * @ORM\Column(name="importo_anno_2", type="decimal", precision=10, scale=2, nullable=false, options={"default"= "0.0"})
	 */
	protected $importo_anno_2 = 0.0;

	/**
	 * @ORM\Column(name="importo_anno_3", type="decimal", precision=10, scale=2, nullable=false, options={"default"= "0.0"})
	 */
	protected $importo_anno_3 = 0.0;

	/**
	 * @ORM\Column(name="importo_anno_4", type="decimal", precision=10, scale=2, nullable=false, options={"default"= "0.0"})
	 */
	protected $importo_anno_4 = 0.0;

	/**
	 * @ORM\Column(name="importo_anno_5", type="decimal", precision=10, scale=2, nullable=false, options={"default"= "0.0"})
	 */
	protected $importo_anno_5 = 0.0;

	/**
	 * @ORM\Column(name="importo_anno_6", type="decimal", precision=10, scale=2, nullable=false, options={"default"= "0.0"})
	 */
	protected $importo_anno_6 = 0.0;

	/**
	 * @ORM\Column(name="importo_anno_7", type="decimal", precision=10, scale=2, nullable=false, options={"default"= "0.0"})
	 */
	protected $importo_anno_7 = 0.0;
    
	/**
	 * @ORM\Column(name="importo_totale", type="decimal", precision=10, scale=2, nullable=false, options={"default"= "0.0"})
	 */
	protected $importo_totale = 0.0; 
    
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $descrizione;    
	
	protected $errore;
	
	/**
	 * @ORM\OneToOne(targetEntity="IstruttorieBundle\Entity\IstruttoriaVocePianoCosto", mappedBy="voce_piano_costo", cascade={"persist"})
	 */
	protected $istruttoria;
    
	/**
	 * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo", mappedBy="voce_piano_costo")
	 */    
	protected $voci_giustificativi;
	
	/**
	 * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\VariazioneVocePianoCosto", mappedBy="voce_piano_costo")
	 */ 
	protected $voci_variazioni;

    public function getId() {
		return $this->id;
	}

	/**
	 * @return PianoCosto
	 */
	public function getPianoCosto() {
		return $this->piano_costo;
	}

	public function getProponente() {
		return $this->proponente;
	}

	public function getImportoAnno1() {
		return $this->importo_anno_1;
	}

	public function getImportoAnno2() {
		return $this->importo_anno_2;
	}

	public function getImportoAnno3() {
		return $this->importo_anno_3;
	}

	public function getImportoAnno4() {
		return $this->importo_anno_4;
	}

	public function getImportoAnno5() {
		return $this->importo_anno_5;
	}

	public function getImportoAnno6() {
		return $this->importo_anno_6;
	}

	public function getImportoAnno7() {
		return $this->importo_anno_7;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setPianoCosto($piano_costo) {
		$this->piano_costo = $piano_costo;
	}

	public function setProponente($proponente) {
		$this->proponente = $proponente;
	}

	public function setImportoAnno1($importo_anno_1 = '0.0') {
		$this->importo_anno_1 = $importo_anno_1;
	}

	public function setImportoAnno2($importo_anno_2 = '0.0') {
		$this->importo_anno_2 = $importo_anno_2;
	}

	public function setImportoAnno3($importo_anno_3 = '0.0') {
		$this->importo_anno_3 = $importo_anno_3;
	}

	public function setImportoAnno4($importo_anno_4 = '0.0') {
		$this->importo_anno_4 = $importo_anno_4;
	}

	public function setImportoAnno5($importo_anno_5 = '0.0') {
		$this->importo_anno_5 = $importo_anno_5;
	}

	public function setImportoAnno6($importo_anno_6 = '0.0') {
		$this->importo_anno_6 = $importo_anno_6;
	}

	public function setImportoAnno7($importo_anno_7 = '0.0') {
		$this->importo_anno_7 = $importo_anno_7;
	}

	function getErrore() {
		return $this->errore;
	}

	function setErrore($errore) {
		$this->errore = $errore;
	}

	public function getRichiesta() {
		return $this->richiesta;
	}

	public function setRichiesta($richiesta) {
		$this->richiesta = $richiesta;
	}

	/**
	 * @return IstruttoriaVocePianoCosto
	 */
	function getIstruttoria() {
		return $this->istruttoria;
	}

	function setIstruttoria($istruttoria) {
		$this->istruttoria = $istruttoria;
	}
    
    function getVociGiustificativi() {
        //return $this->voci_giustificativi;
		// Ritorno solo le voci piano costo che hanno a null il 'creato da di cui', perchè in questo caso so che sono voci REALI e non generati da DI CUI
		// RITORNA ARRAY: non va bene!
		//return \array_filter(is_array($this->voci_giustificativi) ? $this->voci_giustificativi : $this->voci_giustificativi->getValues(), function($voce_giustificativi){return is_null($voce_giustificativi->getCreatoDaDiCui());});							
		// RITORNA ARRAYCOLLACTION: OK!
		return $this->voci_giustificativi->filter(function($voce_giustificativi){return is_null($voce_giustificativi->getCreatoDaDiCui());});	
    }

    function setVociGiustificativi($voci_giustificativi) {
        $this->voci_giustificativi = $voci_giustificativi;
        return $this;
    }
    
    public function getImportoTotale() {
        return $this->importo_totale;
    }

    public function setImportoTotale($importo_totale) {
        $this->importo_totale = $importo_totale;
        return $this;
    }

    public function getDescrizione() {
        return $this->descrizione;
    }

    public function setDescrizione($descrizione) {
        $this->descrizione = $descrizione;
        return $this;
    }

    public function calcolaTotaleSuTreAnni() {
		$sum = $this->importo_anno_1 + $this->importo_anno_2 + $this->importo_anno_3;
		return $sum;
	}
	
	
	public function isVoceVuota($annualita) {
		for($i = 1; $i <= $annualita; $i++) {
			$stringaImporto = 'importo_anno_'.$i;
			if(!is_null($this->$stringaImporto)) {
				return false;
			}
		}
		return true;
	}
	
	
	public function getTotale() {
		$totale = 0.00;
		for($i = 1; $i <= 7; $i++) {
			$stringaImporto = 'importo_anno_'.$i;
			if(!is_null($this->$stringaImporto)) {
				$totale+= $this->$stringaImporto;
			}
		}
		return $totale;
	}

    public function getTotalePerAnno($anno) {
        $totale = 0.00;
        for($i = $anno; $i <= $anno; $i++) {
            $stringaImporto = 'importo_anno_'.$i;
            if(!is_null($this->$stringaImporto)) {
                $totale+= $this->$stringaImporto;
            }
        }
        return $totale;
    }
	
	
	public function getSoggetto() {
		$proponenti = $this->richiesta->getProponenti();
		foreach ($proponenti as $proponente) {
			if ($proponente->getMandatario()) {
				return $proponente->getSoggetto();
			}
		}
	}
    
    public function getImportoAmmesso($annualita, $variazione_rif = null) {
        $variazione = $this->getRichiesta()->getAttuazioneControllo()->getUltimaVariazioneApprovata($variazione_rif);
        
		if(!is_null($variazione) && !$variazione->getIgnoraVariazione()){
			return $variazione->getVariazioneVocePianoCosto($this)->{"getImportoApprovatoAnno".$annualita}();
		}else{
			return $this->getIstruttoria()->{"getImportoAmmissibileAnno".$annualita}();
		}		
	}
	
	
	public function getTotaleAmmesso() {		
		$totale = is_null($this->getIstruttoria()) ? 0 : $this->getIstruttoria()->sommaImporti();
		return $totale;
	}

	public function mostraLabelRendicontazione() {
        return $this->getPianoCosto()->getTitolo();
    }
	
	public function mostraLabelRendicontazioneConSezione() {
		$pianoCosto = $this->getPianoCosto();
		$sezione = $pianoCosto->getSezionePianoCosto()->getTitoloSezione();
        return $pianoCosto->getTitolo() . ' (' . $sezione . ')';
    }

    public function __construct()
    {
        $this->voci_giustificativi = new ArrayCollection();
        $this->voci_variazioni = new ArrayCollection();
    }


    public function addVociGiustificativi(VocePianoCostoGiustificativo $vociGiustificativi): self
    {
        $this->voci_giustificativi[] = $vociGiustificativi;

        return $this;
    }

    public function removeVociGiustificativi(VocePianoCostoGiustificativo $vociGiustificativi): void
    {
        $this->voci_giustificativi->removeElement($vociGiustificativi);
    }


    public function addVociVariazioni(VariazioneVocePianoCosto $vociVariazioni): self
    {
        $this->voci_variazioni[] = $vociVariazioni;

        return $this;
    }

    public function removeVociVariazioni(VariazioneVocePianoCosto $vociVariazioni): void
    {
        $this->voci_variazioni->removeElement($vociVariazioni);
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVociVariazioni(): Collection
    {
        return $this->voci_variazioni;
	}
	
	public function getUltimaVariazione(): ?VariazioneVocePianoCosto{
		$res = \array_reduce($this->voci_variazioni->toArray(), 
			function(?VariazioneVocePianoCosto $carry, VariazioneVocePianoCosto $value): VariazioneVocePianoCosto {
				if(\is_null($carry)){
					return $value;
				}
				return $carry->getVariazione()->getDataInvio() > $value->getVariazione()->getDataInvio() ? $carry : $value;
			}, 
		null);

		return $res;
	}
	
	public function getOrdinamento() {
		return $this->piano_costo->getOrdinamento();
	}
}
