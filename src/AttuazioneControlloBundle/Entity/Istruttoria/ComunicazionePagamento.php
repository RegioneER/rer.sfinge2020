<?php

namespace AttuazioneControlloBundle\Entity\Istruttoria;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Annotation as Sfinge;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\Common\Collections\ArrayCollection;
use AttuazioneControlloBundle\Entity\Pagamento;
use Doctrine\Common\Collections\Collection;
use ProtocollazioneBundle\Entity\RichiestaProtocolloComunicazionePagamento;
use ProtocollazioneBundle\Entity\EmailProtocollo;
use DocumentoBundle\Entity\DocumentoFile;
use BaseBundle\Entity\StatoComunicazionePagamento;
use AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento;
use RichiesteBundle\Entity\Richiesta;
use SoggettoBundle\Entity\Soggetto;

/**
 *
 * @ORM\Entity()
 * @ORM\Table(name="comunicazioni_pagamenti")
 */
class ComunicazionePagamento extends EntityLoggabileCancellabile {

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", inversedBy="comunicazioni")
     * @ORM\JoinColumn(nullable=false)
     * @var Pagamento|null
     */
    protected $pagamento;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\TipologiaComunicazionePagamento")
     * @ORM\JoinColumn(nullable=true)
     * @var TipologiaComunicazionePagamento|null
     */
    protected $tipologia_comunicazione;

    /**
     * @ORM\OneToMany(targetEntity="ProtocollazioneBundle\Entity\RichiestaProtocolloComunicazionePagamento", mappedBy="comunicazione_pagamento", cascade={"persist"})
     * @ORM\OrderBy({"id" = "ASC"})
     *
     * @var Collection|\ProtocollazioneBundle\Entity\RichiestaProtocolloComunicazionePagamento[]
     */
    protected $richieste_protocollo;

    /**
     * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento" , cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     * @var IstruttoriaOggettoPagamento|null
     */
    protected $istruttoria_oggetto_pagamento;

    /**
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamentoDocumento", mappedBy="integrazione", cascade={"persist"})
     * @var Collection|IntegrazionePagamentoDocumento[]
     */
    protected $tipologie_documenti;

    /**
     * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\Istruttoria\RispostaComunicazionePagamento", mappedBy="comunicazione", cascade={"persist"})
     * @var RispostaComunicazionePagamento|null
     */
    protected $risposta;

    /**
     * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     * @var DocumentoFile|null
     */
    private $documento;

    /**
     * @ORM\ManyToOne(targetEntity="BaseBundle\Entity\StatoComunicazionePagamento")
     * @ORM\JoinColumn(nullable=true)
     * @Sfinge\CampoStato()
     * @var StatoComunicazionePagamento|null
     */
    private $stato;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @var \DateTime|null
     */
    protected $data;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    protected $testo;

    /**
     * @ORM\Column(type="text", nullable=false)
     * @var string|null
     */
    protected $testoEmail;

    /**
     * @var Collection|AllegatoComunicazionePagamento[]
     * @ORM\OneToMany(targetEntity="AllegatoComunicazionePagamento", mappedBy="comunicazione_pagamento", cascade={"persist"})
     */
    protected $allegati;


    public function __construct() {
        $this->richieste_protocollo = new ArrayCollection();
        $this->tipologie_documenti = new ArrayCollection();
        $this->allegati = new ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getPagamento(): ?Pagamento {
        return $this->pagamento;
    }

    public function getRichiesteProtocollo(): Collection {
        return $this->richieste_protocollo;
    }

    public function getData(): ?\DateTime {
        return $this->data;
    }

    public function getTesto(): ?string {
        return $this->testo;
    }

    public function getTipologieDocumenti(): Collection {
        return $this->tipologie_documenti;
    }

    public function getRisposta(): ?RispostaComunicazionePagamento {
        return $this->risposta;
    }

    public function getDocumento(): ?DocumentoFile {
        return $this->documento;
    }

    public function getStato(): ?StatoComunicazionePagamento {
        return $this->stato;
    }

    public function getTestoEmail(): ?string {
        return $this->testoEmail;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setPagamento(?Pagamento $pagamento): self {
        $this->pagamento = $pagamento;

        return $this;
    }

    public function setRichiesteProtocollo(Collection $richieste_protocollo): self {
        $this->richieste_protocollo = $richieste_protocollo;

        return $this;
    }

    public function setData(\DateTime $data): self {
        $this->data = $data;

        return $this;
    }

    public function setTesto(?string $testo): self {
        $this->testo = $testo;

        return $this;
    }

    public function setTipologieDocumenti(Collection $tipologie_documenti): self {
        $this->tipologie_documenti = $tipologie_documenti;

        return $this;
    }

    public function setRisposta(?RispostaComunicazionePagamento $risposta): self {
        $this->risposta = $risposta;

        return $this;
    }

    public function setDocumento(?DocumentoFile $documento): self {
        $this->documento = $documento;

        return $this;
    }

    public function setStato(?StatoComunicazionePagamento $stato): self {
        $this->stato = $stato;

        return $this;
    }

    public function setTestoEmail(?string $testoEmail): self {
        $this->testoEmail = $testoEmail;

        return $this;
    }

    function addTipologiaDocumento(IntegrazionePagamentoDocumento $tipologie_documenti): self {
        $this->tipologie_documenti->add($tipologie_documenti);
        $tipologie_documenti->setIntegrazione($this);

        return $this;
    }

    /*
     * Di solito l'integrazione dovrebbe avere una sola richiesta protocollo associata(al più una per tipo), ma può capitare che ne venga generata più di una
     * ..per cui va presa sempre la richiesta protocollo più recente
     * 
     * questo metodo deve fare riferimento solo alle RichiesteProtocolloIntegrazione
     * 
     * attenzione: il filtraggio per nomeClasse risulta necessario perchè essendoci eredità in cascata anche se la relazione richieste_protocollo punta ad un tipo specifico
     * può tornare anche istanze derivate dalla classe specificata
     */
    public function getProtocolloComunicazione() {

        $richiestaProtocollo = null;
        // in caso di più richieste protocollo mi prendo l'ultima(la più recente)
        foreach($this->richieste_protocollo as $r){
            if($r->getNomeClasse() == 'ProtocolloComunicazionePagamento'){
                $richiestaProtocollo = $r;
            }
        }

        $protocollo = '-';
        if (!is_null($richiestaProtocollo)) {
            $protocollo = $richiestaProtocollo->getProtocollo();
        }

        return $protocollo;
    }

    public function getDataProtocolloComunicazione(): ?\DateTime {

        $richiestaProtocollo = null;
        // in caso di più richieste protocollo mi prendo l'ultima(la più recente)
        foreach($this->richieste_protocollo as $r){
            if($r->getNomeClasse() == 'ProtocolloComunicazionePagamento'){
                $richiestaProtocollo = $r;
            }
        }

        $data = null;
        if (!is_null($richiestaProtocollo)) {
            $data = $richiestaProtocollo->getDataPg();
        }

        return $data;
    }

    public function getStatoLeggibile(): ?string {
        if($this->getStato() == 'COM_PAG_INSERITA') {
            return "Inserita da inviare";
        }
        
        if($this->getStato() == 'COM_PAG_INVIATA_PA' ) {
            return "Inviata in attesa di protocollazione";
        }
        
        if($this->getStato() == 'COM_PAG_PROTOCOLLATA' && $this->isInAttesaRisposta()) {
            return "In attesa di risposta";
        }
        
        if($this->getStato() == 'COM_PAG_PROTOCOLLATA' && !$this->isInAttesaRisposta()) {
            return "Risposta caricata dal beneficiario";
        }

        return '-';
    }

    public function isStatoGestibile(): ?bool {
        if($this->getStato() == 'COM_PAG_INSERITA') {
            return true;
        }
        
        if($this->getStato() == 'COM_PAG_INVIATA_PA' ) {
            return false;
        }
        
        if($this->getStato() == 'COM_PAG_PROTOCOLLATA' && $this->isInAttesaRisposta()) {
            return false;
        }
        
        if($this->getStato() == 'COM_PAG_PROTOCOLLATA' && !$this->isInAttesaRisposta()) {
            return false;
        }

        return false;
    }

    public function isInAttesaRisposta(): bool {
        $finali = array('COM_PAG_INVIATA_PA','COM_PAG_PROTOCOLLATA');
        if(!in_array($this->getRisposta()->getStato()->getCodice(), $finali)) {
            return true;
        } else {
            return false;
        }
    }

    public function getSoggetto(): ?Soggetto {
        return $this->pagamento->getSoggetto();
    }

    public function getIstruttoriaOggettoPagamento(): ?IstruttoriaOggettoPagamento {
        return $this->istruttoria_oggetto_pagamento;
    }

    public function setIstruttoriaOggettoPagamento(?IstruttoriaOggettoPagamento $istruttoria_oggetto_pagamento) {
        $this->istruttoria_oggetto_pagamento = $istruttoria_oggetto_pagamento;
    }

    public function getNomeClasse() {
        return "ComunicazionePagamento";
    }

    public function getRichiesta(): ?Richiesta {
        return $this->pagamento->getRichiesta();
    }

    public function getComunicazione(): self {
        return $this;
    }

    public function getDataInvioPEC(): ?\DateTime{
        /** @var RichiestaProtocolloComunicazionePagamento $protocollo*/
        $protocollo = $this->richieste_protocollo->last();
        if($protocollo){
            /** @var EmailProtocollo|bool $email */
            $email = $protocollo->getEmailProtocollo()->last();
            if($email){
                return $email->getDataInvio();
            }
        }

        return null;
    }

    /**
     * @return TipologiaComunicazionePagamento|null
     */
    public function getTipologiaComunicazione(): ?TipologiaComunicazionePagamento
    {
        return $this->tipologia_comunicazione;
    }

    /**
     * @param TipologiaComunicazionePagamento|null $tipologia_comunicazione
     */
    public function setTipologiaComunicazione(?TipologiaComunicazionePagamento $tipologia_comunicazione): void
    {
        $this->tipologia_comunicazione = $tipologia_comunicazione;
    }

    /**
     * @return Collection|AllegatoComunicazionePagamento[]
     */
    public function getAllegati(): Collection
    {
        return $this->allegati;
    }

    public function addAllegati(AllegatoComunicazionePagamento $allegato): self{
        $this->allegati[] = $allegato;

        return $this;
    }

    public function removeAllegati(AllegatoComunicazionePagamento $allegato): void{
        $this->allegati->removeElement($allegato);
    }

    /**
     * @return $this
     */
    public function getComunicazionePagamento() {
        return $this;
    }
}
