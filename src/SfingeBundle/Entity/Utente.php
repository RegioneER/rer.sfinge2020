<?php

namespace SfingeBundle\Entity;

use AnagraficheBundle\Entity\Persona;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use IstruttorieBundle\Entity\AssegnamentoIstruttoriaRichiesta;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="utenti")
 * @ORM\Entity(repositoryClass="SfingeBundle\Entity\UtenteRepository")
 * @UniqueEntity("username")
 * @ORM\AttributeOverrides({
 *  @ORM\AttributeOverride(name="emailCanonical",column=@ORM\Column(type = "string", name = "email_canonical", nullable = true, unique = false)),
 *  @ORM\AttributeOverride(name="email",column=@ORM\Column(type = "string", name = "email", nullable = true, unique = false))
 * })
 */
class Utente extends BaseUser {
    public function __construct() {
        parent::__construct();
        parent::setRoles(array('ROLE_USER'));
        $this->datiPersonaInseriti = false;
        $this->cambiaPassword = true;
        $this->procedure = new ArrayCollection();
        $this->assegnamenti_istruttorie_richieste = new ArrayCollection();
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** @ORM\Column(type="boolean", name="dati_persona_inseriti") */
    protected $datiPersonaInseriti;

    /** @ORM\Column(type="boolean", name="cambio_password") */
    protected $cambiaPassword;

    /**
     * @ORM\OneToOne(targetEntity="AnagraficheBundle\Entity\Persona", inversedBy="utente", cascade={"persist"})
     * @ORM\JoinColumn(name="persona_id", referencedColumnName="id")
     * @var Persona
     */
    protected $persona;

    /**
     * 
     * @ORM\Column(type="string", name="creato_da", nullable=true) 
     */
    protected $creato_da;

    /**
     * 
     * @ORM\Column(type="datetime", name="creato_il", nullable=true) 
     */
    protected $creato_il;

    /**
     * @ORM\OneToMany(targetEntity="SfingeBundle\Entity\Procedura", mappedBy="responsabile")
     */
    private $procedure;


    /**
     *
     * @ORM\ManyToMany(targetEntity="PermessoFunzionalita", inversedBy="utenti")
     * @ORM\JoinTable(name="utenti_permessi_funzionalita")
     */
    protected $permessi_funzionalita;

    
    /**
     * @ORM\Column(type="bigint", name="mantis_user_id", nullable=true)
     */
    protected $mantis_user_id;

    /**
     * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\AssegnamentoIstruttoriaRichiesta", mappedBy="istruttore", cascade={"persist"})
     * @ORM\OrderBy({"dataAssegnamento" = "DESC"})
     * @var Collection|AssegnamentoIstruttoriaRichiesta[]
     */
    protected $assegnamenti_istruttorie_richieste;

    /**
     * @ORM\Column(type="boolean", name="privacy_accettata", options={"default"=0}, nullable=false)
     * @Assert\NotNull()
     */
    protected $privacyAccettata = false;

    /**
     * @ORM\OneToMany(targetEntity="SfingeBundle\Entity\Procedura", mappedBy="rup_procedure")
     */
    private $rup_procedure;

    /**
     * @return bool
     */
    public function getPrivacyAccettata(): bool
    {
        return $this->privacyAccettata;
    }

    /**
     * @param bool $privacyAccettata
     */
    public function setPrivacyAccettata(bool $privacyAccettata): void
    {
        $this->privacyAccettata = $privacyAccettata;
    }
    
    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getDatiPersonaInseriti() {
        return $this->datiPersonaInseriti;
    }

    public function setDatiPersonaInseriti($datiPersonaInseriti) {
        $this->datiPersonaInseriti = $datiPersonaInseriti;
    }

    public function getCambiaPassword() {
        return $this->cambiaPassword;
    }

    public function setCambiaPassword($cambiaPassword) {
        $this->cambiaPassword = $cambiaPassword;
    }

    function getPersona() {
        return $this->persona;
    }

    function setPersona($persona) {
        $this->persona = $persona;
    }

    function getCreatoDa() {
        return $this->creato_da;
    }

    function getCreatoIl() {
        return $this->creato_il;
    }

    function setCreatoDa($creato_da) {
        $this->creato_da = $creato_da;
    }

    function setCreatoIl($creato_il) {
        $this->creato_il = $creato_il;
    }
    
    function getMantisUserId() {
        return $this->mantis_user_id;
    }

    function setMantisUserId($mantis_user_id) {
        $this->mantis_user_id = $mantis_user_id;
    }
    
    function hasMantisUserId(){
        if(is_null($this->getMantisUserId())){
            return false;
        }else{
            return true;
        }
    }

    
    /**
     * @return mixed
     */
    public function getProcedure() {
        return $this->procedure;
    }

    /**
     * @param mixed $procedure
     */
    public function setProcedure($procedure) {
        $this->procedure = $procedure;
    }

    /**
     * Overriding Fos User class due to impossible to set default role ROLE_USER 
     * @see User at line 138
     * @link https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Model/User.php#L138
     * {@inheritdoc}
     */
    public function addRole($role) {
        $role = strtoupper($role);

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function getRuoli() {
        foreach ($this->roles as $ruolo) {

            $ruolo = str_replace('ROLE_', '', $ruolo);
            $ruoliOut[] = $ruolo;
        }

        return $ruoliOut;
    }

    public function esistePersona() {
        return !is_null($this->persona);
    }

    public function __toString() {
        try {
            $persona = $this->getPersona();
            if (!$persona) {
                return $this->getUsername();
            }
            return $persona->getCognome() . " " . $persona->getNome();
        } catch (\Exception $e) {
            return $this->getUsername();
        }
    }

    // Funzione che ritorna tutti i soggetti per i quali l'utente ha un incarico ATTIVO
    public function getSoggetti() {
        $persona = $this->getPersona();
        $soggetti = array();
        foreach ($persona->getIncarichi() as $incarico) {
            if ($incarico->getStato()->getCodice() == 'ATTIVO') {
                $soggetti[] = $incarico->getSoggetto();
            }
        }
        return $soggetti;
    }

    public function getPermessiFunzionalita() {
        return $this->permessi_funzionalita;
    }

    public function setPermessiFunzionalita($permessi_funzionalita) {
        $this->permessi_funzionalita = $permessi_funzionalita;
    }

    /**
     * Returns the user roles
     *
     * @return array The roles
     */
    public function getRoles()
    {
        $roles = parent::getRoles();
        
        if (!is_null($this->getPermessiFunzionalita())) {
            foreach ($this->getPermessiFunzionalita() as $permesso) {
                $roles[] = "ROLE_".$permesso->getCodice();
            }
        }
        
        return $roles;
    }
    
    public function getUtente() {
        return $this;
    }

    function isPA() {
        $ruoliUtente = $this->getRoles(); 
        $isPA = false;
        foreach ($ruoliUtente as $ruoloUtente) {
            if(strstr($ruoloUtente, "PA")){
                return true;
            }
        }
        return $isPA;
    }
    
    function isValutatoreFesr() {
        $ruoliUtente = $this->getRoles(); 
        foreach ($ruoliUtente as $ruoloUtente) {
            if($ruoloUtente == 'ROLE_VALUTATORE_FESR'){
                return true;
            }
        }
        return false;
    }
    
    function isOperatoreCogea() {
        $ruoliUtente = $this->getRoles(); 
        foreach ($ruoliUtente as $ruoloUtente) {
            if($ruoloUtente == 'ROLE_OPERATORE_COGEA'){
                return true;
            }
        }
        return false;
    }
    
    function isInvitalia() {
        $ruoliUtente = $this->getRoles();
        foreach ($ruoliUtente as $ruoloUtente) {
            if ($ruoloUtente == 'ROLE_ISTRUTTORE_INVITALIA') {
                return true;
            }
        }
        return false;
    }
    
    function haDoppioRuoloInvFesr() {
        $arraycf = array('FRRBDT90P57D548U', 'MLZGNN88M26E882H', 'RSSRND70A11D708G');
        return in_array($this->getUsername(), $arraycf);
    }
    
    function haDoppioRuoloInvFesrImpostato() {
        $arraycf = array('FRRBDT90P57D548U', 'MLZGNN88M26E882H', 'RSSRND70A11D708G');
        return in_array($this->getUsername(), $arraycf) && (in_array('ROLE_ISTRUTTORE_INVITALIA', $this->getRoles()) || in_array('ROLE_SUPERVISORE_CONTROLLI', $this->getRoles()));
    }

    function isConsulenteFesr() {
        $ruoliUtente = $this->getRoles(); 
        foreach ($ruoliUtente as $ruoloUtente) {
            if($ruoloUtente == 'ROLE_CONSULENTE_FESR'){
                return true;
            }
        }
        return false;
    }
    
    public function isAbilitatoStrumentiFinanziari() {
        $ruoliUtente = $this->getRoles();
        foreach ($ruoliUtente as $ruoloUtente) {
            if (
                    $ruoloUtente == "ROLE_SUPER_ADMIN" ||
                    $ruoloUtente == "ROLE_GESTIONE_ASSISTENZA_TECNICA" ||
                    $ruoloUtente == "ROLE_GESTIONE_INGEGNERIA_FINANZIARIA" ||
                    $ruoloUtente == "ROLE_GESTIONE_ACQUISIZIONI" ||
                    $ruoloUtente == "ROLE_GESTIONE_ASSISTENZA_TECNICA_READONLY" ||
                    $ruoloUtente == "ROLE_GESTIONE_INGEGNERIA_FINANZIARIA_READONLY" ||
                    $ruoloUtente == "ROLE_GESTIONE_ACQUISIZIONI_READONLY"
            ) {
                return true;
            }
        }
        return false;
    }

    public function isAbilitatoStrumentiFinanziariScrittura() {
        $ruoliUtente = $this->getRoles();
        foreach ($ruoliUtente as $ruoloUtente) {
            if (
                $ruoloUtente == "ROLE_SUPER_ADMIN" ||
                $ruoloUtente == "ROLE_GESTIONE_ASSISTENZA_TECNICA" ||
                $ruoloUtente == "ROLE_GESTIONE_INGEGNERIA_FINANZIARIA" ||
                $ruoloUtente == "ROLE_GESTIONE_ACQUISIZIONI"
            ) 
            {
                return true;
            }
        }
        return false;
    }

    /**
     * @return Collection|AssegnamentoIstruttoriaRichiesta[]
     */
    public function getAssegnamentiIstruttorieRichieste()
    {
        return $this->assegnamenti_istruttorie_richieste;
    }

    /**
     * @param Collection|AssegnamentoIstruttoriaRichiesta[] $assegnamenti_istruttorie_richieste
     */
    public function setAssegnamentiIstruttorieRichieste($assegnamenti_istruttorie_richieste): void
    {
        $this->assegnamenti_istruttorie_richieste = $assegnamenti_istruttorie_richieste;
    }

    /**
     * @return mixed
     */
    public function getRupProcedure()
    {
        return $this->rup_procedure;
    }

    /**
     * @param mixed $rup_procedure
     */
    public function setRupProcedure($rup_procedure): void
    {
        $this->rup_procedure = $rup_procedure;
    }

    /**
     * @return int
     */
    public function getNrAssegnamentiIstruttorieRichiesteInCorso()
    {
        $istruttorieInCorso = $this->getAssegnamentiIstruttorieRichieste()->filter(
            function(AssegnamentoIstruttoriaRichiesta $assegnamento) {
                if ($assegnamento->getRichiesta()->getIstruttoria() && !$assegnamento->getRichiesta()->getIstruttoria()->getEsito()) {
                    return $assegnamento;
                }

            });

        return $istruttorieInCorso->count() ?: 0;
    }

    public function getNomeCognomeIstruttoreRichieste() {
        try {
            $nrAssegnamenti = $this->getNrAssegnamentiIstruttorieRichiesteInCorso() ? ' (' . $this->getNrAssegnamentiIstruttorieRichiesteInCorso() . ' in corso)' : '';

            $persona = $this->getPersona();
            if (!$persona) {
                return $this->getUsername() . $nrAssegnamenti;
            }
            return $persona->getCognome() . " " . $persona->getNome() . $nrAssegnamenti;
        } catch (\Exception $e) {
            return $this->getUsername();
        }
    }
}
