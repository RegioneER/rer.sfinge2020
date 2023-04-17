<?php

namespace AttuazioneControlloBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping AS ORM;


/**
 * @ORM\Entity()
 * @ORM\Table(name="rendicontazione_procedure_config")
 */
class RendicontazioneProceduraConfig {
    
    const PRIVATO = 'PRIVATO';
    const PUBBLICO = 'PUBBLICO';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="SfingeBundle\Entity\Procedura", inversedBy="rendicontazioneProceduraConfig")
     * @ORM\JoinColumn(name="procedura_id", nullable=false)
     */
    protected $procedura;

    /**
     * @ORM\Column(type="boolean", nullable= false, options={"default" : 0}, name= "sezione_atti" )
     */
    protected $sezioneAtti;
    
    /**
     * @ORM\Column(type="boolean", nullable= false, options={"default" : 0}, name= "sezione_contratti" )
     */
    protected $sezioneContratti;
    
    /**
     * @ORM\Column(type="boolean", nullable= false, options={"default" : 0}, name= "sezione_personale" )
     */
    protected $sezionePersonale;

    /**
     * @ORM\Column(type="boolean", nullable= false, options={"default" : 0}, name= "sezione_durc" )
     */
    protected $sezioneDurc;

    /**
     * @ORM\Column(type="boolean", nullable= false, options={"default" : 0}, name= "sezione_antimafia" )
     */
    protected $sezioneAntimafia;
    
    /**
     * @ORM\Column(type="boolean", nullable= false, options={"default" : 0}, name= "sezione_rsi" )
     */
    protected $sezioneRSI;
    
    /**
     * @ORM\Column(type="boolean", nullable= false, options={"default" : 0}, name= "sezione_relazione_tecnica" )
     */
    protected $sezioneRelazioneTecnica;

    /**
     * @ORM\Column(type="boolean", nullable= false, options={"default" : 0}, name= "sezione_video" )
     */
    protected $sezioneVideo;
    
    /**
     * @ORM\Column(type="boolean", nullable= false, options={"default" : 0}, name= "rendicontazione_multi_proponente" )
     */
    protected $rendicontazioneMultiProponente;
    
    /**
     * @ORM\Column(type="string", length=1000, nullable= true, name="link_documenti_progetto")
     */
    protected $linkDocumentiProgetto;
    
    /**
     * usato per implementare comportamenti specifici di una tipologia piuttosto che di un'altra
     * ad oggi abbiamo pubblico e privato e spero finisca qui
     * @ORM\Column(type="string", nullable= true, name="tipologia")
     */
    protected $tipologia;
    
    /**
     * @ORM\Column(type="boolean", nullable= false, options={"default" : 0}, name= "spese_generali" )
     */
    protected $speseGenerali;

    /**
     * @ORM\Column(type="boolean", nullable= false, options={"default" : 0}, name= "incremento_occupazionale" )
     */
    protected $incrementoOccupazionale;

    /**
     * @ORM\Column(type="text", nullable= true, name="avviso_sezione_incremento_occupazionale")
     */
    protected $avvisoSezioneIncrementoOccupazionale;

    /**
     * @var DateTime|null
     * @ORM\Column(type="date", nullable=true, name= "data_inizio_progetto" )
     */
    protected $dataInizioProgetto;

    /**
     * @ORM\Column(type="boolean", nullable= false, options={"default" : 0}, name= "incremento_occupazionale_nuovi_dipendenti" )
     */
    protected $incrementoOccupazionaleNuoviDipendenti;

    /**
     * @ORM\Column(type="array", nullable= true, name="incremento_occupazionale_documenti_obbligatori")
     */
    protected $incrementoOccupazionaleDocumentiObbligatori;
    
    /**
     * serve a visualizzare un eventuale messaggio nella sezione dei documenti progetto lato beneficiario
     * 
     * @ORM\Column(name="avviso_sezione_documenti_progetto", type="text", nullable= true)
     */
    protected $avvisoSezioneDocumentiProgetto;
    
    /**
     * serve a visualizzare un eventuale messaggio nella sezione del dettaglio giustificativo lato beneficiario
     * 
     * @ORM\Column(name="avviso_sezione_giustificativo", type="text", nullable= true)
     */
    protected $avvisoSezioneGiustificativo;
    
    /**
     * serve a visualizzare un eventuale messaggio nella sezione del dettaglio pagamento lato beneficiario
     * 
     * @ORM\Column(name="avviso_sezione_dettaglio_pagamento", type="text", nullable= true)
     */
    protected $avvisoSezioneDettaglioPagamento;
    
    /**
     * serve a visualizzare un eventuale messaggio nella sezione di elenco pagamenti lato beneficiario
     * 
     * @ORM\Column(name="avviso_sezione_elenco_pagamenti", type="text", nullable= true)
     */
    protected $avvisoSezioneElencoPagamenti;

    /**
     * @ORM\Column(type="integer", nullable= true, name="finestra_temporale_richiesta")
     */
    protected $finestraTemporaleRichiesta;	
		
	/**
	 * @var array|null
	 *
	 * @ORM\Column(name="giorni_per_risposta_comunicazioni", type="array", nullable=true)
	 */
	protected $giorniPerRispostaComunicazioni;
    
    /**
     * @ORM\Column(type="boolean", nullable= false, options={"default" : 0}, name= "spese_personale" )
     */
    protected $spesePersonale;

    /**
     * Personalizza la dichiarazione inizio dell'incremento occupazionale
     * 
     * @ORM\Column(type="text", nullable= true, name="etichetta_inizio_incremento_occupazionale")
     */
    protected $etichettaInizioIncrementoOccupazionale;

    /**
     * 
     * Personalizza la dichiarazione fine dell'incremento occupazionale
     * 
     * @ORM\Column(type="text", nullable= true, name="etichetta_fine_incremento_occupazionale")
     */
    protected $etichettaFineIncrementoOccupazionale;

    /**
     * Personalizza colonna inizio incremento occupazionale
     * 
     * @ORM\Column(type="text", nullable= true, name="colonna_inizio_incremento_occupazionale")
     */
    protected $colonnaInizioIncrementoOccupazionale;

    /**
     * Personalizza colonna fine incremento occupazionale
     * 
     * @ORM\Column(type="text", nullable= true, name="colonna_fine_incremento_occupazionale")
     */
    protected $colonnaFineIncrementoOccupazionale;

    /**
     * Personalizza etichetta allegare DM 10 inizio
     *
     * @ORM\Column(type="text", nullable= true, name="etichetta_inizio_allegare_dm_10")
     */
    protected $etichettaInizioAllegareDM10;

    /**
     * Personalizza etichetta allegare DM 10 fine
     *
     * @ORM\Column(type="text", nullable= true, name="etichetta_fine_allegare_dm_10")
     */
    protected $etichettaFineAllegareDM10;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable= false, options={"default" : 1}, name= "richiesta_firma_digitale" )
     */
    protected $richiesta_firma_digitale;

    // fallback... se non definito esplicitamente a db
    public function __construct() {
        // default
        $this->setSezioneRSI(true);
        $this->setSezioneAntimafia(true);
        $this->setSezioneAtti(false);
        $this->setSezioneContratti(false);
        $this->setSezionePersonale(false);
        // al momento la sezione relazione tecnica non la facciamo e facciamo caricare un documento obbligatorio
        $this->setSezioneRelazioneTecnica(false);
        $this->setSezioneVideo(false);
        $this->setRendicontazioneMultiProponente(false);
        $this->setTipologia(self::PRIVATO);
        $this->setSpeseGenerali(false);
        $this->setSpesePersonale(false);
        $this->setIncrementoOccupazionale(false);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getProcedura()
    {
        return $this->procedura;
    }

    /**
     * @return mixed
     */
    public function getSezioneAtti()
    {
        return $this->sezioneAtti;
    }

    /**
     * @return mixed
     */
    public function getSezioneContratti()
    {
        return $this->sezioneContratti;
    }

    /**
     * @return mixed
     */
    public function getSezionePersonale()
    {
        return $this->sezionePersonale;
    }

    /**
     * @return mixed
     */
    public function getSezioneDurc()
    {
        return $this->sezioneDurc;
    }

    /**
     * @return mixed
     */
    public function getSezioneAntimafia()
    {
        return $this->sezioneAntimafia;
    }

    /**
     * @return mixed
     */
    public function getSezioneRSI()
    {
        return $this->sezioneRSI;
    }

    /**
     * @return mixed
     */
    public function getSezioneRelazioneTecnica()
    {
        return $this->sezioneRelazioneTecnica;
    }

    /**
     * @return mixed
     */
    public function getSezioneVideo()
    {
        return $this->sezioneVideo;
    }

    /**
     * @return mixed
     */
    public function getRendicontazioneMultiProponente()
    {
        return $this->rendicontazioneMultiProponente;
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param $procedura
     */
    public function setProcedura($procedura)
    {
        $this->procedura = $procedura;
    }

    /**
     * @param $sezioneAtti
     */
    public function setSezioneAtti($sezioneAtti)
    {
        $this->sezioneAtti = $sezioneAtti;
    }

    /**
     * @param $sezioneContratti
     */
    public function setSezioneContratti($sezioneContratti)
    {
        $this->sezioneContratti = $sezioneContratti;
    }

    /**
     * @param $sezionePersonale
     */
    public function setSezionePersonale($sezionePersonale)
    {
        $this->sezionePersonale = $sezionePersonale;
    }

    /**
     * @param mixed $sezioneDurc
     */
    public function setSezioneDurc($sezioneDurc): void
    {
        $this->sezioneDurc = $sezioneDurc;
    }

    /**
     * @param $sezioneAntimafia
     */
    public function setSezioneAntimafia($sezioneAntimafia)
    {
        $this->sezioneAntimafia = $sezioneAntimafia;
    }

    /**
     * @param $sezioneRSI
     */
    public function setSezioneRSI($sezioneRSI)
    {
        $this->sezioneRSI = $sezioneRSI;
    }

    /**
     * @param $sezioneRelazioneTecnica
     */
    public function setSezioneRelazioneTecnica($sezioneRelazioneTecnica)
    {
        $this->sezioneRelazioneTecnica = $sezioneRelazioneTecnica;
    }

    /**
     * @param mixed $sezioneVideo
     */
    public function setSezioneVideo($sezioneVideo): void
    {
        $this->sezioneVideo = $sezioneVideo;
    }

    /**
     * @param $rendicontazioneMultiProponente
     */
    public function setRendicontazioneMultiProponente($rendicontazioneMultiProponente)
    {
        $this->rendicontazioneMultiProponente = $rendicontazioneMultiProponente;
    }

    /**
     * @return mixed
     */
    public function getLinkDocumentiProgetto()
    {
        return $this->linkDocumentiProgetto;
    }

    /**
     * @param $linkDocumentiProgetto
     */
    public function setLinkDocumentiProgetto($linkDocumentiProgetto)
    {
        $this->linkDocumentiProgetto = $linkDocumentiProgetto;
    }

    /**
     * @return mixed
     */
    public function getTipologia()
    {
        return $this->tipologia;
    }

    /**
     * @param $tipologia
     */
    public function setTipologia($tipologia)
    {
        $this->tipologia = $tipologia;
    }

    /**
     * @return bool
     */
    public function isPrivato()
    {
        return $this->tipologia == self::PRIVATO;
    }

    /**
     * @return bool
     */
    public function isPubblico()
    {
        return $this->tipologia == self::PUBBLICO;
    }

    /**
     * @return mixed
     */
    public function getSpeseGenerali()
    {
        return $this->speseGenerali;
    }

    /**
     * @param $speseGenerali
     */
    public function setSpeseGenerali($speseGenerali)
    {
        $this->speseGenerali = $speseGenerali;
    }

    /**
     * @return bool
     */
    public function hasSpeseGenerali()
    {
        return $this->speseGenerali == true;
    }

    /**
     * @return mixed
     */
    public function getIncrementoOccupazionale()
    {
        return $this->incrementoOccupazionale;
    }

    /**
     * @param mixed $incrementoOccupazionale
     */
    public function setIncrementoOccupazionale($incrementoOccupazionale): void
    {
        $this->incrementoOccupazionale = $incrementoOccupazionale;
    }

    /**
     * @return mixed
     */
    public function getAvvisoSezioneIncrementoOccupazionale()
    {
        return $this->avvisoSezioneIncrementoOccupazionale;
    }

    /**
     * @param mixed $avvisoSezioneIncrementoOccupazionale
     */
    public function setAvvisoSezioneIncrementoOccupazionale($avvisoSezioneIncrementoOccupazionale): void
    {
        $this->avvisoSezioneIncrementoOccupazionale = $avvisoSezioneIncrementoOccupazionale;
    }

    /**
     * @return DateTime|null
     */
    public function getDataInizioProgetto(): ?DateTime
    {
        return $this->dataInizioProgetto;
    }

    /**
     * @param DateTime|null $dataInizioProgetto
     */
    public function setDataInizioProgetto(?DateTime $dataInizioProgetto): void
    {
        $this->dataInizioProgetto = $dataInizioProgetto;
    }
    
    /**
     * @return mixed
     */
    public function getIncrementoOccupazionaleNuoviDipendenti()
    {
        return $this->incrementoOccupazionaleNuoviDipendenti;
    }

    /**
     * @param mixed $incrementoOccupazionaleNuoviDipendenti
     */
    public function setIncrementoOccupazionaleNuoviDipendenti($incrementoOccupazionaleNuoviDipendenti): void
    {
        $this->incrementoOccupazionaleNuoviDipendenti = $incrementoOccupazionaleNuoviDipendenti;
    }
    
    /**
     * @return mixed
     */
    public function getIncrementoOccupazionaleDocumentiObbligatori()
    {
        return $this->incrementoOccupazionaleDocumentiObbligatori;
    }

    /**
     * @param mixed $incrementoOccupazionaleDocumentiObbligatori
     */
    public function setIncrementoOccupazionaleDocumentiObbligatori($incrementoOccupazionaleDocumentiObbligatori): void
    {
        $this->incrementoOccupazionaleDocumentiObbligatori = $incrementoOccupazionaleDocumentiObbligatori;
    }

    /**
     * @return mixed
     */
    public function getAvvisoSezioneDocumentiProgetto()
    {
        return $this->avvisoSezioneDocumentiProgetto;
    }

    /**
     * @return mixed
     */
    public function getAvvisoSezioneGiustificativo()
    {
        return $this->avvisoSezioneGiustificativo;
    }

    /**
     * @param $avvisoSezioneDocumentiProgetto
     */
    public function setAvvisoSezioneDocumentiProgetto($avvisoSezioneDocumentiProgetto)
    {
        $this->avvisoSezioneDocumentiProgetto = $avvisoSezioneDocumentiProgetto;
    }

    /**
     * @param $avvisoSezioneGiustificativo
     */
    public function setAvvisoSezioneGiustificativo($avvisoSezioneGiustificativo)
    {
        $this->avvisoSezioneGiustificativo = $avvisoSezioneGiustificativo;
    }

    /**
     * @return mixed
     */
    public function getAvvisoSezioneDettaglioPagamento()
    {
        return $this->avvisoSezioneDettaglioPagamento;
    }

    /**
     * @param $avvisoSezioneDettaglioPagamento
     */
    public function setAvvisoSezioneDettaglioPagamento($avvisoSezioneDettaglioPagamento)
    {
        $this->avvisoSezioneDettaglioPagamento = $avvisoSezioneDettaglioPagamento;
    }

    /**
     * @return mixed
     */
    public function getAvvisoSezioneElencoPagamenti()
    {
        return $this->avvisoSezioneElencoPagamenti;
    }

    /**
     * @param $avvisoSezioneElencoPagamenti
     */
    public function setAvvisoSezioneElencoPagamenti($avvisoSezioneElencoPagamenti)
    {
        $this->avvisoSezioneElencoPagamenti = $avvisoSezioneElencoPagamenti;
    }

    /**
     * @return mixed
     */
    public function getFinestraTemporaleRichiesta()
    {
        return $this->finestraTemporaleRichiesta;
    }

    /**
     * @param $finestraTemporaleRichiesta
     */
    public function setFinestraTemporaleRichiesta($finestraTemporaleRichiesta)
    {
        $this->finestraTemporaleRichiesta = $finestraTemporaleRichiesta;
    }

    /**
     * @return array|null
     */
    public function getGiorniPerRispostaComunicazioni(): ?array
    {
        return $this->giorniPerRispostaComunicazioni;
    }

    /**
     * @param array|null $giorniPerRispostaComunicazioni
     */
    public function setGiorniPerRispostaComunicazioni(?array $giorniPerRispostaComunicazioni): void
    {
        $this->giorniPerRispostaComunicazioni = $giorniPerRispostaComunicazioni;
    }

    /**
     * @return mixed
     */
    public function getSpesePersonale()
    {
        return $this->spesePersonale;
    }

    /**
     * @param $spesePersonale
     */
    public function setSpesePersonale($spesePersonale)
    {
        $this->spesePersonale = $spesePersonale;
    }

    /**
     * @return bool
     */
    public function hasSpesePersonale() :bool
    {
        return $this->spesePersonale == true;
    }

    /**
     * @return mixed
     */
    public function getEtichettaInizioIncrementoOccupazionale()
    {
        return $this->etichettaInizioIncrementoOccupazionale;
    }

    /**
     * @return mixed
     */
    public function getEtichettaFineIncrementoOccupazionale()
    {
        return $this->etichettaFineIncrementoOccupazionale;
    }

    /**
     * @return mixed
     */
    public function getColonnaInizioIncrementoOccupazionale()
    {
        return $this->colonnaInizioIncrementoOccupazionale;
    }

    /**
     * @return mixed
     */
    public function getColonnaFineIncrementoOccupazionale()
    {
        return $this->colonnaFineIncrementoOccupazionale;
    }

    /**
     * @param $etichettaInizioIncrementoOccupazionale
     */
    public function setEtichettaInizioIncrementoOccupazionale($etichettaInizioIncrementoOccupazionale)
    {
        $this->etichettaInizioIncrementoOccupazionale = $etichettaInizioIncrementoOccupazionale;
    }

    /**
     * @param $etichettaFineIncrementoOccupazionale
     */
    public function setEtichettaFineIncrementoOccupazionale($etichettaFineIncrementoOccupazionale)
    {
        $this->etichettaFineIncrementoOccupazionale = $etichettaFineIncrementoOccupazionale;
    }

    /**
     * @param $colonnaInizioIncrementoOccupazionale
     */
    public function setColonnaInizioIncrementoOccupazionale($colonnaInizioIncrementoOccupazionale)
    {
        $this->colonnaInizioIncrementoOccupazionale = $colonnaInizioIncrementoOccupazionale;
    }

    /**
     * @param $colonnaFineIncrementoOccupazionale
     */
    public function setColonnaFineIncrementoOccupazionale($colonnaFineIncrementoOccupazionale)
    {
        $this->colonnaFineIncrementoOccupazionale = $colonnaFineIncrementoOccupazionale;
    }

    /**
     * @return mixed
     */
    public function getEtichettaInizioAllegareDM10()
    {
        return $this->etichettaInizioAllegareDM10;
    }

    /**
     * @param mixed $etichettaInizioAllegareDM10
     */
    public function setEtichettaInizioAllegareDM10($etichettaInizioAllegareDM10): void
    {
        $this->etichettaInizioAllegareDM10 = $etichettaInizioAllegareDM10;
    }

    /**
     * @return mixed
     */
    public function getEtichettaFineAllegareDM10()
    {
        return $this->etichettaFineAllegareDM10;
    }

    /**
     * @param mixed $etichettaFineAllegareDM10
     */
    public function setEtichettaFineAllegareDM10($etichettaFineAllegareDM10): void
    {
        $this->etichettaFineAllegareDM10 = $etichettaFineAllegareDM10;
    }

    /**
     * @return bool
     */
    public function isRichiestaFirmaDigitale(): bool
    {
        return $this->richiesta_firma_digitale;
    }

    /**
     * @param bool $richiesta_firma_digitale
     */
    public function setRichiestaFirmaDigitale(bool $richiesta_firma_digitale): void
    {
        $this->richiesta_firma_digitale = $richiesta_firma_digitale;
    }
}
