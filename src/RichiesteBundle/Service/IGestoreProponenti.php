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
use RichiesteBundle\Service\GestoreResponse;

interface IGestoreProponenti {
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
     * @return Fascicolo
     */
    public function getFascicoloProponente();

    /**
     * @return int
     */
    public function numeroMaxProponenti();

    /**
     * Se solo_obbligatori = 0, ritorna tutti i documenti obbligatori non caricati e quelli caricabili più volte
     * Se solo_obbligatori = 1, ritorna tutti i documenti obbligatori non caricati
     *
     * @param int $id_richiesta
     * @param int $id_proponente
     * @param bool $solo_obbligatori
     */
    public function getTipiDocumentiProponenti($id_richiesta, $id_proponente, $solo_obbligatori);

    /**
     * @param int $id_richiesta
     * @param array $opzioni
     * @return GestoreResponse
     */
    public function elencoProponenti($id_richiesta, $opzioni = array());

    /**
     * @param int $id_richiesta
     * @param array $opzioni
     * @return GestoreResponse
     */
    public function cercaProponente($id_richiesta, $opzioni = array());

    /**
     * @param int $id_richiesta
     * @param array $opzioni
     * @return GestoreResponse
     */
    public function associaProponente($id_richiesta, $id_soggetto, $opzioni = array());

    /**
     * @param int $id_proponente
     * @param array $opzioni
     * @return GestoreResponse
     */
    public function rimuoviProponente($id_proponente, $opzioni = array());

    /**
     * @param int $id_proponente
     * @param array $opzioni
     * @return GestoreResponse
     */
    public function dettagliProponente($id_proponente, $opzioni = array());

    /**
     * @param $id_richiesta
     * @param array $opzioni
     * @return GestoreResponse
     */
    public function modificaFirmatario($id_richiesta, $opzioni = array());

    /**
     * @param $id_richiesta
     * @param array $opzioni
     * @return EsitoValidazione
     */
    public function validaProponenti($id_richiesta, $opzioni = array());

    /**
     * @param $id_proponente
     * @param array $opzioni
     * @return EsitoValidazione
     */
    public function validaProponente($id_proponente, $opzioni = array());

    /**
     * @param $id_proponente
     * @param array $opzioni
     * @return GestoreResponse
     */
    public function cercaReferente($id_proponente, $opzioni = array());

    /**
     * @param int $id_proponente
     * @param int $id_persona
     * @param array $opzioni
     * @param string|NULL $twig = null
     * @return GestoreResponse
     */
    public function inserisciReferente($id_proponente, $id_persona, $opzioni = array(), $twig = null);

    /**
     * @param int $id_referente
     * @param array $opzioni
     * @return GestoreResponse
     */
    public function rimuoviReferente($id_referente, $opzioni = array());

    /**
     * @param int $id_referente
     * @param array $opzioni
     * @return GestoreResponse
     */
    public function dettagliReferente($id_referente, $opzioni = array());

    public function getTipiReferenzaAmmessi();

    /**
     * @param int $id_richiesta
     * @param array $opzioni
     * @return GestoreResponse
     */
    public function elencoDocumentiProponente($id_richiesta, $id_proponente, $opzioni = array());

    /**
     * @param int $id_proponente
     * @param array $opzioni
     * @return GestoreResponse
     */
    public function caricaDocumentoProponente($id_proponente, $opzioni = array());

    /**
     * @param int $id_documento_proponente
     * @param array $opzioni
     * @return GestoreResponse
     */
    public function eliminaDocumentoProponente($id_documento_proponente, $opzioni = array());

    public function validaDocumentiProponente($id_proponente, $opzioni = array());

    public function dettaglioProfessionista($id_richiesta, $id_proponente);

    /**
     * @param $indice_proponente
     * @return mixed
     */
    public function inizializzaIstanzaFascicoloProponente($indice_proponente);
}
