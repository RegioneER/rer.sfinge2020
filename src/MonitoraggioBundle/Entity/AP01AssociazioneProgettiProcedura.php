<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 07/06/17
 * Time: 12:45
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity
 * @ORM\Table(name="ap01_associazione_progetti_procedura")
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\AP01AssociazioneProgettiProceduraRepository")
 */
class AP01AssociazioneProgettiProcedura extends EntityEsportazione {
    use StrutturaCancellabile;
    use Id;

    const CODICE_TRACCIATO = "AP01";
    const SEPARATORE = "|";

    /**
     * @ORM\ManyToOne(targetEntity="TC1ProceduraAttivazione")
     * @ORM\JoinColumn(name="proc_att_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc1_procedura_attivazione;

    /**
     * @ORM\Column(type="string", length=60, nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Length(max="60", maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     */
    protected $cod_locale_progetto;

    /**
     * @return TC1ProceduraAttivazione
     */
    public function getTc1ProceduraAttivazione() {
        return $this->tc1_procedura_attivazione;
    }

    /**
     * @param TC1ProceduraAttivazione $tc1_procedura_attivazione
     * @return AP01AssociazioneProgettiProcedura
     */
    public function setTc1ProceduraAttivazione($tc1_procedura_attivazione) {
        $this->tc1_procedura_attivazione = $tc1_procedura_attivazione;
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
     * @return AP01AssociazioneProgettiProcedura
     */
    public function setCodLocaleProgetto($cod_locale_progetto) {
        $this->cod_locale_progetto = $cod_locale_progetto;
        return $this;
    }

    public function getTracciato() {
        // TODO: Implement getTracciato() method.
        return  (\is_null($this->getCodLocaleProgetto()) ? "" : $this->getCodLocaleProgetto())
        . self::SEPARATORE .
        (\is_null($this->tc1_procedura_attivazione) ? "" : $this->tc1_procedura_attivazione->getCodProcAtt())
        . self::SEPARATORE .
        (\is_null($this->getFlgCancellazione()) ? "" : $this->getFlgCancellazione());
    }
}
