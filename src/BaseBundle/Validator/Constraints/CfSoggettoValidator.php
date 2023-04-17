<?php

namespace BaseBundle\Validator\Constraints;

use AnagraficheBundle\Entity\Persona;
use GeoBundle\Entity\GeoComune;
use GeoBundle\Entity\GeoStato;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CfSoggettoValidator extends ConstraintValidator
{
    /**
     * Funzioni per il calcolo del codice fiscale e dei relativi codici per omocodi
     *
     * Esempio d'uso:
     * $cf = new controlli();
     *
     * // per calcolare un codice fiscale
     * $codicefiscalecalcolato = $cf->calcola(
     * $nome,
     * $cognome,
     * date("d/m/Y",$timestamp_data_nascita),
     * $sesso,
     * $comune_nascita // Codice es. L113
     * );
     *
     * // per calcolare tutti gli omocodi di un dato codice fiscale
     * $array_omocodi = $cf->calcolaTuttiGliOmocodi($codicefiscalecalcolato);
     *
     * // banale verifica se un codice fiscale dato è valido
     * if( (in_array(strtoupper($codice_fiscale),$array_omocodi,true) === FALSE) {
     * return FALSE;
     * } else {
     * return TRUE;
     * }
     */
    const ERR_GENERIC = 'Errore di calcolo del codice fiscale.';

    /**
     * Array delle consonanti
     */
    protected static $_consonanti = array(
        'B',
        'C',
        'D',
        'F',
        'G',
        'H',
        'J',
        'K',
        'L',
        'M',
        'N',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'V',
        'W',
        'X',
        'Y',
        'Z'
    );

    /**
     * Array delle vocali
     */
    protected static $_vocali = array(
        'A',
        'E',
        'I',
        'O',
        'U'
    );

    /**
     * Array per il calcolo della lettera del mese
     * Al numero del mese corrisponde una lettera
     */
    protected static $_mesi = array(
        1  => 'A',
        2  => 'B',
        3  => 'C',
        4  => 'D',
        5  => 'E',
        6  => 'H',
        7  => 'L',
        8  => 'M',
        9  => 'P',
        10 => 'R',
        11 => 'S',
        12 => 'T'
    );


    protected static $_pari = array(
        '0' => 0,
        '1' => 1,
        '2' => 2,
        '3' => 3,
        '4' => 4,
        '5' => 5,
        '6' => 6,
        '7' => 7,
        '8' => 8,
        '9' => 9,
        'A' => 0,
        'B' => 1,
        'C' => 2,
        'D' => 3,
        'E' => 4,
        'F' => 5,
        'G' => 6,
        'H' => 7,
        'I' => 8,
        'J' => 9,
        'K' => 10,
        'L' => 11,
        'M' => 12,
        'N' => 13,
        'O' => 14,
        'P' => 15,
        'Q' => 16,
        'R' => 17,
        'S' => 18,
        'T' => 19,
        'U' => 20,
        'V' => 21,
        'W' => 22,
        'X' => 23,
        'Y' => 24,
        'Z' => 25
    );

    protected static $_dispari = array(
        '0' => 1,
        '1' => 0,
        '2' => 5,
        '3' => 7,
        '4' => 9,
        '5' => 13,
        '6' => 15,
        '7' => 17,
        '8' => 19,
        '9' => 21,
        'A' => 1,
        'B' => 0,
        'C' => 5,
        'D' => 7,
        'E' => 9,
        'F' => 13,
        'G' => 15,
        'H' => 17,
        'I' => 19,
        'J' => 21,
        'K' => 2,
        'L' => 4,
        'M' => 18,
        'N' => 20,
        'O' => 11,
        'P' => 3,
        'Q' => 6,
        'R' => 8,
        'S' => 12,
        'T' => 14,
        'U' => 16,
        'V' => 10,
        'W' => 22,
        'X' => 25,
        'Y' => 24,
        'Z' => 23
    );

    protected static $_controllo = array(
        '0'  => 'A',
        '1'  => 'B',
        '2'  => 'C',
        '3'  => 'D',
        '4'  => 'E',
        '5'  => 'F',
        '6'  => 'G',
        '7'  => 'H',
        '8'  => 'I',
        '9'  => 'J',
        '10' => 'K',
        '11' => 'L',
        '12' => 'M',
        '13' => 'N',
        '14' => 'O',
        '15' => 'P',
        '16' => 'Q',
        '17' => 'R',
        '18' => 'S',
        '19' => 'T',
        '20' => 'U',
        '21' => 'V',
        '22' => 'W',
        '23' => 'X',
        '24' => 'Y',
        '25' => 'Z'
    );

    protected static $_omocodia = array(
        '0' => 'L',
        '1' => 'M',
        '2' => 'N',
        '3' => 'P',
        '4' => 'Q',
        '5' => 'R',
        '6' => 'S',
        '7' => 'T',
        '8' => 'U',
        '9' => 'V'
    );

    /**
     * Stringa di errore
     */
    protected static $_error = null;

    /**
     * Separatore per la data di nascita
     */
    protected static $_dateSeparator = '/';

    /*
     * Trasforma la stringa passata in un array di lettere
     * e lo incrocia con un ulteriore array
     */
    protected static function _getLettere($string, array $haystack)
    {
        $letters = array();
        foreach (str_split($string) as $needle) {
            if (in_array($needle, $haystack)) {
                $letters[] = $needle;
            }
        }
        return $letters;
    }

    /*
     * Ritorna un array con le vocali di una data stringa
     */
    protected static function _getVocali($string)
    {
        return self::_getLettere($string, self::$_vocali);
    }

    /*
     * Ritorna un array con le consonanti di una data stringa
     */
    protected static function _getConsonanti($string)
    {
        return self::_getLettere($string, self::$_consonanti);
    }

    /*
     * Pulisce la stringa filtrando tutti i caratteri che
     * non sono lettere. Lo switch $toupper se impostato a TRUE
     * converte la stringa risultante in MAIUSCOLO.
     */
    protected static function _sanitize($string, $toupper = true)
    {
        $result = preg_replace('/[^A-Za-z]*/', '', $string);
        return ($toupper) ? strtoupper($result) : $result;
    }

    /*
     * Se la stringa passata a funzione e' costituita
     * da meno di 3 caratteri, rimpiazza le lettere
     * mancanti con la lettera X.
     */
    protected static function _addMissingX($string)
    {
        $code = $string;
        while (strlen($code) < 3) {
            $code .= 'X';
        }
        return $code;
    }

    /*
     * Ottiene il codice identificativo del nome
     */
    protected static function _calcolaNome($string)
    {
        $nome = self::_sanitize($string);
        $code = null;

        // Se il nome inserito e' piu' corto di 3 lettere
        // si aggiungono tante X quanti sono i caratteri
        // mancanti.
        if (strlen($nome) < 3) {
            return self::_addMissingX($nome);
        }

        $nome_cons = self::_getConsonanti($nome);

        // Se le consonanti contenute nel nome sono minori
        // o uguali a 3 vengono considerate nell'ordine in cui
        // compaiono.
        if (count($nome_cons) <= 3) {
            $code = implode('', $nome_cons);
        } else {
            // Se invece abbiamo almeno 4 consonanti, prendiamo
            // la prima, la terza e la quarta.
            for ($i = 0; $i < 4; $i++) {
                if ($i == 1) {
                    continue;
                }

                if (!empty($nome_cons[$i])) {
                    $code .= $nome_cons[$i];
                }
            }
        }

        // Se compaiono meno di 3 consonanti nel nome, si
        // utilizzano le vocali, nell'ordine in cui compaiono
        // nel nome.
        if (strlen($code) < 3) {
            $nome_voc = self::_getVocali($nome);
            while (strlen($code) < 3) {
                $code .= array_shift($nome_voc);
            }
        }

        return $code;
    }

    protected static function _calcolaCognome($string)
    {
        $cognome = self::_sanitize($string);
        $code    = null;

        // Se il cognome inserito e' piu' corto di 3 lettere
        // si aggiungono tante X quanti sono i caratteri
        // mancanti.
        if (strlen($cognome) < 3) {
            return self::_addMissingX($cognome);
        }

        $cognome_cons = self::_getConsonanti($cognome);

        // Per il calcolo del cognome si prendono le prime
        // 3 consonanti.
        for ($i = 0; $i < 3; $i++) {
            if (array_key_exists($i, $cognome_cons)) {
                $code .= $cognome_cons[$i];
            }
        }

        // Se le consonanti non bastano, vengono prese
        // le vocali nell'ordine in cui compaiono.
        if (strlen($code) < 3) {
            $cognome_voc = self::_getVocali($cognome);
            while (strlen($code) < 3) {
                $code .= array_shift($cognome_voc);
            }
        }

        return $code;
    }

    /*
     * Imposta il separatore di data ( default: / )
     */
    public static function setDateSeparator($char)
    {
        self::$_dateSeparator = $char;
        //return self;
    }

    /*
     * Ritorna la parte di codice fiscale corrispondente
     * alla data di nascita del soggetto (Forma: AAMGG)
     */
    protected static function _calcolaDataNascita($data, $sesso)
    {
        $dn = explode(self::$_dateSeparator, $data);

        $giorno = (int)@$dn[0];
        $mese   = (int)@$dn[1];
        $anno   = (int)@$dn[2];

        // Le ultime due cifre dell'anno di nascita
        $aa = substr($anno, -2);

        // La lettera corrispondente al mese di nascita
        $mm = self::$_mesi[$mese];

        // Il giorno viene calcolato a seconda del sesso
        // del soggetto di cui si calcola il codice:
        // se e' Maschio si mette il giorno reale, se e'
        // Femmina viene aggiungo 40 a questo numero.
        $gg = (strtoupper($sesso) == 'M') ? $giorno : ($giorno + 40);

        // Bug #1: Thanks to Luca
        if (strlen($gg) < 2) {
            $gg = '0' . $gg;
        }


        return $aa . $mm . $gg;
    }

    /*
     * Ritorna il codice catastale del comune richiesto
     */
    protected static function _calcolaCatastale($comune)
    {
        return $comune;
    }

    /*
     * Ritorna la cifra di controllo sulla base dei
     * 15 caratteri del codice fiscale calcolati.
     */
    protected static function _calcolaCifraControllo($codice)
    {
        $code = str_split(strtoupper($codice));
        $sum  = 0;

        for ($i = 1; $i <= count($code); $i++) {
            $cifra = $code[$i - 1];
            $sum += ($i % 2) ? self::$_dispari[$cifra] : self::$_pari[$cifra];
        }

        $sum %= 26;

        return self::$_controllo[$sum];
    }

    /*
     * Imposta il messaggio di errore
     */
    protected static function _setError($string)
    {
        self::$_error = $string;
    }

    /*
     * Verifica la presenza di un errore.
     * Ritorna TRUE se presente, FALSE altrimenti.
     */
    public static function hasError()
    {
        return !is_null(self::$_error);
    }

    /*
     * Ritorna la stringa di errore
     */
    public static function getError()
    {
        return self::$_error;
    }

    /*
     * Ritorna il codice fiscale utilizzando i parametri
     * passati a funzione. Se si verifica
     */
    public static function calcolaCodiceFiscale($nome, $cognome, $data, $sesso, $comune)
    {
        $codice = self::_calcolaCognome($cognome) . self::_calcolaNome($nome) . self::_calcolaDataNascita($data, $sesso) . self::_calcolaCatastale($comune);

        if (self::hasError()) {
            return false;
        }

        $codice .= self::_calcolaCifraControllo($codice);

        if (strlen($codice) != 16) {
            self::_setError(self::ERR_GENERIC);
            return false;
        }

        return $codice;
    }

    /*
     * Calcolo degli omocodi
     */
    public static function calcolaTuttiGliOmocodi($codicefiscalenormale, &$array_codici = null)
    {
        if (!$array_codici) {
            $array_codici = array();
            array_push($array_codici, $codicefiscalenormale);
        }

        $codice_senza_controllo = substr($codicefiscalenormale, 0, 15);
        for ($pos = 15; $pos > 0; $pos--) {
            $codice_appoggio = $codice_senza_controllo;
            $test            = ((strlen($codice_senza_controllo) - 1) >= $pos) ? $codice_senza_controllo[$pos] : '';
            if (is_numeric($test)) {
                $codice_appoggio[$pos] = self::$_omocodia[$test];
                $nuovo_cf              = $codice_appoggio . self::_calcolaCifraControllo($codice_appoggio);
                if (!in_array($nuovo_cf, $array_codici)) {
                    array_push($array_codici, $nuovo_cf);
                    self::calcolaTuttiGliOmocodi($nuovo_cf, $array_codici);
                }
            }
        }
        return $array_codici;
    }

    /**
     * @param null $codice_fiscale
     * @param bool $obbligatorio
     * @param null $nome
     * @param null $cognome
     * @param null $data_nascita
     * @param null $sesso
     * @param null $comune_nascita
     *
     * @return bool
     */
    public function verificaCodiceFiscale($codice_fiscale = null, $obbligatorio = true, $nome = null, $cognome = null, $data_nascita = null, $sesso = null, $comune_nascita = null)
    {
        // rimuovo gli spazi vuoti
        $codice_fiscale = strtoupper(trim($codice_fiscale));
        $nome           = trim($nome);
        $cognome        = trim($cognome);
        $data_nascita   = trim($data_nascita);
        $sesso          = trim($sesso);
        $comune_nascita = trim($comune_nascita);

        // se il dato non è obbligatorio ed è vuoto allora ritorno true
        if (empty($codice_fiscale) && filter_var($obbligatorio, FILTER_VALIDATE_BOOLEAN) == false) {
            return true;
        }

        // verifico se il codice e' nullo, la lunghezza del codice, la presenza di caratteri non consentiti
        if (empty($codice_fiscale) || strlen($codice_fiscale) < 16 || ctype_alnum($codice_fiscale) === false) {
            return false;
        }

        // verifico se i dati che lo compongono sono tutti stati inseriti
        if (empty($nome) || empty($cognome) || empty($data_nascita) || empty($sesso) || empty($comune_nascita)) {
            return false;
        }

        $codice_calcolato = self::calcolaCodiceFiscale($nome, $cognome, $data_nascita, $sesso, $comune_nascita);

        if ($codice_fiscale === $codice_calcolato) {
            return true;
        } else {
            $array_omocodi = self::calcolaTuttiGliOmocodi($codice_calcolato);
            if (in_array(strtoupper($codice_fiscale), $array_omocodi, true)) {
                return true;
            }
        }

        // TODO: implementare una gestione per le nazioni che hanno cambiato codifica.
        // Per ora gestito l'uzbekistan
        if($comune_nascita === 'Z259') {
            $comune_nascita = 'Z143';

            $codice_calcolato = self::calcolaCodiceFiscale($nome, $cognome, $data_nascita, $sesso, $comune_nascita);

            if ($codice_fiscale === $codice_calcolato) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param null $codiceFiscale
     * @param bool $obbligatorio
     *
     * @return bool
     */
    public function verificaFormaleCodiceFiscale($codiceFiscale = null, $obbligatorio = false)
    {
        // rimuovo gli spazi vuoti
        $codiceFiscale = strtoupper(trim($codiceFiscale));

        // se il dato non è obbligatorio ed è vuoto allora ritorno true
        if (empty($codiceFiscale) && filter_var($obbligatorio, FILTER_VALIDATE_BOOLEAN) == false) {
            return true;
        }

        // verifico se il codice e' nullo, la lunghezza del codice, la presenza di caratteri non consentiti
        if (empty($codiceFiscale) || strlen($codiceFiscale) < 16 || ctype_alnum($codiceFiscale) === false) {
            return false;
        }

        $s = 0;
        for( $i = 1; $i <= 13; $i += 2 ){
            $c = $codiceFiscale[$i];
            if( strcmp($c, "0") >= 0 and strcmp($c, "9") <= 0 )
                $s += ord($c) - ord('0');
            else
                $s += ord($c) - ord('A');
        }
        for( $i = 0; $i <= 14; $i += 2 ){
            $c = $codiceFiscale[$i];
            switch( $c ){
                case '0':  $s += 1;  break;
                case '1':  $s += 0;  break;
                case '2':  $s += 5;  break;
                case '3':  $s += 7;  break;
                case '4':  $s += 9;  break;
                case '5':  $s += 13;  break;
                case '6':  $s += 15;  break;
                case '7':  $s += 17;  break;
                case '8':  $s += 19;  break;
                case '9':  $s += 21;  break;
                case 'A':  $s += 1;  break;
                case 'B':  $s += 0;  break;
                case 'C':  $s += 5;  break;
                case 'D':  $s += 7;  break;
                case 'E':  $s += 9;  break;
                case 'F':  $s += 13;  break;
                case 'G':  $s += 15;  break;
                case 'H':  $s += 17;  break;
                case 'I':  $s += 19;  break;
                case 'J':  $s += 21;  break;
                case 'K':  $s += 2;  break;
                case 'L':  $s += 4;  break;
                case 'M':  $s += 18;  break;
                case 'N':  $s += 20;  break;
                case 'O':  $s += 11;  break;
                case 'P':  $s += 3;  break;
                case 'Q':  $s += 6;  break;
                case 'R':  $s += 8;  break;
                case 'S':  $s += 12;  break;
                case 'T':  $s += 14;  break;
                case 'U':  $s += 16;  break;
                case 'V':  $s += 10;  break;
                case 'W':  $s += 22;  break;
                case 'X':  $s += 25;  break;
                case 'Y':  $s += 24;  break;
                case 'Z':  $s += 23;  break;
            }
        }
        if( chr($s%26 + ord('A')) != $codiceFiscale[15] ) {

            $array_omocodi = self::calcolaTuttiGliOmocodi(substr($codiceFiscale, 0, 15));
            if (in_array(strtoupper($codiceFiscale), $array_omocodi, true)) {
                return true;
            }

            return false;
        }

        return true;
    }

    /**
     * @param null $partita_iva
     * @param bool $obbligatorio
     *
     * @return bool
     */
    public function verificaPartitaIva($partita_iva = null, $obbligatorio = false)
    {
        return true;
        $partita_iva = strtoupper(trim($partita_iva));

        // se il dato non è obbligatorio ed è vuoto allora ritorno true
        if (empty($partita_iva) && filter_var($obbligatorio, FILTER_VALIDATE_BOOLEAN) == false) {
            return true;
        }

        // verifico se il codice e' nullo, la lunghezza del codice, la presenza di caratteri non consentiti
        if (empty($partita_iva) || (strlen($partita_iva) != 16 && strlen($partita_iva) != 11) || ctype_alnum($partita_iva) === false) {
            return false;
        }

        // verifico se la partita iva è formalmente valida
        $s = 0;

        for( $i = 0; $i <= 9; $i += 2 )
            $s += ord($partita_iva[$i]) - ord('0');
        for( $i = 1; $i <= 9; $i += 2 ){
            $c = 2*( ord($partita_iva[$i]) - ord('0') );
            if( $c > 9 )  $c = $c - 9;
            $s += $c;
        }
        if( ( 10 - $s%10 )%10 != ord($partita_iva[10]) - ord('0') ) {
            return false;
        }

        return true;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed      $value      The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     *
     * @return bool
     */
    public function validate($value, Constraint $constraint)
    {
        if(strlen(strtoupper(trim($value))) == 16) {
            if($constraint->getLegaleRappresentante() instanceof Persona) {
                $nome = $constraint->getLegaleRappresentante()->getNome();
                $cognome = $constraint->getLegaleRappresentante()->getCognome();
                $sesso = $constraint->getLegaleRappresentante()->getSesso();
                /** @var \DateTime $dataNascita */
                $dataNascita = $constraint->getLegaleRappresentante()->getDataNascita();
                /** @var GeoStato $stato */
                $stato = $constraint->getLegaleRappresentante()->getStatoNascita();
                /** @var GeoComune $comune */
                $comune = $constraint->getLegaleRappresentante()->getComune();

                $codiceFiscaleNazioneComune = $comune instanceof GeoComune ? $comune->getCodiceCatastale() : $stato->getCodiceFiscale();
                $valido = $this->verificaCodiceFiscale($value,$constraint->getObbligatorio(), $nome, $cognome, $dataNascita->format('d/m/Y'),$sesso, $codiceFiscaleNazioneComune);

                if (!$valido) {
                    $this->context->buildViolation($constraint->messageCf)->setParameter('%string%', $value)->addViolation();
                    return false;
                }
            } else {
                $valido = $this->verificaFormaleCodiceFiscale($value,$constraint->getObbligatorio());

                if (!$valido) {
                    $this->context->buildViolation($constraint->messageCf)->setParameter('%string%', $value)->addViolation();
                    return false;
                }
            }
        } elseif (strlen(strtoupper(trim($value))) == 11) {
            $valido = $this->verificaPartitaIva($value, $constraint->getObbligatorio());

            if (!$valido) {
                $this->context->buildViolation($constraint->messagePiva)->setParameter('%string%', $value)->addViolation();
                return false;
            }
        } elseif (strlen(strtoupper(trim($value))) != 0 && $constraint->getObbligatorio()) {
            $this->context->buildViolation($constraint->messageLen)->setParameter('%string%', $value)->addViolation();
            return false;
        }

        return true;
    }
}