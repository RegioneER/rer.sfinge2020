<?php

namespace GeoBundle\Model;

class ComuneIstat {
    public $codiceRegione;

    public $codiceUnitaTerritoriale;

    public $codiceProvincia;

    public $progressivoComune;

    public $codiceComuneAlfanumerico;

    public $denominazione;

    public $denominazioneItaliano;

    public $denominazioneAltraLingua;

    public $codiceRipartizioneGeografica;

    public $ripartizioneGeografica;

    public $denominazioneRegione;

    public $denominazioneUnitaTerritoriale;

    public $capoluogo;

    public $siglaAutomobilitstica;

    public $codiceComuneFormatoNumerico;

    public $codiceComune2016;

    public $codiceComune2009;

    public $codiceComune2005;

    public $codiceCastale;

    public $popolazione2011;

    public $NUTS1;

    public $NUTS2;

    public $NUTS3;

    public static function fromArray(array $tupla): self {
        $res = new ComuneIstat();
        
        list(
            $res->codiceRegione,
            $res->codiceUnitaTerritoriale,
            $res->codiceProvincia,
            $res->progressivoComune,
            $res->codiceComuneAlfanumerico,
            $res->denominazione,
            $res->denominazioneItaliano,
            $res->denominazioneAltraLingua,
            $res->codiceRipartizioneGeografica,
            $res->ripartizioneGeografica,
            $res->denominazioneRegione,
            $res->denominazioneUnitaTerritoriale,
            $res->capoluogo,
            $res->siglaAutomobilitstica,
            $res->codiceComuneFormatoNumerico,
            $res->codiceComune2016,
            $res->codiceComune2009,
            $res->codiceComune2005,
            $res->codiceCastale,
            $res->popolazione2011,
            $res->NUTS1,
            $res->NUTS2,
            $res->NUTS3
        ) = $tupla;

        return $res;
    }
}
