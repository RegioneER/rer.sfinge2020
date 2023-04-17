<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 22/02/16
 * Time: 15:57
 */

namespace BaseBundle\ControlloAccessi;

use SfingeBundle\Entity\Utente;
use SoggettoBundle\Entity\IncaricoPersona;
use SoggettoBundle\Entity\Soggetto;
use SoggettoBundle\Entity\TipoIncarico;

class ControlloAccessoSoggetti extends  AControlloAccesso
{
    const INCARICHI_PERMESSI = [
        TipoIncarico::UTENTE_PRINCIPALE,
        // TipoIncarico::OPERATORE,
        // TipoIncarico::CONSULENTE,
    ];
    /**
     * @param $oggetto
     * @param Utente $utente
     * @param array $opzioni
     * @return boolean
     */
    public function verificaAccesso_ROLE_UTENTE($utente,$oggetto, $opzioni = array()) {
        /** @var Soggetto $soggetto */
        $soggetto = $oggetto->getSoggetto();
        $incarichi = $soggetto->getIncarichiPersone();
        $persona = $utente->getPersona();
        $incarichiPersona = $incarichi->filter(function(IncaricoPersona $incarico) use($persona){
            return $incarico->getIncaricato() == $persona;
        });
        $incarichiPersonaRuolo = $incarichiPersona->filter(function(IncaricoPersona $incarico){
            return \in_array($incarico->getTipoIncarico()->getCodice(), self::INCARICHI_PERMESSI);
        });
        
        return $incarichiPersonaRuolo->count() > 0;
    }

}