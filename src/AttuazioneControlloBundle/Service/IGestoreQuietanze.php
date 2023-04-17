<?php

namespace AttuazioneControlloBundle\Service;

interface IGestoreQuietanze
{

    public function aggiungiQuietanza($id_giustificativo);
    
    public function modificaQuietanza($id_quietanza);
    
    public function eliminaQuietanza($id_quietanza);
    
    public function eliminaDocumentoQuietanza($id_documento_quietanza, $id_quietanza);
}
