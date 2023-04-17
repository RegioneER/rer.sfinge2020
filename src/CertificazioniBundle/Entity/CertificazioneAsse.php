<?php

namespace CertificazioniBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="certificazioni_assi")
 */
class CertificazioneAsse {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="CertificazioniBundle\Entity\Certificazione", inversedBy="certificazioni_assi")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $certificazione;
    
	/**
	 * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Asse")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $asse;
    
	/**
	 * @ORM\Column(type="datetime", nullable=false)
	 */
	protected $data_validazione;
    
	/**
	 * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Utente")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $utente_validazione;    
    
    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $importo_strumenti;

	public function __construct() {
        
	}
    
    public function getId() {
        return $this->id;
    }

    public function getCertificazione() {
        return $this->certificazione;
    }

    public function getAsse() {
        return $this->asse;
    }

    public function getDataValidazione() {
        return $this->data_validazione;
    }

    public function getUtenteValidazione() {
        return $this->utente_validazione;
    }

    public function setAsse($asse) {
        $this->asse = $asse;
        return $this;
    }

    public function setDataValidazione($data_validazione) {
        $this->data_validazione = $data_validazione;
        return $this;
    }

    public function setUtenteValidazione($utente_validazione) {
        $this->utente_validazione = $utente_validazione;
        return $this;
    }

    function setCertificazione($certificazione) {
        $this->certificazione = $certificazione;
        return $this;
    }

    public function getImportoStrumenti() {
        return $this->importo_strumenti;
    }

    public function setImportoStrumenti($importo_strumenti) {
        $this->importo_strumenti = $importo_strumenti;
    }

}
