<?php

namespace AttuazioneControlloBundle\Service\Istruttoria;

use AttuazioneControlloBundle\Entity\VariazioneRichiesta;
use Symfony\Component\HttpFoundation\Response;
use AttuazioneControlloBundle\Entity\VariazionePianoCosti;
use RichiesteBundle\Entity\Proponente;
use AttuazioneControlloBundle\Entity\DocumentoVariazione;

interface IGestoreVariazioni
{
    public function riepilogoVariazione(): Response;
    
    public function documentiVariazione(): Response;
    
    // public function pianoCostiVariazione( $annualita, Proponente $proponente = null): Response;

    // public function totaliPianoCosti(): Response;
    
    public function esitoFinale(): Response;

    public function eliminaDocumentoIstruttoriaVariazione(DocumentoVariazione $documento_variazione): Response;
}	
