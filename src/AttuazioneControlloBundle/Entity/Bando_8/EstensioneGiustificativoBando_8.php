<?php

namespace AttuazioneControlloBundle\Entity\Bando_8;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Annotation as Sfinge;
use BaseBundle\Entity\EntityLoggabileCancellabile;

/**
 * @ORM\Entity()
 * @ORM\Table(name="estensioni_giustificativi_bando_8")
 */
class EstensioneGiustificativoBando_8 extends \AttuazioneControlloBundle\Entity\EstensioneGiustificativo {

	/**
	 * @ORM\Column(type="string", nullable=true, length=255)
	 */
	protected $nome;

	/**
	 * @ORM\Column(type="string", nullable=true, length=255)
	 */
	protected $cognome;

	/**
	 * @ORM\Column(type="string", nullable=true, length=255)
	 */
	protected $tipologia_contratto;

	/**
	 * @ORM\Column(type="string", nullable=true, length=255)
	 */
	protected $mansione;

	/**
	 * @ORM\Column(type="date", nullable=true)
	 */
	protected $data_assunzione;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $attivita;

	/**
	 * @ORM\Column(type="date", nullable=true)
	 */
	protected $data_inizio;

	/**
	 * @ORM\Column(type="date", nullable=true)
	 */
	protected $data_fine;

	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $numero_ore_ri;

	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $numero_ore_ss;
	
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $numero_ore_di;

	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $numero_ore_totale;
	

	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $costo_orario;

	protected $imputazione_ri;
	protected $imputazione_ss;
	protected $imputazione_di;
	protected $imputazione_totale;

	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\TipologiaFornitore")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $tipologia_fornitore;

	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\TipologiaSpesa")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $tipologia_spesa;

	/**
	 * @ORM\Column(type="date", nullable=true)
	 */
	protected $data_consegna_bene;

	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $totale_importi_quietanzati;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $numero_prima_fattura;

	/**
	 * @ORM\Column(type="date", nullable=true)
	 */
	protected $data_prima_fattura;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $numero_ultima_fattura;

	/**
	 * @ORM\Column(type="date", nullable=true)
	 */
	protected $data_ultima_fattura;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $numero_rate_rendicontate;

	/**
	 * @ORM\Column(type="date", nullable=true)
	 */
	protected $data_primo_bonifico;

	/**
	 * @ORM\Column(type="date", nullable=true)
	 */
	protected $data_ultimo_bonifico;

	// ammortamento

	/**
	 * @ORM\Column(type="date", nullable=true)
	 */
	protected $cespite_pronto_uso;

	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $importo_bene;

	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $coefficiente_ammortamento;

	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $giorni_utilizzo;

	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $percentuale_uso;

	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $quota_netta;

	/**
	 * @ORM\Column(type="string", nullable=true, length=255)
	 */
	protected $referente;
	
	// solo formtype
	protected $importo_contratto_complessivo;
	

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $alta_tecnologia;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $descrizione_attrezzatura;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $giustificazione_attrezzatura;

	/**
	 * @ORM\ManyToMany(targetEntity="RichiesteBundle\Entity\ObiettivoRealizzativo", inversedBy="estensione")
	 * @ORM\JoinTable(name="obiettivi_realizzativi_estenzioni")    
	 */
	protected $obiettivi_realizzativi;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $descrizione_contratto;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $titolo_brevetto;

	/**
	 * @ORM\Column(type="date", nullable=true)
	 */
	protected $data_inizio_contratto;

	/**
	 * @ORM\Column(type="string", nullable=true, length=255)
	 */
	protected $numero_domanda_brevetto;

	/**
	 * @ORM\Column(type="date", nullable=true)
	 */
	protected $data_domanda_brevetto;

	/**
	 * @ORM\Column(type="string", nullable=true, length=25)
	 */
	protected $stato_brevetto;

	/**
	 * @ORM\Column(type="string", nullable=true, length=25)
	 */
	protected $ambito_brevetto;
    
	/**
     * Utilizzato nelle consulenze specialistiche
     * 
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $giorni_persona;   
    
	/**
     * Utilizzato nelle consulenze specialistiche
     * 
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\ObiettivoRealizzativo")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $or;   
    
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $numero_ore_ri_or0;  
    
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $numero_ore_ri_or1;   
   
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $numero_ore_ri_or2;   

	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $numero_ore_ri_or3;   

	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $numero_ore_ri_or4;   
     
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $numero_ore_ss_or0;      
    
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $numero_ore_ss_or1;   
   
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $numero_ore_ss_or2;   

	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $numero_ore_ss_or3;   

	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $numero_ore_ss_or4; 
    
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $numero_ore_d_or0;      
    
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $numero_ore_d_or5; 
    
	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $prototipo_autonomo;  
    
	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $prototipo_terzi;  
    
	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $prototipo_non_autonomo;   
	
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $importo_contratto;
    
	public function getNome() {
		return $this->nome;
	}

	public function getCognome() {
		return $this->cognome;
	}

	public function getDataAssunzione() {
		return $this->data_assunzione;
	}

	public function getAttivita() {
		return $this->attivita;
	}

	public function getDataInizio() {
		return $this->data_inizio;
	}

	public function getDataFine() {
		return $this->data_fine;
	}

	public function getCostoOrario() {
		return $this->costo_orario;
	}

	public function setNome($nome) {
		$this->nome = $nome;
	}

	public function setCognome($cognome) {
		$this->cognome = $cognome;
	}

	public function setDataAssunzione($data_assunzione) {
		$this->data_assunzione = $data_assunzione;
	}

	public function setAttivita($attivita) {
		$this->attivita = $attivita;
	}

	public function setDataInizio($data_inizio) {
		$this->data_inizio = $data_inizio;
	}

	public function setDataFine($data_fine) {
		$this->data_fine = $data_fine;
	}

	public function setCostoOrario($costo_orario) {
		$this->costo_orario = $costo_orario;
	}

	public function getNumeroOreRi() {
		return $this->numero_ore_ri;
	}

	public function getNumeroOreSs() {
		return $this->numero_ore_ss;
	}

	public function setNumeroOreRi($numero_ore_ri) {
		$this->numero_ore_ri = $numero_ore_ri;
	}

	public function setNumeroOreSs($numero_ore_ss) {
		$this->numero_ore_ss = $numero_ore_ss;
	}

	public function getImputazioneRi() {
		return $this->imputazione_ri;
	}

	public function getImputazioneSs() {
		return $this->imputazione_ss;
	}

	public function setImputazioneRi($imputazione_ri) {
		$this->imputazione_ri = $imputazione_ri;
	}

	public function setImputazioneSs($imputazione_ss) {
		$this->imputazione_ss = $imputazione_ss;
	}

	public function getImputazioneTotale() {
		return $this->imputazione_totale;
	}

	public function setImputazioneTotale($imputazione_totale) {
		$this->imputazione_totale = $imputazione_totale;
	}

	public function getTipologiaFornitore() {
		return $this->tipologia_fornitore;
	}

	public function getCespiteProntoUso() {
		return $this->cespite_pronto_uso;
	}

	public function getImportoBene() {
		return $this->importo_bene;
	}

	public function getCoefficienteAmmortamento() {
		return $this->coefficiente_ammortamento;
	}

	public function getGiorniUtilizzo() {
		return $this->giorni_utilizzo;
	}

	public function getPercentualeUso() {
		return $this->percentuale_uso;
	}

	public function getQuotaNetta() {
		return $this->quota_netta;
	}

	public function setTipologiaFornitore($tipologia_fornitore) {
		$this->tipologia_fornitore = $tipologia_fornitore;
	}

	public function setCespiteProntoUso($cespite_pronto_uso) {
		$this->cespite_pronto_uso = $cespite_pronto_uso;
	}

	public function setImportoBene($importo_bene) {
		$this->importo_bene = $importo_bene;
	}

	public function setCoefficienteAmmortamento($coefficiente_ammortamento) {
		$this->coefficiente_ammortamento = $coefficiente_ammortamento;
	}

	public function setGiorniUtilizzo($giorni_utilizzo) {
		$this->giorni_utilizzo = $giorni_utilizzo;
	}

	public function setPercentualeUso($percentuale_uso) {
		$this->percentuale_uso = $percentuale_uso;
	}

	public function setQuotaNetta($quota_netta) {
		$this->quota_netta = $quota_netta;
	}

	public function getTipologiaSpesa() {
		return $this->tipologia_spesa;
	}

	public function setTipologiaSpesa($tipologia_spesa) {
		$this->tipologia_spesa = $tipologia_spesa;
	}

	public function getDataConsegnaBene() {
		return $this->data_consegna_bene;
	}

	public function getTotaleImportiQuietanzati() {
		return $this->totale_importi_quietanzati;
	}

	public function getNumeroPrimaFattura() {
		return $this->numero_prima_fattura;
	}

	public function getDataPrimaFattura() {
		return $this->data_prima_fattura;
	}

	public function getNumeroUltimaFattura() {
		return $this->numero_ultima_fattura;
	}

	public function getDataUltimaFattura() {
		return $this->data_ultima_fattura;
	}

	public function getNumeroRateRendicontate() {
		return $this->numero_rate_rendicontate;
	}

	public function getDataPrimoBonifico() {
		return $this->data_primo_bonifico;
	}

	public function getDataUltimoBonifico() {
		return $this->data_ultimo_bonifico;
	}

	public function setDataConsegnaBene($data_consegna_bene) {
		$this->data_consegna_bene = $data_consegna_bene;
	}

	public function setTotaleImportiQuietanzati($totale_importi_quietanzati) {
		$this->totale_importi_quietanzati = $totale_importi_quietanzati;
	}

	public function setNumeroPrimaFattura($numero_prima_fattura) {
		$this->numero_prima_fattura = $numero_prima_fattura;
	}

	public function setDataPrimaFattura($data_prima_fattura) {
		$this->data_prima_fattura = $data_prima_fattura;
	}

	public function setNumeroUltimaFattura($numero_ultima_fattura) {
		$this->numero_ultima_fattura = $numero_ultima_fattura;
	}

	public function setDataUltimaFattura($data_ultima_fattura) {
		$this->data_ultima_fattura = $data_ultima_fattura;
	}

	public function setNumeroRateRendicontate($numero_rate_rendicontate) {
		$this->numero_rate_rendicontate = $numero_rate_rendicontate;
	}

	public function setDataPrimoBonifico($data_primo_bonifico) {
		$this->data_primo_bonifico = $data_primo_bonifico;
	}

	public function setDataUltimoBonifico($data_ultimo_bonifico) {
		$this->data_ultimo_bonifico = $data_ultimo_bonifico;
	}

	public function getTipologiaContratto() {
		return $this->tipologia_contratto;
	}

	public function getMansione() {
		return $this->mansione;
	}

	public function setTipologiaContratto($tipologia_contratto) {
		$this->tipologia_contratto = $tipologia_contratto;
	}

	public function setMansione($mansione) {
		$this->mansione = $mansione;
	}

	public function getReferente() {
		return $this->referente;
	}

	public function getImportoContrattoComplessivo() {
		return $this->importo_contratto_complessivo;
	}

	public function setReferente($referente) {
		$this->referente = $referente;
	}

	public function setImportoContrattoComplessivo($importo_contratto_complessivo) {
		$this->importo_contratto_complessivo = $importo_contratto_complessivo;
	}

	public function getAltaTecnologia() {
		return $this->alta_tecnologia;
	}

	public function setAltaTecnologia($alta_tecnologia) {
		$this->alta_tecnologia = $alta_tecnologia;
	}

	public function getDescrizioneAttrezzatura() {
		return $this->descrizione_attrezzatura;
	}

	public function getGiustificazioneAttrezzatura() {
		return $this->giustificazione_attrezzatura;
	}

	public function getObiettiviRealizzativi() {
		return $this->obiettivi_realizzativi;
	}

	public function setDescrizioneAttrezzatura($descrizione_attrezzatura) {
		$this->descrizione_attrezzatura = $descrizione_attrezzatura;
	}

	public function setGiustificazioneAttrezzatura($giustificazione_attrezzatura) {
		$this->giustificazione_attrezzatura = $giustificazione_attrezzatura;
	}

	public function setObiettiviRealizzativi($obiettivi_realizzativi) {
		$this->obiettivi_realizzativi = $obiettivi_realizzativi;
	}

	public function getDescrizioneContratto() {
		return $this->descrizione_contratto;
	}

	public function getTitoloBrevetto() {
		return $this->titolo_brevetto;
	}

	public function getDataInizioContratto() {
		return $this->data_inizio_contratto;
	}

	public function setDescrizioneContratto($descrizione_contratto) {
		$this->descrizione_contratto = $descrizione_contratto;
	}

	public function setTitoloBrevetto($titolo_brevetto) {
		$this->titolo_brevetto = $titolo_brevetto;
	}

	public function setDataInizioContratto($data_inizio_contratto) {
		$this->data_inizio_contratto = $data_inizio_contratto;
	}

	public function getNumeroDomandaBrevetto() {
		return $this->numero_domanda_brevetto;
	}

	public function getDataDomandaBrevetto() {
		return $this->data_domanda_brevetto;
	}

	public function getStatoBrevetto() {
		return $this->stato_brevetto;
	}

	public function getAmbitoBrevetto() {
		return $this->ambito_brevetto;
	}

	public function setNumeroDomandaBrevetto($numero_domanda_brevetto) {
		$this->numero_domanda_brevetto = $numero_domanda_brevetto;
	}

	public function setDataDomandaBrevetto($data_domanda_brevetto) {
		$this->data_domanda_brevetto = $data_domanda_brevetto;
	}

	public function setStatoBrevetto($stato_brevetto) {
		$this->stato_brevetto = $stato_brevetto;
	}

	public function setAmbitoBrevetto($ambito_brevetto) {
		$this->ambito_brevetto = $ambito_brevetto;
	}

	public function getNumeroOreTotale() {
		return $this->numero_ore_totale;
	}

	public function setNumeroOreTotale($numero_ore_totale) {
		$this->numero_ore_totale = $numero_ore_totale;
	}
	
	public function getNumeroOreDi() {
		return $this->numero_ore_di;
	}

	public function getImputazioneDi() {
		return $this->imputazione_di;
	}

	public function setNumeroOreDi($numero_ore_di) {
		$this->numero_ore_di = $numero_ore_di;
	}

	public function setImputazioneDi($imputazione_di) {
		$this->imputazione_di = $imputazione_di;
	}
    
    public function getGiorniPersona() {
        return $this->giorni_persona;
    }

    public function setGiorniPersona($giorni_persona) {
        $this->giorni_persona = $giorni_persona;
    }
    
    public function getOr() {
        return $this->or;
    }

    public function setOr($or) {
        $this->or = $or;
    }
    
    public function getNumeroOreRiOr1() {
        return $this->numero_ore_ri_or1;
    }

    public function getNumeroOreRiOr2() {
        return $this->numero_ore_ri_or2;
    }

    public function getNumeroOreRiOr3() {
        return $this->numero_ore_ri_or3;
    }

    public function getNumeroOreRiOr4() {
        return $this->numero_ore_ri_or4;
    }

    public function getNumeroOreSsOr1() {
        return $this->numero_ore_ss_or1;
    }

    public function getNumeroOreSsOr2() {
        return $this->numero_ore_ss_or2;
    }

    public function getNumeroOreSsOr3() {
        return $this->numero_ore_ss_or3;
    }

    public function getNumeroOreSsOr4() {
        return $this->numero_ore_ss_or4;
    }

    public function setNumeroOreRiOr1($numero_ore_ri_or1) {
        $this->numero_ore_ri_or1 = $numero_ore_ri_or1;
    }

    public function setNumeroOreRiOr2($numero_ore_ri_or2) {
        $this->numero_ore_ri_or2 = $numero_ore_ri_or2;
    }

    public function setNumeroOreRiOr3($numero_ore_ri_or3) {
        $this->numero_ore_ri_or3 = $numero_ore_ri_or3;
    }

    public function setNumeroOreRiOr4($numero_ore_ri_or4) {
        $this->numero_ore_ri_or4 = $numero_ore_ri_or4;
    }

    public function setNumeroOreSsOr1($numero_ore_ss_or1) {
        $this->numero_ore_ss_or1 = $numero_ore_ss_or1;
    }

    public function setNumeroOreSsOr2($numero_ore_ss_or2) {
        $this->numero_ore_ss_or2 = $numero_ore_ss_or2;
    }

    public function setNumeroOreSsOr3($numero_ore_ss_or3) {
        $this->numero_ore_ss_or3 = $numero_ore_ss_or3;
    }

    public function setNumeroOreSsOr4($numero_ore_ss_or4) {
        $this->numero_ore_ss_or4 = $numero_ore_ss_or4;
    }
    
    public function getNumeroOreRiOr0() {
        return $this->numero_ore_ri_or0;
    }

    public function getNumeroOreSsOr0() {
        return $this->numero_ore_ss_or0;
    }

    public function getNumeroOreDOr0() {
        return $this->numero_ore_d_or0;
    }

    public function getNumeroOreDOr5() {
        return $this->numero_ore_d_or5;
    }

    public function setNumeroOreRiOr0($numero_ore_ri_or0) {
        $this->numero_ore_ri_or0 = $numero_ore_ri_or0;
    }

    public function setNumeroOreSsOr0($numero_ore_ss_or0) {
        $this->numero_ore_ss_or0 = $numero_ore_ss_or0;
    }

    public function setNumeroOreDOr0($numero_ore_d_or0) {
        $this->numero_ore_d_or0 = $numero_ore_d_or0;
    }

    public function setNumeroOreDOr5($numero_ore_d_or5) {
        $this->numero_ore_d_or5 = $numero_ore_d_or5;
    }
    
    public function getPrototipoAutonomo() {
        return $this->prototipo_autonomo;
    }

    public function getPrototipoTerzi() {
        return $this->prototipo_terzi;
    }

    public function getPrototipoNonAutonomo() {
        return $this->prototipo_non_autonomo;
    }

    public function setPrototipoAutonomo($prototipo_autonomo) {
        $this->prototipo_autonomo = $prototipo_autonomo;
    }

    public function setPrototipoTerzi($prototipo_terzi) {
        $this->prototipo_terzi = $prototipo_terzi;
    }

    public function setPrototipoNonAutonomo($prototipo_non_autonomo) {
        $this->prototipo_non_autonomo = $prototipo_non_autonomo;
    }
		
	public function getImportoImputazioneRI() {
		foreach ($this->giustificativo_pagamento->getVociPianoCosto() as $vpc) {
			$pc = substr($vpc->getVocePianoCosto()->getPianoCosto()->getCodice(), 0, 2);
			if (substr($pc, 0, 2) == "RI") {
				return $importo_7_ri = $vpc->getImporto();
			}
		}
	}

	public function getImportoImputazioneSP() {
		foreach ($this->giustificativo_pagamento->getVociPianoCosto() as $vpc) {
			$pc = substr($vpc->getVocePianoCosto()->getPianoCosto()->getCodice(), 0, 2);
			if (substr($pc, 0, 2) == "SP") {
				return $importo_7_ss = $vpc->getImporto();
			}
		}
	}
	
	public function getImportoImputazioneDI() {
		foreach ($this->giustificativo_pagamento->getVociPianoCosto() as $vpc) {
			$pc = substr($vpc->getVocePianoCosto()->getPianoCosto()->getCodice(), 0, 2);
			if (substr($pc, 0, 2) == "DI") {
				return $importo_7_ss = $vpc->getImporto();
			}
		}
	}

	public function getSommaImportoImputazioneRISP() {
		return $this->getImportoImputazioneSP() + $this->getImportoImputazioneRI() + $this->getImportoImputazioneDI();
	}
	
	public function getContratto(){
		return $this->giustificativo_pagamento->getContratto();
	}
	
	public function getFattura() {

		$data = $this->giustificativo_pagamento->getDataGiustificativo();
		$num = $this->giustificativo_pagamento->getNumeroGiustificativo();
		if(!is_null($data)) {
			$dataFormat = date_format($data,"d/m/Y");
		}
		else {
			$dataFormat = 'ND';
		}
		if(is_null($num)) {
			$num = 'ND';				
		}
		
		return $dataFormat . ' - ' . $num;
	}
    
    public function controlloCoerenzaOreRi() {
        $a = round($this->numero_ore_ri_or0+
        $this->numero_ore_ri_or1+
        $this->numero_ore_ri_or2+
        $this->numero_ore_ri_or3+
        $this->numero_ore_ri_or4, 2, PHP_ROUND_HALF_UP);
        
        $b = $this->numero_ore_ri;
        
        return $a == $b;         
    }
    
    public function controlloCoerenzaOreSs() {
        $a = round($this->numero_ore_ss_or0+
        $this->numero_ore_ss_or1+
        $this->numero_ore_ss_or2+
        $this->numero_ore_ss_or3+
        $this->numero_ore_ss_or4, 2, PHP_ROUND_HALF_UP);
        
        $b = $this->numero_ore_ss;
        
        return $a == $b;        
    }  
    
    public function controlloCoerenzaOreDi() {
        $a = round($this->numero_ore_d_or0+
        $this->numero_ore_d_or5, 2, PHP_ROUND_HALF_UP);
        
        $b = $this->numero_ore_di;
        
        return $a == $b;      
    }  
	
	public function getImportoContratto() {
		return $this->importo_contratto;
	}

	public function setImportoContratto($importo_contratto) {
		$this->importo_contratto = $importo_contratto;
	}

}
