<?php

namespace ProtocollazioneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;

/**
 * RichiestaProtocolloDocumento
 *
 * @ORM\Entity(repositoryClass="ProtocollazioneBundle\Repository\RichiestaProtocolloDocumentoRepository")
 * @ORM\Table(name="richieste_protocollo_documenti",
 *     indexes={
 *         @ORM\Index(name="idx_richiesta_protocollo_id", columns={"richiesta_protocollo_id"})
 *     })
 */
class RichiestaProtocolloDocumento extends EntityLoggabileCancellabile {
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="RichiestaProtocollo", inversedBy="richiesta_protocollo_documenti")
     * @ORM\JoinColumn(name="richiesta_protocollo_id", referencedColumnName="id")
     */
    protected $richiesta_protocollo;

    /**
     * @var string
     *
     * @ORM\Column(name="tabella_documento", type="string", length=255, nullable=true)
     */
    private $tabella_documento;

    /**
     * @var int
     *
     * @ORM\Column(name="tabella_documento_id", type="integer", nullable=true)
     */
    private $tabella_documento_id;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=1024)
     */
    private $path = "";

    /**
     * @var string
     *
     * @ORM\Column(name="idDocument", type="string", length=255, nullable=true)
     */
    private $identificativoDocEr;

    /**
     * @var int
     *
     * @ORM\Column(name="esito", type="smallint")
     */
    private $esito = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="principale", type="smallint")
     */
    private $principale = 0;

    /**
     * Get id
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set tabellaDocumento
     *
     * @param string $tabellaDocumento
     *
     * @return RichiestaProtocolloDocumento
     */
    public function setTabellaDocumento($tabellaDocumento) {
        $this->tabella_documento = $tabellaDocumento;

        return $this;
    }

    /**
     * Get tabellaDocumento
     *
     * @return string
     */
    public function getTabellaDocumento() {
        return $this->tabella_documento;
    }

    /**
     * Set tabellaDocumentoId
     *
     * @param int $tabellaDocumentoId
     *
     * @return RichiestaProtocolloDocumento
     */
    public function setTabellaDocumentoId($tabellaDocumentoId) {
        $this->tabella_documento_id = $tabellaDocumentoId;

        return $this;
    }

    /**
     * Get tabellaDocumentoId
     *
     * @return int
     */
    public function getTabellaDocumentoId() {
        return $this->tabella_documento_id;
    }

    /**
     * Set path
     *
     * @param string $path
     *
     * @return RichiestaProtocolloDocumento
     */
    public function setPath($path) {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * Set identificativoDocEr
     *
     * @param string $identificativoDocEr
     *
     * @return RichiestaProtocolloDocumento
     */
    public function setIdentificativoDocEr($identificativoDocEr) {
        $this->identificativoDocEr = $identificativoDocEr;
    }

    /**
     * Get identificativoDocEr
     *
     * @return string
     */
    public function getIdentificativoDocEr() {
        return $this->identificativoDocEr;
    }

    /**
     * Set esito
     *
     * @param int $esito
     *
     * @return RichiestaProtocolloDocumento
     */
    public function setEsito($esito) {
        $this->esito = $esito;

        return $this;
    }

    /**
     * Get esito
     *
     * @return int
     */
    public function getEsito() {
        return $this->esito;
    }

    /**
     * Set principale
     *
     * @param int $principale
     *
     * @return RichiestaProtocolloDocumento
     */
    public function setPrincipale($principale) {
        $this->principale = $principale;

        return $this;
    }

    /**
     * @return int
     */
    public function getPrincipale() {
        return $this->principale;
    }

    /**
     * @param RichiestaProtocollo $richiestaProtocollo
     *
     * @return RichiestaProtocolloDocumento
     */
    public function setRichiestaProtocollo($richiestaProtocollo = null) {
        $this->richiesta_protocollo = $richiestaProtocollo;

        return $this;
    }

    /**
     * @return RichiestaProtocollo
     */
    public function getRichiestaProtocollo() {
        return $this->richiesta_protocollo;
    }
}
