<?php

namespace SfingeBundle\Security;

use SfingeBundle\Entity\Utente;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

class UtenteVoter extends Voter
{
    const ALL = 'all';
	
	private $decisionManager;
	private $doctrine;
	private $em;
	
    public function __construct(AccessDecisionManagerInterface $decisionManager, Registry $doctrine)
    {
        $this->decisionManager = $decisionManager;
		$this->doctrine = $doctrine;
		$this->em = $this->doctrine->getManager();
    }	

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::ALL))) {
            return false;
        }

        // only vote on Procedura objects inside this voter
        if (!$subject instanceof Utente) {
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
		
        if ($this->decisionManager->decide($token, array('ROLE_SUPER_ADMIN'))) {
            return true;
        }		
		
        if ($this->decisionManager->decide($token, array('ROLE_ADMIN_PA'))) {
			return !$subject->hasRole("ROLE_SUPER_ADMIN");
        }
			
        return false;
    }
	
}