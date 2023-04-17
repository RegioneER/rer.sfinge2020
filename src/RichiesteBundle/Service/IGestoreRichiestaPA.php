<?php

namespace RichiesteBundle\Service;

interface IGestoreRichiestaPA
{
    /** Inizializza nuova richiesta
     * @return string
     */
    public function nuovaRichiesta();

    public function dettaglioRichiesta();

    public function getVociMenu();

    public function visualizzaSezione($nome_sezione, array $parametri);

    public function risultatoAzione($nome_azione);
}
