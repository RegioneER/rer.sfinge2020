<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 07/06/17
 * Time: 12:49
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\AP05StrumentoAttuativoRepository")
 * @ORM\Table(name="ap05_strumento_attuativo")
 */
class AP05StrumentoAttuativo extends EntityEsportazione {
    use StrutturaCancellabile;
    use Id;

    const CODICE_TRACCIATO = "AP05";
    const SEPARATORE = "|";

    /**
     * @ORM\ManyToOne(targetEntity="TC15StrumentoAttuativo")
     * @ORM\JoinColumn(name="stru_att_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc15_strumento_attuativo;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Length(max="60", maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     */
    protected $cod_locale_progetto;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     * @Assert\Length(max="1", maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Regex(pattern="(S)", match=true, message="Valore flag cancellazione non valido", groups={"esportazione_monitoraggio", "Default"})
     */
    protected $flg_cancellazione;

    /**
     * @return TC15StrumentoAttuativo
     */
    public function getTc15StrumentoAttuativo() {
        return $this->tc15_strumento_attuativo;
    }

    /**
     * @param TC15StrumentoAttuativo $tc15_strumento_attuativo
     */
    public function setTc15StrumentoAttuativo($tc15_strumento_attuativo) {
        $this->tc15_strumento_attuativo = $tc15_strumento_attuativo;
        return $this;
    }

    /**
     * @return string
     */
    public function getCodLocaleProgetto() {
        return $this->cod_locale_progetto;
    }

    /**
     * @param string $cod_locale_progetto
     */
    public function setCodLocaleProgetto($cod_locale_progetto) {
        $this->cod_locale_progetto = $cod_locale_progetto;
        return $this;
    }

    public function getTracciato() {
        // TODO: Implement getTracciato() method.
        return  (\is_null($this->cod_locale_progetto) ? "" : $this->cod_locale_progetto)
                . $this::SEPARATORE .
                (\is_null($this->tc15_strumento_attuativo) ? "" : $this->tc15_strumento_attuativo->getCodStruAtt())
                . $this::SEPARATORE .
                (\is_null($this->flg_cancellazione) ? "" : $this->flg_cancellazione);
    }
}
