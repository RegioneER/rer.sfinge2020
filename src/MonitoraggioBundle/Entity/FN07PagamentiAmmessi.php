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
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\FN07PagamentiAmmessiRepository")
 * @ORM\Table(name="fn07_pagamenti_ammessi")
 */
class FN07PagamentiAmmessi extends EntityEsportazione {
    use StrutturaCancellabile;
    use Id;

    const CODICE_TRACCIATO = "FN07";
    const SEPARATORE = "|";

    /**
     * @ORM\ManyToOne(targetEntity="TC4Programma")
     * @ORM\JoinColumn(name="programma_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc4_programma;

    /**
     * @ORM\ManyToOne(targetEntity="TC39CausalePagamento")
     * @ORM\JoinColumn(name="causale_pag_amm_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc39_causale_pagamento;

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
     * @Assert\Length(max=20, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $cod_pagamento;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Assert\Length(max=5, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"esportazione_monitoraggio", "Default"})
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tipologia_pag;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Date(groups={"esportazione_monitoraggio", "Default"})
     */
    protected $data_pagamento;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\NotNull(groups={"esportazione_monitoraggio", "Default"})
     * @Assert\Date(groups={"esportazione_monitoraggio", "Default"})
     */
    protected $data_pag_amm;

    /**
     * @ORM\Column(type="string", length=5, nullable=false)
     * @Assert\Length(max=5, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"esportazione_monitoraggio", "Default"})
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tipologia_pag_amm;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\GreaterThan(value=0, groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.greaterThan")
     */
    protected $importo_pag_amm;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     * @Assert\Length(max=1000, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"esportazione_monitoraggio", "Default"})
     */
    protected $note_pag;

    /**
     * @param string $codLocaleProgetto
     * @return FN07PagamentiAmmessi
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
     * @return \DateTime
     */
    public function getDataPagamento() {
        return $this->data_pagamento;
    }

    /**
     * @param \DateTime $dataPagAmm
     */
    public function setDataPagAmm($dataPagAmm): self {
        $this->data_pag_amm = $dataPagAmm;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDataPagAmm() {
        return $this->data_pag_amm;
    }

    /**
     * @param string $tipologiaPagAmm
     */
    public function setTipologiaPagAmm($tipologiaPagAmm): self {
        $this->tipologia_pag_amm = $tipologiaPagAmm;

        return $this;
    }

    /**
     * @return string
     */
    public function getTipologiaPagAmm() {
        return $this->tipologia_pag_amm;
    }

    /**
     * @param string $importoPagAmm
     */
    public function setImportoPagAmm($importoPagAmm): self {
        $importo_pulito = str_replace(',', '.', $importoPagAmm);
        $this->importo_pag_amm = (float) $importo_pulito;

        return $this;
    }

    /**
     * @return string
     */
    public function getImportoPagAmm() {
        return $this->importo_pag_amm;
    }

    /**
     * @param string $notePag
     */
    public function setNotePag($notePag): self {
        $this->note_pag = $notePag;

        return $this;
    }

    /**
     * @return string
     */
    public function getNotePag() {
        return $this->note_pag;
    }

    /**
     * @param \MonitoraggioBundle\Entity\TC4Programma $tc4Programma
     */
    public function setTc4Programma(\MonitoraggioBundle\Entity\TC4Programma $tc4Programma = null): self {
        $this->tc4_programma = $tc4Programma;

        return $this;
    }

    /**
     * @return \MonitoraggioBundle\Entity\TC4Programma
     */
    public function getTc4Programma() {
        return $this->tc4_programma;
    }

    /**
     * @param \MonitoraggioBundle\Entity\TC39CausalePagamento $tc39CausalePagamento
     */
    public function setTc39CausalePagamento(TC39CausalePagamento $tc39CausalePagamento): self {
        $this->tc39_causale_pagamento = $tc39CausalePagamento;

        return $this;
    }

    /**
     * @return \MonitoraggioBundle\Entity\TC39CausalePagamento
     */
    public function getTc39CausalePagamento() {
        return $this->tc39_causale_pagamento;
    }

    /**
     * @return FN07PagamentiAmmessi
     */
    public function setTc36LivelloGerarchico(TC36LivelloGerarchico $tc36LivelloGerarchico = null): self {
        $this->tc36_livello_gerarchico = $tc36LivelloGerarchico;

        return $this;
    }

    /**
     * @return \MonitoraggioBundle\Entity\TC36LivelloGerarchico
     */
    public function getTc36LivelloGerarchico() {
        return $this->tc36_livello_gerarchico;
    }

    public function getTracciato() {
        return (\is_null($this->getCodLocaleProgetto()) ? "" : $this->getCodLocaleProgetto())
            . $this::SEPARATORE . (\is_null($this->getCodPagamento()) ? "" : $this->getCodPagamento())
            . $this::SEPARATORE . (\is_null($this->getTipologiaPag()) ? "" : $this->getTipologiaPag())
            . $this::SEPARATORE . (\is_null($this->getDataPagamento()) ? "" : $this->getDataPagamento()->format('d/m/Y'))
            . $this::SEPARATORE . (\is_null($this->getTc4Programma()) ? "" : $this->getTc4Programma()->getCodProgramma())
            . $this::SEPARATORE . (\is_null($this->getTc36LivelloGerarchico()) ? "" : $this->getTc36LivelloGerarchico()->getCodLivGerarchico())
            . $this::SEPARATORE . (\is_null($this->getDataPagAmm()) ? "" : $this->getDataPagAmm()->format('d/m/Y'))
            . $this::SEPARATORE . (\is_null($this->getTipologiaPagAmm()) ? "" : $this->getTipologiaPagAmm())
            . $this::SEPARATORE . (\is_null($this->getTc39CausalePagamento()) ? "" : $this->getTc39CausalePagamento()->getCausalePagamento())
            . $this::SEPARATORE . (\is_null($this->getImportoPagAmm()) ? "" : \number_format($this->getImportoPagAmm(), 2, ',', ''))
            . $this::SEPARATORE . (\is_null($this->getNotePag()) ? "" : $this->getNotePag())
            . $this::SEPARATORE . (\is_null($this->getFlgCancellazione()) ? "" : $this->getFlgCancellazione());
    }
}
