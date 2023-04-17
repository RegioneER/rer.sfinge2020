<?php

namespace RichiesteBundle\Security;

use Doctrine\Common\Collections\ArrayCollection;
use SfingeBundle\Entity\Utente;
use RichiesteBundle\Entity\Richiesta;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Doctrine\Bundle\DoctrineBundle\Registry;

class RichiestaLegge14Voter extends Voter {

    const READ     = 'read';
    const WRITE    = 'write';
    const PRESENT  = 'present';
    const VALIDATE = 'validate';
    const SEND     = 'send';

    /**
     * @var AccessDecisionManagerInterface
     */
    private $decisionManager;

    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager|object
     */
    private $em;

    /**
     * RichiestaLegge14Voter constructor.
     * @param AccessDecisionManagerInterface $decisionManager
     * @param Registry $doctrine
     */
    public function __construct(AccessDecisionManagerInterface $decisionManager, Registry $doctrine)
    {
        $this->decisionManager = $decisionManager;
        $this->doctrine = $doctrine;
        $this->em = $this->doctrine->getManager();
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        // If the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::READ, self::WRITE, self::PRESENT, self::VALIDATE, self::SEND))) {
            return false;
        }

        if(!(in_array($attribute, array(self::PRESENT, self::VALIDATE, self::SEND)))) {
            if (!$subject instanceof Richiesta) {
                return false;
            }

            // Questo voter è solo per il bando Legge 14.
            if ($subject->getProcedura()->getId() != 98) {
                return false;
            }
        } else {
            if(!(is_array($subject['richieste']))) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param Richiesta|ArrayCollection $subject
     * @param TokenInterface $token
     * @return bool|object|\SfingeBundle\Entity\PermessiAsse|\SfingeBundle\Entity\PermessiProcedura|null
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof Utente) {
            // The user must be logged in; if not, deny access
            return false;
        }

        if (!$this->decisionManager->decide($token, array('ROLE_UTENTE_PA', 'ROLE_UTENTE'))) {
            return false;
        }


        if($subject instanceof Richiesta) {
            if (
                ($subject->getProcedura()->getStatoProcedura()->getCodice() != "CONCLUSO" && !$subject->getProcedura()->getVisibileInCorso()) ||
                // Se ho già un programma creato
                (count($subject->getProgrammi()) > 0) ||
                (count($subject->getProponenti()) > 0)
            ) {
                return false;
            }
        }

        switch ($attribute) {
            case self::READ:
                return $this->canRead($subject, $subject->getProcedura(), $user);
            case self::WRITE:
                return $this->canWrite($subject, $subject->getProcedura(), $user);
            case self::PRESENT:
                return $this->canPresent($subject, $user);
            case self::VALIDATE:
                return $this->canValidate($subject, $user);
            case self::SEND:
                return $this->canSend($subject, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    /**
     * @param Richiesta $richiesta
     * @param Procedura $procedura
     * @param Utente $user
     * @return bool|object|\SfingeBundle\Entity\PermessiAsse|\SfingeBundle\Entity\PermessiProcedura|null
     */
    private function canRead(Richiesta $richiesta, Procedura $procedura, Utente $user)
    {
        if (in_array('ROLE_UTENTE_PA', $user->getRoles())) {
            $permesso = $this->findPermesso($procedura, $user);
            return !is_null($permesso);
        }
        if (in_array('ROLE_UTENTE', $user->getRoles())) {
            $permesso = $this->findPermessoRichiesta($richiesta, $user);
            return $permesso;
        }
    }

    /**
     * @param Richiesta $richiesta
     * @param Procedura $procedura
     * @param Utente $user
     * @return bool|object|\SfingeBundle\Entity\PermessiAsse|\SfingeBundle\Entity\PermessiProcedura|null
     */
    private function canWrite(Richiesta $richiesta, Procedura $procedura, Utente $user)
    {
        if (in_array('ROLE_UTENTE_PA', $user->getRoles())) {
            $permesso = $this->findPermesso($procedura, $user);
            return !is_null($permesso);
        }
        if (in_array('ROLE_UTENTE', $user->getRoles())) {
            $permesso = $this->findPermessoRichiesta($richiesta, $user);
            return $permesso;
        }
    }

    /**
     * @param array  $arrayValori
     * @param Utente $utente
     *
     * @return bool
     */
    private function canPresent(array $arrayValori, Utente $utente)
    {
        /** @var Richiesta $richiesta */
        foreach ($arrayValori['richieste'] as $richiesta) {
            if($richiesta->getStato()->getId() > 1) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array  $arrayValori
     * @param Utente $utente
     *
     * @return bool
     */
    private function canValidate(array $arrayValori, Utente $utente)
    {
        return true;
    }

    /**
     * @param array  $arrayValori
     * @param Utente $utente
     *
     * @return bool
     */
    private function canSend(array $arrayValori, Utente $utente)
    {
        return false;
    }

    /**
     * @param Procedura $procedura
     * @param Utente $user
     * @return object|\SfingeBundle\Entity\PermessiAsse|\SfingeBundle\Entity\PermessiProcedura|null
     */
    private function findPermesso(Procedura $procedura, Utente $user)
    {
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

    /**
     * @param $richiesta
     * @param Utente $user
     * @return bool
     */
    private function findPermessoRichiesta($richiesta, Utente $user)
    {
        $incarichiRichieste = $this->em->getRepository('SoggettoBundle:IncaricoPersonaRichiesta')->getRichiesteIncaricato($richiesta, $user->getPersona());
        if (in_array($richiesta->getId(), $incarichiRichieste)) {
            return true;
        }
        return false;
    }
}