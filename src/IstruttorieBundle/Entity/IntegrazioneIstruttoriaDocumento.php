<?php

namespace IstruttorieBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="integrazioni_documenti")
 */
class IntegrazioneIstruttoriaDocumento extends EntityLoggabileCancellabile
{

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="DocumentoBundle\Entity\TipologiaDocumento")
     * @ORM\JoinColumn(nullable=false)
     */
    private $tipologia_documento;

    /**
     * @ORM\ManyToOne(targetEntity="IstruttorieBundle\Entity\IntegrazioneIstruttoria", inversedBy="tipologie_documenti")
     * @ORM\JoinColumn(nullable=false)
     */
    private $integrazione;
	
    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Proponente")
     * @ORM\JoinColumn(nullable=true)
     */
    private $proponente;	
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $nota;
    
    protected $selezionato;
    
    function getId()
    {
        return $this->id;
    }

    function setId($id)
    {
        $this->id = $id;
    }
    
    function getTipologiaDocumento()
    {
        return $this->tipologia_documento;
    }

    function getIntegrazione()
    {
        return $this->integrazione;
    }

    function getNota()
    {
        return $this->nota;
    }

    function setTipologiaDocumento($tipologia_documento)
    {
        $this->tipologia_documento = $tipologia_documento;
    }

    function setIntegrazione($integrazione)
    {
        $this->integrazione = $integrazione;
    }

    function setNota($nota)
    {
        $this->nota = $nota;
    }
    
    function getSelezionato()
    {
        return $this->selezionato;
    }

    function setSelezionato($selezionato)
    {
        $this->selezionato = $selezionato;
    }
	
	function getProponente() 
	{
		return $this->proponente;
	}

	function setProponente($proponente) 
	{
		$this->proponente = $proponente;
	}

}
