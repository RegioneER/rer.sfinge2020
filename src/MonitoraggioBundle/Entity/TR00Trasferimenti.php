<?php

/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 07/06/17
 * Time: 12:41
 */

namespace MonitoraggioBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TR00TrasferimentiRepository")
 * @ORM\Table(name="tr00_trasferimenti")
 */
class TR00Trasferimenti extends EntityEsportazione {
    use Id;
    use StrutturaCancellabile;

    const CODICE_TRACCIATO = "TR00";
    const SEPARATORE = '|';

    /**
     * @ORM\ManyToOne(targetEntity="TC4Programma")
     * @ORM\JoinColumn(name="programma_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     */
    protected $tc4_programma;

    /**
     * @ORM\ManyToOne(targetEntity="TC49CausaleTrasferimento")
     * @ORM\JoinColumn(name="causale_trasferimento_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     */
    protected $tc49_causale_trasferimento;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Length(max="60", maxMessage="sfinge.monitoraggio.maxLength", groups={"esportazione_monitoraggio", "Default"})
     */
    protected $cod_trasferimento;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Date(groups={"esportazione_monitoraggio", "Default"})
     */
    protected $data_trasferimento;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\Regex(pattern="/^\d+.?\d*$/", match=true, message="Formato non valido", groups={"esportazione_monitoraggio", "Default"})
     * @Assert\GreaterThan(value=0, groups={"esportazione_monitoraggio", "Default"})
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     */
    protected $importo_trasferimento;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Length(min="11", max="16", maxMessage="Il valore deve essere di {{ limit }} caratteri", minMessage="Il valore deve essere di {{ limit }} caratteri", groups={"Default", "esportazione_monitoraggio"})
     */
    protected $cf_sog_ricevente;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     * @Assert\Length(min=1, max="1", minMessage="sfinge.monitoraggio.minLength", maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     * @Assert\RegEx(pattern="(S|N)", match=true, message="Valore flag soggetto pubblico non valido", groups={"esportazione_monitoraggio", "Default"})
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     */
    protected $flag_soggetto_pubblico;

    /**
     * @return mixed
     */
    public function getTc4Programma() {
        return $this->tc4_programma;
    }

    /**
     * @param mixed $tc4_programma
     * @return TR00Trasferimenti
     */
    public function setTc4Programma($tc4_programma) {
        $this->tc4_programma = $tc4_programma;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTc49CausaleTrasferimento() {
        return $this->tc49_causale_trasferimento;
    }

    /**
     * @param mixed $tc49_causale_trasferimento
     * @return TR00Trasferimenti
     */
    public function setTc49CausaleTrasferimento($tc49_causale_trasferimento) {
        $this->tc49_causale_trasferimento = $tc49_causale_trasferimento;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCodTrasferimento() {
        return $this->cod_trasferimento;
    }

    /**
     * @param mixed $cod_trasferimento
     * @return TR00Trasferimenti
     */
    public function setCodTrasferimento($cod_trasferimento) {
        $this->cod_trasferimento = $cod_trasferimento;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDataTrasferimento() {
        return $this->data_trasferimento;
    }

    /**
     * @param mixed $data_trasferimento
     * @return TR00Trasferimenti
     */
    public function setDataTrasferimento($data_trasferimento) {
        $this->data_trasferimento = $data_trasferimento;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getImportoTrasferimento() {
        return $this->importo_trasferimento;
    }

    /**
     * @param mixed $importo_trasferimento
     * @return TR00Trasferimenti
     */
    public function setImportoTrasferimento($importo_trasferimento) {
        $importo_pulito = str_replace(',', '.', $importo_trasferimento);
        $this->importo_trasferimento = (float) $importo_pulito;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCfSogRicevente() {
        return $this->cf_sog_ricevente;
    }

    /**
     * @param mixed $cf_sog_ricevente
     * @return TR00Trasferimenti
     */
    public function setCfSogRicevente($cf_sog_ricevente) {
        $this->cf_sog_ricevente = $cf_sog_ricevente;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFlagSoggettoPubblico() {
        return $this->flag_soggetto_pubblico;
    }

    /**
     * @param mixed $flag_soggetto_pubblico
     * @return TR00Trasferimenti
     */
    public function setFlagSoggettoPubblico($flag_soggetto_pubblico) {
        $this->flag_soggetto_pubblico = $flag_soggetto_pubblico;
        return $this;
    }

    public function getTracciato() {
        return (\is_null($this->getCodTrasferimento()) ? "" : $this->getCodTrasferimento())
            . self::SEPARATORE . (\is_null($this->getDataTrasferimento()) ? "" : $this->getDataTrasferimento()->format('d/m/Y'))
            . self::SEPARATORE . (\is_null($this->getTc4Programma()->getCodProgramma()) ? "" : $this->getTc4Programma()->getCodProgramma())
            . self::SEPARATORE . (\is_null($this->getTc49CausaleTrasferimento()) ? "" : $this->getTc49CausaleTrasferimento()->getCausaleTrasferimento())
            . self::SEPARATORE . (\is_null($this->getImportoTrasferimento()) ? "" : \number_format($this->getImportoTrasferimento(), 2, ',', ''))
            . self::SEPARATORE . (\is_null($this->getCfSogRicevente()) ? "" : $this->getCfSogRicevente())
            . self::SEPARATORE . (\is_null($this->getFlagSoggettoPubblico()) ? "" : $this->getFlagSoggettoPubblico())
            . self::SEPARATORE . (\is_null($this->getFlgCancellazione()) ? "" : $this->getFlgCancellazione());
    }
}
