<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Annotation as Sfinge;
use BaseBundle\Entity\EntityLoggabileCancellabile;

/**
 * @ORM\Entity()
 * @ORM\Table(name="collaborazioni_esterne_imprese")
 */
class CollaborazioneEsternaImpresa extends EntityLoggabileCancellabile {
	
	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	

	/**
	 * @ORM\Column(type="string", nullable=true, length=255)
	 */
	protected $ragione_sociale;


	/**
	 * @ORM\Column(type="string", nullable=true, length=255)
	 */
	protected $mansioni;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $attivita;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $numero_unita;

	/**
     * 
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", inversedBy="collaborazioni_esterne_imprese", cascade={"persist"})
 	 * @ORM\JoinColumn(name="pagamento_id", referencedColumnName="id", nullable=false)
	 */
	protected $pagamento; 
	
	function getId() {
		return $this->id;
	}

	function setId($id) {
		$this->id = $id;
	}
    
    
	function getRagioneSociale() {
		return $this->ragione_sociale;
	}

	function getMansioni() {
		return $this->mansioni;
	}

	function getAttivita() {
		return $this->attivita;
	}

	function getNumeroUnita() {
		return $this->numero_unita;
	}

	
	function getPagamento() {
		return $this->pagamento;
	}

	function setRagioneSociale($ragione_sociale) {
		$this->ragione_sociale = $ragione_sociale;
	}

	function setMansioni($mansioni) {
		$this->mansioni = $mansioni;
	}

	function setAttivita($attivita) {
		$this->attivita = $attivita;
	}

	function setNumeroUnita($numero_unita) {
		$this->numero_unita = $numero_unita;
	}

	function setPagamento($pagamento) {
		$this->pagamento = $pagamento;
	}


}
