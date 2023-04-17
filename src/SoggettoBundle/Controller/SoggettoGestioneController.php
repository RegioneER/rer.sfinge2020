<?php

namespace SoggettoBundle\Controller;

use AnagraficheBundle\Entity\Persona;
use Exception;
use SoggettoBundle\Entity\Adrier;
use SoggettoBundle\Entity\AdrierPersona;
use SoggettoBundle\Entity\Soggetto;
use SoggettoBundle\Form\Entity\NuovoSoggetto;
use SoggettoBundle\Form\NuovoSoggettoType;
use SoggettoBundle\Form\SoggettoType;
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
 * Class SoggettoGestioneController
 */
class SoggettoGestioneController extends SoggettoBaseController {

    /**
     * @Route("/crea_soggetto", name="crea_soggetto")
     * @Template("SoggettoBundle:Soggetto:soggetto.html.twig")
     * @Menuitem(menuAttivo = "creaSoggettoGiuridico")
     * @PaginaInfo(titolo="Nuovo Soggetto",sottoTitolo="")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Soggetti", route="elenco_soggetti"), @ElementoBreadcrumb(testo="Crea soggetto")})
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws Exception
     */
	public function creaSoggettoAction(Request $request): Response {
        $em = $this->getDoctrine()->getManager();

        $formModel = new NuovoSoggetto();

        if($request->query->has('codice_fiscale')) {
            $codiceFiscaleSoggetto = $request->query->get('codice_fiscale');
            $formModel->soggetto->setCodiceFiscale($codiceFiscaleSoggetto);
            $formModel->soggetto->setLaboratorioRicerca((bool)($request->query->get('tipo') == Soggetto::UNIVERSITA));

            /** @var Adrier $adrier */
            $adrier = $this->container->get('app.adrier_service')->dettaglioAdrier($codiceFiscaleSoggetto, 'DettaglioCompletoImpresa');
            if (! is_null($adrier) && 'OK' === $adrier->getHeader()->getEsito()) {
                $datiImpresa = $adrier->getDati()->getDatiImpresa();
                $formModel->soggetto->setCodiceFiscale($datiImpresa->getEstremiImpresa()->getCodiceFiscale());
                $formModel->soggetto->setDenominazione($datiImpresa->getEstremiImpresa()->getDenominazione());
                $formModel->soggetto->setPartitaIva($datiImpresa->getEstremiImpresa()->getPartitaIva());
                $formModel->soggetto->setCodiceAteco($this->getCodiceAtecoDaAdrier($datiImpresa->getInformazioniSede()->getAtecoPrincipale()));
                $formModel->soggetto->setCodiceAtecoSecondario($this->getCodiceAtecoDaAdrier($datiImpresa->getInformazioniSede()->getAtecoSecondario()));
                $formModel->soggetto->setDataCostituzione($datiImpresa->getDurataSocieta()->getDtCostituzione());
                $formModel->soggetto->setFormaGiuridica($this->getFormaGiuridicaDaAdrier($datiImpresa->getEstremiImpresa()->getFormaGiuridica()->getDescrizione()));

                $formModel->soggetto->setVia($datiImpresa->getInformazioniSede()->getIndirizzo()->getVia());
                $formModel->soggetto->setCivico($datiImpresa->getInformazioniSede()->getIndirizzo()->getNCivico());

                // da verificare perchè non ho trovato aziende con sede legale all'estero per poter fare un test
                $formModel->soggetto->setStato($this->getStatoSedeDaAdrier($datiImpresa->getInformazioniSede()->getIndirizzo()->getCComune()));

                $formModel->soggetto->setComune($this->getComuneSedeDaAdrier($datiImpresa->getInformazioniSede()->getIndirizzo()->getCComune()));
                $formModel->soggetto->setCap($datiImpresa->getInformazioniSede()->getIndirizzo()->getCap());
                $formModel->soggetto->setLocalita(implode(" - ", [$datiImpresa->getInformazioniSede()->getIndirizzo()->getFrazione(), $datiImpresa->getInformazioniSede()->getIndirizzo()->getAltreIndicazioni()]));
                $formModel->soggetto->setTel($datiImpresa->getInformazioniSede()->getIndirizzo()->getTelefono());
                $formModel->soggetto->setFax($datiImpresa->getInformazioniSede()->getIndirizzo()->getFax());
                $formModel->soggetto->setEmailPec($datiImpresa->getInformazioniSede()->getIndirizzo()->getIndirizzoPec());

                if($datiImpresa->getLegaleRappresentante() instanceof AdrierPersona) {
                    $formModel->legaleRappresentante->setNome($datiImpresa->getLegaleRappresentante()->getPersonaFisica()->getNome());
                    $formModel->legaleRappresentante->setCognome($datiImpresa->getLegaleRappresentante()->getPersonaFisica()->getCognome());
                    $formModel->legaleRappresentante->setSesso($datiImpresa->getLegaleRappresentante()->getPersonaFisica()->getSesso());

                    $formModel->legaleRappresentante->setCodiceFiscale($datiImpresa->getLegaleRappresentante()->getPersonaFisica()->getCodiceFiscale());
                    $formModel->legaleRappresentante->setNazionalita($this->getStatoLrDaAdrier($datiImpresa->getLegaleRappresentante()->getPersonaFisica()->getEstremiNascita()->getStato(), $formModel->legaleRappresentante->getCodiceFiscale()));
                    $formModel->legaleRappresentante->setDataNascita($datiImpresa->getLegaleRappresentante()->getPersonaFisica()->getEstremiNascita()->getData());
                    $formModel->legaleRappresentante->setStatoNascita($this->getStatoLrDaAdrier($datiImpresa->getLegaleRappresentante()->getPersonaFisica()->getEstremiNascita()->getStato(), $formModel->legaleRappresentante->getCodiceFiscale()));
                    $formModel->legaleRappresentante->setComune($this->getComuneLrDaAdrier($datiImpresa->getLegaleRappresentante()->getPersonaFisica()->getEstremiNascita()->getComune(), $formModel->legaleRappresentante->getCodiceFiscale()));
                }
            }
            $codiceFiscaleLegaleRappresentante = $formModel->legaleRappresentante->getCodiceFiscale();
            if ($codiceFiscaleLegaleRappresentante) {
                // Collego persona se già presente a sistema
                $personaDB = $em->getRepository(Persona::class)->findOneBy([
                    'codice_fiscale' => $codiceFiscaleLegaleRappresentante,
                ]);
                if ($personaDB) {
                    $formModel->legaleRappresentante = $personaDB;
                }
            }
        }

        $options["url_indietro"] = $request->headers->get('referer');
        $options["tipo"] = $request->query->get('tipo');

        $form = $this->createForm(NuovoSoggettoType::class, $formModel, $options);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            // Controlli post submit
            $formAzienda = $form->get('soggetto');
            $piva_presente = $em->getRepository("SoggettoBundle:Soggetto")->isCodicePresente('piva', $formAzienda->get('partita_iva')->getData());
            if ($piva_presente && !$formModel->soggetto->isLaboratorioRicerca()) {
                $formAzienda->get('partita_iva')->addError(new FormError('Partita IVA già presente a sistema'));
            }
            $cf_presente = $em->getRepository("SoggettoBundle:Soggetto")->isCodicePresente('cf', $formAzienda->get('codice_fiscale')->getData());
            if ($cf_presente && !$formModel->soggetto->isLaboratorioRicerca()) {
                $formAzienda->get('codice_fiscale')->addError(new FormError('Codice fiscale già presente a sistema'));
            }

            if ($form->isValid()) {
                $codice_organismo = $em->getRepository("SoggettoBundle:Soggetto")->getMaxCodiceOrganismo() + 1;
                $formModel->soggetto->setCodiceOrganismo($codice_organismo);
                try {
                    $legaleRappresentanteDaDB = $em->getRepository(Persona::class)->findOneBy([
                        'codice_fiscale' => $formModel->legaleRappresentante->getCodiceFiscale(),
                    ]);
                    if($legaleRappresentanteDaDB){
                        $legaleRappresentanteDaDB->mergeData($formModel->legaleRappresentante);
                        $formModel->legaleRappresentante = $legaleRappresentanteDaDB;
                    }

                    $tipoIncaricoLegaleRappresentante = $this->trovaDaCostante("SoggettoBundle:TipoIncarico", TipoIncarico::LR);
                    $statoAttivo = $this->trovaDaCostante("SoggettoBundle:StatoIncarico", StatoIncarico::ATTIVO);
                    $incaricoLegaleRappresentante = $formModel->soggetto->incarica($formModel->legaleRappresentante, $tipoIncaricoLegaleRappresentante, $statoAttivo);
                    $em->persist($formModel->soggetto);
                    $em->persist($formModel->legaleRappresentante);
                    $em->persist($incaricoLegaleRappresentante);

                    $tipoIncaricoUtentePrincipale = $this->trovaDaCostante("SoggettoBundle:TipoIncarico", TipoIncarico::UTENTE_PRINCIPALE);
                    $incaricoUtentePrincipale = $formModel->soggetto->incarica($this->getPersona(), $tipoIncaricoUtentePrincipale, $statoAttivo);
                    $em->persist($incaricoUtentePrincipale);

                    $this->aggiungiSedeLegaleAlleSedi($formModel->soggetto, $em);

                    $em->flush();

                    $this->addFlash('success', "Modifiche salvate correttamente");
                    return $this->redirect($this->generateUrl('elenco_soggetti_giuridici'));
                } catch (Exception $e) {
                    $this->addFlash('error', "Si è verificato un errore nel salvataggio dei dati. Si prega di contattare l'assistenza tecnica");
                    $this->get("logger")->error($e->getMessage());
                }
            }
        }

        $form_params = ["form" => $form->createView()];
        return $this->render('@Soggetto/Soggetto/nuovoSoggetto.html.twig', $form_params);
	}

	/**
	 * @Route("/soggetto_modifica/{id_soggetto}", name="soggetto_modifica")
	 * @Template("SoggettoBundle:Soggetto:soggetto.html.twig")
     * @Menuitem(menuAttivo = "elencoSoggettiGiuridici")
	 * @PaginaInfo(titolo="Modifica soggetto",sottoTitolo="pagina per modificare i dati del soggetto selezionato")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Soggetti", route="elenco_soggetti"), @ElementoBreadcrumb(testo="modifica soggetto")})
	 * @ControlloAccesso(contesto="soggetto", classe="SoggettoBundle:Soggetto", opzioni={"id" = "id_soggetto"}, azione="edit")
	 */
	public function modificaSoggettoAction(Request $request, $id_soggetto) {

		$em = $this->getDoctrine()->getManager();

		$funzioniService = $this->get('funzioni_utili');
		/** @var Soggetto $soggetto */
		$soggetto = $em->getRepository('SoggettoBundle:Soggetto')->findOneById($id_soggetto);
		$data = $funzioniService->getDataComuniFromRequestSedeLegale($request, $soggetto);

		$options["readonly"] = false;
		$options["dataIndirizzo"] = $data;
		$options["em"] = $this->getEm();
		$options["url_indietro"] = $request->headers->get('referer'); // $this->generateUrl("elenco_soggetti");
        $options["tipo"] = $soggetto->getTipoByFormaGiuridica();

		$form = $this->createForm(SoggettoType::class, $soggetto, $options);
		$form->handleRequest($request);

		if ($form->isSubmitted()) {

			if (!$soggetto->isLaboratorioRicerca()) {
				$piva_presente = $em->getRepository("SoggettoBundle:Soggetto")->isCodicePresente('piva', $form['partita_iva']->getData(), $soggetto);
				$cf_presente = $em->getRepository("SoggettoBundle:Soggetto")->isCodicePresente('cf', $form['codice_fiscale']->getData(), $soggetto);

				if ($piva_presente) {
					$form->get('partita_iva')->addError(new FormError('Partita IVA già presente a sistema'));
				}
				if ($cf_presente) {
					$form->get('codice_fiscale')->addError(new FormError('Codice fiscale già presente a sistema'));
				}
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
            if(!$soggetto->isFormaGiuridicaCoerente()) {
                $this->addFlash('warning', 'Attenzione! La forma giuridica indicata potrebbe non essere corretta.');
            }
        }

		$form_params["form"] = $form->createView();
		$form_params["azienda"] = $soggetto;

		return $form_params;
	}
}
