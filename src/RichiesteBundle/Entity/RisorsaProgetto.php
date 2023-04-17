<?php

namespace RichiesteBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Intervento
 *
 * @ORM\Table(name="risorse_progetti")
 * @ORM\Entity()
 */
class RisorsaProgetto extends EntityLoggabileCancellabile {

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Proponente")
     * @ORM\JoinColumn(name="proponente_id", nullable=true)
     * @Assert\NotNull(message = "Indicare l'impresa di appartenenza", groups={"bando_65_ricerca", "bando_65_ausiliario"})
     */
    protected $proponente;

    /**
     * @ORM\ManyToOne(targetEntity="TipologiaRisorsa")
     * @ORM\JoinColumn(name="tipologia_risorsa_id", referencedColumnName="id")
     * @Assert\NotNull(message = "Selezionare una tipologia")
     * @var TipologiaRisorsa
     */
    protected $tipologia_risorsa;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="risorse_progetto")
     * @ORM\JoinColumn(name="richiesta_id", referencedColumnName="id", nullable=false)
     */
    protected $richiesta;

    /**
     * @ORM\Column(name="nome", type="string", length=50, nullable=true) 
     * @Assert\NotNull(message = "Indicare il nome del manager", groups={"bando_65_manager"})
     */
    protected $nome;

    /**
     * @ORM\Column(name="cognome", type="string", length=50, nullable=true)
     * @Assert\NotNull(message = "Indicare il cognome del manager", groups={"bando_65_manager"})
     */
    protected $cognome;

    /**
     * @ORM\Column(name="tipologia_laurea", type="string", length=250, nullable=true)
     * @Assert\Length(max = "250")
     * @Assert\NotNull(message = "Indicare il profilo/tipologia di laurea", groups={"bando_65_nuovo","bando_103_nuovo","bando_103_ricerca", "bando_103_ausiliario"})
     */
    protected $tipologia_laurea;

    /**
     * @ORM\Column(name="qualifica_ruolo", type="string", length=250, nullable=true)
     * @Assert\NotNull(message = "Indicare la qualifica/ruolo", 
     *     groups={"bando_65_ricerca", "bando_65_ausiliario","bando_103_ricerca", "bando_103_ausiliario"})
     * @Assert\Length(max = "250")
     */
    private $qualifica_ruolo;

    /**
     * @ORM\Column(name="ruolo", type="string", length=250, nullable=true)
     * @Assert\NotNull(message = "Indicare la mansione", groups={"bando_65_ricerca", "bando_65_ausiliario", "bando_65_nuovo", "bando_103_nuovo"})
     * @Assert\NotNull(message = "Indicare la funzione", groups={"bando_65_contrattuale", "bando_65_materiali", "bando_103_contrattuale"})
     * @Assert\NotNull(message = "Indicare l'utilizzo nel progetto", groups={"bando_103_strumentazione"})
     * @Assert\NotNull(message = "Indicare come si inserisce nel prototipo", groups={"bando_103_prototipi"})
     * @Assert\NotNull(message = "Indicare la descrizione dell'utilizzazione", groups={"bando_103_materiali"})
     * @Assert\Length(max = "250")
     */
    protected $mansione_funzione;

    /**
     * @ORM\Column(name="esperienza_profilo", type="string", length=1000, nullable=true)
     * @Assert\NotNull(message = "Indicare la descrizione del profilo del manager", groups={"bando_65_manager"})
     * @Assert\Length(max = "1000")
     */
    protected $esperienza_profilo;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\NotNull(message = "Indicare i giorni persona", 
     *     groups={"bando_65_ricerca", "bando_65_ausiliario", "bando_65_nuovo", "bando_103_ricerca", "bando_103_ausiliario", "bando_103_nuovo"})
     */
    protected $giorni_persona;

    /**
     * @ORM\Column(type="string", nullable=true, length=255)""
     * @Assert\NotNull(message = "Indicare la tipologia di contratto", groups={"bando_65_nuovo","bando_103_nuovo", "bando_103_ricerca", "bando_103_ausiliario"})
     */
    protected $tipologia_contratto;

    /**
     * @ORM\Column(type="string", nullable=true, length=255)
     */
    protected $tipologia_manager_esterno;

    /**
     * @ORM\Column(name="denominazione_fornitore", type="string", length=250, nullable=true)
     * @Assert\NotNull(message = "Indicare la ragione sociale del fornitore", groups={"bando_65_contrattuale", "bando_103_strumentazione", "bando_103_prototipi", "bando_103_materiali"})
     * @Assert\Length(max = "250")
     */
    protected $denominazione_fornitore;

    /**
     * @ORM\Column(name="codice_fiscale_fornitore", type="string", length=255, nullable=true)
     * @Assert\NotNull(message = "Indicare il codice fiscale del fornitore", groups={"bando_65_contrattuale"})
     * @Assert\Length(min=2, max=16)
     */
    protected $codice_fiscale_fornitore;

    /**
     * @ORM\Column(name="importo", type="decimal", precision=10, scale=2, nullable=true)  
     * @Assert\NotNull(message = "Indicare l'importo", groups={"bando_65_contrattuale", "bando_103_strumentazione", "bando_103_prototipi", "bando_103_materiali"})   
     * @Assert\NotNull(message = "Indicare l'importo del matariale/fornitura", groups={"bando_65_materiali"})   
     */
    protected $importo;

    /**
     * @ORM\Column(name="descrizione_materiale", type="string", length=1000, nullable=true) 
     * @Assert\NotNull(message = "Indicare la descrizione del materiale", groups={"bando_65_materiali"})   
     * @Assert\NotNull(message = "Indicare la descrizione componenti,semilavorati ecc..", groups={"bando_103_prototipi"})   
     * @Assert\NotNull(message = "Indicare le materie prime ecc..", groups={"bando_103_materiali"})   
     * @Assert\Length(max = "1000")
     */
    protected $descrizione_materiale;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\NotNull(message = "Indicare il numero di unità", groups={"bando_103_ricerca", "bando_103_ausiliario"})
     * @Assert\NotNull(message = "Indicare la quantità", groups={ "bando_103_materiali"})
     */
    protected $numero_unita;

    /**
     * @ORM\Column(name="tipo_bene", type="string", length=1000, nullable=true) 
     * @Assert\NotNull(message = "Indicare il tipo del bene", groups={"bando_103_strumentazione"})   
     * @Assert\Length(max = "1000")
     */
    protected $tipo_bene;

    public function getId() {
        return $this->id;
    }

    public function getProponente() {
        return $this->proponente;
    }

    public function getTipologiaRisorsa(): ?TipologiaRisorsa {
        return $this->tipologia_risorsa;
    }

    public function getRichiesta() {
        return $this->richiesta;
    }

    public function getNome() {
        return $this->nome;
    }

    public function getCognome() {
        return $this->cognome;
    }

    public function getTipologiaLaurea() {
        return $this->tipologia_laurea;
    }

    public function getQualificaRuolo() {
        return $this->qualifica_ruolo;
    }

    public function getMansioneFunzione() {
        return $this->mansione_funzione;
    }

    public function getEsperienzaProfilo() {
        return $this->esperienza_profilo;
    }

    public function getGiorniPersona() {
        return $this->giorni_persona;
    }

    public function getTipologiaContratto() {
        return $this->tipologia_contratto;
    }

    public function getTipologiaManagerEsterno() {
        return $this->tipologia_manager_esterno;
    }

    public function getDenominazioneFornitore() {
        return $this->denominazione_fornitore;
    }

    public function getCodiceFiscaleFornitore() {
        return $this->codice_fiscale_fornitore;
    }

    public function getImporto() {
        return $this->importo;
    }

    public function getDescrizioneMateriale() {
        return $this->descrizione_materiale;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setProponente($proponente) {
        $this->proponente = $proponente;
    }

    public function setTipologiaRisorsa($tipologia_risorsa) {
        $this->tipologia_risorsa = $tipologia_risorsa;
    }

    public function setRichiesta($richiesta) {
        $this->richiesta = $richiesta;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function setCognome($cognome) {
        $this->cognome = $cognome;
    }

    public function setTipologiaLaurea($tipologia_laurea) {
        $this->tipologia_laurea = $tipologia_laurea;
    }

    public function setQualificaRuolo($qualifica_ruolo) {
        $this->qualifica_ruolo = $qualifica_ruolo;
    }

    public function setMansioneFunzione($mansione_funzione) {
        $this->mansione_funzione = $mansione_funzione;
    }

    public function setEsperienzaProfilo($esperienza_profilo) {
        $this->esperienza_profilo = $esperienza_profilo;
    }

    public function setGiorniPersona($giorni_persona) {
        $this->giorni_persona = $giorni_persona;
    }

    public function setTipologiaContratto($tipologia_contratto) {
        $this->tipologia_contratto = $tipologia_contratto;
    }

    public function setTipologiaManagerEsterno($tipologia_manager_esterno) {
        $this->tipologia_manager_esterno = $tipologia_manager_esterno;
    }

    public function setDenominazioneFornitore($denominazione_fornitore) {
        $this->denominazione_fornitore = $denominazione_fornitore;
    }

    public function setCodiceFiscaleFornitore($codice_fiscale_fornitore) {
        $this->codice_fiscale_fornitore = $codice_fiscale_fornitore;
    }

    public function setImporto($importo) {
        $this->importo = $importo;
    }

    public function setDescrizioneMateriale($descrizione_materiale) {
        $this->descrizione_materiale = $descrizione_materiale;
    }

    public function getNumeroUnita() {
        return $this->numero_unita;
    }

    public function setNumeroUnita($numero_unita) {
        $this->numero_unita = $numero_unita;
    }

    public function getTipoBene() {
        return $this->tipo_bene;
    }

    public function setTipoBene($tipo_bene) {
        $this->tipo_bene = $tipo_bene;
    }

    public function getTipologiaManagerEsternoDescrizione() {
        switch ($this->getTipologiaManagerEsterno()) {
            case 'CONSULENTE':
                return "Consulente";
            case 'LIBERO_PROF':
                return "Libero professionista";
            default :
                return '-';
        }
    }

    public function getTipologiaContrattoDescrizione() {
        switch ($this->getTipologiaContratto()) {
            case 'STABILIZZAZIONE':
                return "Stabilizzazione";
            case 'NUOVA_ASSUNZIONE':
                return "Nuova assunzione";
            case 'DETERMINATO':
                return "Determinato";
            case 'INDETERMINATO':
                return "Indeterminato";
            default :
                return '-';
        }
    }
    
    public function isNuovaAssunzione() {
        return $this->getTipologiaContratto() == 'NUOVA_ASSUNZIONE';
    }

}
