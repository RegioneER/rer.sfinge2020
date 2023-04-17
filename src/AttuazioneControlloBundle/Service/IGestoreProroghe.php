<?php

namespace AttuazioneControlloBundle\Service;

use AttuazioneControlloBundle\Entity\Proroga;


interface IGestoreProroghe
{
    public function isProrogaAggiungibile($id_richiesta);

    public function aggiungiProroga($id_richiesta);

    public function dettaglioProroga($id_proroga);
    
    public function modificaDatiProroga($id_proroga);
    
    public function validaProroga($id_proroga);
    
    public function invalidaProroga($id_proroga);
    
    public function generaPdf($id_proroga, $facsimile = true, $download = true);
    
    public function inviaProroga($id_proroga);
    
    public function eliminaProroga($id_proroga);
    
    public function elencoDocumentiProroga(Proroga $proroga);
}
