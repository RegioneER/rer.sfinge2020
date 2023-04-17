<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 07/06/17
 * Time: 14:36
 */

namespace MonitoraggioBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\FN06PagamentiRepository")
 * @ORM\Table(name="fn06_pagamenti")
 */
class FN06Pagamenti extends EntityEsportazione {
    use StrutturaCancellabile;
    use Id;

    const CODICE_TRACCIATO = "FN06";
    const SEPARATORE = "|";

    /**
     * @ORM\ManyToOne(targetEntity="TC39CausalePagamento")
     * @ORM\JoinColumn(name="causale_pagamento_id", referencedColumnName="id", nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tc39_causale_pagamento;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     * @Assert\Length(max=60, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"Default", "esportazione_monitoraggio"})
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $cod_locale_progetto;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Assert\Length(max=20, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"Default", "esportazione_monitoraggio"})
     * @Assert\notNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $cod_pagamento;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Assert\Length(max=5, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\Regex(pattern="/^(P|R|P-TR|R-TR)$/", message="sfinge.monitoraggio.invalidValue", match=true, groups={"Default", "esportazione_monitoraggio"})
     */
    protected $tipologia_pag;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Date(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     */
    protected $data_pagamento;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true)
     * @Assert\NotNull(groups={"Default", "esportazione_monitoraggio"})
     * @Assert\GreaterThan(value=0, groups={"Default", "esportazione_monitoraggio"}, message="sfinge.monitoraggio.greaterThan")
     */
    protected $importo_pag;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     * @Assert\Length(max=1000, maxMessage="Il campo non può superare i {{ limit }} caratteri", groups={"Default", "esportazione_monitoraggio"})
     */
    protected $note_pag;

    /**
     * @param string $codLocaleProgetto
     * @return FN06Pagamenti
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
     * @return FN06Pagamenti
     */
    public function setCodPagamento($codPagamento) {
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
     * @return FN06Pagamenti
     */
    public function setTipologiaPag($tipologiaPag) {
        $this->tipologia_pag = $tipologiaPag;

        return $this;
    }

    /**
     * Get tipologia_pag
     *
     * @return string
     */
    public function getTipologiaPag() {
        return $this->tipologia_pag;
    }

    /**
     * Set data_pagamento
     *
     * @param \DateTime $dataPagamento
     * @return FN06Pagamenti
     */
    public function setDataPagamento($dataPagamento) {
        $this->data_pagamento = $dataPagamento;

        return $this;
    }

    /**
     * Get data_pagamento
     *
     * @return \DateTime
     */
    public function getDataPagamento() {
        return $this->data_pagamento;
    }

    /**
     * Set importo_pag
     *
     * @param string $importoPag
     * @return FN06Pagamenti
     */
    public function setImportoPag($importoPag) {
        $importo_pulito = str_replace(',', '.', $importoPag);
        $this->importo_pag = (float) $importo_pulito;

        return $this;
    }

    /**
     * Get importo_pag
     *
     * @return string
     */
    public function getImportoPag() {
        return $this->importo_pag;
    }

    /**
     * Set note_pag
     *
     * @param string $notePag
     * @return FN06Pagamenti
     */
    public function setNotePag($notePag) {
        $this->note_pag = $notePag;

        return $this;
    }

    /**
     * Get note_pag
     *
     * @return string
     */
    public function getNotePag() {
        return $this->note_pag;
    }

    /**
     * Set tc39_causale_pagamento
     *
     * @param \MonitoraggioBundle\Entity\TC39CausalePagamento $tc39CausalePagamento
     * @return FN06Pagamenti
     */
    public function setTc39CausalePagamento(\MonitoraggioBundle\Entity\TC39CausalePagamento $tc39CausalePagamento = null) {
        $this->tc39_causale_pagamento = $tc39CausalePagamento;

        return $this;
    }

    /**
     * Get tc39_causale_pagamento
     *
     * @return \MonitoraggioBundle\Entity\TC39CausalePagamento
     */
    public function getTc39CausalePagamento() {
        return $this->tc39_causale_pagamento;
    }

    public function getTracciato() {
        // TODO: Implement getTracciato() method.
        return  (\is_null($this->getCodLocaleProgetto()) ? "" : $this->getCodLocaleProgetto())
        . $this::SEPARATORE .
        (\is_null($this->getCodPagamento()) ? "" : $this->getCodPagamento())
        . $this::SEPARATORE .
        (\is_null($this->getTipologiaPag()) ? "" : $this->getTipologiaPag())
        . $this::SEPARATORE .
        (\is_null($this->getDataPagamento()) ? "" : $this->getDataPagamento()->format('d/m/Y'))
        . $this::SEPARATORE .
        (\is_null($this->getImportoPag()) ? "" : \number_format($this->getImportoPag(), 2, ',', ''))
        . $this::SEPARATORE .
        (\is_null($this->getTc39CausalePagamento()) ? "" : $this->getTc39CausalePagamento()->getCausalePagamento())
        . $this::SEPARATORE .
        (\is_null($this->getNotePag()) ? "" : $this->getNotePag())
        . $this::SEPARATORE .
        (\is_null($this->getFlgCancellazione()) ? "" : $this->getFlgCancellazione());
    }
}
