<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\EntityLoggabileCancellabile;

/**
 * Description of ImpegniAmmessi.
 *
 * @author vbuscemi
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Repository\ImpegniAmmessiRepository")
 * @ORM\Table(name="richieste_impegni_ammessi")
 */
class ImpegniAmmessi extends EntityLoggabileCancellabile {
    public static $TIPOLOGIE_IMPEGNI_AMMESSI = [
        "I" => "Impegno",
        "D" => "Disimpegno",
        "I-TR" => "Impegno per trasferimento",
        "D-TR" => "Diseimpegno per trasferimento",
    ];

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="RichiestaLivelloGerarchico", inversedBy="impegni_ammessi")
     * @ORM\JoinColumn(name="richiesta_livello_gerarchico_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull
     */
    protected $richiesta_livello_gerarchico;

    /**
     * @ORM\ManyToOne(targetEntity="RichiestaImpegni", inversedBy="mon_impegni_ammessi")
     * @ORM\JoinColumn(name="richiesta_impegni_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull
     */
    protected $richiesta_impegni;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\NotNull
     */
    protected $data_imp_amm;

    /**
     * @ORM\Column(type="string", length=5, nullable=false)
     * @Assert\Length(max="5", maxMessage="sfinge.monitoraggio.maxLength")
     * @Assert\NotNull
     */
    protected $tipologia_imp_amm;

    /**
     * @ORM\ManyToOne(targetEntity="MonitoraggioBundle\Entity\TC38CausaleDisimpegno")
     * @ORM\JoinColumn(name="causale_disimpegno_amm_id", referencedColumnName="id", nullable=true)
     */
    protected $tc38_causale_disimpegno_amm;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\NotNull
     * @Assert\GreaterThan(value=0, message="sfinge.monitoraggio.greaterThan")
     */
    protected $importo_imp_amm;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     * @Assert\Length(max=1000, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $note_imp;

    /**
     * @Assert\CallBack
     */
    public function validate(\Symfony\Component\Validator\Context\ExecutionContextInterface $context) {
        $importoImpegno = $this->richiesta_impegni->getImportoImpegno();
        $sommaImpegni = 0;
        $sommaTrasferimentiImpegni = 0;
        foreach ($this->richiesta_impegni->getMonImpegniAmmessi() as $ammesso) {
            switch ($ammesso->getTipologiaImpAmm()) {
                case 'I':
                    $sommaImpegni += $ammesso->getImportoImpAmm();
                    break;
                case 'D':
                    $sommaImpegni -= $ammesso->getImportoImpAmm();
                    break;
                case 'I-TR':
                    $sommaTrasferimentiImpegni += $ammesso->getImportoImpAmm();
                    break;
                case 'D-TR':
                    $sommaTrasferimentiImpegni -= $ammesso->getImportoImpAmm();
                    break;

                default:
                    throw new \Exception('Tipologia impegno ammesso non valido');
                    break;
            }
        }
        if ($sommaImpegni > $importoImpegno || $sommaTrasferimentiImpegni > $importoImpegno) {
            $context->buildViolation('sfinge.monitoraggio.importoImpegniAmessiSuperiori')
                ->atPath('importo_imp_amm')
                ->addViolation();
        }
        if ($sommaImpegni < 0) {
            $context->buildViolation('sfinge.monitoraggio.importoDismpegniAmessiSuperiori')
                ->atPath('importo_imp_amm')
                ->addViolation();
        }
    }

    public function __construct(RichiestaImpegni $impegno = null, RichiestaLivelloGerarchico $livello = null) {
        $this->richiesta_impegni = $impegno;
        if(\is_null($impegno)){
            return;
        }
        $this->importo_imp_amm = $impegno->getImportoImpegno();
        $this->tipologia_imp_amm = $impegno->getTipologiaImpegno();
        $this->tc38_causale_disimpegno_amm = $impegno->getTc38CausaleDisimpegno();
        $this->data_imp_amm = $impegno->getDataImpegno();
        
        if(\is_null($livello)){
            /** @var RichiestaProgramma $programma */
            $programma = $impegno->getRichiesta()->getMonProgrammi()->first();
            if($programma){
                $livello = $programma->getLivelliGerarchiciObiettivoSpecifico()->first() ?: null;
            }      
        }
            
        $this->richiesta_livello_gerarchico = $livello;
    }

    public function getId() {
        return $this->id;
    }

    /**
     * @return RichiestaLivelloGerarchico
     */
    public function getRichiestaLivelloGerarchico() {
        return $this->richiesta_livello_gerarchico;
    }

    /**
     * @return RichiestaImpegni
     */
    public function getRichiestaImpegni() {
        return $this->richiesta_impegni;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setRichiestaLivelloGerarchico($richiesta_livello_gerarchico) {
        $this->richiesta_livello_gerarchico = $richiesta_livello_gerarchico;
    }

    public function setRichiestaImpegni($richiesta_impegni) {
        $this->richiesta_impegni = $richiesta_impegni;
    }

    public function getDataImpAmm() {
        return $this->data_imp_amm;
    }

    public function getTipologiaImpAmm() {
        return $this->tipologia_imp_amm;
    }

    public function getTc38CausaleDisimpegnoAmm() {
        return $this->tc38_causale_disimpegno_amm;
    }

    public function getImportoImpAmm() {
        return $this->importo_imp_amm;
    }

    public function setDataImpAmm($data_imp_amm) {
        $this->data_imp_amm = $data_imp_amm;
    }

    public function setTipologiaImpAmm($tipologia_imp_amm) {
        $this->tipologia_imp_amm = $tipologia_imp_amm;
    }

    public function setTc38CausaleDisimpegnoAmm($tc38_causale_disimpegno_amm) {
        $this->tc38_causale_disimpegno_amm = $tc38_causale_disimpegno_amm;
    }

    /**
     * @param float $importo_imp_amm
     * @return self
     */
    public function setImportoImpAmm($importo_imp_amm) {
        $this->importo_imp_amm = $importo_imp_amm;
        return $this;
    }

    public function getNoteImp() {
        return $this->note_imp;
    }

    public function setNoteImp($note_imp) {
        $this->note_imp = $note_imp;
    }
}
