<?php

/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 07/06/17
 * Time: 13:05
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\FN05ImpegniAmmessiRepository")
 * @ORM\Table(name="fn05_impegni_ammessi")
 */
class FN05ImpegniAmmessi extends EntityEsportazione {
    use StrutturaCancellabile;
    use Id;

    const CODICE_TRACCIATO = "FN05";
    const SEPARATORE = "|";

    /**
     * @ORM\ManyToOne(targetEntity="TC4Programma")
     * @ORM\JoinColumn(name="programma_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc4_programma;

    /**
     * @ORM\ManyToOne(targetEntity="TC36LivelloGerarchico")
     * @ORM\JoinColumn(name="liv_gerarchico_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc36_livello_gerarchico;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     * @Assert\Length(max=60, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"Default", "esportazione_monitoraggio"})
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $cod_locale_progetto;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Assert\Length(max=20, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"Default", "esportazione_monitoraggio"})
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $cod_impegno;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Assert\Length(max=5, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"Default", "esportazione_monitoraggio"})
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
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Date(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $data_imp_amm;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Assert\Length(max=5, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"Default", "esportazione_monitoraggio"})
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Regex(pattern="/^(I|D|I-TR|D-TR)$/", message="sfinge.monitoraggio.invalidValue", match=true, groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tipologia_imp_amm;

    /**
     * @ORM\ManyToOne(targetEntity="TC38CausaleDisimpegno")
     * @ORM\JoinColumn(name="causale_disimpegno_amm_id", referencedColumnName="id", nullable=true)
     */
    protected $tc38_causale_disimpegno_amm;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\GreaterThan(value=0, groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.greaterThan")
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $importo_imp_amm;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     * @Assert\Length(max=1000, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"Default", "esportazione_monitoraggio"})
     */
    protected $note_imp;

    /**
     * Assert\IsTrue(groups={"Default","esportazione_monitoraggio"})
     */
    public function isTC38TipologiaDisimpegnoValid() {
        return !is_null($this->tc38_causale_disimpegno_amm) ||
            \in_array($this->tipologia_imp_amm, [
                'I',
                'I-TR',
            ]);
    }

    /**
     * Set cod_locale_progetto
     *
     * @param string $codLocaleProgetto
     * @return FN05ImpegniAmmessi
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
     * Set cod_impegno
     *
     * @param string $codImpegno
     * @return FN05ImpegniAmmessi
     */
    public function setCodImpegno($codImpegno) {
        $this->cod_impegno = $codImpegno;

        return $this;
    }

    /**
     * Get cod_impegno
     *
     * @return string
     */
    public function getCodImpegno() {
        return $this->cod_impegno;
    }

    /**
     * Set tipologia_impegno
     *
     * @param string $tipologiaImpegno
     * @return FN05ImpegniAmmessi
     */
    public function setTipologiaImpegno($tipologiaImpegno) {
        $this->tipologia_impegno = $tipologiaImpegno;

        return $this;
    }

    /**
     * Get tipologia_impegno
     *
     * @return string
     */
    public function getTipologiaImpegno() {
        return $this->tipologia_impegno;
    }

    /**
     * Set data_impegno
     *
     * @param \DateTime $dataImpegno
     * @return FN05ImpegniAmmessi
     */
    public function setDataImpegno($dataImpegno) {
        $this->data_impegno = $dataImpegno;

        return $this;
    }

    /**
     * Get data_impegno
     *
     * @return \DateTime
     */
    public function getDataImpegno() {
        return $this->data_impegno;
    }

    /**
     * Set data_imp_amm
     *
     * @param \DateTime $dataImpAmm
     * @return FN05ImpegniAmmessi
     */
    public function setDataImpAmm($dataImpAmm) {
        $this->data_imp_amm = $dataImpAmm;

        return $this;
    }

    /**
     * Get data_imp_amm
     *
     * @return \DateTime
     */
    public function getDataImpAmm() {
        return $this->data_imp_amm;
    }

    /**
     * Set tipologia_imp_amm
     *
     * @param string $tipologiaImpAmm
     * @return FN05ImpegniAmmessi
     */
    public function setTipologiaImpAmm($tipologiaImpAmm) {
        $this->tipologia_imp_amm = $tipologiaImpAmm;

        return $this;
    }

    /**
     * Get tipologia_imp_amm
     *
     * @return string
     */
    public function getTipologiaImpAmm() {
        return $this->tipologia_imp_amm;
    }

    /**
     * Set importo_imp_amm
     *
     * @param string $importoImpAmm
     * @return FN05ImpegniAmmessi
     */
    public function setImportoImpAmm($importoImpAmm) {
        $importo_pulito = str_replace(',', '.', $importoImpAmm);
        $this->importo_imp_amm = (float) $importo_pulito;

        return $this;
    }

    /**
     * Get importo_imp_amm
     *
     * @return string
     */
    public function getImportoImpAmm() {
        return $this->importo_imp_amm;
    }

    /**
     * Set note_imp
     *
     * @param string $noteImp
     * @return FN05ImpegniAmmessi
     */
    public function setNoteImp($noteImp) {
        $this->note_imp = $noteImp;

        return $this;
    }

    /**
     * Get note_imp
     *
     * @return string
     */
    public function getNoteImp() {
        return $this->note_imp;
    }

    /**
     * Set tc4_programma
     *
     * @param \MonitoraggioBundle\Entity\TC4Programma $tc4Programma
     * @return FN05ImpegniAmmessi
     */
    public function setTc4Programma(\MonitoraggioBundle\Entity\TC4Programma $tc4Programma = null) {
        $this->tc4_programma = $tc4Programma;

        return $this;
    }

    /**
     * Get tc4_programma
     *
     * @return \MonitoraggioBundle\Entity\TC4Programma
     */
    public function getTc4Programma() {
        return $this->tc4_programma;
    }

    /**
     * Set tc36_livello_gerarchico
     *
     * @param \MonitoraggioBundle\Entity\TC36LivelloGerarchico $tc36LivelloGerarchico
     * @return FN05ImpegniAmmessi
     */
    public function setTc36LivelloGerarchico(\MonitoraggioBundle\Entity\TC36LivelloGerarchico $tc36LivelloGerarchico = null) {
        $this->tc36_livello_gerarchico = $tc36LivelloGerarchico;

        return $this;
    }

    /**
     * Get tc36_livello_gerarchico
     *
     * @return \MonitoraggioBundle\Entity\TC36LivelloGerarchico
     */
    public function getTc36LivelloGerarchico() {
        return $this->tc36_livello_gerarchico;
    }

    /**
     * Set tc38_causale_disimpegno_amm
     *
     * @param \MonitoraggioBundle\Entity\TC38CausaleDisimpegno $tc38CausaleDisimpegnoAmm
     * @return FN05ImpegniAmmessi
     */
    public function setTc38CausaleDisimpegnoAmm(\MonitoraggioBundle\Entity\TC38CausaleDisimpegno $tc38CausaleDisimpegnoAmm = null) {
        $this->tc38_causale_disimpegno_amm = $tc38CausaleDisimpegnoAmm;

        return $this;
    }

    /**
     * Get tc38_causale_disimpegno_amm
     *
     * @return \MonitoraggioBundle\Entity\TC38CausaleDisimpegno
     */
    public function getTc38CausaleDisimpegnoAmm() {
        return $this->tc38_causale_disimpegno_amm;
    }

    public function getTracciato() {
        // TODO: Implement getTracciato() method.

        return (\is_null($this->getCodLocaleProgetto()) ? "" : $this->getCodLocaleProgetto())
            . $this::SEPARATORE . (\is_null($this->getCodImpegno()) ? "" : $this->getCodImpegno())
            . $this::SEPARATORE . (\is_null($this->getTipologiaImpegno()) ? "" : $this->getTipologiaImpegno())
            . $this::SEPARATORE . (\is_null($this->getDataImpegno()) ? "" : $this->getDataImpegno()->format('d/m/Y'))
            . $this::SEPARATORE . (\is_null($this->getTc4Programma()) ? "" : $this->getTc4Programma()->getCodProgramma())
            . $this::SEPARATORE . (\is_null($this->getTc36LivelloGerarchico()) ? "" : $this->getTc36LivelloGerarchico()->getCodLivGerarchico())
            . $this::SEPARATORE . (\is_null($this->getDataImpAmm()) ? "" : $this->getDataImpAmm()->format('d/m/Y'))
            . $this::SEPARATORE . (\is_null($this->getTipologiaImpAmm()) ? "" : $this->getTipologiaImpAmm())
            . $this::SEPARATORE . (\is_null($this->tc38_causale_disimpegno_amm) ? "" : $this->tc38_causale_disimpegno_amm->getCausaleDisimpegno())
            . $this::SEPARATORE . (\is_null($this->getImportoImpAmm()) ? "" : \number_format($this->getImportoImpAmm(), 2, ',', ''))
            . $this::SEPARATORE . (\is_null($this->getNoteImp()) ? "" : $this->getNoteImp())
            . $this::SEPARATORE . (\is_null($this->flg_cancellazione) ? "" : $this->flg_cancellazione);
    }
}
