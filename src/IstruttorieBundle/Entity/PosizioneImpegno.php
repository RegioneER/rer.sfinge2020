<?php
namespace IstruttorieBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use RichiesteBundle\Entity\Richiesta;

/**
 * @ORM\Entity(repositoryClass="IstruttorieBundle\Entity\PropostaImpegnoRepository")
 * @ORM\Table(name="posizioni_impegni")
 */
class PosizioneImpegno extends EntityLoggabileCancellabile
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
     * @ORM\ManyToOne(targetEntity="IstruttorieBundle\Entity\PropostaImpegno", inversedBy="posizioni_proposta_impegno")
     * @ORM\JoinColumn(name="proposta_impegno", referencedColumnName="id", nullable=false)
     * @var PropostaImpegno
     */
    protected $proposta_impegno;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="posizioni_impegni")
     * @ORM\JoinColumn(name="richiesta_id", referencedColumnName="id", nullable=false)
     * @var Richiesta
     */
    protected $richiesta;

    /**
     * @ORM\Column(type="string", length=50,  name="ptext", nullable=true)
     */
    protected $ptext;

    /**
     * @ORM\Column(type="string", length=10,  name="lifnr", nullable=false)
     */
    protected $lifnr;

    /**
     * @ORM\Column(type="string", length=15,  name="zz_cup", nullable=true)
     */
    protected $zzCup;

    /**
     * @ORM\Column(type="string", length=10,  name="zz_cig", nullable=true)
     */
    protected $zzCig;

    /**
     * @ORM\Column(type="string", length=3,  name="zz_livello5", nullable=false)
     */
    protected $zzLivello5;

    /**
     * @ORM\Column(type="string", length=5,  name="zz_cod_form_av", nullable=true)
     */
    protected $zzCodFormAv;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, name="wtges", nullable=false)
     */
    protected $wtges;

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
     * @return PropostaImpegno
     */
    public function getPropostaImpegno(): PropostaImpegno
    {
        return $this->proposta_impegno;
    }

    /**
     * @param PropostaImpegno $proposta_impegno
     */
    public function setPropostaImpegno(PropostaImpegno $proposta_impegno): void
    {
        $this->proposta_impegno = $proposta_impegno;
    }

    /**
     * @return Richiesta|null
     */
    public function getRichiesta(): ?Richiesta
    {
        return $this->richiesta;
    }

    /**
     * @param Richiesta $richiesta
     * @return void
     */
    public function setRichiesta(Richiesta $richiesta): void
    {
        $this->richiesta = $richiesta;
    }

    /**
     * @return mixed
     */
    public function getPtext()
    {
        return $this->ptext;
    }

    /**
     * @param mixed $ptext
     */
    public function setPtext($ptext): void
    {
        $this->ptext = $ptext;
    }

    /**
     * @return mixed
     */
    public function getLifnr()
    {
        return $this->lifnr;
    }

    /**
     * @param mixed $lifnr
     */
    public function setLifnr($lifnr): void
    {
        $this->lifnr = $lifnr;
    }

    /**
     * @return mixed
     */
    public function getZzCup()
    {
        return $this->zzCup;
    }

    /**
     * @param mixed $zzCup
     */
    public function setZzCup($zzCup): void
    {
        $this->zzCup = $zzCup;
    }

    /**
     * @return mixed
     */
    public function getZzCig()
    {
        return $this->zzCig;
    }

    /**
     * @param mixed $zzCig
     */
    public function setZzCig($zzCig): void
    {
        $this->zzCig = $zzCig;
    }

    /**
     * @return mixed
     */
    public function getZzLivello5()
    {
        return $this->zzLivello5;
    }

    /**
     * @param mixed $zzLivello5
     */
    public function setZzLivello5($zzLivello5): void
    {
        $this->zzLivello5 = $zzLivello5;
    }

    /**
     * @return mixed
     */
    public function getZzCodFormAv()
    {
        return $this->zzCodFormAv;
    }

    /**
     * @param mixed $zzCodFormAv
     */
    public function setZzCodFormAv($zzCodFormAv): void
    {
        $this->zzCodFormAv = $zzCodFormAv;
    }

    /**
     * @return mixed
     */
    public function getWtges()
    {
        return $this->wtges;
    }

    /**
     * @param mixed $wtges
     */
    public function setWtges($wtges): void
    {
        $this->wtges = $wtges;
    }
}
