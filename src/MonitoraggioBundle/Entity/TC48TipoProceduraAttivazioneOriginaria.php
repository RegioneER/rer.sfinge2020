<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:57
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC48TipoProceduraAttivazioneOriginariaRepository")
 * @ORM\Table(name="tc48_tipo_procedura_attivazione_originaria")
 */
class TC48TipoProceduraAttivazioneOriginaria extends EntityLoggabileCancellabile {
    use Id;

    const NON_RILEVANTE = '5';
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=2, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=3, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $tip_proc_att_orig;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizione_tipo_procedura_orig;

    /**
     * @return mixed
     */
    public function getTipProcAttOrig() {
        return $this->tip_proc_att_orig;
    }

    /**
     * @param mixed $tip_proc_att_orig
     */
    public function setTipProcAttOrig($tip_proc_att_orig) {
        $this->tip_proc_att_orig = $tip_proc_att_orig;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneTipoProceduraOrig() {
        return $this->descrizione_tipo_procedura_orig;
    }

    /**
     * @param mixed $descrizione_tipo_procedura_orig
     */
    public function setDescrizioneTipoProceduraOrig($descrizione_tipo_procedura_orig) {
        $this->descrizione_tipo_procedura_orig = $descrizione_tipo_procedura_orig;
    }

    public function __toString() {
        return $this->tip_proc_att_orig . ' - ' . $this->descrizione_tipo_procedura_orig;
    }
}
