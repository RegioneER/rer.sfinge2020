<?php

namespace SegnalazioniBundle\Controller;

use BaseBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SegnalazioniBundle\Form\Entity\Mantis;
use SegnalazioniBundle\Form\MantisType;
use SegnalazioniBundle\MantisConnect\MantisConnect;
use SegnalazioniBundle\MantisConnect\ObjectRef;
use PaginaBundle\Annotations\PaginaInfo;
use Symfony\Component\HttpFoundation\Request;

class SegnalazioniController extends BaseController {

    //variabili di config
    private $mantis_admin_username = '';
    private $mantis_admin_password = '';
    private $mantis_url = '';
    private $mantis_progetto = 'ER - Sfinge';
    private $mantis_categoria = 'Anomalie';
    private $access_name = 'aggiornatore';
    private $provenienza = "Front office";
    //variabili private
    private $mantis_username = '';
    private $mantis_password = '';

    /**
     * @Route("/inserisci_segnalazione", name="inserisci_segnalazione")
     * @Template("SegnalazioniBundle:Segnalazioni:segnalazione.html.twig")
     * @PaginaInfo(titolo="Segnalazione", sottoTitolo="pagina per la creazione di una segnalazione")
     */
    public function inserisciSegnalazione(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $utente = $this->getUser();
        $ruoliUtente = $utente->getRuoli();
        
        $this->mantis_admin_username = $this->getParameter("mantis_admin_username");
        $this->mantis_admin_password = $this->getParameter("mantis_admin_password");
        $this->mantis_url = $this->getParameter("mantis_url");


        $ripeti_password = $utente->getMantisUserId() ? false : true;

        $segnalazione = new Mantis();
        $opzioni["url_indietro"] = $this->generateUrl("home");
        $opzioni["obbligatorio"] = in_array("UTENTE", $ruoliUtente) ? true : false;
        $opzioni["ripeti_password"] = $ripeti_password;
        $segnalazione->setObbligatorio($opzioni["obbligatorio"]);

        $form = $this->createForm('SegnalazioniBundle\Form\MantisType', $segnalazione, $opzioni);

        if ($request->isMethod('POST')) {

            $form->handleRequest($request);

            if ($form->isValid()) {
                if ($ripeti_password && $segnalazione->getPassword() != $segnalazione->getRipetiPassword()) {
                    $this->addFlash('error', "Le password non coincidono");
                    return $this->redirectToRoute('inserisci_segnalazione');
                }
                if (!is_null($segnalazione->getFile()) && $segnalazione->getFile()->getSize() > 5242880) {
                    $this->addFlash('error', "Errore nella compilazione della segnalazione, inserire un file di dimensioni inferiori a 5MB");
                    return $this->redirectToRoute('inserisci_segnalazione');
                }

                $arrContextOptions = [
                    'ssl' => array('verify_peer' => false, 'verify_peer_name' => false)
                ];
                $options = ['stream_context' => stream_context_create($arrContextOptions)];

                //$mantis = new MantisConnect($this->mantis_url . "/api/soap/mantisconnect.php?wsdl");
                $mantis = new MantisConnect(dirname(__FILE__) . '/../Resources/wsdl/mantis.wsdl', $options);

                $persona = $this->getPersona();
                $nome = !is_null($persona) ? $persona->getNome() : " - ";
                $cognome = !is_null($persona) ? $persona->getCognome() : " - ";
                $utenteLoggato = "Utente che apre segnalazione: " . $this->getUserUsername() . ", Nome: " . $nome . ", Cognome: " . $cognome;
                $mailUtenteLoggato = "Mail Utente che apre segnalazione: " . $this->getUser()->getEmail();

                try {
                    $utenteDB = $em->getRepository('SfingeBundle\Entity\Utente')->getUtenteByUsername($segnalazione->getUsername());
                } catch (\Exception $ex) {
                    $utenteDB = null;
                }


                $personaDB = !is_null($utenteDB) ? $utenteDB->getPersona() : null;
                $nomeDB = !is_null($personaDB) ? $personaDB->getNome() : " - ";
                $cognomeDB = !is_null($personaDB) ? $personaDB->getCognome() : " - ";
                $mailDB = !is_null($utenteDB) ? $utenteDB->getEmail() : " - ";
                $utenteForm = "Utente con problematica: " . $segnalazione->getUsername() . ", Nome: " . $nomeDB . ", Cognome: " . $cognomeDB;
                $mailUtenteForm = "Mail Utente con problematica: " . $mailDB;

                $description = $utenteLoggato . PHP_EOL . $mailUtenteLoggato . PHP_EOL . $utenteForm . PHP_EOL . $mailUtenteForm . PHP_EOL . $segnalazione->getNumeroBando() . PHP_EOL . $segnalazione->getProtocolloProgetto() . PHP_EOL .
                        $segnalazione->getContattoTelefonico() . PHP_EOL . $segnalazione->getDescrizione();

                $viewState = new ObjectRef();
                $viewState->id = 50;
                $viewState->name = "privato";

                try {
                    $project_id = $mantis->mc_project_get_id_from_name($this->mantis_admin_username, $this->mantis_admin_password, $this->mantis_progetto);
                    $projectRef = new ObjectRef();
                    $projectRef->id = $project_id;
                    $projectRef->name = $this->mantis_progetto;
                } catch (\Exception $ex) {
                    $this->get('monolog.logger.schema31')->error($ex->getMessage());
                    $this->addFlash('error', $ex->getMessage() . " :Errore nella compilazione della segnalazione, progetto inesistente.");
                    return $this->redirectToRoute('inserisci_segnalazione');
                }

                $processoRef = new ObjectRef();
                $processoRef->id = 13;
                $processoRef->name = "SFINGE - Processo";
                $processoIssueData = new \SegnalazioniBundle\MantisConnect\CustomFieldValueForIssueData();
                $processoIssueData->field = $processoRef;

                $provenienzaRef = new ObjectRef();
                $provenienzaRef->id = 23;
                $provenienzaRef->name = "SFINGE - Provenienza";
                $provenienzaIssueData = new \SegnalazioniBundle\MantisConnect\CustomFieldValueForIssueData();
                $provenienzaIssueData->field = $provenienzaRef;

                $priorityRef = new ObjectRef();
                $priorityRef->id = 30;
                $priorityRef->name = "normale";

                $severityRef = new ObjectRef();
                $severityRef->id = 50;
                $severityRef->name = "minore";

                $statusRef = new ObjectRef();
                $statusRef->id = 10;
                $statusRef->name = "new";

                $issueData = new \SegnalazioniBundle\MantisConnect\IssueData();
                $issueData->view_state = $viewState;
                $issueData->project = $projectRef;
                $issueData->category = $this->mantis_categoria;
                $issueData->priority = $priorityRef;
                $issueData->severity = $severityRef;
                $issueData->status = $statusRef;
                $issueData->summary = "Sfinge2020: " . $segnalazione->getOggetto();
                $issueData->description = "Sfinge2020: " . $description;
                $processoIssueData->value = $segnalazione->getProcesso();
                $provenienzaIssueData->value = $this->provenienza;
                $issueData->custom_fields = array($provenienzaIssueData);

                if (!is_null($processoIssueData)) {
                    $issueData->custom_fields[] = $processoIssueData;
                }

                $issueData->date_submitted = new \DateTime('now');

                try {
                    $this->mantis_username = $this->getUserUsername();
                    $this->mantis_password = $segnalazione->getPassword();

                    if (!$utente->hasMantisUserId()) {
                        $access_levels = $mantis->mc_enum_access_levels($this->mantis_admin_username, $this->mantis_admin_password);
                        //mantis default access level, 10 = viewer
                        $access = 10;
                        foreach ($access_levels as $level) {
                            if ($level->name == $this->access_name) {
                                $access = $level->id;
                            }
                        }

                        $arrContextOptions2 = [
                            'ssl' => array('verify_peer' => false, 'verify_peer_name' => false)
                        ];
                        $options2 = ['stream_context' => stream_context_create($arrContextOptions2)];
                        //$c = new \SoapClient($this->mantis_url . "/api/soap/mantisconnect.php?wsdl");
                        $c = new \SoapClient(dirname(__FILE__) . '/../Resources/wsdl/mantis.wsdl', $options2);
                        $p_user = new \SegnalazioniBundle\MantisConnect\AccountData();
                        $p_user->id = -1; // integer - non usato per l'add dell' account
                        $p_user->name = $this->getUserUsername(); // string
                        $p_user->real_name = strtoupper($nome . ' ' . $cognome); // string
                        $p_user->email = $this->getUser()->getEmail(); // string
                        $p_user->access = $access;
                        $p_pass = $segnalazione->getPassword();
                        $mantis_user_id = $c->mc_account_add($this->mantis_admin_username, $this->mantis_admin_password, $p_user, $p_pass);

                        $c->mc_project_set_user_access($this->mantis_admin_username, $this->mantis_admin_password, $project_id, $mantis_user_id, $access);

                        $utente->setMantisUserId($mantis_user_id);
                        $em->persist($utente);
                        $em->flush();
                    }
                } catch (\Exception $ex) {
                    $this->get('monolog.logger.schema31')->error($ex->getMessage());
                    $str_err_user = "Quel nome utente è già utilizzato.  Torna indietro e scegline uno diverso";
                    $message = $ex->getMessage();
                    if (strpos($message, $str_err_user) !== false) {
                        $this->addFlash('error', "Errore nella creazione della segnalazione.<br/>Una possibile soluzione è quella di loggarsi al sistema mantis e aggiornare la password se è stato necessario fare un cambio password su SFINGE");
                    } else {
                        $this->addFlash('error', "Errore nella creazione della segnalazione");
                    }
                    return $this->redirectToRoute('inserisci_segnalazione');
                }

                try {
                    $new_id = $mantis->mc_issue_add($this->mantis_username, $this->mantis_password, $issueData);
                    if (!is_null($segnalazione->getFile()) && $segnalazione->getFile()->getSize() > 0) {
                        $fileType = $segnalazione->getFile()->getMimeType();
                        $path = $segnalazione->getFile()->getRealPath();
                        $contentFile = file_get_contents($path);
                        $mantis->mc_issue_attachment_add($this->mantis_username, $this->mantis_password, $new_id, $segnalazione->getFile()->getClientOriginalName(), $fileType, $contentFile);
                    }
                    $this->addFlash('success', "Segnalazione creata correttamente");
                    return $this->redirectToRoute('home');
                } catch (\Exception $ex) {
                    $this->get('monolog.logger.schema31')->error($ex->getMessage());
                    $str_err_user = "That username is already being used. Please go back and select another one.";
                    $str_access_denied_user = 'Access denied for user';
                    $str_access_denied = "Access denied";
                    $message = $ex->getMessage();
                    if (strpos($message, $str_err_user) !== false) {
                        $this->addFlash('error', "Errore nella creazione della segnalazione-(Username già utilizzato).<br/>Una possibile soluzione è quella di loggarsi al sistema mantis e aggiornare la password se è stato necessario fare un cambio password su SFINGE");
                    }
                    if (strpos($message, $str_access_denied) !== false || strpos($message, $str_access_denied_user) !== false) {
                        $this->addFlash('error', "Errore nella creazione della segnalazione.<br/>Una possibile soluzione è quella di loggarsi al sistema mantis e aggiornare la password se è stato necessario fare un cambio password su SFINGE");
                    } else {
                        $this->addFlash('error', "Errore nella creazione della segnalazione");
                    }
                    return $this->redirectToRoute('inserisci_segnalazione');
                }
            }
        }
        $form_params["form"] = $form->createView();
        $form_params["ripeti_password"] = $ripeti_password;
        return $form_params;
    }

}
