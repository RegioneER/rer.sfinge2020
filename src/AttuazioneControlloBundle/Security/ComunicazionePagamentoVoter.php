<?php

namespace AttuazioneControlloBundle\Security;

use AttuazioneControlloBundle\Entity\Istruttoria\ComunicazionePagamento;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use RichiesteBundle\Entity\Richiesta;
use SfingeBundle\Entity\PermessiAsse;
use SfingeBundle\Entity\Utente;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Doctrine\Bundle\DoctrineBundle\Registry;

class ComunicazionePagamentoVoter extends Voter {

    const READ = 'read';
    const WRITE = 'write';

    /**
     * @var AccessDecisionManagerInterface An AccessDecisionManager instance
     */
    private $decisionManager;
    
    /** @var ManagerRegistry|null */
    private $doctrine;
    
    /**
     * @var EntityManagerInterface|null
     */
    private $em;

    /**
     * ComunicazionePagamentoVoter constructor.
     * @param AccessDecisionManagerInterface $decisionManager An AccessDecisionManager instance
     * @param Registry $doctrine
     */
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

        // only vote on ComunicazionePagamento objects inside this voter
        if (!$subject instanceof ComunicazionePagamento) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        $user = $token->getUser();

        if (!$user instanceof Utente) {
            // The user must be logged in; if not, deny access
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

    private function canRead(ComunicazionePagamento $comunicazionePagamento, Utente $user) {
        if (in_array('ROLE_UTENTE_PA', $user->getRoles())) {
            $permesso = $this->findPermesso($comunicazionePagamento->getRichiesta()->getProcedura(), $user);
            return !is_null($permesso);
        }
        
        if (in_array('ROLE_UTENTE', $user->getRoles())) {
            $permesso = $this->findPermessoRichiesta($comunicazionePagamento->getRichiesta(), $user);
            return $permesso;
        }
        
        return false;
    }

    private function canWrite(ComunicazionePagamento $comunicazionePagamento, Utente $user) {
        if (in_array('ROLE_UTENTE_PA', $user->getRoles())) {
            $permesso = $this->findPermesso($comunicazionePagamento->getRichiesta()->getProcedura(), $user);
            return !is_null($permesso);
        }
        
        if (in_array('ROLE_UTENTE', $user->getRoles())) {
            $permesso = $this->findPermessoRichiesta($comunicazionePagamento->getRichiesta(), $user);
            return $permesso;
        }

        return false;
    }

    private function findPermesso(Procedura $procedura, Utente $user) {
        $permessoProcedura = $this->em->getRepository('SfingeBundle:PermessiProcedura')->findOneBy(array("utente" => $user, "procedura" => $procedura));

        if ($permessoProcedura) {
            return $permessoProcedura;
        }

        /** @var PermessiAsse $permessoAsse */
        $permessoAsse = $this->em->getRepository('SfingeBundle:PermessiAsse')->findOneBy(array("utente" => $user, "asse" => $procedura->getAsse()));

        if ($permessoAsse) {
            return $permessoAsse;
        }

        return null;
    }

    /**
     * @param Richiesta $richiesta
     * @param Utente $user
     * @return bool
     */
    private function findPermessoRichiesta(Richiesta $richiesta, Utente $user) {
        $abilitatoSoggetto = false;
        $abilitatoRichiesta = false;

        /** @var array $soggettiGestiti */
        $soggettiGestiti = $this->em->getRepository('SoggettoBundle:Soggetto')->cercaTuttiDaPersonaIncaricoNoRichiesta($user->getPersona()->getId());
        if (in_array($richiesta->getSoggetto()->getId(), $soggettiGestiti)) {
            $abilitatoSoggetto = true;
        }

        /** @var array $incarichiRichieste */
        $incarichiRichieste = $this->em->getRepository('SoggettoBundle:IncaricoPersonaRichiesta')->getRichiesteIncaricato($richiesta, $user->getPersona());
        if (in_array($richiesta->getId(), $incarichiRichieste)) {
            $abilitatoRichiesta =  true;
        }
        
        return $abilitatoSoggetto || $abilitatoRichiesta;
    }
}
