<?php

namespace CertificazioniBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use BaseBundle\Annotation as Sfinge;
use CertificazioniBundle\Entity\StatoChiusura;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="CertificazioniBundle\Entity\CertificazioneChiusuraRepository")
 * @ORM\Table(name="certificazioni_chiusure")
 */
class CertificazioneChiusura extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="CertificazioniBundle\Entity\StatoChiusuraCertificazione")
	 * @ORM\JoinColumn(nullable=true)
	 * @Sfinge\CampoStato()
	 */
	protected $stato;

	/**
	 * @ORM\OneToMany(targetEntity="CertificazioniBundle\Entity\Certificazione", mappedBy="chiusura", cascade={"persist"})
	 */
	protected $certificazioni;
    
    /**
	 * @ORM\OneToMany(targetEntity="CertificazioniBundle\Entity\CompensazionePagamento", mappedBy="chiusura", cascade={"persist"})
	 */
	protected $compensazioni;

	/**
	 * @ORM\OneToMany(targetEntity="CertificazioniBundle\Entity\DocumentoCertificazioneChiusura", mappedBy="chiusura", cascade={"persist"})
	 */
	protected $documenti_chiusura;
	
	/**
	 * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\Revoche\Revoca", mappedBy="chiusura", cascade={"persist"})
	 */
	protected $revoche_invio_conti;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $nota_iter;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $osservazioni_8_1;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $osservazioni_8_2;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $osservazioni_8_3;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $osservazioni_8_4;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $osservazioni_8_5;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $osservazioni_8_6;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $osservazioni_8_7;

	public function __construct() {
		
	}

	public function getId() {
		return $this->id;
	}

	public function getStato() {
		return $this->stato;
	}

	public function getCertificazioni() {
		return $this->certificazioni;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function setStato($stato) {
		$this->stato = $stato;
	}

	public function setCertificazioni($certificazioni) {
		$this->certificazioni = $certificazioni;
	}

	public function addCertificazioni(\CertificazioniBundle\Entity\Certificazione $certificazione) {
		$this->certificazioni[] = $certificazione;

		return $this;
	}

	public function removeCertificazioni(\CertificazioniBundle\Entity\Certificazione $certificazione) {
		$this->certificazioni->removeElement($certificazione);
	}

	public function getDocumentiChiusura() {
		return $this->documenti_chiusura;
	}

	public function setDocumentiChiusura($documenti_chiusura) {
		$this->documenti_chiusura = $documenti_chiusura;
	}

	public function getIntervalliAnni() {
		$array_anni = array();

		if (count($this->certificazioni) == 0) {
			return '-';
		}

		foreach ($this->certificazioni as $certificazione) {
			$array_anni[] = $certificazione->getAnno();
		}

		$anno_min = min($array_anni);
		$anno_max = max($array_anni);

		if ($anno_min == $anno_max) {
			return $anno_max;
		} else {
			return $anno_min . '-' . $anno_max;
		}
	}
	
	public function maxAnnoContabile() {
		if (count($this->certificazioni) == 0) {
			return '-';
		}

		foreach ($this->certificazioni as $certificazione) {
			$array_anni[] = $certificazione->getAnno();
		}

		return max($array_anni);
	}

	public function getNumeriCertificazioni() {
		$array_numeri = array();

		if (count($this->certificazioni) == 0) {
			return '-';
		}

		foreach ($this->certificazioni as $certificazione) {
			$array_numeri[] = $certificazione->getAnnoContabile(). '.' .$certificazione->getNumero();
		}

		return $array_numeri;
	}

	public function isInviabile() {
		return $this->getStato()->getCodice() == StatoChiusuraCertificazione::CHI_VALIDATA;
	}
	
	public function isApprovabile() {
		return $this->getStato()->getCodice() == StatoChiusuraCertificazione::CHI_INVIATA;
	}
	
	public function isChiusa() {
		return $this->getStato()->getCodice() == StatoChiusuraCertificazione::CHI_INVIATA || $this->getStato()->getCodice() == StatoChiusuraCertificazione::CHI_APPROVATA;
	}

	public function getRevocheInvioConti() {
		return $this->revoche_invio_conti;
	}

	public function setRevocheInvioConti($revoche_invio_conti) {
		$this->revoche_invio_conti = $revoche_invio_conti;
	}
	
	public function getOsservazioni81() {
		return $this->osservazioni_8_1;
	}

	public function getOsservazioni82() {
		return $this->osservazioni_8_2;
	}

	public function getOsservazioni83() {
		return $this->osservazioni_8_3;
	}

	public function getOsservazioni84() {
		return $this->osservazioni_8_4;
	}

	public function getOsservazioni85() {
		return $this->osservazioni_8_5;
	}

	public function getOsservazioni86() {
		return $this->osservazioni_8_6;
	}

	public function getOsservazioni87() {
		return $this->osservazioni_8_7;
	}

	public function setOsservazioni81($osservazioni_8_1) {
		$this->osservazioni_8_1 = $osservazioni_8_1;
	}

	public function setOsservazioni82($osservazioni_8_2) {
		$this->osservazioni_8_2 = $osservazioni_8_2;
	}

	public function setOsservazioni83($osservazioni_8_3) {
		$this->osservazioni_8_3 = $osservazioni_8_3;
	}

	public function setOsservazioni84($osservazioni_8_4) {
		$this->osservazioni_8_4 = $osservazioni_8_4;
	}

	public function setOsservazioni85($osservazioni_8_5) {
		$this->osservazioni_8_5 = $osservazioni_8_5;
	}

	public function setOsservazioni86($osservazioni_8_6) {
		$this->osservazioni_8_6 = $osservazioni_8_6;
	}

	public function setOsservazioni87($osservazioni_8_7) {
		$this->osservazioni_8_7 = $osservazioni_8_7;
	}
    
    public function getCompensazioni() {
        return $this->compensazioni;
    }

    public function setCompensazioni($compensazioni) {
        $this->compensazioni = $compensazioni;
    }

}
