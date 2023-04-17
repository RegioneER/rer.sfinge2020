<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 07/06/17
 * Time: 12:56
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\SC00SoggettiCollegatiRepository")
 * @ORM\Table(name="sc00_soggetti_collegati")
 */
class SC00SoggettiCollegati extends EntityEsportazione {
    use StrutturaCancellabile;
    use Id;

    const CODICE_TRACCIATO = "SC00";
    const SEPARATORE = "|";

    /**
     * @ORM\ManyToOne(targetEntity="TC24RuoloSoggetto")
     * @ORM\JoinColumn(name="ruolo_sog_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc24_ruolo_soggetto;

    /**
     * @ORM\ManyToOne(targetEntity="TC25FormaGiuridica")
     * @ORM\JoinColumn(name="forma_giuridica_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc25_forma_giuridica;

    /**
     * @ORM\ManyToOne(targetEntity="TC26Ateco")
     * @ORM\JoinColumn(name="sett_att_economica_id", referencedColumnName="id", nullable=true)
     */
    protected $tc26_ateco;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     * @Assert\NotBlank(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $cod_locale_progetto;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     * @Assert\NotBlank(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Length(min=11, max=16, minMessage="sfinge.monitoraggio.minLength", maxMessage="sfinge.monitoraggio.maxLength")
     */
    protected $codice_fiscale;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Length(max=1, maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Regex(pattern="(S|N)", message="Campo bbligatorio con valori consentiti S/N", groups={"Default", "esportazione_monitoraggio"})
     */
    protected $flag_soggetto_pubblico;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Assert\Length(max=10, maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     */
    protected $cod_uni_ipa;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     */
    protected $denominazione_sog;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     * @Assert\length(max=1000, maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     */
    protected $note;

    /**
     * @Assert\IsTrue(message="sfinge.monitoraggio.codiceIPAObbligatorio", groups={"Default", "esportazione_monitoraggio"})
     */
    public function isCodUniIpaValid() {
        return 'N' == $this->flag_soggetto_pubblico || \strlen($this->cod_uni_ipa);
    }

    /**
     * @Assert\IsTrue(message="sfinge.monitoraggio.codiceIPACodiceFiscaleObbligatorio", groups={"Default", "esportazione_monitoraggio"})
     */
    public function isDenominazioneValid() {
        $privato = 'N' == $this->flag_soggetto_pubblico;
        $cf = 15 < \strlen($this->codice_fiscale) && '*' == \substr($this->codice_fiscale, 15, 1);
        $def = true == $this->denominazione_sog;
        return ($privato && !$cf) || $def;
    }

    /**
     * @Assert\IsTrue(message="sfinge.monitoraggio.codiceIPACodiceFiscaleObbligatorio", groups={"Default", "esportazione_monitoraggio"})
     */
    public function isTc26AtecoValid() {
        return $this->tc26_ateco || '*' != \substr($this->codice_fiscale, 15, 1);
    }

    /**
     * @return TC24RuoloSoggetto
     */
    public function getTc24RuoloSoggetto() {
        return $this->tc24_ruolo_soggetto;
    }

    /**
     * @param TC24RuoloSoggetto $tc24_ruolo_soggetto
     */
    public function setTc24RuoloSoggetto($tc24_ruolo_soggetto): self {
        $this->tc24_ruolo_soggetto = $tc24_ruolo_soggetto;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTc25FormaGiuridica() {
        return $this->tc25_forma_giuridica;
    }

    /**
     * @param mixed $tc25_forma_giuridica
     */
    public function setTc25FormaGiuridica($tc25_forma_giuridica): self {
        $this->tc25_forma_giuridica = $tc25_forma_giuridica;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTc26Ateco() {
        return $this->tc26_ateco;
    }

    /**
     * @param mixed $tc26_ateco
     */
    public function setTc26Ateco($tc26_ateco): self {
        $this->tc26_ateco = $tc26_ateco;
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
     */
    public function setCodLocaleProgetto($cod_locale_progetto): self {
        $this->cod_locale_progetto = $cod_locale_progetto;
        return $this;
    }

    /**
     * @return string
     */
    public function getCodiceFiscale() {
        return $this->codice_fiscale;
    }

    /**
     * @param string $codice_fiscale
     */
    public function setCodiceFiscale($codice_fiscale): self {
        $this->codice_fiscale = $codice_fiscale;
        return $this;
    }

    public function getFlagSoggettoPubblico(): ?string {
        return $this->flag_soggetto_pubblico;
    }

    public function setFlagSoggettoPubblico(?string $flag_soggetto_pubblico): self {
        $this->flag_soggetto_pubblico = $flag_soggetto_pubblico;
        return $this;
    }

    public function getCodUniIpa(): ?string {
        return $this->cod_uni_ipa;
    }

    public function setCodUniIpa(?string $cod_uni_ipa): self {
        $this->cod_uni_ipa = $cod_uni_ipa;
        return $this;
    }

    public function getDenominazioneSog(): ?string {
        return $this->denominazione_sog;
    }

    public function setDenominazioneSog(?string $denominazione_sog): self {
        $this->denominazione_sog = $denominazione_sog;
        return $this;
    }

    public function getNote(): ?string {
        return $this->note;
    }

    public function setNote(?string $note): self {
        $this->note = $note;
        return $this;
    }

    public function getTracciato() {
        return  (\is_null($this->cod_locale_progetto) ? "" : $this->cod_locale_progetto)
                . $this::SEPARATORE .
                (\is_null($this->tc24_ruolo_soggetto) ? "" : $this->tc24_ruolo_soggetto->getCodRuoloSog())
                . $this::SEPARATORE .
                (\is_null($this->codice_fiscale) ? "" : $this->codice_fiscale)
                . $this::SEPARATORE .
                (\is_null($this->flag_soggetto_pubblico) ? "" : $this->flag_soggetto_pubblico)
                . $this::SEPARATORE .
                (\is_null($this->cod_uni_ipa) ? "" : $this->cod_uni_ipa)
                . $this::SEPARATORE .
                (\is_null($this->denominazione_sog) ? "" : $this->denominazione_sog)
                . $this::SEPARATORE .
                (\is_null($this->tc25_forma_giuridica) ? "" : $this->tc25_forma_giuridica->getFormaGiuridica())
                . $this::SEPARATORE .
                (\is_null($this->tc26_ateco) ? "" : $this->tc26_ateco->getCodAtecoAnno())
                . $this::SEPARATORE .
                (\is_null($this->note) ? "" : $this->note)
                . $this::SEPARATORE .
                (\is_null($this->flg_cancellazione) ? "" : $this->flg_cancellazione);
    }
}
