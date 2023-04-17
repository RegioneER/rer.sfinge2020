<?php

namespace RichiesteBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;
use Doctrine\ORM\EntityManager;
use RichiesteBundle\Form\RicercaRichiestaType;

abstract class RicercaRichiesta extends AttributiRicerca {
    protected $stato;

    protected $procedura;

    protected $titoloProgetto;

    protected $protocollo;

    protected $ragioneSocialeProponente;

    protected $codiceFiscaleProponente;

    protected $utente;

    protected $finestraTemporale;

    protected $opzioni;

    protected $statiRichiesta;

    protected $token_storage;

    public function __construct() {
    }

    public function getStato() {
        return $this->stato;
    }

    public function setStato($stato) {
        $this->stato = $stato;
    }

    public function getProcedura() {
        return $this->procedura;
    }

    public function getTitoloProgetto() {
        return $this->titoloProgetto;
    }

    public function getProtocollo() {
        return $this->protocollo;
    }

    public function getRagioneSocialeProponente() {
        return $this->ragioneSocialeProponente;
    }

    public function getCodiceFiscaleProponente() {
        return $this->codiceFiscaleProponente;
    }

    public function setProcedura($procedura) {
        $this->procedura = $procedura;
    }

    public function setTitoloProgetto($titoloProgetto) {
        $this->titoloProgetto = $titoloProgetto;
    }

    public function setProtocollo($protocollo) {
        $this->protocollo = $protocollo;
    }

    public function setRagioneSocialeProponente($ragioneSocialeProponente) {
        $this->ragioneSocialeProponente = $ragioneSocialeProponente;
    }

    public function setCodiceFiscaleProponente($codiceFiscaleProponente) {
        $this->codiceFiscaleProponente = $codiceFiscaleProponente;
    }

    public function getUtente() {
        return $this->utente;
    }

    public function setUtente($utente) {
        $this->utente = $utente;
    }

    public function getOpzioni() {
        return $this->opzioni;
    }

    public function setOpzioni($opzioni) {
        $this->opzioni = $opzioni;
    }

    public function getType() {
        return RicercaRichiestaType::class;
    }

    public function getNomeRepository() {
        return "RichiesteBundle:Richiesta";
    }

    public function getNomeMetodoRepository() {
        return "getRichiesteVisibiliPA";
    }

    public function getNumeroElementiPerPagina() {
        return null;
    }

    public function getNomeParametroPagina() {
        return "page";
    }

    public function mergeFreshData($freshData) {
        $this->setUtente($freshData->getUtente());
    }

    public function getFinestraTemporale() {
        return $this->finestraTemporale;
    }

    public function setFinestraTemporale($finestraTemporale) {
        $this->finestraTemporale = $finestraTemporale;
    }

    /**
     * @param array $options opzioni del form
     */
    abstract public function getQueryRicercaProcedura(EntityManager $em, array $options);
}
