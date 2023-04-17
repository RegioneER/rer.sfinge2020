<?php

/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 07/06/17
 * Time: 15:14
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\PR01StatoAttuazioneProgettoRepository")
 * @ORM\Table(name="pr01_stato_attuazione_progetto")
 */
class PR01StatoAttuazioneProgetto extends EntityEsportazione {
    use StrutturaCancellabile;
    use Id;
    use HasCodLocaleProgetto;

    const CODICE_TRACCIATO = "PR01";
    const SEPARATORE = "|";

    /**
     * @ORM\ManyToOne(targetEntity="TC47StatoProgetto")
     * @ORM\JoinColumn(name="stato_progetto_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc47_stato_progetto;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     * @Assert\Length(max=60, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"Default", "esportazione_monitoraggio"})
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $cod_locale_progetto;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Date(groups={"esportazione_monitoraggio", "Default"})
     */
    protected $data_riferimento;

    /**
     * @param \DateTime $dataRiferimento
     * @return PR01StatoAttuazioneProgetto
     */
    public function setDataRiferimento($dataRiferimento) {
        $this->data_riferimento = $dataRiferimento;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDataRiferimento() {
        return $this->data_riferimento;
    }

    /**
     * @param \MonitoraggioBundle\Entity\TC47StatoProgetto $tc47StatoProgetto
     * @return PR01StatoAttuazioneProgetto
     */
    public function setTc47StatoProgetto(\MonitoraggioBundle\Entity\TC47StatoProgetto $tc47StatoProgetto = null) {
        $this->tc47_stato_progetto = $tc47StatoProgetto;

        return $this;
    }

    /**
     * @return \MonitoraggioBundle\Entity\TC47StatoProgetto
     */
    public function getTc47StatoProgetto() {
        return $this->tc47_stato_progetto;
    }

    public function getTracciato() {
        return  $this->cod_locale_progetto
            . $this::SEPARATORE .
            (\is_null($this->tc47_stato_progetto) ? "" : $this->tc47_stato_progetto->getStatoProgetto())
            . $this::SEPARATORE .
            (\is_null($this->getDataRiferimento()) ? "" : $this->data_riferimento->format('d/m/Y'))
            . $this::SEPARATORE .
             $this->flg_cancellazione;
    }
}
