<?php

namespace BaseBundle\Security;

use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @author Antonio Turdo <aturdo@schema31.it>
 */
class AnonymousVoter extends Voter
{
    /**
     * The role check agains
     */
    const IS_ANONYMOUS = 'IS_ANONYMOUS';

    /**
     * @var AuthenticationTrustResolverInterface $authenticationTrustResolver
     */
    protected $authenticationTrustResolver;

    /**
     * @param AuthenticationTrustResolverInterface $authenticationTrustResolver
     */
		public function __construct(AuthenticationTrustResolverInterface $authenticationTrustResolver)
    {
        $this->authenticationTrustResolver = $authenticationTrustResolver;
    }
	
    protected function supports($attribute, $subject)
    {
        return static::IS_ANONYMOUS === $attribute;
    }
	


    /**
     * Only allow access if the TokenInterface isAnonymous. But abstain from voting
     * if the attribute IS_ANONYMOUS isnt supported.
     *
	 * @param string $attribute
	 * @param object $subject
     * @param TokenInterface $token
     * @return integer
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)	
    {
		if (!$this->supports($attribute, $subject)) {
			return null;
		}

            // If the user is anonymous then grant access otherwise deny!
		if ($this->authenticationTrustResolver->isAnonymous($token)) {
			return true;
		}

		return false;
    }
}
