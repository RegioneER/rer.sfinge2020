<?php

/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 07/06/17
 * Time: 15:08
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\FN10EconomieRepository")
 * @ORM\Table(name="fn10_economie")
 */
class FN10Economie extends EntityEsportazione {
    use StrutturaCancellabile;
    use Id;

    const CODICE_TRACCIATO = "FN10";
    const SEPARATORE = "|";

    /**
     * @ORM\ManyToOne(targetEntity="TC33FonteFinanziaria")
     * @ORM\JoinColumn(name="fondo_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc33_fonte_finanziaria;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     * @Assert\Length(max=60, maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $cod_locale_progetto;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\GreaterThan(value=0, groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.greaterThan")
     */
    protected $importo;

    /**
     * Set cod_locale_progetto
     *
     * @param string $codLocaleProgetto
     * @return FN10Economie
     */
    public function setCodLocaleProgetto($codLocaleProgetto) {
        $this->cod_locale_progetto = $codLocaleProgetto;

        return $this;
    }

    /**
     * Get cod_locale_progetto
     *
     * @return string
     */
    public function getCodLocaleProgetto() {
        return $this->cod_locale_progetto;
    }

    /**
     * Set importo
     *
     * @param string $importo
     * @return FN10Economie
     */
    public function setImporto($importo) {
        $importo_pulito = str_replace(',', '.', $importo);
        $this->importo = (float) $importo_pulito;

        return $this;
    }

    /**
     * Get importo
     *
     * @return string
     */
    public function getImporto() {
        return $this->importo;
    }

    /**
     * Set tc33_fonte_finanziaria
     *
     * @param \MonitoraggioBundle\Entity\TC33FonteFinanziaria $tc33FonteFinanziaria
     * @return FN10Economie
     */
    public function setTc33FonteFinanziaria(\MonitoraggioBundle\Entity\TC33FonteFinanziaria $tc33FonteFinanziaria = null) {
        $this->tc33_fonte_finanziaria = $tc33FonteFinanziaria;

        return $this;
    }

    /**
     * Get tc33_fonte_finanziaria
     *
     * @return \MonitoraggioBundle\Entity\TC33FonteFinanziaria
     */
    public function getTc33FonteFinanziaria() {
        return $this->tc33_fonte_finanziaria;
    }

    public function getTracciato() {
        // TODO: Implement getTracciato() method.
        return (\is_null($this->getCodLocaleProgetto()) ? "" : $this->getCodLocaleProgetto())
            . $this::SEPARATORE . (\is_null($this->getTc33FonteFinanziaria()) ? "" : $this->tc33_fonte_finanziaria->getCodFondo())
            . $this::SEPARATORE . (\number_format($this->importo, 2, ',', ''))
            . $this::SEPARATORE . (\is_null($this->getFlgCancellazione()) ? "" : $this->getFlgCancellazione());
    }
}
