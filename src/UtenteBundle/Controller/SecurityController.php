<?php

namespace UtenteBundle\Controller;

use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SecurityController extends Controller {
    /**
     * @var CsrfTokenManagerInterface
     */
    private $tokenManager;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;        
        $this->tokenManager = $container->get('security.csrf.token_manager');
    }

    public function loginAction(Request $request) {
        if ($this->getParameter('login.federa.abilitato')) {
            return $this->redirectToRoute("federa_user_registration_register");
        }

        /** @var $session Session */
        $session = $request->getSession();

        $authErrorKey = Security::AUTHENTICATION_ERROR;
        $lastUsernameKey = Security::LAST_USERNAME;

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has($authErrorKey)) {
            $error = $request->attributes->get($authErrorKey);
        } elseif (null !== $session && $session->has($authErrorKey)) {
            $error = $session->get($authErrorKey);
            $session->remove($authErrorKey);
        } else {
            $error = null;
        }

        if (!$error instanceof AuthenticationException) {
            $error = null; // The value does not come from the security component.
        }

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get($lastUsernameKey);

        $csrfToken = $this->tokenManager
            ? $this->tokenManager->getToken('authenticate')->getValue()
                : null;

        return $this->renderLogin(array(
            'last_username' => $lastUsername,
            'error' => $error,
            'csrf_token' => $csrfToken,
        ));
    }

    public function checkAction() {
        throw new RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }

    public function logoutAction() {

        $utente = $this->getUser();

        if ($utente->haDoppioRuoloInvFesr()) {
            $ruoli = ['ROLE_USER', 'ROLE_UTENTE_PA', 'ROLE_ISTRUTTORE_ATC', 'ROLE_ISTRUTTORE'];
            try {
                $em = $this->getDoctrine()->getManager();
                $utente->setRoles($ruoli);
                $em->persist($utente);
                $em->flush();
            } catch (\Exception $e) {
                $this->get('logger')->error($e->getMessage());
                $this->addFlash('error', "Errore nel salvaltaggio delle informazioni");
            }
        }

        $this->get('session')->clear();
        $this->get('security.token_storage')->setToken(null);
        session_unset();
        session_destroy();

        if ($this->getParameter("login.federa.abilitato")) {
            $host = $_SERVER["HTTP_X_FORWARDED_HOST"];
            if ($host == 'applicazionitest.regione.emilia-romagna.it' || $host == 'applicazioni.regione.emilia-romagna.it') {
                $url_logout = 'https://' . $host . '/index.php';
            } else {
                $url_logout = 'https://applicazioni.regione.emilia-romagna.it/amserver/UI/Logout?goto=https%3A%2F%2Ffedera.lepida.it%2Flogout%2F%3Fspid%3Dhttps%253A%252F%252Fservizifederati.regione.emilia-romagna.it%252Ffesr2020%252F%26spurl%3Dhttps%253A%252F%252Fwww.regione.emilia-romagna.it';
            }
            //$url_logout = $this->getParameter("logout.federa.url") ."?spid=".$spid.'&spurl='.urlencode($spurl);
            //proviamo con url diretto già precostruito come suggerito da federa
            $response = new RedirectResponse($url_logout);
            return $response;
        } else {
            return $this->redirectToRoute("fos_user_security_login");
        }
    }

    /**
     * Renders the login template with the given parameters. Overwrite this function in
     * an extended controller to provide additional data for the login template.
     *
     * @param array $data
     *
     * @return Response
     */
    protected function renderLogin(array $data) {
        return $this->render('FOSUserBundle:Security:login.html.twig', $data);
    }
}
