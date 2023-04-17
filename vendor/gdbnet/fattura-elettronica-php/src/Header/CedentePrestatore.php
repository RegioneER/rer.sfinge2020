<?php

namespace Gdbnet\FatturaElettronica\Header;

use Gdbnet\FatturaElettronica\FatturaElettronicaInterface;
use Gdbnet\FatturaElettronica\Structures\Anagrafica;
use Gdbnet\FatturaElettronica\Structures\Fiscale;
use Gdbnet\FatturaElettronica\Structures\Indirizzo;
use Gdbnet\FatturaElettronica\FatturaElettronicaException;

class CedentePrestatore implements FatturaElettronicaInterface
{
    /**
     * @var Fiscale
     */
    protected $IdFiscaleIVA;

    /**
     * @var string|null
     */
    protected $CodiceFiscale;

    /**
     * @var Anagrafica
     */
    protected $Anagrafica;

    /**
     * @var null|string
     */
    protected $AlboProfessionale = null;

    /**
     * @var null|string
     */
    protected $ProvinciaAlbo = null;

    /**
     * @var null|string
     */
    protected $NumeroIscrizioneAlbo = null;

    /**
     * @var null|string
     */
    protected $DataIscrizioneAlbo = null;

    /**
     * @var string
     */
    protected $RegimeFiscale;

    /**
     * @var Indirizzo
     */
    protected $Sede;

    /**
     * @var Indirizzo|null
     */
    protected $StabileOrganizzazione = null;

    /**
     * @var null|string
     */
    protected $Ufficio = null;

    /**
     * @var null|string
     */
    protected $NumeroREA = null;

    /**
     * @var null|string
     */
    protected $CapitaleSociale = null;

    /**
     * @var null|string
     */
    protected $SocioUnico = null;

    /**
     * @var null|string
     */
    protected $StatoLiquidazione = null;

    /**
     * @var null|string
     */
    protected $Telefono = null;

    /**
     * @var null|string
     */
    protected $Email = null;

    /**
     * @var null|string
     */
    protected $Fax = null;

    /**
     * @var null|string
     */
    protected $RiferimentoAmministrazione = null;

    /**
     * CedentePrestatore constructor.
     * @param Fiscale $IdFiscaleIVA
     * @param Anagrafica $Anagrafica
     * @param Indirizzo $Sede
     * @param string $RegimeFiscale
     */
    public function __construct(
        Fiscale $IdFiscaleIVA,
        Anagrafica $Anagrafica,
        Indirizzo $Sede,
        string $RegimeFiscale
    )
    {
        $this->setIdFiscaleIVA($IdFiscaleIVA);
        $this->setAnagrafica($Anagrafica);
        $this->setSede($Sede);
        $this->setRegimeFiscale($RegimeFiscale);
    }

    /**
     * @return Fiscale
     */
    public function getIdFiscaleIVA(): Fiscale
    {
        return $this->IdFiscaleIVA;
    }

    /**
     * @param Fiscale $IdFiscaleIVA
     * @return CedentePrestatore
     */
    public function setIdFiscaleIVA(Fiscale $IdFiscaleIVA): self
    {
        $this->IdFiscaleIVA = $IdFiscaleIVA;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCodiceFiscale(): ?string
    {
        return $this->CodiceFiscale;
    }

    /**
     * @param null|string $CodiceFiscale
     * @return CedentePrestatore
     */
    public function setCodiceFiscale(?string $CodiceFiscale): self
    {
        $this->CodiceFiscale = strtoupper($CodiceFiscale);

        return $this;
    }

    /**
     * @return Anagrafica
     */
    public function getAnagrafica(): Anagrafica
    {
        return $this->Anagrafica;
    }

    /**
     * @param Anagrafica $Anagrafica
     * @return CedentePrestatore
     */
    public function setAnagrafica(Anagrafica $Anagrafica): self
    {
        $this->Anagrafica = $Anagrafica;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getAlboProfessionale(): ?string
    {
        return $this->AlboProfessionale;
    }

    /**
     * @param null|string $AlboProfessionale
     * @return CedentePrestatore
     */
    public function setAlboProfessionale(?string $AlboProfessionale): self
    {
        $this->AlboProfessionale = $AlboProfessionale;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getProvinciaAlbo(): ?string
    {
        return $this->ProvinciaAlbo;
    }

    /**
     * @param null|string $ProvinciaAlbo
     * @return CedentePrestatore
     */
    public function setProvinciaAlbo(?string $ProvinciaAlbo): self
    {
        $this->ProvinciaAlbo = $ProvinciaAlbo;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getNumeroIscrizioneAlbo(): ?string
    {
        return $this->NumeroIscrizioneAlbo;
    }

    /**
     * @param null|string $NumeroIscrizioneAlbo
     * @return CedentePrestatore
     */
    public function setNumeroIscrizioneAlbo(?string $NumeroIscrizioneAlbo): self
    {
        $this->NumeroIscrizioneAlbo = $NumeroIscrizioneAlbo;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDataIscrizioneAlbo(): ?string
    {
        return $this->DataIscrizioneAlbo;
    }

    /**
     * @param null|string $DataIscrizioneAlbo
     * @return CedentePrestatore
     */
    public function setDataIscrizioneAlbo(?string $DataIscrizioneAlbo): self
    {
        $this->DataIscrizioneAlbo = $DataIscrizioneAlbo;

        return $this;
    }

    /**
     * @return string
     */
    public function getRegimeFiscale(): string
    {
        return $this->RegimeFiscale;
    }

    /**
     * @param string $RegimeFiscale
     * @return $this
     */
    public function setRegimeFiscale(string $RegimeFiscale)
    {
        $this->RegimeFiscale = $RegimeFiscale;

        return $this;
    }

    /**
     * @return Indirizzo
     */
    public function getSede(): Indirizzo
    {
        return $this->Sede;
    }

    /**
     * @param Indirizzo $Sede
     * @return CedentePrestatore
     */
    public function setSede(Indirizzo $Sede): self
    {
        $this->Sede = $Sede;

        return $this;
    }

    /**
     * @return Indirizzo|null
     */
    public function getStabileOrganizzazione(): ?Indirizzo
    {
        return $this->StabileOrganizzazione;
    }

    /**
     * @param Indirizzo|null $StabileOrganizzazione
     * @return CedentePrestatore
     */
    public function setStabileOrganizzazione(?Indirizzo $StabileOrganizzazione): self
    {
        $this->StabileOrganizzazione = $StabileOrganizzazione;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getUfficio(): ?string
    {
        return $this->Ufficio;
    }

    /**
     * @param null|string $Ufficio
     * @return CedentePrestatore
     */
    public function setUfficio(?string $Ufficio): self
    {
        $this->Ufficio = $Ufficio;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getNumeroREA(): ?string
    {
        return $this->NumeroREA;
    }

    /**
     * @param null|string $NumeroREA
     * @return CedentePrestatore
     */
    public function setNumeroREA(?string $NumeroREA): self
    {
        $this->NumeroREA = $NumeroREA;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCapitaleSociale(): ?string
    {
        return $this->CapitaleSociale;
    }

    /**
     * @param null|string $CapitaleSociale
     * @return CedentePrestatore
     */
    public function setCapitaleSociale(?string $CapitaleSociale): self
    {
        $this->CapitaleSociale = number_format($CapitaleSociale, 2, '.', '');

        return $this;
    }

    /**
     * @return null|string
     */
    public function getSocioUnico(): ?string
    {
        return $this->SocioUnico;
    }

    /**
     * @param null|string $SocioUnico
     * @return CedentePrestatore
     */
    public function setSocioUnico(?string $SocioUnico): self
    {
        $this->SocioUnico = $SocioUnico;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getStatoLiquidazione(): ?string
    {
        return $this->StatoLiquidazione;
    }

    /**
     * @param null|string $StatoLiquidazione
     * @return CedentePrestatore|null
     * @throws FatturaElettronicaException
     */
    public function setStatoLiquidazione(?string $StatoLiquidazione): ?self
    {
        switch ($StatoLiquidazione) {
            case 'LN':
            case 'LS':
                $this->StatoLiquidazione = $StatoLiquidazione;
                break;
            default:
                throw new FatturaElettronicaException("Invalid 'StatoLiquidazione' , allowed value are LN or LS");
                break;
        }

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTelefono(): ?string
    {
        return $this->Telefono;
    }

    /**
     * @param null|string $Telefono
     * @return CedentePrestatore
     */
    public function setTelefono(?string $Telefono): self
    {
        $this->Telefono = $Telefono;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getEmail(): ?string
    {
        return $this->Email;
    }

    /**
     * @param null|string $Email
     * @return CedentePrestatore
     */
    public function setEmail(?string $Email): self
    {
        $this->Email = $Email;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getFax(): ?string
    {
        return $this->Fax;
    }

    /**
     * @param null|string $Fax
     * @return CedentePrestatore
     */
    public function setFax(?string $Fax): self
    {
        $this->Fax = $Fax;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getRiferimentoAmministrazione(): ?string
    {
        return $this->RiferimentoAmministrazione;
    }

    /**
     * @param null|string $RiferimentoAmministrazione
     * @return CedentePrestatore
     */
    public function setRiferimentoAmministrazione(?string $RiferimentoAmministrazione): self
    {
        $this->RiferimentoAmministrazione = $RiferimentoAmministrazione;

        return $this;
    }


    /**
     * @return array
     */
    public function toArray()
    {
        $array['DatiAnagrafici'] = [
            'IdFiscaleIVA' => $this->getIdFiscaleIVA()->toArray(),
            'CodiceFiscale' => $this->getCodiceFiscale(),
            'Anagrafica' => $this->getAnagrafica()->toArray(),
            'RegimeFiscale' => $this->getRegimeFiscale(),
        ];

        $array['Sede'] = null;
        if ($this->getSede() instanceof Indirizzo) {
            $array['Sede'] = $this->getSede()->toArray();
        }

        $array['StabileOrganizzazione'] = null;
        if ($this->getStabileOrganizzazione() instanceof Indirizzo) {
            $array['StabileOrganizzazione'] = $this->getStabileOrganizzazione()->toArray();
        }

        if (!empty($this->getAlboProfessionale())) {
            $array['DatiAnagrafici']['AlboProfessionale'] = $this->getAlboProfessionale();
        }
        if (!empty($this->getProvinciaAlbo())) {
            $array['DatiAnagrafici']['ProvinciaAlbo'] = $this->getProvinciaAlbo();
        }
        if (!empty($this->getNumeroIscrizioneAlbo())) {
            $array['DatiAnagrafici']['NumeroIscrizioneAlbo'] = $this->getNumeroIscrizioneAlbo();
        }
        if (!empty($this->getDataIscrizioneAlbo())) {
            $array['DatiAnagrafici']['DataIscrizioneAlbo'] = $this->getDataIscrizioneAlbo();
        }

        $array['IscrizioneREA'] = null;
        if (!empty($this->getUfficio())) {
            $array['IscrizioneREA']['Ufficio'] = $this->getUfficio();
        }
        if (!empty($this->getNumeroREA())) {
            $array['IscrizioneREA']['NumeroREA'] = $this->getNumeroREA();
        }
        if (!empty($this->getCapitaleSociale())) {
            $array['IscrizioneREA']['CapitaleSociale'] = $this->getCapitaleSociale();
        }
        if (!empty($this->getSocioUnico())) {
            $array['IscrizioneREA']['SocioUnico'] = $this->getSocioUnico();
        }
        if (!empty($this->getStatoLiquidazione())) {
            $array['IscrizioneREA']['StatoLiquidazione'] = $this->getStatoLiquidazione();
        }

        $array['Contatti'] = null;
        if (!empty($this->getTelefono())) {
            $array['Contatti']['Telefono'] = $this->getTelefono();
        }
        if (!empty($this->getFax())) {
            $array['Contatti']['Fax'] = $this->getFax();
        }
        if (!empty($this->getEmail())) {
            $array['Contatti']['Email'] = $this->getEmail();
        }

        $array['RiferimentoAmministrazione'] = null;
        if (!empty($this->getRiferimentoAmministrazione())) {
            $array['RiferimentoAmministrazione'] = $this->getRiferimentoAmministrazione();
        }

        return $array;
    }

    /**
     * @param $array
     * @return CedentePrestatore
     * @throws FatturaElettronicaException
     */
    public static function fromArray(array $array): self
    {
        $IdFiscaleIVA = Fiscale::fromArray($array['DatiAnagrafici']['IdFiscaleIVA']);
        $Anagrafica = Anagrafica::fromArray($array['DatiAnagrafici']['Anagrafica']);
        $Sede = Indirizzo::fromArray($array['Sede']);
        $RegimeFiscale = $array['DatiAnagrafici']['RegimeFiscale'];
        $cedentePrestatore = new self($IdFiscaleIVA, $Anagrafica, $Sede, $RegimeFiscale);

        if (isset($array['DatiAnagrafici']['CodiceFiscale']) &&
            !empty(trim($array['DatiAnagrafici']['CodiceFiscale']))) {
            $cedentePrestatore->setCodiceFiscale($array['DatiAnagrafici']['CodiceFiscale']);
        }
        if (isset($array['DatiAnagrafici']['AlboProfessionale']) &&
            !empty(trim($array['DatiAnagrafici']['AlboProfessionale']))) {
            $cedentePrestatore->setAlboProfessionale($array['DatiAnagrafici']['AlboProfessionale']);
        }
        if (isset($array['DatiAnagrafici']['ProvinciaAlbo']) &&
            !empty(trim($array['DatiAnagrafici']['ProvinciaAlbo']))) {
            $cedentePrestatore->setProvinciaAlbo($array['DatiAnagrafici']['ProvinciaAlbo']);
        }
        if (isset($array['DatiAnagrafici']['NumeroIscrizioneAlbo']) &&
            !empty(trim($array['DatiAnagrafici']['NumeroIscrizioneAlbo']))) {
            $cedentePrestatore->setNumeroIscrizioneAlbo($array['DatiAnagrafici']['NumeroIscrizioneAlbo']);
        }
        if (isset($array['DatiAnagrafici']['DataIscrizioneAlbo']) &&
            !empty(trim($array['DatiAnagrafici']['DataIscrizioneAlbo']))) {
            $cedentePrestatore->setDataIscrizioneAlbo($array['DatiAnagrafici']['DataIscrizioneAlbo']);
        }

        if (isset($array['StabileOrganizzazione']) && is_array($array['StabileOrganizzazione'])) {
            $cedentePrestatore->setStabileOrganizzazione(Indirizzo::fromArray($array['StabileOrganizzazione']));
        }

        if (isset($array['IscrizioneREA']['Ufficio']) &&
            !empty(trim($array['IscrizioneREA']['Ufficio']))) {
            $cedentePrestatore->setUfficio($array['IscrizioneREA']['Ufficio']);
        }
        if (isset($array['IscrizioneREA']['NumeroREA']) &&
            !empty(trim($array['IscrizioneREA']['NumeroREA']))) {
            $cedentePrestatore->setNumeroREA($array['IscrizioneREA']['NumeroREA']);
        }
        if (isset($array['IscrizioneREA']['CapitaleSociale']) &&
            !empty(trim($array['IscrizioneREA']['CapitaleSociale']))) {
            $cedentePrestatore->setCapitaleSociale($array['IscrizioneREA']['CapitaleSociale']);
        }
        if (isset($array['IscrizioneREA']['SocioUnico']) &&
            !empty(trim($array['IscrizioneREA']['SocioUnico']))) {
            $cedentePrestatore->setSocioUnico($array['IscrizioneREA']['SocioUnico']);
        }
        if (isset($array['IscrizioneREA']['StatoLiquidazione']) &&
            !empty(trim($array['IscrizioneREA']['StatoLiquidazione']))) {
            $cedentePrestatore->setStatoLiquidazione($array['IscrizioneREA']['StatoLiquidazione']);
        }

        if (isset($array['Contatti']['Telefono']) && !empty(trim($array['Contatti']['Telefono']))) {
            $cedentePrestatore->setTelefono($array['Contatti']['Telefono']);
        }
        if (isset($array['Contatti']['Fax']) && !empty(trim($array['Contatti']['Fax']))) {
            $cedentePrestatore->setFax($array['Contatti']['Fax']);
        }
        if (isset($array['Contatti']['Email']) && !empty(trim($array['Contatti']['Email']))) {
            $cedentePrestatore->setEmail($array['Contatti']['Email']);
        }

        if (isset($array['RiferimentoAmministrazione']) && !empty(trim($array['RiferimentoAmministrazione']))) {
            $cedentePrestatore->setRiferimentoAmministrazione($array['RiferimentoAmministrazione']);
        }

        return $cedentePrestatore;
    }
}