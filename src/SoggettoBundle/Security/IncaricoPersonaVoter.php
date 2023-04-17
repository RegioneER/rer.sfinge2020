<?php

namespace SoggettoBundle\Security;

use SfingeBundle\Entity\Utente;
use SoggettoBundle\Entity\IncaricoPersona;
use SoggettoBundle\Entity\TipoIncarico;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class IncaricoPersonaVoter extends Voter
{
	const ALL = 'all';
	
	private $decisionManager;
	
    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }	

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::ALL))) {
            return false;
        }

        // only vote on Soggetto objects inside this voter
        if (!$subject instanceof IncaricoPersona) {
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
		
        /** @var \SoggettoBundle\Entity\IncaricoPersona $incaricoPersona */
        $incaricoPersona = $subject;
		
		if ($this->decisionManager->decide($token, array('ROLE_APPROVAZIONE_INCARICHI'))) {
			if (!in_array($incaricoPersona->getTipoIncarico()->getCodice(), array(TipoIncarico::LR, TipoIncarico::DELEGATO))) {
				return false;
			}
		}
		
		if ($this->decisionManager->decide($token, array('ROLE_UTENTE'))) {
			return $this->decisionManager->decide($token, array(SoggettoVoter::ALL), $incaricoPersona->getSoggetto());
		}

		if ($this->decisionManager->decide($token, array('ROLE_UTENTE_PA'))) {
			return $this->decisionManager->decide($token, array(SoggettoVoter::SHOW), $incaricoPersona->getSoggetto());
		}		

        return true;
    }

}