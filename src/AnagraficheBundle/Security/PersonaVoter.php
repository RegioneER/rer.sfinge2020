<?php

namespace AnagraficheBundle\Security;

use AnagraficheBundle\Entity\Persona;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
use SfingeBundle\Entity\Utente;

class PersonaVoter extends Voter
{
    const SHOW = 'show';
	const EDIT = 'edit';
	
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
        if (!in_array($attribute, array(self::SHOW, self::EDIT))) {
            return false;
        }

        // only vote on Persona objects inside this voter
        if (!$subject instanceof Persona) {
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
		
        if ($this->decisionManager->decide($token, array('ROLE_ADMIN_PA', 'ROLE_SUPER_ADMIN'))) {
            return true;
        }			
		
        if (!$this->decisionManager->decide($token, array('ROLE_UTENTE'))) {
            return false;
        }
			
        return $user->getUsername() === $subject->getCreatoDa();
    }
	
}