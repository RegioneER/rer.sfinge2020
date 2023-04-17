<?php

namespace AttuazioneControlloBundle\Service\Controlli;

interface IGestoreControlli
{
    public function riepilogoControllo($controllo);
    
    public function documentiControllo($controllo);
    
    public function inizializzaControllo($controllo);
    
    public function valutaChecklist($valutazione_checklist, $extra = array());
    
    public function validaChecklist($valutazione_checklist);
    
    public function esitoFinale($controllo);
    
    public function isEsitoFinalePositivoEmettibile($controllo);
    
    public function isEsitoFinaleEmettibile($controllo);
    
    public function verbaleDeskControllo($controllo);
    
    public function generaVerbaleDeskControllo($controllo);

    public function generaVerbaleSopralluogoControllo($controllo);
        
}	
