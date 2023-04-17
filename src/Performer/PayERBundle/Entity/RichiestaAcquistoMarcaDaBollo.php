<?php

namespace Performer\PayERBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class RichiestaAcquistoMarcaDaBollo
 *
 * @ORM\Entity()
 * @ORM\Table(name="payer_ebollo_richiesta_acquisto_marca_da_bollo")
 */
class RichiestaAcquistoMarcaDaBollo
{
    /**
     * @var string|null
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(name="id", type="guid", nullable=false)
     */
    protected $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="identificativo_versante", type="string", length=16, nullable=false)
     * @Assert\NotBlank
     * @Assert\Length(max="16", min="11")
     */
    protected $identificativoVersante;

    /**
     * @var string|null
     *
     * @ORM\Column(name="denominazione_versante", type="string", length=50, nullable=true)
     * @Assert\Length(max="50")
     */
    protected $denominazioneVersante;

    /**
     * @var string|null
     *
     * @ORM\Column(name="email_versante", type="string", length=50, nullable=true)
     * @Assert\Length(max="50")
     */
    protected $emailVersante;

    /**
     * @var string|null
     *
     * @ORM\Column(name="rid", type="guid", nullable=true)
     */
    protected $rid;

    /**
     * @var string|null
     *
     * @ORM\Column(name="pid", type="guid", nullable=true)
     */
    protected $pid;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="data_invio", type="datetime", nullable=true)
     */
    protected $dataInvio;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="data_notifica_pid", type="datetime", nullable=true)
     */
    protected $dataNotificaPid;

    /**
     * @var Esito|null
     *
     * @ORM\ManyToOne(targetEntity="Performer\PayERBundle\Entity\Esito")
     * @ORM\JoinColumn(name="esito_id", referencedColumnName="id", nullable=true)
     */
    protected $esito;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="data_esito", type="datetime", nullable=true)
     */
    protected $dataEsito;

    /**
     * @var AcquistoMarcaDaBollo[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Performer\PayERBundle\Entity\AcquistoMarcaDaBollo", mappedBy="richiesta", cascade={"persist", "remove"})
     * @Assert\Valid()
     */
    protected $acquistoMarcaDaBollos;

    /**
     * RichiestaAcquistoMarcaDaBollo constructor.
     */
    public function __construct()
    {
        $this->acquistoMarcaDaBollos = new ArrayCollection();
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return self
     */
    public function setId(?string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIdentificativoVersante(): ?string
    {
        return $this->identificativoVersante;
    }

    /**
     * @param string|null $identificativoVersante
     * @return self
     */
    public function setIdentificativoVersante(?string $identificativoVersante): self
    {
        $this->identificativoVersante = $identificativoVersante;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDenominazioneVersante(): ?string
    {
        return $this->denominazioneVersante;
    }

    /**
     * @param string|null $denominazioneVersante
     * @return self
     */
    public function setDenominazioneVersante(?string $denominazioneVersante): self
    {
        $this->denominazioneVersante = $denominazioneVersante;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmailVersante(): ?string
    {
        return $this->emailVersante;
    }

    /**
     * @param string|null $emailVersante
     * @return self
     */
    public function setEmailVersante(?string $emailVersante): self
    {
        $this->emailVersante = $emailVersante;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRid(): ?string
    {
        return $this->rid;
    }

    /**
     * @param string|null $rid
     * @return self
     */
    public function setRid(?string $rid): self
    {
        $this->rid = $rid;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPid(): ?string
    {
        return $this->pid;
    }

    /**
     * @param string|null $pid
     * @return self
     */
    public function setPid(?string $pid): self
    {
        $this->pid = $pid;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getDataInvio(): ?DateTime
    {
        return $this->dataInvio;
    }

    /**
     * @param DateTime|null $dataInvio
     * @return self
     */
    public function setDataInvio(?DateTime $dataInvio): self
    {
        $this->dataInvio = $dataInvio;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getDataNotificaPid(): ?DateTime
    {
        return $this->dataNotificaPid;
    }

    /**
     * @param DateTime|null $dataNotificaPid
     * @return self
     */
    public function setDataNotificaPid(?DateTime $dataNotificaPid): self
    {
        $this->dataNotificaPid = $dataNotificaPid;
        return $this;
    }

    /**
     * @return Esito|null
     */
    public function getEsito(): ?Esito
    {
        return $this->esito;
    }

    /**
     * @param Esito|null $esito
     * @return self
     */
    public function setEsito(?Esito $esito): self
    {
        $this->esito = $esito;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getDataEsito(): ?DateTime
    {
        return $this->dataEsito;
    }

    /**
     * @param DateTime|null $dataEsito
     * @return self
     */
    public function setDataEsito(?DateTime $dataEsito): self
    {
        $this->dataEsito = $dataEsito;
        return $this;
    }

    /**
     * @return ArrayCollection|AcquistoMarcaDaBollo[]
     */
    public function getAcquistoMarcaDaBollos()
    {
        return $this->acquistoMarcaDaBollos;
    }

    /**
     * @param ArrayCollection|AcquistoMarcaDaBollo[] $acquistoMarcaDaBollos
     * @return self
     */
    public function setAcquistoMarcaDaBollos($acquistoMarcaDaBollos): self
    {
        $this->acquistoMarcaDaBollos = $acquistoMarcaDaBollos;
        return $this;
    }

    /**
     * @param AcquistoMarcaDaBollo $acquistoMarcaDaBollo
     * @return $this
     */
    public function addAcquistoMarcaDaBollo(AcquistoMarcaDaBollo $acquistoMarcaDaBollo)
    {
        if (!$this->acquistoMarcaDaBollos->contains($acquistoMarcaDaBollo)) {
            $acquistoMarcaDaBollo->setRichiesta($this);
            $this->acquistoMarcaDaBollos->add($acquistoMarcaDaBollo);
        }

        return $this;
    }

    /**
     * @param AcquistoMarcaDaBollo $acquistoMarcaDaBollo
     * @return $this
     */
    public function removeAcquistoMarcaDaBollo(AcquistoMarcaDaBollo $acquistoMarcaDaBollo)
    {
        $acquistoMarcaDaBollo->setRichiesta(null);
        $this->acquistoMarcaDaBollos->remove($acquistoMarcaDaBollo);
        return $this;
    }

    /**
     * @return bool
     */
    public function isInviata(): bool
    {
        return $this->dataInvio !== null;
    }

    /**
     * @return bool
     */
    public function hasErroreInvio(): bool
    {
        return $this->esito
            && $this->esito->isErrore()
        ;
    }

    /**
     * @return Esito|null
     */
    public function getErroreInvio(): ?Esito
    {
        if (!$this->hasErroreInvio()) {
            return null;
        }

        return $this->esito;
    }

    /**
     * @return bool
     */
    public function isInTimeout(): bool
    {
        return $this->esito === null && $this->rid === null;
    }
}