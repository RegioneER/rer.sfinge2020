<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 05/06/17
 * Time: 15:19
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity
 * @ORM\Table(name="tc2_tipo_procedura_attivazione")
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC2TipoProceduraAttivazioneRepository")
 */
class TC2TipoProceduraAttivazione extends EntityLoggabileCancellabile {
    use Id;
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=2, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max="2", maxMessage="sfinge.monitoraggio.maxLength")
     * @Assert\Regex(pattern="/^\d+$/", match=true, message="Il valore deve essere un numero")
     */
    protected $tip_procedura_att;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="descrizione_tipo_procedura")
     * @Assert\Length(max="255", maxMessage="sfinge.monitoraggio.maxLength")
     */
    protected $cod_proc_att_locale;

    /**
     * @return mixed
     */
    public function getTipProceduraAtt() {
        return $this->tip_procedura_att;
    }

    /**
     * @param mixed $tip_procedura_att
     */
    public function setTipProceduraAtt($tip_procedura_att) {
        $this->tip_procedura_att = $tip_procedura_att;
    }

    /**
     * @return mixed
     */
    public function getCodProcAttLocale() {
        return $this->cod_proc_att_locale;
    }

    /**
     * @param mixed $cod_proc_att_locale
     */
    public function setCodProcAttLocale($cod_proc_att_locale) {
        $this->cod_proc_att_locale = $cod_proc_att_locale;
    }

    public function __toString() {
        return $this->tip_procedura_att . '. ' . $this->cod_proc_att_locale;
    }
}
