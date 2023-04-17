<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityTipo;
use Doctrine\ORM\Mapping as ORM;
use MonitoraggioBundle\Entity\TC39CausalePagamento;
use AttuazioneControlloBundle\Entity\ModalitaPagamentoProcedura;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 *
 * @ORM\Entity()
 * @ORM\Table(name="modalita_pagamento")
 */
class ModalitaPagamento extends EntityTipo
{
	const ANTICIPO = "ANTICIPO";
	const PRIMO_SAL = "PRIMO_SAL";
    const SAL = "SAL";
    const SALDO_FINALE = "SALDO_FINALE";
    const UNICA_SOLUZIONE = "UNICA_SOLUZIONE";
	const TRASFERIMENTO = "TRASFERIMENTO";
	const SECONDO_SAL = "SECONDO_SAL";
	const TERZO_SAL = "TERZO_SAL";
	const QUARTO_SAL = "QUARTO_SAL";
	const QUINTO_SAL = "QUINTO_SAL";
	const SESTO_SAL = "SESTO_SAL";
	const SETTIMO_SAL = "SETTIMO_SAL";
	const OTTAVO_SAL = "OTTAVO_SAL";
	const NONO_SAL = "NONO_SAL";
	const DECIMO_SAL = "DECIMO_SAL";

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $richiede_giustificativi;   
    
	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	protected $unico;    
	
	/**
     * @ORM\Column(name="ordine_cronologico", type="integer", nullable=false)
     */
	protected $ordine_cronologico;
	
	/**
	 * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC39CausalePagamento")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $causale;

	/**
	 * @ORM\OneToMany(targetEntity="ModalitaPagamentoProcedura", mappedBy="modalita_pagamento")
	 */
	protected $modalita_procedure;

    public function __toString() {
        return $this->getDescrizioneBreve();
    }
    
    public function getRichiedeGiustificativi() {
        return $this->richiede_giustificativi;
    }

    public function setRichiedeGiustificativi($richiede_giustificativi) {
        $this->richiede_giustificativi = $richiede_giustificativi;
        return $this;
    }
    
    public function getUnico() {
        return $this->unico;
    }

    public function setUnico($unico): self {
        $this->unico = $unico;
        return $this;
    }
    
    public function getDescrizioneBreve() {
        if ($this->codice == "SAL") {
            return $this->codice;
        }
        
        return $this->getDescrizione();
    }

	public function isTrasferimento(): bool {
		return $this->codice == self::TRASFERIMENTO;
	}
	
	public function isSAL(): bool {
		return $this->codice == self::SAL;
	}
	
	public function isPrimoSAL(): bool {
		return $this->codice == self::PRIMO_SAL;
	}
	
	public function isSaldo(): bool {
		return $this->codice == self::SALDO_FINALE;
	}
	
	public function isUnicaSoluzione(){
		return $this->codice == self::UNICA_SOLUZIONE;
	}
	
	public function isAnticipo(): bool {
		return $this->codice == self::ANTICIPO;
	}
	
	public function isSecondoSAL(): bool {
		return $this->codice == self::SECONDO_SAL;
	}	
	
	public function isTerzoSAL(): bool {
		return $this->codice == self::TERZO_SAL;
	}	
	
	public function isQuartoSAL(): bool {
		return $this->codice == self::QUARTO_SAL;
	}	
	
	public function isQuintoSAL(): bool {
		return $this->codice == self::QUINTO_SAL;
	}

    public function isSestoSAL(): bool {
        return $this->codice == self::SESTO_SAL;
    }

    public function isSettimoSAL(): bool {
        return $this->codice == self::SETTIMO_SAL;
    }

    public function isOttavoSAL(): bool {
        return $this->codice == self::OTTAVO_SAL;
    }

    public function isNonoSAL(): bool {
        return $this->codice == self::NONO_SAL;
    }

    public function isDecimoSAL(): bool {
        return $this->codice == self::DECIMO_SAL;
    }

    // aggiornare con eventuali nuove modalità intermedie ..tipo terzo sal, quarto sal, 120-simo sal   :|
	public function isPagamentoIntermedio(): bool {
		return  $this->isSAL() || $this->isPrimoSAL() || $this->isSecondoSAL() || $this->isTerzoSAL() || $this->isQuartoSAL() || $this->isQuintoSAL() || $this->isSestoSAL() || $this->isSettimoSAL() || $this->isOttavoSAL() || $this->isNonoSAL() || $this->isDecimoSAL();
	}
	
	public function isPagamentoFinale(): bool {
		return  $this->isSaldo() || $this->isUnicaSoluzione();
	}
	
	public function getOrdineCronologico() {
		return $this->ordine_cronologico;
	}

	public function setOrdineCronologico($ordine_cronologico) {
		$this->ordine_cronologico = $ordine_cronologico;
	}

    public function setCausale(TC39CausalePagamento $causale = null): self
    {
        $this->causale = $causale;

        return $this;
    }


    public function getCausale(): ?TC39CausalePagamento
    {
        return $this->causale;
    }

    public function __construct()
    {
        $this->modalita_procedure = new ArrayCollection();
    }


    public function addModalitaProcedure(ModalitaPagamentoProcedura $modalitaProcedure): self
    {
        $this->modalita_procedure[] = $modalitaProcedure;

        return $this;
    }


    public function removeModalitaProcedure(ModalitaPagamentoProcedura $modalitaProcedure): void
    {
        $this->modalita_procedure->removeElement($modalitaProcedure);
    }

    /**
     * Get modalitaProcedure
     *
     * @return Collection|ModalitaPagamentoProcedura[]
     */
    public function getModalitaProcedure(): Collection
    {
        return $this->modalita_procedure;
    }
}
