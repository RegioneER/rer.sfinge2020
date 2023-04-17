<?php

namespace Gdbnet\FatturaElettronica\Body;

use Gdbnet\FatturaElettronica\FatturaElettronicaInterface;

class DettaglioPagamento implements FatturaElettronicaInterface
{
    /**
     * @var string|null
     */
    protected $Beneficiario;

    /**
     * @var string
     */
    protected $ModalitaPagamento;

    /**
     * @var string|null
     */
    protected $DataRiferimentoTerminiPagamento;

    /**
     * @var int|null
     */
    protected $GiorniTerminiPagamento;

    /**
     * @var string|null
     */
    protected $DataScadenzaPagamento;

    /**
     * @var string
     */
    protected $ImportoPagamento;

    /**
     * @var string|null
     */
    protected $CodUfficioPostale;

    /**
     * @var string|null
     */
    protected $CognomeQuietanzante;

    /**
     * @var string|null
     */
    protected $NomeQuietanzante;

    /**
     * @var string|null
     */
    protected $CFQuietanzante;

    /**
     * @var string|null
     */
    protected $TitoloQuietanzante;

    /**
     * @var string|null
     */
    protected $IstitutoFinanziario;

    /**
     * @var string|null
     */
    protected $IBAN;

    /**
     * @var string|null
     */
    protected $ABI;

    /**
     * @var string|null
     */
    protected $CAB;

    /**
     * @var string|null
     */
    protected $BIC;

    /**
     * @var string|null
     */
    protected $ScontoPagamentoAnticipato;

    /**
     * @var string|null
     */
    protected $DataLimitePagamentoAnticipato;

    /**
     * @var string|null
     */
    protected $PenalitaPagamentiRitardati;

    /**
     * @var string|null
     */
    protected $DataDecorrenzaPenale;

    /**
     * @var string|null
     */
    protected $CodicePagamento;

    /**
     * DettaglioPagamento constructor.
     * @param string $ModalitaPagamento
     * @param float $ImportoPagamento
     */
    public function __construct(string $ModalitaPagamento, float $ImportoPagamento)
    {
        $this->setModalitaPagamento($ModalitaPagamento);
        $this->setImportoPagamento($ImportoPagamento);
    }

    /**
     * @return string|null
     */
    public function getBeneficiario(): ?string
    {
        return $this->Beneficiario;
    }

    /**
     * @param string|null $Beneficiario
     * @return DettaglioPagamento
     */
    public function setBeneficiario(?string $Beneficiario): self
    {
        $this->Beneficiario = $Beneficiario;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getModalitaPagamento(): ?string
    {
        return $this->ModalitaPagamento;
    }

    /**
     * @param string|null $ModalitaPagamento
     * @return DettaglioPagamento
     */
    public function setModalitaPagamento(?string $ModalitaPagamento): self
    {
        $this->ModalitaPagamento = $ModalitaPagamento;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDataRiferimentoTerminiPagamento(): ?string
    {
        return $this->DataRiferimentoTerminiPagamento;
    }

    /**
     * @param string|null $DataRiferimentoTerminiPagamento
     * @return DettaglioPagamento
     */
    public function setDataRiferimentoTerminiPagamento(?string $DataRiferimentoTerminiPagamento): self
    {
        $this->DataRiferimentoTerminiPagamento = $DataRiferimentoTerminiPagamento;

        return $this;
    }

    /**
     * @return integer|null
     */
    public function getGiorniTerminiPagamento(): ?int
    {
        return $this->GiorniTerminiPagamento;
    }

    /**
     * @param integer|null $GiorniTerminiPagamento
     * @return DettaglioPagamento
     */
    public function setGiorniTerminiPagamento(?int $GiorniTerminiPagamento): self
    {
        $this->GiorniTerminiPagamento = $GiorniTerminiPagamento;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDataScadenzaPagamento(): ?string
    {
        return $this->DataScadenzaPagamento;
    }

    /**
     * @param string|null $DataScadenzaPagamento
     * @return DettaglioPagamento
     */
    public function setDataScadenzaPagamento(?string $DataScadenzaPagamento): self
    {
        $this->DataScadenzaPagamento = $DataScadenzaPagamento;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getImportoPagamento(): ?string
    {
        return $this->ImportoPagamento;
    }

    /**
     * @param float|null $ImportoPagamento
     * @return DettaglioPagamento
     */
    public function setImportoPagamento(?float $ImportoPagamento): self
    {
        $this->ImportoPagamento = number_format($ImportoPagamento, 2, '.', '');

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCodUfficioPostale(): ?string
    {
        return $this->CodUfficioPostale;
    }

    /**
     * @param string|null $CodUfficioPostale
     * @return DettaglioPagamento
     */
    public function setCodUfficioPostale(?string $CodUfficioPostale): self
    {
        $this->CodUfficioPostale = $CodUfficioPostale;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCognomeQuietanzante(): ?string
    {
        return $this->CognomeQuietanzante;
    }

    /**
     * @param string|null $CognomeQuietanzante
     * @return DettaglioPagamento
     */
    public function setCognomeQuietanzante(?string $CognomeQuietanzante): self
    {
        $this->CognomeQuietanzante = $CognomeQuietanzante;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNomeQuietanzante(): ?string
    {
        return $this->NomeQuietanzante;
    }

    /**
     * @param string|null $NomeQuietanzante
     * @return DettaglioPagamento
     */
    public function setNomeQuietanzante(?string $NomeQuietanzante): self
    {
        $this->NomeQuietanzante = $NomeQuietanzante;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCFQuietanzante(): ?string
    {
        return $this->CFQuietanzante;
    }

    /**
     * @param string|null $CFQuietanzante
     * @return DettaglioPagamento
     */
    public function setCFQuietanzante(?string $CFQuietanzante): self
    {
        $this->CFQuietanzante = $CFQuietanzante;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitoloQuietanzante(): ?string
    {
        return $this->TitoloQuietanzante;
    }

    /**
     * @param string|null $TitoloQuietanzante
     * @return DettaglioPagamento
     */
    public function setTitoloQuietanzante(?string $TitoloQuietanzante): self
    {
        $this->TitoloQuietanzante = $TitoloQuietanzante;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getIstitutoFinanziario(): ?string
    {
        return $this->IstitutoFinanziario;
    }

    /**
     * @param string|null $IstitutoFinanziario
     * @return DettaglioPagamento
     */
    public function setIstitutoFinanziario(?string $IstitutoFinanziario): self
    {
        $this->IstitutoFinanziario = $IstitutoFinanziario;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getIBAN(): ?string
    {
        return $this->IBAN;
    }

    /**
     * @param string|null $IBAN
     * @return DettaglioPagamento
     */
    public function setIBAN(?string $IBAN): self
    {
        $this->IBAN = $IBAN;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getABI(): ?string
    {
        return $this->ABI;
    }

    /**
     * @param string|null $ABI
     * @return DettaglioPagamento
     */
    public function setABI(?string $ABI): self
    {
        $this->ABI = $ABI;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCAB(): ?string
    {
        return $this->CAB;
    }

    /**
     * @param string|null $CAB
     * @return DettaglioPagamento
     */
    public function setCAB(?string $CAB): self
    {
        $this->CAB = $CAB;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBIC(): ?string
    {
        return $this->BIC;
    }

    /**
     * @param string|null $BIC
     * @return DettaglioPagamento
     */
    public function setBIC(?string $BIC): self
    {
        $this->BIC = $BIC;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getScontoPagamentoAnticipato(): ?string
    {
        return $this->ScontoPagamentoAnticipato;
    }

    /**
     * @param float|null $ScontoPagamentoAnticipato
     * @return DettaglioPagamento
     */
    public function setScontoPagamentoAnticipato(?float $ScontoPagamentoAnticipato): self
    {
        $this->ScontoPagamentoAnticipato = number_format($ScontoPagamentoAnticipato, 2, '.', '');

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDataLimitePagamentoAnticipato(): ?string
    {
        return $this->DataLimitePagamentoAnticipato;
    }

    /**
     * @param string|null $DataLimitePagamentoAnticipato
     * @return DettaglioPagamento
     */
    public function setDataLimitePagamentoAnticipato(?string $DataLimitePagamentoAnticipato): self
    {
        $this->DataLimitePagamentoAnticipato = $DataLimitePagamentoAnticipato;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPenalitaPagamentiRitardati(): ?string
    {
        return $this->PenalitaPagamentiRitardati;
    }

    /**
     * @param float|null $PenalitaPagamentiRitardati
     * @return DettaglioPagamento
     */
    public function setPenalitaPagamentiRitardati(?float $PenalitaPagamentiRitardati): self
    {
        $this->PenalitaPagamentiRitardati = number_format($PenalitaPagamentiRitardati, 2, '.', '');

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDataDecorrenzaPenale(): ?string
    {
        return $this->DataDecorrenzaPenale;
    }

    /**
     * @param string|null $DataDecorrenzaPenale
     * @return DettaglioPagamento
     */
    public function setDataDecorrenzaPenale(?string $DataDecorrenzaPenale): self
    {
        $this->DataDecorrenzaPenale = $DataDecorrenzaPenale;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCodicePagamento(): ?string
    {
        return $this->CodicePagamento;
    }

    /**
     * @param string|null $CodicePagamento
     * @return DettaglioPagamento
     */
    public function setCodicePagamento(?string $CodicePagamento): self
    {
        $this->CodicePagamento = $CodicePagamento;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'Beneficiario' => $this->getBeneficiario(),
            'ModalitaPagamento' => $this->getModalitaPagamento(),
            'DataRiferimentoTerminiPagamento' => $this->getDataRiferimentoTerminiPagamento(),
            'GiorniTerminiPagamento' => $this->getGiorniTerminiPagamento(),
            'DataScadenzaPagamento' => $this->getDataScadenzaPagamento(),
            'ImportoPagamento' => $this->getImportoPagamento(),
            'CodUfficioPostale' => $this->getCodUfficioPostale(),
            'CognomeQuietanzante' => $this->getCognomeQuietanzante(),
            'NomeQuietanzante' => $this->getNomeQuietanzante(),
            'CFQuietanzante' => $this->getCFQuietanzante(),
            'TitoloQuietanzante' => $this->getTitoloQuietanzante(),
            'IstitutoFinanziario' => $this->getIstitutoFinanziario(),
            'IBAN' => $this->getIBAN(),
            'ABI' => $this->getABI(),
            'CAB' => $this->getCAB(),
            'BIC' => $this->getBIC(),
            'ScontoPagamentoAnticipato' => $this->getScontoPagamentoAnticipato(),
            'DataLimitePagamentoAnticipato' => $this->getDataLimitePagamentoAnticipato(),
            'PenalitaPagamentiRitardati' => $this->getPenalitaPagamentiRitardati(),
            'DataDecorrenzaPenale' => $this->getDataDecorrenzaPenale(),
            'CodicePagamento' => $this->getCodicePagamento(),
        ];
    }

    /**
     * @param array $array
     * @return DettaglioPagamento
     */
    public static function fromArray(array $array): self
    {
        $dettaglioPagamento = new self($array['ModalitaPagamento'], $array['ImportoPagamento']);

        if (isset($array['Beneficiario']) && !empty(trim($array['Beneficiario']))) {
            $dettaglioPagamento->setBeneficiario($array['Beneficiario']);
        }
        if (isset($array['DataRiferimentoTerminiPagamento']) && !empty(trim($array['DataRiferimentoTerminiPagamento']))) {
            $dettaglioPagamento->setDataRiferimentoTerminiPagamento($array['DataRiferimentoTerminiPagamento']);
        }
        if (isset($array['GiorniTerminiPagamento']) && !empty(trim($array['GiorniTerminiPagamento']))) {
            $dettaglioPagamento->setGiorniTerminiPagamento($array['GiorniTerminiPagamento']);
        }
        if (isset($array['DataScadenzaPagamento']) && !empty(trim($array['DataScadenzaPagamento']))) {
            $dettaglioPagamento->setDataScadenzaPagamento($array['DataScadenzaPagamento']);
        }
        if (isset($array['CodUfficioPostale']) && !empty(trim($array['CodUfficioPostale']))) {
            $dettaglioPagamento->setCodUfficioPostale($array['CodUfficioPostale']);
        }
        if (isset($array['CognomeQuietanzante']) && !empty(trim($array['CognomeQuietanzante']))) {
            $dettaglioPagamento->setCognomeQuietanzante($array['CognomeQuietanzante']);
        }
        if (isset($array['NomeQuietanzante']) && !empty(trim($array['NomeQuietanzante']))) {
            $dettaglioPagamento->setNomeQuietanzante($array['NomeQuietanzante']);
        }
        if (isset($array['CFQuietanzante']) && !empty(trim($array['CFQuietanzante']))) {
            $dettaglioPagamento->setCFQuietanzante($array['CFQuietanzante']);
        }
        if (isset($array['TitoloQuietanzante']) && !empty(trim($array['TitoloQuietanzante']))) {
            $dettaglioPagamento->setTitoloQuietanzante($array['TitoloQuietanzante']);
        }
        if (isset($array['IstitutoFinanziario']) && !empty(trim($array['IstitutoFinanziario']))) {
            $dettaglioPagamento->setIstitutoFinanziario($array['IstitutoFinanziario']);
        }
        if (isset($array['IBAN']) && !empty(trim($array['IBAN']))) {
            $dettaglioPagamento->setIBAN($array['IBAN']);
        }
        if (isset($array['ABI']) && !empty(trim($array['ABI']))) {
            $dettaglioPagamento->setABI($array['ABI']);
        }
        if (isset($array['CAB']) && !empty(trim($array['CAB']))) {
            $dettaglioPagamento->setCAB($array['CAB']);
        }
        if (isset($array['BIC']) && !empty(trim($array['BIC']))) {
            $dettaglioPagamento->setBIC($array['BIC']);
        }
        if (isset($array['ScontoPagamentoAnticipato']) && !empty(trim($array['ScontoPagamentoAnticipato']))) {
            $dettaglioPagamento->setScontoPagamentoAnticipato($array['ScontoPagamentoAnticipato']);
        }
        if (isset($array['DataLimitePagamentoAnticipato']) && !empty(trim($array['DataLimitePagamentoAnticipato']))) {
            $dettaglioPagamento->setDataLimitePagamentoAnticipato($array['DataLimitePagamentoAnticipato']);
        }
        if (isset($array['PenalitaPagamentiRitardati']) && !empty(trim($array['PenalitaPagamentiRitardati']))) {
            $dettaglioPagamento->setPenalitaPagamentiRitardati($array['PenalitaPagamentiRitardati']);
        }
        if (isset($array['DataDecorrenzaPenale']) && !empty(trim($array['DataDecorrenzaPenale']))) {
            $dettaglioPagamento->setDataDecorrenzaPenale($array['DataDecorrenzaPenale']);
        }
        if (isset($array['CodicePagamento']) && !empty(trim($array['CodicePagamento']))) {
            $dettaglioPagamento->setCodicePagamento($array['CodicePagamento']);
        }

        return $dettaglioPagamento;
    }
}