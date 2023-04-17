<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\Common\Collections\Collection;
use AttuazioneControlloBundle\Entity\DocumentoContratto;
use AttuazioneControlloBundle\Entity\Pagamento;

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Repository\ContrattoRepository")
 * @ORM\Table(name="contratti")
 * @Assert\Callback(callback="validateImportazione",groups={"sanita"})
 */
class Contratto extends EntityLoggabileCancellabile {

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\TipologiaSpesa")
     * @ORM\JoinColumn(name="tipologia_spesa_id", nullable=false)
     */
    protected $tipologiaSpesa;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\DocumentoContratto", mappedBy="contratto", cascade={"persist"})
     * @var DocumentoContratto[]|Collection
     */
    protected $documentiContratto;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\GiustificativoPagamento", mappedBy="contratto", cascade={"persist"})
     * @var GiustificativoPagamento[]|Collection
     */
    protected $giustificativiPagamento;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", inversedBy="contratti")
     * @ORM\JoinColumn(name="pagamento_id", nullable=false)
     */
    protected $pagamento;

    /**
     * @ORM\Column(name="data_inizio", type="date", nullable=true)
     */
    protected $dataInizio;

    /**
     * @ORM\Column(name="data_contratto", type="date", nullable=true)
     */
    protected $dataContratto;

    /**
     * @ORM\Column(name="descrizione", type="text", nullable=false)
     */
    protected $descrizione;

    /**
     * @ORM\Column(name="fornitore", type="string", nullable=false)
     */
    protected $fornitore;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\TipologiaFornitore")
     * @ORM\JoinColumn(name="tipologia_fornitore_id", nullable=true)
     */
    protected $tipologiaFornitore;

    /**
     * @ORM\Column(name="numero", type="string", nullable=false)
     */
    protected $numero;

    /**
     * @ORM\Column(name="titolo_brevetto", type="string", nullable=true)
     */
    protected $titolo_brevetto;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $importo_contratto_complessivo;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $importo_contratto_complessivo_ivato;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $alta_tecnologia;

    /**
     * @ORM\Column(type="string", nullable=true, length=255)
     */
    protected $referente;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $attivita;

    /**
     * @ORM\Column(type="string", nullable=true, length=255)
     */
    protected $numero_domanda_brevetto;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $data_domanda_brevetto;

    /**
     * @ORM\Column(type="string", nullable=true, length=25)
     */
    protected $stato_brevetto;

    /**
     * @ORM\Column(type="string", nullable=true, length=25)
     */
    protected $ambito_brevetto;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Proponente")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $proponente;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $gestione_ipr_brevetto;

    // IL SEGUENTE CAMPO VIENE UTILIZZATO NEL BANDO 773 SE:
    // - il beneficiario è ammesso allo scorrimento SAL
    // - la data di inizio è tra 01/05/2016 e 01/01/2017

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $importo_eleggibilita;

    // IL SEGUENTE CAMPO VIENE UTILIZZATO NEL BANDO 773 IN ISTRUTTORIA DEL PRECEDENTE

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $importo_eleggibilita_istruttoria;

    /**
     * @ORM\ManyToOne(targetEntity="Contratto")
     * @ORM\JoinColumn(name="contratto_clonato_id", referencedColumnName="id", nullable=true)
     */
    protected $contratto_clonato;

    /**
     * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento" , cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     * @var IstruttoriaOggettoPagamento|null
     */
    protected $istruttoria_oggetto_pagamento;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\TipologiaStazioneAppaltante")
     * @ORM\JoinColumn(name="tipologia_stazione_id", nullable=true)
     */
    protected $tipologia_stazione_appaltante;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $altro_stazione_appaltante;

    /**
     * @ORM\Column(name="beneficiario", type="string", nullable=false)
     */
    protected $beneficiario;

    /**
     * @ORM\Column(name="piattaforma_committenza", type="string", nullable=false)
     */
    protected $piattaforma_committenza;

    /**
     * @ORM\Column(name="provvedimento_avvio_procedimento", type="string", nullable=true)
     */
    protected $provvedimento_avvio_procedimento;

    /**
     * @ORM\Column(name="num_atto_aggiudicazione", type="string", nullable=true)
     */
    protected $num_atto_aggiudicazione;

    /**
     * @ORM\Column(name="tipologia_atto_aggiudicazione", type="string", nullable=true)
     */
    protected $tipologia_atto_aggiudicazione;

    /**
     * @ORM\Column(name="data_atto_aggiudicazione", type="date", nullable=true)
     */
    protected $data_atto_aggiudicazione;

    public function __construct() {
        $this->giustificativiPagamento = new ArrayCollection();
        $this->documentiContratto = new ArrayCollection();
    }

    function getId() {
        return $this->id;
    }

    function getTipologiaSpesa() {
        return $this->tipologiaSpesa;
    }

    function getDocumentiContratto() {
        return $this->documentiContratto;
    }

    /**
     * @return GiustificativoPagamento[]|Collection
     */
    function getGiustificativiPagamento() {
        return $this->giustificativiPagamento;
    }

    /**
     * @return Pagamento
     */
    function getPagamento() {
        return $this->pagamento;
    }

    function getDataInizio() {
        return $this->dataInizio;
    }

    function getDescrizione() {
        return $this->descrizione;
    }

    function getFornitore() {
        return $this->fornitore;
    }

    function getNumero() {
        return $this->numero;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setTipologiaSpesa($tipologiaSpesa) {
        $this->tipologiaSpesa = $tipologiaSpesa;
    }

    function setDocumentiContratto($documentiContratto) {
        $this->documentiContratto = $documentiContratto;
    }

    function setGiustificativiPagamento($giustificativiPagamento) {
        $this->giustificativiPagamento = $giustificativiPagamento;
    }

    function setPagamento($pagamento) {
        $this->pagamento = $pagamento;
    }

    function setDataInizio($dataInizio) {
        $this->dataInizio = $dataInizio;
    }

    function setDescrizione($descrizione) {
        $this->descrizione = $descrizione;
    }

    function setFornitore($fornitore) {
        $this->fornitore = $fornitore;
    }

    function setNumero($numero) {
        $this->numero = $numero;
    }

    function addGiustificativoPagamento(GiustificativoPagamento $giustificativoPagamento) {
        $giustificativoPagamento->setContratto($this);
        $this->giustificativiPagamento[] = $giustificativoPagamento;
    }

    function addDocumentoContratto(DocumentoContratto $documentoContratto) {
        $documentoContratto->setContratto($this);
        $this->documentiContratto[] = $documentoContratto;
    }

    function getTipologiaFornitore() {
        return $this->tipologiaFornitore;
    }

    function setTipologiaFornitore($tipologiaFornitore) {
        $this->tipologiaFornitore = $tipologiaFornitore;
    }

    function getSoggetto() {
        return $this->pagamento->getSoggetto();
    }

    public function __toString() {
        return $this->numero . ' - ' . $this->descrizione;
    }

    public function getImportoContrattoComplessivo() {
        return $this->importo_contratto_complessivo;
    }

    public function setImportoContrattoComplessivo($importo_contratto_complessivo) {
        $this->importo_contratto_complessivo = $importo_contratto_complessivo;
    }

    public function getAltaTecnologia() {
        return $this->alta_tecnologia;
    }

    public function setAltaTecnologia($alta_tecnologia) {
        $this->alta_tecnologia = $alta_tecnologia;
    }

    public function getReferente() {
        return $this->referente;
    }

    public function getAttivita() {
        return $this->attivita;
    }

    public function setReferente($referente) {
        $this->referente = $referente;
    }

    public function setAttivita($attivita) {
        $this->attivita = $attivita;
    }

    public function getTitoloBrevetto() {
        return $this->titolo_brevetto;
    }

    public function getNumeroDomandaBrevetto() {
        return $this->numero_domanda_brevetto;
    }

    public function getDataDomandaBrevetto() {
        return $this->data_domanda_brevetto;
    }

    public function getStatoBrevetto() {
        return $this->stato_brevetto;
    }

    public function getAmbitoBrevetto() {
        return $this->ambito_brevetto;
    }

    public function setTitoloBrevetto($titolo_brevetto) {
        $this->titolo_brevetto = $titolo_brevetto;
    }

    public function setNumeroDomandaBrevetto($numero_domanda_brevetto) {
        $this->numero_domanda_brevetto = $numero_domanda_brevetto;
    }

    public function setDataDomandaBrevetto($data_domanda_brevetto) {
        $this->data_domanda_brevetto = $data_domanda_brevetto;
    }

    public function setStatoBrevetto($stato_brevetto) {
        $this->stato_brevetto = $stato_brevetto;
    }

    public function setAmbitoBrevetto($ambito_brevetto) {
        $this->ambito_brevetto = $ambito_brevetto;
    }

    public function getProponente() {
        return $this->proponente;
    }

    public function setProponente($proponente) {
        $this->proponente = $proponente;
    }

    public function getGestioneIprBrevetto() {
        return $this->gestione_ipr_brevetto;
    }

    public function setGestioneIprBrevetto($gestione_ipr_brevetto) {
        $this->gestione_ipr_brevetto = $gestione_ipr_brevetto;
    }

    public function getSommaImportoImputazioneRISP() {

        $somma = 0.00;
        foreach ($this->giustificativiPagamento as $giustificativo) {
            $sp = $giustificativo->getEstensione()->getImportoImputazioneSP();
            $ri = $giustificativo->getEstensione()->getImportoImputazioneRI();
            $risp = $ri + $sp;
            $somma += $risp;
        }
        return $somma;
    }

    public function getFatture() {

        $fatture = array();
        foreach ($this->giustificativiPagamento as $giustificativo) {
            $data = $giustificativo->getDataGiustificativo();
            $num = $giustificativo->getNumeroGiustificativo();
            if (!is_null($data)) {
                $dataFormat = date_format($data, "d/m/Y");
            } else {
                $dataFormat = 'ND';
            }
            if (is_null($num)) {
                $num = 'ND';
            }

            $fatture[] = $dataFormat . ' - ' . $num;
        }

        return $fatture;
    }

    public function isReteAltaTecnologia() {
        return $this->tipologiaFornitore && $this->tipologiaFornitore->getCodice() == 'RI';
    }

    public function getImportoEleggibilita() {
        return $this->importo_eleggibilita;
    }

    public function setImportoEleggibilita($importo_eleggibilita) {
        $this->importo_eleggibilita = $importo_eleggibilita;
    }

    public function getImportoEleggibilitaIstruttoria() {
        return $this->importo_eleggibilita_istruttoria;
    }

    public function setImportoEleggibilitaIstruttoria($importo_eleggibilita_istruttoria) {
        $this->importo_eleggibilita_istruttoria = $importo_eleggibilita_istruttoria;
    }

    public function getRichiesta() {
        return $this->getPagamento()->getRichiesta();
    }

    public function getContratto() {
        return $this;
    }

    public function __clone() {
        if ($this->id) {
            $this->id = NULL;

            $documentiClonati = new ArrayCollection();
            foreach ($this->documentiContratto as $documentoContratto) {
                $cloneDocumento = clone $documentoContratto;
                $cloneDocumento->setContratto($this);
                $documentiClonati->add($cloneDocumento);
            }
            $this->documentiContratto = $documentiClonati;

            $giustificativiClonati = new ArrayCollection();
            foreach ($this->giustificativiPagamento as $giustificativo) {
                /** @var GiustificativoPagamento $giustificativoClonato */
                $giustificativoClonato = clone $giustificativo;
                $giustificativoClonato->setContratto($this);
                $giustificativiClonati->add($giustificativoClonato);
            }
            $this->giustificativiPagamento = $giustificativiClonati;
        }
    }

    /**
     * @param Pagamento $pagamento
     * @return self
     */
    public function clonaContrattoPerNuovoPagamento(Pagamento $pagamento) {
        /** @var self $clone */
        $clone = clone $this;
        $clone->setPagamento($pagamento);
        $clone->setGiustificativiPagamento(new ArrayCollection());
        $clone->setContrattoClonato($this);

        return $clone;
    }

    /**
     * @param DocumentoContratto $documentiContratto
     * @return self
     */
    public function addDocumentiContratto(DocumentoContratto $documentiContratto) {
        $this->documentiContratto[] = $documentiContratto;

        return $this;
    }

    /**
     * @param DocumentoContratto $documentiContratto
     */
    public function removeDocumentiContratto(DocumentoContratto $documentiContratto) {
        $this->documentiContratto->removeElement($documentiContratto);
    }

    /**
     * @param GiustificativoPagamento $giustificativiPagamento
     * @return self
     */
    public function addGiustificativiPagamento(\AttuazioneControlloBundle\Entity\GiustificativoPagamento $giustificativiPagamento) {
        $this->giustificativiPagamento[] = $giustificativiPagamento;

        return $this;
    }

    /**
     * @param GiustificativoPagamento $giustificativiPagamento
     */
    public function removeGiustificativiPagamento(\AttuazioneControlloBundle\Entity\GiustificativoPagamento $giustificativiPagamento) {
        $this->giustificativiPagamento->removeElement($giustificativiPagamento);
    }

    /**
     * @param self $contrattoClonato
     * @return self
     */
    public function setContrattoClonato(\AttuazioneControlloBundle\Entity\Contratto $contrattoClonato = null) {
        $this->contratto_clonato = $contrattoClonato;

        return $this;
    }

    /**
     * @return self 
     */
    public function getContrattoClonato() {
        return $this->contratto_clonato;
    }

    function getDataContratto() {
        return $this->dataContratto;
    }

    function getImportoContrattoComplessivoIvato() {
        return $this->importo_contratto_complessivo_ivato;
    }

    function setDataContratto($dataContratto): void {
        $this->dataContratto = $dataContratto;
    }

    function setImportoContrattoComplessivoIvato($importo_contratto_complessivo_ivato): void {
        $this->importo_contratto_complessivo_ivato = $importo_contratto_complessivo_ivato;
    }

    public function validateImportazione(\Symfony\Component\Validator\Context\ExecutionContextInterface $context) {
        $provvedimenti = array("Bando", "Decreto", "Determina", "Avviso", "Altro", "BANDO", "DECRETO", "DETERMINA", "AVVISO", "ALTRO");
        $tipologieAtto = array("Decreto", "Determina", "Ordine", "Altro", "DECRETO", "DETERMINA", "ORDINE", "ALTRO");
        $committenza = array("SI", "NO");

        if (\is_null($this->getTipologiaSpesa())) {
            $context->buildViolation('tipologia_spesa non valorizzato')
                    ->atPath('contratto')
                    ->addViolation();
        }

        if (\is_null($this->getTipologiaFornitore())) {
            $context->buildViolation('tipologia_fornitore non valorizzato')
                    ->atPath('contratto')
                    ->addViolation();
        }

        if (\is_null($this->getDataInizio())) {
            $context->buildViolation('data_inizio non valorizzato')
                    ->atPath('contratto')
                    ->addViolation();
        }

        if (\is_null($this->getDataContratto())) {
            $context->buildViolation('data_contratto non valorizzato')
                    ->atPath('contratto')
                    ->addViolation();
        }

        if (\is_null($this->getDescrizione())) {
            $context->buildViolation('descrizione non valorizzato')
                    ->atPath('contratto')
                    ->addViolation();
        }

        if (\is_null($this->getFornitore())) {
            $context->buildViolation('fornitore non valorizzato')
                    ->atPath('contratto')
                    ->addViolation();
        }

        if (\is_null($this->getNumero())) {
            $context->buildViolation('numero non valorizzato')
                    ->atPath('contratto')
                    ->addViolation();
        }

        if (\is_null($this->getImportoContrattoComplessivo())) {
            $context->buildViolation('importo_contratto_complessivo non valorizzato')
                    ->atPath('contratto')
                    ->addViolation();
        }

        if (\is_null($this->getImportoContrattoComplessivoIvato())) {
            $context->buildViolation('importo_contratto_complessivo_ivato non valorizzato')
                    ->atPath('contratto')
                    ->addViolation();
        }

        if (\is_null($this->getBeneficiario())) {
            $context->buildViolation('beneficiario_contratto non valorizzato')
                    ->atPath('contratto')
                    ->addViolation();
        }

        if (\is_null($this->getTipologiaStazioneAppaltante())) {
            $context->buildViolation('stazione_appaltante non valorizzato')
                    ->atPath('contratto')
                    ->addViolation();
        }

        if (!\is_null($this->getTipologiaStazioneAppaltante()) && $this->getTipologiaStazioneAppaltante()->getCodice() == 'ST15') {
            if (\is_null($this->getAltroStazioneAppaltante())) {
                $context->buildViolation('altro_stazione_appaltante non valorizzato ma indicata tipologia altro(ST15)')
                        ->atPath('contratto')
                        ->addViolation();
            }
        }

        if (\is_null($this->getPiattaformaCommittenza())) {
            $context->buildViolation('piattaforma_committenza non valorizzato')
                    ->atPath('contratto')
                    ->addViolation();
        }
        if (!\is_null($this->getPiattaformaCommittenza())) {
            if (!in_array($this->getPiattaformaCommittenza(), $committenza)) {
                $context->buildViolation('piattaforma_committenza non presente tra quelli ammissibili')
                        ->atPath('contratto')
                        ->addViolation();
            }
        }

        if (\is_null($this->getProvvedimentoAvvioProcedimento())) {
            $context->buildViolation('provvedimento_avvio_procedimento non valorizzato')
                    ->atPath('contratto')
                    ->addViolation();
        }
        if (!\is_null($this->getProvvedimentoAvvioProcedimento())) {
            if (!in_array($this->getProvvedimentoAvvioProcedimento(), $provvedimenti)) {
                $context->buildViolation('provvedimento_avvio_procedimento non presente tra quelli ammissibili')
                        ->atPath('contratto')
                        ->addViolation();
            }
        }

        if (\is_null($this->getNumAttoAggiudicazione())) {
            $context->buildViolation('num_atto_aggiudicazione non valorizzato')
                    ->atPath('contratto')
                    ->addViolation();
        }

        if (\is_null($this->getTipologiaAttoAggiudicazione())) {
            $context->buildViolation('tipologia_atto_aggiudicazione non valorizzato')
                    ->atPath('contratto')
                    ->addViolation();
        }

        if (!\is_null($this->getTipologiaAttoAggiudicazione())) {
            if (!in_array($this->getTipologiaAttoAggiudicazione(), $tipologieAtto)) {
                $context->buildViolation('tipologia_atto_aggiudicazione non presente tra quelli ammissibili')
                        ->atPath('contratto')
                        ->addViolation();
            }
        }

        if (\is_null($this->getDataAttoAggiudicazione())) {
            $context->buildViolation('data_atto_aggiudicazione non valorizzato')
                    ->atPath('contratto')
                    ->addViolation();
        }
    }

    public function getTotaleGiustificativi(): float {
        $importo = 0.00;
        $optimization_strategy = $this->getGiustificativiPagamento();
        foreach ($optimization_strategy as $g) {
            $importo += $g->getImportoRichiesto();
        }
        return $importo;
    }

    public function isComplessivoEqualGiustificativi() {
        $a = $this->getTotaleGiustificativi();
        return $this->importo_contratto_complessivo >= $this->getTotaleGiustificativi();
    }

    function getIstruttoriaOggettoPagamento(): ?IstruttoriaOggettoPagamento {
        return $this->istruttoria_oggetto_pagamento;
    }

    function setIstruttoriaOggettoPagamento(?IstruttoriaOggettoPagamento $istruttoria_oggetto_pagamento): void {
        $this->istruttoria_oggetto_pagamento = $istruttoria_oggetto_pagamento;
    }

    function getTipologiaStazioneAppaltante() {
        return $this->tipologia_stazione_appaltante;
    }

    function getAltroStazioneAppaltante() {
        return $this->altro_stazione_appaltante;
    }

    function getBeneficiario() {
        return $this->beneficiario;
    }

    function getPiattaformaCommittenza() {
        return $this->piattaforma_committenza;
    }

    function setTipologiaStazioneAppaltante($tipologia_stazione_appaltante): void {
        $this->tipologia_stazione_appaltante = $tipologia_stazione_appaltante;
    }

    function setAltroStazioneAppaltante($altro_stazione_appaltante): void {
        $this->altro_stazione_appaltante = $altro_stazione_appaltante;
    }

    function setBeneficiario($beneficiario): void {
        $this->beneficiario = $beneficiario;
    }

    function setPiattaformaCommittenza($piattaforma_committenza): void {
        $this->piattaforma_committenza = $piattaforma_committenza;
    }

    function getProvvedimentoAvvioProcedimento() {
        return $this->provvedimento_avvio_procedimento;
    }

    function getNumAttoAggiudicazione() {
        return $this->num_atto_aggiudicazione;
    }

    function getTipologiaAttoAggiudicazione() {
        return $this->tipologia_atto_aggiudicazione;
    }

    function getDataAttoAggiudicazione() {
        return $this->data_atto_aggiudicazione;
    }

    function setProvvedimentoAvvioProcedimento($provvedimento_avvio_procedimento): void {
        $this->provvedimento_avvio_procedimento = $provvedimento_avvio_procedimento;
    }

    function setNumAttoAggiudicazione($num_atto_aggiudicazione): void {
        $this->num_atto_aggiudicazione = $num_atto_aggiudicazione;
    }

    function setTipologiaAttoAggiudicazione($tipologia_atto_aggiudicazione): void {
        $this->tipologia_atto_aggiudicazione = $tipologia_atto_aggiudicazione;
    }

    function setDataAttoAggiudicazione($data_atto_aggiudicazione): void {
        $this->data_atto_aggiudicazione = $data_atto_aggiudicazione;
    }

}
