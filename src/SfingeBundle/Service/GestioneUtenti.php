<?php

namespace SfingeBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use UtenteBundle\Form\GestioneUtenteAdminType;

class GestioneUtenti extends \BaseBundle\Service\BaseService {

	protected $em;
	protected $container;

	public function __construct(ContainerInterface $container) {
		parent::__construct($container);
		$this->em = $this->container->get("doctrine")->getManager();
	}

	public function getEM() {
		return $this->em;
	}

	public function getArrayRuoli($da_escludere = array()) {
		$roles_choices = array();
		//metodo copiato su stackoverflow
		$roles = $this->container->getParameter('security.role_hierarchy.roles');

		foreach ($roles as $role => $inherited_roles) {
			foreach ($inherited_roles as $id => $inherited_role) {
				if (!array_key_exists($inherited_role, $roles_choices)) {
					$roles_choices[$inherited_role] = $inherited_role;
				}
			}

			if (!array_key_exists($role, $roles_choices)) {
				$roles_choices[$role] = $role . ' (' .
						implode(', ', $inherited_roles) . ')';
			}
		}

		foreach ($da_escludere as $ruolo) {
			unset($roles_choices[$ruolo]);
		}

		return $roles_choices;
	}

	public function gestioneUtente($utente) {
		$userManager = $userManager = $this->container->get('fos_user.user_manager');
		$loggedUser = $this->getUser();

		$opzioni["ruoli"] = $loggedUser->hasRole("ROLE_SUPER_ADMIN") ? $this->getArrayRuoli() : $this->getArrayRuoli(array("ROLE_SUPER_ADMIN"));
		$form = $this->createForm(GestioneUtenteAdminType::class, $utente, $opzioni);
		$request = $this->getCurrentRequest();
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$utente->setCreatoDa($loggedUser->getUsername());
			$utente->setCreatoIl(new \DateTime());
			$utente->setEnabled(true);
			
			$factory = $this->container->get('security.encoder_factory');
			$encoder = $factory->getEncoder($utente);
			$password = $encoder->encodePassword('password', $utente->getSalt());
			$utente->setPassword($password);

			$userManager->updateUser($utente);
			$this->addFlash(self::msg_ok, "Utente creato correttamente");
			return $this->redirect($this->generateUrl('elenco_utenti', array()));
		}
		return $this->render('UtenteBundle:Utente:creaUtente.html.twig', array(
					'form' => $form->createView(),
		));
	}

	public function getRuoliUtente($utente) {
		$roles_choices = array();
		$roles = $this->container->getParameter('security.role_hierarchy.roles');

		$ruoliUtente = $utente->getRoles();
		$roles_choices = $ruoliUtente;

		foreach ($ruoliUtente as $ruoloUtente) {
			if (array_key_exists($ruoloUtente, $roles)) {
				foreach ($roles[$ruoloUtente] as $ruolo) {
					if (!in_array($ruolo, $roles_choices)) {
						$roles_choices[] = $ruolo;
					}
				}
			}
		}

		foreach ($roles_choices as $ruoloUtente) {
			if (array_key_exists($ruoloUtente, $roles)) {
				foreach ($roles[$ruoloUtente] as $ruolo) {
					if (!in_array($ruolo, $roles_choices)) {
						$roles_choices[] = $ruolo;
					}
				}
			}
		}


		return $roles_choices;
	}

}
