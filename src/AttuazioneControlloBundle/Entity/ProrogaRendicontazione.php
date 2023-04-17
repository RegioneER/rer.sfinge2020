<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use BaseBundle\Entity\Id;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use RichiesteBundle\Entity\Richiesta;

/**
 * @ORM\Table(name="proroghe_rendicontazioni")
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Repository\ProrogaRendicontazioneRepository")
 * @Assert\Expression("this.getDataInizio() != null || this.getDataScadenza() != null",
 *     message="E' necessario indicare la data di inizio o fine rendicontazione"
 * )
 */
class ProrogaRendicontazione extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloRichiesta", inversedBy="proroghe_rendicontazione")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull
     * @var AttuazioneControlloRichiesta
     */
    private $attuazione_controllo_richiesta;

    /**
     * @ORM\ManyToOne(targetEntity="ModalitaPagamento")
     * @ORM\JoinColumn(nullable=true)
     * @Assert\NotNull
     * @var ModalitaPagamento
     */
    private $modalita_pagamento;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @var \DateTime|null
     * @Assert\Date
     * @Assert\NotNull
     */
    private $data_inizio;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @var \DateTime|null
     * @Assert\Date
     * @Assert\Expression("value > this.getDataInizio()",
     *     message="La data di scandenza non può essere precedente della data di inizio rendicontazione"
     * )
     * @Assert\NotNull
     */
    private $data_scadenza;

    public function __construct(AttuazioneControlloRichiesta $atc) {
        $this->attuazione_controllo_richiesta = $atc;
    }

    public function setAttuazioneControlloRichiesta(AttuazioneControlloRichiesta $attuazioneControlloRichiesta): self {
        $this->attuazione_controllo_richiesta = $attuazioneControlloRichiesta;

        return $this;
    }

    public function getAttuazioneControlloRichiesta(): AttuazioneControlloRichiesta {
        return $this->attuazione_controllo_richiesta;
    }

    public function setModalitaPagamento(ModalitaPagamento $modalitaPagamento): self {
        $this->modalita_pagamento = $modalitaPagamento;

        return $this;
    }

    public function getModalitaPagamento(): ?ModalitaPagamento {
        return $this->modalita_pagamento;
    }

    public function setDataInizio(?\DateTime $dataInizio): self {
        $this->data_inizio = $dataInizio;

        return $this;
    }

    public function getDataInizio(): ?\DateTime {
        return $this->data_inizio;
    }

    public function setDataScadenza(?\DateTime $dataScadenza): self {
        $this->data_scadenza = $dataScadenza;

        return $this;
    }

    public function getDataScadenza(): ?\DateTime {
        return $this->data_scadenza;
    }

    public function getProtocollo(): string {
        return $this->getRichiesta()->getProtocollo();
    }

    public function getRichiesta(): ?Richiesta {
        return $this->attuazione_controllo_richiesta->getRichiesta();
    }

    public function isRendicontabile(\DateTime $dataRif): bool {
        $dataRif = $dataRif ?: new \DateTime();
        return $dataRif >= $this->data_inizio && 
                $dataRif <= $this->data_scadenza->modify('+23 hours')->modify('+59 minutes')->modify('+59 seconds');
    }
}
