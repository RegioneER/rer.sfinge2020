<?php

namespace RichiesteBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use SoggettoBundle\Entity\IncaricoPersona;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="RichiesteBundle\Entity\OggettoRichiestaRepository")
 * @ORM\Table(name="oggetti_richiesta",
 * 	indexes={
 *      @ORM\Index(name="idx_richiesta_id", columns={"richiesta_id"}),
 * 		@ORM\Index(name="idx_istanza_fascicolo_id", columns={"istanza_fascicolo_id"})
 *  })
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="tipo", type="string")
 * @ORM\DiscriminatorMap({"OGGETTO_RICHIESTA"="RichiesteBundle\Entity\OggettoRichiesta"
 * })
 */
class OggettoRichiesta extends EntityLoggabileCancellabile {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint", name="id")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="oggetti_richiesta")
	 * @ORM\JoinColumn(name="richiesta_id", referencedColumnName="id", nullable=false)
	 */
	protected $richiesta;

	/**
	 * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\Proponente", mappedBy="oggetto_richiesta")
	 */
	protected $proponenti;

	/**
	 * @ORM\OneToOne(targetEntity="FascicoloBundle\Entity\IstanzaFascicolo", inversedBy="oggetto_richiesta", cascade={"persist"})
	 * @ORM\JoinColumn(name="istanza_fascicolo_id", referencedColumnName="id")
	 */
	protected $istanza_fascicolo;

	/**
	 * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\DocumentoRichiesta", mappedBy="richiesta")
	 */
	protected $documenti_richiesta;

	/**
	 * @var string $descrizione
	 *
	 * @ORM\Column(name="descrizione", type="string", length=1024)
	 * @Assert\NotNull()
	 */
	protected $descrizione;
	
	/**
	 * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\Intervento", mappedBy="oggetto_richiesta")
	 */
	protected $interventi;

	/**
	 * @param Richiesta $richiesta=null
	 */
	public function __construct(Richiesta $richiesta = null) {
		$this->documenti_richiesta = new ArrayCollection();
		$this->interventi = new ArrayCollection();
		$this->richiesta = $richiesta;
	}

	function getId() {
		return $this->id;
	}

	/**
	 * @return Richiesta
	 */
	function getRichiesta() {
		return $this->richiesta;
	}

	/**
	 * @return \FascicoloBundle\Entity\IstanzaFascicolo 
	 */
	function getIstanzaFascicolo() {
		return $this->istanza_fascicolo;
	}

	function setId($id) {
		$this->id = $id;
	}

	function setRichiesta($richiesta) {
		$this->richiesta = $richiesta;
	}

	function setIstanzaFascicolo($istanza_fascicolo) {
		$this->istanza_fascicolo = $istanza_fascicolo;
	}

	function getDocumentiRichiesta() {
		return $this->documenti_richiesta;
	}

	function setDocumentiRichiesta($documenti_richiesta) {
		$this->documenti_richiesta = $documenti_richiesta;
	}

	public function addDocumentoRichiesta($documentoRichiesta) {
		$this->documenti_richiesta[] = $documentoRichiesta;
		$documentoRichiesta->setOggettoRichiesta($this);
	}

	function getProponenti() {
		return $this->proponenti;
	}

	function setProponenti($proponenti) {
		$this->proponenti = $proponenti;
	}

	public function addProponente($proponente) {
		$this->proponenti[] = $proponente;
		$proponente->setOggettoRichiesta($this);
	}

	/**
	 * @return string
	 */
	public function getDescrizione() {
		return $this->descrizione;
	}

	/**
	 * @param string $descrizione
	 */
	public function setDescrizione($descrizione) {
		$this->descrizione = $descrizione;
	}

	public function getSoggetto() {
		foreach ($this->proponenti as $proponente) {
			if ($proponente->getMandatario()) {
				return $proponente->getSoggetto();
			}
		}
	}
	
	function getInterventi() {
		return $this->interventi;
	}

	function setInterventi($interventi) {
		$this->interventi = $interventi;
	}


    /**
     * Add proponenti
     *
     * @param \RichiesteBundle\Entity\Proponente $proponenti
     * @return OggettoRichiesta
     */
    public function addProponenti(\RichiesteBundle\Entity\Proponente $proponenti)
    {
        $this->proponenti[] = $proponenti;

        return $this;
    }

    /**
     * Remove proponenti
     *
     * @param \RichiesteBundle\Entity\Proponente $proponenti
     */
    public function removeProponenti(\RichiesteBundle\Entity\Proponente $proponenti)
    {
        $this->proponenti->removeElement($proponenti);
    }

    /**
     * Add documenti_richiesta
     *
     * @param \RichiesteBundle\Entity\DocumentoRichiesta $documentiRichiesta
     * @return OggettoRichiesta
     */
    public function addDocumentiRichiestum(\RichiesteBundle\Entity\DocumentoRichiesta $documentiRichiesta)
    {
        $this->documenti_richiesta[] = $documentiRichiesta;

        return $this;
    }

    /**
     * Remove documenti_richiesta
     *
     * @param \RichiesteBundle\Entity\DocumentoRichiesta $documentiRichiesta
     */
    public function removeDocumentiRichiestum(\RichiesteBundle\Entity\DocumentoRichiesta $documentiRichiesta)
    {
        $this->documenti_richiesta->removeElement($documentiRichiesta);
    }

    /**
     * Add interventi
     *
     * @param \RichiesteBundle\Entity\Intervento $interventi
     * @return OggettoRichiesta
     */
    public function addInterventi(\RichiesteBundle\Entity\Intervento $interventi)
    {
        $this->interventi[] = $interventi;

        return $this;
    }

    /**
     * Remove interventi
     *
     * @param \RichiesteBundle\Entity\Intervento $interventi
     */
    public function removeInterventi(\RichiesteBundle\Entity\Intervento $interventi)
    {
        $this->interventi->removeElement($interventi);
    }

    /**
     * @param string $codiceFiscale
     * @return bool
     */
    public function isLegaleRappresentanteODelegato(string $codiceFiscale)
    {
        $incarichiAutorizzati = ['LR', 'DELEGATO'];
        /** @var Richiesta $richiesta */
        $richiesta = $this->richiesta;

        $mandatario = $richiesta->getMandatario()->getSoggetto();
        /** @var IncaricoPersona[] $incarichi */
        $incarichi = $mandatario->getIncarichiPersone();
        foreach ($incarichi as $incarico) {
            if ($incarico->getStato()->getCodice() == "ATTIVO" && in_array($incarico->getTipoIncarico()->getCodice(), $incarichiAutorizzati)
                && $incarico->getIncaricato()->getCodiceFiscale() == $codiceFiscale) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $codiceFiscale
     * @return bool
     */
    public function isLegaleRappresentante(string $codiceFiscale)
    {
        $incarichiAutorizzati = ['LR'];
        /** @var Richiesta $richiesta */
        $richiesta = $this->richiesta;

        $mandatario = $richiesta->getMandatario()->getSoggetto();
        /** @var IncaricoPersona[] $incarichi */
        $incarichi = $mandatario->getIncarichiPersone();
        foreach ($incarichi as $incarico) {
            if ($incarico->getStato()->getCodice() == "ATTIVO" && in_array($incarico->getTipoIncarico()->getCodice(), $incarichiAutorizzati)
                && $incarico->getIncaricato()->getCodiceFiscale() == $codiceFiscale) {
                return true;
            }
        }
        return false;
    }
}
