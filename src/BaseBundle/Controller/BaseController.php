<?php

namespace BaseBundle\Controller;

use AnagraficheBundle\Entity\Persona;
use BaseBundle\Entity\EntityTipo;
use BaseBundle\Exception\SfingeException;
use Doctrine\ORM\EntityManager;
use InvalidArgumentException;
use LogicException;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Service\IGestoreRichiesta;
use SfingeBundle\Entity\Utente;
use SoggettoBundle\Entity\IncaricoPersona;
use SoggettoBundle\Entity\Soggetto;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class BaseController extends Controller {
    const msg_warning = "warning";
    const msg_errore = "error";
    const msg_ok = "success";
    const msg_info = "info";

    //lista dei nomi degli oggetti in sessione
    const SESSIONE_SOGGETTO = "_soggetto";
    const SESSIONE_SOGGETTO_ISTRUENDO = "_soggetto_istruendo";
    const SESSIONE_INCARICO = "_incarico";
    const SESSIONE_INDIETRO_URL = "_indietro_url";

    public function getSession(): SessionInterface {
        $session = $this->getCurrentRequest()->getSession();
        return $session;
    }

    public function getCurrentRequest(): Request {
        $request = $this->getRequest();
        return $request;
    }

    public function getEm(): EntityManager {
        return $this->getDoctrine()->getManager();
    }

    public function getUserUsername(): ?string {
        $user = $this->getUser();
        if (!\is_null($user)) {
            return $this->getUser()->getUsername();
        }
        return null;
    }

    public function getUserUserid(): ?int {
        $user = $this->getUser();
        if (!\is_null($user)) {
            return $this->getUser()->getId();
        }
        return null;
    }

    public function getPersonaId(): ?int {
        $user = $this->getUser();
        if (!\is_null($user)) {
            if (!\is_null($user->getPersona())) {
                return $user->getPersona()->getId();
            }
        }
        return null;
    }

    public function getPersona(): ?Persona {
        $user = $this->getUser();
        if (!\is_null($user)) {
            if (!\is_null($user->getPersona())) {
                return $user->getPersona();
            }
        }
        return null;
    }

    public function trovaDaCostante($entityTipo, $codice) {
        $em = $this->getEm();

        if ($entityTipo instanceof EntityTipo) {
            $function = new \ReflectionClass($entityTipo);

            $entityName = $function->getNamespaceName() . "\\" . $function->getShortName();

            $repository = $em->getRepository($entityName);
        } else {
            $repository = $em->getRepository($entityTipo);
        }
        return $repository->findOneByCodice($codice);
    }

    //TODO da testare
    protected function inviaEmail($email, $tipo, $subject, $renderViewTwig, $parametriView, $noHtmlViewTwig = null, $indirizzoAggiuntivo = null) {
        return $this->get("messaggi.email")->inviaEmail($email, $tipo, $subject, $renderViewTwig, $parametriView, $noHtmlViewTwig, $indirizzoAggiuntivo);
    }

    protected function addError($messaggio, $redirectUrl = null): ?Response {
        $this->addFlash("error", $messaggio);
        if (!is_null($redirectUrl)) {
            return $this->redirect($redirectUrl);
        }
        return null;
    }

    protected function addErrorRedirect($messaggio, $rotta, $parametri = []): Response {
        $this->addFlash("error", $messaggio);
        return $this->redirectToRoute($rotta, $parametri);
    }

    protected function addWarning($messaggio, $redirectUrl = null): ?Response {
        $this->addFlash("warning", $messaggio);
        if (!is_null($redirectUrl)) {
            return $this->redirect($redirectUrl);
        }
        return null;
    }

    protected function addWarningRedirect($messaggio, $rotta, $parametri = []): Response {
        $this->addFlash("warning", $messaggio);
        return $this->redirectToRoute($rotta, $parametri);
    }

    protected function addSuccess($messaggio, $redirectUrl = null): ?Response {
        $this->addFlash("success", $messaggio);
        if (!is_null($redirectUrl)) {
            return $this->redirect($redirectUrl);
        }
        return null;
    }

    protected function addSuccessRedirect($messaggio, $rotta, $parametri = []): Response {
        $this->addFlash("success", $messaggio);
        return $this->redirectToRoute($rotta, $parametri);
    }

    protected function isManagerRegionale(): bool {
        $ruoli = $this->getUser()->getRoles();
        if (in_array('ROLE_MANAGER_PA', $ruoli)) {
            return true;
        }
        return false;
    }

    protected function puoVedereTutto(): bool {
        return $this->isAdmin();
    }

    public function isAdmin(): bool {
        $ruoli = $this->getUser()->getRoles();
        return in_array('ROLE_SUPER_ADMIN', $ruoli) || in_array('ROLE_ADMIN_PA', $ruoli);
    }

    public function isSuperAdmin(): bool {
        $ruoli = $this->getUser()->getRoles();
        return in_array('ROLE_SUPER_ADMIN', $ruoli);
    }

    protected function puoVedereTuttiGliIncarichi(): bool {
        $ruoli = $this->getUser()->getRoles();
        return $this->isAdmin() || in_array('ROLE_MANAGER_PA', $ruoli) || in_array('ROLE_UTENTE_PA', $ruoli);
    }

    protected function isRegionale(): bool {
        $ruoli = $this->getUser()->getRoles();
        if (in_array('ROLE_UTENTE_PA', $ruoli) ||
            in_array('ROLE_MANAGER_PA', $ruoli) || $this->isAdmin()) {
            return true;
        }
        return false;
    }

    public function isUtente(): bool {
        $ruoli = $this->getUser()->getRoles();
        return in_array('ROLE_UTENTE', $ruoli);
    }

    protected function checkCsrf($name, $query = '_token') {
        return $this->get("base")->checkCsrf($name, $query);
    }

    /**
     * @return IncaricoPersona[]
     */
    protected function getIncaricoDaSoggetto(Soggetto $soggetto): array {
        $incarichiArray = [];
        $incarichi = $soggetto->getIncarichiPersone();
        foreach ($incarichi as $incarico) {
            if (true == $incarico->isAttivo()) {
                $incarichiArray[] = $incarico;
            }
        }
        return $incarichiArray;
    }

    /**
     * @return IncaricoPersona[]
     */
    protected function getIncaricoPersona(Utente $utente): array {
        $incarichiArray = [];
        $persona = $utente->getPersona();
        $incarichi = $persona->getIncarichiPersone();
        foreach ($incarichi as $incarico) {
            if (true == $incarico->isAttivo()) {
                $incarichiArray[] = $incarico;
            }
        }
        return $incarichiArray;
    }

    /**
     * @return IncaricoPersona[]
     */
    protected function getIncarichiSuSoggetto(Soggetto $soggetto, Utente $utente): array {
        $intersezione = array_intersect($this->getIncaricoDaSoggetto($soggetto), $this->getIncaricoPersona($utente));
        return $intersezione;
    }

    /**
     * @param Richiesta[] $richieste
     * @return Richiesta[]
     * @throws LogicException
     * @throws InvalidArgumentException
     */
    protected function valutaVisibilitaRichiesta(array $richieste, Soggetto $soggetto, Utente $utente): array {
        $incarichiAttivi = $this->getIncarichiSuSoggetto($soggetto, $utente);
        $incarichiRichieste = [];
        foreach ($richieste as $richiestaIn) {
            $incarichiRichiesteArray = $this->getEm()->getRepository('SoggettoBundle:IncaricoPersonaRichiesta')->getRichiesteIncaricato($richiestaIn, $utente->getPersona());
            foreach ($incarichiRichiesteArray as $inc) {
                $incarichiRichieste[] = $inc;
            }
        }
        $richiesteOut = [];
        foreach ($incarichiAttivi as $incarico) {
            if (in_array($incarico->getTipoIncarico()->getCodice(), ['UTENTE_PRINCIPALE', 'OPERATORE', 'CONSULENTE', 'LR', 'DELEGATO'])) {
                $richiesteOut = $richieste;
                break;
            } elseif (in_array($incarico->getTipoIncarico()->getCodice(), ['OPERATORE_RICHIESTA'])) {
                foreach ($richieste as $richiesta) {
                    if (in_array($richiesta->getId(), $incarichiRichieste)) {
                        $richiesteOut[] = $richiesta;
                    }
                }
            }
        }
        return $richiesteOut;
    }

    /**
     * @throws SfingeException
     */
    protected function getRichiesta(string $id_richiesta): Richiesta {
        $richiesta = $this->getEm()->getRepository(Richiesta::class)->find($id_richiesta);
        if (\is_null($richiesta)) {
            throw new SfingeException('Richiesta non presente a sistema');
        }

        return $richiesta;
    }

    protected function getGestoreRichiesta(Richiesta $richiesta = null): IGestoreRichiesta {
        $procedura = \is_null($richiesta) ? null : $richiesta->getProcedura();

        return $this->get("gestore_richieste")->getGestore($procedura);
    }

    protected function logError(string $message, array $param = []): void {
        $this->log('error', $message, $param);
    }

    protected function log(string $type, string $message, array $param = []): void {
        $this->get('logger')->$type($message, $param);
    }

    protected function isSuperAdminIrap(): bool {
        $arrayUtentiAbilitati = ['RCCRRT70L15A191Z', 'BNONDR81B07A952Z'];
        $username = $this->getUser()->getUsername();
        return in_array($username, $arrayUtentiAbilitati);
    }

    /**
     * @throws \Exception 
     * @throws \LogicException 
     * @throws \InvalidArgumentException 
     */
    protected function getSoggetto(): Soggetto {
        $soggettoSession = $this->getSession()->get(self::SESSIONE_SOGGETTO);
        /** @var SoggettoRepository */
        $soggettoRepo = $this->getEm()->getRepository(Soggetto::class);
        $soggetto = $soggettoRepo->find($soggettoSession);
        if(\is_null($soggetto)){
            throw new \Exception("Soggetto non trovato", 1);
        }

        return $soggetto;
    }
}
