<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\AttoLiquidazioneRepository")
 * @ORM\Table(name="atti_liquidazione")
 */
class AttoLiquidazione extends EntityLoggabileCancellabile {
    
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\NotBlank
     */
    protected $numero;
    
    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @Assert\NotBlank
     */
    protected $data;    

    /**
     * @ORM\Column(type="text", nullable=false)
     * @Assert\NotBlank
     */
    protected $descrizione;   

    /**
     * @ORM\ManyToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile",cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     * @Assert\Valid
     * @Assert\NotNull
     */
    protected $documento;

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Asse")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank
    */
    protected $asse;	

    function getId() {
        return $this->id;
    }

    function getNumero() {
        return $this->numero;
    }

    function getData() {
        return $this->data;
    }

    function getDescrizione() {
        return $this->descrizione;
    }

    function getDocumento() {
        return $this->documento;
    }

    function getAsse() {
        return $this->asse;
    }

    function setId($id) {
        $this->id = $id;
        return $this;
    }

    function setNumero($numero) {
        $this->numero = $numero;
        return $this;
    }

    function setData($data) {
        $this->data = $data;
        return $this;
    }

    function setDescrizione($descrizione) {
        $this->descrizione = $descrizione;
        return $this;
    }

    function setDocumento($documento) {
        $this->documento = $documento;
        return $this;
    }

    function setAsse($asse) {
        $this->asse = $asse;
        return $this;
    }

    public function __toString() {
        return $this->numero." - ".$this->data->format("d/m/Y");
    }
}
