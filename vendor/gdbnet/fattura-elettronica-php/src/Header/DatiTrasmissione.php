<?php

namespace Gdbnet\FatturaElettronica\Header;

use Gdbnet\FatturaElettronica\FatturaElettronicaException;
use Gdbnet\FatturaElettronica\FatturaElettronicaInterface;
use Gdbnet\FatturaElettronica\Structures\Fiscale;

class DatiTrasmissione implements FatturaElettronicaInterface
{
    /**
     * @var Fiscale|null
     */
    protected $IdTrasmittente;

    /**
     * @var string|null
     */
    protected $ProgressivoInvio;

    /**
     * @var string|null
     */
    protected $FormatoTrasmissione;

    /**
     * @var string
     */
    protected $CodiceDestinatario = '';

    /**
     * @var string|null
     */
    protected $Telefono;

    /**
     * @var string|null
     */
    protected $Email;

    /**
     * @var string|null
     */
    protected $PECDestinatario;

    /**
     * DatiTrasmissione constructor.
     * @param Fiscale $IdTrasmittente
     * @param string $ProgressivoInvio
     * @param string $FormatoTrasmissione
     * @param string $CodiceDestinatario
     * @param string|null $Telefono
     * @param string|null $Email
     * @param string|null $PECDestinatario
     * @throws FatturaElettronicaException
     */
    public function __construct(
        Fiscale $IdTrasmittente,
        string $ProgressivoInvio,
        string $FormatoTrasmissione,
        string $CodiceDestinatario,
        ?string $Telefono = null,
        ?string $Email = null,
        ?string $PECDestinatario = null
    )
    {
        $this->setIdTrasmittente($IdTrasmittente);
        $this->setProgressivoInvio($ProgressivoInvio);
        $this->setFormatoTrasmissione($FormatoTrasmissione);
        $this->setCodiceDestinatario($CodiceDestinatario);
        $this->setTelefono($Telefono);
        $this->setEmail($Email);
        $this->setPECDestinatario($PECDestinatario);
    }

    /**
     * @return Fiscale|null
     */
    public function getIdTrasmittente(): ?Fiscale
    {
        return $this->IdTrasmittente;
    }

    /**
     * @param Fiscale $IdTrasmittente
     * @return DatiTrasmissione
     */
    public function setIdTrasmittente(Fiscale $IdTrasmittente): self
    {
        $this->IdTrasmittente = $IdTrasmittente;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getProgressivoInvio(): ?string
    {
        return $this->ProgressivoInvio;
    }

    /**
     * @param string $ProgressivoInvio
     * @return DatiTrasmissione
     */
    public function setProgressivoInvio(string $ProgressivoInvio): self
    {
        $this->ProgressivoInvio = $ProgressivoInvio;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFormatoTrasmissione(): ?string
    {
        return $this->FormatoTrasmissione;
    }

    /**
     * @param string $FormatoTrasmissione
     * @return DatiTrasmissione
     * @throws FatturaElettronicaException
     */
    public function setFormatoTrasmissione(string $FormatoTrasmissione): self
    {
        switch ($FormatoTrasmissione) {
            case 'FPA12':
            case 'FSM10':
            case 'FPR12':
                $this->FormatoTrasmissione = $FormatoTrasmissione;
                break;
            default:
                throw new FatturaElettronicaException("Invalid 'FormatoTrasmissione', you provide '" . $FormatoTrasmissione . "', value can be FPA12, FSM10 or FPR12");
                break;
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCodiceDestinatario(): ?string
    {
        return $this->CodiceDestinatario;
    }

    /**
     * @param string $CodiceDestinatario
     * @return DatiTrasmissione
     */
    public function setCodiceDestinatario(string $CodiceDestinatario): self
    {
        $this->CodiceDestinatario = $CodiceDestinatario;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTelefono(): ?string
    {
        return $this->Telefono;
    }

    /**
     * @param string|null $Telefono
     * @return DatiTrasmissione
     */
    public function setTelefono(?string $Telefono): self
    {
        $this->Telefono = $Telefono;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->Email;
    }

    /**
     * @param string|null $Email
     * @return DatiTrasmissione
     */
    public function setEmail(?string $Email): self
    {
        $this->Email = $Email;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPECDestinatario(): ?string
    {
        return $this->PECDestinatario;
    }

    /**
     * @param string|null $PECDestinatario
     * @return DatiTrasmissione
     */
    public function setPECDestinatario(?string $PECDestinatario): self
    {
        $this->PECDestinatario = $PECDestinatario;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = [
            'IdTrasmittente' => $this->getIdTrasmittente()->toArray(),
            'ProgressivoInvio' => $this->getProgressivoInvio(),
            'FormatoTrasmissione' => $this->getFormatoTrasmissione(),
            'CodiceDestinatario' => $this->getCodiceDestinatario(),
            'ContattiTrasmittente' => null,
            'PECDestinatario' => $this->getPECDestinatario(),
        ];

        if (!empty($this->getTelefono())) {
            $array['ContattiTrasmittente']['Telefono'] = $this->getTelefono();
        }
        if (!empty($this->getEmail())) {
            $array['ContattiTrasmittente']['Email'] = $this->getEmail();
        }

        return $array;
    }

    /**
     * @param array $array
     * @return DatiTrasmissione
     * @throws FatturaElettronicaException
     */
    public static function fromArray(array $array): DatiTrasmissione
    {
        $IdTrasmittente = new Fiscale($array['IdTrasmittente']['IdPaese'], $array['IdTrasmittente']['IdCodice']);
        $datiTrasmissione = new self(
            $IdTrasmittente,
            $array['ProgressivoInvio'],
            $array['FormatoTrasmissione'],
            $array['CodiceDestinatario']
        );

        if (isset($array['ContattiTrasmittente']['Telefono']) &&
            !empty(trim($array['ContattiTrasmittente']['Telefono']))) {
            $datiTrasmissione->setTelefono($array['ContattiTrasmittente']['Telefono']);
        }
        if (isset($array['ContattiTrasmittente']['Email']) &&
            !empty(trim($array['ContattiTrasmittente']['Email']))) {
            $datiTrasmissione->setEmail($array['ContattiTrasmittente']['Email']);
        }
        if (isset($array['PECDestinatario']) && !empty(trim($array['PECDestinatario']))) {
            $datiTrasmissione->setPECDestinatario($array['PECDestinatario']);
        }

        return $datiTrasmissione;
    }
}