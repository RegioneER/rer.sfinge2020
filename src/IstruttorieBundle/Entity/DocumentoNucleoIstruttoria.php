<?php

namespace IstruttorieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;

/**
 * 
 *
 * @ORM\Table(name="documento_nucleo_istruttorie", indexes={@ORM\Index(name="search_istruttoria", columns={"nucleo_instruttoria_id"})})
 * @ORM\Entity
 */
 class DocumentoNucleoIstruttoria extends EntityLoggabileCancellabile{

     /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")     
     */
    private $id;
	
	/**
     * @ORM\ManyToOne(targetEntity="IstruttorieBundle\Entity\NucleoIstruttoria", inversedBy="documentiNucleoIstruttoria", cascade={"persist"})
     * @ORM\JoinColumn(name="nucleo_instruttoria_id", referencedColumnName="id", nullable=false)
     */
    private $nucleoIstruttoria;

    /**
     * @ORM\OneToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile",cascade={"persist"})
     * @ORM\JoinColumn(name="documento_file_id", referencedColumnName="id", nullable=false)
     */
    private $documentoFile;

    function getId(){
        return $this->id;
    }

    function setId($value){
        $this->id = $value;
    }

    function getNucleoIstruttoria(){
        return $this->nucleoIstruttoria;
    }

    function setNucleoIstruttoria( $value ){
        $this->nucleoIstruttoria = $value;
    }

    function getDocumentoFile(){
        return $this->documentoFile;
    }

    function setDocumentoFile(  $value ){
        $this->documentoFile = $value;
    }
    
 }