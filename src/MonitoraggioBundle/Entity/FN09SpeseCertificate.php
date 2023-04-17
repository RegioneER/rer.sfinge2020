<?php

/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 07/06/17
 * Time: 15:07
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\FN09SpeseCertificateRepository")
 * @ORM\Table(name="fn09_spese_certificate")
 */
class FN09SpeseCertificate extends EntityEsportazione {
    use StrutturaCancellabile;
    use Id;

    const CODICE_TRACCIATO = "FN09";
    const SEPARATORE = "|";

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="TC41DomandaPagamento")
     * @ORM\JoinColumn(name="domanda_pagamento_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc41_domande_pagamento;

    /**
     * @ORM\ManyToOne(targetEntity="TC36LivelloGerarchico")
     * @ORM\JoinColumn(name="liv_gerarchico_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc36_livello_gerarchico;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     * @Assert\Length(max=60, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     * @Assert\NotNull
     */
    protected $cod_locale_progetto;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Date(groups={"esportazione_monitoraggio", "Default"})
     */
    protected $data_domanda;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     * @Assert\Length(max=1, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"esportazione_monitoraggio", "Default"})
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tipologia_importo;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\GreaterThan(value=0, groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.greaterThan")
     */
    protected $importo_spesa_tot;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\GreaterThan(value=0, groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.greaterThan")
     */
    protected $importo_spesa_pub;

    public function setCodLocaleProgetto(?string $codLocaleProgetto): self {
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
     * @param \DateTime $dataDomanda
     */
    public function setDataDomanda($dataDomanda): self {
        $this->data_domanda = $dataDomanda;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDataDomanda() {
        return $this->data_domanda;
    }

    /**
     * @param string $tipologiaImporto
     */
    public function setTipologiaImporto($tipologiaImporto): self {
        $this->tipologia_importo = $tipologiaImporto;

        return $this;
    }

    /**
     * @return string
     */
    public function getTipologiaImporto() {
        return $this->tipologia_importo;
    }

    /**
     * @param string $importoSpesaTot
     * @return FN09SpeseCertificate
     */
    public function setImportoSpesaTot($importoSpesaTot) {
        $importo_pulito = str_replace(',', '.', $importoSpesaTot);
        $this->importo_spesa_tot = (float) $importo_pulito;

        return $this;
    }

    /**
     * @return string
     */
    public function getImportoSpesaTot() {
        return $this->importo_spesa_tot;
    }

    /**
     * @param string $importoSpesaPub
     */
    public function setImportoSpesaPub($importoSpesaPub): self {
        $importo_pulito = str_replace(',', '.', $importoSpesaPub);
        $this->importo_spesa_pub = (float) $importo_pulito;

        return $this;
    }

    /**
     * @return string
     */
    public function getImportoSpesaPub() {
        return $this->importo_spesa_pub;
    }

    public function setTc41DomandePagamento(?TC41DomandaPagamento $tc41DomandePagamento = null): self {
        $this->tc41_domande_pagamento = $tc41DomandePagamento;

        return $this;
    }

    /**
     * @return \MonitoraggioBundle\Entity\TC41DomandaPagamento
     */
    public function getTc41DomandePagamento() {
        return $this->tc41_domande_pagamento;
    }

    public function setTc36LivelloGerarchico(TC36LivelloGerarchico $tc36LivelloGerarchico = null): self {
        $this->tc36_livello_gerarchico = $tc36LivelloGerarchico;

        return $this;
    }

    public function getTc36LivelloGerarchico(): ?TC36LivelloGerarchico {
        return $this->tc36_livello_gerarchico;
    }

    public function getTracciato() {
        return (\is_null($this->getCodLocaleProgetto()) ? "" : $this->getCodLocaleProgetto())
            . $this::SEPARATORE . (\is_null($this->getDataDomanda()) ? "" : $this->getDataDomanda()->format('d/m/Y'))
            . $this::SEPARATORE . (\is_null($this->getTc41DomandePagamento()) ? "" : $this->getTc41DomandePagamento()->getIdDomandaPagamento())
            . $this::SEPARATORE .
            $this->tipologia_importo
            . $this::SEPARATORE . (\is_null($this->tc36_livello_gerarchico) ? "" : $this->tc36_livello_gerarchico->getCodLivGerarchico())
            . $this::SEPARATORE . (\number_format($this->getImportoSpesaTot(), 2, ',', ''))
            . $this::SEPARATORE . (\number_format($this->getImportoSpesaPub(), 2, ',', ''))
            . $this::SEPARATORE . (\is_null($this->getFlgCancellazione()) ? "" : $this->getFlgCancellazione());
    }
}
