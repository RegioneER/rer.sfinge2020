<?php

/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 21/01/16
 * Time: 17:23
 */

namespace RichiesteBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use FascicoloBundle\Entity\Fascicolo;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Utility\EsitoValidazione;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\HttpFoundation\Response;
use RichiesteBundle\Entity\IndicatoreOutput;

interface IGestoreRichiesta {

    /**
     * @return Procedura
     */
    public function getProcedura();

    /**
     * @return Richiesta
     */
    public function getRichiesta();

    /**
     * @return ArrayCollection
     */
    public function getProponenti();

    /**
     * @return Proponente
     */
    public function getCapofila();

    /**
     * Metodo che controlla se il proponente selezionato e coerente con la domanda
     * @return string|null
     */
    public function controllaCapofila();

    public function getPianiDeiCosti();

    /**
     * @return Fascicolo
     */
    public function getFascicoli();

    /**
     * @return integer
     */
    public function numeroMaxProponenti();

    /**
     * metodo che torna un array con in chiave la label da mostrare nel link e il link a cui andare
     * @param $id_richiesta
     * @return array
     */
    public function dammiVociMenuElencoRichieste($id_richiesta);

    /**
     * Se solo_obbligatori = 0, ritorna tutti i documenti obbligatori non caricati e quelli caricabili più volte
     * Se solo_obbligatori = 1, ritorna tutti i documenti obbligatori non caricati
     * 
     * @param int $id_richiesta
     * @param int|bool $solo_obbligatori
     */
    public function getTipiDocumenti($id_richiesta, $solo_obbligatori);

    /**
     * @param int $id_bando
     * @param array $opzioni
     * @return GestoreResponse
     */
    public function nuovaRichiesta($id_bando, $opzioni = array());

    /**
     * @param int $id_richiesta
     * @param array $opzioni
     * @return GestoreResponse
     */
    public function datiGenerali($id_richiesta, $opzioni = array());

    public function validaDatiGenerali(Richiesta $richiesta, $opzioni = array());

    /**
     * @param int $id_richiesta
     * @param array $opzioni
     * @return GestoreResponse
     */
    public function datiMarcaDaBollo($id_richiesta, $opzioni = array());

    public function validaDatiMarcaDaBollo(Richiesta $richiesta, $opzioni = array());

    /**
     * @param int $id_richiesta
     * @param array $opzioni
     * @return GestoreResponse
     */
    public function elencoDocumenti($id_richiesta, $opzioni = array());

    /**
     * @param int $id_documento_oggetto
     * @param array $opzioni
     * @return GestoreResponse
     */
    public function eliminaDocumentoRichiesta($id_documento_oggetto, $opzioni = array());

    public function validaDocumenti($id_richiesta, $opzioni = array());

    /**
     * @param int $id_richiesta
     * @param array $opzioni
     * @return GestoreResponse
     */
    public function dettaglioRichiesta($id_richiesta, $opzioni = array());

    /**
     * @param int $id_richiesta
     * @param array $opzioni
     * @return GestoreResponse
     */
    public function gestioneDatiProgetto($id_richiesta, $opzioni = array());

    public function validaDatiProgetto($id_richiesta, $opzioni = array());

    /**
     * @param int $id_richiesta
     * @param array $opzioni
     * @return EsitoValidazione
     */
    public function controllaValiditaRichiesta($id_richiesta, $opzioni = array());

    /**
     * @param $id_richiesta
     * @param array $opzioni
     * @return GestoreResponse
     */
    public function validaRichiesta($id_richiesta, $opzioni = array());

    /**
     * @param $id_richiesta
     * @param array $opzioni
     * @return GestoreResponse
     */
    public function invalidaRichiesta($id_richiesta, $opzioni = array());

    /**
     * @param $id_richiesta
     * @param array $opzioni
     * @return GestoreResponse
     */
    public function inviaRichiesta($id_richiesta, $opzioni = array());

    public function generaPdf($id_richiesta, $facsimile = true, $download = true);

    public function isRichiestaDisabilitata();

    public function hasSezionePriorita();

    public function hasSezioneAmbitiTematiciS3(): bool;

    public function hasDichiarazioniDsnh();

    public function isPrioritaRichiesta();
    public function isAmbitiTematiciS3Richiesta(): bool;

    public function hasSistemiProduttiviMultipli();

    public function aggiungiEdificioPlesso($id_richiesta);

    public function gestioneIndicatoreOutput(Richiesta $richiesta, array $options = []): GestoreResponse;

    public function validaIndicatoriOutput(Richiesta $richiesta): EsitoValidazione;

    public function gestioneProceduraAggiudicazione(Richiesta $richiesta): Response;

    public function validaProceduraAggiudicazione(Richiesta $richiesta): EsitoValidazione;

    public function gestioneModificaProceduraAggiudicazione(Richiesta $richiesta, $id_procedura_aggiudicazione);

    public function gestioneEliminaProceduraAggiudicazione(Richiesta $richiesta, $id_procedura_aggiudicazione);

    public function gestioneIterProgetto(Richiesta $richiesta): Response;
}
