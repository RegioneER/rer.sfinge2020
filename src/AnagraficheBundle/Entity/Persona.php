<?php

namespace AnagraficheBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DocumentoBundle\Entity\DocumentoFile;
use SfingeBundle\Entity\Utente;
use SoggettoBundle\Entity\IncaricoPersona;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * 
 * @ORM\Entity(repositoryClass="AnagraficheBundle\Entity\PersonaRepository")
 *
 * @ORM\Table(name="persone",
 *  indexes={
 *      @ORM\Index(name="idx_carta_identita_id", columns={"carta_identita_id"})
 *  })
 * 
 * @Assert\Callback(callback="checkSelezioneStato")
 */
class Persona extends EntityLoggabileCancellabile {

	/**
	 * @var integer $id
	 *
	 *
	 * @ORM\Column(name="id", type="bigint")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @var string $nome
	 *
	 * @ORM\Column(name="nome", type="string", length=50)
	 * 
	 * @Assert\NotBlank()
	 * @Assert\Length(min=2, max=32)
	 */
	protected $nome;

	/**
	 * @var string $cognome
	 *
	 * @ORM\Column(name="cognome", type="string", length=50)
	 * 
	 * @Assert\NotBlank()
	 * @Assert\Length(min=2, max=32)
	 */
	protected $cognome;

	/**
	 * @var \GeoBundle\Entity\GeoStato $nazionalita
	 *
	 * @ORM\ManyToOne(targetEntity="GeoBundle\Entity\GeoStato", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $nazionalita;

	/**
	 * @var \DateTime $data_nascita
	 *
	 * @ORM\Column(name="data_nascita", type="date", nullable=true)
	 * 
	 * @Assert\NotNull(groups={"persona"})
	 */
	protected $data_nascita;

	/**
	 * @var \GeoBundle\Entity\GeoStato $stato_nascita
	 * 
	 * @ORM\ManyToOne(targetEntity="GeoBundle\Entity\GeoStato", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 * 
	 * @Assert\NotNull(groups={"persona"})
	 */
	protected $stato_nascita;

	/**
	 * @var \GeoBundle\Entity\GeoComune $comune_nascita
	 *
	 *
	 * @ORM\ManyToOne(targetEntity="GeoBundle\Entity\GeoComune", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $comune;

	/**
	 *
	 * @ORM\ManyToOne(targetEntity="BaseBundle\Entity\Indirizzo", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true)
	 *
	 *  @Assert\Valid()
	 */
	protected $luogo_residenza;

	/**
	 * @ORM\Column(name="sesso", nullable=true)
	 *
	 */
	protected $sesso;

	/**
	 * @var string $codice_fiscale
	 *
	 * @ORM\Column(name="codice_fiscale", type="string", length=16, nullable=true)
	 * 
	 * @Assert\Length(min=16, max=16, exactMessage="Il campo codice fiscale deve essere lungo {{ limit }} caratteri",groups={"persona"})
	 * @Assert\NotBlank(groups={"persona"})
	 */
	protected $codice_fiscale;

	/**
	 * @ORM\OneToOne(targetEntity="SfingeBundle\Entity\Utente", mappedBy="persona")
	 */
	protected $utente;

	/**
	 * @var string $provinciaEstera
	 *
	 *
	 * @ORM\Column(name="provincia_estera", type="string", length=100, nullable=true)
	 */
	protected $provincia_estera;

	/**
	 * @var string $comuneEstero
	 *
	 *
	 * @ORM\Column(name="comune_estero", type="string", length=100, nullable=true)
	 */
	protected $comune_estero;

	/**
	 * 
	 * @ORM\Column(name="telefono_principale", type="string", length=20)
	 * 
	 * @Assert\NotBlank(message="Specificare il numero di telefono o rimuoverlo (un telefono è obbligatorio)")
	 * @Assert\Length(min = "8", max = "20")
	 * @Assert\Regex(pattern="/^[\d]+$/", message="Il telefono può contenere solo cifre")
	 */
	protected $telefono_principale;

	/**
	 *
	 * @ORM\Column(name="fax_principale", type="string", length=20, nullable=true)
	 * 
	 * @Assert\Length(min = "0", max = "20",groups={"persona"})
	 * @Assert\Regex(pattern="/^[\d]+$/", message="Il fax può contenere solo cifre",groups={"persona"})
	 */
	protected $fax_principale;

	/**
	 * 
	 * @ORM\Column(name="email_principale", type="string", length=128, nullable=true)
	 * 
	 * @Assert\NotBlank(message="Specificare l'indirizzo email")
	 * @Assert\Email()
	 * @Assert\Length(max = "128")
	 */
	protected $email_principale;

	/**
	 * 
	 * @ORM\Column(name="telefono_secondario", type="string", length=20, nullable=true)
	 * 
	 * @Assert\Length(min = "3", max = "20",groups={"persona"})
	 * @Assert\Regex(pattern="/^[\d]+$/", message="Il telefono può contenere solo cifre",groups={"persona"})
	 */
	protected $telefono_secondario;

	/**
	 *
	 * @ORM\Column(name="fax_secondario", type="string", length=20, nullable=true)
	 * 
	 * @Assert\Length(min = "3", max = "20",groups={"persona"})
	 * @Assert\Regex(pattern="/^[\d]+$/", message="Il fax può contenere solo cifre",groups={"persona"})
	 */
	protected $fax_secondario;

	/**
	 * 
	 * @ORM\Column(name="email_secondario", type="string", length=128, nullable=true)
	 * 
	 * @Assert\Email(groups={"persona"})
	 * @Assert\Length(max = "128",groups={"persona"} )
	 */
	protected $email_secondario;

	/**
	 * @ORM\OneToMany(targetEntity="SoggettoBundle\Entity\IncaricoPersona", mappedBy="incaricato")
     * @var Collection|IncaricoPersona[]
	 */
	protected $incarichi_persone;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $id_sfinge_2013;

	/**
	 * @ORM\ManyToOne(targetEntity="DocumentoBundle\Entity\DocumentoFile")
	 * @ORM\JoinColumn(nullable=true)
	 */
	protected $carta_identita;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    protected $lifnr_sap;

    /**
     * @var bool|null
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $lifnr_sap_created;

	public $disabilitaCombo;

	public function __construct() {
		$this->incarichi_persone = new \Doctrine\Common\Collections\ArrayCollection();
	}

	public function __toString() {
		return $this->getNome() . ' ' . $this->getCognome();
	}

	/**
	 * Get utente
	 *
	 * @return Utente
	 */
	public function getUtente() {
		return $this->utente;
	}

	public function setUtente($utente) {
		$this->utente = $utente;
	}

	/**
	 * Get id
	 *
	 * @return integer 
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Set id
	 *
	 * @param id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * Set nome
	 *
	 * @param string $nome
	 */
	public function setNome($nome) {
		$this->nome = $nome;
	}

	/**
	 * Get nome
	 *
	 * @return string 
	 */
	public function getNome() {
		return $this->nome;
	}

	/**
	 * Set cognome
	 *
	 * @param string $cognome
	 */
	public function setCognome($cognome) {
		$this->cognome = $cognome;
	}

	/**
	 * @return string 
	 */
	public function getCognome() {
		return $this->cognome;
	}

	/**
	 * @param \DateTime $dataNascita
	 */
	public function setDataNascita($dataNascita) {
		$this->data_nascita = \is_string($dataNascita) ? new \DateTime($dataNascita) : $dataNascita;
	}

	/**
	 * Get data_nascita
	 *
	 * @return \DateTime 
	 */
	public function getDataNascita() {
		return $this->data_nascita;
	}

	public function getDataNascitaString() {
		if (!is_null($this->data_nascita)) {
			return $this->data_nascita->format('d-m-Y');
		} else {
			return '-';
		}
	}

	/**
	 * Set sesso
	 *
	 * @param string $sesso
	 */
	public function setSesso($sesso) {
		$this->sesso = $sesso;
	}

	/**
	 * Get sesso
	 *
	 * @return string 
	 */
	public function getSesso() {
		return $this->sesso;
	}

	/**
	 * Set codice_fiscale
	 *
	 * @param string $codiceFiscale
	 */
	public function setCodiceFiscale($codiceFiscale) {
		$this->codice_fiscale = strtoupper($codiceFiscale);
	}

	/**
	 * Get codice_fiscale
	 *
	 * @return string 
	 */
	public function getCodiceFiscale() {
		return strtoupper($this->codice_fiscale);
	}

	/**
	 * Set luogo_residenza
	 *
	 * @param \BaseBundle\Entity\Indirizzo $luogoResidenza
	 */
	public function setLuogoResidenza($luogoResidenza) {
		$this->luogo_residenza = $luogoResidenza;
	}

	/**
	 * Get luogo_residenza
	 *
	 * @return \BaseBundle\Entity\Indirizzo
	 */
	public function getLuogoResidenza() {
		return $this->luogo_residenza;
	}

	public function getNazionalita() {
		return $this->nazionalita;
	}

	public function setNazionalita($nazionalita) {
		$this->nazionalita = $nazionalita;
	}

	public function getStatoNascita() {
		return $this->stato_nascita;
	}

	public function setStatoNascita($stato_nascita) {
		$this->stato_nascita = $stato_nascita;
	}

	public function getComune() {
		return $this->comune;
	}

	public function setComune($comune) {
		$this->comune = $comune;
	}

	public function getProvincia() {
		return $this->getComune() ? $this->getComune()->getProvincia() : null;
	}

	public function getProvinciaEstera() {
		return $this->provincia_estera;
	}

	public function setProvinciaEstera($provinciaEstera) {
		$this->provincia_estera = $provinciaEstera;
	}

	public function getComuneEstero() {
		return $this->comune_estero;
	}

	public function setComuneEstero($comuneEstero) {
		$this->comune_estero = $comuneEstero;
	}

	function getTelefonoPrincipale() {
		return $this->telefono_principale;
	}

	function getFaxPrincipale() {
		return $this->fax_principale;
	}

	function getEmailPrincipale() {
		return $this->email_principale;
	}

	function getTelefonoSecondario() {
		return $this->telefono_secondario;
	}

	function getFaxSecondario() {
		return $this->fax_secondario;
	}

	function getEmailSecondario() {
		return $this->email_secondario;
	}

	function setTelefonoPrincipale($telefono_principale) {
		$this->telefono_principale = $telefono_principale;
	}

	function setFaxPrincipale($fax_principale) {
		$this->fax_principale = $fax_principale;
	}

	function setEmailPrincipale($email_principale) {
		$this->email_principale = $email_principale;
	}

	function setTelefonoSecondario($telefono_secondario) {
		$this->telefono_secondario = $telefono_secondario;
	}

	function setFaxSecondario($fax_secondario) {
		$this->fax_secondario = $fax_secondario;
	}

	function setEmailSecondario($email_secondario) {
		$this->email_secondario = $email_secondario;
	}

	/**
	 * @return DocumentoFile
	 */
	public function getCartaIdentita() {
		return $this->carta_identita;
	}

	/**
	 * @param DocumentoFile $carta_identita
	 */
	public function setCartaIdentita(DocumentoFile $carta_identita) {
		$this->carta_identita = $carta_identita;
	}

    /**
     * @return string|null
     */
    public function getLifnrSap(): ?string
    {
        return $this->lifnr_sap;
    }

    /**
     * @param string|null $lifnr_sap
     */
    public function setLifnrSap(?string $lifnr_sap): void
    {
        $this->lifnr_sap = $lifnr_sap;
    }

    /**
     * @return bool|null
     */
    public function getLifnrSapCreated(): ?bool
    {
        return $this->lifnr_sap_created;
    }

    /**
     * @param bool|null $lifnr_sap_created
     */
    public function setLifnrSapCreated(?bool $lifnr_sap_created): void
    {
        $this->lifnr_sap_created = $lifnr_sap_created;
    }

	public function getNomeCognome() {
		return $this->getNome() . " " . $this->getCognome();
	}

	public function setProvincia($provincia) {
		
	}

	/**
	 * validazione in base allo stato
	 *
	 */
	public function checkSelezioneStato(\Symfony\Component\Validator\Context\ExecutionContextInterface $context) {
		if ($this->getStatoNascita()) {
			if ($this->getStatoNascita()->getDenominazione() == "Italia") {
				if (is_null($this->getProvincia())) {
					$context->buildViolation('Devi selezionare provincia e comune se lo stato è Italia')
							->atPath('provincia')
							->addViolation();
				}
				if (is_null($this->getComune())) {
					$context->buildViolation('Devi selezionare provincia e comune se lo stato è Italia')
							->atPath('comune')
							->addViolation();
				}
			}
		}
	}

	public function getPersona() {
		return $this;
	}

	function getIncarichiPersone() {
		return $this->incarichi_persone;
	}

	function setIncarichiPersone($incarichi_persone) {
		$this->incarichi_persone = $incarichi_persone;
	}

	public function addIncarichiPersone(\SoggettoBundle\Entity\IncaricoPersona $incarichiPersone): self {
		$this->incarichi_persone[] = $incarichiPersone;

		return $this;
	}

	public function removeIncarichiPersone(\SoggettoBundle\Entity\IncaricoPersona $incarichiPersone): void {
		$this->incarichi_persone->removeElement($incarichiPersone);
	}

	/**
	 * @param string $idSfinge2013
	 * @return Persona
	 */
	public function setIdSfinge2013($idSfinge2013) {
		$this->id_sfinge_2013 = $idSfinge2013;

		return $this;
	}

	/**
	 * @return string 
	 */
	public function getIdSfinge2013() {
		return $this->id_sfinge_2013;
	}

	/** Copia quasi tutti i dati da un persona a quest'oggetto
	 * @param Persona $persona fonte dei dati
	 */
	public function mergeData(Persona $persona): void {
		$this->cognome = $persona->getCognome();
		$this->comune = $persona->getComune();
		$this->comune_estero = $persona->getComuneEstero();
		$this->data_nascita = $persona->getDataNascita();
		$this->email_principale = $persona->getEmailPrincipale();
		$this->email_secondario = $persona->getEmailSecondario();
		$this->fax_principale = $persona->getFaxPrincipale();
		$this->fax_secondario = $persona->getFaxSecondario();
		$this->luogo_residenza = $persona->getLuogoResidenza();
		$this->nazionalita = $persona->getNazionalita();
		$this->nome = $persona->getNome();
		$this->provincia_estera = $persona->getProvinciaEstera();
		$this->sesso = $persona->getSesso();
		$this->stato_nascita = $persona->getStatoNascita();
		$this->telefono_principale = $persona->getTelefonoPrincipale();
		$this->telefono_secondario = $persona->getTelefonoSecondario();
	}

    /**
     * @return array
     */
    public function getPersonaSoggettoSap(): array
    {
        $retVal = ['esito' => true, 'persona' => null, 'errori' => []];
        if (empty($this->getNome())) {
            $retVal['esito'] = false;
            $retVal['errori'][] = 'Nome mancante';
        }

        if (empty($this->getCognome())) {
            $retVal['esito'] = false;
            $retVal['errori'][] = 'Cognome mancante';
        }

        if (empty($this->getCodiceFiscale())) {
            $retVal['esito'] = false;
            $retVal['errori'][] = 'Codice fiscale mancante';
        }

        if (empty($this->getSesso())) {
            $retVal['esito'] = false;
            $retVal['errori'][] = 'Sesso mancante';
        }

        if (empty($this->getEmailPrincipale())) {
            $retVal['esito'] = false;
            $retVal['errori'][] = 'E-mail mancante';
        }

        /*if (empty($this->emailPec)) {
            $retVal['esito'] = false;
            $retVal['errori'][] = 'E-mail PEC mancante';
        }*/

        if (empty($this->getLuogoResidenza())) {
            $retVal['esito'] = false;
            $retVal['errori'][] = 'Indirizzo di residenza mancante';
        } else {
            if (empty($this->getLuogoResidenza()->getComune())) {
                $retVal['esito'] = false;
                $retVal['errori'][] = 'Comune mancante';
            }

            if (empty($this->getLuogoResidenza()->getCap())) {
                $retVal['esito'] = false;
                $retVal['errori'][] = 'CAP mancante';
            }

            if (empty($this->getLuogoResidenza()->getVia())) {
                $retVal['esito'] = false;
                $retVal['errori'][] = 'Via mancante';
            }

            if (empty($this->getLuogoResidenza()->getNumeroCivico())) {
                $retVal['esito'] = false;
                $retVal['errori'][] = 'Numero civico mancante';
            }
        }

        $persona = new Persona();
        if ($retVal['esito']) {
            $persona->zzCatEc = 100;
            $persona->setNome($this->getNome());
            $persona->setCognome($this->getCognome());
            $persona->setCodiceFiscale($this->getCodiceFiscale());
            $persona->setLuogoResidenza($this->getLuogoResidenza());
            $persona->setEmailPrincipale($this->getEmailPrincipale());

            if (!empty($this->emailPEC)) {
                $persona->emailPEC = $this->emailPEC;
            }

            if ($this->getSesso() == 'M') {
                $persona->sexkz = "1";
            } else {
                $persona->sexkz = "2";
            }

            $retVal['persona'] = $persona;
        }

        return $retVal;
    }
}
