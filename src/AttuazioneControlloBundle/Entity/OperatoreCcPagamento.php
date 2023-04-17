<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;

/**
 * OperatoreCcPagamento
 *
 * @ORM\Table(name="operatori_cc_pagamento")
 * @ORM\Entity()
 */
class OperatoreCcPagamento extends EntityLoggabileCancellabile {

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", inversedBy="operatori_cc")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $pagamento;

    /**
     * @ORM\ManyToOne(targetEntity="AnagraficheBundle\Entity\Persona")
     * @ORM\JoinColumn(name="persona_id", referencedColumnName="id", nullable=false)
     */
    private $persona;
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set pagamento
     *
     * @param \AttuazioneControlloBundle\Entity\Pagamento $pagamento
     * @return OperatoreCcPagamento
     */
    public function setPagamento(\AttuazioneControlloBundle\Entity\Pagamento $pagamento)
    {
        $this->pagamento = $pagamento;

        return $this;
    }

    /**
     * Get pagamento
     *
     * @return \AttuazioneControlloBundle\Entity\Pagamento 
     */
    public function getPagamento()
    {
        return $this->pagamento;
    }

    /**
     * Set persona
     *
     * @param \AnagraficheBundle\Entity\Persona $persona
     * @return OperatoreCcPagamento
     */
    public function setPersona(\AnagraficheBundle\Entity\Persona $persona)
    {
        $this->persona = $persona;

        return $this;
    }

    /**
     * Get persona
     *
     * @return \AnagraficheBundle\Entity\Persona 
     */
    public function getPersona()
    {
        return $this->persona;
    }
}
