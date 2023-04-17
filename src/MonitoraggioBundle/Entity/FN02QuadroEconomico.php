<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 07/06/17
 * Time: 13:01
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\FN02QuadroEconomicoRepository")
 * @ORM\Table(name="fn02_quadro_economico")
 */
class FN02QuadroEconomico extends EntityEsportazione {
    use StrutturaCancellabile;
    use Id;

    const CODICE_TRACCIATO = "FN02";
    const SEPARATORE = "|";

    /**
     * @ORM\ManyToOne(targetEntity="TC37VoceSpesa")
     * @ORM\JoinColumn(name="voce_spesa_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc37_voce_spesa;

    /**
     * @ORM\Column(type="string", length=60, nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Length(max=60, maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     */
    protected $cod_locale_progetto;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=false)
     * @Assert\GreaterThan(value=0, groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.greaterThan")
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $importo;

    /**
     * Set cod_locale_progetto
     *
     * @param string $codLocaleProgetto
     * @return FN02QuadroEconomico
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
     * @return FN02QuadroEconomico
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
     * Set tc37_voce_spesa
     *
     * @param \MonitoraggioBundle\Entity\TC37VoceSpesa $tc37VoceSpesa
     * @return FN02QuadroEconomico
     */
    public function setTc37VoceSpesa(\MonitoraggioBundle\Entity\TC37VoceSpesa $tc37VoceSpesa) {
        $this->tc37_voce_spesa = $tc37VoceSpesa;

        return $this;
    }

    /**
     * Get tc37_voce_spesa
     *
     * @return \MonitoraggioBundle\Entity\TC37VoceSpesa
     */
    public function getTc37VoceSpesa() {
        return $this->tc37_voce_spesa;
    }

    public function getTracciato() {
        // TODO: Implement getTracciato() method.
        return  (\is_null($this->getCodLocaleProgetto()) ? "" : $this->getCodLocaleProgetto())
        . $this::SEPARATORE .
        (\is_null($this->getTc37VoceSpesa()) ? "" : $this->getTc37VoceSpesa()->getVoceSpesa())
        . $this::SEPARATORE .
        (\is_null($this->getImporto()) ? "" : \number_format($this->getImporto(), 2, ',', ''))
        . $this::SEPARATORE .
        (\is_null($this->getFlgCancellazione()) ? "" : $this->getFlgCancellazione());
    }
}
