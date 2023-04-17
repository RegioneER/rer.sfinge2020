<?php

namespace ProtocollazioneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Log
 *
 * @ORM\Table(name="log_service")
 * @ORM\Entity(repositoryClass="ProtocollazioneBundle\Repository\LogRepository")
 */
class Log
{
	
    /**
     * @var int $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int $richiesta_protocollo_id
     *
     * @ORM\Column(name="richiesta_protocollo_id", type="integer", nullable=false, options={"default":0})
     */
    private $richiesta_protocollo_id = 0;
        
    /**
     * @var \DateTime $log_time
     *
     * @ORM\Column(name="log_time", type="datetime")
     */
    private $log_time;

    /**
     * @var string $message
     *
     * @ORM\Column(name="message", type="text", nullable=true)
     */
    private $message;

    /**
     * @var string $app_function_target
     *
     * @ORM\Column(name="app_function_target", type="string", length=255, nullable=true)
     */
    private $app_function_target;

    /**
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=10, nullable=true)
     */
    private $code;

    /**
     * @var string $app_function
     *
     * @ORM\Column(name="app_function", type="string", length=255, nullable=true)
     */
    private $app_function;

	
	/**
	 * @ORM\Column(name="fase_richiesta", type="string", length=2, nullable=true)
	 */
	private $fase_richiesta;
	

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Get richiesta_protocollo_id
     *
     * @return int
     */
    function getRichiesta_protocollo_id() 
    {
        return $this->richiesta_protocollo_id;
    }

    
    /**
     * Set richiesta_protocollo_id
     *
     * @param $richiesta_protocollo_id
     *
     * @return Log
     */
    function setRichiesta_protocollo_id($richiesta_protocollo_id) 
    {
        $this->richiesta_protocollo_id = $richiesta_protocollo_id;
    }


    /**
     * Set logTime
     *
     * @param $logTime
     *
     * @return Log
     */
    public function setLogTime($logTime)
    {
        $this->log_time = $logTime;

        return $this;
    }

    /**
     * Get logTime
     *
     * @return \DateTime
     */
    public function getLogTime()
    {
        return $this->log_time;
    }

    /**
     * Set message
     *
     * @param $message
     *
     * @return Log
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set appFunctionTarget
     *
     * @param string $appFunctionTarget
     *
     * @return Log
     */
    public function setAppFunctionTarget($appFunctionTarget)
    {
        $this->app_function_target = $appFunctionTarget;

        return $this;
    }

    /**
     * Get appFunctionTarget
     *
     * @return string
     */
    public function getAppFunctionTarget()
    {
        return $this->app_function_target;
    }
    
    /**
     * Set setCode
     *
     * @param string $code
     *
     * @return Log
     */
    function setCode($code) {
        $this->code = $code;
    }

    /**
     * Get getCode
     *
     * @return string
     */
    function getCode() {
        return $this->code;
    }

    /**
     * Set appFunction
     *
     * @param string $appFunction
     *
     * @return Log
     */
    public function setAppFunction($appFunction)
    {
        $this->app_function = $appFunction;

        return $this;
    }

    /**
     * Get appFunction
     *
     * @return string
     */
    public function getAppFunction()
    {
        return $this->app_function;
    }
	
	
	function getFaseRichiesta() {
		return $this->fase_richiesta;
	}

	function setFaseRichiesta($fase_richiesta) {
		$this->fase_richiesta = $fase_richiesta;
	}



    
}
