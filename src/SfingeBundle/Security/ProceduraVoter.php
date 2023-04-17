<?php

namespace SfingeBundle\Security;

use SfingeBundle\Entity\Utente;
use SfingeBundle\Entity\Procedura;
use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;

class ProceduraVoter extends Voter {

    const READ = 'read';
    const WRITE = 'write';
    const COGEA = array(2, 5, 58, 64, 67, 70, 72, 75, 77, 83, 81, 107, 110, 112, 111, 116, 128, 140, 142, 161);

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
        if (!$subject instanceof Procedura) {
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

        if ($subject instanceof Procedura) {
            switch ($attribute) {
                case self::READ:
                    return $this->canRead($subject, $user);
                case self::WRITE:
                    return $this->canWrite($subject, $user);
            }
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canRead(Procedura $procedura, Utente $user) {
        if (in_array('ROLE_OPERATORE_COGEA', $user->getRoles()) && in_array($procedura->getId(), self::COGEA)) {
            return true;
        }
        $permesso = $this->findPermesso($procedura, $user);
        return !is_null($permesso);
    }

    private function canWrite(Procedura $procedura, Utente $user) {
        if (in_array('ROLE_OPERATORE_COGEA', $user->getRoles()) && in_array($procedura->getId(), self::COGEA)) {
            return true;
        }
        $permesso = $this->findPermesso($procedura, $user);
        return !is_null($permesso) && !$permesso->getSoloLettura();
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

}
