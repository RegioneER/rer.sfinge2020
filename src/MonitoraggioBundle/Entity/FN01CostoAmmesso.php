<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 07/06/17
 * Time: 12:59
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\FN01CostoAmmessoRepository")
 * @ORM\Table(name="fn01_costo_ammesso")
 */
class FN01CostoAmmesso extends EntityEsportazione {
    use Id;
    use StrutturaCancellabile;

    const CODICE_TRACCIATO = "FN01";
    const SEPARATORE = "|";

    /**
     * @ORM\ManyToOne(targetEntity="TC4Programma")
     * @ORM\JoinColumn(name="programma_id", referencedColumnName="id", nullable=true)
     */
    protected $tc4_programma;

    /**
     * @ORM\ManyToOne(targetEntity="TC36LivelloGerarchico")
     * @ORM\JoinColumn(name="liv_gerarchico_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull
     */
    protected $tc36_livello_gerarchico;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     * @Assert\NotNull
     */
    protected $cod_locale_progetto;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     */
    protected $importo_ammesso;

    /**
     * @param string $codLocaleProgetto
     * @return FN01CostoAmmesso
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
     * @param string $importoAmmesso
     * @return FN01CostoAmmesso
     */
    public function setImportoAmmesso($importoAmmesso) {
        $importo_pulito = str_replace(',', '.', $importoAmmesso);
        $this->importo_ammesso = (float) $importo_pulito;

        return $this;
    }

    /**
     * @return string
     */
    public function getImportoAmmesso() {
        return $this->importo_ammesso;
    }

    /**
     * @param C4Programma $tc4Programma
     * @return FN01CostoAmmesso
     */
    public function setTc4Programma(\MonitoraggioBundle\Entity\TC4Programma $tc4Programma = null) {
        $this->tc4_programma = $tc4Programma;

        return $this;
    }

    /**
     * @return TC4Programma
     */
    public function getTc4Programma() {
        return $this->tc4_programma;
    }

    /**
     * @param TC36LivelloGerarchico $tc36LivelloGerarchico
     * @return FN01CostoAmmesso
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

    public function getTracciato() {
        // TODO: Implement getTracciato() method.
        return  (\is_null($this->getCodLocaleProgetto()) ? "" : $this->getCodLocaleProgetto())
        . $this::SEPARATORE .
        (\is_null($this->getTc4Programma()) ? "" : $this->getTc4Programma()->getCodProgramma())
        . $this::SEPARATORE .
        (\is_null($this->getTc36LivelloGerarchico()) ? "" : $this->getTc36LivelloGerarchico()->getCodLivGerarchico())
        . $this::SEPARATORE .
        (\is_null($this->getImportoAmmesso()) ? "" : \number_format($this->getImportoAmmesso(), 2, ',', ''))
        . $this::SEPARATORE .
        (\is_null($this->getFlgCancellazione()) ? "" : $this->getFlgCancellazione());
    }
}
