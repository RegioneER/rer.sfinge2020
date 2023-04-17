<?php
namespace FunzioniServizioBundle\Service;

use Exception;
use IstruttorieBundle\Entity\PropostaImpegno;
use SoapClient;
use SoapFault;
use stdClass;

/**
 * Class PropostaImpegnoService
 */
class PropostaImpegnoService
{
    const MAX_TIME_LIMIT_ELAB = 600; // 10 minuti

    protected $container;
    protected $environment;

    /**
     * @var SoapClient
     */
    private $client;

    /**
     * @param $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        if ($this->container->getParameter('env') == 'prod') {
            $this->environment = 'Prod';
        } else {
            $this->environment = 'Dev';
        }
    }

    /**
     * @param $wsdl
     * @return void
     * @throws SoapFault
     */
    private function inizializzaClient($wsdl): void
    {
        if ($this->environment == 'Dev') {
            $pemFile = '/../../../app/config/certs/SFINGE_POT.pem';
        } elseif ($this->environment == 'Prod') {
            $pemFile = '/../../../app/config/certs/SFINGE_POP.pem';
        } else {
            return;
        }

        $serverLocation = __DIR__ . '/SapWsdl/' . $this->environment . '/' . $wsdl;

        $options = [
            'trace'              => 1,
            'cache_wsdl'         => WSDL_CACHE_NONE,
            'local_cert'         => __DIR__ . $pemFile,
            'connection_timeout' => 2400,
            'exceptions'         => 0,
            'authentication' => SOAP_AUTHENTICATION_DIGEST,
        ];

        $this->client = new SoapClientFunzioniServizio($serverLocation, $options);
    }

    /**
     * @param $propostaImpegno
     * @return stdClass
     */
    public function creaPropostaImpegno($propostaImpegno)
    {
        try {
            set_time_limit(self::MAX_TIME_LIMIT_ELAB);
            $this->inizializzaClient('Z_WS_PROPOSTE_IMPEGNI.wsdl');
            $dati = $this->getDatiPropostaImpegno($propostaImpegno);
            $result = $this->client->CreazioneImpegnoStart_BPM_out($dati);
            if (property_exists($result, 'esitoRichiesta')
                && property_exists($result, 'ProcessInstanceID')) {
                if ($result->esitoRichiesta == 'OK') {
                    $em = $this->container->get("doctrine")->getManager();
                    $propostaImpegno->setProcessInstanceId($result->ProcessInstanceID);
                    $em->persist($propostaImpegno);
                    $em->flush();
                    $stdObj = $result;
                } else {
                    $stdObj = new stdClass();
                    $stdObj->esitoRichiesta = 'NOK';
                    $stdObj->messaggi = 'Esito non OK ' . $result;
                    $this->container->get("logger")->error($result);
                }
            } else {
                $stdObj = new stdClass();
                $stdObj->esitoRichiesta = 'NOK';
                $stdObj->messaggi = 'Nessun esito ' . $result;
                $this->container->get("logger")->error($result);
            }
        } catch (SoapFault $e) {
            $stdObj = new stdClass();
            $stdObj->esitoRichiesta = 'NOK';
            $stdObj->messaggi = 'Nessun esito ' . $e->getMessage();
            $this->container->get("logger")->error($e->getMessage());
        }

        return $stdObj;
    }

    /**
     * @param PropostaImpegno $propostaImpegno
     * @return void
     */
    protected function getDatiPropostaImpegno(PropostaImpegno $propostaImpegno)
    {
        $propostaImpegnoArray = [
            'I_RIF_IMPEGNO' => $propostaImpegno->getId(), // ID PropostaImpegno (Facoltativo)
            'I_RETURN_PROPOSTA' => True, // Restituisce o meno l'ID della proposta creata? (Facoltativo)
            'I_KBLK' => [
                'BLDAT' => $propostaImpegno->getBldat()->format('Y-m-d'), // Data doc nel documento (Obbligatorio)
                'KTEXT' => $propostaImpegno->getKtext(), // Testo testata documento (Facoltativo)
                'BUKRS' => $propostaImpegno->getBukrs(), // Società  (Obbligatorio)
                'BUDAT' => $propostaImpegno->getBudat()->format('Y-m-d'), // Data impegno? (Data di registrazione nel documento) (Obbligatorio)
                'ZZPROTOCOLLO' => $propostaImpegno->getZzProtocollo(), // Protocollo (Facoltativo - alternativo a I_TESTO)
                'ZZNUMRIPARTIZ' => $propostaImpegno->getZzNumRipartiz(), // Numero ripartizione (Facoltativo)
                'ZZTIPODOC' => $propostaImpegno->getZzTipoDoc(), // Tipo documento (Obbligatorio)
                'ZZPROGR_PROG' => $propostaImpegno->getZzProgrProg(), // Progressivo progetto (Facoltativo)
                'ZZCONTR_IMP' => $propostaImpegno->getZzContrImp(), // Tipo gestione impegno (Obbligatorio)
                'ZZASSENZA_ATTO' => $propostaImpegno->getZzAssenzaAtto(), // Flag assenza atto (Facoltativo)
                'ZZFIPOS' => $propostaImpegno->getZzFipos(), // Capitolo (Obbligatorio)
                'ZZPRENOTAZIONE' => $propostaImpegno->getZzPrenotazione(), // Prenotazione (Facoltativo)
                'ZZBELNR_RIF' => $propostaImpegno->getZzBelnrRif(), // Numero documento: fondi accantonati (Facoltativo)
                'ZZPROGR_RIF' => $propostaImpegno->getZzProgrRif(), // Progressivo variazione (Facoltativo)
            ],
        ];

        $posizioniPropostaImpegno = [];
        foreach ($propostaImpegno->getPosizioniPropostaImpegno() as $posizioneImpegno) {
            $temp = [];

            if (!empty($posizioneImpegno->getPtext())) {
                $temp['PTEXT'] =  $posizioneImpegno->getPtext();
            }

            if (!empty($posizioneImpegno->getZzCig())) {
                $temp['ZZCIG'] =  $posizioneImpegno->getZzCig();
            }

            if (!empty($posizioneImpegno->getZzCodFormAv())) {
                $temp['ZZ_COD_FORM_AV'] =  $posizioneImpegno->getZzCodFormAv();
            }

            $temp['LIFNR'] = $posizioneImpegno->getLifnr();
            $temp['ZZCUP'] = $posizioneImpegno->getZzCup();
            $temp['ZZLIVELLO5'] = $posizioneImpegno->getZzLivello5();
            $temp['WTGES'] = $posizioneImpegno->getWtges();

            $posizioniPropostaImpegno[] = $temp;
        }

        $propostaImpegnoArray['I_KBLP'] = $posizioniPropostaImpegno;
        return $propostaImpegnoArray;
    }

    public function inviaNotifica($env = 'Dev')
    {
        try {
            set_time_limit(self::MAX_TIME_LIMIT_ELAB);
            $this->inizializzaClient('Z_WS_SERVER_PROPOSTE_IMPEGNI.wsdl');

            $result = $this->client->CreazioneImpegnoRisultato_in([
                'I_PROPOSTA' => '1',
                'I_IMPEGNO' => '1',
                'I_DESCR_RC' => '-',
                'I_RC' => '-',
                'I_RIF_IMPEGNO' => '-',
                'IT_MESG' => '-',
            ]);

        } catch (Exception $e) {dump('ll');
            throw $e;
        }

        $ee = $this->client->__getLastRequestHeaders();
        dump($ee);

        return $result;
    }
}
