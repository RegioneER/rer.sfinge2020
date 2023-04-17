<?php

namespace SfingeBundle\Service\Importazione;

/**
 * Description of IGestoreImportazioneProcedura
 *
 * @author aturdo
 */
interface IGestoreImportazioneProcedura {
    public function getQueryRichieste();
    
    public function getIdProcedura2013();
    
    public function getIdProcedura2020();
    
    public function getParametriBollo();
    
    public function importaRichieste();
    
    public function dammiProgetto($pre_richiesta);
    
    public function dammiAziendaMandataria($progetto);
    
    public function dammiAltreAziendeProponenti($progetto);
    
    public function dammiUnitaLocale($progetto);
    
    public function creaPersona($em, $azienda_mandataria);
    
    public function creaAzienda($em, $azienda_mandataria);
    
    public function determinaClasseSoggetto($azienda_mandataria);
    
    public function creaIncarico($em, $persona, $soggetto);
    
    public function creaRichiesta($em, $azienda_mandataria, $pre_richiesta);
    
    public function creaProponenti($em, $progetto, $richiesta,$pre_richiesta, $soggettoMandatario, $altre_aziende_proponenti, $multiPianoCosto, $azienda_mandataria);
    
    public function creaVociPianoCosto($em, $progetto, $proponente, $richiesta, $azienda);
    
    public function creaRichiestaProtocollo($em, $richiesta, $pre_richiesta);
    
    public function creaIstruttoria($richiesta, $pre_richiesta);
    
    public function creaAtc($richiesta, $pre_richiesta);
	
	public function importaDocumentiProponenti($richiesta, $pre_richiesta,$proponente,$azienda);
	
	public function importaDocumentiPresentazione($richiesta, $pre_richiesta);
		
}
