<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 07/06/17
 * Time: 12:51
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\AP06LocalizzazioneGeograficaRepository")
 * @ORM\Table(name="ap06_localizzazione_geografica")
 */
class AP06LocalizzazioneGeografica extends EntityEsportazione {
    use StrutturaCancellabile;
    use Id;

    const CODICE_TRACCIATO = "AP06";
    const SEPARATORE = "|";

    /**
     * @ORM\Column(type="string", length=60, nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Length(max="60", maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     */
    protected $cod_locale_progetto;

    /**
     * @var TC16LocalizzazioneGeografica
     * @ORM\ManyToOne(targetEntity="TC16LocalizzazioneGeografica")
     * @ORM\JoinColumn(name="tc16_localizzazione_geografica_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $localizzazioneGeografica;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     * @Assert\Length(max="1000", maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     */
    protected $indirizzo;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Assert\Length(max="5", maxMessage="sfinge.monitoraggio.maxLength", groups={"Default", "esportazione_monitoraggio"})
     */
    protected $cod_cap;

    /**
     * @return string
     */
    public function getCodLocaleProgetto() {
        return $this->cod_locale_progetto;
    }

    /**
     * @return AP06LocalizzazioneGeografica
     * @param mixed $value
     */
    public function setCodLocaleProgetto($value) {
        $this->cod_locale_progetto = $value;
        return $this;
    }

    public function getLocalizzazioneGeografica(): ?TC16LocalizzazioneGeografica {
        return $this->localizzazioneGeografica;
    }

    public function setLocalizzazioneGeografica(?TC16LocalizzazioneGeografica $localizzazioneGeografica) {
        $this->localizzazioneGeografica = $localizzazioneGeografica;
        return $this;
    }

    public function getIndirizzo(): ?string {
        return $this->indirizzo;
    }

    public function setIndirizzo(?string $indirizzo) {
        $this->indirizzo = $indirizzo;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCodCap() {
        return $this->cod_cap;
    }

    /**
     * @param mixed $cod_cap
     * @return AP06LocalizzazioneGeografica
     */
    public function setCodCap($cod_cap) {
        $this->cod_cap = $cod_cap;
        return $this;
    }

    public function getTracciato() {
        // TODO: Implement getTracciato() method.
        return  (\is_null($this->cod_locale_progetto) ? "" : $this->cod_locale_progetto)
        . $this::SEPARATORE .
        (\is_null($this->localizzazioneGeografica->getCodiceRegione()) ? "" : $this->localizzazioneGeografica->getCodiceRegione())
        . $this::SEPARATORE .
        (\is_null($this->localizzazioneGeografica->getCodiceProvincia()) ? "" : $this->localizzazioneGeografica->getCodiceProvincia())
        . $this::SEPARATORE .
        (\is_null($this->localizzazioneGeografica->getCodiceComune()) ? "" : $this->localizzazioneGeografica->getCodiceComune())
        . $this::SEPARATORE .
        (\is_null($this->indirizzo) ? "" : $this->indirizzo)
        . $this::SEPARATORE .
        (\is_null($this->cod_cap) ? "" : $this->cod_cap)
        . $this::SEPARATORE .
        (\is_null($this->flg_cancellazione) ? "" : $this->flg_cancellazione);
    }
}
