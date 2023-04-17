<?php

namespace SfingeBundle\Security;

use SfingeBundle\Entity\Utente;
use SfingeBundle\Entity\Asse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

class AsseVoter extends Voter {

	const READ = 'read';
	const WRITE = 'write';

	private $decisionManager;
	private $doctrine;
	private $em;

	public function __construct(AccessDecisionManagerInterface $decisionManager, Registry $doctrine) {
		$this->decisionManager = $decisionManager;
		$this->doctrine = $doctrine;
		$this->em = $this->doctrine->getManager();
	}

	protected function supports($attribute, $subject) {
		// if the attribute isn't one we support, return false
		if (!in_array($attribute, array(self::READ, self::WRITE))) {
			return false;
		}

		// only vote on Procedura objects inside this voter
		if (!$subject instanceof Asse) {
			return false;
		}

		return true;
	}

	protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
		$user = $token->getUser();

		if (!$user instanceof Utente) {
			// the user must be logged in; if not, deny access
			return false;
		}

		if ($this->decisionManager->decide($token, array('ROLE_SUPER_ADMIN'))) {
			return true;
		}

		if ($this->decisionManager->decide($token, array('ROLE_ADMIN_PA'))) {
			return true;
		}

		if (!$this->decisionManager->decide($token, array('ROLE_UTENTE_PA'))) {
			return false;
		}
		
		if ($subject instanceof Asse) {
			switch ($attribute) {
				case self::READ:
					return $this->canRead($subject, $user);
				case self::WRITE:
					return $this->canWrite($subject, $user);
			}
		}

		throw new \LogicException('This code should not be reached!');
	}

	private function canRead(Asse $asse, Utente $user) {
		$permesso = $this->findPermesso($asse, $user);

		return !is_null($permesso);
	}

	private function canWrite(Asse $asse, Utente $user) {
		$permesso = $this->findPermesso($asse, $user);

		return !is_null($permesso) && !$permesso->getSoloLettura();
	}

	private function findPermesso(Asse $asse, Utente $user) {
		$permessoAsse = $this->em->getRepository('SfingeBundle:PermessiAsse')->findOneBy(array("utente" => $user, "asse" => $asse));

		if ($permessoAsse) {
			return $permessoAsse;
		}

		return null;
	}

}
