<?php

namespace UtenteBundle\Controller;

use AnagraficheBundle\Entity\Persona;
use BaseBundle\Controller\BaseController;
use DateTime;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use SfingeBundle\Entity\Utente;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use PaginaBundle\Annotations\PaginaInfo;


use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class RegistrationController extends BaseController {

	public function registerAction(Request $request) {

		$formFactory = $this->get('fos_user.registration.form.factory');
		$userManager = $this->get('fos_user.user_manager');

		$dispatcher = $this->get('event_dispatcher');
		$user = $userManager->createUser();
		$user->setEnabled(true);
		$event = new GetResponseUserEvent($user, $request);
		$dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);
		if (null !== $event->getResponse()) {
			return $event->getResponse();
		}
		$form = $formFactory->createForm();
		$form->setData($user);
		$form->handleRequest($request);
		if ($form->isValid()) {
			$event = new FormEvent($form, $request);
			$dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);
			$user->setRoles(array('ROLE_USER', 'ROLE_UTENTE'));
			$user->setCreatoDa($user->getUsername());
			$user->setCreatoIl(new DateTime());
			$userManager->updateUser($user);
			if (null === $response = $event->getResponse()) {
				$url = $this->generateUrl('fos_user_registration_confirmed');
				$response = new RedirectResponse($url);
			}
			$dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));
			return $response;
		}
		return $this->render('FOSUserBundle:Registration:register.html.twig', array('form' => $form->createView(),
		));
	}

	/**
	 * @Route("/registrazione_federa", name="federa_user_registration_register")
	 */

	public function registrazioneFederaAction(Request $request) {
		
		$em = $this->getDoctrine()->getManager();
		$userManager = $this->get('fos_user.user_manager');
		
		//test
		//$cf = 'utente18';
        
        $cf = $request->headers->get('codicefiscale'); 
        $email = $request->headers->get('emailaddress'); 
        $username = $cf;
			
		$user = $em->getRepository('SfingeBundle\Entity\Utente')->findOneByUsername($username);
		if(!$user){
			//creo un nuovo utente
			$user = $userManager->createUser();
			$user->setUsername($username);

			$factory = $this->get('security.encoder_factory');
			$encoder = $factory->getEncoder($user);
			$password = $encoder->encodePassword('password', $user->getSalt());
			$user->setPassword($password);

			$user->setEnabled(true);
			if(isset($email)){
				$user->setEmail($email);
			}

			$user->setRoles(array('ROLE_USER','ROLE_UTENTE'));

			$user->setCreatoDa($user->getUsername());
			$user->setCreatoIl(new DateTime());
            try {
                $userManager->updateUser($user);

                /** @var Utente $utenteCreato */
                $utenteCreato = $em->getRepository('SfingeBundle\Entity\Utente')->findOneByUsername($username);

                $personaEsistente = $em->getRepository("AnagraficheBundle\Entity\Persona")->cercaPersoneByCf($utenteCreato->getUsername());

               if(isset($personaEsistente[0]) && $personaEsistente[0] instanceof Persona) {
                    $utenteCreato->setPersona($personaEsistente[0]);
                    $utenteCreato->setDatiPersonaInseriti(true);
                    $utenteCreato->setEmail($personaEsistente[0]->getEmailPrincipale());
                    $utenteCreato->setEmailCanonical($personaEsistente[0]->getEmailPrincipale());
                }

                $em->persist($utenteCreato);

                $em->flush();

            } catch (UniqueConstraintViolationException $e ) {
                $this->get('logger')->error($e->getMessage());
                return $this->addErrorRedirect("L'indirizzo email {$email} è già presente a sistema. Si richiede di modificare l'indirizzo email su Federa e di ritentare l'accesso", "errore_registrazione");
            }

		}
		
		//se l'utente esiste forzo il login

		$token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
		$this->get('security.context')->setToken($token);
		$this->get('session')->set('_security_main', serialize($token));

		$user->setLastLogin(new DateTime());
		$userManager->updateUser($user);
						
		return $this->redirect($this->generateUrl('home'));
		
	}
    
    /**
	 * @Route("/errore_registrazione", name="errore_registrazione")
	 * @PaginaInfo(titolo="Errore", sottoTitolo="")
	 */
    public function erroreRegistrazioneAction(){
		return $this->render('UtenteBundle:Utente:erroreRegistrazione.html.twig');
    }

}
