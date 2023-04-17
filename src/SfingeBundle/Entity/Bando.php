<?php

namespace SfingeBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="SfingeBundle\Entity\BandoRepository")
 */
class Bando extends Procedura {
    /**
     * Data pubblicazione BUR
     *
     * @var DateTime
     *
     * @ORM\Column(name="data_pubblicazione", type="date", nullable=true)
     */
    protected $data_pubblicazione;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="data_ora_inizio_presentazione", type="datetime", nullable=true)
     *
     * @Assert\NotBlank
     */
    protected $data_ora_inizio_presentazione;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="data_ora_fine_presentazione", type="datetime", nullable=true)
     *
     * @Assert\NotBlank
     */
    protected $data_ora_fine_presentazione;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="data_click_day", type="datetime", nullable=true)
     *
     */
    protected $data_click_day;

    /**
     * @ORM\OneToMany(targetEntity="SfingeBundle\Entity\DocumentoBando", mappedBy="bando")
     */
    private $documentoBando;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="data_ora_scadenza", type="datetime", nullable=true)
     */
    protected $data_ora_scadenza;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="data_ora_fine_creazione", type="datetime", nullable=true)
     *
     * @Assert\NotBlank
     */
    protected $data_ora_fine_creazione;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false, options={"default" : 1})
     * @Assert\NotNull
     */
    protected $richiesta_firma_digitale = true;

    /**
     * @return DateTime
     */
    public function getDataPubblicazione() {
        return $this->data_pubblicazione;
    }

    /**
     * @param DateTime $data_pubblicazione
     */
    public function setDataPubblicazione($data_pubblicazione) {
        $this->data_pubblicazione = $data_pubblicazione;
    }

    /**
     * @return DateTime
     */
    public function getDataOraInizioPresentazione() {
        return $this->data_ora_inizio_presentazione;
    }

    /**
     * @param DateTime $data_ora_inizio_presentazione
     */
    public function setDataOraInizioPresentazione($data_ora_inizio_presentazione) {
        $this->data_ora_inizio_presentazione = $data_ora_inizio_presentazione;
    }

    /**
     * @return DateTime
     */
    public function getDataOraFinePresentazione() {
        return $this->data_ora_fine_presentazione;
    }

    /**
     * @param DateTime $data_ora_fine_presentazione
     */
    public function setDataOraFinePresentazione($data_ora_fine_presentazione) {
        $this->data_ora_fine_presentazione = $data_ora_fine_presentazione;
    }

    /**
     * @return DateTime|null
     */
    public function getDataClickDay(): ?DateTime
    {
        return $this->data_click_day;
    }

    /**
     * @param DateTime|null $data_click_day
     */
    public function setDataClickDay(?DateTime $data_click_day): void
    {
        $this->data_click_day = $data_click_day;
    }

    public function getDocumentoBando() {
        return $this->documentoBando;
    }

    public function setDocumentoBando($documentoBando) {
        $this->documentoBando = $documentoBando;
    }

    /**
     * @return DateTime
     */
    public function getDataOraScadenza() {
        return $this->data_ora_scadenza;
    }

    /**
     * @param DateTime $data_ora_scadenza
     */
    public function setDataOraScadenza(?DateTime $data_ora_scadenza): self {
        $this->data_ora_scadenza = $data_ora_scadenza;

        return $this;
    }

    public function getDataOraFineCreazione(): ?DateTime {
        return $this->data_ora_fine_creazione;
    }

    public function setDataOraFineCreazione(?DateTime $data_ora_fine_creazione): self {
        $this->data_ora_fine_creazione = $data_ora_fine_creazione;
        
        return $this;
    }

    public function isModificabile(): bool {
        return $this->getDataOraInizioPresentazione() > new DateTime();
    }

    public function getTipo() {
        return "BANDO";
    }

    public function addDocumentoBando(DocumentoBando $documentoBando): self {
        $this->documentoBando[] = $documentoBando;

        return $this;
    }

    public function removeDocumentoBando(DocumentoBando $documentoBando): void {
        $this->documentoBando->removeElement($documentoBando);
    }

    /**
     * @return bool
     */
    public function isRichiestaFirmaDigitale(): bool
    {
        return $this->richiesta_firma_digitale;
    }

    /**
     * @param bool $richiesta_firma_digitale
     */
    public function setRichiestaFirmaDigitale(bool $richiesta_firma_digitale): void
    {
        $this->richiesta_firma_digitale = $richiesta_firma_digitale;
    }
}