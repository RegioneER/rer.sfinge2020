<?php

/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 13/01/16
 * Time: 11:37
 */

namespace DocumentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DocumentoBundle\Validator\Constraints as DocumentoAssert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @DocumentoAssert\ValidaDocumento
 */
class DocumentoFile extends Documento {
    /**
     * @var string
     *
     * @ORM\Column(name="mime_type", type="string", length=255)
     */
    protected $mime_type;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=1024)
     */
    protected $path;

    /**
     * @var integer
     *
     * @ORM\Column(name="file_size", type="integer")
     */
    protected $file_size;

    /**
     * @Assert\File(maxSize="1073741824")
     * @var UploadedFile
     */
    protected $file;

    /**
     * @ORM\Column(name="cf_firmatario", type="string", length=50)
     */
    protected $cf_firmatario;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="data_scadenza", type="date", nullable=true)
     */
    protected $data_scadenza;

    public function getFile(): ?UploadedFile {
        return $this->file;
    }

    public function setFile(UploadedFile $file) {
        $this->file = $file;
    }

    public function getMimeType(): ?string {
        return $this->mime_type;
    }

    public function setMimeType(string $mime_type) {
        $this->mime_type = $mime_type;
    }

    public function getPath(): ?string {
        return $this->path;
    }

    public function setPath(string $path) {
        $this->path = $path;
    }

    public function getFileSize(): ?int {
        return $this->file_size;
    }

    public function setFileSize(int $file_size) {
        $this->file_size = $file_size;
    }

    public function getCfFirmatario(): ?string {
        return $this->cf_firmatario;
    }

    public function setCfFirmatario(string $cf_firmatario) {
        $this->cf_firmatario = $cf_firmatario;
    }

    public function setDataScadenza(?\DateTime $dataScadenza): self {
        $this->data_scadenza = $dataScadenza;

        return $this;
    }

    public function getDataScadenza(): ?\DateTime {
        return $this->data_scadenza;
    }

    public function __clone() {
        if ($this->id) {
            $this->id = null;
        }
    }

    public function isP7m(): bool {
        return 'application/octet-stream' == $this->mime_type;
    }

    public function isXml(): bool {
        $basename = explode('.', basename($this->nome_originale, '.p7m'));

        $est = end($basename);

        if ('xml' === $est) {
            return true;
        }

        return 'text/xml' === $this->mime_type || 'application/xml' === $this->mime_type || 'text/plain' === $this->mime_type;
    }

    public function isFileXml(): bool {
        $fullname = $this->file ? basename($this->file->getClientOriginalName(), '.' . $this->file->getClientOriginalExtension()) : $this->getNomeOriginale();
        $basename = explode('.', $fullname);
        $est = end($basename);
        if ('xml' === $est) {
            return true;
        }
        $mime = $this->file ? $this->file->getMimeType() : $this->getMimeType();

        return 'text/xml' === $mime || 'application/xml' === $mime;
    }

    public function isGiustificativo(): bool {
        return 'GIUSTIFICATIVO' === $this->getTipologiaDocumento()->getCodice() || 'GIUSTIFICATIVO_CON_SP' === $this->getTipologiaDocumento()->getCodice();
    }

    public function isFatturaElettronica(): bool {
        $ext = $this->file->getClientOriginalExtension();
        $isFatturaElettronica = \in_array(\strtolower($ext), ['xml', 'p7m']) || $this->tipologia_documento->isFatturaElettronica();

        return $isFatturaElettronica;
    }
    
    public function getExt() {
        $basename = explode('.', basename($this->getFile()->getClientOriginalName()));
        $est = end($basename);
        return $est;
    }
    
    public function isEstensioneNonAmmessa() {
        $arrayNonAmmessi = array('p7s', 'p7e', 'p7c');
        if(in_array(strtolower($this->getExt()), $arrayNonAmmessi)) {
            return true;
        }
        return false;
    }
}
