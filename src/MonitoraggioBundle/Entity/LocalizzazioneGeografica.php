<?php

/**
 * @author lfontana
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use RichiesteBundle\Entity\Richiesta;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\LocalizzazioneGeograficaRepository")
 * @ORM\Table(name="localizzazione_geografica",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(columns={"richiesta_id", "localizzazione_id", "data_cancellazione"})
 *     }))
 */
class LocalizzazioneGeografica extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="mon_localizzazione_geografica")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     * @Assert\NotNull
     */
    protected $richiesta;
    /**
     * @ORM\ManyToOne(targetEntity="TC16LocalizzazioneGeografica")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     * @Assert\NotNull
     */
    protected $localizzazione;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    protected $indirizzo;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    protected $cap;

    /**
     * @param \RichiesteBundle\entity\Richiesta|null $richiesta
     */
    public function __construct(Richiesta $richiesta = null) {
        $this->richiesta = $richiesta;
    }

    public function setIndirizzo(?string $indirizzo): self {
        $this->indirizzo = $indirizzo;

        return $this;
    }

    public function getIndirizzo(): ?string {
        return $this->indirizzo;
    }

    public function setCap(?string $cap): self {
        $this->cap = $cap;

        return $this;
    }

    public function getCap(): ?string {
        return $this->cap;
    }

    public function setRichiesta(Richiesta $richiesta): self {
        $this->richiesta = $richiesta;

        return $this;
    }

    public function getRichiesta(): ?Richiesta {
        return $this->richiesta;
    }

    public function setLocalizzazione(TC16LocalizzazioneGeografica $localizzazione): self {
        $this->localizzazione = $localizzazione;

        return $this;
    }

    public function getLocalizzazione(): ?TC16LocalizzazioneGeografica {
        return $this->localizzazione;
    }
}
