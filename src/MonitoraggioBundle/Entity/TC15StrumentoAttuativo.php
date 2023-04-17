<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 10:24
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC15StrumentoAttuativoRepository")
 * @ORM\Table(name="tc15_strumento_attuativo")
 */
class TC15StrumentoAttuativo extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=10, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $cod_stru_att;

    /**
     * @ORM\Column(type="string", length=500, nullable=true, name="descr_strumento_attuativo")
     * @Assert\Length(max=500, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $desc_strumento_attuativo;

    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     * @Assert\Length(max=500, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $denom_resp_stru_att;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Date
     */
    protected $data_approv_stru_att;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Assert\Length(max=10, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $cod_tip_stru_att;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="descr_tip_stru_att")
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $desc_tip_stru_att;

    /**
     * @var \AttuazioneControlloBundle\Entity\StrumentoAttuativo
     * @ORM\OneToMany(targetEntity="AttuazioneControlloBundle\Entity\StrumentoAttuativo", mappedBy="tc15_strumento_attuativo")
     */
    protected $strumenti_attuativi;

    public function __construct() {
        $this->strumenti_attuativi = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getCodStruAtt() {
        return $this->cod_stru_att;
    }

    /**
     * @param mixed $cod_stru_att
     */
    public function setCodStruAtt($cod_stru_att) {
        $this->cod_stru_att = $cod_stru_att;
    }

    /**
     * @return mixed
     */
    public function getDescStrumentoAttuativo() {
        return $this->desc_strumento_attuativo;
    }

    /**
     * @param mixed $desc_strumento_attuativo
     */
    public function setDescStrumentoAttuativo($desc_strumento_attuativo) {
        $this->desc_strumento_attuativo = $desc_strumento_attuativo;
    }

    /**
     * @return mixed
     */
    public function getDenomRespStruAtt() {
        return $this->denom_resp_stru_att;
    }

    /**
     * @param mixed $denom_resp_stru_att
     */
    public function setDenomRespStruAtt($denom_resp_stru_att) {
        $this->denom_resp_stru_att = $denom_resp_stru_att;
    }

    /**
     * @return mixed
     */
    public function getDataApprovStruAtt() {
        return $this->data_approv_stru_att;
    }

    /**
     * @param mixed $data_approv_stru_att
     */
    public function setDataApprovStruAtt($data_approv_stru_att) {
        $this->data_approv_stru_att = $data_approv_stru_att;
    }

    /**
     * @return mixed
     */
    public function getCodTipStruAtt() {
        return $this->cod_tip_stru_att;
    }

    /**
     * @param mixed $cod_tip_stru_att
     */
    public function setCodTipStruAtt($cod_tip_stru_att) {
        $this->cod_tip_stru_att = $cod_tip_stru_att;
    }

    /**
     * @return mixed
     */
    public function getDescTipStruAtt() {
        return $this->desc_tip_stru_att;
    }

    /**
     * @param mixed $desc_tip_stru_att
     */
    public function setDescTipStruAtt($desc_tip_stru_att) {
        $this->desc_tip_stru_att = $desc_tip_stru_att;
    }

    public function __toString() {
        return $this->cod_stru_att . ' - ' . $this->desc_strumento_attuativo;
    }

    public function getStrumentiAttuativi() {
        return $this->strumenti_attuativi;
    }

    public function setStrumentiAttuativi(\AttuazioneControlloBundle\Entity\StrumentoAttuativo $strumenti_attuativi) {
        $this->strumenti_attuativi = $strumenti_attuativi;
    }
}
