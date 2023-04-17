<?php

namespace RichiesteBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="proponenti_professionisti",
 *     indexes={
 *         @ORM\Index(name="idx_proponente_id", columns={"proponente_id"})
 *     })
 */
class ProponenteProfessionista extends EntityLoggabileCancellabile {
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="iscrizione_ordine_appartenenza", nullable=true)
     */
    protected $iscrizioneOrdineAppartenenza;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true, name="ordine_appartenenza")
     */
    protected $ordineAppartenenza;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true, name="numero_iscrizione_ordine")
     * @Assert\Length(min=1, minMessage="Il campo deve essere almeno {{ limit }} caratteri", groups={"singoloIscritto", "associazioneIscritto"})
     */
    protected $numeroIscrizioneOrdine;

    /**
     * @var \DateTime
     * @ORM\Column(type="date", nullable=true, name="data_iscrizione_ordine")
     * @Assert\Date
     */
    protected $dataIscrizioneOrdine;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=true, name="iscritto_previdenza_appartenenza")
     */
    protected $iscrittoPrevidenzaAppartenenza;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true, name="cassa_previdenza_apparteneneza")
     * @Assert\Length(min=1, minMessage="Il campo deve essere almeno {{ limit }} caratteri")
     */
    protected $cassaPrevidenzaAppartenenza;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true, name="matricola_cassa_previdenza_appartenenza")
     * @Assert\Length(min=1, minMessage="Il campo deve essere almeno {{ limit }} caratteri")
     */
    protected $matricolaCassaPrevidenzaAppartenenza;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=true, name="iscritto_inps")
     */
    protected $iscrittoInps;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true, name="numero_iscrizione_inps")
     * @Assert\Length(min=11, minMessage="Il campo deve essere almeno {{ limit }} caratteri", groups={"singoloNonIscritto", "associazioneNonIscritto"})
     */
    protected $numeroIscrizioneInps;

    /**
     * @var int
     * @Assert\Type("integer")
     * @ORM\Column(type="integer", nullable=true, name="numero_dipendenti")
     * @Assert\GreaterThan(value=-1)
     * @Assert\NotNull(groups={ "professionistaSingolo", "associazione", "professionistaSingolo118", "associazione118", "professionistaSingolo125", "associazione125" })
     */
    protected $numeroDipendenti;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true, name="contratto_collettivo")
     * @Assert\NotNull(groups={ "professionistaSingolo", "associazione" })
     */
    protected $contrattoCollettivo;

    /**
     * @var float
     * @ORM\Column(type="float", nullable=true, name="reddito_professionale")
     * @Assert\NotNull(groups={ "professionistaSingolo", "associazione" })
     */
    protected $redditoProfessionale;

    /**
     * @var float
     * @ORM\Column(type="float", nullable=true)
     * @Assert\NotNull(groups={ "professionistaSingolo", "associazione" })
     */
    protected $fatturato;

    /**
     * @var Proponente
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Proponente", inversedBy="professionisti")
     * @ORM\JoinColumn(name="proponente_id", referencedColumnName="id", nullable=false)
     */
    protected $proponente;

    /**
     * @ORM\Column(type="date", nullable=true, name="data_nascita")
     * @Assert\Date
     * @Assert\NotNull(groups={ "professionistaAssociazione" })
     * @var \DateTime
     */
    protected $dataNascita;

    /**
     * @ORM\Column(type="string", nullable=true, name="luogo_nascita")
     * @Assert\NotNull(groups={ "professionistaAssociazione" })
     * @var string
     */
    protected $luogoNascita;

    /**
     * va compilato dall'utente se viene selezionato altro dal menu a tendine degli ordini professionali
     * @var string
     * @ORM\Column(type="string", nullable=true, name="specifica_ordine_professionale")
     */
    protected $specificaOrdineProfessionale;

    /**
     * va compilato dall'utente se non è selezionato l'ordine professionale
     * @var string
     * @ORM\Column(type="string", nullable=true, name="tipologia_professionista")
     */
    protected $tipologiaProfessionista;

    /**
     * @var int
     * @Assert\Type("integer")
     * @ORM\Column(type="integer", nullable=true, name="numero_professionisti")
     * @Assert\GreaterThan(value=0)
     * @Assert\NotNull(groups={ "associazione", "associazione118", "associazione125" })
     */
    protected $numeroProfessionisti;

    /**
     * Get id
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set iscrizioneOrdineAppartenenza
     *
     * @param bool $iscrizioneOrdineAppartenenza
     * @return ProponenteProfessionista
     */
    public function setIscrizioneOrdineAppartenenza($iscrizioneOrdineAppartenenza) {
        $this->iscrizioneOrdineAppartenenza = $iscrizioneOrdineAppartenenza;

        return $this;
    }

    /**
     * Get iscrizioneOrdineAppartenenza
     *
     * @return bool
     */
    public function getIscrizioneOrdineAppartenenza() {
        return $this->iscrizioneOrdineAppartenenza;
    }

    /**
     * Set ordineAppartenenza
     *
     * @param string $ordineAppartenenza
     * @return ProponenteProfessionista
     */
    public function setOrdineAppartenenza($ordineAppartenenza) {
        $this->ordineAppartenenza = $ordineAppartenenza;

        return $this;
    }

    /**
     * Get ordineAppartenenza
     *
     * @return string
     */
    public function getOrdineAppartenenza() {
        return $this->ordineAppartenenza;
    }

    /**
     * Set NumeroIscrizioneOrdine
     *
     * @param string $numeroIscrizioneOrdine
     * @return ProponenteProfessionista
     */
    public function setNumeroIscrizioneOrdine($numeroIscrizioneOrdine) {
        $this->numeroIscrizioneOrdine = $numeroIscrizioneOrdine;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumeroIscrizioneOrdine() {
        return $this->numeroIscrizioneOrdine;
    }

    /**
     * @param \DateTime $dataIscrizioneOrdine
     * @return ProponenteProfessionista
     */
    public function setDataIscrizioneOrdine($dataIscrizioneOrdine) {
        $this->dataIscrizioneOrdine = $dataIscrizioneOrdine;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDataIscrizioneOrdine() {
        return $this->dataIscrizioneOrdine;
    }

    /**
     * @param bool $iscrittoPrevidenzaAppartenenza
     * @return ProponenteProfessionista
     */
    public function setIscrittoPrevidenzaAppartenenza($iscrittoPrevidenzaAppartenenza) {
        $this->iscrittoPrevidenzaAppartenenza = $iscrittoPrevidenzaAppartenenza;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIscrittoPrevidenzaAppartenenza() {
        return $this->iscrittoPrevidenzaAppartenenza;
    }

    /**
     * @param string $cassaPrevidenzaAppartenenza
     * @return ProponenteProfessionista
     */
    public function setCassaPrevidenzaAppartenenza($cassaPrevidenzaAppartenenza) {
        $this->cassaPrevidenzaAppartenenza = $cassaPrevidenzaAppartenenza;

        return $this;
    }

    /**
     * @return string
     */
    public function getCassaPrevidenzaAppartenenza() {
        return $this->cassaPrevidenzaAppartenenza;
    }

    /**
     * @param bool $iscrittoInps
     * @return ProponenteProfessionista
     */
    public function setIscrittoInps($iscrittoInps) {
        $this->iscrittoInps = $iscrittoInps;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIscrittoInps() {
        return $this->iscrittoInps;
    }

    /**
     * @param string $numeroIscrizioneInps
     * @return ProponenteProfessionista
     */
    public function setNumeroIscrizioneInps($numeroIscrizioneInps) {
        $this->numeroIscrizioneInps = $numeroIscrizioneInps;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumeroIscrizioneInps() {
        return $this->numeroIscrizioneInps;
    }

    /**
     * @param int $numeroDipendenti
     * @return ProponenteProfessionista
     */
    public function setNumeroDipendenti($numeroDipendenti) {
        $this->numeroDipendenti = $numeroDipendenti;

        return $this;
    }

    /**
     * @return int
     */
    public function getNumeroDipendenti() {
        return $this->numeroDipendenti;
    }

    /**
     * @param string $contrattoCollettivo
     * @return ProponenteProfessionista
     */
    public function setContrattoCollettivo($contrattoCollettivo) {
        $this->contrattoCollettivo = $contrattoCollettivo;

        return $this;
    }

    /**
     * @return string
     */
    public function getContrattoCollettivo() {
        return $this->contrattoCollettivo;
    }

    /**
     * @param float $redditoProfessionale
     * @return ProponenteProfessionista
     */
    public function setRedditoProfessionale($redditoProfessionale) {
        $this->redditoProfessionale = $redditoProfessionale;

        return $this;
    }

    /**
     * @return float
     */
    public function getRedditoProfessionale() {
        return $this->redditoProfessionale;
    }

    /**
     * @param float $fatturato
     * @return ProponenteProfessionista
     */
    public function setFatturato($fatturato) {
        $this->fatturato = $fatturato;

        return $this;
    }

    /**
     * @return float
     */
    public function getFatturato() {
        return $this->fatturato;
    }

    /**
     * @param \RichiesteBundle\Entity\Proponente $proponente
     * @return ProponenteProfessionista
     */
    public function setProponente(\RichiesteBundle\Entity\Proponente $proponente) {
        $this->proponente = $proponente;

        return $this;
    }

    /**
     * @return \RichiesteBundle\Entity\Proponente
     */
    public function getProponente() {
        return $this->proponente;
    }

    /**
     * @param string $matricolaCassaPrevidenzaAppartenenza
     * @return ProponenteProfessionista
     */
    public function setMatricolaCassaPrevidenzaAppartenenza($matricolaCassaPrevidenzaAppartenenza) {
        $this->matricolaCassaPrevidenzaAppartenenza = $matricolaCassaPrevidenzaAppartenenza;

        return $this;
    }

    /**
     * @return string
     */
    public function getMatricolaCassaPrevidenzaAppartenenza() {
        return $this->matricolaCassaPrevidenzaAppartenenza;
    }

    /**
     * @param \DateTime $dataNascita
     * @return ProponenteProfessionista
     */
    public function setDataNascita($dataNascita) {
        $this->dataNascita = $dataNascita;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDataNascita() {
        return $this->dataNascita;
    }

    /**
     * @param string $luogoNascita
     * @return ProponenteProfessionista
     */
    public function setLuogoNascita($luogoNascita) {
        $this->luogoNascita = $luogoNascita;

        return $this;
    }

    /**
     * @return string
     */
    public function getLuogoNascita() {
        return $this->luogoNascita;
    }

    public function getSpecificaOrdineProfessionale() {
        return $this->specificaOrdineProfessionale;
    }

    public function setSpecificaOrdineProfessionale($specificaOrdineProfessionale) {
        $this->specificaOrdineProfessionale = $specificaOrdineProfessionale;
    }

    public function getTipologiaProfessionista() {
        return $this->tipologiaProfessionista;
    }

    public function setTipologiaProfessionista($tipologiaProfessionista) {
        $this->tipologiaProfessionista = $tipologiaProfessionista;
    }

    /**
     * @Assert\Callback
     */
    public function validateOrdine(ExecutionContextInterface $context) {
        if ($this->iscrizioneOrdineAppartenenza) {
            if (is_null($this->ordineProfessionale)) {
                $context->buildViolation('Campo obbligatorio')
                        ->atPath('ordineProfessionale')
                        ->addViolation();
            }
        }
    }

    /**
     * @Assert\Callback(groups={ "professionistaSingolo", "professionistaAssociazione", "professionistaSingolo118", "professionistaSingolo125" })
     */
    public function validateNumeroIscrizione(ExecutionContextInterface $context) {
        if ($this->iscrizioneOrdineAppartenenza) {
            if (is_null($this->ordineProfessionale)) {
                $context->buildViolation('Campo obbligatorio')
                        ->atPath('ordineProfessionale')
                        ->addViolation();
            }

            if (is_null($this->numeroIscrizioneOrdine)) {
                $context->buildViolation('Campo obbligatorio')
                        ->atPath('numeroIscrizioneOrdine')
                        ->addViolation();
            }
        }
    }

    /**
     * @Assert\Callback
     */
    public function validateCassa(ExecutionContextInterface $context) {
        if ($this->iscrittoPrevidenzaAppartenenza) {
            if (is_null($this->cassaPrevidenzaAppartenenza)) {
                $context->buildViolation('Campo obbligatorio')
                        ->atPath('cassaPrevidenzaAppartenenza')
                        ->addViolation();
            }

            if (is_null($this->matricolaCassaPrevidenzaAppartenenza)) {
                $context->buildViolation('Campo obbligatorio')
                    ->atPath('matricolaCassaPrevidenzaAppartenenza')
                    ->addViolation();
            }
        }
    }

    /**
     * @Assert\Callback
     */
    public function validateInps(ExecutionContextInterface $context) {
        $procedureNoControllo = [125];
        if ($this->iscrittoInps) {
            if (!in_array($this->proponente->getRichiesta()->getProcedura()->getId(), $procedureNoControllo)) {
                if (is_null($this->numeroIscrizioneInps)) {
                    $context->buildViolation('Campo obbligatorio')
                        ->atPath('numeroIscrizioneInps')
                        ->addViolation();
                }
            }
        }
    }

    /**
     * @Assert\Callback(groups={ "professionistaSingolo", "professionistaAssociazione", "professionistaSingolo118", "professionistaSingolo125" })
     */
    public function validateIscrizioni(ExecutionContextInterface $context): void {
        $oggetto = $this->proponente->getRichiesta()->getPrimoOggetto();
        if (!in_array($this->proponente->getRichiesta()->getProcedura()->getId(), $procedureNoControllo)) {
            if ($oggetto->isSingoloNonIscrittoOrdine()) {
                return;
            }
        }
        // se sono iscritto all'inps non posso esserlo ad ordine e cassa..e viceversa
        if (
                ($this->iscrittoInps && ($this->iscrizioneOrdineAppartenenza || $this->iscrittoPrevidenzaAppartenenza))
                ||
                (!$this->iscrittoInps && !$this->iscrizioneOrdineAppartenenza && !$this->iscrittoPrevidenzaAppartenenza)
            ) {
            $context->buildViolation('Inserire o i dati di ordine professionale e cassa di previdenza oppure quelli relativi alla gestione separata INPS')
                    ->atPath('iscrizioneOrdineAppartenenza')
                    ->addViolation();
        }

        //l'iscrizione ad un ordine implica anche l'iscrizione ad una cassa previdenziale e viceversa
        if ($this->iscrizioneOrdineAppartenenza ^ $this->iscrittoPrevidenzaAppartenenza) {
            $context->buildViolation('Se si specifica un ordine professionale va specificata anche l\'appartenenza ad una cassa previdenziale')
                    ->atPath('iscrizioneOrdineAppartenenza')
                    ->addViolation();
        }
    }

    /**
     * @Assert\Callback(groups={ "professionistaSingolo", "professionistaAssociazione", "professionistaSingolo118", "professionistaSingolo125" })
     */
    public function validateTipologiaProfessionista(ExecutionContextInterface $context) {
        if (!$this->iscrizioneOrdineAppartenenza && is_null($this->tipologiaProfessionista)) {
            $context->buildViolation('Se non si inserisce l\'appartenenza ad un ordine professionale è obbligatorio compilare questo campo')
                    ->atPath('tipologiaProfessionista')
                    ->addViolation();
        }
    }

    /**
     * @Assert\Callback
     */
    public function validateSpecificaOrdine(ExecutionContextInterface $context) {
        // voce altro implica la valorizzazione del campo "specificare ordine"
        if ($this->ordineProfessionale && '31' == $this->ordineProfessionale->getId() && empty($this->specificaOrdineProfessionale)) {
            $context->buildViolation('Nel caso in cui venga selezionata la voce Altro è obbligatorio compilare questo campo')
                ->atPath('specificaOrdineProfessionale')
                ->addViolation();
        }
    }

    /**
     * @Assert\IsTrue(message="Indicare il contratto collettivo", groups={"societa", "associazione", "singoloIscritto", "singoloNonIscritto"})
     */
    public function isNumeroDipendentiValid() {
        if ((is_null($this->numeroDipendenti) ? 0 : $this->numeroDipendenti) > 0 && is_null($this->contrattoCollettivo)) {
            return false;
        }
        return true;
    }

    public function getNumeroProfessionisti() {
        return $this->numeroProfessionisti;
    }

    public function setNumeroProfessionisti($numeroProfessionisti) {
        $this->numeroProfessionisti = $numeroProfessionisti;
    }
}
