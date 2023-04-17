<?php

namespace AttuazioneControlloBundle\Service;

interface IGestoreVociPianoCosto
{
    public function getVociPianoCosto($proponente, $pagamento);

    public function aggiungiVocePianoCosto($id_giustificativo, $options = array());
	
	public function modificaVocePianoCosto($id_voce_piano, $options = array());
    
    public function eliminaVocePianoCosto($id_voce_costo_giustificativo);	
}
