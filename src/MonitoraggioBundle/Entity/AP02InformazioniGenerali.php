<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 07/06/17
 * Time: 12:46
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\AP02InformazioniGeneraliRepository")
 * @ORM\Table(name="ap02_informazioni_generali")
 */
class AP02InformazioniGenerali extends EntityEsportazione {
    use StrutturaCancellabile;
    use Id;

    const CODICE_TRACCIATO = "AP02";
    const SEPARATORE = "|";

    /**
     * @var TC7ProgettoComplesso|null
     * @ORM\ManyToOne(targetEntity="TC7ProgettoComplesso")
     * @ORM\JoinColumn(name="prg_complesso_id", referencedColumnName="id", nullable=true)
     */
    protected $tc7_progetto_complesso;

    /**
     * @ORM\ManyToOne(targetEntity="TC8GrandeProgetto")
     * @ORM\JoinColumn(name="grande_progetto_id", referencedColumnName="id", nullable=true)
     */
    protected $tc8_grande_progetto;

    /**
     * @var TC9TipoLivelloIstituzione|null
     * @ORM\ManyToOne(targetEntity="TC9TipoLivelloIstituzione")
     * @ORM\JoinColumn(name="liv_istituzione_str_fin_id", referencedColumnName="id", nullable=true)
     */
    protected $tc9_tipo_livello_istituzione;

    /**
     * @var TC10TipoLocalizzazione|null
     * @ORM\ManyToOne(targetEntity="TC10TipoLocalizzazione")
     * @ORM\JoinColumn(name="tipo_localizzazione_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc10_tipo_localizzazione;

    /**
     * @var TC13GruppoVulnerabileProgetto|null
     * @ORM\ManyToOne(targetEntity="TC13GruppoVulnerabileProgetto")
     * @ORM\JoinColumn(name="vulnerabili_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc13_gruppo_vulnerabile_progetto;

    /**
     * @var string
     * @ORM\Column(type="string", length=60, nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Length(max="60", maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     */
    protected $cod_locale_progetto;

    /**
     * @var string
     * @ORM\Column(type="string", length=1, nullable=false)
     * @Assert\Length(max="1", maxMessage="sfinge.monitoraggio.maxLength")
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Regex(pattern="(S|N)", match=true, message="Valore flag cancellazione non valido", groups={"esportazione_monitoraggio", "Default"})
     */
    protected $generatore_entrate;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=1, nullable=true)
     * @Assert\Length(max="1", maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Regex(pattern="(S|N)", match=true, message="Valore flag cancellazione non valido", groups={"esportazione_monitoraggio", "Default"})
     */
    protected $fondo_di_fondi;

    /**
     * @Assert\IsTrue(groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.fondoDiFondiObbligatorio")
     */
    public function isFondoDiFondiObbligatorioValid() {
        return is_null($this->tc9_tipo_livello_istituzione) ||
            !\in_array($this->tc9_tipo_livello_istituzione->getLivIstituzioneStrFin(), [2, 3]) ||
            !empty($this->fondo_di_fondi);
    }

    /**
     * @return mixed
     */
    public function getTc7ProgettoComplesso() {
        return $this->tc7_progetto_complesso;
    }

    /**
     * @param mixed $tc7_progetto_complesso
     * @return $this
     */
    public function setTc7ProgettoComplesso($tc7_progetto_complesso) {
        $this->tc7_progetto_complesso = $tc7_progetto_complesso;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTc8GrandeProgetto() {
        return $this->tc8_grande_progetto;
    }

    /**
     * @param mixed $tc8_grande_progetto
     * @return $this
     */
    public function setTc8GrandeProgetto($tc8_grande_progetto) {
        $this->tc8_grande_progetto = $tc8_grande_progetto;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTc9TipoLivelloIstituzione() {
        return $this->tc9_tipo_livello_istituzione;
    }

    /**
     * @param mixed $tc9_tipo_livello_istituzione
     * @return $this
     */
    public function setTc9TipoLivelloIstituzione($tc9_tipo_livello_istituzione) {
        $this->tc9_tipo_livello_istituzione = $tc9_tipo_livello_istituzione;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTc10TipoLocalizzazione() {
        return $this->tc10_tipo_localizzazione;
    }

    /**
     * @param mixed $tc10_tipo_localizzazione
     * @return $this
     */
    public function setTc10TipoLocalizzazione($tc10_tipo_localizzazione) {
        $this->tc10_tipo_localizzazione = $tc10_tipo_localizzazione;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTc13GruppoVulnerabileProgetto() {
        return $this->tc13_gruppo_vulnerabile_progetto;
    }

    /**
     * @param mixed $tc13_gruppo_vulnerabile_progetto
     * @return $this
     */
    public function setTc13GruppoVulnerabileProgetto($tc13_gruppo_vulnerabile_progetto) {
        $this->tc13_gruppo_vulnerabile_progetto = $tc13_gruppo_vulnerabile_progetto;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCodLocaleProgetto() {
        return $this->cod_locale_progetto;
    }

    /**
     * @param mixed $cod_locale_progetto
     * @return $this
     */
    public function setCodLocaleProgetto($cod_locale_progetto) {
        $this->cod_locale_progetto = $cod_locale_progetto;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGeneratoreEntrate() {
        return $this->generatore_entrate;
    }

    /**
     * @param mixed $generatore_entrate
     * @return $this
     */
    public function setGeneratoreEntrate($generatore_entrate) {
        $this->generatore_entrate = $generatore_entrate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFondoDiFondi() {
        return $this->fondo_di_fondi;
    }

    /**
     * @param mixed $fondo_di_fondi
     * @return $this
     */
    public function setFondoDiFondi($fondo_di_fondi) {
        $this->fondo_di_fondi = $fondo_di_fondi;
        return $this;
    }

    public function getTracciato() {
        // TODO: Implement getTracciato() method.
        return  (\is_null($this->getCodLocaleProgetto()) ? "" : $this->getCodLocaleProgetto())
        . $this::SEPARATORE .
        (\is_null($this->getTc7ProgettoComplesso()) ? "" : $this->getTc7ProgettoComplesso()->getCodPrgComplesso())
        . $this::SEPARATORE .
        (\is_null($this->getTc8GrandeProgetto()) ? "" : $this->getTc8GrandeProgetto()->getGrandeProgetto())
        . $this::SEPARATORE .
        (\is_null($this->getGeneratoreEntrate()) ? "" : $this->getGeneratoreEntrate())
        . $this::SEPARATORE .
        (\is_null($this->getTc9TipoLivelloIstituzione()) ? "" : $this->getTc9TipoLivelloIstituzione()->getLivIstituzioneStrFin())
        . $this::SEPARATORE .
        (\is_null($this->getFondoDiFondi()) ? "" : $this->getFondoDiFondi())
        . $this::SEPARATORE .
        (\is_null($this->getTc10TipoLocalizzazione()) ? "" : $this->getTc10TipoLocalizzazione()->getTipoLocalizzazione())
        . $this::SEPARATORE .
        (\is_null($this->getTc13GruppoVulnerabileProgetto()) ? "" : $this->getTc13GruppoVulnerabileProgetto()->getCodVulnerabili())
        . $this::SEPARATORE .
        (\is_null($this->getFlgCancellazione()) ? "" : $this->getFlgCancellazione());
    }
}
