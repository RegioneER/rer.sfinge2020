<?php
/**
 * @author lfontana
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use RichiesteBundle\Entity\Richiesta;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\VoceSpesaRepository")
 * @ORM\Table(name="voci_spesa")
 */
class VoceSpesa extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Richiesta", inversedBy="mon_voce_spesa")
     * @ORM\JoinColumn(name="richiesta_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull
     */
    protected $richiesta;

    /**
     * @ORM\ManyToOne(targetEntity="TC37VoceSpesa")
     * @ORM\JoinColumn(name="tipo_voce_spesa_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull
     */
    protected $tipo_voce_spesa;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=false)
     * @Assert\Regex(pattern="/^\d+.?\d*$/", match=true, message="sfinge.monitoraggio.invalidNumber")
     */
    protected $importo;

    /**
     * @param Richiesta $richiesta = null
     */
    public function __construct(Richiesta $richiesta = null) {
        $this->richiesta = $richiesta;
    }

    /**
     * Set importo
     *
     * @param string $importo
     * @return VoceSpesa
     */
    public function setImporto($importo) {
        $this->importo = $importo;

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
     * Set richiesta
     *
     * @param \RichiesteBundle\Entity\Richiesta $richiesta
     * @return VoceSpesa
     */
    public function setRichiesta(\RichiesteBundle\Entity\Richiesta $richiesta) {
        $this->richiesta = $richiesta;

        return $this;
    }

    /**
     * Get richiesta
     *
     * @return \RichiesteBundle\Entity\Richiesta
     */
    public function getRichiesta() {
        return $this->richiesta;
    }

    /**
     * Set tipo_voce_spesa
     *
     * @param \MonitoraggioBundle\Entity\TC37VoceSpesa $tipoVoceSpesa
     * @return VoceSpesa
     */
    public function setTipoVoceSpesa(\MonitoraggioBundle\Entity\TC37VoceSpesa $tipoVoceSpesa) {
        $this->tipo_voce_spesa = $tipoVoceSpesa;

        return $this;
    }

    /**
     * Get tipo_voce_spesa
     *
     * @return \MonitoraggioBundle\Entity\TC37VoceSpesa
     */
    public function getTipoVoceSpesa() {
        return $this->tipo_voce_spesa;
    }
}
