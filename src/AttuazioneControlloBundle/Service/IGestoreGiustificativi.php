<?php

namespace AttuazioneControlloBundle\Service;

interface IGestoreGiustificativi
{
    public function elencoGiustificativi($id_pagamento);

    public function aggiungiGiustificativo($id_pagamento);
    
    public function dettaglioGiustificativo($id_giustificativo);
    
    public function eliminaGiustificativo($id_giustificativo); 
}
