<?php


namespace SoggettoBundle\Service;

use InvalidArgumentException;
use SoapClient;
use SoapFault;
use SoggettoBundle\Entity\Adrier;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

/**
 * Class Adrier
 */
class AdrierService
{
    const SERVER_PRODUZIONE = 'https://adrier.lepida.it/AdriGate/services/RicercaImprese';
    const WSLD_PRODUZIONE   = 'https://adrier.lepida.it/AdriGate/services/RicercaImprese?wsdl';

    const SERVER_TEST = 'https://adriertest.lepida.it/AdriGate/services/RicercaImprese';
    const WSDL_TEST   = 'https://adriertest.lepida.it/AdriGate/services/RicercaImprese?wsdl';

    const MODE_WSDL    = 'wsdl';
    const MODE_NO_WSDL = 'no_wsdl';

    /**
     * @var ContainerInterface
     */
    private $container;

    /** @var SoapClient */
    private $client;

    /** @var XmlEncoder */
    private $encoder;

    /**
     * Adrier constructor.
     *
     * @param ContainerInterface $container
     *
     * @throws SoapFault
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->encoder = new XmlEncoder();

        if ($this->container->getParameter('kernel.environment') === 'prod') {
            $this->inizializzaClient(self::WSLD_PRODUZIONE);
        } else {
            $this->inizializzaClient(self::WSLD_PRODUZIONE, self::MODE_WSDL, 1);
        }
    }

    /**
     * @return string
     */
    public function __getReq()
    {
        return htmlentities($this->client->__getLastRequest());
    }

    /**
     * @return string
     */
    public function __getRes()
    {
        return htmlentities($this->client->__getLastResponse());
    }

    /**
     * @param      $soapMode
     * @param int  $doTrace
     * @param bool $isTest
     *
     * @throws SoapFault
     */
    private function inizializzaClient($serverLocation, $soapMode = self::MODE_WSDL, $doTrace = 0)
    {
        switch ($soapMode) {
            case self::MODE_WSDL:
                $options = [
                    'trace'              => $doTrace,
                    'cache_wsdl'         => WSDL_CACHE_NONE,
                    'connection_timeout' => 540,
                ];

                $this->client = new SoapClient($serverLocation, $options);

                break;
            case self::MODE_NO_WSDL:
                $options = [
                    'location'           => $serverLocation,
                    'uri'                => $serverLocation,
                    'trace'              => $doTrace,
                    'cache_wsdl'         => WSDL_CACHE_NONE,
                    'connection_timeout' => 540,
                ];

                $this->client = new SoapClient(null, $options);

                break;
            default:
                throw new InvalidArgumentException('Error: invalid SOAP mode provided.');
                break;
        }
    }

    /**
     * @param $codiceFiscale
     *
     * @return array|mixed|string
     */
    public function ricercaImpresaPerCodiceFiscale($codiceFiscale)
    {
        $ricercaXml = $this->client->ricercaImpresePerCodiceFiscale($codiceFiscale, '', $this->container->getParameter("UTENTE_ADRIER"), $this->container->getParameter("PWD____ADRIER"));

        return $this->encoder->decode($ricercaXml,null);
    }

    /**
     * @param $codiceFiscale
     * @param $chiamata
     *
     * @return array|mixed|string
     */
    public function dettaglioAdrier($codiceFiscale, $chiamata)
    {
        try {
            $esitoRicerca = $this->ricercaImpresaPerCodiceFiscale($codiceFiscale);
        } catch (\Exception $e) {
            throw new \Exception('Errore comunicazione Adrier"');         
        }
        if ($esitoRicerca['HEADER']['ESITO'] === 'OK') {
            $elementiTrovati = $esitoRicerca['DATI']['LISTA_IMPRESE']['@totale'];

            if($elementiTrovati > 1) {
                $esitoRicerca['HEADER']['ESITO'] = 'KO';

                unset($esitoRicerca['DATI']['LISTA_IMPRESE']);

                $esitoRicerca['DATI']['ERRORE']['TIPO'] = 'occorrenza_max';
                $esitoRicerca['DATI']['ERRORE']['MSG_ERRORE'] = 'Numero di occorrenze trovato troppo alto';

            } else {
                $sgl_prov_sede = $esitoRicerca['DATI']['LISTA_IMPRESE']['ESTREMI_IMPRESA']['DATI_ISCRIZIONE_REA']['CCIAA'];
                $n_rea_sede = $esitoRicerca['DATI']['LISTA_IMPRESE']['ESTREMI_IMPRESA']['DATI_ISCRIZIONE_REA']['NREA'];

                $dettaglioXml = $this->client->{$chiamata}($sgl_prov_sede, $n_rea_sede, '', $this->container->getParameter("UTENTE_ADRIER"), $this->container->getParameter("PWD____ADRIER"));

                $dettaglioArray = $this->encoder->decode($dettaglioXml, null);

                $adrier = new Adrier($dettaglioArray);

                return $adrier;
            }
        }

        return new Adrier($esitoRicerca);
    }
}