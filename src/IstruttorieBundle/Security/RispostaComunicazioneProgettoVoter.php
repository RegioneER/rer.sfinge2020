<?php

namespace IstruttorieBundle\Security;

use SfingeBundle\Entity\Utente;
use IstruttorieBundle\Entity\RispostaComunicazioneProgetto;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

class RispostaComunicazioneProgettoVoter extends Voter {

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

		// only vote on IntegrazionePagamento objects inside this voter
		if (!$subject instanceof RispostaComunicazioneProgetto) {
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

		if (!$this->decisionManager->decide($token, array('ROLE_UTENTE_PA', 'ROLE_UTENTE'))) {
			return false;
		}

		switch ($attribute) {
			case self::READ:
				return $this->canRead($subject, $user);
			case self::WRITE:
				return $this->canWrite($subject, $user);
		}

		throw new \LogicException('This code should not be reached!');
	}

	private function canRead(RispostaComunicazioneProgetto $comunicazione, Utente $user) {
		if (in_array('ROLE_UTENTE_PA', $user->getRoles())) {
			$permesso = $this->findPermesso($comunicazione->getRichiesta()->getProcedura(), $user);
			return !is_null($permesso);
		}
		if (in_array('ROLE_UTENTE', $user->getRoles())) {
			$permesso = $this->findPermessoRichiesta($comunicazione->getRichiesta(), $user);
			return $permesso;
		}
	}

	private function canWrite(RispostaComunicazioneProgetto $comunicazione, Utente $user) {
		if (in_array('ROLE_UTENTE_PA', $user->getRoles())) {
			$permesso = $this->findPermesso($comunicazione->getRichiesta()->getProcedura(), $user);
			return !is_null($permesso);
		}
		if (in_array('ROLE_UTENTE', $user->getRoles())) {
			$permesso = $this->findPermessoRichiesta($comunicazione->getRichiesta(), $user);
			return $permesso;
		}
	}

	private function findPermesso(Procedura $procedura, Utente $user) {
		$permessoProcedura = $this->em->getRepository('SfingeBundle:PermessiProcedura')->findOneBy(array("utente" => $user, "procedura" => $procedura));

		if ($permessoProcedura) {
			return $permessoProcedura;
		}

		$permessoAsse = $this->em->getRepository('SfingeBundle:PermessiAsse')->findOneBy(array("utente" => $user, "asse" => $procedura->getAsse()));

		if ($permessoAsse) {
			return $permessoAsse;
		}

		return null;
	}

	private function findPermessoRichiesta($richiesta, $user) {
		$abilitatoSoggetto = false;
		$abilitatoRichiesta = false;
		
		$soggettiGestiti = $this->em->getRepository('SoggettoBundle:Soggetto')->cercaTuttiDaPersonaIncaricoNoRichiesta($user->getPersona()->getId());
		if (in_array($richiesta->getSoggetto()->getId(), $soggettiGestiti)) {
			$abilitatoSoggetto = true;
		}
		
		$incarichiRichieste = $this->em->getRepository('SoggettoBundle:IncaricoPersonaRichiesta')->getRichiesteIncaricato($richiesta, $user->getPersona());
		if (in_array($richiesta->getId(), $incarichiRichieste)) {
			$abilitatoRichiesta =  true;
		}
		return $abilitatoSoggetto || $abilitatoRichiesta;
	}

}
