<?php

namespace AttuazioneControlloBundle\Entity\Istruttoria;

use Doctrine\ORM\Mapping as ORM;
use AttuazioneControlloBundle\Entity\Pagamento;

/**
 * AssegnamentoIstruttoriaPagamento
 *
 * @ORM\Table(name="assegnamenti_istruttorie_pagamenti")
 * @ORM\Entity()
 */
class AssegnamentoIstruttoriaPagamento
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", inversedBy="assegnamenti_istruttoria")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $pagamento;

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Utente")
     * @ORM\JoinColumn(nullable=false)
    */
    protected $istruttore;  
    
    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Utente")
     * @ORM\JoinColumn(nullable=false)
    */
    protected $assegnatore;      

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="data_assegnamento", type="datetime")
     */
    protected $dataAssegnamento;

    /**
     * @var bool
     *
     * @ORM\Column(name="attivo", type="boolean")
     */
    protected $attivo;


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
     * Set dataAssegnamento
     *
     * @param \DateTime $dataAssegnamento
     * @return AssegnamentoIstruttoriaPagamento
     */
    public function setDataAssegnamento($dataAssegnamento)
    {
        $this->dataAssegnamento = $dataAssegnamento;

        return $this;
    }

    /**
     * Get dataAssegnamento
     *
     * @return \DateTime 
     */
    public function getDataAssegnamento()
    {
        return $this->dataAssegnamento;
    }

    /**
     * Set attivo
     *
     * @param boolean $attivo
     * @return AssegnamentoIstruttoriaPagamento
     */
    public function setAttivo($attivo)
    {
        $this->attivo = $attivo;

        return $this;
    }

    /**
     * Get attivo
     *
     * @return boolean 
     */
    public function getAttivo()
    {
        return $this->attivo;
    }

    /**
     * Set pagamento
     *
     * @param Pagamento $pagamento
     * @return AssegnamentoIstruttoriaPagamento
     */
    public function setPagamento($pagamento)
    {
        $this->pagamento = $pagamento;

        return $this;
    }

    /**
     * Get pagamento
     *
     * @return Pagamento
     */
    public function getPagamento()
    {
        return $this->pagamento;
    }
    
    public function getIstruttore() {
        return $this->istruttore;
    }

    public function getAssegnatore() {
        return $this->assegnatore;
    }

    public function setIstruttore($istruttore) {
        $this->istruttore = $istruttore;
    }

    public function setAssegnatore($assegnatore) {
        $this->assegnatore = $assegnatore;
    }

}
