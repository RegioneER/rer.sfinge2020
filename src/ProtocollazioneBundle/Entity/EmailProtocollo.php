<?php

namespace ProtocollazioneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * EmailProtocollo
 *
 * @ORM\Entity(repositoryClass="ProtocollazioneBundle\Repository\EmailProtocolloRepository")
 * @ORM\Table(name="email_protocollo")
 */
class EmailProtocollo extends EntityLoggabileCancellabile {
	
	/*
	 * Lifecycle EmailProtocollo
	 * EmailProtocollo è un oggetto della coda di invio
	 * Per ogni protocollazione in uscita per cui deve essere in inviata una email viene creata un'istanza con stato DA_INVIARE.
	 * La coda di invio (on success) farà evolvere lo stato in INVIATA.
	 * In base alle ricevute pervenute l'istanza evolverà in uno dei due stati finali CONSEGNATA o NON_CONSEGNATA
         * 29/12/2020
         * Aggiungo stati non inviabile e nessuna notifica per bloccare invio e ricerca ricevute per pec vecchie
	 */
	const DA_INVIARE = 'DA_INVIARE';
	const INVIATA = 'INVIATA';	
	const CONSEGNATA = 'CONSEGNATA';
	const NON_CONSEGNATA = 'NON_CONSEGNATA';
        const NON_INVIABILE = 'NON_INVIABILE';
        const NESSUNA_NOTIFICA = 'NESSUNA_NOTIFICA';

	/**
	 * @var int
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;
	
	/**
	 * CSV
	 * @ORM\Column(name="ricevute_pervenute", type="array", nullable=true)
	 */
	private $ricevutePervenute;

	/**
	 * @ORM\Column(name="stato", type="string", length=32, nullable=false)
	 */
	private $stato;
	
	/**
	 * @ORM\Column(name="destinatario", type="string", length=255, nullable=true)
	 */
	private $destinatario;
	
	/**
	 * generato/i da egrammata
	 * @ORM\Column(name="id_email", type="array", nullable=true)
	 */
	private $idEmail;
	

	/**
	 * @ORM\ManyToOne(targetEntity="ProtocollazioneBundle\Entity\RichiestaProtocollo", inversedBy="emailProtocollo")
	 * @ORM\JoinColumn(name="richiesta_protocollo_id", referencedColumnName="id", nullable=false)
	 */
	protected $richiestaProtocollo;
	
	/**
	 * @ORM\Column(name="data_invio", type="datetime", nullable=true)
	 * @var \DateTime|null
	 */
	protected $dataInvio;
	
	function __construct() {
		$this->ricevutePervenute = array();
		$this->idEmail = array();
	}

	function getId() {
		return $this->id;
	}

	function getRicevutePervenute() {
		return $this->ricevutePervenute;
	}

	function getStato() {
		return $this->stato;
	}

	function getDestinatario() {
		return $this->destinatario;
	}

	function getIdEmail() {
		return $this->idEmail;
	}

	function getRichiestaProtocollo() {
		return $this->richiestaProtocollo;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setRicevutePervenute($ricevutePervenute) {
		$this->ricevutePervenute = $ricevutePervenute;
	}

	function setStato($stato) {
		$this->stato = $stato;
	}

	function setDestinatario($destinatario) {
		$this->destinatario = $destinatario;
	}

	function setIdEmail($idEmail) {
		$this->idEmail = $idEmail;
	}

	function setRichiestaProtocollo($richiestaProtocollo) {
		$this->richiestaProtocollo = $richiestaProtocollo;
	}
	
	function getDataInvio(): ?\DateTime {
		return $this->dataInvio;
	}

	function setDataInvio(?\DateTime $dataInvio) {
		$this->dataInvio = $dataInvio;
	}

	public function getDataConsegna() {
		if($this->stato != 'CONSEGNATA'){
			return '-';
		}
		foreach ($this->ricevutePervenute as $el) {
			if ($el[0] == 'avvenuta_consegna'){
				$part = explode(' ', $el[1]);
				return $part[0];
			}
		}
		return '-';		
	}
	
	public function getStatoLeggibile() {
		switch ($this->stato) {
			case self::CONSEGNATA:
				return 'Consegnata';
			case self::DA_INVIARE:
				return 'Da inviare';
			case self::INVIATA:
				return 'Inviata';
			case self::NON_CONSEGNATA:
				return 'Non consegnata';
                        case self::NON_INVIABILE:
				return 'Impossibile inviare la pec';
                        case self::NESSUNA_NOTIFICA:
				return 'Nessuna notifica ricezione';
			default :'ND';
		} 
	}
	
}
