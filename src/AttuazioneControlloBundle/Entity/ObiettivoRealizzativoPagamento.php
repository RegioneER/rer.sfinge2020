<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="obiettivi_realizzativi_pagamenti")
 */
class ObiettivoRealizzativoPagamento extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\ObiettivoRealizzativo")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $obiettivo_realizzativo;

	/**
	 * @ORM\ManyToOne(targetEntity="AttuazioneControlloBundle\Entity\Pagamento", inversedBy="obiettivi_realizzativi")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $pagamento;
    
	/**
	 * @ORM\OneToOne(targetEntity="AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento" , cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $istruttoria_oggetto_pagamento; 

	/**
	 * @ORM\Column(name="mese_avvio_effettivo", type="integer", nullable=true)
	 */
	protected $mese_avvio_effettivo;

	/**
	 * @ORM\Column(name="mese_fine_effettivo", type="integer", nullable=true)
	 */
	protected $mese_fine_effettivo;

	/**
	 * @ORM\Column(name="obiettivi_previsti", type="text", nullable=true)
	 */
	protected $obiettivi_previsti;

	/**
	 * @ORM\Column(name="risultati_attesi", type="text", nullable=true)
	 */
	protected $risultati_attesi;

	/**
	 * @ORM\Column(name="attivita_svolte", type="text", nullable=true)
	 */
	protected $attivita_svolte;

	/**
	 * @ORM\Column(name="attivita_da_realizzare", type="text", nullable=true)
	 */
	protected $attivita_da_realizzare;	
    
    protected $titolo_or;
    protected $percentuale_ri;
    protected $percentuale_ss;
    protected $mese_avvio_previsto;
    protected $mese_fine_previsto;
    protected $attivita_previste;

	public function getCodiceOr() {
		return $this->obiettivo_realizzativo->getCodiceOr();
	}

	public function getTitoloOr() {
		return $this->obiettivo_realizzativo->getTitoloOr();
	}

	public function getMeseAvvioPrevisto() {
		return $this->obiettivo_realizzativo->getMeseAvvioPrevisto();
	}

	public function getMeseFinePrevisto() {
		return $this->obiettivo_realizzativo->getMeseFinePrevisto();
	}  
    
	public function getPercentualeRi() {
		return $this->obiettivo_realizzativo->getPercentualeRi();
	}

	public function getPercentualeSs() {
		return $this->obiettivo_realizzativo->getPercentualeSs();
	}    
    
	public function getAttivitaPreviste() {
		return $this->obiettivo_realizzativo->getAttivitaPreviste();
	}    
    
    public function getId() {
        return $this->id;
    }

    public function getObiettivoRealizzativo() {
        return $this->obiettivo_realizzativo;
    }

    public function getPagamento() {
        return $this->pagamento;
    }

    public function getIstruttoriaOggettoPagamento() {
        return $this->istruttoria_oggetto_pagamento;
    }

    public function getMeseAvvioEffettivo() {
        return $this->mese_avvio_effettivo;
    }

    public function getMeseFineEffettivo() {
        return $this->mese_fine_effettivo;
    }

    public function getObiettiviPrevisti() {
        return $this->obiettivi_previsti;
    }

    public function getRisultatiAttesi() {
        return $this->risultati_attesi;
    }

    public function getAttivitaSvolte() {
        return $this->attivita_svolte;
    }

    public function getAttivitaDaRealizzare() {
        return $this->attivita_da_realizzare;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setObiettivoRealizzativo($obiettivo_realizzativo) {
        $this->obiettivo_realizzativo = $obiettivo_realizzativo;
    }

    public function setPagamento($pagamento) {
        $this->pagamento = $pagamento;
    }

    public function setIstruttoriaOggettoPagamento($istruttoria_oggetto_pagamento) {
        $this->istruttoria_oggetto_pagamento = $istruttoria_oggetto_pagamento;
    }

    public function setMeseAvvioEffettivo($mese_avvio_effettivo) {
        $this->mese_avvio_effettivo = $mese_avvio_effettivo;
    }

    public function setMeseFineEffettivo($mese_fine_effettivo) {
        $this->mese_fine_effettivo = $mese_fine_effettivo;
    }

    public function setObiettiviPrevisti($obiettivi_previsti) {
        $this->obiettivi_previsti = $obiettivi_previsti;
    }

    public function setRisultatiAttesi($risultati_attesi) {
        $this->risultati_attesi = $risultati_attesi;
    }

    public function setAttivitaSvolte($attivita_svolte) {
        $this->attivita_svolte = $attivita_svolte;
    }

    public function setAttivitaDaRealizzare($attivita_da_realizzare) {
        $this->attivita_da_realizzare = $attivita_da_realizzare;
    }

    
}
