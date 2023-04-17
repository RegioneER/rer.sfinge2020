<?php
namespace IstruttorieBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="IstruttorieBundle\Entity\PropostaImpegnoRepository")
 * @ORM\Table(name="proposte_impegni")
 */
class PropostaImpegno extends EntityLoggabileCancellabile
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\Procedura", inversedBy="proposte_impegno")
     * @ORM\JoinColumn(name="procedura_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull()
     */
    protected $procedura;

    /**
     * @var PosizioneImpegno[]|Collection
     * @ORM\OneToMany(targetEntity="IstruttorieBundle\Entity\PosizioneImpegno", mappedBy="proposta_impegno")
     */
    protected $posizioni_proposta_impegno;

    /**
     * @ORM\Column(type="date", name="bldat", nullable=false)
     */
    protected $bldat;

    /**
     * @ORM\Column(type="string", length=50,  name="ktext", nullable=true)
     */
    protected $ktext;

    /**
     * @ORM\Column(type="string", length=4,  name="bukrs", nullable=false)
     */
    protected $bukrs;

    /**
     * @ORM\Column(type="date", name="budat", nullable=false)
     */
    protected $budat;

    /**
     * @ORM\Column(type="string", length=14,  name="zz_protocollo", nullable=true)
     */
    protected $zzProtocollo;

    /**
     * @ORM\Column(type="smallint", name="zz_num_ripartiz", nullable=true)
     */
    protected $zzNumRipartiz;

    /**
     * @ORM\Column(type="string", length=1,  name="zz_tipo_doc", nullable=true)
     */
    protected $zzTipoDoc;

    /**
     * @ORM\Column(type="smallint", name="zz_progr_prog", nullable=true)
     */
    protected $zzProgrProg;

    /**
     * @ORM\Column(type="string", length=1,  name="zz_contr_imp", nullable=true)
     */
    protected $zzContrImp;

    /**
     * @ORM\Column(type="string", length=1,  name="zz_assenza_atto", nullable=true)
     */
    protected $zzAssenzaAtto;

    /**
     * @ORM\Column(type="string", length=14,  name="zz_fipos", nullable=true)
     */
    protected $zzFipos;

    /**
     * @ORM\Column(type="string", length=10,  name="zz_prenotazione", nullable=true)
     */
    protected $zzPrenotazione;

    /**
     * @ORM\Column(type="string", length=10,  name="zz_belnr_rif", nullable=true)
     */
    protected $zzBelnrRif;

    /**
     * @ORM\Column(type="smallint", name="zz_progr_rif", nullable=true)
     */
    protected $zzProgrRif;

    /**
     * @ORM\Column(type="string", length=32,  name="process_instance_id", nullable=true)
     */
    protected $processInstanceId;

    /**
     * @ORM\Column(type="string", length=10,  name="numero_proposta_impegno", nullable=true)
     */
    protected $numeroPropostaImpegno;

    /**
     * @ORM\Column(type="string", length=1,  name="it_mesg_type", nullable=true)
     */
    protected $itMesgType;

    /**
     * @ORM\Column(type="string", length=22,  name="it_mesg_message", nullable=true)
     */
    protected $itMesgMessage;

    /**
     * @ORM\Column(type="string", length=10,  name="numero_impegno", nullable=true)
     */
    protected $numeroImpegno;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return Procedura|null
     */
    public function getProcedura(): ?Procedura
    {
        return $this->procedura;
    }

    /**
     * @param $procedura
     * @return void
     */
    public function setProcedura($procedura): void
    {
        $this->procedura = $procedura;
    }

    /**
     * @return Collection|PosizioneImpegno[]
     */
    public function getPosizioniPropostaImpegno()
    {
        return $this->posizioni_proposta_impegno;
    }

    /**
     * @param Collection|PosizioneImpegno[] $posizioni_proposta_impegno
     */
    public function setPosizioniPropostaImpegno($posizioni_proposta_impegno): void
    {
        $this->posizioni_proposta_impegno = $posizioni_proposta_impegno;
    }

    /**
     * @return mixed
     */
    public function getBldat()
    {
        return $this->bldat;
    }

    /**
     * @param mixed $bldat
     */
    public function setBldat($bldat): void
    {
        $this->bldat = $bldat;
    }

    /**
     * @return mixed
     */
    public function getKtext()
    {
        return $this->ktext;
    }

    /**
     * @param mixed $ktext
     */
    public function setKtext($ktext): void
    {
        $this->ktext = $ktext;
    }

    /**
     * @return mixed
     */
    public function getBukrs()
    {
        return $this->bukrs;
    }

    /**
     * @param mixed $bukrs
     */
    public function setBukrs($bukrs): void
    {
        $this->bukrs = $bukrs;
    }

    /**
     * @return mixed
     */
    public function getBudat()
    {
        return $this->budat;
    }

    /**
     * @param mixed $budat
     */
    public function setBudat($budat): void
    {
        $this->budat = $budat;
    }

    /**
     * @return mixed
     */
    public function getZzProtocollo()
    {
        return $this->zzProtocollo;
    }

    /**
     * @param mixed $zzProtocollo
     */
    public function setZzProtocollo($zzProtocollo): void
    {
        $this->zzProtocollo = $zzProtocollo;
    }

    /**
     * @return mixed
     */
    public function getZzNumRipartiz()
    {
        return $this->zzNumRipartiz;
    }

    /**
     * @param mixed $zzNumRipartiz
     */
    public function setZzNumRipartiz($zzNumRipartiz): void
    {
        $this->zzNumRipartiz = $zzNumRipartiz;
    }

    /**
     * @return mixed
     */
    public function getZzTipoDoc()
    {
        return $this->zzTipoDoc;
    }

    /**
     * @param mixed $zzTipoDoc
     */
    public function setZzTipoDoc($zzTipoDoc): void
    {
        $this->zzTipoDoc = $zzTipoDoc;
    }

    /**
     * @return mixed
     */
    public function getZzProgrProg()
    {
        return $this->zzProgrProg;
    }

    /**
     * @param mixed $zzProgrProg
     */
    public function setZzProgrProg($zzProgrProg): void
    {
        $this->zzProgrProg = $zzProgrProg;
    }

    /**
     * @return mixed
     */
    public function getZzContrImp()
    {
        return $this->zzContrImp;
    }

    /**
     * @param mixed $zzContrImp
     */
    public function setZzContrImp($zzContrImp): void
    {
        $this->zzContrImp = $zzContrImp;
    }

    /**
     * @return mixed
     */
    public function getZzAssenzaAtto()
    {
        return $this->zzAssenzaAtto;
    }

    /**
     * @param mixed $zzAssenzaAtto
     */
    public function setZzAssenzaAtto($zzAssenzaAtto): void
    {
        $this->zzAssenzaAtto = $zzAssenzaAtto;
    }

    /**
     * @return mixed
     */
    public function getZzFipos()
    {
        return $this->zzFipos;
    }

    /**
     * @param mixed $zzFipos
     */
    public function setZzFipos($zzFipos): void
    {
        $this->zzFipos = $zzFipos;
    }

    /**
     * @return mixed
     */
    public function getZzPrenotazione()
    {
        return $this->zzPrenotazione;
    }

    /**
     * @param mixed $zzPrenotazione
     */
    public function setZzPrenotazione($zzPrenotazione): void
    {
        $this->zzPrenotazione = $zzPrenotazione;
    }

    /**
     * @return mixed
     */
    public function getZzBelnrRif()
    {
        return $this->zzBelnrRif;
    }

    /**
     * @param mixed $zzBelnrRif
     */
    public function setZzBelnrRif($zzBelnrRif): void
    {
        $this->zzBelnrRif = $zzBelnrRif;
    }

    /**
     * @return mixed
     */
    public function getZzProgrRif()
    {
        return $this->zzProgrRif;
    }

    /**
     * @param mixed $zzProgrRif
     */
    public function setZzProgrRif($zzProgrRif): void
    {
        $this->zzProgrRif = $zzProgrRif;
    }

    /**
     * @return mixed
     */
    public function getProcessInstanceId()
    {
        return $this->processInstanceId;
    }

    /**
     * @param mixed $processInstanceId
     */
    public function setProcessInstanceId($processInstanceId): void
    {
        $this->processInstanceId = $processInstanceId;
    }

    /**
     * @return mixed
     */
    public function getNumeroPropostaImpegno()
    {
        return $this->numeroPropostaImpegno;
    }

    /**
     * @param mixed $numeroPropostaImpegno
     */
    public function setNumeroPropostaImpegno($numeroPropostaImpegno): void
    {
        $this->numeroPropostaImpegno = $numeroPropostaImpegno;
    }

    /**
     * @return mixed
     */
    public function getItMesgType()
    {
        return $this->itMesgType;
    }

    /**
     * @param mixed $itMesgType
     */
    public function setItMesgType($itMesgType): void
    {
        $this->itMesgType = $itMesgType;
    }

    /**
     * @return mixed
     */
    public function getItMesgMessage()
    {
        return $this->itMesgMessage;
    }

    /**
     * @param mixed $itMesgMessage
     */
    public function setItMesgMessage($itMesgMessage): void
    {
        $this->itMesgMessage = $itMesgMessage;
    }

    /**
     * @return mixed
     */
    public function getNumeroImpegno()
    {
        return $this->numeroImpegno;
    }

    /**
     * @param mixed $numeroImpegno
     */
    public function setNumeroImpegno($numeroImpegno): void
    {
        $this->numeroImpegno = $numeroImpegno;
    }
}
