<?php

namespace AttuazioneControlloBundle\Entity;

use function array_reduce;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use function implode;
use function is_null;
use function substr;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\Common\Collections\Collection;
use RichiesteBundle\Entity\Proponente;

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\GiustificativoPagamentoRepository")
 * @ORM\Table(name="giustificativi_pagamenti")
 * @Assert\Callback(callback="validateImportazione",groups={"sanita"})
 */
class GiustificativoPagamento extends EntityLoggabileCancellabile {

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", inversedBy="giustificativi")
     * @ORM\JoinColumn(nullable=false)
     * @var Pagamento|null
     */
    protected $pagamento;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo", mappedBy="giustificativo_pagamento", orphanRemoval=true, cascade={"persist", "remove"})
     * @var Collection|VocePianoCostoGiustificativo[]
     */
    protected $voci_piano_costo;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\QuietanzaGiustificativo", mappedBy="giustificativo_pagamento", cascade={"persist", "remove"})
     * @var Collection|QuietanzaGiustificativo[]
     */
    protected $quietanze;

    /**
     * @ORM\Column(type="string", nullable=true, length=255)
     * @var string|null
     */
    protected $denominazione_fornitore;

    /**
     * @ORM\Column(type="string", nullable=true, length=16)
     * @var string|null
     */
    protected $codice_fiscale_fornitore;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    protected $descrizione_giustificativo;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    protected $numero_giustificativo;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @var \DateTime|null
     */
    protected $data_giustificativo;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @var \DateTime|null
     */
    protected $data_consegna;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    protected $luogo_consegna;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $importo_imponibile_giustificativo;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $importo_iva_giustificativo;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     * @Assert\Range(min=100, minMessage="Importo minimo 100€", groups={"giustificativi_68"})
     * @Assert\GreaterThan(0)
     */
    protected $importo_giustificativo;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     * Assert\Expression(
     *     "this.getImportoRichiesto() <= this.getImportoGiustificativo()",
     *     message="L'importo richiesto non può essere superiore all'importo del giustificativo"
     * )
     */
    protected $importo_richiesto;

    /**
     * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid
     */
    protected $documento_giustificativo;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $importo_approvato;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $nota_beneficiario;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $integrazione;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $nota_integrazione;

    /**
     * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\GiustificativoPagamento", inversedBy="integrato_da")
     * @ORM\JoinColumn(nullable=true)
     * @var GiustificativoPagamento|null
     */
    protected $integrazione_di;

    /**
     * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\GiustificativoPagamento", mappedBy="integrazione_di")
     * @var GiustificativoPagamento|null
     */
    protected $integrato_da;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\TipologiaGiustificativo")
     * @ORM\JoinColumn(nullable=true)
     * @var TipologiaGiustificativo|null
     */
    protected $tipologia_giustificativo;

    /**
     * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\EstensioneGiustificativo", cascade={"persist"}, inversedBy="giustificativo_pagamento")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $estensione;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\DocumentoGiustificativo", mappedBy="giustificativo_pagamento", cascade={"persist"})
     * @Assert\Valid
     * @var Collection|DocumentoGiustificativo[]
     */
    protected $documenti_giustificativo;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Contratto", inversedBy="giustificativiPagamento")
     * @ORM\JoinColumn(nullable=true)
     * @var Contratto|null
     */
    protected $contratto;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Proponente")
     * @ORM\JoinColumn(nullable=true)
     * @var Proponente|null
     */
    protected $proponente;

    /**
     * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento" , cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     * @var IstruttoriaOggettoPagamento|null
     */
    protected $istruttoria_oggetto_pagamento;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\DocumentoPrototipo", mappedBy="giustificativo_pagamento")
     * @var Collection|DocumentoPrototipo
     */
    protected $documenti_prototipo;

    /**
     * @ORM\OneToMany(targetEntity="PagamentiPercettoriGiustificativo", mappedBy="giustificativo_pagamento")
     * @var Collection|PagamentiPercettoriGiustificativo
     */
    protected $pagamenti_percettori;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\GiustificativoPagamento")
     * @ORM\JoinColumn(name="giustificativo_origine_id", referencedColumnName="id", nullable=true)	
     */
    protected $giustificativo_origine;

    public function __construct() {
        $this->voci_piano_costo = new ArrayCollection();
        $this->quietanze = new ArrayCollection();
        $this->documenti_giustificativo = new ArrayCollection();
        $this->documenti_prototipo = new ArrayCollection();
        $this->pagamenti_percettori = new ArrayCollection();
    }

    function getId() {
        return $this->id;
    }

    /**
     * @return Pagamento
     */
    function getPagamento(): ?Pagamento {
        return $this->pagamento;
    }

    function getVociPianoCosto(): Collection {
        // Ritorno solo le voci piano costo che hanno a null il 'creato da di cui', perchè in questo caso so che sono voci REALI e non generati da DI CUI
        return $this->voci_piano_costo->filter(function($voce_piano_costo) {
                    return is_null($voce_piano_costo->getCreatoDaDiCui());
                });
    }

    function getQuietanze(): Collection {
        return $this->quietanze;
    }

    function getDenominazioneFornitore() {
        return $this->denominazione_fornitore;
    }

    function getCodiceFiscaleFornitore() {
        return $this->codice_fiscale_fornitore;
    }

    function getDescrizioneGiustificativo() {
        return $this->descrizione_giustificativo;
    }

    function getNumeroGiustificativo() {
        return $this->numero_giustificativo;
    }

    function getDataGiustificativo() {
        return $this->data_giustificativo;
    }

    function getImportoGiustificativo() {
        return $this->importo_giustificativo;
    }

    function getImportoRichiesto() {
        return $this->importo_richiesto;
    }

    function getDocumentoGiustificativo() {
        return $this->documento_giustificativo;
    }

    function getImportoApprovato() {
        return $this->importo_approvato;
    }

    function getNotaBeneficiario() {
        return $this->nota_beneficiario;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setPagamento(?Pagamento $pagamento) {
        $this->pagamento = $pagamento;
    }

    function setVociPianoCosto(Collection $voci_piano_costo): self {
        $this->voci_piano_costo = $voci_piano_costo;

        return $this;
    }

    function setQuietanze(Collection $quietanze): self {
        $this->quietanze = $quietanze;

        return $this;
    }

    function setDenominazioneFornitore($denominazione_fornitore) {
        $this->denominazione_fornitore = $denominazione_fornitore;
    }

    function setCodiceFiscaleFornitore($codice_fiscale_fornitore) {
        $this->codice_fiscale_fornitore = $codice_fiscale_fornitore;
    }

    function setDescrizioneGiustificativo($descrizione_giustificativo) {
        $this->descrizione_giustificativo = $descrizione_giustificativo;
    }

    function setNumeroGiustificativo($numero_giustificativo) {
        $this->numero_giustificativo = $numero_giustificativo;
    }

    function setDataGiustificativo($data_giustificativo) {
        $this->data_giustificativo = $data_giustificativo;
    }

    function setImportoGiustificativo($importo_giustificativo) {
        $this->importo_giustificativo = $importo_giustificativo;
    }

    function setImportoRichiesto($importo_richiesto) {
        $this->importo_richiesto = $importo_richiesto;
    }

    function setDocumentoGiustificativo($documento_giustificativo) {
        $this->documento_giustificativo = $documento_giustificativo;
    }

    function setImportoApprovato($importo_approvato) {
        $this->importo_approvato = $importo_approvato;
    }

    function setNotaBeneficiario($nota_beneficiario) {
        $this->nota_beneficiario = $nota_beneficiario;
    }

    public function getDocumentiPrototipo() {
        return $this->documenti_prototipo;
    }

    public function setDocumentiPrototipo($documenti_prototipo) {
        $this->documenti_prototipo = $documenti_prototipo;
    }

    public function getSoggetto() {
        return $this->getPagamento()->getSoggetto();
    }

    /**
     * @param string $importoImponibileGiustificativo
     */
    public function setImportoImponibileGiustificativo($importoImponibileGiustificativo): self {
        $this->importo_imponibile_giustificativo = $importoImponibileGiustificativo;

        return $this;
    }

    /**
     * Get importo_imponibile_giustificativo
     *
     * @return string 
     */
    public function getImportoImponibileGiustificativo() {
        return $this->importo_imponibile_giustificativo;
    }

    /**
     * Set importo_iva_giustificativo
     *
     * @param string $importoIvaGiustificativo
     * @return GiustificativoPagamento
     */
    public function setImportoIvaGiustificativo($importoIvaGiustificativo): self {
        $this->importo_iva_giustificativo = $importoIvaGiustificativo;

        return $this;
    }

    /**
     * @return string 
     */
    public function getImportoIvaGiustificativo() {
        return $this->importo_iva_giustificativo;
    }

    public function addVocePianoCosto(VocePianoCostoGiustificativo $voce): self {
        $this->voci_piano_costo->add($voce);
        $voce->setGiustificativoPagamento($this);

        return $this;
    }

    public function removeVocePianoCosto(VocePianoCostoGiustificativo $voce): void {
        $this->voci_piano_costo->removeElement($voce);
        $voce->setGiustificativoPagamento(null);
    }

    public function addQuietanze(QuietanzaGiustificativo $quietanze): self {
        $this->quietanze[] = $quietanze;

        return $this;
    }

    public function removeQuietanze(QuietanzaGiustificativo $quietanze) {
        $this->quietanze->removeElement($quietanze);
    }

    function getIntegrazione() {
        return $this->integrazione;
    }

    function getNotaIntegrazione() {
        return $this->nota_integrazione;
    }

    function setIntegrazione($integrazione) {
        $this->integrazione = $integrazione;
        return $this;
    }

    function setNotaIntegrazione($nota_integrazione) {
        $this->nota_integrazione = $nota_integrazione;
        return $this;
    }

    public function getProcedura() {
        return $this->getPagamento()->getProcedura();
    }

    // calcola il totale delle imputazione del giustificativo sulle voci spesa
    public function calcolaImportoRichiesto() {

        $importo = 0;
        foreach ($this->getVociPianoCosto() as $voce) {
            $importo += $voce->getImporto();
        }

        $this->setImportoRichiesto($importo);
        $this->pagamento->calcolaImportoRichiesto();
    }

    public function calcolaImportoAmmesso() {
        $this->setImportoApprovato($this->getImportoAmmesso());
        return $this->getImportoApprovato();
    }

    public function getImportoAmmesso(): float {
        return array_reduce(
                $this->getVociPianoCosto()->toArray(),
                function(float $carry, VocePianoCostoGiustificativo $voce): float {
            return $carry + $voce->getImportoApprovato();
        },
                0.0
        );
    }

    public function getImportoAmmesso773($tipo) {
        $totale = 0.0;
        foreach ($this->voci_piano_costo as $voce) {
            if ($voce->getVocePianoCosto()->getPianoCosto()->getSezionePianoCosto()->getCodice() == $tipo) {
                $totale += ($voce->getImportoApprovato() - $voce->getImportoNonAmmessoPerSuperamentoMassimali());
            }
        }
        return $totale;
    }

    public function isModificabileIntegrazione(): bool {
        return is_null($this->getPagamento()->getIntegrazioneDi()) || !is_null($this->getIntegrazioneDi());
    }

    public function __clone() {
        if ($this->id) {
            if (!is_null($this->voci_piano_costo)) {
                $voci_piano_costo = new ArrayCollection();
                foreach ($this->voci_piano_costo as $voce_piano_costo) {
                    $voce_piano_costo_clonato = clone $voce_piano_costo;
                    $voce_piano_costo_clonato->setGiustificativoPagamento($this);
                    $voci_piano_costo[] = $voce_piano_costo_clonato;
                }
                $this->setVociPianoCosto($voci_piano_costo);
            }

            if (!is_null($this->quietanze)) {
                $quietanze = new ArrayCollection();
                foreach ($this->quietanze as $quietanza) {
                    $quietanza_clonata = clone $quietanza;
                    $quietanza_clonata->setGiustificativoPagamento($this);
                    $quietanze[] = $quietanza_clonata;
                }
                $this->setQuietanze($quietanze);
            }

            if (!is_null($this->documenti_giustificativo)) {
                $documenti_giustificativo = new ArrayCollection();
                foreach ($this->documenti_giustificativo as $documentiGiustificativo) {
                    $documentiGiustificativoClonato = clone $documentiGiustificativo;
                    $documentiGiustificativoClonato->setGiustificativoPagamento($this);
                    $documenti_giustificativo[] = $documentiGiustificativoClonato;
                }
                $this->setDocumentiGiustificativo($documenti_giustificativo);
            }

            $this->documento_giustificativo = $this->documento_giustificativo ? clone $this->documento_giustificativo : NULL;
        }
    }

    function getIntegrazioneDi() {
        return $this->integrazione_di;
    }

    function getIntegratoDa() {
        return $this->integrato_da;
    }

    function setIntegrazioneDi($integrazione_di) {
        $this->integrazione_di = $integrazione_di;
        return $this;
    }

    function setIntegratoDa($integrato_da) {
        $this->integrato_da = $integrato_da;
        return $this;
    }

    public function getTipologiaGiustificativo() {
        return $this->tipologia_giustificativo;
    }

    public function setTipologiaGiustificativo($tipologia_giustificativo) {
        $this->tipologia_giustificativo = $tipologia_giustificativo;
    }

    public function getEstensione() {
        return $this->estensione;
    }

    public function setEstensione($estensione) {
        $this->estensione = $estensione;
    }

    /**
     * @return VocePianoCostoGiustificativo|null
     */
    public function getVocePianoCosto($codice_piano_costo) {
        foreach ($this->getVociPianoCosto() as $voce_piano_costo) {
            if ($voce_piano_costo->getVocePianoCosto()->getPianoCosto()->getCodice() == $codice_piano_costo) {
                return $voce_piano_costo;
            }
        }
        return null;
    }

    public function getVocePianoCostoProponente($codice_piano_costo, $proponente = null) {
        $optimization_strategy = $this->getVociPianoCosto();
        if (!is_null($optimization_strategy) && !is_null($proponente)) {
            foreach ($optimization_strategy as $voce_piano_costo) {
                if (is_null($voce_piano_costo->getVocePianoCosto())) {
                    continue;
                }
                if ($voce_piano_costo->getVocePianoCosto()->getPianoCosto()->getCodice() == $codice_piano_costo && $proponente->getId() == $voce_piano_costo->getVocePianoCosto()->getProponente()->getId()) {
                    return $voce_piano_costo;
                }
            }
        }
        return null;
    }

    public function getVocePianoCostoByStart($codice_piano_costo) {
        $optimization_strategy = $this->getVociPianoCosto();
        if (!is_null($optimization_strategy)) {
            foreach ($optimization_strategy as $voce_piano_costo) {
                $start_codice = substr($voce_piano_costo->getVocePianoCosto()->getPianoCosto()->getCodice(), 0, strlen($codice_piano_costo));
                if ($start_codice == $codice_piano_costo) {
                    return $voce_piano_costo;
                }
            }
        }
        return null;
    }

    public function getDocumentiGiustificativo() {
        return $this->documenti_giustificativo;
    }

    public function setDocumentiGiustificativo($documenti_giustificativo) {
        $this->documenti_giustificativo = $documenti_giustificativo;
    }

    function getContratto() {
        return $this->contratto;
    }

    function setContratto($contratto) {
        $this->contratto = $contratto;
    }

    public function getProponente(): ?Proponente {
        return $this->proponente;
    }

    public function setProponente($proponente) {
        $this->proponente = $proponente;
    }

    public function getIstruttoriaOggettoPagamento(): ?IstruttoriaOggettoPagamento {
        return $this->istruttoria_oggetto_pagamento;
    }

    public function setIstruttoriaOggettoPagamento($istruttoria_oggetto_pagamento) {
        $this->istruttoria_oggetto_pagamento = $istruttoria_oggetto_pagamento;
    }

    public function addVociPianoCosto(VocePianoCostoGiustificativo $vociPianoCosto): self {
        $this->voci_piano_costo[] = $vociPianoCosto;

        return $this;
    }

    public function removeVociPianoCosto(VocePianoCostoGiustificativo $vociPianoCosto): void {
        $this->voci_piano_costo->removeElement($vociPianoCosto);
    }

    public function addDocumentiGiustificativo(DocumentoGiustificativo $documentiGiustificativo): self {
        $this->documenti_giustificativo[] = $documentiGiustificativo;

        return $this;
    }

    public function removeDocumentiGiustificativo(DocumentoGiustificativo $documentiGiustificativo): void {
        $this->documenti_giustificativo->removeElement($documentiGiustificativo);
    }

    public function addDocumentiPrototipo(DocumentoPrototipo $documentiPrototipo): self {
        $this->documenti_prototipo[] = $documentiPrototipo;

        return $this;
    }

    public function removeDocumentiPrototipo(DocumentoPrototipo $documentiPrototipo): void {
        $this->documenti_prototipo->removeElement($documentiPrototipo);
    }

    public function addPagamentiPercettori(PagamentiPercettoriGiustificativo $pagamentiPercettori): self {
        $this->pagamenti_percettori[] = $pagamentiPercettori;

        return $this;
    }

    public function removePagamentiPercettori(PagamentiPercettoriGiustificativo $pagamentiPercettori): void {
        $this->pagamenti_percettori->removeElement($pagamentiPercettori);
    }

    /**
     * @return Collection|PagamentiPercettoriGiustificativo[]
     */
    public function getPagamentiPercettori(): Collection {
        return $this->pagamenti_percettori;
    }

    // VARIABILE DI APPOGGIO non mappata sul db che mi permette di capire se questo giustificativo (tipicamente legato ad un pagamento di SAL)
    // o parte di esso, va ribaltato nel SALDO
    // IN PARTICOLARE se questo giustificativo è legato ad un pagamento SAL e in istruttoria ci sono stati tagli per il superamento dei massimali (vedi bando 773)
    // questi tagli vanno riproposti in fase di SALDO
    protected $isSalDaRibaltareInSaldo = false;

    public function isSalDaRibaltareInSaldo() {
        return $this->isSalDaRibaltareInSaldo;
    }

    public function setIsSalDaRibaltareInSaldo($value) {
        $this->isSalDaRibaltareInSaldo = $value;
    }

    // VARIABILE DI APPOGGIO non mappata sul db che mi permette di capire se questo giustificativo (tipicamente legato ad un pagamento di PRIMO SAL)
    // o parte di esso, va ribaltato nel SECONDO SAL
    // IN PARTICOLARE se questo giustificativo è legato ad un pagamento PRIMO SAL e in istruttoria ci sono stati tagli per il superamento dei massimali (vedi bando 774)
    // questi tagli vanno riproposti in fase di SECONDO SAL
    protected $isPrimoSalDaRibaltareInSecondoSal = false;

    public function isPrimoSalDaRibaltareInSecondoSal() {
        return $this->isPrimoSalDaRibaltareInSecondoSal;
    }

    public function setPrimoSalDaRibaltareInSecondoSal($value) {
        $this->isPrimoSalDaRibaltareInSecondoSal = $value;
    }

    // VARIABILE DI APPOGGIO non mappata sul db che mi permette di capire se questo giustificativo (ora anche legato ad un pagamento di SECONDO SAL)
    // o parte di esso, va ribaltato nel SECONDO SAL
    // IN PARTICOLARE se questo giustificativo è legato ad un pagamento SECONDO SAL e in istruttoria ci sono stati tagli per il superamento dei massimali (vedi bando 774)
    // questi tagli vanno riproposti in fase di TERZO SAL
    protected $isSecondoSalDaRibaltareInTerzoSal = false;

    public function isSecondoSalDaRibaltareInTerzoSal() {
        return $this->isSecondoSalDaRibaltareInTerzoSal;
    }

    public function setSecondoSalDaRibaltareInTerzoSal($value) {
        $this->isSecondoSalDaRibaltareInTerzoSal = $value;
    }

    // VARIABILE DI APPOGGIO non mappata sul db che mi permette di capire se questo giustificativo (tipicamente legato ad un pagamento di SAL)
    // o parte di esso, va ribaltato nel SALDO
    // IN PARTICOLARE se questo giustificativo è legato ad un pagamento SAL e in istruttoria ci sono stati tagli per il superamento dei massimali (vedi bando 773)
    // questi tagli vanno riproposti in fase di SALDO
    protected $isSalDaRibaltareInTerzoSal = false;

    public function isSalDaRibaltareInTerzoSal() {
        return $this->isSalDaRibaltareInTerzoSal;
    }

    public function setIsSalDaRibaltareInTerzoSal($value) {
        $this->isSalDaRibaltareInTerzoSal = $value;
    }

    public function getTotaleImputato(): float {
        $importo = 0.00;
        $optimization_strategy = $this->getVociPianoCosto();
        foreach ($optimization_strategy as $voce_piano_costo) {
            $importo += $voce_piano_costo->getImporto();
        }
        return $importo;
    }

    public function getTotaleImputatoApprovato() {
        $importo = 0.00;
        $optimization_strategy = $this->getVociPianoCosto();
        if (!is_null($optimization_strategy)) {
            foreach ($optimization_strategy as $voce_piano_costo) {
                $importo += $voce_piano_costo->getImportoApprovato();
            }
        }
        return $importo;
    }

    // calcola il totale non ammesso  sulle voci spesa per il giustificativo
    public function calcolaImportoNonAmmesso() {
        $importoNonAmmesso = 0;
        foreach ($this->getVociPianoCosto() as $voce) {
            $importoNonAmmesso += $voce->calcolaImportoNonAmmesso();
        }

        return $importoNonAmmesso;
    }

    public function getGiustificativo() {
        return $this;
    }

    function getGiustificativoOrigine() {
        return $this->giustificativo_origine;
    }

    function setGiustificativoOrigine($giustificativo_origine) {
        $this->giustificativo_origine = $giustificativo_origine;
    }

    /**
     * @return Collection|VocePianoCostoGiustificativo[]
     */
    public function getVociPianoCostoByCodiceSezione(string $codice): Collection {
        return $this->getVociPianoCosto()->filter(function(VocePianoCostoGiustificativo $voce) use($codice) {
                    return $voce->getVocePianoCosto()->getPianoCosto()->getSezionePianoCosto()->getCodice() == $codice;
                });
    }

    /**
     * @return Collection|VocePianoCostoGiustificativo[]
     */
    public function getPianoCosto(): array {
        $arrayPiani = array();
        foreach ($this->getVociPianoCosto() as $voce) {
            $arrayPiani[] = $voce->getVocePianoCosto()->getPianoCosto()->getTitolo();
        }
        return $arrayPiani;
    }

    public function getImportoSpesaNonAmmessaDaRibaltareASaldo(): float {
        return array_reduce($this->getVociPianoCosto()->toArray(),
                function(float $riporto, VocePianoCostoGiustificativo $voce) {
            return $riporto + $voce->getImportoNonAmmessoPerSuperamentoMassimali();
        }, 0.0);
    }

    public function getMotivazioniNonAmmissibilita(): string {
        return implode("\n",
                $this->getVociPianoCosto()->map(
                                function(VocePianoCostoGiustificativo $voce): string {
                            return implode(" - ", [
                                substr($voce->getVocePianoCosto()->getPianoCosto()->getCodice(), 0, 2),
                                $voce->getNota(),
                                $voce->getNotaSuperamentoMassimali()
                            ]);
                        })
                        ->toArray()
        );
    }

    /**
     * @return bool
     */
    public function isFatturaElettronica() {
        if (!is_null($this->getTipologiaGiustificativo())) {
            return $this->getTipologiaGiustificativo()->getCodice() == 'TIPOLOGIA_STANDARD_FATTURA_ELETTRONICA';
        }

        return false;
    }

    /**
     * @return float
     */
    public function getImportoPagamentoSuccessivo(): float {
        return \array_reduce($this->getVociPianoCosto()->toArray(),
                function(float $riporto, VocePianoCostoGiustificativo $voce) {
            return $riporto + $voce->getImportoPagamentoSuccessivo();
        }, 0.0);
    }

    public function validateImportazione(\Symfony\Component\Validator\Context\ExecutionContextInterface $context) {
        if (\is_null($this->getDocumentoGiustificativo())) {
            $context->buildViolation('documento_giustificativo non valorizzato')
                    ->atPath('giustificativo')
                    ->addViolation();
        }

        if (\is_null($this->getDenominazioneFornitore())) {
            $context->buildViolation('denominazione_fornitore non valorizzato')
                    ->atPath('giustificativo')
                    ->addViolation();
        }

        if (\is_null($this->getCodiceFiscaleFornitore())) {
            $context->buildViolation('codice_fiscale_fornitore non valorizzato')
                    ->atPath('giustificativo')
                    ->addViolation();
        }

        if (\is_null($this->getDescrizioneGiustificativo())) {
            $context->buildViolation('descrizione_giustificativo non valorizzato')
                    ->atPath('giustificativo')
                    ->addViolation();
        }

        if (\is_null($this->getNumeroGiustificativo())) {
            $context->buildViolation('numero_giustificativo non valorizzato')
                    ->atPath('giustificativo')
                    ->addViolation();
        }

        if (\is_null($this->getDataGiustificativo())) {
            $context->buildViolation('data_giustificativo non valorizzato')
                    ->atPath('giustificativo')
                    ->addViolation();
        }

        if (\is_null($this->getImportoGiustificativo())) {
            $context->buildViolation('importo_giustificativo non valorizzato')
                    ->atPath('giustificativo')
                    ->addViolation();
        }

        if (\is_null($this->getNotaBeneficiario())) {
            $context->buildViolation('nota_beneficiario non valorizzato')
                    ->atPath('giustificativo')
                    ->addViolation();
        }

        if (\is_null($this->getTipologiaGiustificativo())) {
            $context->buildViolation('tipologia_giustificativo non valorizzato')
                    ->atPath('giustificativo')
                    ->addViolation();
        }

        if (\is_null($this->getDataConsegna())) {
            $context->buildViolation('data_consegna_giustificativo non valorizzato')
                    ->atPath('giustificativo')
                    ->addViolation();
        }
    }

    public function isRichiestoEqualImputato() {
        return bccomp($this->importo_richiesto, $this->getTotaleImputato(), 2) == 0;
    }

    function getDataConsegna(): ?\DateTime {
        return $this->data_consegna;
    }

    function getLuogoConsegna(): ?string {
        return $this->luogo_consegna;
    }

    function setDataConsegna(?\DateTime $data_consegna): void {
        $this->data_consegna = $data_consegna;
    }

    function setLuogoConsegna(?string $luogo_consegna): void {
        $this->luogo_consegna = $luogo_consegna;
    }

}
