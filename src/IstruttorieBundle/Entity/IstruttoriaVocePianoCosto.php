<?php

namespace IstruttorieBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Entity(repositoryClass="IstruttorieBundle\Repository\IstruttoriaVocePianoCostoRepository")
 * @ORM\Table(name="istruttorie_voci_piani_costo")
 */
class IstruttoriaVocePianoCosto extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\OneToOne(targetEntity="RichiesteBundle\Entity\VocePianoCosto", inversedBy="istruttoria")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $voce_piano_costo;
	
	/**
	 * @ORM\Column(name="taglio_anno_1", type="decimal", precision=10, scale=2, nullable=false, options={ "default" = "0.0" })
	 */
	protected $taglio_anno_1 = 0.0;

	/**
	 * @ORM\Column(name="taglio_anno_2", type="decimal", precision=10, scale=2, nullable=false, options={ "default" = "0.0" })
	 */
	protected $taglio_anno_2 = 0.0;

	/**
	 * @ORM\Column(name="taglio_anno_3", type="decimal", precision=10, scale=2, nullable=false, options={ "default" = "0.0" })
	 */
	protected $taglio_anno_3 = 0.0;

	/**
	 * @ORM\Column(name="taglio_anno_4", type="decimal", precision=10, scale=2, nullable=false, options={ "default" = "0.0" })
	 */
	protected $taglio_anno_4 = 0.0;

	/**
	 * @ORM\Column(name="taglio_anno_5", type="decimal", precision=10, scale=2, nullable=false, options={ "default" = "0.0" })
	 */
	protected $taglio_anno_5 = 0.0;

	/**
	 * @ORM\Column(name="taglio_anno_6", type="decimal", precision=10, scale=2, nullable=false, options={ "default" = "0.0" })
	 */
	protected $taglio_anno_6 = 0.0;

	/**
	 * @ORM\Column(name="taglio_anno_7", type="decimal", precision=10, scale=2, nullable=false, options={ "default" = "0.0" })
	 */
	protected $taglio_anno_7 = 0.0;
	
	/**
	 * @ORM\Column(name="importo_ammissibile_anno_1", type="decimal", precision=10, scale=2, nullable=false, options={ "default" = "0.0" })
	 */
	protected $importo_ammissibile_anno_1 = 0.0;

	/**
	 * @ORM\Column(name="importo_ammissibile_anno_2", type="decimal", precision=10, scale=2, nullable=false, options={ "default" = "0.0" })
	 */
	protected $importo_ammissibile_anno_2 = 0.0;

	/**
	 * @ORM\Column(name="importo_ammissibile_anno_3", type="decimal", precision=10, scale=2, nullable=false, options={ "default" = "0.0" })
	 */
	protected $importo_ammissibile_anno_3 = 0.0;

	/**
	 * @ORM\Column(name="importo_ammissibile_anno_4", type="decimal", precision=10, scale=2, nullable=false, options={ "default" = "0.0" })
	 */
	protected $importo_ammissibile_anno_4 = 0.0;

	/**
	 * @ORM\Column(name="importo_ammissibile_anno_5", type="decimal", precision=10, scale=2, nullable=false, options={ "default" = "0.0" })
	 */
	protected $importo_ammissibile_anno_5 = 0.0;

	/**
	 * @ORM\Column(name="importo_ammissibile_anno_6", type="decimal", precision=10, scale=2, nullable=false, options={ "default" = "0.0" })
	 */
	protected $importo_ammissibile_anno_6 = 0.0;

	/**
	 * @ORM\Column(name="importo_ammissibile_anno_7", type="decimal", precision=10, scale=2, nullable=false, options={ "default" = "0.0" })
	 */
	protected $importo_ammissibile_anno_7 = 0.0;
	
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

	public function getId() {
		return $this->id;
	}

	function getTaglioAnno1() {
		return $this->taglio_anno_1;
	}

	function getTaglioAnno2() {
		return $this->taglio_anno_2;
	}

	function getTaglioAnno3() {
		return $this->taglio_anno_3;
	}

	function getTaglioAnno4() {
		return $this->taglio_anno_4;
	}

	function getTaglioAnno5() {
		return $this->taglio_anno_5;
	}

	function getTaglioAnno6() {
		return $this->taglio_anno_6;
	}

	function getTaglioAnno7() {
		return $this->taglio_anno_7;
	}

	function getImportoAmmissibileAnno1() {
		return $this->importo_ammissibile_anno_1;
	}

	function getImportoAmmissibileAnno2() {
		return $this->importo_ammissibile_anno_2;
	}

	function getImportoAmmissibileAnno3() {
		return $this->importo_ammissibile_anno_3;
	}

	function getImportoAmmissibileAnno4() {
		return $this->importo_ammissibile_anno_4;
	}

	function getImportoAmmissibileAnno5() {
		return $this->importo_ammissibile_anno_5;
	}

	function getImportoAmmissibileAnno6() {
		return $this->importo_ammissibile_anno_6;
	}

	function getImportoAmmissibileAnno7() {
		return $this->importo_ammissibile_anno_7;
	}

	function setTaglioAnno1($taglio_anno_1 = '0.0') {
		$this->taglio_anno_1 = $taglio_anno_1;
	}

	function setTaglioAnno2($taglio_anno_2 = '0.0') {
		$this->taglio_anno_2 = $taglio_anno_2;
	}

	function setTaglioAnno3($taglio_anno_3 = '0.0') {
		$this->taglio_anno_3 = $taglio_anno_3;
	}

	function setTaglioAnno4($taglio_anno_4 = '0.0') {
		$this->taglio_anno_4 = $taglio_anno_4;
	}

	function setTaglioAnno5($taglio_anno_5 = '0.0') {
		$this->taglio_anno_5 = $taglio_anno_5;
	}

	function setTaglioAnno6($taglio_anno_6 = '0.0') {
		$this->taglio_anno_6 = $taglio_anno_6;
	}

	function setTaglioAnno7($taglio_anno_7 = '0.0') {
		$this->taglio_anno_7 = $taglio_anno_7;
	}

	function setImportoAmmissibileAnno1($importo_ammissibile_anno_1 = '0.0') {
		$this->importo_ammissibile_anno_1 = $importo_ammissibile_anno_1;
	}

	function setImportoAmmissibileAnno2($importo_ammissibile_anno_2 = '0.0') {
		$this->importo_ammissibile_anno_2 = $importo_ammissibile_anno_2;
	}

	function setImportoAmmissibileAnno3($importo_ammissibile_anno_3 = '0.0') {
		$this->importo_ammissibile_anno_3 = $importo_ammissibile_anno_3;
	}

	function setImportoAmmissibileAnno4($importo_ammissibile_anno_4 = '0.0') {
		$this->importo_ammissibile_anno_4 = $importo_ammissibile_anno_4;
	}

	function setImportoAmmissibileAnno5($importo_ammissibile_anno_5 = '0.0') {
		$this->importo_ammissibile_anno_5 = $importo_ammissibile_anno_5;
	}

	function setImportoAmmissibileAnno6($importo_ammissibile_anno_6 = '0.0') {
		$this->importo_ammissibile_anno_6 = $importo_ammissibile_anno_6;
	}

	function setImportoAmmissibileAnno7($importo_ammissibile_anno_7 = '0.0') {
		$this->importo_ammissibile_anno_7 = $importo_ammissibile_anno_7;
	}

	function getNotaAnno1() {
		return $this->nota_anno_1;
	}

	function getNotaAnno2() {
		return $this->nota_anno_2;
	}

	function getNotaAnno3() {
		return $this->nota_anno_3;
	}

	function getNotaAnno4() {
		return $this->nota_anno_4;
	}

	function getNotaAnno5() {
		return $this->nota_anno_5;
	}

	function getNotaAnno6() {
		return $this->nota_anno_6;
	}

	function getNotaAnno7() {
		return $this->nota_anno_7;
	}

	function setNotaAnno1($nota_anno_1) {
		$this->nota_anno_1 = $nota_anno_1;
	}

	function setNotaAnno2($nota_anno_2) {
		$this->nota_anno_2 = $nota_anno_2;
	}

	function setNotaAnno3($nota_anno_3) {
		$this->nota_anno_3 = $nota_anno_3;
	}

	function setNotaAnno4($nota_anno_4) {
		$this->nota_anno_4 = $nota_anno_4;
	}

	function setNotaAnno5($nota_anno_5) {
		$this->nota_anno_5 = $nota_anno_5;
	}

	function setNotaAnno6($nota_anno_6) {
		$this->nota_anno_6 = $nota_anno_6;
	}

	function setNotaAnno7($nota_anno_7) {
		$this->nota_anno_7 = $nota_anno_7;
	}

	function getVocePianoCosto() {
		return $this->voce_piano_costo;
	}

	function setVocePianoCosto($voce_piano_costo) {
		$this->voce_piano_costo = $voce_piano_costo;
	}
    
    public function sommaImporti(): float {
        $somma_importi = 0.0;
        for ($anno = 1; $anno <= 7; $anno++) {
            $somma_importi += $this->{"getImportoAmmissibileAnno".$anno}();
        }
        
        return $somma_importi;
    }
	
	public function sommaImportiAvanzamento(): float {
       return $this->sommaImporti();
    }

}
