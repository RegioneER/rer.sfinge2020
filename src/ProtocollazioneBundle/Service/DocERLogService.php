<?php

namespace ProtocollazioneBundle\Service;

use ProtocollazioneBundle\Entity\Log;

/**
 * Servizio per la memorizzazione su database dei messaggi di log
 *
 * @author Davide Cannistraro
 */
class DocERLogService {
       
	const MAX_MESSAGE_SIZE = 20000;
    protected $em;

    public function __construct($doctrine) {
        $this->em = $doctrine->getManager();        
    }
    
	
    protected $richiestaProt;
    function getRichiestaProt() { return $this->richiestaProt; }
    function setRichiestaProt($richiestaProt) { $this->richiestaProt = $richiestaProt; }

	protected $faseRichiestaProt;
	function getFaseRichiestaProt() { return $this->faseRichiestaProt; }
	function setFaseRichiestaProt($faseRichiestaProt) { $this->faseRichiestaProt = $faseRichiestaProt; }

		
        
    public function createLog($message, $app_function, $app_function_target = null, $code = null)
    {        
        $log_time = new \DateTime('NOW');
        
        try {
            $richiestaProtocolloiD = $this->getRichiestaProt();
			$fase_richiesta = $this->getFaseRichiestaProt();
            $Log = new Log();
            if (\is_null($richiestaProtocolloiD)) {
                $richiestaProtocolloiD = 0;
            }
            $Log->setRichiesta_protocollo_id($richiestaProtocolloiD);
            $Log->setLogTime($log_time);
			
			if(!\is_null($message)) {
				if(strlen($message) > self::MAX_MESSAGE_SIZE) $message="messaggio troppo grande";
				else $message = base64_encode ($message);
			}
			
            $Log->setMessage($message);
            $Log->setAppFunctionTarget($app_function_target);
            $Log->setCode($code);
            $Log->setAppFunction($app_function);
            $Log->setFaseRichiesta($fase_richiesta);

            $this->em->persist($Log);                      //comunico a Doctrine che l’oggetto appena creato necessita di essere gestito  
            $this->em->flush();                             //inserisco effettivamente l’oggetto nel database
            
            return true;
            
        } catch (\Exception $ex) {
            return false;
        }
        
    }
    
}
