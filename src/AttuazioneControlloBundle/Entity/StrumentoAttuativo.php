<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use RichiesteBundle\Entity\Richiesta;
use SfingeBundle\Entity\Procedura;
use MonitoraggioBundle\Entity\TC15StrumentoAttuativo;

/**
 * @ORM\Entity
 * @ORM\Table(name="richiesta_strumento_attuativo")
 *
 * @author lfontana
 */
class StrumentoAttuativo extends EntityLoggabileCancellabile {
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC15StrumentoAttuativo", inversedBy="strumenti_attuativi")
     * @ORM\JoinColumn(name="stru_att_id", referencedColumnName="id", nullable=false)
     * @var TC15StrumentoAttuativo
     */
    protected $tc15_strumento_attuativo;

    /**
     * @var \RichiesteBundle\Entity\Richiesta
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="mon_strumenti_attuativi")
     * @ORM\JoinColumn(name="richiesta_id", referencedColumnName="id", nullable=false)
     * @var Richiesta
     */
    protected $richiesta;

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return TC15StrumentoAttuativo
     */
    public function getTc15StrumentoAttuativo() {
        return $this->tc15_strumento_attuativo;
    }

    /**
     * @param TC15StrumentoAttuativo $tc15_strumento_attuativo
     * @return self
     */
    public function setTc15StrumentoAttuativo($tc15_strumento_attuativo) {
        $this->tc15_strumento_attuativo = $tc15_strumento_attuativo;
        return $this;
    }

    /**
     * @return Richiesta
     */
    public function getRichiesta() {
        return $this->richiesta;
    }

    /**
     * @param Richiesta $richiesta
     * @return self
     */
    public function setRichiesta(Richiesta $richiesta) {
        $this->richiesta = $richiesta;
        return $this;
    }

    /**
     * @param Richiesta $richiesta = null
     * @param TC15StrumentoAttuativo $tc15_strumento_attuativo = null
     */
    public function __construct(Richiesta $richiesta = null, TC15StrumentoAttuativo $tc15_strumento_attuativo = null) {
        $this->tc15_strumento_attuativo = $tc15_strumento_attuativo;
        $this->richiesta = $richiesta;
    }

    /**
     * @return Procedura
     */
    public function getProcedura() {
        return $this->richiesta->getProcedura();
    }
}
