<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 05/06/17
 * Time: 15:24
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC3ResponsabileProceduraRepository")
 * @ORM\Table(name="tc3_responsabile_procedura")
 */
class TC3ResponsabileProcedura extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=2, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max="2", maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $cod_tipo_resp_proc;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max="255", maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $descrizione_responsabile_procedura;

    /**
     * @return mixed
     */
    public function getCodTipoRespProc() {
        return $this->cod_tipo_resp_proc;
    }

    /**
     * @param mixed $cod_tipo_resp_proc
     */
    public function setCodTipoRespProc($cod_tipo_resp_proc) {
        $this->cod_tipo_resp_proc = $cod_tipo_resp_proc;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneResponsabileProcedura() {
        return $this->descrizione_responsabile_procedura;
    }

    /**
     * @param mixed $descrizione_responsabile_procedura
     */
    public function setDescrizioneResponsabileProcedura($descrizione_responsabile_procedura) {
        $this->descrizione_responsabile_procedura = $descrizione_responsabile_procedura;
    }

    public function __toString() {
        return $this->cod_tipo_resp_proc . '. ' . $this->descrizione_responsabile_procedura;
    }
}
