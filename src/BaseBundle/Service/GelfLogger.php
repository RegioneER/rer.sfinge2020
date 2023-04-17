<?php

namespace BaseBundle\Service;
// require "Cloud31_GELFLog/GELFLog.php";

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

/**
 * Description of GelfLogger
 *
 * @author cblanda - aturdo
 */
class GelfLogger extends AbstractProcessingHandler {
	
    private $container;
    private $validLevel;
    
    const EMERGENCY = 0;
    const ALERT = 1;
    const CRITICAL = 2;
    const ERROR = 3;
    const WARNING = 4;
    const NOTICE = 5;
    const INFO = 6;
    const DEBUG = 7;

    public function __construct($container, $level = Logger::NOTICE, $bubble = true)
    {
        
        parent::__construct($level, $bubble);
        $this->container = $container;
        
        $this->validLevel = array(
			'EMERGENCY' => self::EMERGENCY,
			'ALERT' => self::ALERT,
			'CRITICAL' => self::CRITICAL,
			'ERROR' => self::ERROR,
			'WARNING' => self::WARNING,
			'NOTICE' => self::NOTICE,
			'INFO' => self::INFO,
			'DEBUG' => self::DEBUG);
              
    }

    protected function write(array $record)
    {
        if (!$this->container->hasParameter('gl')) {
            return;
        }
        
        $params = $this->container->getParameter('gl');
        
        if (!array_key_exists("stream", $params) || !array_key_exists("auth", $params)) {
            return;
        }
        
        $streamName = $params["stream"];
        $authentication = $params["auth"];
        
        $GELFLog = new GELFLog($streamName, $authentication);
        if(array_key_exists('facility', $record['context'])){           
            $facility = $record['context']['facility'];
            $GELFLog->message->setFacility($facility);
        } else {
             $GELFLog->message->setFacility('none');
        }
        
        $GELFLog->message->setFile(__FILE__);
		if (!is_null($record['message']) && $record['message'] != "") {
			$GELFLog->message->setShortMessage($record['message']);
		} else {
			$GELFLog->message->setShortMessage("Contenuto del log vuoto");
		}
        $GELFLog->message->setLevel($this->validLevel[$record['level_name']]);

        $utente = $this->getUser();
		$additionalData = array();
        if(is_object($utente) && method_exists($utente, 'getUsername')){
            $username_utente = $utente->getUsername();
            $additionalData['username_utente'] = $username_utente;
        }
		
		$additionalData['channel'] = $record['channel'];
		$additionalData['time'] = $record['datetime']->format('Y-m-d H:i:s');
		
		foreach ($additionalData as $key => $value) {
			$GELFLog->message->setAdditional($key, $value);
		}		
        
        $fullMsg = $record['message'];
                
        $GELFLog->message->setFullMessage($fullMsg);
        
        $GELFLog->publish();

    }
    
    public function getUser() {
        $user = null;
        $securityContext = $this->container->get('security.context');
        if($securityContext !== null) {
                $securityToken = $securityContext->getToken();
                if($securityToken !== null) {
                        $user = $securityToken->getUser();
                }
        }

        return $user;
    }

}
