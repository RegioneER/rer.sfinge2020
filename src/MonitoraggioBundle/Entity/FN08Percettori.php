<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 07/06/17
 * Time: 15:06
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\FN08PercettoriRepository")
 * @ORM\Table(name="fn08_percettori")
 */
class FN08Percettori extends EntityEsportazione {
    use StrutturaCancellabile;
    use Id;

    const CODICE_TRACCIATO = "FN08";
    const SEPARATORE = "|";

    /**
     * @ORM\ManyToOne(targetEntity="TC40TipoPercettore")
     * @ORM\JoinColumn(name="tipo_percettore_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc40_tipo_percettore;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Length(max=60, maxMessage="sfinge.monitoraggio.maxLength")
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $cod_locale_progetto;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Length(max=20, maxMessage="sfinge.monitoraggio.maxLength")
     */
    protected $cod_pagamento;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Length(max=20, maxMessage="sfinge.monitoraggio.maxLength")
     * @Assert\Regex(pattern="/^(P|P-TR|R|R-TR)$/", match=true, message="sfinge.monitoraggio.invalidValue")
     */
    protected $tipologia_pag;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Date(groups={"esportazione_monitoraggio", "Default"})
     */
    protected $data_pagamento;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Length(min=1, minMessage="sfinge.monitoraggio.minLength")
     */
    protected $codice_fiscale;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     * @Assert\Regex(pattern="/^(S|N)$/", match=true, message="sfinge.monitoraggio.invalidValue")
     */
    protected $flag_soggetto_pubblico;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\GreaterThan(value=0, groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.greaterThan")
     */
    protected $importo;

    /**
     * @param string $codLocaleProgetto
     * @return FN08Percettori
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
     * @param string $codPagamento
     */
    public function setCodPagamento($codPagamento): self {
        $this->cod_pagamento = $codPagamento;

        return $this;
    }

    /**
     * @return string
     */
    public function getCodPagamento() {
        return $this->cod_pagamento;
    }

    /**
     * Set tipologia_pag
     *
     * @param string $tipologiaPag
     */
    public function setTipologiaPag($tipologiaPag): self {
        $this->tipologia_pag = $tipologiaPag;

        return $this;
    }

    /**
     * @return string
     */
    public function getTipologiaPag() {
        return $this->tipologia_pag;
    }

    /**
     * @param \DateTime $dataPagamento
     */
    public function setDataPagamento($dataPagamento): self {
        $this->data_pagamento = $dataPagamento;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDataPagamento() {
        return $this->data_pagamento;
    }

    /**
     * @param string $codiceFiscale
     */
    public function setCodiceFiscale($codiceFiscale): self {
        $this->codice_fiscale = $codiceFiscale;

        return $this;
    }

    /**
     * @return string
     */
    public function getCodiceFiscale() {
        return $this->codice_fiscale;
    }

    /**
     * @param string $flagSoggettoPubblico
     */
    public function setFlagSoggettoPubblico($flagSoggettoPubblico): self {
        $this->flag_soggetto_pubblico = $flagSoggettoPubblico;

        return $this;
    }

    /**
     * @return string
     */
    public function getFlagSoggettoPubblico() {
        return $this->flag_soggetto_pubblico;
    }

    /**
     * @param string $importo
     */
    public function setImporto($importo): self {
        $importo_pulito = str_replace(',', '.', $importo);
        $this->importo = (float) $importo_pulito;

        return $this;
    }

    /**
     * @return string
     */
    public function getImporto() {
        return $this->importo;
    }

    public function setTc40TipoPercettore(?TC40TipoPercettore $tc40TipoPercettore = null): self {
        $this->tc40_tipo_percettore = $tc40TipoPercettore;

        return $this;
    }

    public function getTc40TipoPercettore(): ?TC40TipoPercettore {
        return $this->tc40_tipo_percettore;
    }

    public function getTracciato() {
        return  (\is_null($this->getCodLocaleProgetto()) ? "" : $this->getCodLocaleProgetto())
                . $this::SEPARATORE .
                (\is_null($this->getCodPagamento()) ? "" : $this->getCodPagamento())
                . $this::SEPARATORE .
                (\is_null($this->getTipologiaPag()) ? "" : $this->getTipologiaPag())
                . $this::SEPARATORE .
                (\is_null($this->getDataPagamento()) ? "" : $this->getDataPagamento()->format('d/m/Y'))
                . $this::SEPARATORE .
                (\is_null($this->getCodiceFiscale()) ? "" : $this->getCodiceFiscale())
                . $this::SEPARATORE .
                (\is_null($this->getFlagSoggettoPubblico()) ? "" : $this->getFlagSoggettoPubblico())
                . $this::SEPARATORE .
                (\is_null($this->getTc40TipoPercettore()) ? "" : $this->getTc40TipoPercettore()->getTipoPercettore())
                . $this::SEPARATORE .
                (\is_null($this->getImporto()) ? "" : \number_format($this->getImporto(), 2, ',', ''))
                . $this::SEPARATORE .
                (\is_null($this->getFlgCancellazione()) ? "" : $this->getFlgCancellazione());
    }

    public function __toString() {
        return $this->getCodLocaleProgetto() . ' - ' . $this->getCodPagamento() . ': ' . \number_format($this->getImporto(), 2, ',', '.');
    }
}
