<?php


namespace SoggettoBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;

/**
 * Class Adrier
 */
class Adrier
{
    /**
     * @var AdrierHeader
     */
    protected $header;

    /**
     * @var AdrierDati
     */
    protected $dati;

    /**
     * Adrier constructor.
     */
    public function __construct(array $rispostaAdrier)
    {
        $this->header = new AdrierHeader($rispostaAdrier['HEADER']);
        $this->dati   = new AdrierDati($rispostaAdrier['DATI']);
    }

    /**
     * @return AdrierHeader
     */
    public function getHeader(): AdrierHeader
    {
        return $this->header;
    }

    /**
     * @param AdrierHeader $header
     *
     * @return Adrier
     */
    public function setHeader(AdrierHeader $header): Adrier
    {
        $this->header = $header;

        return $this;
    }

    /**
     * @return AdrierDati
     */
    public function getDati(): AdrierDati
    {
        return $this->dati;
    }

    /**
     * @param AdrierDati $dati
     *
     * @return Adrier
     */
    public function setDati(AdrierDati $dati): Adrier
    {
        $this->dati = $dati;

        return $this;
    }
}

/**
 * Class AdrierHeader
 */
class AdrierHeader
{
    /**
     * @var string
     */
    protected $esecutore;

    /**
     * @var string
     */
    protected $servizio;

    /**
     * @var string
     */
    protected $esito;

    /**
     * AdrierHeader constructor.
     */
    public function __construct(array $header)
    {
        $this->esecutore = $header['ESECUTORE'];
        $this->servizio  = $header['SERVIZIO'];
        $this->esito     = $header['ESITO'];
    }

    /**
     * @return string
     */
    public function getEsecutore(): string
    {
        return $this->esecutore;
    }

    /**
     * @param string $esecutore
     *
     * @return AdrierHeader
     */
    public function setEsecutore(string $esecutore): AdrierHeader
    {
        $this->esecutore = $esecutore;

        return $this;
    }

    /**
     * @return string
     */
    public function getServizio(): string
    {
        return $this->servizio;
    }

    /**
     * @param string $servizio
     *
     * @return AdrierHeader
     */
    public function setServizio(string $servizio): AdrierHeader
    {
        $this->servizio = $servizio;

        return $this;
    }

    /**
     * @return string
     */
    public function getEsito(): string
    {
        return $this->esito;
    }

    /**
     * @param string $esito
     *
     * @return AdrierHeader
     */
    public function setEsito(string $esito): AdrierHeader
    {
        $this->esito = $esito;

        return $this;
    }
}

/**
 * Class AdrierDati
 */
class AdrierDati
{
    /**
     * @var AdrierDatiImpresa
     */
    protected $datiImpresa;

    /**
     * @var AdrierErrore
     */
    protected $errore;

    /**
     * AdrierDati constructor.
     */
    public function __construct(array $dati)
    {
        if (isset($dati['DATI_IMPRESA'])) {
            $this->datiImpresa = new AdrierDatiImpresa($dati['DATI_IMPRESA']);
        }
        if (isset($dati['ERRORE'])) {
            $this->errore = new AdrierErrore($dati['ERRORE'] ?? null);
        }
    }

    /**
     * @return AdrierDatiImpresa|null
     */
    public function getDatiImpresa(): ?AdrierDatiImpresa
    {
        return $this->datiImpresa;
    }

    /**
     * @param AdrierDatiImpresa $datiImpresa
     *
     * @return AdrierDati
     */
    public function setDatiImpresa(AdrierDatiImpresa $datiImpresa): AdrierDati
    {
        $this->datiImpresa = $datiImpresa;

        return $this;
    }

    /**
     * @return AdrierErrore|null
     */
    public function getErrore(): ?AdrierErrore
    {
        return $this->errore;
    }

    /**
     * @param AdrierErrore $errore
     *
     * @return AdrierDati
     */
    public function setErrore(AdrierErrore $errore): AdrierDati
    {
        $this->errore = $errore;

        return $this;
    }
}

/**
 * Class AdrierErrore
 */
class AdrierErrore
{
    /**
     * @var string
     */
    protected $tipo;

    /**
     * @var string
     */
    protected $msgErr;

    /**
     * AdrierErrore constructor.
     */
    public function __construct(?array $errore)
    {
        $this->tipo   = $errore['TIPO'] ?? null;
        $this->msgErr = $errore['MSG_ERR'] ?? null;
    }

    /**
     * @return string
     */
    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    /**
     * @param string $tipo
     *
     * @return AdrierErrore
     */
    public function setTipo(string $tipo): AdrierErrore
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * @return string
     */
    public function getMsgErr(): ?string
    {
        return $this->msgErr;
    }

    /**
     * @param string $msgErr
     *
     * @return AdrierErrore
     */
    public function setMsgErr(string $msgErr): AdrierErrore
    {
        $this->msgErr = $msgErr;

        return $this;
    }
}

/**
 * Class AdrierDatiImpresa
 */
class AdrierDatiImpresa
{
    /**
     * @var AdrierEstremiImpresa
     */
    protected $estremiImpresa;

    /**
     * @var string
     */
    protected $oggettoSociale;

    /**
     * @var string
     */
    protected $dtFondazione;

    /**
     * @var string
     */
    protected $codiceFormaAmmv;

    /**
     * @var string
     */
    protected $descrizioneFormaAmmv;

    /**
     * @var AdrierDurataSocieta
     */
    protected $durataSocieta;

    /**
     * @var AdrierCapitali
     */
    protected $capitali;

    /**
     * @var AdrierCapitaleInvestito
     */
    protected $capitaleInvestito;

    /**
     * @var AdrierInformazioniSede
     */
    protected $informazioniSede;

    /**
     * @var AdrierPersoneSede
     */
    protected $personeSede;

    /**
     * @var AdrierLocalizzazioni[]|ArrayCollection
     */
    protected $localizzazioni;

    /**
     * AdrierDatiImpresa constructor.
     */
    public function __construct(?array $datiImpresa)
    {
        $this->estremiImpresa       = new AdrierEstremiImpresa($datiImpresa['ESTREMI_IMPRESA'] ?? null);
        $this->oggettoSociale       = $datiImpresa['OGGETTO_SOCIALE'] ?? null;
        $this->dtFondazione         = $datiImpresa['DT_FONDAZIONE'] ?? null;
        $this->codiceFormaAmmv      = $datiImpresa['CODICE_FORMA_AMMV'] ?? null;
        $this->descrizioneFormaAmmv = $datiImpresa['DESC_FORMA_AMMV'] ?? null;
        $this->durataSocieta        = new AdrierDurataSocieta($datiImpresa['DURATA_SOCIETA'] ?? null);
        $this->capitali             = new AdrierCapitali($datiImpresa['CAPITALI'] ?? null);
        $this->capitaleInvestito    = new AdrierCapitaleInvestito($datiImpresa['CAPITALE_INVESTITO'] ?? null);
        $this->informazioniSede     = new AdrierInformazioniSede($datiImpresa['INFORMAZIONI_SEDE'] ?? null);
        $this->personeSede          = new AdrierPersoneSede($datiImpresa['PERSONE_SEDE'] ?? null);
        $this->localizzazioni       = new ArrayCollection();

        if (isset($datiImpresa['LOCALIZZAZIONI'])) {
            if(isset($datiImpresa['LOCALIZZAZIONI']['LOCALIZZAZIONE'])) {
                $this->localizzazioni->add(new AdrierLocalizzazioni($datiImpresa['LOCALIZZAZIONI']));
            } else {
                foreach ($datiImpresa['LOCALIZZAZIONI'] as $localizzazioni) {
                    $this->localizzazioni->add(new AdrierLocalizzazioni($localizzazioni ?? null));
                }
            }
        }
    }

    /**
     * @return AdrierEstremiImpresa
     */
    public function getEstremiImpresa(): ?AdrierEstremiImpresa
    {
        return $this->estremiImpresa;
    }

    /**
     * @param AdrierEstremiImpresa $estremiImpresa
     *
     * @return AdrierDatiImpresa
     */
    public function setEstremiImpresa(AdrierEstremiImpresa $estremiImpresa): AdrierDatiImpresa
    {
        $this->estremiImpresa = $estremiImpresa;

        return $this;
    }

    /**
     * @return string
     */
    public function getOggettoSociale(): ?string
    {
        return $this->oggettoSociale;
    }

    /**
     * @param string $oggettoSociale
     *
     * @return AdrierDatiImpresa
     */
    public function setOggettoSociale(string $oggettoSociale): AdrierDatiImpresa
    {
        $this->oggettoSociale = $oggettoSociale;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtFondazione(): ?string
    {
        return $this->dtFondazione;
    }

    /**
     * @param string $dtFondazione
     *
     * @return AdrierDatiImpresa
     */
    public function setDtFondazione(string $dtFondazione): AdrierDatiImpresa
    {
        $this->dtFondazione = $dtFondazione;

        return $this;
    }

    /**
     * @return string
     */
    public function getCodiceFormaAmmv(): ?string
    {
        return $this->codiceFormaAmmv;
    }

    /**
     * @param string $codiceFormaAmmv
     *
     * @return AdrierDatiImpresa
     */
    public function setCodiceFormaAmmv(string $codiceFormaAmmv): AdrierDatiImpresa
    {
        $this->codiceFormaAmmv = $codiceFormaAmmv;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescrizioneFormaAmmv(): ?string
    {
        return $this->descrizioneFormaAmmv;
    }

    /**
     * @param string $descrizioneFormaAmmv
     *
     * @return AdrierDatiImpresa
     */
    public function setDescrizioneFormaAmmv(string $descrizioneFormaAmmv): AdrierDatiImpresa
    {
        $this->descrizioneFormaAmmv = $descrizioneFormaAmmv;

        return $this;
    }

    /**
     * @return AdrierDurataSocieta
     */
    public function getDurataSocieta(): ?AdrierDurataSocieta
    {
        return $this->durataSocieta;
    }

    /**
     * @param AdrierDurataSocieta $durataSocieta
     *
     * @return AdrierDatiImpresa
     */
    public function setDurataSocieta(AdrierDurataSocieta $durataSocieta): AdrierDatiImpresa
    {
        $this->durataSocieta = $durataSocieta;

        return $this;
    }

    /**
     * @return AdrierCapitali
     */
    public function getCapitali(): ?AdrierCapitali
    {
        return $this->capitali;
    }

    /**
     * @param AdrierCapitali $capitali
     *
     * @return AdrierDatiImpresa
     */
    public function setCapitali(AdrierCapitali $capitali): AdrierDatiImpresa
    {
        $this->capitali = $capitali;

        return $this;
    }

    /**
     * @return AdrierCapitaleInvestito
     */
    public function getCapitaleInvestito(): ?AdrierCapitaleInvestito
    {
        return $this->capitaleInvestito;
    }

    /**
     * @param AdrierCapitaleInvestito $capitaleInvestito
     *
     * @return AdrierDatiImpresa
     */
    public function setCapitaleInvestito(AdrierCapitaleInvestito $capitaleInvestito): AdrierDatiImpresa
    {
        $this->capitaleInvestito = $capitaleInvestito;

        return $this;
    }

    /**
     * @return AdrierInformazioniSede
     */
    public function getInformazioniSede(): ?AdrierInformazioniSede
    {
        return $this->informazioniSede;
    }

    /**
     * @param AdrierInformazioniSede $informazioniSede
     *
     * @return AdrierDatiImpresa
     */
    public function setInformazioniSede(AdrierInformazioniSede $informazioniSede): AdrierDatiImpresa
    {
        $this->informazioniSede = $informazioniSede;

        return $this;
    }

    /**
     * @return AdrierPersoneSede
     */
    public function getPersoneSede(): ?AdrierPersoneSede
    {
        return $this->personeSede;
    }

    /**
     * @param AdrierPersoneSede $personeSede
     *
     * @return AdrierDatiImpresa
     */
    public function setPersoneSede(AdrierPersoneSede $personeSede): AdrierDatiImpresa
    {
        $this->personeSede = $personeSede;

        return $this;
    }

    /**
     * @return ArrayCollection|AdrierLocalizzazioni[]
     */
    public function getLocalizzazioni()
    {
        return $this->localizzazioni;
    }

    /**
     * @param ArrayCollection|AdrierLocalizzazioni[] $localizzazioni
     *
     * @return AdrierDatiImpresa
     */
    public function setLocalizzazioni($localizzazioni)
    {
        $this->localizzazioni = $localizzazioni;

        return $this;
    }

    /**
     * @param AdrierLocalizzazioni $localizzazione
     */
    public function addLocalizzazione(AdrierLocalizzazioni $localizzazione)
    {
        if (!$this->localizzazioni->contains($localizzazione)) {
            $this->localizzazioni->add($localizzazione);
        }
    }

    /**
     * @return AdrierPersona
     */
    public function getLegaleRappresentante()
    {
        /** @var AdrierPersona $personaSede */
        foreach ($this->getPersoneSede()->getPersona() as $personaSede) {
            if(strtoupper($personaSede->getRappresentante()) === 'SI') {
                return $personaSede;
            }
        }
    }
}

/**
 * Class AdrierEstremiImpresa
 */
class AdrierEstremiImpresa
{
    /**
     * @var string
     */
    protected $denominazione;

    /**
     * @var string
     */
    protected $codiceFiscale;

    /**
     * @var string
     */
    protected $partitaIva;

    /**
     * @var AdrierFormaGiuridica
     */
    protected $formaGiuridica;

    /**
     * @var AdrierDatiIscrizioneRi
     */
    protected $datiIscrizioneRi;

    /**
     * @var AdrierDatiIscrizioneRea
     */
    protected $datiIscrizioneRea;

    /**
     * AdrierEstremiImpresa constructor.
     */
    public function __construct(?array $estremiImpresa)
    {
        $this->denominazione     = $estremiImpresa['DENOMINAZIONE'] ?? null;
        $this->codiceFiscale     = $estremiImpresa['CODICE_FISCALE'] ?? null;
        $this->partitaIva        = $estremiImpresa['PARTITA_IVA'] ?? null;
        $this->formaGiuridica    = new AdrierFormaGiuridica($estremiImpresa['FORMA_GIURIDICA'] ?? null);
        $this->datiIscrizioneRi  = new AdrierDatiIscrizioneRi($estremiImpresa['DATI_ISCRIZIONE_RI'] ?? null);
        $this->datiIscrizioneRea = new AdrierDatiIscrizioneRea($estremiImpresa['DATI_ISCRIZIONE_REA'] ?? null);
    }

    /**
     * @return string
     */
    public function getDenominazione(): ?string
    {
        return $this->denominazione;
    }

    /**
     * @param string $denominazione
     *
     * @return AdrierEstremiImpresa
     */
    public function setDenominazione(string $denominazione): AdrierEstremiImpresa
    {
        $this->denominazione = $denominazione;

        return $this;
    }

    /**
     * @return string
     */
    public function getCodiceFiscale(): ?string
    {
        return $this->codiceFiscale;
    }

    /**
     * @param string $codiceFiscale
     *
     * @return AdrierEstremiImpresa
     */
    public function setCodiceFiscale(string $codiceFiscale): AdrierEstremiImpresa
    {
        $this->codiceFiscale = $codiceFiscale;

        return $this;
    }

    /**
     * @return string
     */
    public function getPartitaIva(): ?string
    {
        return $this->partitaIva;
    }

    /**
     * @param string $partitaIva
     *
     * @return AdrierEstremiImpresa
     */
    public function setPartitaIva(string $partitaIva): AdrierEstremiImpresa
    {
        $this->partitaIva = $partitaIva;

        return $this;
    }

    /**
     * @return AdrierFormaGiuridica
     */
    public function getFormaGiuridica(): ?AdrierFormaGiuridica
    {
        return $this->formaGiuridica;
    }

    /**
     * @param AdrierFormaGiuridica $formaGiuridica
     *
     * @return AdrierEstremiImpresa
     */
    public function setFormaGiuridica(AdrierFormaGiuridica $formaGiuridica): AdrierEstremiImpresa
    {
        $this->formaGiuridica = $formaGiuridica;

        return $this;
    }

    /**
     * @return AdrierDatiIscrizioneRi
     */
    public function getDatiIscrizioneRi(): ?AdrierDatiIscrizioneRi
    {
        return $this->datiIscrizioneRi;
    }

    /**
     * @param AdrierDatiIscrizioneRi $datiIscrizioneRi
     *
     * @return AdrierEstremiImpresa
     */
    public function setDatiIscrizioneRi(AdrierDatiIscrizioneRi $datiIscrizioneRi): AdrierEstremiImpresa
    {
        $this->datiIscrizioneRi = $datiIscrizioneRi;

        return $this;
    }

    /**
     * @return AdrierDatiIscrizioneRea
     */
    public function getDatiIscrizioneRea(): ?AdrierDatiIscrizioneRea
    {
        return $this->datiIscrizioneRea;
    }

    /**
     * @param AdrierDatiIscrizioneRea $datiIscrizioneRea
     *
     * @return AdrierEstremiImpresa
     */
    public function setDatiIscrizioneRea(AdrierDatiIscrizioneRea $datiIscrizioneRea): AdrierEstremiImpresa
    {
        $this->datiIscrizioneRea = $datiIscrizioneRea;

        return $this;
    }
}

/**
 * Class AdrierFormaGiuridica
 */
class AdrierFormaGiuridica
{
    /**
     * @var string
     */
    protected $cFormaGiuridica;

    /**
     * @var string
     */
    protected $descrizione;

    /**
     * AdrierFormaGiuridica constructor.
     */
    public function __construct(array $formaGiuridica)
    {
        $this->cFormaGiuridica = $formaGiuridica['C_FORMA_GIURIDICA'] ?? null;
        $this->descrizione     = $formaGiuridica['DESCRIZIONE'] ?? null;
    }

    /**
     * @return string
     */
    public function getCFormaGiuridica(): ?string
    {
        return $this->cFormaGiuridica;
    }

    /**
     * @param string $cFormaGiuridica
     *
     * @return AdrierFormaGiuridica
     */
    public function setCFormaGiuridica(string $cFormaGiuridica): AdrierFormaGiuridica
    {
        $this->cFormaGiuridica = $cFormaGiuridica;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescrizione(): ?string
    {
        return $this->descrizione;
    }

    /**
     * @param string $descrizione
     *
     * @return AdrierFormaGiuridica
     */
    public function setDescrizione(string $descrizione): AdrierFormaGiuridica
    {
        $this->descrizione = $descrizione;

        return $this;
    }
}

/**
 * Class AdrierDatiIscrizioneRi
 */
class AdrierDatiIscrizioneRi
{
    /**
     * @var string
     */
    protected $numeroRi;

    /**
     * @var string
     */
    protected $data;

    /**
     * @var AdrierSezione[]|ArrayCollection
     */
    protected $sezioni;

    /**
     * AdrierDatiIscrizioneRi constructor.
     */
    public function __construct(?array $datiIscrizioneRi)
    {
        $this->numeroRi = $datiIscrizioneRi['NUMERO_RI'] ?? null;
        $this->data     = $datiIscrizioneRi['DATA'] ?? null;
        $this->sezioni  = new ArrayCollection();
        if (isset($datiIscrizioneRi['SEZIONI'])) {
            foreach ($datiIscrizioneRi['SEZIONI'] as $sezione) {
                $this->sezioni->add(new AdrierSezione($sezione ?? null));
            }
        }
    }

    /**
     * @return string
     */
    public function getNumeroRi(): ?string
    {
        return $this->numeroRi;
    }

    /**
     * @param string $numeroRi
     *
     * @return AdrierDatiIscrizioneRi
     */
    public function setNumeroRi(string $numeroRi): AdrierDatiIscrizioneRi
    {
        $this->numeroRi = $numeroRi;

        return $this;
    }

    /**
     * @return string
     */
    public function getData(): ?string
    {
        return $this->data;
    }

    /**
     * @param string $data
     *
     * @return AdrierDatiIscrizioneRi
     */
    public function setData(string $data): AdrierDatiIscrizioneRi
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return ArrayCollection|AdrierSezione[]
     */
    public function getSezioni()
    {
        return $this->sezioni;
    }

    /**
     * @param ArrayCollection|AdrierSezione[] $sezioni
     *
     * @return AdrierDatiIscrizioneRi
     */
    public function setSezioni($sezioni)
    {
        $this->sezioni = $sezioni;

        return $this;
    }

    /**
     * @param AdrierSezione $sezione
     */
    public function addSezione(AdrierSezione $sezione)
    {
        if (!$this->sezioni->contains($sezione)) {
            $this->sezioni->add($sezione);
        }
    }
}

/**
 * Class AdrierSezione
 */
class AdrierSezione
{
    /**
     * @var string
     */
    protected $cSezione;

    /**
     * @var string
     */
    protected $descrizione;

    /**
     * @var string
     */
    protected $dtIscrizione;

    /**
     * @var string
     */
    protected $coltDiretto;

    /**
     * AdrierSezione constructor.
     */
    public function __construct(?array $sezione)
    {
        $this->cSezione     = $sezione['C_SEZIONE'] ?? null;
        $this->descrizione  = $sezione['DESCRIZIONE'] ?? null;
        $this->dtIscrizione = $sezione['DT_ISCRIZIONE'] ?? null;
        $this->coltDiretto  = $sezione['COLT_DIRETTO'] ?? null;
    }

    /**
     * @return string
     */
    public function getCSezione(): ?string
    {
        return $this->cSezione;
    }

    /**
     * @param string $cSezione
     *
     * @return AdrierSezione
     */
    public function setCSezione(string $cSezione): AdrierSezione
    {
        $this->cSezione = $cSezione;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescrizione(): ?string
    {
        return $this->descrizione;
    }

    /**
     * @param string $descrizione
     *
     * @return AdrierSezione
     */
    public function setDescrizione(string $descrizione): AdrierSezione
    {
        $this->descrizione = $descrizione;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtIscrizione(): ?string
    {
        return $this->dtIscrizione;
    }

    /**
     * @param string $dtIscrizione
     *
     * @return AdrierSezione
     */
    public function setDtIscrizione(string $dtIscrizione): AdrierSezione
    {
        $this->dtIscrizione = $dtIscrizione;

        return $this;
    }

    /**
     * @return string
     */
    public function getColtDiretto(): ?string
    {
        return $this->coltDiretto;
    }

    /**
     * @param string $coltDiretto
     *
     * @return AdrierSezione
     */
    public function setColtDiretto(string $coltDiretto): AdrierSezione
    {
        $this->coltDiretto = $coltDiretto;

        return $this;
    }
}

/**
 * Class AdrierDatiIscrizioneRea
 */
class AdrierDatiIscrizioneRea
{
    /**
     * @var string
     */
    protected $nRea;

    /**
     * @var string
     */
    protected $cciaa;

    /**
     * @var string
     */
    protected $flagSede;

    /**
     * @var string
     */
    protected $iTrasferimentoSede;

    /**
     * @var string
     */
    protected $fAggiornamento;

    /**
     * @var string
     */
    protected $data;

    /**
     * @var string
     */
    protected $dtUltAggiornamento;

    /**
     * @var string
     */
    protected $cFonte;

    /**
     * @var AdrierCessazione
     */
    protected $cessazione;

    /**
     * AdrierDatiIscrizioneRea constructor.
     */
    public function __construct(?array $datiIscrizioneRea)
    {
        $this->nRea               = $datiIscrizioneRea['NREA'] ?? null;
        $this->cciaa              = $datiIscrizioneRea['CCIAA'] ?? null;
        $this->flagSede           = $datiIscrizioneRea['FLAG_SEDE'] ?? null;
        $this->iTrasferimentoSede = $datiIscrizioneRea['I_TRASFERIMENTO_SEDE'] ?? null;
        $this->fAggiornamento     = $datiIscrizioneRea['F_AGGIORNAMENTO'] ?? null;
        $this->data               = $datiIscrizioneRea['DATA'] ?? null;
        $this->dtUltAggiornamento = $datiIscrizioneRea['DT_ULT_AGGIORNAMENTO'] ?? null;
        $this->cFonte             = $datiIscrizioneRea['C_FONTE'] ?? null;
        $this->cessazione         = new AdrierCessazione($datiIscrizioneRea['CESSAZIONE'] ?? null);
    }

    /**
     * @return string
     */
    public function getNRea(): ?string
    {
        return $this->nRea;
    }

    /**
     * @param string $nRea
     *
     * @return AdrierDatiIscrizioneRea
     */
    public function setNRea(string $nRea): AdrierDatiIscrizioneRea
    {
        $this->nRea = $nRea;

        return $this;
    }

    /**
     * @return string
     */
    public function getCciaa(): ?string
    {
        return $this->cciaa;
    }

    /**
     * @param string $cciaa
     *
     * @return AdrierDatiIscrizioneRea
     */
    public function setCciaa(string $cciaa): AdrierDatiIscrizioneRea
    {
        $this->cciaa = $cciaa;

        return $this;
    }

    /**
     * @return string
     */
    public function getFlagSede(): ?string
    {
        return $this->flagSede;
    }

    /**
     * @param string $flagSede
     *
     * @return AdrierDatiIscrizioneRea
     */
    public function setFlagSede(string $flagSede): AdrierDatiIscrizioneRea
    {
        $this->flagSede = $flagSede;

        return $this;
    }

    /**
     * @return string
     */
    public function getITrasferimentoSede(): ?string
    {
        return $this->iTrasferimentoSede;
    }

    /**
     * @param string $iTrasferimentoSede
     *
     * @return AdrierDatiIscrizioneRea
     */
    public function setITrasferimentoSede(string $iTrasferimentoSede): AdrierDatiIscrizioneRea
    {
        $this->iTrasferimentoSede = $iTrasferimentoSede;

        return $this;
    }

    /**
     * @return string
     */
    public function getFAggiornamento(): ?string
    {
        return $this->fAggiornamento;
    }

    /**
     * @param string $fAggiornamento
     *
     * @return AdrierDatiIscrizioneRea
     */
    public function setFAggiornamento(string $fAggiornamento): AdrierDatiIscrizioneRea
    {
        $this->fAggiornamento = $fAggiornamento;

        return $this;
    }

    /**
     * @return string
     */
    public function getData(): ?DateTime
    {
        if (!empty($this->data)) {
            return DateTime::createFromFormat('Ymd', $this->data);
        }

        return null;
    }

    /**
     * @param string $data
     *
     * @return AdrierDatiIscrizioneRea
     */
    public function setData(string $data): AdrierDatiIscrizioneRea
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtUltAggiornamento(): ?string
    {
        return $this->dtUltAggiornamento;
    }

    /**
     * @param string $dtUltAggiornamento
     *
     * @return AdrierDatiIscrizioneRea
     */
    public function setDtUltAggiornamento(string $dtUltAggiornamento): AdrierDatiIscrizioneRea
    {
        $this->dtUltAggiornamento = $dtUltAggiornamento;

        return $this;
    }

    /**
     * @return string
     */
    public function getCFonte(): ?string
    {
        return $this->cFonte;
    }

    /**
     * @param string $cFonte
     *
     * @return AdrierDatiIscrizioneRea
     */
    public function setCFonte(string $cFonte): AdrierDatiIscrizioneRea
    {
        $this->cFonte = $cFonte;

        return $this;
    }

    /**
     * @return AdrierCessazione
     */
    public function getCessazione(): ?AdrierCessazione
    {
        return $this->cessazione;
    }

    /**
     * @param AdrierCessazione $cessazione
     *
     * @return AdrierDatiIscrizioneRea
     */
    public function setCessazione(AdrierCessazione $cessazione): AdrierDatiIscrizioneRea
    {
        $this->cessazione = $cessazione;

        return $this;
    }
}

/**
 * Class AdrierCessazione
 */
class AdrierCessazione
{
    /**
     * @var string
     */
    protected $dtCancellazione;

    /**
     * @var string
     */
    protected $dtCessazione;

    /**
     * @var string
     */
    protected $dtDenunciaCess;

    /**
     * @var string
     */
    protected $causale;

    /**
     * AdrierCessazione constructor.
     */
    public function __construct(?array $cessazione)
    {
        $this->dtCancellazione = $cessazione['DT_CANCELLAZIONE'] ?? null;
        $this->dtCessazione    = $cessazione['DT_CESSAZIONE'] ?? null;
        $this->dtDenunciaCess  = $cessazione['DT_DENUNCIA_CESS'] ?? null;
        $this->causale         = $cessazione['CAUSALE'] ?? null;
    }

    /**
     * @return string
     */
    public function getDtCancellazione(): ?string
    {
        return $this->dtCancellazione;
    }

    /**
     * @param string $dtCancellazione
     *
     * @return AdrierCessazione
     */
    public function setDtCancellazione(string $dtCancellazione): AdrierCessazione
    {
        $this->dtCancellazione = $dtCancellazione;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtCessazione(): ?string
    {
        return $this->dtCessazione;
    }

    /**
     * @param string $dtCessazione
     *
     * @return AdrierCessazione
     */
    public function setDtCessazione(string $dtCessazione): AdrierCessazione
    {
        $this->dtCessazione = $dtCessazione;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtDenunciaCess(): ?string
    {
        return $this->dtDenunciaCess;
    }

    /**
     * @param string $dtDenunciaCess
     *
     * @return AdrierCessazione
     */
    public function setDtDenunciaCess(string $dtDenunciaCess): AdrierCessazione
    {
        $this->dtDenunciaCess = $dtDenunciaCess;

        return $this;
    }

    /**
     * @return string
     */
    public function getCausale(): ?string
    {
        return $this->causale;
    }

    /**
     * @param string $causale
     *
     * @return AdrierCessazione
     */
    public function setCausale(string $causale): AdrierCessazione
    {
        $this->causale = $causale;

        return $this;
    }
}

/**
 * Class AdrierDurataSocieta
 */
class AdrierDurataSocieta
{
    /**
     * @var string
     */
    protected $dtCostituzione;

    /**
     * @var string
     */
    protected $dtTermine;

    /**
     * @var string
     */
    protected $durataIllimitata;

    /**
     * @var string
     */
    protected $scadenzeEsercizi;

    /**
     * AdrierDurataSocieta constructor.
     */
    public function __construct(?array $durataSocieta)
    {
        $this->dtCostituzione   = $durataSocieta['DT_COSTITUZIONE'] ?? null;
        $this->dtTermine        = $durataSocieta['DT_TERMINE'] ?? null;
        $this->durataIllimitata = $durataSocieta['DURATA_ILLIMITATA'] ?? null;
        $this->scadenzeEsercizi = new AdrierScadenzeEsercizi($durataSocieta['SCADENZE_ESERCIZI'] ?? null);
    }

    /**
     * @return DateTime|null
     *
     * @throws Exception
     */
    public function getDtCostituzione(): ?DateTime
    {
        if (!empty($this->dtCostituzione)) {
            return DateTime::createFromFormat('Ymd', $this->dtCostituzione);
        }

        return null;
    }

    /**
     * @param string $dtCostituzione
     *
     * @return AdrierDurataSocieta
     */
    public function setDtCostituzione(string $dtCostituzione): AdrierDurataSocieta
    {
        $this->dtCostituzione = $dtCostituzione;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtTermine(): ?string
    {
        return $this->dtTermine;
    }

    /**
     * @param string $dtTermine
     *
     * @return AdrierDurataSocieta
     */
    public function setDtTermine(string $dtTermine): AdrierDurataSocieta
    {
        $this->dtTermine = $dtTermine;

        return $this;
    }

    /**
     * @return string
     */
    public function getDurataIllimitata(): ?string
    {
        return $this->durataIllimitata;
    }

    /**
     * @param string $durataIllimitata
     *
     * @return AdrierDurataSocieta
     */
    public function setDurataIllimitata(string $durataIllimitata): AdrierDurataSocieta
    {
        $this->durataIllimitata = $durataIllimitata;

        return $this;
    }

    /**
     * @return string
     */
    public function getScadenzeEsercizi(): ?string
    {
        return $this->scadenzeEsercizi;
    }

    /**
     * @param string $scadenzeEsercizi
     *
     * @return AdrierDurataSocieta
     */
    public function setScadenzeEsercizi(string $scadenzeEsercizi): AdrierDurataSocieta
    {
        $this->scadenzeEsercizi = $scadenzeEsercizi;

        return $this;
    }
}

/**
 * Class AdrierScadenzeEsercizi
 */
class AdrierScadenzeEsercizi
{
    /**
     * @var string
     */
    protected $drPrimoEsercizio;

    /**
     * @var string
     */
    protected $dtSuccessive;

    /**
     * AdrierScadenzeEsercizi constructor.
     */
    public function __construct(?array $scadenzeEsercizi)
    {
        $this->drPrimoEsercizio = $scadenzeEsercizi['DT_PRIMO_ESERCIZIO'] ?? null;
        $this->drPrimoEsercizio = $scadenzeEsercizi['DT_SUCCESSIVE'] ?? null;
    }

    /**
     * @return string
     */
    public function getDrPrimoEsercizio(): ?string
    {
        return $this->drPrimoEsercizio;
    }

    /**
     * @param string $drPrimoEsercizio
     *
     * @return AdrierScadenzeEsercizi
     */
    public function setDrPrimoEsercizio(string $drPrimoEsercizio): AdrierScadenzeEsercizi
    {
        $this->drPrimoEsercizio = $drPrimoEsercizio;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtSuccessive(): ?string
    {
        return $this->dtSuccessive;
    }

    /**
     * @param string $dtSuccessive
     *
     * @return AdrierScadenzeEsercizi
     */
    public function setDtSuccessive(string $dtSuccessive): AdrierScadenzeEsercizi
    {
        $this->dtSuccessive = $dtSuccessive;

        return $this;
    }
}

/**
 * Class AdrierCapitali
 */
class AdrierCapitali
{
    /**
     * @var AdrierFondoConsortile
     */
    protected $fondoConsortile;

    /**
     * @var AdrierTotaleQuote
     */
    protected $totaleQuote;

    /**
     * @var AdrierCapitaleSociale
     */
    protected $capitaleSociale;

    /**
     * AdrierCapitali constructor.
     */
    public function __construct(?array $capitali)
    {
        $this->fondoConsortile = new AdrierFondoConsortile($capitali['FONDO_CONSORTILE'] ?? null);
        $this->totaleQuote     = new AdrierTotaleQuote($capitali['TOTALE_QUOTE'] ?? null);
        $this->capitaleSociale = new AdrierCapitaleSociale($capitali['CAPITALE_SOCIALE'] ?? null);
    }

    /**
     * @return AdrierFondoConsortile
     */
    public function getFondoConsortile(): ?AdrierFondoConsortile
    {
        return $this->fondoConsortile;
    }

    /**
     * @param AdrierFondoConsortile $fondoConsortile
     *
     * @return AdrierCapitali
     */
    public function setFondoConsortile(AdrierFondoConsortile $fondoConsortile): AdrierCapitali
    {
        $this->fondoConsortile = $fondoConsortile;

        return $this;
    }

    /**
     * @return AdrierTotaleQuote
     */
    public function getTotaleQuote(): ?AdrierTotaleQuote
    {
        return $this->totaleQuote;
    }

    /**
     * @param AdrierTotaleQuote $totaleQuote
     *
     * @return AdrierCapitali
     */
    public function setTotaleQuote(AdrierTotaleQuote $totaleQuote): AdrierCapitali
    {
        $this->totaleQuote = $totaleQuote;

        return $this;
    }

    /**
     * @return AdrierCapitaleSociale
     */
    public function getCapitaleSociale(): ?AdrierCapitaleSociale
    {
        return $this->capitaleSociale;
    }

    /**
     * @param AdrierCapitaleSociale $capitaleSociale
     *
     * @return AdrierCapitali
     */
    public function setCapitaleSociale(AdrierCapitaleSociale $capitaleSociale): AdrierCapitali
    {
        $this->capitaleSociale = $capitaleSociale;

        return $this;
    }
}

/**
 * Class AdrierFondoConsortile
 */
class AdrierFondoConsortile
{
    /**
     * @var string
     */
    protected $ammontare;

    /**
     * @var string
     */
    protected $valuta;

    /**
     * AdrierFondoConsortile constructor.
     */
    public function __construct(?array $fondoConsortile)
    {
        $this->ammontare = $fondoConsortile['AMMONTARE'] ?? null;
        $this->valuta    = $fondoConsortile['VALUTA'] ?? null;
    }

    /**
     * @return string
     */
    public function getAmmontare(): ?string
    {
        return $this->ammontare;
    }

    /**
     * @param string $ammontare
     *
     * @return AdrierFondoConsortile
     */
    public function setAmmontare(string $ammontare): AdrierFondoConsortile
    {
        $this->ammontare = $ammontare;

        return $this;
    }

    /**
     * @return string
     */
    public function getValuta(): ?string
    {
        return $this->valuta;
    }

    /**
     * @param string $valuta
     *
     * @return AdrierFondoConsortile
     */
    public function setValuta(string $valuta): AdrierFondoConsortile
    {
        $this->valuta = $valuta;

        return $this;
    }
}

/**
 * Class AdrierTotaleQuote
 */
class AdrierTotaleQuote
{
    /**
     * @var string
     */
    protected $numeroAzioni;

    /**
     * @var string
     */
    protected $numeroQuote;

    /**
     * @var string
     */
    protected $ammontare;

    /**
     * @var string
     */
    protected $valuta;

    /**
     * AdrierTotaleQuote constructor.
     */
    public function __construct(?array $totaleQuote)
    {
        $this->numeroAzioni = $totaleQuote['NUMERO_AZIONI'] ?? null;
        $this->numeroQuote  = $totaleQuote['NUMERO_QUOTE'] ?? null;
        $this->ammontare    = $totaleQuote['AMMONTARE'] ?? null;
        $this->valuta       = $totaleQuote['VALUTA'] ?? null;
    }

    /**
     * @return string
     */
    public function getNumeroAzioni(): ?string
    {
        return $this->numeroAzioni;
    }

    /**
     * @param string $numeroAzioni
     *
     * @return AdrierTotaleQuote
     */
    public function setNumeroAzioni(string $numeroAzioni): AdrierTotaleQuote
    {
        $this->numeroAzioni = $numeroAzioni;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumeroQuote(): ?string
    {
        return $this->numeroQuote;
    }

    /**
     * @param string $numeroQuote
     *
     * @return AdrierTotaleQuote
     */
    public function setNumeroQuote(string $numeroQuote): AdrierTotaleQuote
    {
        $this->numeroQuote = $numeroQuote;

        return $this;
    }

    /**
     * @return string
     */
    public function getAmmontare(): ?string
    {
        return $this->ammontare;
    }

    /**
     * @param string $ammontare
     *
     * @return AdrierTotaleQuote
     */
    public function setAmmontare(string $ammontare): AdrierTotaleQuote
    {
        $this->ammontare = $ammontare;

        return $this;
    }

    /**
     * @return string
     */
    public function getValuta(): ?string
    {
        return $this->valuta;
    }

    /**
     * @param string $valuta
     *
     * @return AdrierTotaleQuote
     */
    public function setValuta(string $valuta): AdrierTotaleQuote
    {
        $this->valuta = $valuta;

        return $this;
    }
}

/**
 * Class AdrierCapitaleSociale
 */
class AdrierCapitaleSociale
{
    /**
     * @var string
     */
    protected $deliberato;

    /**
     * @var string
     */
    protected $sottoscritto;

    /**
     * @var string
     */
    protected $versato;

    /**
     * @var string
     */
    protected $tipoConferimenti;

    /**
     * @var string
     */
    protected $valuta;

    /**
     * AdrierCapitaleSociale constructor.
     */
    public function __construct(?array $capitaleSociale)
    {
        $this->deliberato       = $capitaleSociale['DELIBERATO'] ?? null;
        $this->sottoscritto     = $capitaleSociale['SOTTOSCRITTO'] ?? null;
        $this->versato          = $capitaleSociale['VERSATO'] ?? null;
        $this->tipoConferimenti = $capitaleSociale['TIPO_CONFERIMENTI'] ?? null;
        $this->valuta           = $capitaleSociale['VALUTA'] ?? null;
    }

    /**
     * @return string
     */
    public function getDeliberato(): ?string
    {
        return $this->deliberato;
    }

    /**
     * @param string $deliberato
     *
     * @return AdrierCapitaleSociale
     */
    public function setDeliberato(string $deliberato): AdrierCapitaleSociale
    {
        $this->deliberato = $deliberato;

        return $this;
    }

    /**
     * @return string
     */
    public function getSottoscritto(): ?string
    {
        return $this->sottoscritto;
    }

    /**
     * @param string $sottoscritto
     *
     * @return AdrierCapitaleSociale
     */
    public function setSottoscritto(string $sottoscritto): AdrierCapitaleSociale
    {
        $this->sottoscritto = $sottoscritto;

        return $this;
    }

    /**
     * @return string
     */
    public function getVersato(): ?string
    {
        return $this->versato;
    }

    /**
     * @param string $versato
     *
     * @return AdrierCapitaleSociale
     */
    public function setVersato(string $versato): AdrierCapitaleSociale
    {
        $this->versato = $versato;

        return $this;
    }

    /**
     * @return string
     */
    public function getTipoConferimenti(): ?string
    {
        return $this->tipoConferimenti;
    }

    /**
     * @param string $tipoConferimenti
     *
     * @return AdrierCapitaleSociale
     */
    public function setTipoConferimenti(string $tipoConferimenti): AdrierCapitaleSociale
    {
        $this->tipoConferimenti = $tipoConferimenti;

        return $this;
    }

    /**
     * @return string
     */
    public function getValuta(): ?string
    {
        return $this->valuta;
    }

    /**
     * @param string $valuta
     *
     * @return AdrierCapitaleSociale
     */
    public function setValuta(string $valuta): AdrierCapitaleSociale
    {
        $this->valuta = $valuta;

        return $this;
    }
}

/**
 * Class AdrierCapitaleInvestito
 */
class AdrierCapitaleInvestito
{
    /**
     * @var string
     */
    protected $ammontare;

    /**
     * @var string
     */
    protected $valuta;

    /**
     * AdrierCapitaleInvestito constructor.
     */
    public function __construct(?array $capitaleInvestito)
    {
        $this->ammontare = $capitaleInvestito['AMMONTARE'] ?? null;
        $this->valuta    = $capitaleInvestito['VALUTA'] ?? null;
    }

    /**
     * @return string
     */
    public function getAmmontare(): ?string
    {
        return $this->ammontare;
    }

    /**
     * @param string $ammontare
     *
     * @return AdrierCapitaleInvestito
     */
    public function setAmmontare(string $ammontare): AdrierCapitaleInvestito
    {
        $this->ammontare = $ammontare;

        return $this;
    }

    /**
     * @return string
     */
    public function getValuta(): ?string
    {
        return $this->valuta;
    }

    /**
     * @param string $valuta
     *
     * @return AdrierCapitaleInvestito
     */
    public function setValuta(string $valuta): AdrierCapitaleInvestito
    {
        $this->valuta = $valuta;

        return $this;
    }
}

/**
 * Class AdrierInformazioniSede
 */
class AdrierInformazioniSede
{
    /**
     * @var string
     */
    protected $insegna;

    /**
     * @var AdrierIndirizzo
     */
    protected $indirizzo;

    /**
     * @var string
     */
    protected $attivita;

    /**
     * @var AdrierInfoAttivita
     */
    protected $infoAttivita;

    /**
     * @var AdrierAttivitaIstat[]|ArrayCollection
     */
    protected $codiciIstat02;

    /**
     * @var AdrierAttivitaIstat[]|ArrayCollection
     */
    protected $codiceAtecoUl;

    /**
     * @var AdrierRuoloLoc[]|ArrayCollection
     */
    protected $ruoliLoc;

    /**
     * @var AdrierDatiArtigiani
     */
    protected $datiArtigiani;

    /**
     * @var AdrierCommercioDettaglio
     */
    protected $commercioDettaglio;

    /**
     * @var AdrierCessazione
     */
    protected $cessazione;

    /**
     * @var AdrierProceduraConcorsuale[]|ArrayCollection
     */
    protected $procedureConcorsuali;

    /**
     * AdrierInformazioniSede constructor.
     */
    public function __construct(?array $informazioniSede)
    {
        $this->insegna              = $informazioniSede['INSEGNA'] ?? null;
        $this->indirizzo            = new AdrierIndirizzo($informazioniSede['INDIRIZZO'] ?? null);
        $this->attivita             = $informazioniSede['ATTIVITA'] ?? null;
        $this->infoAttivita         = new AdrierInfoAttivita($informazioniSede['INFO_ATTIVITA'] ?? null);
        $this->codiciIstat02        = new ArrayCollection();
        $this->codiceAtecoUl        = new ArrayCollection();
        $this->ruoliLoc             = new ArrayCollection();
        $this->datiArtigiani        = new AdrierDatiArtigiani($informazioniSede['DATI_ARTIGIANI'] ?? null);
        $this->commercioDettaglio   = new AdrierCommercioDettaglio($informazioniSede['COMMERCIO_DETTAGLIO'] ?? null);
        $this->cessazione           = new AdrierCessazione($informazioniSede['CESSAZIONE'] ?? null);
        $this->procedureConcorsuali = new ArrayCollection();

        if (isset($informazioniSede['CODICI_ISTAT_02']['ATTIVITA_ISTAT'][0])) {
            foreach ($informazioniSede['CODICI_ISTAT_02']['ATTIVITA_ISTAT'] as $codiciIstat) {
                $this->codiciIstat02->add(new AdrierAttivitaIstat($codiciIstat ?? null));
            }
        } else {
            $this->codiciIstat02->add(new AdrierAttivitaIstat($informazioniSede['CODICE_ATECO_UL']['ATTIVITA_ISTAT'] ?? null));
        }

        if (isset($informazioniSede['CODICE_ATECO_UL']['ATTIVITA_ISTAT'][0])) {
            foreach ($informazioniSede['CODICE_ATECO_UL']['ATTIVITA_ISTAT'] as $codiciAteco) {
                $this->codiceAtecoUl->add(new AdrierAttivitaIstat($codiciAteco ?? null));
            }
        } else {
            $this->codiceAtecoUl->add(new AdrierAttivitaIstat($informazioniSede['CODICE_ATECO_UL']['ATTIVITA_ISTAT'] ?? null));
        }

        if (isset($informazioniSede['RUOLI_LOC']['RUOLO_LOC'][0])) {
            foreach ($informazioniSede['RUOLI_LOC']['RUOLO_LOC'] as $ruoloLoc) {
                $this->ruoliLoc->add(new AdrierRuoloLoc($ruoloLoc ?? null));
            }
        } else {
            $this->ruoliLoc->add(new AdrierRuoloLoc($informazioniSede['RUOLI_LOC']['RUOLO_LOC'] ?? null));
        }

        if (isset($informazioniSede['PROCEDURE_CONCORSUALI']['PROCEDURA_CONCORSUALE'][0])) {
            foreach ($informazioniSede['PROCEDURE_CONCORSUALI']['PROCEDURA_CONCORSUALE'] as $procedureConcorsuali) {
                $this->procedureConcorsuali->add(new AdrierProceduraConcorsuale($procedureConcorsuali ?? null));
            }
        } else {
            $this->procedureConcorsuali->add(new AdrierProceduraConcorsuale($informazioniSede['PROCEDURE_CONCORSUALI']['PROCEDURA_CONCORSUALE'] ?? null));
        }

    }

    /**
     * @return string
     */
    public function getInsegna(): ?string
    {
        return $this->insegna;
    }

    /**
     * @param string $insegna
     *
     * @return AdrierInformazioniSede
     */
    public function setInsegna(string $insegna): AdrierInformazioniSede
    {
        $this->insegna = $insegna;

        return $this;
    }

    /**
     * @return AdrierIndirizzo
     */
    public function getIndirizzo(): ?AdrierIndirizzo
    {
        return $this->indirizzo;
    }

    /**
     * @param AdrierIndirizzo $indirizzo
     *
     * @return AdrierInformazioniSede
     */
    public function setIndirizzo(AdrierIndirizzo $indirizzo): AdrierInformazioniSede
    {
        $this->indirizzo = $indirizzo;

        return $this;
    }

    /**
     * @return string
     */
    public function getAttivita(): ?string
    {
        return $this->attivita;
    }

    /**
     * @param string $attivita
     *
     * @return AdrierInformazioniSede
     */
    public function setAttivita(string $attivita): AdrierInformazioniSede
    {
        $this->attivita = $attivita;

        return $this;
    }

    /**
     * @return AdrierInfoAttivita
     */
    public function getInfoAttivita(): ?AdrierInfoAttivita
    {
        return $this->infoAttivita;
    }

    /**
     * @param AdrierInfoAttivita $infoAttivita
     *
     * @return AdrierInformazioniSede
     */
    public function setInfoAttivita(AdrierInfoAttivita $infoAttivita): AdrierInformazioniSede
    {
        $this->infoAttivita = $infoAttivita;

        return $this;
    }

    /**
     * @return ArrayCollection|AdrierAttivitaIstat[]
     */
    public function getCodiciIstat02()
    {
        return $this->codiciIstat02;
    }

    /**
     * @param ArrayCollection|AdrierAttivitaIstat[] $codiciIstat02
     *
     * @return AdrierInformazioniSede
     */
    public function setCodiciIstat02($codiciIstat02)
    {
        $this->codiciIstat02 = $codiciIstat02;

        return $this;
    }

    /**
     * @param AdrierAttivitaIstat $codiceIstat
     */
    public function addCodiciIstat02(AdrierAttivitaIstat $codiceIstat)
    {
        if (!$this->codiciIstat02->contains($codiceIstat)) {
            $this->codiciIstat02->add($codiceIstat);
        }
    }

    /**
     * @return ArrayCollection|AdrierAttivitaIstat[]
     */
    public function getCodiceAtecoUl()
    {
        return $this->codiceAtecoUl;
    }

    /**
     * @param ArrayCollection|AdrierAttivitaIstat[] $codiceAtecoUl
     *
     * @return AdrierInformazioniSede
     */
    public function setCodiceAtecoUl($codiceAtecoUl)
    {
        $this->codiceAtecoUl = $codiceAtecoUl;

        return $this;
    }

    /**
     * @param AdrierAttivitaIstat $codiceIstat
     */
    public function addCodiceAtecoUl(AdrierAttivitaIstat $codiceIstat)
    {
        if (!$this->codiceAtecoUl->contains($codiceIstat)) {
            $this->codiceAtecoUl->add($codiceIstat);
        }
    }

    /**
     * @return array
     */
    public function getAtecoPrincipale()
    {
        foreach ($this->codiceAtecoUl as $ateco) {
            if ($ateco->getCImportanza() === 'P') {
                return [wordwrap($ateco->getCAttivita(), 2, '.', true), $ateco->getDescAttivita()];
            }
        }

        return [];
    }

    /**
     * @return array
     */
    public function getAtecoSecondario()
    {
        foreach ($this->codiceAtecoUl as $ateco) {
            if ($ateco->getCImportanza() === 'S') {
                return [wordwrap($ateco->getCAttivita(), 2, '.', true), $ateco->getDescAttivita()];
            }
        }

        return [];
    }

    /**
     * @return ArrayCollection|AdrierRuoloLoc[]
     */
    public function getRuoliLoc()
    {
        return $this->ruoliLoc;
    }

    /**
     * @param ArrayCollection|AdrierRuoloLoc[] $ruoliLoc
     *
     * @return AdrierInformazioniSede
     */
    public function setRuoliLoc($ruoliLoc)
    {
        $this->ruoliLoc = $ruoliLoc;

        return $this;
    }

    /**
     * @param AdrierRuoloLoc $ruoloLoc
     */
    public function addRuoliLoc(AdrierRuoloLoc $ruoloLoc)
    {
        if (!$this->ruoliLoc->contains($ruoloLoc)) {
            $this->ruoliLoc->add($ruoloLoc);
        }
    }

    /**
     * @return AdrierDatiArtigiani
     */
    public function getDatiArtigiani(): ?AdrierDatiArtigiani
    {
        return $this->datiArtigiani;
    }

    /**
     * @param AdrierDatiArtigiani $datiArtigiani
     *
     * @return AdrierInformazioniSede
     */
    public function setDatiArtigiani(AdrierDatiArtigiani $datiArtigiani): AdrierInformazioniSede
    {
        $this->datiArtigiani = $datiArtigiani;

        return $this;
    }

    /**
     * @return AdrierCommercioDettaglio
     */
    public function getCommercioDettaglio(): ?AdrierCommercioDettaglio
    {
        return $this->commercioDettaglio;
    }

    /**
     * @param AdrierCommercioDettaglio $commercioDettaglio
     *
     * @return AdrierInformazioniSede
     */
    public function setCommercioDettaglio(AdrierCommercioDettaglio $commercioDettaglio): AdrierInformazioniSede
    {
        $this->commercioDettaglio = $commercioDettaglio;

        return $this;
    }

    /**
     * @return AdrierCessazione
     */
    public function getCessazione(): ?AdrierCessazione
    {
        return $this->cessazione;
    }

    /**
     * @param AdrierCessazione $cessazione
     *
     * @return AdrierInformazioniSede
     */
    public function setCessazione(AdrierCessazione $cessazione): AdrierInformazioniSede
    {
        $this->cessazione = $cessazione;

        return $this;
    }

    /**
     * @return ArrayCollection|AdrierProceduraConcorsuale[]
     */
    public function getProcedureConcorsuali()
    {
        return $this->procedureConcorsuali;
    }

    /**
     * @param ArrayCollection|AdrierProceduraConcorsuale[] $procedureConcorsuali
     *
     * @return AdrierInformazioniSede
     */
    public function setProcedureConcorsuali($procedureConcorsuali)
    {
        $this->procedureConcorsuali = $procedureConcorsuali;

        return $this;
    }

    /**
     * @param AdrierProceduraConcorsuale $proceduraConcorsuale
     */
    public function addProcedureConcorsuali(AdrierProceduraConcorsuale $proceduraConcorsuale)
    {
        if (!$this->procedureConcorsuali->contains($proceduraConcorsuale)) {
            $this->procedureConcorsuali->add($proceduraConcorsuale);
        }
    }
}

/**
 * Class AdrierProceduraConcorsuale
 */
class AdrierProceduraConcorsuale
{
    /**
     * @var string
     */
    protected $codicePc;

    /**
     * @var string
     */
    protected $descPc;

    /**
     * @var string
     */
    protected $dtInizioPc;

    /**
     * @var string
     */
    protected $dtTerminePc;

    /**
     * @var string
     */
    protected $dtOmologazionePc;

    /**
     * @var string
     */
    protected $dtAccertamentoPc;

    /**
     * @var string
     */
    protected $dtCessazionePc;

    /**
     * @var string
     */
    protected $dtChiusuraPc;

    /**
     * @var string
     */
    protected $dtEsecuzionePc;

    /**
     * @var string
     */
    protected $dtRisoluzionePc;

    /**
     * @var string
     */
    protected $dtRevocaPc;

    /**
     * AdrierProceduraConcorsuale constructor.
     */
    public function __construct(?array $proceduraConcorsuale)
    {
        $this->codicePc         = $proceduraConcorsuale['CODICE_PC'] ?? null;
        $this->descPc           = $proceduraConcorsuale['DESC_PC'] ?? null;
        $this->dtInizioPc       = $proceduraConcorsuale['DT_INIZIO_PC'] ?? null;
        $this->dtTerminePc      = $proceduraConcorsuale['DT_TERMINE_PC'] ?? null;
        $this->dtOmologazionePc = $proceduraConcorsuale['DT_OMOLOGAZIONE_PC'] ?? null;
        $this->dtAccertamentoPc = $proceduraConcorsuale['DT_ACCERTAMENTO_PC'] ?? null;
        $this->dtCessazionePc   = $proceduraConcorsuale['DT_CESSAZIONE_PC'] ?? null;
        $this->dtChiusuraPc     = $proceduraConcorsuale['DT_CHIUSURA_PC'] ?? null;
        $this->dtEsecuzionePc   = $proceduraConcorsuale['DT_ESECUZIONE_PC'] ?? null;
        $this->dtRisoluzionePc  = $proceduraConcorsuale['DT_RISOLUZIONE_PC'] ?? null;
        $this->dtRevocaPc       = $proceduraConcorsuale['DT_REVOCA_PC'] ?? null;
    }

    /**
     * @return string
     */
    public function getCodicePc(): ?string
    {
        return $this->codicePc;
    }

    /**
     * @param string $codicePc
     *
     * @return AdrierProceduraConcorsuale
     */
    public function setCodicePc(string $codicePc): AdrierProceduraConcorsuale
    {
        $this->codicePc = $codicePc;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescPc(): ?string
    {
        return $this->descPc;
    }

    /**
     * @param string $descPc
     *
     * @return AdrierProceduraConcorsuale
     */
    public function setDescPc(string $descPc): AdrierProceduraConcorsuale
    {
        $this->descPc = $descPc;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtInizioPc(): ?string
    {
        return $this->dtInizioPc;
    }

    /**
     * @param string $dtInizioPc
     *
     * @return AdrierProceduraConcorsuale
     */
    public function setDtInizioPc(string $dtInizioPc): AdrierProceduraConcorsuale
    {
        $this->dtInizioPc = $dtInizioPc;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtTerminePc(): ?string
    {
        return $this->dtTerminePc;
    }

    /**
     * @param string $dtTerminePc
     *
     * @return AdrierProceduraConcorsuale
     */
    public function setDtTerminePc(string $dtTerminePc): AdrierProceduraConcorsuale
    {
        $this->dtTerminePc = $dtTerminePc;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtOmologazionePc(): ?string
    {
        return $this->dtOmologazionePc;
    }

    /**
     * @param string $dtOmologazionePc
     *
     * @return AdrierProceduraConcorsuale
     */
    public function setDtOmologazionePc(string $dtOmologazionePc): AdrierProceduraConcorsuale
    {
        $this->dtOmologazionePc = $dtOmologazionePc;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtAccertamentoPc(): ?string
    {
        return $this->dtAccertamentoPc;
    }

    /**
     * @param string $dtAccertamentoPc
     *
     * @return AdrierProceduraConcorsuale
     */
    public function setDtAccertamentoPc(string $dtAccertamentoPc): AdrierProceduraConcorsuale
    {
        $this->dtAccertamentoPc = $dtAccertamentoPc;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtCessazionePc(): ?string
    {
        return $this->dtCessazionePc;
    }

    /**
     * @param string $dtCessazionePc
     *
     * @return AdrierProceduraConcorsuale
     */
    public function setDtCessazionePc(string $dtCessazionePc): AdrierProceduraConcorsuale
    {
        $this->dtCessazionePc = $dtCessazionePc;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtChiusuraPc(): ?string
    {
        return $this->dtChiusuraPc;
    }

    /**
     * @param string $dtChiusuraPc
     *
     * @return AdrierProceduraConcorsuale
     */
    public function setDtChiusuraPc(string $dtChiusuraPc): AdrierProceduraConcorsuale
    {
        $this->dtChiusuraPc = $dtChiusuraPc;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtEsecuzionePc(): ?string
    {
        return $this->dtEsecuzionePc;
    }

    /**
     * @param string $dtEsecuzionePc
     *
     * @return AdrierProceduraConcorsuale
     */
    public function setDtEsecuzionePc(string $dtEsecuzionePc): AdrierProceduraConcorsuale
    {
        $this->dtEsecuzionePc = $dtEsecuzionePc;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtRisoluzionePc(): ?string
    {
        return $this->dtRisoluzionePc;
    }

    /**
     * @param string $dtRisoluzionePc
     *
     * @return AdrierProceduraConcorsuale
     */
    public function setDtRisoluzionePc(string $dtRisoluzionePc): AdrierProceduraConcorsuale
    {
        $this->dtRisoluzionePc = $dtRisoluzionePc;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtRevocaPc(): ?string
    {
        return $this->dtRevocaPc;
    }

    /**
     * @param string $dtRevocaPc
     *
     * @return AdrierProceduraConcorsuale
     */
    public function setDtRevocaPc(string $dtRevocaPc): AdrierProceduraConcorsuale
    {
        $this->dtRevocaPc = $dtRevocaPc;

        return $this;
    }
}

/**
 * Class AdrierCommercioDettaglio
 */
class AdrierCommercioDettaglio
{
    /**
     * @var string
     */
    protected $dtDenuncia;

    /**
     * @var string
     */
    protected $superficie;

    /**
     * @var string
     */
    protected $settoreMerceologico;

    /**
     * AdrierCommercioDettaglio constructor.
     */
    public function __construct(?array $commercioDettaglio)
    {
        $this->dtDenuncia          = $commercioDettaglio['DT_DENUNCIA'] ?? null;
        $this->superficie          = $commercioDettaglio['SUPERFICIE'] ?? null;
        $this->settoreMerceologico = $commercioDettaglio['SETTORE_MERCEOLOGICO'] ?? null;
    }

    /**
     * @return string
     */
    public function getDtDenuncia(): ?string
    {
        return $this->dtDenuncia;
    }

    /**
     * @param string $dtDenuncia
     *
     * @return AdrierCommercioDettaglio
     */
    public function setDtDenuncia(string $dtDenuncia): AdrierCommercioDettaglio
    {
        $this->dtDenuncia = $dtDenuncia;

        return $this;
    }

    /**
     * @return string
     */
    public function getSuperficie(): ?string
    {
        return $this->superficie;
    }

    /**
     * @param string $superficie
     *
     * @return AdrierCommercioDettaglio
     */
    public function setSuperficie(string $superficie): AdrierCommercioDettaglio
    {
        $this->superficie = $superficie;

        return $this;
    }

    /**
     * @return string
     */
    public function getSettoreMerceologico(): ?string
    {
        return $this->settoreMerceologico;
    }

    /**
     * @param string $settoreMerceologico
     *
     * @return AdrierCommercioDettaglio
     */
    public function setSettoreMerceologico(string $settoreMerceologico): AdrierCommercioDettaglio
    {
        $this->settoreMerceologico = $settoreMerceologico;

        return $this;
    }
}

/**
 * Class AdrierDatiArtigiani
 */
class AdrierDatiArtigiani
{
    /**
     * @var string
     */
    protected $nAa;

    /**
     * @var AdrierIscrizioneArti
     */
    protected $iscrizioneArti;

    /**
     * @var string
     */
    protected $dtInizioAttivita;

    /**
     * @var AdrierCessazioneArti
     */
    protected $cessazioneArti;

    /**
     * AdrierDatiArtigiani constructor.
     */
    public function __construct(?array $datiArtigiani)
    {
        $this->nAa = $datiArtigiani['N_AA'] ?? null;
        $this->nAa = new AdrierIscrizioneArti($datiArtigiani['ISCRIZIONE_ARTI'] ?? null);
        $this->nAa = $datiArtigiani['DT_INIZIO_ATTIVITA'] ?? null;
        $this->nAa = new AdrierCessazioneArti($datiArtigiani['CESSAZIONE_ARTI'] ?? null);
    }

    /**
     * @return string
     */
    public function getNAa(): ?string
    {
        return $this->nAa;
    }

    /**
     * @param string $nAa
     *
     * @return AdrierDatiArtigiani
     */
    public function setNAa(string $nAa): AdrierDatiArtigiani
    {
        $this->nAa = $nAa;

        return $this;
    }

    /**
     * @return AdrierIscrizioneArti
     */
    public function getIscrizioneArti(): ?AdrierIscrizioneArti
    {
        return $this->iscrizioneArti;
    }

    /**
     * @param AdrierIscrizioneArti $iscrizioneArti
     *
     * @return AdrierDatiArtigiani
     */
    public function setIscrizioneArti(AdrierIscrizioneArti $iscrizioneArti): AdrierDatiArtigiani
    {
        $this->iscrizioneArti = $iscrizioneArti;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtInizioAttivita(): ?string
    {
        return $this->dtInizioAttivita;
    }

    /**
     * @param string $dtInizioAttivita
     *
     * @return AdrierDatiArtigiani
     */
    public function setDtInizioAttivita(string $dtInizioAttivita): AdrierDatiArtigiani
    {
        $this->dtInizioAttivita = $dtInizioAttivita;

        return $this;
    }

    /**
     * @return AdrierCessazioneArti
     */
    public function getCessazioneArti(): ?AdrierCessazioneArti
    {
        return $this->cessazioneArti;
    }

    /**
     * @param AdrierCessazioneArti $cessazioneArti
     *
     * @return AdrierDatiArtigiani
     */
    public function setCessazioneArti(AdrierCessazioneArti $cessazioneArti): AdrierDatiArtigiani
    {
        $this->cessazioneArti = $cessazioneArti;

        return $this;
    }
}

/**
 * Class AdrierCessazioneArti
 */
class AdrierCessazioneArti
{
    /**
     * @var string
     */
    protected $causale;

    /**
     * @var string
     */
    protected $dtDomanda;

    /**
     * @var string
     */
    protected $dtDelibera;

    /**
     * @var string
     */
    protected $dtCessazione;

    /**
     * AdrierCessazioneArti constructor.
     */
    public function __construct(?array $cessazioneArti)
    {
        $this->causale      = $cessazioneArti['CAUSALE'] ?? null;
        $this->dtDomanda    = $cessazioneArti['DT_DOMANDA'] ?? null;
        $this->dtDelibera   = $cessazioneArti['DT_DELIBERA'] ?? null;
        $this->dtCessazione = $cessazioneArti['DT_CESSAZIONE'] ?? null;
    }

    /**
     * @return string
     */
    public function getCausale(): ?string
    {
        return $this->causale;
    }

    /**
     * @param string $causale
     *
     * @return AdrierCessazioneArti
     */
    public function setCausale(string $causale): AdrierCessazioneArti
    {
        $this->causale = $causale;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtDomanda(): ?string
    {
        return $this->dtDomanda;
    }

    /**
     * @param string $dtDomanda
     *
     * @return AdrierCessazioneArti
     */
    public function setDtDomanda(string $dtDomanda): AdrierCessazioneArti
    {
        $this->dtDomanda = $dtDomanda;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtDelibera(): ?string
    {
        return $this->dtDelibera;
    }

    /**
     * @param string $dtDelibera
     *
     * @return AdrierCessazioneArti
     */
    public function setDtDelibera(string $dtDelibera): AdrierCessazioneArti
    {
        $this->dtDelibera = $dtDelibera;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtCessazione(): ?string
    {
        return $this->dtCessazione;
    }

    /**
     * @param string $dtCessazione
     *
     * @return AdrierCessazioneArti
     */
    public function setDtCessazione(string $dtCessazione): AdrierCessazioneArti
    {
        $this->dtCessazione = $dtCessazione;

        return $this;
    }
}

/**
 * Class AdrierIscrizioneArti
 */
class AdrierIscrizioneArti
{
    /**
     * @var string
     */
    protected $provincia;

    /**
     * @var string
     */
    protected $dtDomanda;

    /**
     * @var string
     */
    protected $dtDelibera;

    /**
     * AdrierIscrizioneArti constructor.
     */
    public function __construct(?array $iscrizioneArti)
    {
        $this->provincia  = $iscrizioneArti['PROVINCIA'] ?? null;
        $this->dtDomanda  = $iscrizioneArti['DT_DOMANDA'] ?? null;
        $this->dtDelibera = $iscrizioneArti['DT_DELIBERA'] ?? null;
    }

    /**
     * @return string
     */
    public function getProvincia(): ?string
    {
        return $this->provincia;
    }

    /**
     * @param string $provincia
     *
     * @return AdrierIscrizioneArti
     */
    public function setProvincia(string $provincia): AdrierIscrizioneArti
    {
        $this->provincia = $provincia;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtDomanda(): ?string
    {
        return $this->dtDomanda;
    }

    /**
     * @param string $dtDomanda
     *
     * @return AdrierIscrizioneArti
     */
    public function setDtDomanda(string $dtDomanda): AdrierIscrizioneArti
    {
        $this->dtDomanda = $dtDomanda;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtDelibera(): ?string
    {
        return $this->dtDelibera;
    }

    /**
     * @param string $dtDelibera
     *
     * @return AdrierIscrizioneArti
     */
    public function setDtDelibera(string $dtDelibera): AdrierIscrizioneArti
    {
        $this->dtDelibera = $dtDelibera;

        return $this;
    }
}

/**
 * Class AdrierRuoloLoc
 */
class AdrierRuoloLoc
{
    /**
     * @var AdrierImpiantistiLoc
     */
    protected $impiantistiLoc;

    /**
     * @var AdrierMeccanici
     */
    protected $meccanici;

    /**
     * @var AdrierImpresePulizia
     */
    protected $impresePulizia;

    /**
     * @var AdrierAltroRuoloLoc
     */
    protected $altroRuoloLoc;

    /**
     * @var AdrierCessazioneRuolo
     */
    protected $cessazioneRuolo;

    /**
     * AdrierRuoloLoc constructor.
     */
    public function __construct(?array $ruoloLoc)
    {
        $this->impiantistiLoc  = new AdrierImpiantistiLoc($ruoloLoc['IMPIANTISTI_LOC'] ?? null);
        $this->meccanici       = new AdrierMeccanici($ruoloLoc['MECCANICI'] ?? null);
        $this->impresePulizia  = new AdrierImpresePulizia($ruoloLoc['IMPRESE_PULIZIA'] ?? null);
        $this->altroRuoloLoc   = new AdrierAltroRuoloLoc($ruoloLoc['ALTRO_RUOLO_LOC'] ?? null);
        $this->cessazioneRuolo = new AdrierCessazioneRuolo($ruoloLoc['CESSAZIONE_RUOLO'] ?? null);
    }

    /**
     * @return AdrierImpiantistiLoc
     */
    public function getImpiantistiLoc(): ?AdrierImpiantistiLoc
    {
        return $this->impiantistiLoc;
    }

    /**
     * @param AdrierImpiantistiLoc $impiantistiLoc
     *
     * @return AdrierRuoloLoc
     */
    public function setImpiantistiLoc(AdrierImpiantistiLoc $impiantistiLoc): AdrierRuoloLoc
    {
        $this->impiantistiLoc = $impiantistiLoc;

        return $this;
    }

    /**
     * @return AdrierMeccanici
     */
    public function getMeccanici(): ?AdrierMeccanici
    {
        return $this->meccanici;
    }

    /**
     * @param AdrierMeccanici $meccanici
     *
     * @return AdrierRuoloLoc
     */
    public function setMeccanici(AdrierMeccanici $meccanici): AdrierRuoloLoc
    {
        $this->meccanici = $meccanici;

        return $this;
    }

    /**
     * @return AdrierImpresePulizia
     */
    public function getImpresePulizia(): ?AdrierImpresePulizia
    {
        return $this->impresePulizia;
    }

    /**
     * @param AdrierImpresePulizia $impresePulizia
     *
     * @return AdrierRuoloLoc
     */
    public function setImpresePulizia(AdrierImpresePulizia $impresePulizia): AdrierRuoloLoc
    {
        $this->impresePulizia = $impresePulizia;

        return $this;
    }

    /**
     * @return AdrierAltroRuoloLoc
     */
    public function getAltroRuoloLoc(): ?AdrierAltroRuoloLoc
    {
        return $this->altroRuoloLoc;
    }

    /**
     * @param AdrierAltroRuoloLoc $altroRuoloLoc
     *
     * @return AdrierRuoloLoc
     */
    public function setAltroRuoloLoc(AdrierAltroRuoloLoc $altroRuoloLoc): AdrierRuoloLoc
    {
        $this->altroRuoloLoc = $altroRuoloLoc;

        return $this;
    }

    /**
     * @return AdrierCessazioneRuolo
     */
    public function getCessazioneRuolo(): ?AdrierCessazioneRuolo
    {
        return $this->cessazioneRuolo;
    }

    /**
     * @param AdrierCessazioneRuolo $cessazioneRuolo
     *
     * @return AdrierRuoloLoc
     */
    public function setCessazioneRuolo(AdrierCessazioneRuolo $cessazioneRuolo): AdrierRuoloLoc
    {
        $this->cessazioneRuolo = $cessazioneRuolo;

        return $this;
    }
}

/**
 * Class AdrierAltroRuoloLoc
 */
class AdrierAltroRuoloLoc
{
    /**
     * @var string
     */
    protected $cRuolo;

    /**
     * @var string
     */
    protected $descrizione;

    /**
     * @var string
     */
    protected $ultDescrizione;

    /**
     * @var string
     */
    protected $numero;

    /**
     * @var string
     */
    protected $dtIscrizione;

    /**
     * @var AdrierAltroRuoloNonCciaa
     */
    protected $altroRuoloNonCciaa;

    /**
     * @var AdrierAltroRuoloCciaa
     */
    protected $altroRuoloCciaa;

    /**
     * AdrierAltroRuoloLoc constructor.
     */
    public function __construct(?array $altroRuoloLoc)
    {
        $this->cRuolo             = $altroRuoloLoc['C_RUOLO'] ?? null;
        $this->descrizione        = $altroRuoloLoc['DESCRIZIONE'] ?? null;
        $this->ultDescrizione     = $altroRuoloLoc['ULT_DESCRIZIONE'] ?? null;
        $this->numero             = $altroRuoloLoc['NUMERO'] ?? null;
        $this->dtIscrizione       = $altroRuoloLoc['DT_ISCRIZIONE'] ?? null;
        $this->altroRuoloNonCciaa = new AdrierAltroRuoloNonCciaa($altroRuoloLoc['ALTRO_RUOLO_NON_CCIAA'] ?? null);
        $this->altroRuoloCciaa    = new AdrierAltroRuoloCciaa($altroRuoloLoc['ALTRO_RUOLO_CCIAA'] ?? null);
    }

    /**
     * @return string
     */
    public function getCRuolo(): ?string
    {
        return $this->cRuolo;
    }

    /**
     * @param string $cRuolo
     *
     * @return AdrierAltroRuoloLoc
     */
    public function setCRuolo(string $cRuolo): AdrierAltroRuoloLoc
    {
        $this->cRuolo = $cRuolo;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescrizione(): ?string
    {
        return $this->descrizione;
    }

    /**
     * @param string $descrizione
     *
     * @return AdrierAltroRuoloLoc
     */
    public function setDescrizione(string $descrizione): AdrierAltroRuoloLoc
    {
        $this->descrizione = $descrizione;

        return $this;
    }

    /**
     * @return string
     */
    public function getUltDescrizione(): ?string
    {
        return $this->ultDescrizione;
    }

    /**
     * @param string $ultDescrizione
     *
     * @return AdrierAltroRuoloLoc
     */
    public function setUltDescrizione(string $ultDescrizione): AdrierAltroRuoloLoc
    {
        $this->ultDescrizione = $ultDescrizione;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumero(): ?string
    {
        return $this->numero;
    }

    /**
     * @param string $numero
     *
     * @return AdrierAltroRuoloLoc
     */
    public function setNumero(string $numero): AdrierAltroRuoloLoc
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtIscrizione(): ?string
    {
        return $this->dtIscrizione;
    }

    /**
     * @param string $dtIscrizione
     *
     * @return AdrierAltroRuoloLoc
     */
    public function setDtIscrizione(string $dtIscrizione): AdrierAltroRuoloLoc
    {
        $this->dtIscrizione = $dtIscrizione;

        return $this;
    }

    /**
     * @return AdrierAltroRuoloNonCciaa
     */
    public function getAltroRuoloNonCciaa(): ?AdrierAltroRuoloNonCciaa
    {
        return $this->altroRuoloNonCciaa;
    }

    /**
     * @param AdrierAltroRuoloNonCciaa $altroRuoloNonCciaa
     *
     * @return AdrierAltroRuoloLoc
     */
    public function setAltroRuoloNonCciaa(AdrierAltroRuoloNonCciaa $altroRuoloNonCciaa): AdrierAltroRuoloLoc
    {
        $this->altroRuoloNonCciaa = $altroRuoloNonCciaa;

        return $this;
    }

    /**
     * @return AdrierAltroRuoloCciaa
     */
    public function getAltroRuoloCciaa(): ?AdrierAltroRuoloCciaa
    {
        return $this->altroRuoloCciaa;
    }

    /**
     * @param AdrierAltroRuoloCciaa $altroRuoloCciaa
     *
     * @return AdrierAltroRuoloLoc
     */
    public function setAltroRuoloCciaa(AdrierAltroRuoloCciaa $altroRuoloCciaa): AdrierAltroRuoloLoc
    {
        $this->altroRuoloCciaa = $altroRuoloCciaa;

        return $this;
    }
}

/**
 * Class AdrierCessazioneRuolo
 */
class AdrierCessazioneRuolo
{
    /**
     * @var string
     */
    protected $causaleCessazione;

    /**
     * @var string
     */
    protected $dtDomanda;

    /**
     * @var string
     */
    protected $dtDelibera;

    /**
     * @var string
     */
    protected $dtCessazione;

    /**
     * AdrierCessazioneRuolo constructor.
     */
    public function __construct(?array $cessazione)
    {
        $this->causaleCessazione = $cessazione['CAUSALE_CESSAZIONE'] ?? null;
        $this->dtDomanda         = $cessazione['DT_DOMANDA'] ?? null;
        $this->dtDelibera        = $cessazione['DT_DELIBERA'] ?? null;
        $this->dtCessazione      = $cessazione['DT_CESSAZIONE'] ?? null;
    }

    /**
     * @return string
     */
    public function getCausaleCessazione(): ?string
    {
        return $this->causaleCessazione;
    }

    /**
     * @param string $causaleCessazione
     *
     * @return AdrierCessazioneRuolo
     */
    public function setCausaleCessazione(string $causaleCessazione): AdrierCessazioneRuolo
    {
        $this->causaleCessazione = $causaleCessazione;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtDomanda(): ?string
    {
        return $this->dtDomanda;
    }

    /**
     * @param string $dtDomanda
     *
     * @return AdrierCessazioneRuolo
     */
    public function setDtDomanda(string $dtDomanda): AdrierCessazioneRuolo
    {
        $this->dtDomanda = $dtDomanda;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtDelibera(): ?string
    {
        return $this->dtDelibera;
    }

    /**
     * @param string $dtDelibera
     *
     * @return AdrierCessazioneRuolo
     */
    public function setDtDelibera(string $dtDelibera): AdrierCessazioneRuolo
    {
        $this->dtDelibera = $dtDelibera;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtCessazione(): ?string
    {
        return $this->dtCessazione;
    }

    /**
     * @param string $dtCessazione
     *
     * @return AdrierCessazioneRuolo
     */
    public function setDtCessazione(string $dtCessazione): AdrierCessazioneRuolo
    {
        $this->dtCessazione = $dtCessazione;

        return $this;
    }
}

/**
 * Class AdrierAltroRuoloNonCciaa
 */
class AdrierAltroRuoloNonCciaa
{
    /**
     * @var string
     */
    protected $forma;

    /**
     * @var string
     */
    protected $enteRilascio;

    /**
     * @var string
     */
    protected $provincia;

    /**
     * AdrierAltroRuoloNonCciaa constructor.
     */
    public function __construct(?array $altroRuoloNonCciaa)
    {
        $this->forma        = $altroRuoloNonCciaa['FORMA'] ?? null;
        $this->enteRilascio = $altroRuoloNonCciaa['ENTE_RILASCIO'] ?? null;
        $this->provincia    = $altroRuoloNonCciaa['PROVINCIA'] ?? null;
    }

    /**
     * @return string
     */
    public function getForma(): ?string
    {
        return $this->forma;
    }

    /**
     * @param string $forma
     *
     * @return AdrierAltroRuoloNonCciaa
     */
    public function setForma(string $forma): AdrierAltroRuoloNonCciaa
    {
        $this->forma = $forma;

        return $this;
    }

    /**
     * @return string
     */
    public function getEnteRilascio(): ?string
    {
        return $this->enteRilascio;
    }

    /**
     * @param string $enteRilascio
     *
     * @return AdrierAltroRuoloNonCciaa
     */
    public function setEnteRilascio(string $enteRilascio): AdrierAltroRuoloNonCciaa
    {
        $this->enteRilascio = $enteRilascio;

        return $this;
    }

    /**
     * @return string
     */
    public function getProvincia(): ?string
    {
        return $this->provincia;
    }

    /**
     * @param string $provincia
     *
     * @return AdrierAltroRuoloNonCciaa
     */
    public function setProvincia(string $provincia): AdrierAltroRuoloNonCciaa
    {
        $this->provincia = $provincia;

        return $this;
    }
}

/**
 * Class AdrierAltroRuoloCciaa
 */
class AdrierAltroRuoloCciaa
{
    /**
     * @var string
     */
    protected $categoria;

    /**
     * @var string
     */
    protected $forma;

    /**
     * @var string
     */
    protected $provincia;

    /**
     * AdrierAltroRuoloCciaa constructor.
     */
    public function __construct(?array $altroRuoloCciaa)
    {
        $this->categoria = $altroRuoloCciaa['CATEGORIA'] ?? null;
        $this->forma     = $altroRuoloCciaa['FORMA'] ?? null;
        $this->provincia = $altroRuoloCciaa['PROVINCIA'] ?? null;
    }

    /**
     * @return string
     */
    public function getCategoria(): ?string
    {
        return $this->categoria;
    }

    /**
     * @param string $categoria
     *
     * @return AdrierAltroRuoloCciaa
     */
    public function setCategoria(string $categoria): AdrierAltroRuoloCciaa
    {
        $this->categoria = $categoria;

        return $this;
    }

    /**
     * @return string
     */
    public function getForma(): ?string
    {
        return $this->forma;
    }

    /**
     * @param string $forma
     *
     * @return AdrierAltroRuoloCciaa
     */
    public function setForma(string $forma): AdrierAltroRuoloCciaa
    {
        $this->forma = $forma;

        return $this;
    }

    /**
     * @return string
     */
    public function getProvincia(): ?string
    {
        return $this->provincia;
    }

    /**
     * @param string $provincia
     *
     * @return AdrierAltroRuoloCciaa
     */
    public function setProvincia(string $provincia): AdrierAltroRuoloCciaa
    {
        $this->provincia = $provincia;

        return $this;
    }
}

/**
 * Class AdrierImpresePulizia
 */
class AdrierImpresePulizia
{
    /**
     * @var string
     */
    protected $fascia;

    /**
     * @var string
     */
    protected $volume;

    /**
     * @var string
     */
    protected $dtDenuncia;

    /**
     * AdrierImpresePulizia constructor.
     */
    public function __construct(?array $impresePulizia)
    {
        $this->fascia     = $impresePulizia['FASCIA'] ?? null;
        $this->volume     = $impresePulizia['VOLUME'] ?? null;
        $this->dtDenuncia = $impresePulizia['DT_DENUNCIA'] ?? null;
    }

    /**
     * @return string
     */
    public function getFascia(): ?string
    {
        return $this->fascia;
    }

    /**
     * @param string $fascia
     *
     * @return AdrierImpresePulizia
     */
    public function setFascia(string $fascia): AdrierImpresePulizia
    {
        $this->fascia = $fascia;

        return $this;
    }

    /**
     * @return string
     */
    public function getVolume(): ?string
    {
        return $this->volume;
    }

    /**
     * @param string $volume
     *
     * @return AdrierImpresePulizia
     */
    public function setVolume(string $volume): AdrierImpresePulizia
    {
        $this->volume = $volume;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtDenuncia(): ?string
    {
        return $this->dtDenuncia;
    }

    /**
     * @param string $dtDenuncia
     *
     * @return AdrierImpresePulizia
     */
    public function setDtDenuncia(string $dtDenuncia): AdrierImpresePulizia
    {
        $this->dtDenuncia = $dtDenuncia;

        return $this;
    }
}

/**
 * Class AdrierMeccanici
 */
class AdrierMeccanici
{
    /**
     * @var string
     */
    protected $tipo;

    /**
     * @var string
     */
    protected $provincia;

    /**
     * @var string
     */
    protected $numero;

    /**
     * @var string
     */
    protected $dtIscrizione;

    /**
     * @var string
     */
    protected $qualifica;

    /**
     * AdrierMeccanici constructor.
     */
    public function __construct(?array $meccanici)
    {
        $this->tipo         = $meccanici['TIPO'] ?? null;
        $this->provincia    = $meccanici['PROVINCIA'] ?? null;
        $this->numero       = $meccanici['NUMERO'] ?? null;
        $this->dtIscrizione = $meccanici['DT_ISCRIZIONE'] ?? null;
        $this->qualifica    = $meccanici['QUALIFICA'] ?? null;
    }

    /**
     * @return string
     */
    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    /**
     * @param string $tipo
     *
     * @return AdrierMeccanici
     */
    public function setTipo(string $tipo): AdrierMeccanici
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * @return string
     */
    public function getProvincia(): ?string
    {
        return $this->provincia;
    }

    /**
     * @param string $provincia
     *
     * @return AdrierMeccanici
     */
    public function setProvincia(string $provincia): AdrierMeccanici
    {
        $this->provincia = $provincia;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumero(): ?string
    {
        return $this->numero;
    }

    /**
     * @param string $numero
     *
     * @return AdrierMeccanici
     */
    public function setNumero(string $numero): AdrierMeccanici
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtIscrizione(): ?string
    {
        return $this->dtIscrizione;
    }

    /**
     * @param string $dtIscrizione
     *
     * @return AdrierMeccanici
     */
    public function setDtIscrizione(string $dtIscrizione): AdrierMeccanici
    {
        $this->dtIscrizione = $dtIscrizione;

        return $this;
    }

    /**
     * @return string
     */
    public function getQualifica(): ?string
    {
        return $this->qualifica;
    }

    /**
     * @param string $qualifica
     *
     * @return AdrierMeccanici
     */
    public function setQualifica(string $qualifica): AdrierMeccanici
    {
        $this->qualifica = $qualifica;

        return $this;
    }
}

/**
 * Class AdrierImpiantistiLoc
 */
class AdrierImpiantistiLoc
{
    /**
     * @var string
     */
    protected $lettera;

    /**
     * @var string
     */
    protected $provincia;

    /**
     * @var string
     */
    protected $numero;

    /**
     * @var string
     */
    protected $dtIscrizione;

    /**
     * @var string
     */
    protected $enteRilascio;

    /**
     * AdrierImpiantistiLoc constructor.
     */
    public function __construct(?array $impiantisti)
    {
        $this->lettera      = $impiantisti['LETTERA'] ?? null;
        $this->provincia    = $impiantisti['PROVINCIA'] ?? null;
        $this->numero       = $impiantisti['NUMERO'] ?? null;
        $this->dtIscrizione = $impiantisti['DT_ISCRIZIONE'] ?? null;
        $this->enteRilascio = $impiantisti['ENTE_RILASCIO'] ?? null;
    }

    /**
     * @return string
     */
    public function getLettera(): ?string
    {
        return $this->lettera;
    }

    /**
     * @param string $lettera
     *
     * @return AdrierImpiantistiLoc
     */
    public function setLettera(string $lettera): AdrierImpiantistiLoc
    {
        $this->lettera = $lettera;

        return $this;
    }

    /**
     * @return string
     */
    public function getProvincia(): ?string
    {
        return $this->provincia;
    }

    /**
     * @param string $provincia
     *
     * @return AdrierImpiantistiLoc
     */
    public function setProvincia(string $provincia): AdrierImpiantistiLoc
    {
        $this->provincia = $provincia;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumero(): ?string
    {
        return $this->numero;
    }

    /**
     * @param string $numero
     *
     * @return AdrierImpiantistiLoc
     */
    public function setNumero(string $numero): AdrierImpiantistiLoc
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtIscrizione(): ?string
    {
        return $this->dtIscrizione;
    }

    /**
     * @param string $dtIscrizione
     *
     * @return AdrierImpiantistiLoc
     */
    public function setDtIscrizione(string $dtIscrizione): AdrierImpiantistiLoc
    {
        $this->dtIscrizione = $dtIscrizione;

        return $this;
    }

    /**
     * @return string
     */
    public function getEnteRilascio(): ?string
    {
        return $this->enteRilascio;
    }

    /**
     * @param string $enteRilascio
     *
     * @return AdrierImpiantistiLoc
     */
    public function setEnteRilascio(string $enteRilascio): AdrierImpiantistiLoc
    {
        $this->enteRilascio = $enteRilascio;

        return $this;
    }
}

/**
 * Class AdrierAttivitaIstat
 */
class AdrierAttivitaIstat
{
    /**
     * @var string
     */
    protected $cAttivita;

    /**
     * @var string
     */
    protected $tCodifica;

    /**
     * @var string
     */
    protected $cFonte;

    /**
     * @var string
     */
    protected $descAttivita;

    /**
     * @var string
     */
    protected $cImportanza;

    /**
     * @var string
     */
    protected $dtInizioAttivita;

    /**
     * AdrierAttivitaIstat constructor.
     */
    public function __construct(?array $attivitaIstat)
    {
        $this->cAttivita        = $attivitaIstat['C_ATTIVITA'] ?? null;
        $this->tCodifica        = $attivitaIstat['T_CODIFICA'] ?? null;
        $this->cFonte           = $attivitaIstat['C_FONTE'] ?? null;
        $this->descAttivita     = $attivitaIstat['DESC_ATTIVITA'] ?? null;
        $this->cImportanza      = $attivitaIstat['C_IMPORTANZA'] ?? null;
        $this->dtInizioAttivita = $attivitaIstat['DT_INIZIO_ATTIVITA'] ?? null;
    }

    /**
     * @return string
     */
    public function getCAttivita(): ?string
    {
        return $this->cAttivita;
    }

    /**
     * @param string $cAttivita
     *
     * @return AdrierAttivitaIstat
     */
    public function setCAttivita(string $cAttivita): AdrierAttivitaIstat
    {
        $this->cAttivita = $cAttivita;

        return $this;
    }

    /**
     * @return string
     */
    public function getTCodifica(): ?string
    {
        return $this->tCodifica;
    }

    /**
     * @param string $tCodifica
     *
     * @return AdrierAttivitaIstat
     */
    public function setTCodifica(string $tCodifica): AdrierAttivitaIstat
    {
        $this->tCodifica = $tCodifica;

        return $this;
    }

    /**
     * @return string
     */
    public function getCFonte(): ?string
    {
        return $this->cFonte;
    }

    /**
     * @param string $cFonte
     *
     * @return AdrierAttivitaIstat
     */
    public function setCFonte(string $cFonte): AdrierAttivitaIstat
    {
        $this->cFonte = $cFonte;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescAttivita(): ?string
    {
        return $this->descAttivita;
    }

    /**
     * @param string $descAttivita
     *
     * @return AdrierAttivitaIstat
     */
    public function setDescAttivita(string $descAttivita): AdrierAttivitaIstat
    {
        $this->descAttivita = $descAttivita;

        return $this;
    }

    /**
     * @return string
     */
    public function getCImportanza(): ?string
    {
        return $this->cImportanza;
    }

    /**
     * @param string $cImportanza
     *
     * @return AdrierAttivitaIstat
     */
    public function setCImportanza(string $cImportanza): AdrierAttivitaIstat
    {
        $this->cImportanza = $cImportanza;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtInizioAttivita(): ?string
    {
        return $this->dtInizioAttivita;
    }

    /**
     * @param string $dtInizioAttivita
     *
     * @return AdrierAttivitaIstat
     */
    public function setDtInizioAttivita(string $dtInizioAttivita): AdrierAttivitaIstat
    {
        $this->dtInizioAttivita = $dtInizioAttivita;

        return $this;
    }
}

/**
 * Class AdrierIndirizzo
 */
class AdrierIndirizzo
{
    /**
     * @var string
     */
    protected $provincia;

    /**
     * @var string
     */
    protected $comune;

    /**
     * @var string
     */
    protected $cComune;

    /**
     * @var string
     */
    protected $toponimo;

    /**
     * @var string
     */
    protected $via;

    /**
     * @var string
     */
    protected $nCivico;

    /**
     * @var string
     */
    protected $cap;

    /**
     * @var string
     */
    protected $stato;

    /**
     * @var string
     */
    protected $frazione;

    /**
     * @var string
     */
    protected $altreIndicazioni;
    /**
     * @var string
     */
    protected $stradario;

    /**
     * @var string
     */
    protected $telefono;

    /**
     * @var string
     */
    protected $fax;

    /**
     * @var string
     */
    protected $indirizzoPec;

    /**
     * AdrierIndirizzo constructor.
     */
    public function __construct(?array $indirizzo)
    {
        $this->provincia        = $indirizzo['PROVINCIA'] ?? null;
        $this->comune           = $indirizzo['COMUNE'] ?? null;
        $this->cComune          = $indirizzo['C_COMUNE'] ?? null;
        $this->toponimo         = $indirizzo['TOPONIMO'] ?? null;
        $this->via              = $indirizzo['VIA'] ?? null;
        $this->nCivico          = $indirizzo['N_CIVICO'] ?? null;
        $this->cap              = $indirizzo['CAP'] ?? null;
        $this->stato            = $indirizzo['STATO'] ?? null;
        $this->frazione         = $indirizzo['FRAZIONE'] ?? null;
        $this->altreIndicazioni = $indirizzo['ALTRE_INDICAZIONI'] ?? null;
        $this->stradario        = $indirizzo['STRADARIO'] ?? null;
        $this->telefono         = $indirizzo['TELEFONO'] ?? null;
        $this->fax              = $indirizzo['FAX'] ?? null;
        $this->indirizzoPec     = $indirizzo['INDIRIZZO_PEC'] ?? null;
    }

    /**
     * @return string
     */
    public function getProvincia(): ?string
    {
        return $this->provincia;
    }

    /**
     * @param string $provincia
     *
     * @return AdrierIndirizzo
     */
    public function setProvincia(string $provincia): AdrierIndirizzo
    {
        $this->provincia = $provincia;

        return $this;
    }

    /**
     * @return string
     */
    public function getComune(): ?string
    {
        return $this->comune;
    }

    /**
     * @param string $comune
     *
     * @return AdrierIndirizzo
     */
    public function setComune(string $comune): AdrierIndirizzo
    {
        $this->comune = $comune;

        return $this;
    }

    /**
     * @return string
     */
    public function getCComune(): ?string
    {
        return $this->cComune;
    }

    /**
     * @param string $cComune
     *
     * @return AdrierIndirizzo
     */
    public function setCComune(string $cComune): AdrierIndirizzo
    {
        $this->cComune = $cComune;

        return $this;
    }

    /**
     * @return string
     */
    public function getToponimo(): ?string
    {
        return $this->toponimo;
    }

    /**
     * @param string $toponimo
     *
     * @return AdrierIndirizzo
     */
    public function setToponimo(string $toponimo): AdrierIndirizzo
    {
        $this->toponimo = $toponimo;

        return $this;
    }

    /**
     * @return string
     */
    public function getVia(): ?string
    {
        return $this->via;
    }

    /**
     * @param string $via
     *
     * @return AdrierIndirizzo
     */
    public function setVia(string $via): AdrierIndirizzo
    {
        $this->via = $via;

        return $this;
    }

    /**
     * @return string
     */
    public function getNCivico(): ?string
    {
        return $this->nCivico;
    }

    /**
     * @param string $nCivico
     *
     * @return AdrierIndirizzo
     */
    public function setNCivico(string $nCivico): AdrierIndirizzo
    {
        $this->nCivico = $nCivico;

        return $this;
    }

    /**
     * @return string
     */
    public function getCap(): ?string
    {
        return $this->cap;
    }

    /**
     * @param string $cap
     *
     * @return AdrierIndirizzo
     */
    public function setCap(string $cap): AdrierIndirizzo
    {
        $this->cap = $cap;

        return $this;
    }

    /**
     * @return string
     */
    public function getStato(): ?string
    {
        return $this->stato;
    }

    /**
     * @param string $stato
     *
     * @return AdrierIndirizzo
     */
    public function setStato(string $stato): AdrierIndirizzo
    {
        $this->stato = $stato;

        return $this;
    }

    /**
     * @return string
     */
    public function getFrazione(): ?string
    {
        return $this->frazione;
    }

    /**
     * @param string $frazione
     *
     * @return AdrierIndirizzo
     */
    public function setFrazione(string $frazione): AdrierIndirizzo
    {
        $this->frazione = $frazione;

        return $this;
    }

    /**
     * @return string
     */
    public function getAltreIndicazioni(): ?string
    {
        return $this->altreIndicazioni;
    }

    /**
     * @param string $altreIndicazioni
     *
     * @return AdrierIndirizzo
     */
    public function setAltreIndicazioni(string $altreIndicazioni): AdrierIndirizzo
    {
        $this->altreIndicazioni = $altreIndicazioni;

        return $this;
    }

    /**
     * @return string
     */
    public function getStradario(): ?string
    {
        return $this->stradario;
    }

    /**
     * @param string $stradario
     *
     * @return AdrierIndirizzo
     */
    public function setStradario(string $stradario): AdrierIndirizzo
    {
        $this->stradario = $stradario;

        return $this;
    }

    /**
     * @return string
     */
    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    /**
     * @param string $telefono
     *
     * @return AdrierIndirizzo
     */
    public function setTelefono(string $telefono): AdrierIndirizzo
    {
        $this->telefono = $telefono;

        return $this;
    }

    /**
     * @return string
     */
    public function getFax(): ?string
    {
        return $this->fax;
    }

    /**
     * @param string $fax
     *
     * @return AdrierIndirizzo
     */
    public function setFax(string $fax): AdrierIndirizzo
    {
        $this->fax = $fax;

        return $this;
    }

    /**
     * @return string
     */
    public function getIndirizzoPec(): ?string
    {
        return $this->indirizzoPec;
    }

    /**
     * @param string $indirizzoPec
     *
     * @return AdrierIndirizzo
     */
    public function setIndirizzoPec(string $indirizzoPec): AdrierIndirizzo
    {
        $this->indirizzoPec = $indirizzoPec;

        return $this;
    }
}

/**
 * Class AdrierInfoAttivita
 */
class AdrierInfoAttivita
{
    /**
     * @var string
     */
    protected $dtInizioAttivita;

    /**
     * @var string
     */
    protected $statoAttivita;

    /**
     * AdrierInfoAttivita constructor.
     */
    public function __construct(?array $infoAttivita)
    {
        $this->dtInizioAttivita = $infoAttivita['DT_INIZIO_ATTIVITA'] ?? null;
        $this->statoAttivita    = $infoAttivita['STATO_ATTIVITA'] ?? null;
    }

    /**
     * @return string
     */
    public function getDtInizioAttivita(): ?string
    {
        return $this->dtInizioAttivita;
    }

    /**
     * @param string $dtInizioAttivita
     *
     * @return AdrierInfoAttivita
     */
    public function setDtInizioAttivita(string $dtInizioAttivita): AdrierInfoAttivita
    {
        $this->dtInizioAttivita = $dtInizioAttivita;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatoAttivita(): ?string
    {
        return $this->statoAttivita;
    }

    /**
     * @param string $statoAttivita
     *
     * @return AdrierInfoAttivita
     */
    public function setStatoAttivita(string $statoAttivita): AdrierInfoAttivita
    {
        $this->statoAttivita = $statoAttivita;

        return $this;
    }
}

/**
 * Class AdrierPersoneSede
 */
class AdrierPersoneSede
{
    /**
     * @var int
     */
    protected $totale;

    /**
     * @var AdrierPersona[]|ArrayCollection
     */
    protected $persona;

    /**
     * AdrierPersoneSede constructor.
     */
    public function __construct(?array $personeSede)
    {
        $this->totale  = $personeSede['@totale'] ?? null;
        $this->persona = new ArrayCollection();

        if (isset($personeSede['PERSONA'])) {
            if($this->totale > 1) {
                foreach ($personeSede['PERSONA'] as $persona) {
                    $this->persona->add(new AdrierPersona($persona ?? null));
                }
            } else {
                $this->persona->add(new AdrierPersona($personeSede['PERSONA'] ?? null));
            }
        }
    }

    /**
     * @return int
     */
    public function getTotale(): ?int
    {
        return $this->totale;
    }

    /**
     * @param int $totale
     *
     * @return AdrierPersoneSede
     */
    public function setTotale(int $totale): AdrierPersoneSede
    {
        $this->totale = $totale;

        return $this;
    }

    /**
     * @return ArrayCollection|AdrierPersona[]
     */
    public function getPersona()
    {
        return $this->persona;
    }

    /**
     * @param ArrayCollection|AdrierPersona[] $persona
     *
     * @return AdrierPersoneSede
     */
    public function setPersona($persona)
    {
        $this->persona = $persona;

        return $this;
    }

    /**
     * @param AdrierPersona $persona
     */
    public function addPersona(AdrierPersona $persona)
    {
        if (!$this->persona->contains($persona)) {
            $this->persona->add($persona);
        }
    }
}

/**
 * Class AdrierPersona
 */
class AdrierPersona
{
    /**
     * @var int
     */
    protected $elemento;

    /**
     * @var AdrierIdentificativo
     */
    protected $identificativo;

    /**
     * @var AdrierPersonaFisica
     */
    protected $personaFisica;

    /**
     * @var AdrierPersonaGiuridica
     */
    protected $personaGiuridica;

    /**
     * @var AdrierCarica[]|ArrayCollection
     */
    protected $cariche;

    /**
     * @var AdrierFallimentoPersona
     */
    protected $fallimentoPersona;

    /**
     * @var string
     */
    protected $elettore;

    /**
     * @var string
     */
    protected $rappresentante;

    /**
     * AdrierPersona constructor.
     */
    public function __construct(?array $persona)
    {
        $this->elemento          = $persona['@elemento'] ?? null;
        $this->identificativo    = new AdrierIdentificativo($persona['IDENTIFICATIVO'] ?? null);
        $this->personaFisica     = new AdrierPersonaFisica($persona['PERSONA_FISICA'] ?? null);
        $this->personaGiuridica  = new AdrierPersonaGiuridica($persona['PERSONA_GIURIDICA'] ?? null);
        $this->cariche           = new ArrayCollection();
        $this->fallimentoPersona = new AdrierFallimentoPersona($persona['FALLIMENTO_PERSONA'] ?? null);
        $this->elettore          = $persona['ELETTORE'] ?? null;
        $this->rappresentante    = $persona['RAPPRESENTANTE'] ?? null;

        if (isset($persona['CARICHE'])) {
            if(isset($persona['CARICHE']['CARICA'])) {
                if(count($persona['CARICHE']['CARICA']) > 1) {
                    foreach ($persona['CARICHE']['CARICA'] as $carica) {
                        if(is_array($carica)) {
                            $this->cariche->add(new AdrierCarica($carica ?? null));
                        } else {
                            $this->cariche->add(new AdrierCarica($persona['CARICHE']['CARICA'] ?? null));
                        }
                    }
                } else {
                    $this->cariche->add(new AdrierCarica($persona['CARICHE']['CARICA'] ?? null));
                }
            }
        }
    }

    /**
     * @return int
     */
    public function getElemento(): ?int
    {
        return $this->elemento;
    }

    /**
     * @param int $elemento
     *
     * @return AdrierPersona
     */
    public function setElemento(int $elemento): AdrierPersona
    {
        $this->elemento = $elemento;

        return $this;
    }

    /**
     * @return AdrierIdentificativo
     */
    public function getIdentificativo(): ?AdrierIdentificativo
    {
        return $this->identificativo;
    }

    /**
     * @param AdrierIdentificativo $identificativo
     *
     * @return AdrierPersona
     */
    public function setIdentificativo(AdrierIdentificativo $identificativo): AdrierPersona
    {
        $this->identificativo = $identificativo;

        return $this;
    }

    /**
     * @return AdrierPersonaFisica
     */
    public function getPersonaFisica(): ?AdrierPersonaFisica
    {
        return $this->personaFisica;
    }

    /**
     * @param AdrierPersonaFisica $personaFisica
     *
     * @return AdrierPersona
     */
    public function setPersonaFisica(AdrierPersonaFisica $personaFisica): AdrierPersona
    {
        $this->personaFisica = $personaFisica;

        return $this;
    }

    /**
     * @return AdrierPersonaGiuridica
     */
    public function getPersonaGiuridica(): ?AdrierPersonaGiuridica
    {
        return $this->personaGiuridica;
    }

    /**
     * @param AdrierPersonaGiuridica $personaGiuridica
     *
     * @return AdrierPersona
     */
    public function setPersonaGiuridica(AdrierPersonaGiuridica $personaGiuridica): AdrierPersona
    {
        $this->personaGiuridica = $personaGiuridica;

        return $this;
    }

    /**
     * @return ArrayCollection|AdrierCarica[]
     */
    public function getCariche()
    {
        return $this->cariche;
    }

    /**
     * @param ArrayCollection|AdrierCarica[] $cariche
     *
     * @return AdrierPersona
     */
    public function setCariche($cariche)
    {
        $this->cariche = $cariche;

        return $this;
    }

    /**
     * @param AdrierCarica $carica
     */
    public function addCariche(AdrierCarica $carica)
    {
        if (!$this->cariche->contains($carica)) {
            $this->cariche->add($carica);
        }
    }

    /**
     * @return AdrierFallimentoPersona
     */
    public function getFallimentoPersona(): ?AdrierFallimentoPersona
    {
        return $this->fallimentoPersona;
    }

    /**
     * @param AdrierFallimentoPersona $fallimentoPersona
     *
     * @return AdrierPersona
     */
    public function setFallimentoPersona(AdrierFallimentoPersona $fallimentoPersona): AdrierPersona
    {
        $this->fallimentoPersona = $fallimentoPersona;

        return $this;
    }

    /**
     * @return string
     */
    public function getElettore(): ?string
    {
        return $this->elettore;
    }

    /**
     * @param string $elettore
     *
     * @return AdrierPersona
     */
    public function setElettore(string $elettore): AdrierPersona
    {
        $this->elettore = $elettore;

        return $this;
    }

    /**
     * @return string
     */
    public function getRappresentante(): ?string
    {
        return $this->rappresentante;
    }

    /**
     * @param string $rappresentante
     *
     * @return AdrierPersona
     */
    public function setRappresentante(string $rappresentante): AdrierPersona
    {
        $this->rappresentante = $rappresentante;

        return $this;
    }
}

/**
 * Class AdrierFallimentoPersona
 */
class AdrierFallimentoPersona
{
    /**
     * @var AdrierInProprio
     */
    protected $inProprio;

    /**
     * @var AdrierPerEstensione
     */
    protected $perEstensione;

    /**
     * AdrierFallimentoPersona constructor.
     */
    public function __construct(?array $fallimentoPersona)
    {
        $this->inProprio     = new AdrierInProprio($fallimentoPersona['IN_PROPRIO'] ?? null);
        $this->perEstensione = new AdrierPerEstensione($fallimentoPersona['PER_ESTENSIONE'] ?? null);
    }

    /**
     * @return AdrierInProprio
     */
    public function getInProprio(): ?AdrierInProprio
    {
        return $this->inProprio;
    }

    /**
     * @param AdrierInProprio $inProprio
     *
     * @return AdrierFallimentoPersona
     */
    public function setInProprio(AdrierInProprio $inProprio): AdrierFallimentoPersona
    {
        $this->inProprio = $inProprio;

        return $this;
    }

    /**
     * @return AdrierPerEstensione
     */
    public function getPerEstensione(): ?AdrierPerEstensione
    {
        return $this->perEstensione;
    }

    /**
     * @param AdrierPerEstensione $perEstensione
     *
     * @return AdrierFallimentoPersona
     */
    public function setPerEstensione(AdrierPerEstensione $perEstensione): AdrierFallimentoPersona
    {
        $this->perEstensione = $perEstensione;

        return $this;
    }
}

/**
 * Class AdrierPerEstensione
 */
class AdrierPerEstensione
{
    /**
     * @var string
     */
    protected $cognome;

    /**
     * @var string
     */
    protected $nome;

    /**
     * @var string
     */
    protected $dataNascita;

    /**
     * AdrierPerEstensione constructor.
     */
    public function __construct(?array $perEstensione)
    {
        $this->cognome     = $perEstensione['COGNOME'] ?? null;
        $this->nome        = $perEstensione['NOME'] ?? null;
        $this->dataNascita = $perEstensione['DATA_NASCITA'] ?? null;
    }

    /**
     * @return string
     */
    public function getCognome(): ?string
    {
        return $this->cognome;
    }

    /**
     * @param string $cognome
     *
     * @return AdrierPerEstensione
     */
    public function setCognome(string $cognome): AdrierPerEstensione
    {
        $this->cognome = $cognome;

        return $this;
    }

    /**
     * @return string
     */
    public function getNome(): ?string
    {
        return $this->nome;
    }

    /**
     * @param string $nome
     *
     * @return AdrierPerEstensione
     */
    public function setNome(string $nome): AdrierPerEstensione
    {
        $this->nome = $nome;

        return $this;
    }

    /**
     * @return string
     */
    public function getDataNascita(): ?string
    {
        return $this->dataNascita;
    }

    /**
     * @param string $dataNascita
     *
     * @return AdrierPerEstensione
     */
    public function setDataNascita(string $dataNascita): AdrierPerEstensione
    {
        $this->dataNascita = $dataNascita;

        return $this;
    }
}

/**
 * Class AdrierInProprio
 */
class AdrierInProprio
{
    /**
     * @var string
     */
    protected $nFallimento;

    /**
     * @var string
     */
    protected $dtFallimento;

    /**
     * @var string
     */
    protected $tribunale;

    /**
     * @var string
     */
    protected $pvTribunale;

    /**
     * @var string
     */
    protected $nSentenza;

    /**
     * @var string
     */
    protected $dtSentenza;

    /**
     * @var string
     */
    protected $curatore;

    /**
     * @var string
     */
    protected $dtChiusura;

    /**
     * @var string
     */
    protected $organoGiudiziario;

    /**
     * AdrierInProprio constructor.
     */
    public function __construct(?array $inProprio)
    {
        $this->nFallimento       = $inProprio['N_FALLIMENTO'] ?? null;
        $this->dtFallimento      = $inProprio['DT_FALLIMENTO'] ?? null;
        $this->tribunale         = $inProprio['TRIBUNALE'] ?? null;
        $this->pvTribunale       = $inProprio['PV_TRIBUNALE'] ?? null;
        $this->nSentenza         = $inProprio['N_SENTENZA'] ?? null;
        $this->dtSentenza        = $inProprio['DT_SENTENZA'] ?? null;
        $this->curatore          = $inProprio['CURATORE'] ?? null;
        $this->dtChiusura        = $inProprio['DT_CHIUSURA'] ?? null;
        $this->organoGiudiziario = $inProprio['ORGANO_GIUDIZIARIO'] ?? null;
    }

    /**
     * @return string
     */
    public function getNFallimento(): ?string
    {
        return $this->nFallimento;
    }

    /**
     * @param string $nFallimento
     *
     * @return AdrierInProprio
     */
    public function setNFallimento(string $nFallimento): AdrierInProprio
    {
        $this->nFallimento = $nFallimento;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtFallimento(): ?string
    {
        return $this->dtFallimento;
    }

    /**
     * @param string $dtFallimento
     *
     * @return AdrierInProprio
     */
    public function setDtFallimento(string $dtFallimento): AdrierInProprio
    {
        $this->dtFallimento = $dtFallimento;

        return $this;
    }

    /**
     * @return string
     */
    public function getTribunale(): ?string
    {
        return $this->tribunale;
    }

    /**
     * @param string $tribunale
     *
     * @return AdrierInProprio
     */
    public function setTribunale(string $tribunale): AdrierInProprio
    {
        $this->tribunale = $tribunale;

        return $this;
    }

    /**
     * @return string
     */
    public function getPvTribunale(): ?string
    {
        return $this->pvTribunale;
    }

    /**
     * @param string $pvTribunale
     *
     * @return AdrierInProprio
     */
    public function setPvTribunale(string $pvTribunale): AdrierInProprio
    {
        $this->pvTribunale = $pvTribunale;

        return $this;
    }

    /**
     * @return string
     */
    public function getNSentenza(): ?string
    {
        return $this->nSentenza;
    }

    /**
     * @param string $nSentenza
     *
     * @return AdrierInProprio
     */
    public function setNSentenza(string $nSentenza): AdrierInProprio
    {
        $this->nSentenza = $nSentenza;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtSentenza(): ?string
    {
        return $this->dtSentenza;
    }

    /**
     * @param string $dtSentenza
     *
     * @return AdrierInProprio
     */
    public function setDtSentenza(string $dtSentenza): AdrierInProprio
    {
        $this->dtSentenza = $dtSentenza;

        return $this;
    }

    /**
     * @return string
     */
    public function getCuratore(): ?string
    {
        return $this->curatore;
    }

    /**
     * @param string $curatore
     *
     * @return AdrierInProprio
     */
    public function setCuratore(string $curatore): AdrierInProprio
    {
        $this->curatore = $curatore;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtChiusura(): ?string
    {
        return $this->dtChiusura;
    }

    /**
     * @param string $dtChiusura
     *
     * @return AdrierInProprio
     */
    public function setDtChiusura(string $dtChiusura): AdrierInProprio
    {
        $this->dtChiusura = $dtChiusura;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrganoGiudiziario(): ?string
    {
        return $this->organoGiudiziario;
    }

    /**
     * @param string $organoGiudiziario
     *
     * @return AdrierInProprio
     */
    public function setOrganoGiudiziario(string $organoGiudiziario): AdrierInProprio
    {
        $this->organoGiudiziario = $organoGiudiziario;

        return $this;
    }
}

/**
 * Class AdrierCarica
 */
class AdrierCarica
{
    /**
     * @var string
     */
    protected $descrizione;

    /**
     * @var string
     */
    protected $cCarica;

    /**
     * @var string
     */
    protected $dtInizio;

    /**
     * @var string
     */
    protected $dtFine;

    /**
     * AdrierCarica constructor.
     */
    public function __construct(?array $carica)
    {
        $this->descrizione = $carica['DESCRIZIONE'] ?? null;
        $this->cCarica     = $carica['C_CARICA'] ?? null;
        $this->dtInizio    = $carica['DT_INIZIO'] ?? null;
        $this->dtFine      = $carica['DT_FINE'] ?? null;
    }

    /**
     * @return string
     */
    public function getDescrizione(): ?string
    {
        return $this->descrizione;
    }

    /**
     * @param string $descrizione
     *
     * @return AdrierCarica
     */
    public function setDescrizione(string $descrizione): AdrierCarica
    {
        $this->descrizione = $descrizione;

        return $this;
    }

    /**
     * @return string
     */
    public function getCCarica(): ?string
    {
        return $this->cCarica;
    }

    /**
     * @param string $cCarica
     *
     * @return AdrierCarica
     */
    public function setCCarica(string $cCarica): AdrierCarica
    {
        $this->cCarica = $cCarica;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtInizio(): ?string
    {
        return $this->dtInizio;
    }

    /**
     * @param string $dtInizio
     *
     * @return AdrierCarica
     */
    public function setDtInizio(string $dtInizio): AdrierCarica
    {
        $this->dtInizio = $dtInizio;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtFine(): ?string
    {
        return $this->dtFine;
    }

    /**
     * @param string $dtFine
     *
     * @return AdrierCarica
     */
    public function setDtFine(string $dtFine): AdrierCarica
    {
        $this->dtFine = $dtFine;

        return $this;
    }
}

/**
 * Class AdrierPersonaGiuridica
 */
class AdrierPersonaGiuridica
{
    /**
     * @var string
     */
    protected $cciaa;

    /**
     * @var string
     */
    protected $nIscrizioneRea;

    /**
     * @var string
     */
    protected $denominazione;

    /**
     * @var string
     */
    protected $codiceFiscale;

    /**
     * @var string
     */
    protected $dtCostituzione;

    /**
     * @var AdrierIndirizzo
     */
    protected $indirizzo;

    /**
     * AdrierPersonaGiuridica constructor.
     */
    public function __construct(?array $personaGiuridica)
    {
        $this->cciaa          = $personaGiuridica['CCIAA'] ?? null;
        $this->nIscrizioneRea = $personaGiuridica['N_ISCRIZIONE_REA'] ?? null;
        $this->denominazione  = $personaGiuridica['DENOMINAZIONE'] ?? null;
        $this->codiceFiscale  = $personaGiuridica['CODICE_FISCALE'] ?? null;
        $this->dtCostituzione = $personaGiuridica['DT_COSTITUZIONE'] ?? null;
        $this->indirizzo      = new AdrierIndirizzo($personaGiuridica['INDIRIZZO'] ?? null);
    }

    /**
     * @return string
     */
    public function getCciaa(): ?string
    {
        return $this->cciaa;
    }

    /**
     * @param string $cciaa
     *
     * @return AdrierPersonaGiuridica
     */
    public function setCciaa(string $cciaa): AdrierPersonaGiuridica
    {
        $this->cciaa = $cciaa;

        return $this;
    }

    /**
     * @return string
     */
    public function getNIscrizioneRea(): ?string
    {
        return $this->nIscrizioneRea;
    }

    /**
     * @param string $nIscrizioneRea
     *
     * @return AdrierPersonaGiuridica
     */
    public function setNIscrizioneRea(string $nIscrizioneRea): AdrierPersonaGiuridica
    {
        $this->nIscrizioneRea = $nIscrizioneRea;

        return $this;
    }

    /**
     * @return string
     */
    public function getDenominazione(): ?string
    {
        return $this->denominazione;
    }

    /**
     * @param string $denominazione
     *
     * @return AdrierPersonaGiuridica
     */
    public function setDenominazione(string $denominazione): AdrierPersonaGiuridica
    {
        $this->denominazione = $denominazione;

        return $this;
    }

    /**
     * @return string
     */
    public function getCodiceFiscale(): ?string
    {
        return $this->codiceFiscale;
    }

    /**
     * @param string $codiceFiscale
     *
     * @return AdrierPersonaGiuridica
     */
    public function setCodiceFiscale(string $codiceFiscale): AdrierPersonaGiuridica
    {
        $this->codiceFiscale = $codiceFiscale;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtCostituzione(): ?string
    {
        return $this->dtCostituzione;
    }

    /**
     * @param string $dtCostituzione
     *
     * @return AdrierPersonaGiuridica
     */
    public function setDtCostituzione(string $dtCostituzione): AdrierPersonaGiuridica
    {
        $this->dtCostituzione = $dtCostituzione;

        return $this;
    }

    /**
     * @return AdrierIndirizzo
     */
    public function getIndirizzo(): ?AdrierIndirizzo
    {
        return $this->indirizzo;
    }

    /**
     * @param AdrierIndirizzo $indirizzo
     *
     * @return AdrierPersonaGiuridica
     */
    public function setIndirizzo(AdrierIndirizzo $indirizzo): AdrierPersonaGiuridica
    {
        $this->indirizzo = $indirizzo;

        return $this;
    }
}

/**
 * Class AdrierPersonaFisica
 */
class AdrierPersonaFisica
{
    /**
     * @var string
     */
    protected $cognome;

    /**
     * @var string
     */
    protected $nome;

    /**
     * @var string
     */
    protected $sesso;

    /**
     * @var AdrierEstremiNascita
     */
    protected $estremiNascita;

    /**
     * @var string
     */
    protected $codiceFiscale;

    /**
     * @var string
     */
    protected $cittadinanza;

    /**
     * @var AdrierIndirizzo
     */
    protected $indirizzo;

    /**
     * AdrierPersonaFisica constructor.
     */
    public function __construct(?array $personaFisica)
    {
        $this->cognome        = $personaFisica['COGNOME'] ?? null;
        $this->nome           = $personaFisica['NOME'] ?? null;
        $this->sesso          = $personaFisica['SESSO'] ?? null;
        $this->estremiNascita = new AdrierEstremiNascita($personaFisica['ESTREMI_NASCITA'] ?? null);
        $this->codiceFiscale  = $personaFisica['CODICE_FISCALE'] ?? null;
        $this->cittadinanza   = $personaFisica['CITTADINANZA'] ?? null;
        $this->indirizzo      = new AdrierIndirizzo($personaFisica['INDIRIZZO'] ?? null);
    }

    /**
     * @return string
     */
    public function getCognome(): ?string
    {
        return $this->cognome;
    }

    /**
     * @param string $cognome
     *
     * @return AdrierPersonaFisica
     */
    public function setCognome(string $cognome): AdrierPersonaFisica
    {
        $this->cognome = $cognome;

        return $this;
    }

    /**
     * @return string
     */
    public function getNome(): ?string
    {
        return $this->nome;
    }

    /**
     * @param string $nome
     *
     * @return AdrierPersonaFisica
     */
    public function setNome(string $nome): AdrierPersonaFisica
    {
        $this->nome = $nome;

        return $this;
    }

    /**
     * @return string
     */
    public function getSesso(): ?string
    {
        return $this->sesso;
    }

    /**
     * @param string $sesso
     *
     * @return AdrierPersonaFisica
     */
    public function setSesso(string $sesso): AdrierPersonaFisica
    {
        $this->sesso = $sesso;

        return $this;
    }

    /**
     * @return AdrierEstremiNascita
     */
    public function getEstremiNascita(): ?AdrierEstremiNascita
    {
        return $this->estremiNascita;
    }

    /**
     * @param AdrierEstremiNascita $estremiNascita
     *
     * @return AdrierPersonaFisica
     */
    public function setEstremiNascita(AdrierEstremiNascita $estremiNascita): AdrierPersonaFisica
    {
        $this->estremiNascita = $estremiNascita;

        return $this;
    }

    /**
     * @return string
     */
    public function getCodiceFiscale(): ?string
    {
        return $this->codiceFiscale;
    }

    /**
     * @param string $codiceFiscale
     *
     * @return AdrierPersonaFisica
     */
    public function setCodiceFiscale(string $codiceFiscale): AdrierPersonaFisica
    {
        $this->codiceFiscale = $codiceFiscale;

        return $this;
    }

    /**
     * @return string
     */
    public function getCittadinanza(): ?string
    {
        return $this->cittadinanza;
    }

    /**
     * @param string $cittadinanza
     *
     * @return AdrierPersonaFisica
     */
    public function setCittadinanza(string $cittadinanza): AdrierPersonaFisica
    {
        $this->cittadinanza = $cittadinanza;

        return $this;
    }

    /**
     * @return AdrierIndirizzo
     */
    public function getIndirizzo(): ?AdrierIndirizzo
    {
        return $this->indirizzo;
    }

    /**
     * @param AdrierIndirizzo $indirizzo
     *
     * @return AdrierPersonaFisica
     */
    public function setIndirizzo(AdrierIndirizzo $indirizzo): AdrierPersonaFisica
    {
        $this->indirizzo = $indirizzo;

        return $this;
    }
}

/**
 * Class AdrierEstremiNascita
 */
class AdrierEstremiNascita
{
    /**
     * @var string
     */
    protected $provincia;

    /**
     * @var string
     */
    protected $comune;

    /**
     * @var string
     */
    protected $cComune;

    /**
     * @var string
     */
    protected $stato;

    /**
     * @var string
     */
    protected $data;

    /**
     * AdrierEstremiNascita constructor.
     */
    public function __construct(?array $estremiNascita)
    {
        $this->provincia = $estremiNascita['PROVINCIA'] ?? null;
        $this->comune    = $estremiNascita['COMUNE'] ?? null;
        $this->cComune   = $estremiNascita['C_COMUNE'] ?? null;
        $this->stato     = $estremiNascita['STATO'] ?? null;
        $this->data      = $estremiNascita['DATA'] ?? null;
    }

    /**
     * @return string
     */
    public function getProvincia(): ?string
    {
        return $this->provincia;
    }

    /**
     * @param string $provincia
     *
     * @return AdrierEstremiNascita
     */
    public function setProvincia(string $provincia): AdrierEstremiNascita
    {
        $this->provincia = $provincia;

        return $this;
    }

    /**
     * @return string
     */
    public function getComune(): ?string
    {
        return $this->comune;
    }

    /**
     * @param string $comune
     *
     * @return AdrierEstremiNascita
     */
    public function setComune(string $comune): AdrierEstremiNascita
    {
        $this->comune = $comune;

        return $this;
    }

    /**
     * @return string
     */
    public function getCComune(): ?string
    {
        return $this->cComune;
    }

    /**
     * @param string $cComune
     *
     * @return AdrierEstremiNascita
     */
    public function setCComune(string $cComune): AdrierEstremiNascita
    {
        $this->cComune = $cComune;

        return $this;
    }

    /**
     * @return string
     */
    public function getStato(): ?string
    {
        return $this->stato;
    }

    /**
     * @param string $stato
     *
     * @return AdrierEstremiNascita
     */
    public function setStato(string $stato): AdrierEstremiNascita
    {
        $this->stato = $stato;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getData(): ?DateTime
    {
        if (!empty($this->data)) {
            return DateTime::createFromFormat('Ymd', $this->data);
        }

        return null;
    }

    /**
     * @param string $data
     *
     * @return AdrierEstremiNascita
     */
    public function setData(string $data): AdrierEstremiNascita
    {
        $this->data = $data;

        return $this;
    }
}

/**
 * Class AdrierIdentificativo
 */
class AdrierIdentificativo
{
    /**
     * @var string
     */
    protected $pressoCciaa;

    /**
     * @var string
     */
    protected $pressoNRea;

    /**
     * @var string
     */
    protected $progressivoPersona;

    /**
     * @var string
     */
    protected $progressivoLoc;

    /**
     * AdrierIdentificativo constructor.
     */
    public function __construct(?array $identificativo)
    {
        $this->pressoCciaa        = $identificativo['PRESSO_CCIAA'] ?? null;
        $this->pressoNRea         = $identificativo['PRESSO_N_REA'] ?? null;
        $this->progressivoPersona = $identificativo['PROGRESSIVO_PERSONA'] ?? null;
        $this->progressivoLoc     = $identificativo['PROGRESSIVO_LOC'] ?? null;
    }

    /**
     * @return string
     */
    public function getPressoCciaa(): ?string
    {
        return $this->pressoCciaa;
    }

    /**
     * @param string $pressoCciaa
     *
     * @return AdrierIdentificativo
     */
    public function setPressoCciaa(string $pressoCciaa): AdrierIdentificativo
    {
        $this->pressoCciaa = $pressoCciaa;

        return $this;
    }

    /**
     * @return string
     */
    public function getPressoNRea(): ?string
    {
        return $this->pressoNRea;
    }

    /**
     * @param string $pressoNRea
     *
     * @return AdrierIdentificativo
     */
    public function setPressoNRea(string $pressoNRea): AdrierIdentificativo
    {
        $this->pressoNRea = $pressoNRea;

        return $this;
    }

    /**
     * @return string
     */
    public function getProgressivoPersona(): ?string
    {
        return $this->progressivoPersona;
    }

    /**
     * @param string $progressivoPersona
     *
     * @return AdrierIdentificativo
     */
    public function setProgressivoPersona(string $progressivoPersona): AdrierIdentificativo
    {
        $this->progressivoPersona = $progressivoPersona;

        return $this;
    }

    /**
     * @return string
     */
    public function getProgressivoLoc(): ?string
    {
        return $this->progressivoLoc;
    }

    /**
     * @param string $progressivoLoc
     *
     * @return AdrierIdentificativo
     */
    public function setProgressivoLoc(string $progressivoLoc): AdrierIdentificativo
    {
        $this->progressivoLoc = $progressivoLoc;

        return $this;
    }
}

/**
 * Class AdrierLocalizzazioni
 */
class AdrierLocalizzazioni
{
    /**
     * @var int
     */
    protected $elemento;

    /**
     * @var string
     */
    protected $provincia;

    /**
     * @var AdrierLocalizzazione[]|ArrayCollection
     */
    protected $localizzazione;

    /**
     * AdrierLocalizzazioni constructor.
     */
    public function __construct(?array $localizzazioni)
    {
        $this->elemento       = $localizzazioni['@elemento'] ?? null;
        $this->provincia      = $localizzazioni['@provincia'] ?? null;
        $this->localizzazione = new ArrayCollection();

        if ($this->elemento > 1) {
            foreach ($localizzazioni['LOCALIZZAZIONE'] as $localizzazione) {
                $this->localizzazione->add(new AdrierLocalizzazione($localizzazione ?? null));
            }
        } else {
            $this->localizzazione->add(new AdrierLocalizzazione($localizzazioni['LOCALIZZAZIONE'] ?? null));
        }
    }

    /**
     * @return int
     */
    public function getElemento(): ?int
    {
        return $this->elemento;
    }

    /**
     * @param int $elemento
     *
     * @return AdrierLocalizzazioni
     */
    public function setElemento(int $elemento): AdrierLocalizzazioni
    {
        $this->elemento = $elemento;

        return $this;
    }

    /**
     * @return string
     */
    public function getProvincia(): ?string
    {
        return $this->provincia;
    }

    /**
     * @param string $provincia
     *
     * @return AdrierLocalizzazioni
     */
    public function setProvincia(string $provincia): AdrierLocalizzazioni
    {
        $this->provincia = $provincia;

        return $this;
    }

    /**
     * @return ArrayCollection|AdrierLocalizzazione[]
     */
    public function getLocalizzazione()
    {
        return $this->localizzazione;
    }

    /**
     * @param ArrayCollection|AdrierLocalizzazione[] $localizzazione
     *
     * @return AdrierLocalizzazioni
     */
    public function setLocalizzazione($localizzazione)
    {
        $this->localizzazione = $localizzazione;

        return $this;
    }

    /**
     * @param AdrierLocalizzazione $localizzazione
     */
    public function addLocalizzazione(AdrierLocalizzazione $localizzazione)
    {
        if (!$this->localizzazione->contains($localizzazione)) {
            $this->localizzazione->add($localizzazione);
        }
    }
}

/**
 * Class AdrierLocalizzazione
 */
class AdrierLocalizzazione
{
    /**
     * @var int
     */
    protected $elemento;

    /**
     * @var AdrierNumeroTipo
     */
    protected $numeroTipo;

    /**
     * @var string
     */
    protected $denominazione;

    /**
     * @var string
     */
    protected $insegna;

    /**
     * @var AdrierIndirizzo
     */
    protected $indirizzo;

    /**
     * @var string
     */
    protected $dtApertura;

    /**
     * @var string
     */
    protected $attivita;

    /**
     * @var AdrierAttivitaIstat[]|ArrayCollection
     */
    protected $codiciIstat02;

    /**
     * @var AdrierAttivitaIstat[]|ArrayCollection
     */
    protected $codiceAtecoUl;

    /**
     * @var AdrierRuoloLoc[]|ArrayCollection
     */
    protected $ruoliLoc;

    /**
     * @var AdrierDatiArtigiani
     */
    protected $datiArtigiani;

    /**
     * @var AdrierCommercioDettaglio
     */
    protected $commercioDettaglio;

    /**
     * @var AdrierPersoneLoc
     */
    protected $personeLoc;

    /**
     * @var AdrierCessazioneLoc
     */
    protected $cessazioneLoc;

    /**
     * AdrierLocalizzazione constructor.
     */
    public function __construct(?array $localizzazione)
    {
        $this->elemento           = $localizzazione['@elemento'] ?? null;
        $this->numeroTipo         = new AdrierNumeroTipo($localizzazione['NUMERO_TIPO'] ?? null);
        $this->denominazione      = $localizzazione['DENOMINAZIONE'] ?? null;
        $this->insegna            = $localizzazione['INSEGNA'] ?? null;
        $this->indirizzo          = new AdrierIndirizzo($localizzazione['INDIRIZZO'] ?? null);
        $this->dtApertura         = $localizzazione['DT_APERTURA'] ?? null;
        $this->attivita           = $localizzazione['ATTIVITA'] ?? null;
        $this->codiceAtecoUl      = new ArrayCollection();
        $this->codiciIstat02      = new ArrayCollection();
        $this->ruoliLoc           = new ArrayCollection();
        $this->datiArtigiani      = new AdrierDatiArtigiani($localizzazione['DATI_ARTIGIANI'] ?? null);
        $this->commercioDettaglio = new AdrierCommercioDettaglio($localizzazione['COMMERCIO_DETTAGLIO'] ?? null);
        $this->personeLoc         = new AdrierPersoneLoc($localizzazione['PERSONE_LOC'] ?? null);
        $this->cessazioneLoc      = new AdrierCessazioneLoc($localizzazione['CESSAZIONE_LOC'] ?? null);

        if (isset($localizzazione['CODICE_ATECO_UL']['ATTIVITA_ISTAT'][0])) {
            foreach ($localizzazione['CODICE_ATECO_UL']['ATTIVITA_ISTAT'] as $codiciAteco) {
                $this->codiceAtecoUl->add(new AdrierAttivitaIstat($codiciAteco ?? null));
            }
        } else {
            $this->codiceAtecoUl->add(new AdrierAttivitaIstat($localizzazione['CODICE_ATECO_UL']['ATTIVITA_ISTAT'] ?? null));
        }

        if (isset($localizzazione['CODICI_ISTAT_02']['ATTIVITA_ISTAT'][0])) {
            foreach ($localizzazione['CODICI_ISTAT_02']['ATTIVITA_ISTAT'] as $codiciIstat) {
                $this->codiciIstat02->add(new AdrierAttivitaIstat($codiciIstat ?? null));
            }
        } else {
            $this->codiciIstat02->add(new AdrierAttivitaIstat($localizzazione['CODICI_ISTAT_02']['ATTIVITA_ISTAT'] ?? null));
        }

        if (isset($localizzazione['RUOLI_LOC']['RUOLO_LOC'][0])) {
            foreach ($localizzazione['RUOLI_LOC']['RUOLO_LOC'] as $ruoloLoc) {
                $this->ruoliLoc->add(new AdrierRuoloLoc($ruoloLoc ?? null));
            }
        } else {
            $this->ruoliLoc->add(new AdrierRuoloLoc($localizzazione['RUOLI_LOC']['RUOLO_LOC'] ?? null));
        }
    }

    /**
     * @return int
     */
    public function getElemento(): ?int
    {
        return $this->elemento;
    }

    /**
     * @param int $elemento
     *
     * @return AdrierLocalizzazione
     */
    public function setElemento(int $elemento): AdrierLocalizzazione
    {
        $this->elemento = $elemento;

        return $this;
    }

    /**
     * @return AdrierNumeroTipo
     */
    public function getNumeroTipo(): ?AdrierNumeroTipo
    {
        return $this->numeroTipo;
    }

    /**
     * @param AdrierNumeroTipo $numeroTipo
     *
     * @return AdrierLocalizzazione
     */
    public function setNumeroTipo(AdrierNumeroTipo $numeroTipo): AdrierLocalizzazione
    {
        $this->numeroTipo = $numeroTipo;

        return $this;
    }

    /**
     * @return string
     */
    public function getDenominazione(): ?string
    {
        return $this->denominazione;
    }

    /**
     * @param string $denominazione
     *
     * @return AdrierLocalizzazione
     */
    public function setDenominazione(string $denominazione): AdrierLocalizzazione
    {
        $this->denominazione = $denominazione;

        return $this;
    }

    /**
     * @return string
     */
    public function getInsegna(): ?string
    {
        return $this->insegna;
    }

    /**
     * @param string $insegna
     *
     * @return AdrierLocalizzazione
     */
    public function setInsegna(string $insegna): AdrierLocalizzazione
    {
        $this->insegna = $insegna;

        return $this;
    }

    /**
     * @return AdrierIndirizzo
     */
    public function getIndirizzo(): ?AdrierIndirizzo
    {
        return $this->indirizzo;
    }

    /**
     * @param AdrierIndirizzo $indirizzo
     *
     * @return AdrierLocalizzazione
     */
    public function setIndirizzo(AdrierIndirizzo $indirizzo): AdrierLocalizzazione
    {
        $this->indirizzo = $indirizzo;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtApertura(): ?string
    {
        return $this->dtApertura;
    }

    /**
     * @param string $dtApertura
     *
     * @return AdrierLocalizzazione
     */
    public function setDtApertura(string $dtApertura): AdrierLocalizzazione
    {
        $this->dtApertura = $dtApertura;

        return $this;
    }

    /**
     * @return string
     */
    public function getAttivita(): ?string
    {
        return $this->attivita;
    }

    /**
     * @param string $attivita
     *
     * @return AdrierLocalizzazione
     */
    public function setAttivita(string $attivita): AdrierLocalizzazione
    {
        $this->attivita = $attivita;

        return $this;
    }

    /**
     * @return ArrayCollection|AdrierAttivitaIstat[]
     */
    public function getCodiciIstat02()
    {
        return $this->codiciIstat02;
    }

    /**
     * @param ArrayCollection|AdrierAttivitaIstat[] $codiciIstat02
     *
     * @return AdrierLocalizzazione
     */
    public function setCodiciIstat02($codiciIstat02)
    {
        $this->codiciIstat02 = $codiciIstat02;

        return $this;
    }

    /**
     * @param AdrierAttivitaIstat $codiceIstat
     */
    public function addCodiciIstat02(AdrierAttivitaIstat $codiceIstat)
    {
        if (!$this->codiciIstat02->contains($codiceIstat)) {
            $this->codiciIstat02->add($codiceIstat);
        }
    }

    /**
     * @return ArrayCollection|AdrierAttivitaIstat[]
     */
    public function getCodiceAtecoUl()
    {
        return $this->codiceAtecoUl;
    }

    /**
     * @param ArrayCollection|AdrierAttivitaIstat[] $codiceAtecoUl
     *
     * @return AdrierLocalizzazione
     */
    public function setCodiceAtecoUl($codiceAtecoUl)
    {
        $this->codiceAtecoUl = $codiceAtecoUl;

        return $this;
    }

    /**
     * @param AdrierAttivitaIstat $codiceIstat
     */
    public function addCodiceAtecoUl(AdrierAttivitaIstat $codiceIstat)
    {
        if (!$this->codiceAtecoUl->contains($codiceIstat)) {
            $this->codiceAtecoUl->add($codiceIstat);
        }
    }

    /**
     * @return ArrayCollection|AdrierRuoloLoc[]
     */
    public function getRuoliLoc()
    {
        return $this->ruoliLoc;
    }

    /**
     * @param ArrayCollection|AdrierRuoloLoc[] $ruoliLoc
     *
     * @return AdrierLocalizzazione
     */
    public function setRuoliLoc($ruoliLoc)
    {
        $this->ruoliLoc = $ruoliLoc;

        return $this;
    }

    /**
     * @param AdrierRuoloLoc $ruoloLoc
     */
    public function addRuoliLoc(AdrierRuoloLoc $ruoloLoc)
    {
        if (!$this->ruoliLoc->contains($ruoloLoc)) {
            $this->ruoliLoc->add($ruoloLoc);
        }
    }

    /**
     * @return AdrierDatiArtigiani
     */
    public function getDatiArtigiani(): ?AdrierDatiArtigiani
    {
        return $this->datiArtigiani;
    }

    /**
     * @param AdrierDatiArtigiani $datiArtigiani
     *
     * @return AdrierLocalizzazione
     */
    public function setDatiArtigiani(AdrierDatiArtigiani $datiArtigiani): AdrierLocalizzazione
    {
        $this->datiArtigiani = $datiArtigiani;

        return $this;
    }

    /**
     * @return AdrierCommercioDettaglio
     */
    public function getCommercioDettaglio(): ?AdrierCommercioDettaglio
    {
        return $this->commercioDettaglio;
    }

    /**
     * @param AdrierCommercioDettaglio $commercioDettaglio
     *
     * @return AdrierLocalizzazione
     */
    public function setCommercioDettaglio(AdrierCommercioDettaglio $commercioDettaglio): AdrierLocalizzazione
    {
        $this->commercioDettaglio = $commercioDettaglio;

        return $this;
    }

    /**
     * @return AdrierPersoneLoc
     */
    public function getPersoneLoc(): ?AdrierPersoneLoc
    {
        return $this->personeLoc;
    }

    /**
     * @param AdrierPersoneLoc $personeLoc
     *
     * @return AdrierLocalizzazione
     */
    public function setPersoneLoc(AdrierPersoneLoc $personeLoc): AdrierLocalizzazione
    {
        $this->personeLoc = $personeLoc;

        return $this;
    }

    /**
     * @return AdrierCessazioneLoc
     */
    public function getCessazioneLoc(): ?AdrierCessazioneLoc
    {
        return $this->cessazioneLoc;
    }

    /**
     * @param AdrierCessazioneLoc $cessazioneLoc
     *
     * @return AdrierLocalizzazione
     */
    public function setCessazioneLoc(AdrierCessazioneLoc $cessazioneLoc): AdrierLocalizzazione
    {
        $this->cessazioneLoc = $cessazioneLoc;

        return $this;
    }
}

/**
 * Class AdrierCessazioneLoc
 */
class AdrierCessazioneLoc
{
    /**
     * @var string
     */
    protected $dtCessazione;

    /**
     * @var string
     */
    protected $dtDenunciaCess;

    /**
     * @var string
     */
    protected $causale;

    /**
     * AdrierCessazioneLoc constructor.
     */
    public function __construct(?array $cessazioneLoc)
    {
        $this->dtCessazione   = $cessazioneLoc['DT_CESSAZIONE'] ?? null;
        $this->dtDenunciaCess = $cessazioneLoc['DT_DENUNCIA_CESS'] ?? null;
        $this->causale        = $cessazioneLoc['CAUSALE'] ?? null;
    }

    /**
     * @return string
     */
    public function getDtCessazione(): ?string
    {
        return $this->dtCessazione;
    }

    /**
     * @param string $dtCessazione
     *
     * @return AdrierCessazioneLoc
     */
    public function setDtCessazione(string $dtCessazione): AdrierCessazioneLoc
    {
        $this->dtCessazione = $dtCessazione;

        return $this;
    }

    /**
     * @return string
     */
    public function getDtDenunciaCess(): ?string
    {
        return $this->dtDenunciaCess;
    }

    /**
     * @param string $dtDenunciaCess
     *
     * @return AdrierCessazioneLoc
     */
    public function setDtDenunciaCess(string $dtDenunciaCess): AdrierCessazioneLoc
    {
        $this->dtDenunciaCess = $dtDenunciaCess;

        return $this;
    }

    /**
     * @return string
     */
    public function getCausale(): ?string
    {
        return $this->causale;
    }

    /**
     * @param string $causale
     *
     * @return AdrierCessazioneLoc
     */
    public function setCausale(string $causale): AdrierCessazioneLoc
    {
        $this->causale = $causale;

        return $this;
    }
}

class AdrierPersoneLoc
{
    /**
     * @var int
     */
    protected $totalePersone;

    /**
     * @var AdrierPersona[]|ArrayCollection
     */
    protected $persona;

    /**
     * AdrierPersoneLoc constructor.
     */
    public function __construct(?array $personeLoc)
    {
        $this->totalePersone = $personeLoc['@totale_persone'] ?? null;
        $this->persona       = new ArrayCollection();

        if (isset($personeLoc['PERSONA'])) {
            if($this->totalePersone > 1) {
                foreach ($personeLoc['PERSONA'] as $persona) {
                    $this->persona->add(new AdrierPersona($persona ?? null));
                }
            } else {
                $this->persona->add(new AdrierPersona($personeLoc['PERSONA'] ?? null));
            }
        }
    }

    /**
     * @return int
     */
    public function getTotalePersone(): ?int
    {
        return $this->totalePersone;
    }

    /**
     * @param int $totalePersone
     *
     * @return AdrierPersoneLoc
     */
    public function setTotalePersone(int $totalePersone): AdrierPersoneLoc
    {
        $this->totalePersone = $totalePersone;

        return $this;
    }

    /**
     * @return ArrayCollection|AdrierPersona[]
     */
    public function getPersona()
    {
        return $this->persona;
    }

    /**
     * @param ArrayCollection|AdrierPersona[] $persona
     *
     * @return AdrierPersoneLoc
     */
    public function setPersona($persona)
    {
        $this->persona = $persona;

        return $this;
    }

    /**
     * @param AdrierPersona $persona
     */
    public function addPersona(AdrierPersona $persona)
    {
        if (!$this->persona->contains($persona)) {
            $this->persona->add($persona);
        }
    }
}

/**
 * Class AdrierNumeroTipo
 */
class AdrierNumeroTipo
{
    /**
     * @var string
     */
    protected $cciaa;

    /**
     * @var string
     */
    protected $nRea;

    /**
     * @var string
     */
    protected $numero;

    /**
     * @var string
     */
    protected $tipo1;

    /**
     * @var string
     */
    protected $tipo2;

    /**
     * @var string
     */
    protected $tipo3;

    /**
     * @var string
     */
    protected $tipo4;

    /**
     * @var string
     */
    protected $tipo5;

    /**
     * AdrierNumeroTipo constructor.
     */
    public function __construct(?array $numeroTipo)
    {
        $this->cciaa = $numeroTipo['CCIAA'] ?? null;
        $this->cciaa = $numeroTipo['N_REA'] ?? null;
        $this->cciaa = $numeroTipo['NUMERO'] ?? null;
        $this->cciaa = $numeroTipo['TIPO_1'] ?? null;
        $this->cciaa = $numeroTipo['TIPO_2'] ?? null;
        $this->cciaa = $numeroTipo['TIPO_3'] ?? null;
        $this->cciaa = $numeroTipo['TIPO_4'] ?? null;
        $this->cciaa = $numeroTipo['TIPO_5'] ?? null;
    }

    /**
     * @return string
     */
    public function getCciaa(): ?string
    {
        return $this->cciaa;
    }

    /**
     * @param string $cciaa
     *
     * @return AdrierNumeroTipo
     */
    public function setCciaa(string $cciaa): AdrierNumeroTipo
    {
        $this->cciaa = $cciaa;

        return $this;
    }

    /**
     * @return string
     */
    public function getNRea(): ?string
    {
        return $this->nRea;
    }

    /**
     * @param string $nRea
     *
     * @return AdrierNumeroTipo
     */
    public function setNRea(string $nRea): AdrierNumeroTipo
    {
        $this->nRea = $nRea;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumero(): ?string
    {
        return $this->numero;
    }

    /**
     * @param string $numero
     *
     * @return AdrierNumeroTipo
     */
    public function setNumero(string $numero): AdrierNumeroTipo
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * @return string
     */
    public function getTipo1(): ?string
    {
        return $this->tipo1;
    }

    /**
     * @param string $tipo1
     *
     * @return AdrierNumeroTipo
     */
    public function setTipo1(string $tipo1): AdrierNumeroTipo
    {
        $this->tipo1 = $tipo1;

        return $this;
    }

    /**
     * @return string
     */
    public function getTipo2(): ?string
    {
        return $this->tipo2;
    }

    /**
     * @param string $tipo2
     *
     * @return AdrierNumeroTipo
     */
    public function setTipo2(string $tipo2): AdrierNumeroTipo
    {
        $this->tipo2 = $tipo2;

        return $this;
    }

    /**
     * @return string
     */
    public function getTipo3(): ?string
    {
        return $this->tipo3;
    }

    /**
     * @param string $tipo3
     *
     * @return AdrierNumeroTipo
     */
    public function setTipo3(string $tipo3): AdrierNumeroTipo
    {
        $this->tipo3 = $tipo3;

        return $this;
    }

    /**
     * @return string
     */
    public function getTipo4(): ?string
    {
        return $this->tipo4;
    }

    /**
     * @param string $tipo4
     *
     * @return AdrierNumeroTipo
     */
    public function setTipo4(string $tipo4): AdrierNumeroTipo
    {
        $this->tipo4 = $tipo4;

        return $this;
    }

    /**
     * @return string
     */
    public function getTipo5(): ?string
    {
        return $this->tipo5;
    }

    /**
     * @param string $tipo5
     *
     * @return AdrierNumeroTipo
     */
    public function setTipo5(string $tipo5): AdrierNumeroTipo
    {
        $this->tipo5 = $tipo5;

        return $this;
    }
}