<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="modalita_pagamento_procedure")
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Repository\ModalitaPagamentoProceduraRepository")
 */
class ModalitaPagamentoProcedura {
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="ModalitaPagamento", inversedBy="modalita_procedure")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull
     * @var ModalitaPagamento|null
     */
    protected $modalita_pagamento;

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Procedura", inversedBy="modalita_pagamento")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull
     * @var Procedura|null
     */
    protected $procedura;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2, nullable=true)
     * @Assert\Range(min=0, max= 100)
     * @var float|null
     */
    protected $percentuale_contributo;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\Date
     * @var \DateTime|null
     */
    protected $data_inizio_rendicontazione;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\Date
     */
    protected $data_fine_rendicontazione;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\Date
     * @var \DateTime|null
     */
    protected $data_invio_abilitata;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min=0)
     * @var int|null
     */
    protected $finestra_temporale;

    public function getId() {
        return $this->id;
    }

    public function getModalitaPagamento(): ?ModalitaPagamento {
        return $this->modalita_pagamento;
    }

    public function getProcedura(): ?Procedura {
        return $this->procedura;
    }

    public function getPercentualeContributo() {
        return $this->percentuale_contributo;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setModalitaPagamento(?ModalitaPagamento $modalita_pagamento): self {
        $this->modalita_pagamento = $modalita_pagamento;
        return $this;
    }

    public function setProcedura(?Procedura $procedura): self {
        $this->procedura = $procedura;
        return $this;
    }

    public function setPercentualeContributo($percentuale_contributo) {
        $this->percentuale_contributo = $percentuale_contributo;
        return $this;
    }

    public function getDataInizioRendicontazione(): ?\DateTime {
        return $this->data_inizio_rendicontazione;
    }

    public function getDataFineRendicontazione(): ?\DateTime {
        return $this->data_fine_rendicontazione;
    }

    public function setDataInizioRendicontazione(?\DateTime $data_inizio_rendicontazione): self {
        $this->data_inizio_rendicontazione = $data_inizio_rendicontazione;

        return $this;
    }

    public function setDataFineRendicontazione(?\DateTime $data_fine_rendicontazione): self {
        $this->data_fine_rendicontazione = $data_fine_rendicontazione;

        return $this;
    }

    public function getDataInvioAbilitata(): ?\DateTime {
        return $this->data_invio_abilitata;
    }

    public function setDataInvioAbilitata(?\DateTime $data_invio_abilitata) {
        $this->data_invio_abilitata = $data_invio_abilitata;
    }

    public function setFinestraTemporale(?int $finestraTemporale): self {
        $this->finestra_temporale = $finestraTemporale;

        return $this;
    }

    public function getFinestraTemporale(): ?int {
        return $this->finestra_temporale;
    }
}
