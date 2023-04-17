<?php
namespace FunzioniServizioBundle\Service;

use Exception;
use Monolog\Logger;
use SoapClient;
use SoapFault;

class SoapClientFunzioniServizio extends SoapClient
{
    const MAX_TIME_LIMIT_ELAB = 600; // 10 minuti

    protected $container;
    /** @var Logger $logger */
    protected $logger;

    /**
     * @param $serverLocation
     * @param $options
     * @throws SoapFault
     */
    public function __construct($serverLocation, $options)
    {
        parent::__construct($serverLocation, $options);
    }

    /**
     * @param $request
     * @param $location
     * @param $action
     * @param $version
     * @param $one_way
     * @return mixed
     */
    function __doRequest($request, $location, $action, $version, $one_way = 0)
    {
        //$this->log($request, $location, $action, $version);

        try {
            $response = parent::__doRequest($request, $location, $action, $version, $one_way);
        } catch (Exception $e) {
            $this->container->get("logger")->error($e->getMessage());
        }

        //$this->log($response, $location, $action, $version);
        return $response;
    }

    /**
     * @param $request
     * @param $location
     * @param $action
     * @param $version
     * @return void
     */
    public function log($request, $location, $action, $version)
    {
        $this->logger->error('Message', [
            'request' => $request,
        ]);
    }
}