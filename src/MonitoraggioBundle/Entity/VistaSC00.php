<?php

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="vista_sc00")
 */
class VistaSC00 {
    use StrutturaRichiestaTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=60, nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Length(max="60", maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     *
     * @var string
     */
    protected $cod_locale_progetto;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="TC24RuoloSoggetto")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @var TC24RuoloSoggetto
     */
    protected $tc24_ruolo_soggetto;

    /**
     * @ORM\ManyToOne(targetEntity="TC25FormaGiuridica")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @var TC25FormaGiuridica
     */
    protected $tc25_forma_giuridica;

    /**
     * @ORM\ManyToOne(targetEntity="TC26Ateco")
     * @ORM\JoinColumn(nullable=true)
     * @var TC26Ateco|null
     */
    protected $tc26_ateco;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=16, nullable=true)
     * @Assert\NotBlank(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Length(min=11, max=16, minMessage="sfinge.monitoraggio.minLength", maxMessage="sfinge.monitoraggio.maxLength")
     * @var string
     */
    protected $codice_fiscale;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Length(max=1, maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Regex(pattern="(S|N)", message="Campo bbligatorio con valori consentiti S/N", groups={"Default", "esportazione_monitoraggio"})
     * @var string
     */
    protected $flag_soggetto_pubblico;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Assert\Length(max=10, maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     * @var string
     */
    protected $cod_uni_ipa;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     * @var string
     */
    protected $denominazione_sog;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     * @Assert\length(max=1000, maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     * @var string
     */
    protected $note;

    /**
     * @Assert\IsTrue(message="sfinge.monitoraggio.codiceIPAObbligatorio", groups={"Default", "esportazione_monitoraggio"})
     */
    public function isCodUniIpaValid(): bool {
        return 'N' == $this->flag_soggetto_pubblico || \strlen($this->cod_uni_ipa);
    }

    /**
     * @Assert\IsTrue(message="sfinge.monitoraggio.codiceIPACodiceFiscaleObbligatorio", groups={"Default", "esportazione_monitoraggio"})
     */
    public function isDenominazioneValid(): bool {
        $privato = 'N' == $this->flag_soggetto_pubblico;
        $cf = 15 < \strlen($this->codice_fiscale) && '*' == \substr($this->codice_fiscale, 15, 1);
        $def = true == $this->denominazione_sog;
        return ($privato && !$cf) || $def;
    }

    /**
     * @Assert\IsTrue(message="sfinge.monitoraggio.codiceIPACodiceFiscaleObbligatorio", groups={"Default", "esportazione_monitoraggio"})
     */
    public function isTc26AtecoValid(): bool {
        return $this->tc26_ateco || '*' != \substr($this->codice_fiscale, 15, 1);
    }

    public function getTc24RuoloSoggetto(): ?TC24RuoloSoggetto {
        return $this->tc24_ruolo_soggetto;
    }

    public function setTc24RuoloSoggetto(TC24RuoloSoggetto $tc24_ruolo_soggetto): self {
        $this->tc24_ruolo_soggetto = $tc24_ruolo_soggetto;
        return $this;
    }

    public function getTc25FormaGiuridica(): ?TC25FormaGiuridica {
        return $this->tc25_forma_giuridica;
    }

    public function setTc25FormaGiuridica(?TC25FormaGiuridica $tc25_forma_giuridica): self {
        $this->tc25_forma_giuridica = $tc25_forma_giuridica;
        return $this;
    }

    public function getTc26Ateco(): ?TC26Ateco {
        return $this->tc26_ateco;
    }

    public function setTc26Ateco(?TC26Ateco $tc26_ateco): self {
        $this->tc26_ateco = $tc26_ateco;
        return $this;
    }

    public function getCodLocaleProgetto(): ?string {
        return $this->cod_locale_progetto;
    }

    public function setCodLocaleProgetto(string $cod_locale_progetto): self {
        $this->cod_locale_progetto = $cod_locale_progetto;
        return $this;
    }

    public function getCodiceFiscale(): ?string {
        return $this->codice_fiscale;
    }

    public function setCodiceFiscale(string $codice_fiscale): self {
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
}
