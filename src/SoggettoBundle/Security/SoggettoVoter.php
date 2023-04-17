<?php

namespace SoggettoBundle\Security;

use SfingeBundle\Entity\Utente;
use SoggettoBundle\Entity\Soggetto;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class SoggettoVoter extends Voter
{
	const ALL = 'all';
	const SHOW = 'show';
	const EDIT = 'edit';
	
	private $decisionManager;
	
    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }	

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::ALL,self::SHOW,self::EDIT))) {
            return false;
        }

        // only vote on Soggetto objects inside this voter
        if (!$subject instanceof Soggetto) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof Utente) {
            // the user must be logged in; if not, deny access
            return false;
        }
		if($attribute == self::SHOW && $this->decisionManager->decide($token, array('ROLE_UTENTE_PA','ROLE_SUPER_ADMIN'))){
			return true;
		}
		
		if($attribute == self::EDIT && $this->decisionManager->decide($token, array('ROLE_ADMIN_PA','ROLE_SUPER_ADMIN'))){
			return true;
		}
		
        if (!$this->decisionManager->decide($token, array('ROLE_UTENTE'))) {
            return false;
        }		

        // you know $subject is a Post object, thanks to supports
        /** @var Soggetto $soggetto */
        $soggetto = $subject;

        switch($attribute) {
            case self::ALL:
			case self::EDIT:
                return $this->canAll($soggetto, $user);
			case self::SHOW:
				return $this->canShow($soggetto, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canAll(Soggetto $soggetto, Utente $user)
    {
		$incarichi = $soggetto->getIncarichiPersone();

		foreach ($incarichi as $incarico) {
			$utente = $incarico->getIncaricato()->getUtente();
			if($incarico->getStato()->getCodice() == "ATTIVO" && !is_null($utente) && in_array($incarico->getTipoIncarico()->getCodice(), array('UTENTE_PRINCIPALE', 'OPERATORE', 'CONSULENTE', 'LR', 'DELEGATO'))) {
				if ($utente->getId() == $user->getId()) {
					return true;
				}
			}
		}
		
		return false;
    }
	
	private function canShow(Soggetto $soggetto, Utente $user)
    {
		$incarichi = $soggetto->getIncarichiPersone();

		foreach ($incarichi as $incarico) {
			$utente = $incarico->getIncaricato()->getUtente();
			if($incarico->getStato()->getCodice() == "ATTIVO" && !is_null($utente) && in_array($incarico->getTipoIncarico()->getCodice(), array('UTENTE_PRINCIPALE', 'OPERATORE', 'OPERATORE_RICHIESTA', 'CONSULENTE', 'LR', 'DELEGATO'))) {
				if ($utente->getId() == $user->getId()) {
					return true;
				}
			}
		}
		
		return false;
    }
}