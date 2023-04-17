<?php

namespace CertificazioniBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use BaseBundle\Entity\EntityLoggabileCancellabile;
use BaseBundle\Annotation as Sfinge;
use CertificazioniBundle\Entity\StatoCertificazione;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="CertificazioniBundle\Entity\CertificazioneRepository")
 * @ORM\Table(name="certificazioni")
 */
class Certificazione extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="CertificazioniBundle\Entity\StatoCertificazione")
	 * @ORM\JoinColumn(nullable=true)
	 * @Sfinge\CampoStato()
	 */
	protected $stato;

	/**
	 * @ORM\Column(type="string", nullable=false)
	 */
	protected $numero;

	/**
	 * @ORM\Column(type="integer", nullable=false)
	 */
	protected $anno;

	/**
	 * @ORM\Column(type="integer", nullable=false)
	 */
	protected $anno_contabile;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $data_proposta_adg;

	/**
     * @ORM\OneToMany(targetEntity="CertificazioniBundle\Entity\CertificazioneAsse", mappedBy="certificazione", cascade={"persist"})
     */
    protected $certificazioni_assi;  
	
     /**
     * @ORM\ManyToOne(targetEntity="TipologiaCertificazione")
     * @ORM\JoinColumn(name="tipologia_certificazione_id", referencedColumnName="id")
     * @Assert\NotNull(message = "Selezionare una tipologia")
     */
    protected $tipologia_certificazione;

    /**
     * @ORM\OneToMany(targetEntity="CertificazioniBundle\Entity\DocumentoCertificazione", mappedBy="certificazione", cascade={"persist"})
     */
    protected $documenti_certificazione;      
    
    /**
     * Ad ogni certificazione corrisponde una TC41(che ci viene fornita da IGRUE)
     * @ORM\OneToOne(targetEntity="MonitoraggioBundle\Entity\TC41DomandaPagamento")
     * @ORM\JoinColumn(name="domanda_pagamento_id", referencedColumnName="id")
     */
    protected $domanda_pagamento;
    
    /**
     * @Assert\Valid
     */
    protected $pagamenti_estesi;  
	
	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $data_approvazione;

	/**
	 * @ORM\OneToMany(targetEntity="CertificazioniBundle\Entity\CertificazionePagamento", mappedBy="certificazione", cascade={"persist"})
	 */
	protected $pagamenti;

	/**
	 * @ORM\ManyToOne(targetEntity="CertificazioniBundle\Entity\CertificazioneChiusura", inversedBy="certificazioni")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $chiusura;

	public function __construct() {
		
	}

	public function getId() {
		return $this->id;
	}

	public function getStato() {
		return $this->stato;
	}

	public function getNumero() {
		return $this->numero;
	}

	public function getAnno() {
		return $this->anno;
	}

	public function getDataPropostaAdg() {
		return $this->data_proposta_adg;
	}

	public function getPagamenti() {
		return $this->pagamenti;
	}

	public function setId($id) {
		$this->id = $id;
		return $this;
	}

	public function setStato($stato) {
		$this->stato = $stato;
		return $this;
	}

	public function setNumero($numero) {
		$this->numero = $numero;
		return $this;
	}

	public function setAnno($anno) {
		$this->anno = $anno;
		return $this;
	}

	public function setDataPropostaAdg($data_proposta_adg) {
		$this->data_proposta_adg = $data_proposta_adg;
		return $this;
	}

	public function setPagamenti($pagamenti) {
		$this->pagamenti = $pagamenti;
		return $this;
	}

	public function getPagamentiEstesi() {
		return $this->pagamenti_estesi;
	}

	public function setPagamentiEstesi($pagamenti_estesi) {
		$this->pagamenti_estesi = $pagamenti_estesi;
		return $this;
	}

	public function isEliminabile() {
		return in_array($this->getStato()->getCodice(), array(StatoCertificazione::CERT_INSERITA));
	}

	public function addPagamentoEsteso($pagamento) {
		$this->pagamenti_estesi[] = $pagamento;
		$pagamento->setCertificazione($this);
	}

	public function addPagamento($pagamento) {
		$this->pagamenti->add($pagamento);
		$pagamento->setCertificazione($this);
	}

	public function isPreValidabile() {
		if ($this->tipologia_certificazione->getCodice() != '2') {
			return $this->getStato()->getCodice() == StatoCertificazione::CERT_INSERITA && count($this->getPagamenti()) > 0;
		} else {
			return $this->getStato()->getCodice() == StatoCertificazione::CERT_INSERITA;
		}
	}

	public function isInviabile() {
		return $this->getStato()->getCodice() == StatoCertificazione::CERT_VALIDATA;
	}

	public function isValidabile() {
		return $this->getStato()->getCodice() == StatoCertificazione::CERT_PREVALIDATA;
	}

	public function isApprovabile() {
		return $this->getStato()->getCodice() == StatoCertificazione::CERT_INVIATA;
	}

	public function isApprovata() {
		return $this->getStato()->getCodice() == StatoCertificazione::CERT_APPROVATA;
	}

	public function hasPagamenti() {
		return count($this->getPagamenti()) > 0;
	}

	public function isCertificazioneChiusuraVuota() {
		return count($this->getPagamenti()) == 0 && $this->tipologia_certificazione->getCodice() == '2';
	}

	/**
	 * Set data_approvazione
	 *
	 * @param \DateTime $dataApprovazione
	 * @return Certificazione
	 */
	public function setDataApprovazione($dataApprovazione) {
		$this->data_approvazione = $dataApprovazione;

		return $this;
	}

	/**
	 * Get data_approvazione
	 *
	 * @return \DateTime 
	 */
	public function getDataApprovazione() {
		return $this->data_approvazione;
	}

	/**
	 * Add pagamenti
	 *
	 * @param \CertificazioniBundle\Entity\CertificazionePagamento $pagamenti
	 * @return Certificazione
	 */
	public function addPagamenti(\CertificazioniBundle\Entity\CertificazionePagamento $pagamenti) {
		$this->pagamenti[] = $pagamenti;

		return $this;
	}

	/**
	 * Remove pagamenti
	 *
	 * @param \CertificazioniBundle\Entity\CertificazionePagamento $pagamenti
	 */
	public function removePagamenti(\CertificazioniBundle\Entity\CertificazionePagamento $pagamenti) {
		$this->pagamenti->removeElement($pagamenti);
	}

	/**
	 * Set tipologia_certificazione
	 *
	 * @param \CertificazioniBundle\Entity\TipologiaCertificazione $tipologiaCertificazione
	 * @return Certificazione
	 */
	public function setTipologiaCertificazione(\CertificazioniBundle\Entity\TipologiaCertificazione $tipologiaCertificazione = null) {
		$this->tipologia_certificazione = $tipologiaCertificazione;

		return $this;
	}

	/**
	 * Get tipologia_certificazione
	 *
	 * @return \CertificazioniBundle\Entity\TipologiaCertificazione 
	 */
	public function getTipologiaCertificazione() {
		return $this->tipologia_certificazione;
	}

	public function getDocumentiCertificazione() {
		return $this->documenti_certificazione;
	}

	public function setDocumentiCertificazione($documenti_certificazione) {
		$this->documenti_certificazione = $documenti_certificazione;
		return $this;
	}

	public function getCertificazioniAssi() {
		return $this->certificazioni_assi;
	}

	public function setCertificazioniAssi($certificazioni_assi) {
		$this->certificazioni_assi = $certificazioni_assi;
	}

    public function getImportoProposto()
    {
        $pagamenti = $this->getPagamenti();
        $totale_certificabile = 0;
        foreach ($pagamenti as $key => $pagamento) {
            $totale_certificabile += $pagamento->getImporto();
        }
        return $totale_certificabile;
    }

    public function getImportoTagliAdC()
    {
        $pagamenti = $this->getPagamenti();
        $totale_tagli_adc = 0;
        foreach ($pagamenti as $key => $pagamento) {
            if($pagamento->getImportoTaglio() > 0 && $pagamento->getTipologiaTaglio() == 'AdC'){
                $totale_tagli_adc += $pagamento->getImportoTaglio();
            }
        }
        return $totale_tagli_adc;
    }
    
    public function getImportoTagliAdA()
    {
        $pagamenti = $this->getPagamenti();
        $totale_tagli_adg = 0;
        foreach ($pagamenti as $key => $pagamento) {
            if($pagamento->getImportoTaglio() > 0 && $pagamento->getTipologiaTaglio() == 'AdA'){
                $totale_tagli_adg += $pagamento->getImportoTaglio();
            }
        }
        return $totale_tagli_adg;
    }
    
    /**
     * Get domanda_pagamento
     *
     * @return \MonitoraggioBundle\Entity\TC41DomandaPagamento 
     */
    public function getDomandaPagamento(){
        return $this->domanda_pagamento;
    }
    
    /**
     * Set domanda_pagamento
     *
     * @param \MonitoraggioBundle\Entity\TC41DomandaPagamento $domanda_pagamento
     * @return Certificazione
     */
    public function setDomandaPagamento(\MonitoraggioBundle\Entity\TC41DomandaPagamento $domanda_pagamento = null){
        $this->domanda_pagamento = $domanda_pagamento; 
        return $this;
    }

	/*
	 * Calcola l'importo certificato in questa certificazione, tenendo conto dei tagli.
	 * La funzione ritorna il valore così calcolato anche quando la certificazione non è approvata
	 */
	public function getImportoCertificato() {
		$pagamenti = $this->getPagamenti();
		$totale_certificato = 0;
		if ($this->isApprovata() == false) {
			return $totale_certificato;
		} else {
			foreach ($pagamenti as $certificazione_pagamento) {
				//$totale_certificato += $certificazione_pagamento->getPagamento()->getImportoCertificato();
				$totale_certificato += ($certificazione_pagamento->getImporto() - $certificazione_pagamento->getImportoTaglio());
			}
		}
		return $totale_certificato;
	}

	public function __toString() {
		return $this->numero . " / " . $this->anno;
	}

	public function getChiusura() {
		return $this->chiusura;
	}

	public function setChiusura($chiusura) {
		$this->chiusura = $chiusura;
	}

	public function getAnnoContabile() {
		return $this->anno_contabile;
	}

	public function setAnnoContabile($anno_contabile) {
		$this->anno_contabile = $anno_contabile;
	}

	public function isVisibileAgrea($ruoli) {
		if(in_array('ROLE_CERTIFICATORE_AGREA',$ruoli) && $this->stato->getCodice() != 'CERT_INVIATA' && $this->stato->getCodice() != 'CERT_APPROVATA') {
			return false;
		}
		return true;
	}
	
	public function getAnnoContabileNumero() {
		return $this->anno_contabile.'.'.$this->numero;
	}
}
