<?php
namespace SoggettoBundle\Controller;

use AnagraficheBundle\Entity\Persona;
use DateTime;
use Exception;
use SoggettoBundle\Entity\PersonaFisica;
use SoggettoBundle\Entity\Soggetto;
use SoggettoBundle\Form\Entity\NuovaPersonaFisica;
use SoggettoBundle\Form\NuovaPersonaFisicaType;
use SoggettoBundle\Form\PersonaFisicaType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SoggettoBundle\Entity\StatoIncarico;
use SoggettoBundle\Entity\TipoIncarico;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use Symfony\Component\Form\FormError;
use BaseBundle\Annotation\ControlloAccesso;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PersonaFisicaGestioneController
 */
class PersonaFisicaGestioneController extends SoggettoBaseController
{
    /**
     * @Route("/crea_persona_fisica", name="crea_persona_fisica")
     * @Template("SoggettoBundle:Soggetto:personaFisica.html.twig")
     * @Menuitem(menuAttivo = "creaSoggettoGiuridico")
     * @PaginaInfo(titolo="Nuova Persona fisica",sottoTitolo="")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Soggetti", route="elenco_soggetti"), @ElementoBreadcrumb(testo="Crea soggetto")})
     * @throws Exception
     */
	public function creaPersonaFisicaAction(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $formModel = new NuovaPersonaFisica();

        if ($request->query->has('codice_fiscale')) {
            $codiceFiscaleSoggetto = $request->query->get('codice_fiscale');
            $formModel->legaleRappresentante->setCodiceFiscale($codiceFiscaleSoggetto);

            // Collego la persona se già presente a sistema
            $personaDB = $em->getRepository(Persona::class)->findOneBy([
                'codice_fiscale' => $codiceFiscaleSoggetto,
            ]);

            if ($personaDB) {
                $formModel->legaleRappresentante = $personaDB;
            }
        }

        $options["url_indietro"] = $request->headers->get('referer');
        $options["tipo"] = $request->query->get('tipo');

        $form = $this->createForm(NuovaPersonaFisicaType::class, $formModel, $options);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            // Controlli post submit
            $formLegaleRappresentante = $form->get('legaleRappresentante');
            $piva_presente = $em->getRepository("SoggettoBundle:Soggetto")
                ->isCodicePresente('piva', $formModel->legaleRappresentante->getCodiceFiscale());

            if ($piva_presente) {
                $formLegaleRappresentante->get('partita_iva')
                    ->addError(new FormError('Partita IVA già presente a sistema'));
            }

            $cf_presente = $em->getRepository("SoggettoBundle:Soggetto")
                ->isCodicePresente('cf', $formModel->legaleRappresentante->getCodiceFiscale());

            if ($cf_presente) {
                $formLegaleRappresentante->get('codice_fiscale')
                    ->addError(new FormError('Codice fiscale già presente a sistema'));
            }

            if ($form->isValid()) {
                try {
                    $soggetto = new PersonaFisica();
                    $denominazione = $formModel->legaleRappresentante->getNome() . ' ' . $formModel->legaleRappresentante->getCognome();
                    $soggetto->setDenominazione($denominazione);
                    $soggetto->setCodiceFiscale(strtoupper($formModel->legaleRappresentante->getCodiceFiscale()));

                    $codiceOrganismo = $em->getRepository("SoggettoBundle:Soggetto")->getMaxCodiceOrganismo() + 1;
                    $soggetto->setCodiceOrganismo($codiceOrganismo);

                    $formaGiuridica = $em->getRepository("SoggettoBundle:FormaGiuridica")
                        ->findOneBy(['codice' => '9.9.99',]);
                    $soggetto->setFormaGiuridica($formaGiuridica);

                    $soggetto->setStato($formModel->legaleRappresentante->getLuogoResidenza()->getStato());
                    $soggetto->setComune($formModel->legaleRappresentante->getLuogoResidenza()->getComune());
                    $soggetto->setVia($formModel->legaleRappresentante->getLuogoResidenza()->getVia());
                    $soggetto->setCivico($formModel->legaleRappresentante->getLuogoResidenza()->getNumeroCivico());
                    $soggetto->setCap($formModel->legaleRappresentante->getLuogoResidenza()->getCap());
                    $soggetto->setLocalita($formModel->legaleRappresentante->getLuogoResidenza()->getLocalita());
                    $soggetto->setProvinciaEstera($formModel->legaleRappresentante->getLuogoResidenza()->getProvinciaEstera());
                    $soggetto->setComuneEstero($formModel->legaleRappresentante->getLuogoResidenza()->getComuneEstero());
                    $soggetto->setEmail($formModel->legaleRappresentante->getEmailPrincipale());
                    $soggetto->setTel($formModel->legaleRappresentante->getTelefonoPrincipale());
                    $soggetto->setDataRegistrazione(new DateTime());
                    $soggetto->setEmailPec($form->get('email_pec')->getData());

                    $legaleRappresentanteDaDB = $em->getRepository(Persona::class)->findOneBy([
                        'codice_fiscale' => $formModel->legaleRappresentante->getCodiceFiscale(),
                    ]);

                    if ($legaleRappresentanteDaDB) {
                        $legaleRappresentanteDaDB->mergeData($formModel->legaleRappresentante);
                        $formModel->legaleRappresentante = $legaleRappresentanteDaDB;
                    }

                    $tipoIncaricoLegaleRappresentante = $this->trovaDaCostante("SoggettoBundle:TipoIncarico", TipoIncarico::LR);
                    $statoAttivo = $this->trovaDaCostante("SoggettoBundle:StatoIncarico", StatoIncarico::ATTIVO);
                    $incaricoLegaleRappresentante = $soggetto
                        ->incarica($formModel->legaleRappresentante, $tipoIncaricoLegaleRappresentante, $statoAttivo);

                    $em->persist($soggetto);
                    $em->persist($formModel->legaleRappresentante);
                    $em->persist($incaricoLegaleRappresentante);

                    $tipoIncaricoUtentePrincipale = $this->trovaDaCostante("SoggettoBundle:TipoIncarico", TipoIncarico::UTENTE_PRINCIPALE);
                    $incaricoUtentePrincipale = $soggetto
                        ->incarica($this->getPersona(), $tipoIncaricoUtentePrincipale, $statoAttivo);
                    $em->persist($incaricoUtentePrincipale);

                    $this->aggiungiSedeLegaleAlleSedi($soggetto, $em);

                    $em->flush();
                    $this->addFlash('success', "Modifiche salvate correttamente");
                    return $this->redirect($this->generateUrl('elenco_soggetti_giuridici'));
                } catch (Exception $e) {dump($e->getMessage());
                    $this->addFlash('error', "Si è verificato un errore nel salvataggio dei dati. Si prega di contattare l’assistenza tecnica");
                    $this->get("logger")->error($e->getMessage());
                }
            }
        }

        $form_params = ["form" => $form->createView()];
        return $this->render('@Soggetto/Soggetto/nuovaPersonaFisica.html.twig', $form_params);
	}

	/**
	 * @Route("/persona_fisica_modifica/{id_soggetto}", name="persona_fisica_modifica")
     * @Template("SoggettoBundle:Soggetto:personaFisica.html.twig")
     * @Menuitem(menuAttivo = "elencoSoggettiGiuridici")
	 * @PaginaInfo(titolo="Modifica persona fisica",sottoTitolo="pagina per modificare i dati della persona fisica selezionata")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Soggetti", route="elenco_soggetti"), @ElementoBreadcrumb(testo="modifica persona fisica")})
	 * @ControlloAccesso(contesto="soggetto", classe="SoggettoBundle:Soggetto", opzioni={"id" = "id_soggetto"}, azione="edit")
	 */
	public function modificaSoggettoAction(Request $request, $id_soggetto) {

		$em = $this->getDoctrine()->getManager();

		/** @var Soggetto $soggetto */
		$soggetto = $em->getRepository('SoggettoBundle:Soggetto')->findOneById($id_soggetto);

		$options["readonly"] = false;
		$options["url_indietro"] = $request->headers->get('referer');
        $options["tipo"] = $soggetto->getTipoByFormaGiuridica();

		$form = $this->createForm(PersonaFisicaType::class, $soggetto, $options);
		$form->handleRequest($request);

		if ($form->isSubmitted()) {
            $cf_presente = $em->getRepository("SoggettoBundle:Soggetto")
                ->isCodicePresente('cf', $form['codice_fiscale']->getData(), $soggetto);

            if ($cf_presente) {
                $form->get('codice_fiscale')->addError(new FormError('Codice fiscale già presente a sistema'));
            }

			if ($form->isValid()) {
				try {
					$em->persist($soggetto);
					$em->flush();

					$this->addFlash('success', "Modifiche salvate correttamente");

					return $this->redirect($this->generateUrl('elenco_soggetti_giuridici'));
				} catch (Exception $e) {
					$this->addFlash('error', "Si è verificato un errore nel salvataggio dei dati. Si prega di contattare l'assistenza tecnica");
					$this->get("logger")->error($e->getMessage());
				}
			}
		} else {
            if (!$soggetto->isFormaGiuridicaCoerente()) {
                $this->addFlash('warning', 'Attenzione! La forma giuridica indicata potrebbe non essere corretta.');
            }
        }

		$form_params["form"] = $form->createView();
		$form_params["azienda"] = $soggetto;

		return $form_params;
	}
}
