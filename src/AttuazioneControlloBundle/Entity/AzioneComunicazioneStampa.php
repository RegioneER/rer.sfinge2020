<?php
/**
 * Created by PhpStorm.
 * User: giuseppe.dibona
 * Date: 2019-03-26
 * Time: 17:21
 */

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RichiesteBundle\Entity\Richiesta;
use SfingeBundle\Entity\Utente;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AzioneComunicazioneStampa
 *
 * @ORM\Entity()
 * @ORM\Table(name="azione_comunicazione_stampa",uniqueConstraints={
 *     @ORM\UniqueConstraint(name="azione_comunicazione_stampa_unique", columns={"richiesta_id", "tipo_stampa"})
 * })
 */
class AzioneComunicazioneStampa
{
    /**
     * @var string
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * @var Richiesta
     *
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta")
     * @ORM\JoinColumn(name="richiesta_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull()
     */
    protected $richiestaId;

    /**
     * @var string
     *
     * @ORM\Column(name="tipo_stampa", type="string", length=50, nullable=false)
     * @Assert\NotNull()
     */
    protected $tipoStampa;

    /**
     * @var Utente
     *
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Utente")
     * @ORM\JoinColumn(name="ultima_stampa_utente_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull()
     */
    protected $ultimaStampaUtenteId;

    /**
     * @var \DateTime
     * @ORM\Column(name="ultima_stampa_data", type="datetime", nullable=false)
     * @Assert\NotNull()
     */
    protected $ultimaStampaData;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return Richiesta
     */
    public function getRichiestaId(): Richiesta
    {
        return $this->richiestaId;
    }

    /**
     * @param Richiesta $richiestaId
     */
    public function setRichiestaId(Richiesta $richiestaId): void
    {
        $this->richiestaId = $richiestaId;
    }

    /**
     * @return string
     */
    public function getTipoStampa(): string
    {
        return $this->tipoStampa;
    }

    /**
     * @param string $tipoStampa
     */
    public function setTipoStampa(string $tipoStampa): void
    {
        $this->tipoStampa = $tipoStampa;
    }

    /**
     * @return Utente
     */
    public function getUltimaStampaUtenteId(): Utente
    {
        return $this->ultimaStampaUtenteId;
    }

    /**
     * @param Utente $ultimaStampaUtenteId
     */
    public function setUltimaStampaUtenteId(Utente $ultimaStampaUtenteId): void
    {
        $this->ultimaStampaUtenteId = $ultimaStampaUtenteId;
    }

    /**
     * @return \DateTime
     */
    public function getUltimaStampaData(): \DateTime
    {
        return $this->ultimaStampaData;
    }

    /**
     * @param \DateTime $ultimaStampaData
     */
    public function setUltimaStampaData(\DateTime $ultimaStampaData): void
    {
        $this->ultimaStampaData = $ultimaStampaData;
    }
}