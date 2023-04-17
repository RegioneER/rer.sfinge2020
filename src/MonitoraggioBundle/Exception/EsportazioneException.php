<?php

namespace MonitoraggioBundle\Exception;

use RichiesteBundle\Entity\Richiesta;

/**
 * {@inheritdoc}
 */
class EsportazioneException extends \Exception {
    public static function richiestaNonEsportabile(string $nomeStruttura, Richiesta $richiesta): self {
        return new EsportazioneException("Esportazione struttura $nomeStruttura " . self::suffissoMessaggioPerRichiesta($richiesta) . " non necessaria");
    }

    protected static function suffissoMessaggioPerRichiesta(Richiesta $richiesta) {
        $protocollo = $richiesta->getProtocollo();
        return "per il progetto $protocollo";
    }

    public static function richiestaSenzaTipoLocalizzazione(Richiesta $richiesta): self {
        return new EsportazioneException("Tipo localizzazione non definito " . self::suffissoMessaggioPerRichiesta($richiesta));
    }

    public static function richiestaSenzaGruppoVulnerabile(Richiesta $richiesta): self {
        return new EsportazioneException("Gruppo vulnerabile non definito " . self::suffissoMessaggioPerRichiesta($richiesta));
    }

    public static function richiestaSenzaProgramma(Richiesta $richiesta): self {
        return new EsportazioneException("Nessun programma associato " . self::suffissoMessaggioPerRichiesta($richiesta));
    }

    public static function richiestaSenzaStrumentoAttuativo(Richiesta $richiesta): self {
        return new EsportazioneException("Nessuno strumento attuativo " . self::suffissoMessaggioPerRichiesta($richiesta));
    }

    public static function richiestaSenzaLocalizzazioneGeografica(Richiesta $richiesta): self {
        return new EsportazioneException("Nessuna localizzazione geografica " . self::suffissoMessaggioPerRichiesta($richiesta));
    }

    public static function richiestaSenzaPianoCosti(Richiesta $richiesta): self {
        return new EsportazioneException("Nessuna voce piano dei costi " . self::suffissoMessaggioPerRichiesta($richiesta));
    }

    public static function proceduraAttivazioneNonDefinita() {
        return new EsportazioneException("Procedura di attivazione non definita");
    }
}
