<?php

namespace AnagraficheBundle\Service;

use AnagraficheBundle\Entity\Persona;
use AnagraficheBundle\Form\Entity\PersonaPA;
use AnagraficheBundle\Form\PersonaPAType;
use AnagraficheBundle\Form\PersonaType;
use BaseBundle\Service\BaseService;
use DateTime;
use GeoBundle\Entity\GeoComune;
use SfingeBundle\Entity\Utente;
use Symfony\Component\DependencyInjection\ContainerInterface;
use function is_null;

/**
 * Created by PhpStorm.
 * User: scipriani
 * Date: 29/01/16
 * Time: 09:28
 */
class InserimentoPersona extends BaseService
{
    private $em;

    /**
     * InserimentoPersona constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->em = $this->container->get('doctrine')->getManager();
    }

    /*
     * Se tra i parametriOpzionali è presente
     *
     *      parametriOpzionali["utente"] = Utente()
     *
     * sto inserendo la persona per la registrazione dell'utente.
     */

    public function inserisciPersona($urlIndietro, $urlRedirect, $parametriRedirect = array(), $parametriOpzionali = array()) {
        $em = $this->em;
        $funzioniService = $this->container->get('funzioni_utili');
        $utente = $parametriOpzionali["utente"] ?? NULL;
        if (is_null($utente) || is_null($utente->getPersona())) {
            $persona = new Persona();
        } else {
            $persona = $utente->getPersona();
        }
        $is_utente_PA = $parametriOpzionali['is_utente_PA'] ?? false;
        $request = $this->getCurrentRequest();

        $data = $is_utente_PA ? null : $funzioniService->getDataComuniFromRequest($request, $persona->getLuogoResidenza());
        $dataPersona = $is_utente_PA ? null : $funzioniService->getDataComuniPersonaFromRequest($request, $persona);

        $options["disabilitaEmail"] = false;
        $options["mostra_indietro"] = true;
        if (!is_null($utente) && !is_null($utente->getEmail()) && ($utente instanceof Utente)) {
            $persona->setEmailPrincipale($utente->getEmail());
            //$options["disabilitaEmail"] = true;
            $options["disabilitaEmail"] = false;
            $options["mostra_indietro"] = false;
        } 
        
        $options["disabilitaCf"] = false;
        if (!is_null($utente) && ($utente instanceof Utente)) {
            $options["disabilitaCf"] = true;
            $persona->setCodiceFiscale($utente->getUsername());

            $persona->setDataNascita($this->dammiDataNascita($persona->getCodiceFiscale()));

            $persona->setSesso(substr($persona->getCodiceFiscale(), 9, 2) > 40 ? 'F' : 'M');

            $datiLuogoNascita = $this->dammiNazioneNascita($persona->getCodiceFiscale());

            if($datiLuogoNascita[0] instanceof GeoComune) {
                $persona->setComune($datiLuogoNascita[0]);
                $dataPersona->comune = $datiLuogoNascita[0];

                $persona->setProvincia($datiLuogoNascita[1]);
                $dataPersona->provincia = $datiLuogoNascita[1];
            }

            $persona->setNazionalita($datiLuogoNascita[3]);
            $persona->setStatoNascita($datiLuogoNascita[3]);
            $dataPersona->stato = $datiLuogoNascita[3];
        }

        $options["readonly"] = false;
        $options["dataIndirizzo"] = $data;
        $options["dataPersona"] = $dataPersona;
        $options["url_indietro"] = $urlIndietro;

        $form = null;
        if ($is_utente_PA) {
            $personaPA = new PersonaPA();
            $personaPA->setEmailPrincipale(!is_null($utente) ? $utente->getEmail() : null);
            $form = $this->createForm(PersonaPAType::class, $personaPA, $options);
        } else {
            $form = $this->createForm(PersonaType::class, $persona, $options);
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $form_params["form"] = $form->createView();
            $form_params["persona"] = $persona;

            if (!$is_utente_PA) {
                $errore = $this->verificaCfMail($persona, $utente);
                if ($errore) {
                    $this->addFlash('warning', $errore);
                    return $this->render("AnagraficheBundle:Persona:datiPersona.html.twig", $form_params);
                }
            } else {
                $persona->setNome($personaPA->getNome());
                $persona->setCognome($personaPA->getCognome());
                $persona->setEmailPrincipale($personaPA->getEmailPrincipale());
                $persona->setTelefonoPrincipale($personaPA->getTelefonoPrincipale());
            }

            if ($form->isValid()) {

                try {
                    $em->beginTransaction();

                    if (!is_null($utente)) {
                        $utente->setPersona($persona);
                        $utente->setDatiPersonaInseriti(true);

                        $utente->setEmail($persona->getEmailPrincipale());
                        $utente->setEmailCanonical($persona->getEmailPrincipale());

                        $persona->setEmailPrincipale($utente->getEmail());
                        $em->persist($utente);
                    }

                    $em->persist($persona);
                    $em->flush();
                    $em->commit();

                    $this->addFlash('success', "Modifiche salvate correttamente");

                    $parametriRedirect['persona_id'] = $persona->getId();
                    $parametriRedirect['persona_inserita'] = true;
                    return $this->redirect($this->generateUrl($urlRedirect, $parametriRedirect));
                } catch (\Exception $e) {
                    $em->rollback();
                    $this->addFlash('error', "Errore nell'inserimento della persona");
                }
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["persona"] = $persona;

        return $this->render($is_utente_PA ? "AnagraficheBundle:Persona:datiPersonaPA.html.twig" : "AnagraficheBundle:Persona:datiPersona.html.twig", $form_params);
    }

    private function verificaCfMail($persona, $utente){
        $em = $this->em;
        $persona_verifica_cf = $em->getRepository("AnagraficheBundle\Entity\Persona")->cercaPersoneByCf($persona->getCodiceFiscale());
        // Controllo se se esiste una persona con lo stesso cf
        $persona_id_from_persona = $persona->getId();
        $persona_id_from_utente = (is_null($utente)) ? null : (is_null($utente->getPersona()) ? null : $utente->getPersona()->getId());
        
        if(!is_null($persona_id_from_persona) && !is_null($persona_id_from_utente)){
            $ctrl_cf = $persona_id_from_persona != $persona_id_from_utente;
        } else {
            $ctrl_cf = true;
        }
        
        if ($persona_verifica_cf && $ctrl_cf) {
            return "Il codice fiscale inserito è già presente a sistema";
        }

        $persona_verifica_email = $em->getRepository("AnagraficheBundle\Entity\Persona")->cercaPersoneByEmailPrincipale($persona->getEmailPrincipale());
        // Controllo se se esiste una persona con lo stesso email
        if ($persona_verifica_email) {
            return "La email inserita è già presente a sistema";
        }
        // Controlli sulla mail degli utenti la faccio solo se sto inserendo
        if(is_null($utente)) {
            $utente_verifica_email = $em->getRepository("SfingeBundle\Entity\Utente")->cercaUtenteByEmail($persona->getEmailPrincipale());
            // Controllo se se esiste una utenza con lo stesso email
            if ($utente_verifica_email) {
                return "La email inserita è già presente a sistema";
            }
        }
        return false;
    }

    public function dammiNazioneNascita($codiceFiscale)
    {
        $belfiore = substr($codiceFiscale, 11, 4);

        $risultatoRicerca = $this->em->getRepository('GeoBundle:CodiceCatasto')->getGeoByCodiceCatasto($belfiore);

        if($risultatoRicerca instanceof GeoComune) {
            $comune = $risultatoRicerca;

            $provincia = $comune->getProvincia();

            $regione = $provincia->getRegione();

            $stato = $regione->getStato();

            return [$comune, $provincia, $regione, $stato];
        }

        return [null, null, null, $risultatoRicerca];


    }

    private function dammiDataNascita($codiceFiscale)
    {
        $_mesi = array(
            1  => 'A',
            2  => 'B',
            3  => 'C',
            4  => 'D',
            5  => 'E',
            6  => 'H',
            7  => 'L',
            8  => 'M',
            9  => 'P',
            10 => 'R',
            11 => 'S',
            12 => 'T'
        );

        $day = (int) substr($codiceFiscale, 9, 2);
        $gender = $day > 40 ? 'F' : 'M';

        if($gender === 'F') {
            $day -= 40;
        }

        $monthChar = substr($codiceFiscale, 8,1);

        $monthNumber = array_search($monthChar, $_mesi);

        $year = substr($codiceFiscale, 6, 2);

        $currentDate = new DateTime();
        $currentYear = $currentDate->format('y');

        $currentCentury = substr($currentDate->format('Y'), 0, 2);
        $century = $year < $currentYear ? $currentCentury : $currentCentury - 1;

        $dataNascita = new DateTime();

        $dataNascita->setDate($century.$year, $monthNumber, $day)->setTime(0,0,0);

        return $dataNascita;
    }

}