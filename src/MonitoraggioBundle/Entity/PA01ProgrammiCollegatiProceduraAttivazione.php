<?php

/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 07/06/17
 * Time: 12:39.
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Table(name="pa01_programmi_collegati_procedure_attivazione")
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\PA01ProgrammiCollegatiProceduraAttivazioneRepository")
 */
class PA01ProgrammiCollegatiProceduraAttivazione extends EntityEsportazione {
    use StrutturaCancellabile;
    use Id;

    const CODICE_TRACCIATO = 'PA01';
    const SEPARATORE = '|';

    /**
     * @ORM\ManyToOne(targetEntity="TC4Programma")
     * @ORM\JoinColumn(name="programma_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio"})
     */
    protected $tc4_programma;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     * @Assert\Length(max=30, maxMessage="Il codice di procedura di attivazione non può superare i {{ limit }} caratteri", groups={"esportazione_monitoraggio"})
     */
    protected $cod_proc_att;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\GreaterThan(value=0, groups={"esportazione_monitoraggio", "Default"})
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     */
    protected $importo;

    /**
     * @return mixed
     */
    public function getTc4Programma() {
        return $this->tc4_programma;
    }

    /**
     * @param mixed $tc4_programma
     */
    public function setTc4Programma($tc4_programma): self {
        $this->tc4_programma = $tc4_programma;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCodProcAtt() {
        return $this->cod_proc_att;
    }

    /**
     * @param mixed $cod_proc_att
     */
    public function setCodProcAtt($cod_proc_att): self {
        $this->cod_proc_att = $cod_proc_att;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getImporto() {
        return $this->importo;
    }

    /**
     * @param mixed $importo
     */
    public function setImporto($importo): self {
        $importo_pulito = str_replace(',', '.', $importo);
        $this->importo = (float) $importo_pulito;
        return $this;
    }

    public function getTracciato() {
        return (\is_null($this->getCodProcAtt()) ? '' : $this->getCodProcAtt())
            . $this::SEPARATORE . (\is_null($this->getTc4Programma()) ? '' : $this->getTc4Programma()->getCodProgramma())
            . $this::SEPARATORE . (\is_null($this->getImporto()) ? '' : $this->getImporto())
            . $this::SEPARATORE . (\is_null($this->getFlgCancellazione()) ? '' : $this->getFlgCancellazione());
    }
}
