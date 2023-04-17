<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Entity\TC47StatoProgetto;

/**
 * @ORM\Entity()
 * @ORM\Table(name="richiesta_stato_attuazione_progetto")
 * @author lfontana
 * 
 */
class RichiestaStatoAttuazioneProgetto extends EntityLoggabileCancellabile{
    
    /**
     * @var int
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
    protected $id;
    
    /**
     * @var Richiesta|null
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="mon_stato_progetti")
	 * @ORM\JoinColumn(name="richiesta_id", referencedColumnName="id", nullable=false)
	 *
	 * @Assert\NotNull()
	 */
    protected $richiesta;

    /**
     *
     * @var \DateTime|null
     * @ORM\Column( type="date", nullable = false )
     * @Assert\Date()
     * @Assert\NotBlank()
     */
    protected $data_riferimento;
    
    /**
	 * @var TC47StatoProgetto|null
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC47StatoProgetto")
	 * @ORM\JoinColumn(name="stato_progetto_id", referencedColumnName="id", nullable=false)
	 * @Assert\NotNull()
	 */
    
    protected $stato_progetto;
    
    public function __construct(?Richiesta $richiesta = null, ?TC47StatoProgetto $stato = null) {
        $this->richiesta = $richiesta;
        $this->stato_progetto = $stato;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setDataRiferimento(?\DateTime $dataRiferimento): self
    {
        $this->data_riferimento = $dataRiferimento;

        return $this;
    }

    public function getDataRiferimento(): ?\DateTime
    {
        return $this->data_riferimento;
    }

    public function setRichiesta(?Richiesta $richiesta): self
    {
        $this->richiesta = $richiesta;

        return $this;
    }

    public function getRichiesta(): ?Richiesta
    {
        return $this->richiesta;
    }


    public function setStatoProgetto(?TC47StatoProgetto $statoProgetto): self
    {
        $this->stato_progetto = $statoProgetto;

        return $this;
    }

    public function getStatoProgetto(): ?TC47StatoProgetto
    {
        return $this->stato_progetto;
    }
    
    
}
