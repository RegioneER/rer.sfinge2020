<?php

namespace MessaggiBundle\Controller;

use BaseBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class MessaggiController extends BaseController {

	/**
	 * @Route("/test_email", name="test_email")
	 */
	public function testEmailAction() {

		//app_dev.php/messaggi/test_email (url per testare)
		//Preparo i parametri per inviare la mail
		//$to = $this->getUser()->getPersona()->getEmailPrincipale();
		$to = 'vdamico@schema31.it';
		$tipo = ""; //se si mette pec invia una pec
		$subject = "Test invio email sfinge";
		$parametriView = array();
		$renderViewTwig = "MessaggiBundle:Email:test.email.html.twig";
		$noHtmlViewTwig = "MessaggiBundle:Email:test.email.twig";

		try {
			$this->get("messaggi.email")->inviaEmail($to, $tipo, $subject, $renderViewTwig, $parametriView, $noHtmlViewTwig, $indirizzoAggiuntivo = null);
			$this->addFlash('success', "Email inviata con successo");
		} catch (\Exception $e) {
			$this->addFlash('danger', "Non è stato possibile inoltrare la Email : ". $e->getMEssage());
		}

		return $this->redirect($this->generateUrl('home'));
	}

}
