<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;
use RichiesteBundle\Entity\Richiesta;
use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use RichiesteBundle\Entity\VocePianoCosto;

/**
 *
 * @ORM\Entity()
 * @ORM\Table(name="variazioni_voci_piani_costo")
 */
class VariazioneVocePianoCosto extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\VocePianoCosto", inversedBy="voci_variazioni")
	 * @ORM\JoinColumn(nullable=false)
	 * @var VocePianoCosto|null
	 */
	protected $voce_piano_costo;
	
	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\VariazionePianoCosti", inversedBy="voci_piano_costo")
	 * @ORM\JoinColumn(name="variazione_id", referencedColumnName="id", nullable=false)
	 * @var VariazionePianoCosti
	 */
	protected $variazione;
	
	/**
	 * @ORM\Column(name="importo_variazione_anno_1", type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $importo_variazione_anno_1;

	/**
	 * @ORM\Column(name="importo_variazione_anno_2", type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $importo_variazione_anno_2;

	/**
	 * @ORM\Column(name="importo_variazione_anno_3", type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $importo_variazione_anno_3;

	/**
	 * @ORM\Column(name="importo_variazione_anno_4", type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $importo_variazione_anno_4;

	/**
	 * @ORM\Column(name="importo_variazione_anno_5", type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $importo_variazione_anno_5;

	/**
	 * @ORM\Column(name="importo_variazione_anno_6", type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $importo_variazione_anno_6;

	/**
	 * @ORM\Column(name="importo_variazione_anno_7", type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $importo_variazione_anno_7;
	
	/**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    protected $nota_anno_1;
	
	/**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    protected $nota_anno_2;
	
	/**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    protected $nota_anno_3;
	
	/**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    protected $nota_anno_4;
	
	/**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    protected $nota_anno_5;
	
	/**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    protected $nota_anno_6;
	
	/**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    protected $nota_anno_7;
	
	/**
	 * @ORM\Column(name="importo_approvato_anno_1", type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $importo_approvato_anno_1;

	/**
	 * @ORM\Column(name="importo_approvato_anno_2", type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $importo_approvato_anno_2;

	/**
	 * @ORM\Column(name="importo_approvato_anno_3", type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $importo_approvato_anno_3;

	/**
	 * @ORM\Column(name="importo_approvato_anno_4", type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $importo_approvato_anno_4;

	/**
	 * @ORM\Column(name="importo_approvato_anno_5", type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $importo_approvato_anno_5;

	/**
	 * @ORM\Column(name="importo_approvato_anno_6", type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $importo_approvato_anno_6;

	/**
	 * @ORM\Column(name="importo_approvato_anno_7", type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $importo_approvato_anno_7;
	
	/**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    protected $nota_istruttore_anno_1;
	
	/**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    protected $nota_istruttore_anno_2;
	
	/**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    protected $nota_istruttore_anno_3;
	
	/**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    protected $nota_istruttore_anno_4;
	
	/**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    protected $nota_istruttore_anno_5;
	
	/**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    protected $nota_istruttore_anno_6;
	
	/**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    protected $nota_istruttore_anno_7;

	public function getId() {
		return $this->id;
	}

	public function getVocePianoCosto():?VocePianoCosto {
		return $this->voce_piano_costo;
	}

	public function getImportoVariazioneAnno1() {
		return $this->importo_variazione_anno_1;
	}

	public function getImportoVariazioneAnno2() {
		return $this->importo_variazione_anno_2;
	}

	public function getImportoVariazioneAnno3() {
		return $this->importo_variazione_anno_3;
	}

	public function getImportoVariazioneAnno4() {
		return $this->importo_variazione_anno_4;
	}

	public function getImportoVariazioneAnno5() {
		return $this->importo_variazione_anno_5;
	}

	public function getImportoVariazioneAnno6() {
		return $this->importo_variazione_anno_6;
	}

	public function getImportoVariazioneAnno7() {
		return $this->importo_variazione_anno_7;
	}

	public function getNotaAnno1() {
		return $this->nota_anno_1;
	}

	public function getNotaAnno2() {
		return $this->nota_anno_2;
	}

	public function getNotaAnno3() {
		return $this->nota_anno_3;
	}

	public function getNotaAnno4() {
		return $this->nota_anno_4;
	}

	public function getNotaAnno5() {
		return $this->nota_anno_5;
	}

	public function getNotaAnno6() {
		return $this->nota_anno_6;
	}

	public function getNotaAnno7() {
		return $this->nota_anno_7;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setVocePianoCosto($voce_piano_costo) {
		$this->voce_piano_costo = $voce_piano_costo;
	}

	public function setImportoVariazioneAnno1($importo_variazione_anno_1) {
		$this->importo_variazione_anno_1 = $importo_variazione_anno_1;
	}

	public function setImportoVariazioneAnno2($importo_variazione_anno_2) {
		$this->importo_variazione_anno_2 = $importo_variazione_anno_2;
	}

	public function setImportoVariazioneAnno3($importo_variazione_anno_3) {
		$this->importo_variazione_anno_3 = $importo_variazione_anno_3;
	}

	public function setImportoVariazioneAnno4($importo_variazione_anno_4) {
		$this->importo_variazione_anno_4 = $importo_variazione_anno_4;
	}

	public function setImportoVariazioneAnno5($importo_variazione_anno_5) {
		$this->importo_variazione_anno_5 = $importo_variazione_anno_5;
	}

	public function setImportoVariazioneAnno6($importo_variazione_anno_6) {
		$this->importo_variazione_anno_6 = $importo_variazione_anno_6;
	}

	public function setImportoVariazioneAnno7($importo_variazione_anno_7) {
		$this->importo_variazione_anno_7 = $importo_variazione_anno_7;
	}

	public function setNotaAnno1($nota_anno_1) {
		$this->nota_anno_1 = $nota_anno_1;
	}

	public function setNotaAnno2($nota_anno_2) {
		$this->nota_anno_2 = $nota_anno_2;
	}

	public function setNotaAnno3($nota_anno_3) {
		$this->nota_anno_3 = $nota_anno_3;
	}

	public function setNotaAnno4($nota_anno_4) {
		$this->nota_anno_4 = $nota_anno_4;
	}

	public function setNotaAnno5($nota_anno_5) {
		$this->nota_anno_5 = $nota_anno_5;
	}

	public function setNotaAnno6($nota_anno_6) {
		$this->nota_anno_6 = $nota_anno_6;
	}

	public function setNotaAnno7($nota_anno_7) {
		$this->nota_anno_7 = $nota_anno_7;
	}

	public function getVariazione():VariazionePianoCosti {
		return $this->variazione;
	}

	public function setVariazione($variazione) {
		$this->variazione = $variazione;
	}
	
	public function getImportoApprovatoAnno1() {
		return $this->importo_approvato_anno_1;
	}

	public function getImportoApprovatoAnno2() {
		return $this->importo_approvato_anno_2;
	}

	public function getImportoApprovatoAnno3() {
		return $this->importo_approvato_anno_3;
	}

	public function getImportoApprovatoAnno4() {
		return $this->importo_approvato_anno_4;
	}

	public function getImportoApprovatoAnno5() {
		return $this->importo_approvato_anno_5;
	}

	public function getImportoApprovatoAnno6() {
		return $this->importo_approvato_anno_6;
	}

	public function getImportoApprovatoAnno7() {
		return $this->importo_approvato_anno_7;
	}

	public function getNotaIstruttoreAnno1() {
		return $this->nota_istruttore_anno_1;
	}

	public function getNotaIstruttoreAnno2() {
		return $this->nota_istruttore_anno_2;
	}

	public function getNotaIstruttoreAnno3() {
		return $this->nota_istruttore_anno_3;
	}

	public function getNotaIstruttoreAnno4() {
		return $this->nota_istruttore_anno_4;
	}

	public function getNotaIstruttoreAnno5() {
		return $this->nota_istruttore_anno_5;
	}

	public function getNotaIstruttoreAnno6() {
		return $this->nota_istruttore_anno_6;
	}

	public function getNotaIstruttoreAnno7() {
		return $this->nota_istruttore_anno_7;
	}

	public function setImportoApprovatoAnno1($importo_approvato_anno_1) {
		$this->importo_approvato_anno_1 = $importo_approvato_anno_1;
	}

	public function setImportoApprovatoAnno2($importo_approvato_anno_2) {
		$this->importo_approvato_anno_2 = $importo_approvato_anno_2;
	}

	public function setImportoApprovatoAnno3($importo_approvato_anno_3) {
		$this->importo_approvato_anno_3 = $importo_approvato_anno_3;
	}

	public function setImportoApprovatoAnno4($importo_approvato_anno_4) {
		$this->importo_approvato_anno_4 = $importo_approvato_anno_4;
	}

	public function setImportoApprovatoAnno5($importo_approvato_anno_5) {
		$this->importo_approvato_anno_5 = $importo_approvato_anno_5;
	}

	public function setImportoApprovatoAnno6($importo_approvato_anno_6) {
		$this->importo_approvato_anno_6 = $importo_approvato_anno_6;
	}

	public function setImportoApprovatoAnno7($importo_approvato_anno_7) {
		$this->importo_approvato_anno_7 = $importo_approvato_anno_7;
	}

	public function setNotaIstruttoreAnno1($nota_istruttore_anno_1) {
		$this->nota_istruttore_anno_1 = $nota_istruttore_anno_1;
	}

	public function setNotaIstruttoreAnno2($nota_istruttore_anno_2) {
		$this->nota_istruttore_anno_2 = $nota_istruttore_anno_2;
	}

	public function setNotaIstruttoreAnno3($nota_istruttore_anno_3) {
		$this->nota_istruttore_anno_3 = $nota_istruttore_anno_3;
	}

	public function setNotaIstruttoreAnno4($nota_istruttore_anno_4) {
		$this->nota_istruttore_anno_4 = $nota_istruttore_anno_4;
	}

	public function setNotaIstruttoreAnno5($nota_istruttore_anno_5) {
		$this->nota_istruttore_anno_5 = $nota_istruttore_anno_5;
	}

	public function setNotaIstruttoreAnno6($nota_istruttore_anno_6) {
		$this->nota_istruttore_anno_6 = $nota_istruttore_anno_6;
	}

	public function setNotaIstruttoreAnno7($nota_istruttore_anno_7) {
		$this->nota_istruttore_anno_7 = $nota_istruttore_anno_7;
	}

	public function verificaImporti(array $annualita): bool {
		foreach($annualita as $anno => $descrizione) {
			$importo = $this->{"getImportoVariazioneAnno".$anno}();
			if (\is_null($importo)) {
				return  false;
			}
		}

		return true;
	}

	public function sommaImporti() {
        $somma_importi = 0;
        for ($anno = 1; $anno <= 7; $anno++) {
            $somma_importi += $this->{"getImportoVariazioneAnno".$anno}();
        }
        
        return $somma_importi;
    }   
	
	public function sommaImportiApprovati() {
        $somma_importi = 0;
        for ($anno = 1; $anno <= 7; $anno++) {
            $somma_importi += $this->{"getImportoApprovatoAnno".$anno}();
        }
        
        return $somma_importi;
    } 
	
	public function sommaImportiAvanzamento() {
       return $this->sommaImportiApprovati();
    }
	
	public function getImportoVariazione($annualita, $variazione_rif = null) {
        $variazione = $this->getRichiesta()->getAttuazioneControllo()->getUltimaVariazioneApprovata($variazione_rif);
        
		if(!is_null($variazione) && !$variazione->getIgnoraVariazione()){
			return $variazione->getVariazioneVocePianoCosto($this)->{"getImportoApprovatoAnno".$annualita}();
		}else{
			return $this->getIstruttoria()->{"getImportoApprovatoAnno".$annualita}();
		}		
	}
	
	public function getRichiesta(): ?Richiesta{
		return $this->variazione->getRichiesta();
	}

	public function getIstruttoria(): ?IstruttoriaRichiesta{
		return $this->getRichiesta()->getIstruttoria();
	}
}
