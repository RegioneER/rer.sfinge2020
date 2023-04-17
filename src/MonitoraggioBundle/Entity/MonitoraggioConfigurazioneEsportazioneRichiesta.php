<?php

/**
 * @author lfontana
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use MonitoraggioBundle\Repository\MonitoraggioEsportazioneRepository;
use RichiesteBundle\Entity\Richiesta;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\MonitoraggioConfigurazioneEsportazioneRichiestaRepository")
 */
class MonitoraggioConfigurazioneEsportazioneRichiesta extends MonitoraggioConfigurazioneEsportazione {
    public static $TAVOLE = [
        'AP00',
        'AP01',
        'AP02',
        'AP03',
        'AP04',
        'AP05',
        'AP06',
        'PG00',
        'IN00',
        'IN01',
        'FN00',
        'FN01',
        'FN02',
        'FN03',
        'FN04',
        'FN05',
        'FN06',
        'FN07',
        'FN08',
        'FN09',
        'FN10',
        'SC00',
        'PR00',
        'PR01',
    ];

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta")
     * @ORM\JoinColumn(name="richiesta_id", referencedColumnName="id", nullable=true)
     *
     * @var Richiesta
     */
    protected $richiesta;

    /**
     * @var ArrayCollection
     */
    protected $strutture;

    public function __construct($richiesta = null, $monitoraggio_esportazione = null, $struttura = null) {
        parent::__construct($monitoraggio_esportazione);
        $this->richiesta = $richiesta;
        $this->strutture = new ArrayCollection();
    }

    /**
     * @return Richiesta
     */
    public function getRichiesta(): Richiesta {
        return $this->richiesta;
    }

    /**
     * @param Richiesta $richiesta
     *
     * @return self
     */
    public function setRichiesta(Richiesta $richiesta): self {
        $this->richiesta = $richiesta;

        return $this;
    }

    public function getStrutture() {
        return $this->strutture;
    }

    public function setStrutture($strutture): self {
        $this->strutture = $strutture;
        return $this;
    }

    public function addStrutture($struttura): void {
        $this->strutture->add($struttura);
    }

    public function finalizedTavole(): self {
        foreach (\array_keys(MonitoraggioEsportazioneRepository::$ENTITY_REPOSITORY) as $struttura_tavola) {
            if (!$this->strutture->contains($struttura_tavola)) {
                $tavola = new MonitoraggioConfigurazioneEsportazioneTavole($this);
                $tavola->setTavolaProtocollo($struttura_tavola);
                $this->addMonitoraggioConfigurazioneEsportazioneTavole($tavola);
            }
        }

        return $this;
    }

    /**
     * @return Richiesta
     */
    public function getElemento(): Richiesta {
        return $this->richiesta;
    }

    /**
     * @param Richiesta $richiesta
     * @return self
     */
    public function setElemento($richiesta): self {
        $this->richiesta = $richiesta;
        return $this;
    }
}
