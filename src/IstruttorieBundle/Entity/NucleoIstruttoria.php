<?php

namespace IstruttorieBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Symfony\Component\Validator\Constraints as Assert;



/**
 * @ORM\Table(name="nucleo_istruttorie", indexes={@ORM\Index(name="search_istruttoria", columns={"istruttoria_richiesta_id"})})
* @ORM\Entity
*/
class NucleoIstruttoria extends EntityLoggabileCancellabile{

    /**
     * @ORM\Column(type="integer", name="id")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
    * @ORM\OneToOne(targetEntity="\IstruttorieBundle\Entity\IstruttoriaRichiesta", inversedBy="nucleoIstruttoria")
    * @ORM\JoinColumn(name="istruttoria_richiesta_id", referencedColumnName="id", nullable=false)   
    */
    protected $istruttoriaRichiesta;

    /**
    * @ORM\Column(type="date", name="data_nucleo", nullable=true)
    * @Assert\Date()
    * @Assert\NotNull()
    */
    protected $dataNucleo;

    /**
    * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\DocumentoNucleoIstruttoria", mappedBy="nucleoIstruttoria", cascade={"persist"})  
    */
    protected $documentiNucleoIstruttoria;


    function __construct() {
		$this->documentiNucleoIstruttoria = new \Doctrine\Common\Collections\ArrayCollection();
    }

    function getId(){
        return $this->id;
    }

    function setId( $value ){
        $this->id = $value;
    }

    function getIstruttoriaRichiesta(){
        return $this->istruttoriaRichiesta;
    }

    function setIstruttoriaRichiesta( $value ){
        $this->istruttoriaRichiesta = $value;
    }

    function getDataNucleo(){
        return $this->dataNucleo;
    }

    function setDataNucleo( $value ){
        $this->dataNucleo = $value;
    }

    function getDocumentiNucleoIstruttoria(){
        return $this->documentiNucleoIstruttoria;
    }

    function setDocumentiNucleoIstruttoria($value){
        $this->documentiNucleoIstruttoria = $value;
    }
    
    function addDocumentiNucleoIstruttoria( DocumentoNucleoIstruttoria $value ){
        $this->documentiNucleoIstruttoria->add( $value );
    }
}
    