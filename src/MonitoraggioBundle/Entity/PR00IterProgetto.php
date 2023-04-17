<?php

/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 07/06/17
 * Time: 15:12
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\PR00IterProgettoRepository")
 * @ORM\Table(name="pr00_iter_progetto")
 */
class PR00IterProgetto extends EntityEsportazione {
    use StrutturaCancellabile;
    use Id;
    use HasCodLocaleProgetto;

    const CODICE_TRACCIATO = "PR00";
    const SEPARATORE = "|";

    /**
     * @ORM\ManyToOne(targetEntity="TC46FaseProcedurale")
     * @ORM\JoinColumn(name="fase_provedurale_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc46_fase_procedurale;

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
    protected $data_inizio_prevista;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $data_inizio_effettiva;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Date(groups={"esportazione_monitoraggio", "Default"})
     */
    protected $data_fine_prevista;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $data_fine_effettiva;

    /**
     * Set data_inizio_prevista
     *
     * @param \DateTime $dataInizioPrevista
     * @return PR00IterProgetto
     */
    public function setDataInizioPrevista($dataInizioPrevista) {
        $this->data_inizio_prevista = $dataInizioPrevista;

        return $this;
    }

    /**
     * Get data_inizio_prevista
     *
     * @return \DateTime
     */
    public function getDataInizioPrevista() {
        return $this->data_inizio_prevista;
    }

    /**
     * Set data_inizio_effettiva
     *
     * @param \DateTime $dataInizioEffettiva
     * @return PR00IterProgetto
     */
    public function setDataInizioEffettiva($dataInizioEffettiva) {
        $this->data_inizio_effettiva = $dataInizioEffettiva;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDataInizioEffettiva() {
        return $this->data_inizio_effettiva;
    }

    /**
     * @param \DateTime $dataFinePrevista
     * @return PR00IterProgetto
     */
    public function setDataFinePrevista($dataFinePrevista) {
        $this->data_fine_prevista = $dataFinePrevista;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDataFinePrevista() {
        return $this->data_fine_prevista;
    }

    /**
     * @param \DateTime $dataFineEffettiva
     * @return PR00IterProgetto
     */
    public function setDataFineEffettiva($dataFineEffettiva) {
        $this->data_fine_effettiva = $dataFineEffettiva;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDataFineEffettiva() {
        return $this->data_fine_effettiva;
    }

    /**
     * @param \MonitoraggioBundle\Entity\TC46FaseProcedurale $tc46FaseProcedurale
     * @return PR00IterProgetto
     */
    public function setTc46FaseProcedurale(\MonitoraggioBundle\Entity\TC46FaseProcedurale $tc46FaseProcedurale = null) {
        $this->tc46_fase_procedurale = $tc46FaseProcedurale;

        return $this;
    }

    /**
     * @return \MonitoraggioBundle\Entity\TC46FaseProcedurale
     */
    public function getTc46FaseProcedurale() {
        return $this->tc46_fase_procedurale;
    }

    public function getTracciato() {
        return (\is_null($this->getCodLocaleProgetto()) ? "" : $this->getCodLocaleProgetto())
            . $this::SEPARATORE .
            ($this->tc46_fase_procedurale->getCodFase())
            . $this::SEPARATORE .
            (\is_null($this->getDataInizioPrevista()) ? "" : $this->getDataInizioPrevista()->format('d/m/Y'))
            . $this::SEPARATORE .
            (\is_null($this->getDataInizioEffettiva()) ? "" : $this->getDataInizioEffettiva()->format('d/m/Y'))
            . $this::SEPARATORE .
            (\is_null($this->getDataFinePrevista()) ? "" : $this->getDataFinePrevista()->format('d/m/Y'))
            . $this::SEPARATORE .
            (\is_null($this->getDataFineEffettiva()) ? "" : $this->getDataFineEffettiva()->format('d/m/Y'))
            . $this::SEPARATORE .
            ($this->flg_cancellazione);
    }
}
