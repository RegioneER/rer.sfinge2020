<?php

namespace ProtocollazioneBundle\Entity;

/**
 * Description of EmailSendableInterface
 * 
 * Visto che nelle varie RichiesteProtocollo si arriva in modo diverso al Soggetto
 * ogni richiesta protocollo in uscita che dovrà inviare email tramite egrammata deve implementare questa interface 
 * 
 * Facciamo lo stesso ragionamento per il testo dell'email che potrebbe dover essere recuperato in maniera diversa
 * 
 * @author gdisparti
 */
interface EmailSendableInterface {
	public function getDestinatarioEmailProtocollo();
	
	public function getTestoEmailProtocollo();
}
