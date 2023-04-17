<?php

/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 04/01/16
 * Time: 15:22
 */

namespace SoggettoBundle\Entity;

use AnagraficheBundle\Entity\Persona;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping AS ORM;
use DocumentoBundle\Entity\DocumentoFile;

/**
 *
 * @ORM\Entity(repositoryClass="SoggettoBundle\Entity\IncaricoPersonaRepository")
 * @ORM\Table(name="incarico_persona",
 *  indexes={
 *      @ORM\Index(name="idx_soggetto_id", columns={"soggetto_id"}),
 *      @ORM\Index(name="idx_incaricato_id", columns={"incaricato_id"}),
 *      @ORM\Index(name="idx_tipo_incarico_id", columns={"tipo_incarico_id"}),
 *      @ORM\Index(name="idx_stato_id", columns={"stato_id"}),
 *      @ORM\Index(name="idx_documento_nomina_id", columns={"documento_nomina_id"})
 *  }
 * )
 *
 */
class IncaricoPersona extends EntityLoggabileCancellabile {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Soggetto", inversedBy="incarichi_persone")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $soggetto;

    /**
     * @ORM\ManyToOne(targetEntity="AnagraficheBundle\Entity\Persona", inversedBy="incarichi_persone", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    protected $incaricato;

    /**
     * @ORM\ManyToOne(targetEntity="TipoIncarico")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $tipo_incarico;

    /**
     * @ORM\ManyToOne(targetEntity="StatoIncarico")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $stato;

    /**
     * @ORM\ManyToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $documento_nomina;

    /**
     * @ORM\ManyToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $carta_identita_lr;

    /**
     * @ORM\OneToMany(targetEntity="SoggettoBundle\Entity\DocumentoIncarico", mappedBy="incarico")
     */
    protected $documenti_incarico;

    /**
     * @ORM\Column(type="text", nullable=true, name="nota")
     */
    private $nota;

    /**
     * @ORM\OneToMany(targetEntity="SoggettoBundle\Entity\IncaricoPersonaRichiesta", mappedBy="incarico_persona")
     * @var IncaricoPersonaRichiesta[]|Collection
     */
    protected $incarichi_richiesta;

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return Persona
     */
    public function getIncaricato() {
        return $this->incaricato;
    }

    /**
     * @param Persona $incaricato
     */
    public function setIncaricato(Persona $incaricato) {
        $this->incaricato = $incaricato;
    }

    /**
     * @return Soggetto
     */
    public function getSoggetto() {
        return $this->soggetto;
    }

    /**
     * @param Soggetto $soggetto
     */
    public function setSoggetto(Soggetto $soggetto) {
        $this->soggetto = $soggetto;
    }

    /**
     * @return TipoIncarico
     */
    public function getTipoIncarico() {
        return $this->tipo_incarico;
    }

    /**
     * @param mixed $tipo_incarico
     */
    public function setTipoIncarico(TipoIncarico $tipo_incarico) {
        $this->tipo_incarico = $tipo_incarico;
    }

    /**
     * @return StatoIncarico
     */
    public function getStato() {
        return $this->stato;
    }

    /**
     * @param StatoIncarico $stato
     */
    public function setStato(StatoIncarico $stato) {
        $this->stato = $stato;
    }

    /**
     * @return DocumentoFile
     */
    public function getDocumentoNomina() {
        return $this->documento_nomina;
    }

    /**
     * @param DocumentoFile $documento_nomina
     */
    public function setDocumentoNomina(DocumentoFile $documento_nomina) {
        $this->documento_nomina = $documento_nomina;
    }

    /**
     * Set nota
     *
     * @param string $nota
     * @return IncaricoPersona
     */
    public function setNota($nota) {
        $this->nota = $nota;

        return $this;
    }

    /**
     * Get nota
     *
     * @return string 
     */
    public function getNota() {
        return $this->nota;
    }

    /**
     * Usato dall'annotazione ControlloAccesso
     */
    public function getIncaricoPersona(): self {
        return $this;
    }

    /**
     * Set carta_identita_lr
     *
     * @param \DocumentoBundle\Entity\DocumentoFile $cartaIdentitaLr
     * @return IncaricoPersona
     */
    public function setCartaIdentitaLr(\DocumentoBundle\Entity\DocumentoFile $cartaIdentitaLr = null) {
        $this->carta_identita_lr = $cartaIdentitaLr;

        return $this;
    }

    /**
     * Get carta_identita_lr
     *
     * @return \DocumentoBundle\Entity\DocumentoFile 
     */
    public function getCartaIdentitaLr() {
        return $this->carta_identita_lr;
    }

    public function isAttivo(): bool {
        return $this->stato && ($this->stato->getCodice() == StatoIncarico::ATTIVO);
    }

    /**
     * 
     * @return Collection|IncaricoPersonaRichiesta[]
     */
    public function getIncarichiRichiesta(): Collection {
        return $this->incarichi_richiesta;
    }

    public function setIncarichiRichiesta(Collection $incarichi_richiesta) {
        $this->incarichi_richiesta = $incarichi_richiesta;
    }

    public function __toString() {
        try {
            return (string) $this->getId();
        } catch (\Exception $exception) {
            return '';
        }
    }

    public function hasIncaricoProgetto($richiesta_id) {
        foreach ($this->getIncarichiRichiesta() as $inc) {
            if ($inc->getRichiesta()->getId() == $richiesta_id) {
                return true;
            }
        }
        return false;
    }

    public function addDocumentiIncarico(DocumentoIncarico $documenti): self {
        $this->documenti_incarico[] = $documenti;

        return $this;
    }

    public function removeDocumentiIncarico(DocumentoIncarico $documenti): void {
        $this->documenti_incarico->removeElement($documenti);
    }

    public function getDocumentiIncarico() {
        return $this->documenti_incarico;
    }

    public function setDocumentiIncarico($documenti_incarico): void {
        $this->documenti_incarico = $documenti_incarico;
    }

}
