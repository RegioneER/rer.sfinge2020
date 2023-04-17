<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use MonitoraggioBundle\Entity\TC12Classificazione;

/**
 * @author lfontana
 *
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Repository\RichiestaProgrammaClassificazioneRepository")
 * @ORM\Table(name="richieste_programmi_classificazioni")
 */
class RichiestaProgrammaClassificazione extends EntityLoggabileCancellabile {
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    protected $id;

    /**
     * @var \AttuazioneControlloBundle\Entity\RichiestaProgramma
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\RichiestaProgramma", inversedBy="classificazioni")
     * @ORM\JoinColumn(name="richiesta_programma_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull
     */
    protected $richiesta_programma;

    /**
     * @var \MonitoraggioBundle\Entity\TC12Classificazione
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC12Classificazione", inversedBy="richieste_classificazioni")
     * @ORM\JoinColumn(name="classificazione_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull
     */
    protected $classificazione;

    /**
     * Get id
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param RichiestaProgramma $richiestaProgramma
     * @return RichiestaProgrammaClassificazione
     */
    public function setRichiestaProgramma(RichiestaProgramma $richiestaProgramma) {
        $this->richiesta_programma = $richiestaProgramma;

        return $this;
    }

    /**
     * @return RichiestaProgramma
     */
    public function getRichiestaProgramma() {
        return $this->richiesta_programma;
    }

    /**
     * @param TC12Classificazione $classificazione
     * @return RichiestaProgrammaClassificazione
     */
    public function setClassificazione(TC12Classificazione $classificazione = null) {
        $this->classificazione = $classificazione;

        return $this;
    }

    /**
     * @return TC12Classificazione
     */
    public function getClassificazione() {
        return $this->classificazione;
    }

    public function __construct(RichiestaProgramma $richiesta_programma = null, TC12Classificazione $classificazione = null) {
        $this->richiesta_programma = $richiesta_programma;
        $this->classificazione = $classificazione;
    }

    /**
     * @return \SfingeBundle\Entity\Procedura
     */
    public function getProcedura() {
        return $this->getRichiestaProgramma()->getRichiesta()->getProcedura();
    }
}
