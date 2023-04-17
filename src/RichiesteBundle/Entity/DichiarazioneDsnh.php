<?php

namespace RichiesteBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Validator\Constraints\ValidaLunghezza;

/**
 * @ORM\Entity
 * @ORM\Table(name="dichiarazioni_dsnh") 
 * )
 */
class DichiarazioneDsnh extends EntityLoggabileCancellabile {

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="dichiarazione_dnsh")
     * @ORM\JoinColumn(name="richiesta_id", referencedColumnName="id", nullable=false)
     * @var Richiesta|null
     */
    private $richiesta;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var bool
     */
    protected $non_arreca;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var bool
     */
    protected $adotta_misure;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @ValidaLunghezza(min=0, max=5000)
     */
    protected $descrizione_adotta_misure;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @var bool
     */
    protected $specifica_documentazione;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @ValidaLunghezza(min=0, max=5000)
     */
    protected $descrizione_specifica_documentazione;

    public function __construct(Richiesta $richiesta = null) {
        $this->richiesta = $richiesta;
        $this->non_arreca = false;
        $this->adotta_misure = false;
        $this->specifica_documentazione = false;
    }

    public function getId() {
        return $this->id;
    }

    public function getRichiesta(): ?Richiesta {
        return $this->richiesta;
    }

    public function getNonArreca(): bool {
        return $this->non_arreca;
    }

    public function getAdottaMisure(): bool {
        return $this->adotta_misure;
    }

    public function getSpecificaDocumentazione(): bool {
        return $this->specifica_documentazione;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setRichiesta(?Richiesta $richiesta): void {
        $this->richiesta = $richiesta;
    }

    public function setNonArreca(bool $non_arreca): void {
        $this->non_arreca = $non_arreca;
    }

    public function setAdottaMisure(bool $adotta_misure): void {
        $this->adotta_misure = $adotta_misure;
    }

    public function setSpecificaDocumentazione(bool $specifica_documentazione): void {
        $this->specifica_documentazione = $specifica_documentazione;
    }

    public function hasAdottaMisure() {
        return $this->adotta_misure == true;
    }
    
    public function hasSpecificaDocumentazione() {
        return $this->specifica_documentazione == true;
    }
    
    public function getDescrizioneAdottaMisure() {
        return $this->descrizione_adotta_misure;
    }

    public function getDescrizioneSpecificaDocumentazione() {
        return $this->descrizione_specifica_documentazione;
    }

    public function setDescrizioneAdottaMisure($descrizione_adotta_misure): void {
        $this->descrizione_adotta_misure = $descrizione_adotta_misure;
    }

    public function setDescrizioneSpecificaDocumentazione($descrizione_specifica_documentazione): void {
        $this->descrizione_specifica_documentazione = $descrizione_specifica_documentazione;
    }



}
