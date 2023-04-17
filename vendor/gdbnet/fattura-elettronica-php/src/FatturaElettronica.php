<?php

namespace Gdbnet\FatturaElettronica;

use Gdbnet\FatturaElettronica\Body\FatturaElettronicaBody;
use Gdbnet\FatturaElettronica\Header\CedentePrestatore;
use Gdbnet\FatturaElettronica\Header\CessionarioCommittente;
use Gdbnet\FatturaElettronica\Header\FatturaElettronicaHeader;

class FatturaElettronica
{
    /**
     * @var FatturaElettronicaHeader
     */
    protected $header;

    /**
     * @var FatturaElettronicaBody[]
     */
    protected $bodys;

    /**
     * @var string
     */
    protected $versione;

    /**
     * @var bool
     */
    protected $lotto = false;

    /**
     * @var FatturaElettronicaPrinterInterface
     */
    private $output;

    /**
     * FatturaElettronica constructor.
     * @param FatturaElettronicaHeader $header
     * @param FatturaElettronicaBody $body
     */
    public function __construct(
        FatturaElettronicaHeader $header,
        FatturaElettronicaBody $body = null
    )
    {
        $this->setHeader($header);
        $this->versione = $header->getDatiTrasmissione()->getFormatoTrasmissione();

        if ($body) {
            $this->addBody($body);
        }
    }

    /**
     * @return Structures\Fiscale|null
     */
    public function getPivaCedente()
    {
        if ($this->getHeader() instanceof FatturaElettronicaHeader) {
            if ($this->getHeader()->getCedentePrestatore() instanceof CedentePrestatore) {
                return $this->getHeader()->getCedentePrestatore()->getIdFiscaleIVA();
            }
        }

        return null;
    }

    /**
     * @return Structures\Fiscale|null
     */
    public function getPivaCommittente()
    {
        if ($this->getHeader() instanceof FatturaElettronicaHeader) {
            if ($this->getHeader()->getCessionarioCommittente() instanceof CessionarioCommittente) {
                return $this->getHeader()->getCessionarioCommittente()->getIdFiscaleIVA();
            }
        }

        return null;
    }

    /**
     * @return null|string
     */
    public function getCodiceDestinatario()
    {
        if ($this->getHeader() instanceof FatturaElettronicaHeader) {
            if ($this->getHeader()->getCessionarioCommittente() instanceof CessionarioCommittente) {
                return $this->getHeader()->getDatiTrasmissione()->getCodiceDestinatario();
            }
        }

        return null;
    }

    /**
     * @return null|string
     * @throws FatturaElettronicaException
     */
    public function getNumeroFattura()
    {
        if ($this->isLotto()) {
            throw new FatturaElettronicaException("There are multiple body, that means that is non a single 'FatturaElettronica', but a 'Lotto di fatture'. 'Numero fattura' can't be returned safely");
        }

        return $this->getBodys()[0]->getDatiGenerali()->getDatiGeneraliDocumento()->getNumero();
    }

    /**
     * @return null|string
     * @throws FatturaElettronicaException
     */
    public function getDataFattura()
    {
        if ($this->isLotto()) {
            throw new FatturaElettronicaException("There are multiple body, that means that is non a single 'FatturaElettronica', but a 'Lotto di fatture'. 'Numero fattura' can't be returned safely");
        }

        return $this->getBodys()[0]->getDatiGenerali()->getDatiGeneraliDocumento()->getData();
    }

    /**
     * @return null|string
     * @throws FatturaElettronicaException
     */
    public function getImportoTotaleDocumento()
    {
        if ($this->isLotto()) {
            throw new FatturaElettronicaException("There are multiple body, that means that is non a single 'FatturaElettronica', but a 'Lotto di fatture'. 'ImportoTotaleDocumento' can't be returned safely");
        }

        return $this->getBodys()[0]->getDatiGenerali()->getDatiGeneraliDocumento()->getImportoTotaleDocumento();

    }

    /**
     * @return array
     * @throws FatturaElettronicaException
     */
    public function getDescrizione()
    {
        if ($this->isLotto()) {
            throw new FatturaElettronicaException("There are multiple body, that means that is non a single 'FatturaElettronica', but a 'Lotto di fatture'. 'Descrizione' can't be returned safely");
        }

        $descrizione = [];

        foreach ($this->getBodys()[0]->getDatiBeniServizi()->getDettaglioLinee() as $dettaglioLinea) {
            array_push($descrizione, $dettaglioLinea->getDescrizione());
        }

        return $descrizione;
    }

    /**
     * @return bool
     */
    public function isLotto(): bool
    {
        if (count($this->getBodys()) > 1) {
            $this->lotto = true;
        }

        return $this->lotto;
    }

    /**
     * @param bool $lotto
     * @return FatturaElettronica
     */
    public function setLotto(bool $lotto): self
    {
        $this->lotto = $lotto;

        return $this;
    }

    /**
     * @return FatturaElettronicaHeader
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @param FatturaElettronicaHeader $header
     * @return FatturaElettronica
     */
    public function setHeader($header)
    {
        $this->header = $header;
        $this->versione = $header->getDatiTrasmissione()->getFormatoTrasmissione();

        return $this;
    }

    /**
     * @return FatturaElettronicaBody[]
     */
    public function getBodys(): array
    {
        return $this->bodys;
    }

    /**
     * @param FatturaElettronicaBody[] $bodys
     * @return FatturaElettronica
     */
    public function setBodys($bodys): self
    {
        $this->bodys = $bodys;

        return $this;
    }

    /**
     * @param FatturaElettronicaBody $body
     * @return FatturaElettronica
     */
    public function addBody(FatturaElettronicaBody $body): self
    {
        $this->bodys[] = $body;
        if (count($this->bodys) > 1) {
            $this->setLotto(true);
        }

        return $this;
    }

    /**
     * @return array
     * @throws FatturaElettronicaException
     */
    public function toArray()
    {
        if (!$this->header instanceof FatturaElettronicaHeader) {
            throw new FatturaElettronicaException("Missing instance of FatturaElettronicaHeader");
        }

        $array = [
            '@versione' => $this->versione,
            '@xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
            '@xmlns:p' => 'http://ivaservizi.agenziaentrate.gov.it/docs/xsd/fatture/v1.2',
            '@xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
            '@xsi:schemaLocation' => 'http://ivaservizi.agenziaentrate.gov.it/docs/xsd/fatture/v1.2 http://www.fatturapa.gov.it/export/fatturazione/sdi/fatturapa/v1.2/Schema_del_file_xml_FatturaPA_versione_1.2.xsd',
            'FatturaElettronicaHeader' => $this->header->toArray(),
            'FatturaElettronicaBody' => null,
        ];

        if (count($this->getBodys()) == 0) {
            throw new FatturaElettronicaException("Missed instance of FatturaElettronicaBody");
        } else {
            if ($this->isLotto()) {
                foreach ($this->getBodys() as $body) {
                    $array['FatturaElettronicaBody'][] = $body->toArray();
                }
            } else {
                $array['FatturaElettronicaBody'] = $this->getBodys()[0]->toArray();
            }
        }

        return $array;
    }

    /**
     * @param array $array
     * @return FatturaElettronica
     * @throws FatturaElettronicaException
     */
    public static function fromArray(array $array)
    {
        if (!isset($array['FatturaElettronicaHeader'])) {
            throw new FatturaElettronicaException('Missing FatturaElettronicaHeader');
        }

        $header = FatturaElettronicaHeader::fromArray($array['FatturaElettronicaHeader']);
        $fatturaElettronica = new self($header);

        if (isset($array['FatturaElettronicaBody'])) {
            if (isset($array['FatturaElettronicaBody'][0])) {
                foreach ($array['FatturaElettronicaBody'] as $item) {
                    $fatturaElettronica->addBody(FatturaElettronicaBody::fromArray($item));
                }
            } else {
                $fatturaElettronica->addBody(FatturaElettronicaBody::fromArray($array['FatturaElettronicaBody']));
            }
        }

        return $fatturaElettronica;
    }

    /**
     * @param string $json
     * @return FatturaElettronica
     * @throws FatturaElettronicaException
     */
    public static function fromJson(string $json)
    {
        return FatturaElettronica::fromArray(json_decode($json, true));
    }

    /**
     * @return string
     */
    public function getVersione(): string
    {
        return $this->versione;
    }

    /**
     * @param string $versione
     * @return FatturaElettronica
     */
    public function setVersione(string $versione): self
    {
        $this->versione = $versione;

        return $this;
    }

    /**
     * @param FatturaElettronicaPrinterInterface $output
     */
    public function setOutput(FatturaElettronicaPrinterInterface $output): void
    {
        $this->output = $output;
    }

    /**
     * @return mixed
     */
    public function getOutput()
    {
        return $this->output->stampa(new FatturaElettronicaXmlWriter($this));
    }
}