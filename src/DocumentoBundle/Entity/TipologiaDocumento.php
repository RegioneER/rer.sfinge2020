<?php

namespace DocumentoBundle\Entity;

use BaseBundle\Entity\EntityTipo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="tipi_documento")
 * @ORM\Entity(repositoryClass="DocumentoBundle\Entity\TipologiaDocumentoRepository")
 * @UniqueEntity(fields={"codice"}, message="Codice già presente")
 */
class TipologiaDocumento extends EntityTipo {
    public const ALTRO = "ALTRO";
    public const ATTO_REVOCA = "ATTO_REVOCA";
    public const CI = "CI";
    public const CI_LR = "CI_LR";
    public const ATTO_NOMINA_LR = "ATTO_NOMINA_LR";
    public const DELEGA_DELEGATO = "DELEGA_DELEGATO";
    public const ATTO_AMMINISTRATIVO = "ATTO_AMMINISTRATIVO";

    public const RICHIESTA_CONTRIBUTO = "RICHIESTA_CONTRIBUTO";
    public const RICHIESTA_CONTRIBUTO_FIRMATO = "RICHIESTA_CONTRIBUTO_FIRMATO";
    public const DOCUMENTO_MARCA_DA_BOLLO_DIGITALE = "DOCUMENTO_MARCA_DA_BOLLO_DIGITALE";

    public const RICHIESTA_INTEGRAZIONE_RICHIESTA = "RICHIESTA_INTEGRAZIONE_RICHIESTA";
    public const RICHIESTA_INTEGRAZIONE_RISPOSTA = "RICHIESTA_INTEGRAZIONE_RISPOSTA";
    public const RICHIESTA_INTEGRAZIONE_RISPOSTA_FIRMATO = "RICHIESTA_INTEGRAZIONE_RISPOSTA_FIRMATO";

    public const RICHIESTA_PROROGA = "RICHIESTA_PROROGA";
    public const RICHIESTA_PROROGA_FIRMATA = "RICHIESTA_PROROGA_FIRMATA";

    public const PAGAMENTO_CONTRIBUTO = "PAGAMENTO_CONTRIBUTO";
    public const PAGAMENTO_CONTRIBUTO_FIRMATO = "PAGAMENTO_CONTRIBUTO_FIRMATO";

    public const VARIAZIONE_RICHIESTA = "VARIAZIONE_RICHIESTA";
    public const VARIAZIONE_RICHIESTA_FIRMATA = "VARIAZIONE_RICHIESTA_FIRMATA";

    public const RICHIESTA_INTEGRAZIONE_PAGAMENTO = "RICHIESTA_INTEGRAZIONE_PAGAMENTO";
    public const RISPOSTA_INTEGRAZIONE_PAGAMENTO = "RISPOSTA_INTEGRAZIONE_PAGAMENTO";

    public const ESITO_ISTRUTTORIA_PAGAMENTO_ALTRO = "ESITO_ISTRUTTORIA_PAGAMENTO_ALTRO";
    public const ESITO_ISTRUTTORIA_PAGAMENTO_DET_DIR = "ESITO_ISTRUTTORIA_PAGAMENTO_DET_DIR";

    public const ESITO_ISTRUTTORIA = "ESITO_ISTRUTTORIA";
    public const COMUNICAZIONE_ESITO_ISTRUTTORIA = "COMUNICAZIONE_ESITO_ISTRUTTORIA";
    public const COMUNICAZIONE_ESITO_RISPOSTA = "COMUNICAZIONE_ESITO_RISPOSTA";
    public const ESITO_RISPOSTA_ISTRUTTORIA = "ESITO_RISPOSTA_ISTRUTTORIA";
    public const ESITO_RISPOSTA_ISTRUTTORIA_FIRMATO = "ESITO_RISPOSTA_ISTRUTTORIA_FIRMATO";

    public const RICHIESTA_CHIARIMENTI = "RICHIESTA_CHIARIMENTI";
    public const RISPOSTA_RICHIESTA_CHIARIMENTI = "RISPOSTA_RICHIESTA_CHIARIMENTI";
    public const RICH_CHIAR_RISPOSTA_FIRMATO = "RICH_CHIAR_RISPOSTA_FIRMATO";

    public const INCREMENTO_OCCUPAZIONALE = "INCREMENTO_OCCUPAZIONALE";
    public const RELAZIONE_FINALE_A_SALDO = "RELAZIONE_FINALE_A_SALDO";

    public const COMUNICAZIONE_PROGETTO_RICHIESTA = "COMUNICAZIONE_PROGETTO_RICHIESTA";
    public const COMUNICAZIONE_PROGETTO_RISPOSTA = "COMUNICAZIONE_PROGETTO_RISPOSTA";
    public const COMUNICAZIONE_PROGETTO_RISPOSTA_FIRMATO = "COMUNICAZIONE_PROGETTO_RISPOSTA_FIRMATO";
    public const COMUNICAZIONE_RICHIESTA_ALLEGATO = "COMUNICAZIONE_RICHIESTA_ALLEGATO";
    public const COMUNICAZIONE_RISPOSTA_ALLEGATO = "COMUNICAZIONE_RISPOSTA_ALLEGATO";
    public const DOC_IMPEGNO = 'DOC_IMPEGNO';
    public const ALLEGATO_RICHIESTA_CHIARIMENTI = "ALLEGATO_RICHIESTA_CHIARIMENTI";
    public const IMPORTAZIONE_IMPEGNI_PAGAMENTI = 'IMPORTAZIONE_IMPEGNI_PAGAMENTI';

    public const COMUNICAZIONE_PAGAMENTO = "COMUNICAZIONE_PAGAMENTO";
    public const COMUNICAZIONE_PAGAMENTO_ALLEGATO = "COMUNICAZIONE_PAGAMENTO_ALLEGATO";
    public const COMUNICAZIONE_PAGAMENTO_RISPOSTA = "COMUNICAZIONE_PAGAMENTO_RISPOSTA";
    public const COMUNICAZIONE_PAGAMENTO_RISPOSTA_ALLEGATO = "COMUNICAZIONE_PAGAMENTO_RISPOSTA_ALLEGATO";
    public const COMUNICAZIONE_PAGAMENTO_RISPOSTA_FIRMATO = "COMUNICAZIONE_PAGAMENTO_RISPOSTA_FIRMATO";

    /**
     * @param int $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @var bool
     * @ORM\Column(name="firma_digitale", type="boolean", nullable=true)
     */
    protected $firma_digitale;

    /**
     * @var bool
     * @ORM\Column(name="autocertificazione", type="boolean", nullable=true)
     */
    protected $autocertificazione;

    /**
     * @var bool
     * @ORM\Column(name="con_scadenza", type="boolean", nullable=true)
     */
    protected $con_scadenza;

    /**
     * @var int
     *
     * @ORM\Column(name="durata_validita", type="integer", nullable=true)
     */
    protected $durata_validita;

    /**
     * @var int
     *
     * @ORM\Column(name="dimensione_massima", type="integer", nullable=false, options={"default" : 10})
     */
    protected $dimensione_massima;

    /**
     * @var bool
     * @ORM\Column(name="dropzone", type="boolean", nullable=false, options={"default": 0})
     */
    protected $dropzone;

    /**
     * @var string
     *
     * @ORM\Column(name="mime_ammessi", type="string", length=512, nullable=false, options={"default" : "application/pdf"})
     */
    protected $mime_ammessi;

    /**
     * @var bool
     * @ORM\Column(name="obbligatorio", type="boolean", nullable=true)
     */
    protected $obbligatorio;

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Procedura", inversedBy="documenti_richiesti")
     * @ORM\JoinColumn(name="procedura_id", referencedColumnName="id", nullable=true)
     */
    protected $procedura;

    /**
     * @var bool
     * @ORM\Column(name="abilita_duplicati", type="boolean", nullable=true)
     */
    protected $abilita_duplicati;

    /**
     * @var string
     *
     * @ORM\Column(name="tipologia", type="string", length=255, nullable=true, options={"default" : ""})
     */
    protected $tipologia;

    /**
     * @var string
     *
     * @ORM\Column(name="prefix", type="string", length=50, nullable=true, options={"default" : ""})
     */
    protected $prefix;

    /**
     * @return bool
     */
    public function isAutocertificazione() {
        return $this->autocertificazione;
    }

    /**
     * @param bool $autocertificazione
     */
    public function setAutocertificazione($autocertificazione) {
        $this->autocertificazione = $autocertificazione;
    }

    /**
     * @return bool
     */
    public function isConScadenza() {
        return $this->con_scadenza;
    }

    /**
     * @param bool $con_scadenza
     */
    public function setConScadenza($con_scadenza) {
        $this->con_scadenza = $con_scadenza;
    }

    /**
     * @return int
     */
    public function getDurataValidita() {
        return $this->durata_validita;
    }

    /**
     * @param int $durata_validita
     */
    public function setDurataValidita($durata_validita) {
        $this->durata_validita = $durata_validita;
    }

    /**
     * @return bool
     */
    public function isFirmaDigitale() {
        return $this->firma_digitale;
    }

    /**
     * @param bool $firma_digitale
     */
    public function setFirmaDigitale($firma_digitale) {
        $this->firma_digitale = $firma_digitale;
    }

    /**
     * @return int
     */
    public function getDimensioneMassima() {
        return $this->dimensione_massima;
    }

    /**
     * @param int $dimensione_massima
     */
    public function setDimensioneMassima($dimensione_massima) {
        $this->dimensione_massima = $dimensione_massima;
    }

    /**
     * @return bool
     */
    public function isDropzone(): bool
    {
        return $this->dropzone;
    }

    /**
     * @param bool $dropzone
     */
    public function setDropzone(bool $dropzone): void
    {
        $this->dropzone = $dropzone;
    }

    /**
     * @return string
     */
    public function getMimeAmmessi() {
        return $this->mime_ammessi;
    }

    /**
     * @param string $mime_ammessi
     */
    public function setMimeAmmessi($mime_ammessi) {
        $this->mime_ammessi = $mime_ammessi;
    }

    /**
     * Get firmaDigitale
     *
     * @return bool
     */
    public function getFirmaDigitale() {
        return $this->firma_digitale;
    }

    /**
     * Get autocertificazione
     *
     * @return bool
     */
    public function getAutocertificazione() {
        return $this->autocertificazione;
    }

    /**
     * Get conScadenza
     *
     * @return bool
     */
    public function getConScadenza() {
        return $this->con_scadenza;
    }

    /**
     * Set obbligatorio
     *
     * @param bool $obbligatorio
     *
     * @return TipologiaDocumento
     */
    public function setObbligatorio($obbligatorio) {
        $this->obbligatorio = $obbligatorio;

        return $this;
    }

    /**
     * Get obbligatorio
     *
     * @return bool
     */
    public function getObbligatorio() {
        return $this->obbligatorio;
    }

    /**
     * Set procedura
     *
     * @param \SfingeBundle\Entity\Procedura $procedura
     *
     * @return TipologiaDocumento
     */
    public function setProcedura(\SfingeBundle\Entity\Procedura $procedura = null) {
        $this->procedura = $procedura;

        return $this;
    }

    /**
     * Get procedura
     *
     * @return \SfingeBundle\Entity\Procedura
     */
    public function getProcedura() {
        return $this->procedura;
    }

    /**
     * Set abilitaDuplicati
     *
     * @param bool $abilitaDuplicati
     *
     * @return TipologiaDocumento
     */
    public function setAbilitaDuplicati($abilitaDuplicati) {
        $this->abilita_duplicati = $abilitaDuplicati;

        return $this;
    }

    /**
     * Get abilitaDuplicati
     *
     * @return bool
     */
    public function getAbilitaDuplicati() {
        return $this->abilita_duplicati;
    }

    /**
     * Set tipologia
     *
     * @param string $tipologia
     *
     * @return TipologiaDocumento
     */
    public function setTipologia($tipologia) {
        $this->tipologia = $tipologia;

        return $this;
    }

    /**
     * Get tipologia
     *
     * @return string
     */
    public function getTipologia() {
        return $this->tipologia;
    }

    /**
     * Set prefix
     *
     * @param string $prefix
     *
     * @return TipologiaDocumento
     */
    public function setPrefix($prefix) {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Get prefix
     *
     * @return string
     */
    public function getPrefix() {
        return $this->prefix;
    }

    public function isFatturaElettronica(): bool {
        return \in_array($this->codice, [
            'INTEGRAZIONE_PAGAMENTO_FATTURA_ELETTRONICA',
            'RISPOSTA_RICHIESTA_CHIARIMENTI_FATTURA_ELETTRONICA',
        ]);
    }
}
