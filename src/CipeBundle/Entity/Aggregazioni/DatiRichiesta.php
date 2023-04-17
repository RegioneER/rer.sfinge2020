<?php

namespace CipeBundle\Entity\Aggregazioni;

use Doctrine\ORM\Mapping as ORM;


/**
 * Description of DatiRichiesta
 *
 * @author gaetanoborgosano
 * @ORM\Table(name="dati_richieste")
 * @ORM\Entity()
 */

class DatiRichiesta {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	function getId() { return $this->id; }
	function setId($id) { $this->id = $id; }

		
	/**
	 * @ORM\Column(type="integer")
	 */
	protected $idRichiesta = null;
	function getIdRichiesta() { return $this->idRichiesta; }
	function setIdRichiesta($idRichiesta) { $this->idRichiesta = $idRichiesta; }

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $idProgetto = null;
	function getIdProgetto() { return $this->idProgetto; }
	function setIdProgetto($idProgetto) { $this->idProgetto = intval($idProgetto); }
		
	/**
	 * @ORM\Column(type="string", length=4, nullable=true)
	 */
	protected $anno_decisione = null;
	function getAnno_decisione() { return $this->anno_decisione; }
	function setAnno_decisione($anno_decisione) { $this->anno_decisione = $anno_decisione; }
		
	/**
	 * @ORM\Column(type="string", length=1, nullable=true)
	 */	
	protected $cumulativo = null;
	function getCumulativo() { return $this->cumulativo; }
	function setCumulativo($cumulativo) { $this->cumulativo = $cumulativo; }
		
	/**
	 * @ORM\Column(type="string", length=60, nullable=true)
	 */	
	protected $codifica_locale = null;
	function getCodifica_locale() { return $this->codifica_locale; }
	function setCodifica_locale($codifica_locale) { $this->codifica_locale = $codifica_locale; }
		
	/**
	 * @ORM\Column(type="string", length=10, nullable=true)
	 */	
	protected $natura = null;
	function getNatura() { return $this->natura; }
	function setNatura($natura) { $this->natura = $natura; }
		
	/**
	 * @ORM\Column(type="string", length=10, nullable=true)
	 */	
	protected $tipologia = null;
	function getTipologia() { return $this->tipologia; }
	function setTipologia($tipologia) { $this->tipologia = $tipologia; }
		
	/**
	 * @ORM\Column(type="string", length=10, nullable=true)
	 */	
	protected $settore = null;
	function getSettore() { return $this->settore; }
	function setSettore($settore) { $this->settore = $settore; }
		
	/**
	 * @ORM\Column(type="string", length=10, nullable=true)
	 */
	protected $sottosettore = null;
	function getSottosettore() { return $this->sottosettore; }
	function setSottosettore($sottosettore) { $this->sottosettore = $sottosettore; }

	/**
	 * @ORM\Column(type="string", length=10, nullable=true)
	 */	
	protected $categoria = null;
	function getCategoria() { return $this->categoria; }
	function setCategoria($categoria) { $this->categoria = $categoria; }
		
	/**
	 * @ORM\Column(type="string", length=10, nullable=true)
	 */	
	protected $stato = null;
	function getStato() { return $this->stato; }
	function setStato($stato) { $this->stato = $stato; }
		
	/**
	 * @ORM\Column(type="string", length=10, nullable=true)
	 */	
	protected $regione = null;
	function getRegione() { return $this->regione; }
	function setRegione($regione) { $this->regione = $regione; }
		
	/**
	 * @ORM\Column(type="string", length=10, nullable=true)
	 */	
	protected $provincia = null;
	function getProvincia() { return $this->provincia; }
	function setProvincia($provincia) { $this->provincia = $provincia; }
		
	/**
	 * @ORM\Column(type="string", length=10, nullable=true)
	 */	
	protected $comune = null;
	function getComune() { return $this->comune; }
	function setComune($comune) { $this->comune = $comune; }
		
	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */	
	protected $benficiario = null;
	function getBenficiario() { return $this->benficiario; }
	function setBenficiario($benficiario) { $this->benficiario = $benficiario; }
	
	/**
	 * @ORM\Column(type="string", length=16, nullable=true)
	 */	
	protected $partita_iva = null;
	function getPartita_iva() { return $this->partita_iva; }
	function setPartita_iva($partita_iva) { $this->partita_iva = $partita_iva; }
		
	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */	
	protected $struttura = null;
	function getStruttura() { return $this->struttura; }
	function setStruttura($struttura) { $this->struttura = $struttura; }
		
	/**
	 * @ORM\Column(type="string", length=10, nullable=true)
	 */	
	protected $tipo_ind_area_rifer = null;
	function getTipo_ind_area_rifer() { return $this->tipo_ind_area_rifer; }
	function setTipo_ind_area_rifer($tipo_ind_area_rifer) { $this->tipo_ind_area_rifer = $tipo_ind_area_rifer; }

	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */	
	protected $ind_area_rifer = null;
	function getInd_area_rifer() { return $this->ind_area_rifer; }
	function setInd_area_rifer($ind_area_rifer) { $this->ind_area_rifer = $ind_area_rifer; }

	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */	
	protected $descr_intervento = null;
	function getDescr_intervento() { return $this->descr_intervento; }
	function setDescr_intervento($descr_intervento) { $this->descr_intervento = $descr_intervento; }
		
	/**
	 * @ORM\Column(type="string", length=10, nullable=true)
	 */	
	protected $strum_progr = null;
	function getStrum_progr() { return $this->strum_progr; }
	function setStrum_progr($strum_progr) { $this->strum_progr = $strum_progr; }
		
	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */	
	protected $descr_strum_progr = null;
	function getDescr_strum_progr() { return $this->descr_strum_progr; }
	function setDescr_strum_progr($descr_strum_progr) { $this->descr_strum_progr = $descr_strum_progr; }
	
	/**
	 * @ORM\Column(type="text", length=4000, nullable=true)
	 */	
	protected $altre_informazioni = null;
	function getAltre_informazioni() { return $this->altre_informazioni; }
	function setAltre_informazioni($altre_informazioni) { $this->altre_informazioni = $altre_informazioni; }
		
	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */	
	protected $denominazione_impresa_stabilimento = null;
	function getDenominazione_impresa_stabilimento() { return $this->denominazione_impresa_stabilimento; }
	function setDenominazione_impresa_stabilimento($denominazione_impresa_stabilimento) { $this->denominazione_impresa_stabilimento = $denominazione_impresa_stabilimento; }
	
	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */	
	protected $denominazione_impresa_stabilimento_prec = null;
	function getDenominazione_impresa_stabilimento_prec() { return $this->denominazione_impresa_stabilimento_prec; }
	function setDenominazione_impresa_stabilimento_prec($denominazione_impresa_stabilimento_prec) { $this->denominazione_impresa_stabilimento_prec = $denominazione_impresa_stabilimento_prec; }
	
	/**
	 * @ORM\Column(type="string", length=10, nullable=true)
	 */	
	protected $ateco_sezione = null;
	function getAteco_sezione() { return $this->ateco_sezione; }
	function setAteco_sezione($ateco_sezione) { $this->ateco_sezione = $ateco_sezione; }

	/**
	 * @ORM\Column(type="string", length=10, nullable=true)
	 */	
	protected $ateco_divisione = null;
	function getAteco_divisione() { return $this->ateco_divisione; }
	function setAteco_divisione($ateco_divisione) { $this->ateco_divisione = $ateco_divisione; }

	/**
	 * @ORM\Column(type="string", length=10, nullable=true)
	 */	
	protected $ateco_gruppo = null;
	function getAteco_gruppo() { return $this->ateco_gruppo; }
	function setAteco_gruppo($ateco_gruppo) { $this->ateco_gruppo = $ateco_gruppo; }
	
	/**
	 * @ORM\Column(type="string", length=10, nullable=true)
	 */	
	protected $ateco_classe = null;
	function getAteco_classe() { return $this->ateco_classe; }
	function setAteco_classe($ateco_classe) { $this->ateco_classe = $ateco_classe; }
	
	/**
	 * @ORM\Column(type="string", length=10, nullable=true)
	 */	
	protected $ateco_categoria = null;
	function getAteco_categoria() { return $this->ateco_categoria; }
	function setAteco_categoria($ateco_categoria) { $this->ateco_categoria = $ateco_categoria; }
	
	/**
	 * @ORM\Column(type="string", length=10, nullable=true)
	 */	
	protected $ateco_sottocategoria = null;
	function setAteco_sottocategoria($ateco_sottocategoria) { $this->ateco_sottocategoria = $ateco_sottocategoria; }
	function getAteco_sottocategoria() { return $this->ateco_sottocategoria; }
	
	
	/**
	 * @ORM\Column(type="decimal", precision=15, scale=3, nullable=true)
	 */	
	protected $costo = null;
	function getCosto() { return $this->costo; }
 	function setCosto($costo) { $this->costo = $costo; }
	
	/**
	 * @ORM\Column(type="decimal", precision=15, scale=3, nullable=true)
	 */	
	protected $finanziamento = null;
	function getFinanziamento() { return $this->finanziamento; }
	function setFinanziamento($finanziamento) { $this->finanziamento = $finanziamento; }

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */	
	protected $codici_tipologia_cop_finanz = "[]";
	function getCodici_tipologia_cop_finanz() { return json_decode($this->codici_tipologia_cop_finanz); }
	function setCodici_tipologia_cop_finanz($codici_tipologia_cop_finanz) { $this->codici_tipologia_cop_finanz = json_encode($codici_tipologia_cop_finanz); }
		
	/**
	 * @ORM\Column(type="string", length=1, nullable=true)
	 */	
	protected $sponsorizzazione = null;
	function getSponsorizzazione() { return $this->sponsorizzazione; }
	function setSponsorizzazione($sponsorizzazione) { $this->sponsorizzazione = $sponsorizzazione; }
		
	/**
	 * @ORM\Column(type="string", length=1, nullable=true)
	 */	
	protected $finanza_progetto = null;
	function getFinanza_progetto() { return $this->finanza_progetto; }
	function setFinanza_progetto($finanza_progetto) { $this->finanza_progetto = $finanza_progetto; }

	
	protected $IstruttoriaRichiesta_id;
	function getIstruttoriaRichiesta_id() { return $this->IstruttoriaRichiesta_id; }
	function setIstruttoriaRichiesta_id($IstruttoriaRichiesta_id) { $this->IstruttoriaRichiesta_id = $IstruttoriaRichiesta_id; }

	/**
 	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $nome_str_infrastr;
	function getNome_str_infrastr() { return $this->nome_str_infrastr; }
	function setNome_str_infrastr($nome_str_infrastr) { $this->nome_str_infrastr = $nome_str_infrastr; }

	/**
 	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	protected $bene;
	function getBene() { return $this->bene; }
	function setBene($bene) { $this->bene = $bene; }

	/**
  	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	protected $str_infrastr_unica;
	function getStr_infrastr_unica() { return $this->str_infrastr_unica; }
	function setStr_infrastr_unica($str_infrastr_unica) { $this->str_infrastr_unica = $str_infrastr_unica; }

	/**
  	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	protected $ragione_sociale;
	function getRagione_sociale() { return $this->ragione_sociale; }
	function setRagione_sociale($ragione_sociale) { $this->ragione_sociale = $ragione_sociale; }
	
	/**
  	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	protected $ragione_sociale_prec;
	function getRagione_sociale_prec() { return $this->ragione_sociale_prec; }
	function setRagione_sociale_prec($ragione_sociale_prec) { $this->ragione_sociale_prec = $ragione_sociale_prec; }
	
	/**
  	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	protected $finalita;
	function getFinalita() { return $this->finalita; }
	function setFinalita($finalita) { $this->finalita = $finalita; }

	/**
  	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	protected $denom_progetto;
	function getDenom_progetto() { return $this->denom_progetto; }
	function setDenom_progetto($denom_progetto) { $this->denom_progetto = $denom_progetto; }

		
	/**
  	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	protected $denom_ente_corso;
	function getDenom_ente_corso() { return $this->denom_ente_corso; }
	function setDenom_ente_corso($denom_ente_corso) { $this->denom_ente_corso = $denom_ente_corso; }

	/**
  	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	protected $obiett_corso;
	function getObiett_corso() { return $this->obiett_corso; }
	function setObiett_corso($obiett_corso) { $this->obiett_corso = $obiett_corso; }
		
	/**
  	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	protected $mod_intervento_frequenza;
	function getMod_intervento_frequenza() { return $this->mod_intervento_frequenza; }
	function setMod_intervento_frequenza($mod_intervento_frequenza) { $this->mod_intervento_frequenza = $mod_intervento_frequenza; }

	/**
  	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	protected $servizio;
	function getServizio() { return $this->servizio; }
	function setServizio($servizio) { $this->servizio = $servizio; }

	/**
  	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $denominazione_progetto;
	function getDenominazione_progetto() { return $this->denominazione_progetto; }
	function setDenominazione_progetto($denominazione_progetto) { $this->denominazione_progetto = $denominazione_progetto; }
	
	/**
  	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $ente;
	function getEnte() { return $this->ente; }
	function setEnte($ente) { $this->ente = $ente; }


}
