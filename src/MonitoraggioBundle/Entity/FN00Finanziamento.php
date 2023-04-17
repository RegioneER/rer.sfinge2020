<?php

/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 07/06/17
 * Time: 12:58
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\FN00FinanziamentoRepository")
 * @ORM\Table(name="fn00_finanziamento")
 */
class FN00Finanziamento extends EntityEsportazione {
    use StrutturaCancellabile;
    use Id;

    const CODICE_TRACCIATO = "FN00";
    const SEPARATORE = "|";

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="TC33FonteFinanziaria")
     * @ORM\JoinColumn(name="fondo_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc33_fonte_finanziaria;

    /**
     * @ORM\ManyToOne(targetEntity="TC35Norma")
     * @ORM\JoinColumn(name="norma_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc35_norma;

    /**
     * @ORM\ManyToOne(targetEntity="TC34DeliberaCIPE")
     * @ORM\JoinColumn(name="delibera_cipe_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc34_delibera_cipe;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max="60", maxMessage="sfinge.monitoraggio.maxLength")
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $cod_locale_progetto;

    /**
     * @ORM\ManyToOne(targetEntity="TC16LocalizzazioneGeografica")
     * @ORM\JoinColumn(name="tc16_localizzazione_geografica_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc16_localizzazione_geografica;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     * @Assert\Length(max="16", maxMessage="sfinge.monitoraggio.maxLength")
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Length(max=16, min=11, maxMessage="sfinge.monitoraggio.maxLength", minMessage="sfinge.monitoraggio.minLength")
     */
    protected $cf_cofinanz;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\GreaterThan(value=0, groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.greaterThan")
     */
    protected $importo;

    /**
     * @param string $codLocaleProgetto
     * @return FN00Finanziamento
     */
    public function setCodLocaleProgetto($codLocaleProgetto) {
        $this->cod_locale_progetto = $codLocaleProgetto;

        return $this;
    }

    /**
     * @return string
     */
    public function getCodLocaleProgetto() {
        return $this->cod_locale_progetto;
    }

    /**
     * @param string $cfCofinanz
     * @return FN00Finanziamento
     */
    public function setCfCofinanz($cfCofinanz) {
        $this->cf_cofinanz = $cfCofinanz;

        return $this;
    }

    /**
     * @return string
     */
    public function getCfCofinanz() {
        return $this->cf_cofinanz;
    }

    /**
     * @param string $importo
     * @return FN00Finanziamento
     */
    public function setImporto($importo) {
        $importo_pulito = str_replace(',', '.', $importo);
        $this->importo = (float) $importo_pulito;

        return $this;
    }

    /**
     * @return string
     */
    public function getImporto() {
        return $this->importo;
    }

    /**
     * @param \MonitoraggioBundle\Entity\TC33FonteFinanziaria $tc33FonteFinanziaria
     * @return FN00Finanziamento
     */
    public function setTc33FonteFinanziaria(\MonitoraggioBundle\Entity\TC33FonteFinanziaria $tc33FonteFinanziaria = null) {
        $this->tc33_fonte_finanziaria = $tc33FonteFinanziaria;

        return $this;
    }

    /**
     * @return \MonitoraggioBundle\Entity\TC33FonteFinanziaria
     */
    public function getTc33FonteFinanziaria() {
        return $this->tc33_fonte_finanziaria;
    }

    /**
     * @param \MonitoraggioBundle\Entity\TC35Norma $tc35Norma
     * @return FN00Finanziamento
     */
    public function setTc35Norma(\MonitoraggioBundle\Entity\TC35Norma $tc35Norma = null) {
        $this->tc35_norma = $tc35Norma;

        return $this;
    }

    /**
     * @return \MonitoraggioBundle\Entity\TC35Norma
     */
    public function getTc35Norma() {
        return $this->tc35_norma;
    }

    /**
     * @param \MonitoraggioBundle\Entity\TC34DeliberaCIPE $tc34DeliberaCipe
     * @return FN00Finanziamento
     */
    public function setTc34DeliberaCipe(\MonitoraggioBundle\Entity\TC34DeliberaCIPE $tc34DeliberaCipe = null) {
        $this->tc34_delibera_cipe = $tc34DeliberaCipe;

        return $this;
    }

    /**
     * @return \MonitoraggioBundle\Entity\TC34DeliberaCIPE
     */
    public function getTc34DeliberaCipe() {
        return $this->tc34_delibera_cipe;
    }

    /**
     * @param \MonitoraggioBundle\Entity\TC16LocalizzazioneGeografica $tc16LocalizzazioneGeografica
     * @return FN00Finanziamento
     */
    public function setTc16LocalizzazioneGeografica(\MonitoraggioBundle\Entity\TC16LocalizzazioneGeografica $tc16LocalizzazioneGeografica = null) {
        $this->tc16_localizzazione_geografica = $tc16LocalizzazioneGeografica;

        return $this;
    }

    /**
     * @return \MonitoraggioBundle\Entity\TC16LocalizzazioneGeografica
     */
    public function getTc16LocalizzazioneGeografica() {
        return $this->tc16_localizzazione_geografica;
    }

    public function getTracciato() {
        return (\is_null($this->getCodLocaleProgetto()) ? "" : $this->getCodLocaleProgetto())
            . $this::SEPARATORE . (\is_null($this->getTc33FonteFinanziaria()) ? "" : $this->getTc33FonteFinanziaria()->getCodFondo())
            . $this::SEPARATORE . (\is_null($this->getTc35Norma()) ? "" : $this->getTc35Norma()->getCodNorma())
            . $this::SEPARATORE . (\is_null($this->getTc34DeliberaCipe()) ? "" : $this->getTc34DeliberaCipe()->getCodDelCipe())
            . $this::SEPARATORE . (\is_null($this->tc16_localizzazione_geografica) ? "" : $this->tc16_localizzazione_geografica->getCodLocalizzazione())
            . $this::SEPARATORE . (\is_null($this->getCfCofinanz()) ? "" : $this->getCfCofinanz())
            . $this::SEPARATORE . (\is_null($this->getImporto()) ? "" : \number_format($this->getImporto(), 2, ',', ''))
            . $this::SEPARATORE . (\is_null($this->getFlgCancellazione()) ? "" : $this->getFlgCancellazione());
    }
}
