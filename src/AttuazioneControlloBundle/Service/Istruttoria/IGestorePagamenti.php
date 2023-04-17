<?php

namespace AttuazioneControlloBundle\Service\Istruttoria;

use AttuazioneControlloBundle\Entity\Istruttoria\AllegatoComunicazionePagamento;
use AttuazioneControlloBundle\Entity\Istruttoria\AllegatoRichiestaChiarimento;
use AttuazioneControlloBundle\Entity\Pagamento;
use Symfony\Component\HttpFoundation\Response;


interface IGestorePagamenti
{
    public function riepilogoPagamento($pagamento);
    
    public function documentiPagamento($pagamento);
    
    public function inizializzaIstruttoriaPagamento($pagamento);
    
    public function valutaChecklist($valutazione_checklist, $extra = array());
    
    public function validaChecklist($valutazione_checklist);

    public function gestioneIndicatoriOutput(Pagamento $pagamento): Response;

    public function eliminaAllegatoRichiestaChiarimento(AllegatoRichiestaChiarimento $allegato): void;
    
    public function eliminaAllegatoComunicazionePagamento(AllegatoComunicazionePagamento $allegato): void;
}
