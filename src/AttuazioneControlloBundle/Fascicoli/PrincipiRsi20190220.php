<?php

namespace AttuazioneControlloBundle\Fascicoli;

use FascicoloBundle\Entity\IstanzaFascicolo;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PrincipiRsi20190220 {

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct($container) {
        $this->container = $container;
    }
    
    /**
     * @param IstanzaFascicolo $istanzaFascicolo
     * @return bool
     */
    public function isCompilazioneObbligatoria(IstanzaFascicolo $istanzaFascicolo) {
        try {
            /** @var bool $isCompilazioneObbligatoria */
            $isCompilazioneObbligatoria = (int) $this->container->get('fascicolo.istanza')->getOne($istanzaFascicolo, 'principi_rsi_20190220.indice_rsi.principi_rsi_sezione_0.principi_rsi_form_0.campo_0', true);
        } catch (\Exception $e) {
            return false;
        }
        
        // Il valore 1 implica la *non* compilazione del questionario.
        // Questo dipende dal modo in cui è stato creato il campo radio le cui scelte sono:
        // 0 => Sì, voglio compilare
        // 1 => No, *non* voglio compilare
        if ($isCompilazioneObbligatoria === 1) {
            return false;
        } else {
            return true;
        }
    }
}
