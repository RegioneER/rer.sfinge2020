<?php

namespace GeoBundle\Service;

use BaseBundle\Service\BaseService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use BaseBundle\Service\BaseServiceTrait;

class IstatAPIService
{
    use BaseServiceTrait;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container, string $csv_url)
    {
        $this->container = $container;
        $this->url = $csv_url;
    }

    public function getElencoComuni(){
        $raw = $this->download();
        $utf = \iconv("ISO-8859-1", "UTF-8", $raw);

        $righe = \str_getcsv($utf, "\n");
        \array_shift($righe);
        \array_shift($righe);
        \array_shift($righe);
        $elencoComuni = \array_map(function(string $riga){
            $record = \str_getcsv($riga, ";");
            $recordNormalized = \array_map(function($value){
                return \trim($value);
            }, $record);
            return $recordNormalized;
        },$righe);

        return $elencoComuni;
    }
    
    protected function download(){
        $curl = \curl_init($this->url);
        \curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_BINARYTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $logger = $this->container->get('logger');
        $info = \curl_getinfo($curl);

        $output = \curl_exec($curl);
        if(!$output){
            $error = \curl_error($curl);
            $logger->error($error);
            \curl_close($curl);
            throw new \Exception($error);
        }
        \curl_close($curl);

        return $output;
    }
}