<?php

namespace AttuazioneControlloBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class PagamentiPercettoriGiustificativo extends PagamentiPercettori {
    /**
     * @ORM\ManyToOne(targetEntity="GiustificativoPagamento", inversedBy="pagamenti_percettori")
     * @ORM\JoinColumn(name="giustificativo_id", referencedColumnName="id", nullable=true)
     * @var GiustificativoPagamento|null
     */
    protected $giustificativo_pagamento;

    public function setGiustificativoPagamento(?GiustificativoPagamento $giustificativoPagamento): self {
        $this->giustificativo_pagamento = $giustificativoPagamento;

        return $this;
    }

    public function getGiustificativoPagamento(): ?GiustificativoPagamento {
        return $this->giustificativo_pagamento;
    }

    public function getTipo(): string {
        return "GIUSTIFICATIVO_PAGAMENTO";
    }

    /**
     * @throws \Exception
     */
    public function aggiornaDaGiustificativo(): void {
        if(\is_null($this->giustificativo_pagamento)){
            throw new \Exception('Giustificativo non presente');
        }

        $this->codice_fiscale = $this->giustificativo_pagamento->getCodiceFiscaleFornitore();
        $this->importo = $this->giustificativo_pagamento->getImportoAmmesso();
    }
}
