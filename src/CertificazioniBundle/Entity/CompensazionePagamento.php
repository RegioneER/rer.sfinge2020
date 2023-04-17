<?php

namespace CertificazioniBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Validator\Constraints\ValidaLunghezza;
use AttuazioneControlloBundle\Entity\Pagamento;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use CertificazioniBundle\Entity\StatoChiusuraCertificazione;

/**
 * @ORM\Entity(repositoryClass="CertificazioniBundle\Repository\CompensazionePagamentoRepository")
 * @ORM\Table(name="compensazioni_pagamenti")
 */
class CompensazionePagamento extends EntityLoggabileCancellabile {

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="CertificazioniBundle\Entity\CertificazioneChiusura", inversedBy="compensazioni")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $chiusura;

    /**
     * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", inversedBy="compensazioni")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $pagamento;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=false)
     */
    protected $importo_compensazione;

    /**
     * @var boolean $ritiro
     * @ORM\Column(type="boolean", name="ritiro", nullable=true)
     */
    protected $ritiro;

    /**
     * @var boolean $recupero
     * @ORM\Column(type="boolean", name="recupero", nullable=true)
     */
    protected $recupero;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $note;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $anno_contabile;

    public function __construct() {
        
    }

    /**
     * @var boolean $taglio_ada
     * @ORM\Column(type="boolean", name="taglio_ada", nullable=true)
     */
    protected $taglio_ada;

    public function getId() {
        return $this->id;
    }

    public function getPagamento() {
        return $this->pagamento;
    }

    public function getImportoCompensazione() {
        return $this->importo_compensazione;
    }

    public function getRitiro() {
        return $this->ritiro;
    }

    public function getRecupero() {
        return $this->recupero;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setPagamento($pagamento) {
        $this->pagamento = $pagamento;
    }

    public function setImportoCompensazione($importo_compensazione) {
        $this->importo_compensazione = $importo_compensazione;
    }

    public function setRitiro($ritiro) {
        $this->ritiro = $ritiro;
    }

    public function setRecupero($recupero) {
        $this->recupero = $recupero;
    }

    public function getChiusura() {
        return $this->chiusura;
    }

    public function setChiusura($chiusura) {
        $this->chiusura = $chiusura;
    }

    public function getNote() {
        return $this->note;
    }

    public function getAnnoContabile() {
        return $this->anno_contabile;
    }

    public function setNote($note) {
        $this->note = $note;
    }

    public function setAnnoContabile($anno_contabile) {
        $this->anno_contabile = $anno_contabile;
    }

    public function isEliminabile() {
        return in_array($this->chiusura->getStato()->getCodice(), array(StatoChiusuraCertificazione::CHI_LAVORAZIONE, StatoChiusuraCertificazione::CHI_BLOCCATA));
    }

    public function getTaglioAda() {
        return $this->taglio_ada;
    }

    public function setTaglioAda($taglio_ada): void {
        $this->taglio_ada = $taglio_ada;
    }

}
