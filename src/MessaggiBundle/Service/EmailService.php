<?php

/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 11/01/16
 * Time: 10:20
 */

namespace MessaggiBundle\Service;

class EmailService {

	protected $container;
	protected $mailer;

	public function __construct($serviceContainer, $mailer) {
		$this->container = $serviceContainer;
		$this->mailer = $mailer;
	}

	//TODO da testare
	public function inviaEmail($emails, $tipo, $subject, $renderViewTwig, $parametriView, $noHtmlViewTwig = null) {
		
		$esito = new \stdClass();
		$esito->res = true;

		if(!$this->container->getParameter("invio.email.abilitato")) {
			$esito->res = false;
			$esito->error = 'Invio email notifica disabilitato';
			return $esito;
		}
		
		$mailer = null;
		$frommer = null;
		if ($tipo == 'pec') {
			$tr = \Swift_SmtpTransport::newInstance($this->container->getParameter('pec_mailer_host'), $this->container->getParameter('pec_mailer_port'), $this->container->getParameter('pec_mailer_encryption'))
					->setUsername($this->container->getParameter('pec_mailer_username'))
					->setPassword($this->container->getParameter('pec_mailer_password'));
			$mailer = \Swift_Mailer::newInstance($tr);
			$frommer = $this->container->getParameter('pec_mailer_from');
		} else {
			$mailer = $this->mailer;
			$frommer = $this->container->getParameter('mailer_from');
		}
		$message = \Swift_Message::newInstance()
				->setCharset("UTF-8")
				->setSubject($subject)
				->setFrom($frommer);

		foreach ($emails as $email) {
			$message->addTo($email);
		}

		if ($renderViewTwig && $noHtmlViewTwig) {
			$message->setBody($this->container->get("templating")->render($noHtmlViewTwig, $parametriView));
			$message->addPart($this->container->get("templating")->render($renderViewTwig, $parametriView), 'text/html');
		} else if ($renderViewTwig && !$noHtmlViewTwig) {
			$message->setBody($this->container->get("templating")->render($renderViewTwig, $parametriView), 'text/html');
		} else if (!$renderViewTwig && $noHtmlViewTwig) {
			$message->setBody($this->container->get("templating")->render($noHtmlViewTwig, $parametriView));
		}
		
		if(!$mailer->send($message) > 0) {
			$esito->res = false;
			$esito->error = 'Impossibile inviare la email';
		}
		return $esito;
	}

}
