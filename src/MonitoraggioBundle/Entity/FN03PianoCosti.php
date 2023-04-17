<?php

/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 07/06/17
 * Time: 13:02
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\FN03PianoCostiRepository")
 * @ORM\Table(name="fn03_piano_costi")
 */
class FN03PianoCosti extends EntityEsportazione {
    use StrutturaCancellabile;
    use Id;

    const CODICE_TRACCIATO = "FN03";
    const SEPARATORE = "|";

    /**
     * @ORM\Column(type="string", length=60, nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Length(max=60, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $cod_locale_progetto;

    /**
     * @ORM\Column(type="integer", nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Regex(pattern="/^\d{4}$/", groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.invalidYear")
     * @Assert\LessThan(value=10000, groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.lessthan")
     * @Assert\GreaterThan(value=0, groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.greaterThan")
     */
    protected $anno_piano;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\GreaterThanOrEqual(value=0, groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.greaterThanOrEqual")
     */
    protected $imp_realizzato;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\GreaterThanOrEqual(value=0, groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.greaterThan")
     */
    protected $imp_da_realizzare;

    /**
     * @Assert\IsTrue(groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.genericViolation")
     */
    public function isImportoValid(): bool {
        return $this->imp_da_realizzare >= 0 && $this->imp_realizzato >= 0;
    }

    /**
     * @param string $codLocaleProgetto
     */
    public function setCodLocaleProgetto($codLocaleProgetto): self {
        $this->cod_locale_progetto = $codLocaleProgetto;

        return $this;
    }

    /**
     * @return string
     */
    public function getCodLocaleProgetto(): ?string {
        return $this->cod_locale_progetto;
    }

    /**
     * @param int $annoPiano
     */
    public function setAnnoPiano($annoPiano): self {
        $this->anno_piano = $annoPiano;

        return $this;
    }

    /**
     * @return int
     */
    public function getAnnoPiano() {
        return $this->anno_piano;
    }

    /**
     * @param string $impRealizzato
     */
    public function setImpRealizzato($impRealizzato): self {
        $importo_pulito = str_replace(',', '.', $impRealizzato);
        $this->imp_realizzato = (float) $importo_pulito;

        return $this;
    }

    /**
     * @return string
     */
    public function getImpRealizzato() {
        return $this->imp_realizzato;
    }

    /**
     * @param string $impDaRealizzare
     */
    public function setImpDaRealizzare($impDaRealizzare): self {
        $importo_pulito = str_replace(',', '.', $impDaRealizzare);
        $this->imp_da_realizzare = (float) $importo_pulito;

        return $this;
    }

    /**
     * Get imp_da_realizzare
     *
     * @return string
     */
    public function getImpDaRealizzare() {
        return $this->imp_da_realizzare;
    }

    public function getTracciato() {
        // TODO: Implement getTracciato() method.
        return (\is_null($this->getCodLocaleProgetto()) ? "" : $this->getCodLocaleProgetto())
            . $this::SEPARATORE .
            (\is_null($this->getAnnoPiano()) ? "" : $this->getAnnoPiano())
            . $this::SEPARATORE .
            (\is_null($this->getImpRealizzato()) ? "" : \number_format($this->getImpRealizzato(), 2, ',', ''))
            . $this::SEPARATORE .
            (\is_null($this->getImpDaRealizzare()) ? "" : \number_format($this->getImpDaRealizzare(), 2, ',', ''))
            . $this::SEPARATORE .
            (\is_null($this->getFlgCancellazione()) ? "" : $this->getFlgCancellazione());
    }
}
