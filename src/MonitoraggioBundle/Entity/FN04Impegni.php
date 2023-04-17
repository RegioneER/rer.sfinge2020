<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 07/06/17
 * Time: 13:03
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\FN04ImpegniRepository")
 * @ORM\Table(name="fn04_impegni")
 */
class FN04Impegni extends EntityEsportazione {
    use StrutturaCancellabile;
    use Id;

    const CODICE_TRACCIATO = "FN04";
    const SEPARATORE = "|";

    /**
     * @ORM\ManyToOne(targetEntity="TC38CausaleDisimpegno")
     * @ORM\JoinColumn(name="causale_disimpegno_id", referencedColumnName="id", nullable=true)
     */
    protected $tc38_causale_disimpegno;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Length(max=60, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"Default", "esportazione_monitoraggio"})
     */
    protected $cod_locale_progetto;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Assert\Length(max=20, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"Default", "esportazione_monitoraggio"})
     */
    protected $cod_impegno;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Assert\Length(max=5, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Regex(pattern="/^(I|D|I-TR|D-TR)$/", message="sfinge.monitoraggio.invalidValue", match=true, groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tipologia_impegno;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Date(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $data_impegno;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\GreaterThan(value=0, groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.greaterThan")
     */
    protected $importo_impegno;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     * @Assert\Length(max=1000, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"Default", "esportazione_monitoraggio"})
     */
    protected $note_impegno;

    /**
     * Assert\IsTrue(groups={"Default","esportazione_monitoraggio"})
     */
    public function isTC38TipologiaDisimpegnoValid() {
        return !is_null($this->tc38_causale_disimpegno) ||
            \in_array($this->tipologia_impegno, [
                'I',
                'I-TR',
            ]);
    }

    /**
     * @param string $codLocaleProgetto
     * @return FN04Impegni
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
     * @param string $codImpegno
     * @return FN04Impegni
     */
    public function setCodImpegno($codImpegno) {
        $this->cod_impegno = $codImpegno;

        return $this;
    }

    public function getCodImpegno(): ?string {
        return $this->cod_impegno;
    }

    public function setTipologiaImpegno(?string $tipologiaImpegno): self {
        $this->tipologia_impegno = $tipologiaImpegno;

        return $this;
    }

    public function getTipologiaImpegno(): ?string {
        return $this->tipologia_impegno;
    }

    /**
     * @param \DateTime $dataImpegno
     * @return FN04Impegni
     */
    public function setDataImpegno($dataImpegno) {
        $this->data_impegno = $dataImpegno;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDataImpegno() {
        return $this->data_impegno;
    }

    /**
     * @param string $importoImpegno
     * @return FN04Impegni
     */
    public function setImportoImpegno($importoImpegno) {
        $importo_pulito = str_replace(',', '.', $importoImpegno);
        $this->importo_impegno = (float) $importo_pulito;

        return $this;
    }

    /**
     * @return string
     */
    public function getImportoImpegno() {
        return $this->importo_impegno;
    }

    /**
     * @param string $noteImpegno
     * @return FN04Impegni
     */
    public function setNoteImpegno($noteImpegno) {
        $this->note_impegno = $noteImpegno;

        return $this;
    }

    /**
     * @return string
     */
    public function getNoteImpegno() {
        return $this->note_impegno;
    }

    /**
     * @param TC38CausaleDisimpegno $tc38CausaleDisimpegno
     * @return FN04Impegni
     */
    public function setTc38CausaleDisimpegno(TC38CausaleDisimpegno $tc38CausaleDisimpegno = null): self {
        $this->tc38_causale_disimpegno = $tc38CausaleDisimpegno;

        return $this;
    }

    /**
     * @return TC38CausaleDisimpegno
     */
    public function getTc38CausaleDisimpegno() {
        return $this->tc38_causale_disimpegno;
    }

    public function getTracciato() {
        return  (\is_null($this->getCodLocaleProgetto()) ? "" : $this->getCodLocaleProgetto())
        . $this::SEPARATORE .
        (\is_null($this->getCodImpegno()) ? "" : $this->getCodImpegno())
        . $this::SEPARATORE .
        (\is_null($this->getTipologiaImpegno()) ? "" : $this->getTipologiaImpegno())
        . $this::SEPARATORE .
        (\is_null($this->getDataImpegno()) ? "" : $this->getDataImpegno()->format('d/m/Y'))
        . $this::SEPARATORE .
        (\is_null($this->getImportoImpegno()) ? "" : \number_format($this->getImportoImpegno(), 2, ',', ''))
        . $this::SEPARATORE .
        (\is_null($this->getTc38CausaleDisimpegno()) ? "" : $this->getTc38CausaleDisimpegno()->getCausaleDisimpegno())
        . $this::SEPARATORE .
        (\is_null($this->getNoteImpegno()) ? "" : $this->getNoteImpegno())
        . $this::SEPARATORE .
        (\is_null($this->getFlgCancellazione()) ? "" : $this->getFlgCancellazione());
    }
}
