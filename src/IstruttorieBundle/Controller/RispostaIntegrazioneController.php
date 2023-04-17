<?php
namespace IstruttorieBundle\Controller;

use BaseBundle\Controller\BaseController;
use BaseBundle\Exception\SfingeException;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use BaseBundle\Annotation\ControlloAccesso;;
use BaseBundle\Entity\StatoIntegrazione;
use DocumentoBundle\Entity\TipologiaDocumento;
use IstruttorieBundle\Entity\RispostaIntegrazioneIstruttoria;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco integrazioni", route="elenco_integrazioni")})
 */
class RispostaIntegrazioneController extends BaseController {

	/**
	 * @Route("/elenco/{sort}/{direction}/{page}", defaults={"sort" = "i.id", "direction" = "asc", "page" = "1"}, name="elenco_integrazioni")
	 * @Template("IstruttorieBundle:RispostaIntegrazione:elencoIntegrazioni.html.twig")
	 * @PaginaInfo(titolo="Elenco integrazioni",sottoTitolo="mostra l'elenco delle integrazioni richieste")
	 * @Menuitem(menuAttivo = "elencoIntegrazioni")
	 */
	public function elencoIntegrazioniAction() {
		$soggettoSession = $this->getSession()->get(self::SESSIONE_SOGGETTO);
		$soggetto = $this->getEm()->getRepository("SoggettoBundle\Entity\Soggetto")->findOneById($soggettoSession->getId());		
		$datiRicerca = new \IstruttorieBundle\Form\Entity\RicercaIntegrazione();
		$datiRicerca->setSoggetto($soggetto);

		$risultato = $this->get("ricerca")->ricerca($datiRicerca);

		$params = array('risultati' => $risultato["risultato"], "form_ricerca" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"]);
		
		return $params;
	}

	/**
	 * @Route("/elenco_integrazioni_pulisci", name="elenco_integrazioni_pulisci")
	 */
	public function elencoIntegrazioniPulisciAction() {
		$this->get("ricerca")->pulisci(new \IstruttorieBundle\Form\Entity\RicercaIntegrazione());
		return $this->redirectToRoute("elenco_integrazioni");
	}	

	/**
	 * @Route("/{id_integrazione_istruttoria}/dettaglio", name="dettaglio_integrazione_istruttoria")
	 * @PaginaInfo(titolo="Integrazione",sottoTitolo="pagina di dettaglio per una integrazione richiesta")
	 * @Menuitem(menuAttivo = "elencoIntegrazioni")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Dettaglio integrazione")})
	 * @ControlloAccesso(contesto="soggetto", classe="IstruttorieBundle:IntegrazioneIstruttoria", opzioni={"id" = "id_integrazione_istruttoria"})
	 */
	public function dettaglioIntegrazioneAction($id_integrazione_istruttoria) {
		$integrazione_istruttoria = $this->getEm()->getRepository("IstruttorieBundle\Entity\IntegrazioneIstruttoria")->find($id_integrazione_istruttoria);
		$risposta = $integrazione_istruttoria->getRisposta();
        if (is_null($risposta->getFirmatario()) && $integrazione_istruttoria->getProcedura()->isRichiestaFirmaDigitaleStepSuccessivi()) {
			return $this->redirectToRoute("risposta_integrazione_scelta_firmatario", array("id_integrazione_istruttoria" => $id_integrazione_istruttoria));
		}

		$this->getSession()->set("gestore_integrazione_bundle", "IstruttorieBundle");
		$gestore = $this->get("gestore_integrazione")->getGestore($this->getSession()->get("gestore_integrazione_bundle"));

		$dati = array();
		$dati["integrazione_istruttoria"] = $integrazione_istruttoria;
		$dati["azioni_ammesse"] = $gestore->calcolaAzioniAmmesse($integrazione_istruttoria->getRisposta());
		$dati["docs_raggruppati"] = $gestore->raggruppaDocumentiIntegrazione($integrazione_istruttoria);
		$dati["avanzamenti"] = $gestore->gestioneBarraAvanzamento($integrazione_istruttoria->getRisposta());

		return $this->render('IstruttorieBundle:RispostaIntegrazione:dettaglioIntegrazione.html.twig', $dati);
	}

	/**
	 * @Route("/{id_integrazione_istruttoria}/nota_risposta", name="nota_risposta_integrazione_istruttoria")
	 * @PaginaInfo(titolo="Nota risposta integrazione")
	 * @Menuitem(menuAttivo = "elencoIntegrazioni")
	 * @Breadcrumb(elementi={
	 *		@ElementoBreadcrumb(testo="Dettaglio integrazione", route="dettaglio_integrazione_istruttoria", parametri={"id_integrazione_istruttoria"}),
	 *		@ElementoBreadcrumb(testo="Nota risposta")
	 * })
	 * @ControlloAccesso(contesto="soggetto", classe="IstruttorieBundle:IntegrazioneIstruttoria", opzioni={"id" = "id_integrazione_istruttoria"})
	 */	
	public function notaRispostaIntegrazioneAction($id_integrazione_istruttoria) {
		$integrazione_istruttoria = $this->getEm()->getRepository("IstruttorieBundle\Entity\IntegrazioneIstruttoria")->find($id_integrazione_istruttoria);

		$this->getSession()->set("gestore_integrazione_bundle", "IstruttorieBundle");
		$gestore = $this->get("gestore_integrazione")->getGestore($this->getSession()->get("gestore_integrazione_bundle"));

		$opzioni = array("form_options" => array());
		$opzioni["form_options"]["url_indietro"] = $this->generateUrl("dettaglio_integrazione_istruttoria", array("id_integrazione_istruttoria" => $id_integrazione_istruttoria));
		return $gestore->notaRispostaIntegrazione($integrazione_istruttoria, $opzioni)->getResponse();
	}

	/**
	 * 
	 * @Route("/{id_integrazione_istruttoria}/elenco_documenti/{id_proponente}", name="risposta_integrazione_elenco_documenti_richiesta", defaults={"id_proponente" = "-"})
	 * @PaginaInfo(titolo="Elenco Documenti",sottoTitolo="carica i documenti richiesti")
	 * @Menuitem(menuAttivo = "elencoIntegrazioni")
	 * @Breadcrumb(elementi={
	 *		@ElementoBreadcrumb(testo="Dettaglio integrazione", route="dettaglio_integrazione_istruttoria", parametri={"id_integrazione_istruttoria"}),
	 *		@ElementoBreadcrumb(testo="Documenti in integrazione")
	 * })
	 * @ControlloAccesso(contesto="soggetto", classe="IstruttorieBundle:IntegrazioneIstruttoria", opzioni={"id" = "id_integrazione_istruttoria"})
	 */
	public function elencoDocumentiAction($id_integrazione_istruttoria, $id_proponente) {
		$integrazione_istruttoria = $this->getEm()->getRepository("IstruttorieBundle\Entity\IntegrazioneIstruttoria")->find($id_integrazione_istruttoria);

        if (is_null($integrazione_istruttoria->getRisposta()->getFirmatario()) && $integrazione_istruttoria->getProcedura()->isRichiestaFirmaDigitaleStepSuccessivi()) {
			return $this->redirectToRoute("risposta_integrazione_scelta_firmatario", array("id_integrazione_istruttoria" => $id_integrazione_istruttoria));
		}

		$this->getSession()->set("gestore_integrazione_bundle", "IstruttorieBundle");
		$gestore = $this->get("gestore_integrazione")->getGestore($this->getSession()->get("gestore_integrazione_bundle"));

		$proponente = $id_proponente == "-" ? null : $this->getEm()->getRepository("RichiesteBundle\Entity\Proponente")->find($id_proponente);

		$opzioni = array();
		$opzioni["url_corrente"] = $this->generateUrl("risposta_integrazione_elenco_documenti_richiesta", array("id_integrazione_istruttoria" => $id_integrazione_istruttoria, "id_proponente" => $id_proponente));
		$opzioni["url_indietro"] = $this->generateUrl("dettaglio_integrazione_istruttoria", array("id_integrazione_istruttoria" => $id_integrazione_istruttoria));
		$opzioni["route_cancellazione_documento"] = "risposta_integrazione_elimina_documento";
		
		$response = $gestore->elencoDocumenti($integrazione_istruttoria, $proponente, $opzioni);
		return $response->getResponse();
	}

	/**
	 * @Route("/{id_integrazione_istruttoria}/scelta_firmatario_risposta", name="risposta_integrazione_scelta_firmatario")
	 * @PaginaInfo(titolo="Scelta firmatario",sottoTitolo="pagina per scegliere il firmatario della richiesta")
	 * @Breadcrumb(elementi={
	 *		@ElementoBreadcrumb(testo="Dettaglio integrazione", route="dettaglio_integrazione_istruttoria", parametri={"id_integrazione_istruttoria"}),
	 *		@ElementoBreadcrumb(testo="Scelta firmatario")
	 * })
	 * @Menuitem(menuAttivo = "elencoIntegrazioni")
	 * @ControlloAccesso(contesto="soggetto", classe="IstruttorieBundle:IntegrazioneIstruttoria", opzioni={"id" = "id_integrazione_istruttoria"})
	 */
	public function sceltaFirmatarioAction($id_integrazione_istruttoria) {

		try {
			$this->getSession()->set("gestore_integrazione_bundle", "IstruttorieBundle");
			$gestore = $this->get("gestore_integrazione")->getGestore($this->getSession()->get("gestore_integrazione_bundle"));

			$integrazione_istruttoria = $this->getEm()->getRepository("IstruttorieBundle\Entity\IntegrazioneIstruttoria")->find($id_integrazione_istruttoria);
			$richiesta = $integrazione_istruttoria->getIstruttoria()->getRichiesta();
			$opzioni = array("form_options" => array());
			$opzioni["form_options"]["url_indietro"] = $this->generateUrl("dettaglio_integrazione_istruttoria", array("id_integrazione_istruttoria" => $id_integrazione_istruttoria));
			$opzioni["form_options"]["firmatabili"] = $this->getEm()->getRepository("SoggettoBundle:Soggetto")->getFirmatariAmmissibili($richiesta->getSoggetto());

			$response = $gestore->sceltaFirmatario($integrazione_istruttoria, $opzioni);
			return $response->getResponse();
		} catch (SfingeException $e) {
			return $this->addErrorRedirect($e->getMessage(), "dettaglio_integrazione_istruttoria", array("id_integrazione_istruttoria" => $id_integrazione_istruttoria));
		} catch (\Exception $e) {
			//mettere log
			return $this->addErrorRedirect("Errore generico", "dettaglio_integrazione_istruttoria", array("id_integrazione_istruttoria" => $id_integrazione_istruttoria));
		}
	}

	/**
	 *
	 * @Route("/{id_risposta_integrazione}/valida_integrazione_istruttoria", name="valida_integrazione_istruttoria")
	 * @ControlloAccesso(contesto="soggetto", classe="IstruttorieBundle:RispostaIntegrazioneIstruttoria", opzioni={"id" = "id_risposta_integrazione"})
	 * @ParamConverter("rispostaIntegrazione", options={"mapping": {"id_risposta_integrazione"   : "id"}})
	 */
	public function validaIntegrazioneAction(RispostaIntegrazioneIstruttoria $rispostaIntegrazione): Response {
		$this->get('base')->checkCsrf('token');
		$integrazione = $rispostaIntegrazione->getIntegrazione();

		try {
			$this->getSession()->set("gestore_integrazione_bundle", "IstruttorieBundle");
			$gestore = $this->get("gestore_integrazione")->getGestore($this->getSession()->get("gestore_integrazione_bundle"));
			
			$response = $gestore->validaIntegrazione($rispostaIntegrazione->getId());
			return $response->getResponse();
		} catch (SfingeException $e) {
			return $this->addErrorRedirect($e->getMessage(), "dettaglio_integrazione_istruttoria", array("id_integrazione_istruttoria" => $integrazione->getId()));
		} catch (\Exception $e) {
			//mettere log
			return $this->addErrorRedirect("Errore generico", "dettaglio_integrazione_istruttoria", array("id_integrazione_istruttoria" => $integrazione->getId()));
		}
	}

	/**
	 * @Route("/{id_proponente}/elimina_documento/{id_documento_integrazione}", name="risposta_integrazione_elimina_documento", defaults={"id_proponente" = "-"})
	 */
	public function eliminaDocumentoAction($id_documento_integrazione, $id_proponente) {
		$this->get('base')->checkCsrf('token');
		$documento = $this->getEm()->getRepository("IstruttorieBundle\Entity\DocumentoIntegrazioneIstruttoria")->find($id_documento_integrazione);
		$integrazioneIstruttoria = $documento->getRispostaIntegrazione()->getIntegrazione();
		
		$contestoSoggetto = $this->get('contesto')->getContestoRisorsa($integrazioneIstruttoria, "soggetto");
		$accessoConsentito = $this->isGranted(\SoggettoBundle\Security\SoggettoVoter::ALL, $contestoSoggetto);
		if (!$accessoConsentito) {
			throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException('Accesso non consentito al documento integrazione');
		}		

		$this->getSession()->set("gestore_integrazione_bundle", "IstruttorieBundle");
		$gestore = $this->get("gestore_integrazione")->getGestore($this->getSession()->get("gestore_integrazione_bundle"));

		$response = $gestore->eliminaDocumento($id_documento_integrazione, array("url_indietro" => $this->generateUrl("risposta_integrazione_elenco_documenti_richiesta", array("id_proponente" => $id_proponente, "id_integrazione_istruttoria" => $integrazioneIstruttoria->getId()))));
		
		return $response->getResponse();
	}

	/**
	 *  @Route("/{id_integrazione_istruttoria}/scarica_integrazione_risposta", name="scarica_integrazione_risposta")
	 *  @ControlloAccesso(contesto="soggetto", classe="IstruttorieBundle:IntegrazioneIstruttoria", opzioni={"id" = "id_integrazione_istruttoria"})
	 */
	public function scaricaRispostaAction($id_integrazione_istruttoria) {
		$integrazioneIstruttoria = $this->getEm()->getRepository("IstruttorieBundle\Entity\IntegrazioneIstruttoria")->find($id_integrazione_istruttoria);
		$risposta = $integrazioneIstruttoria->getRisposta();

		if (is_null($risposta)) {
			return $this->addErrorRedirect("Richiesta non valida", "elenco_richieste");
		}

		if (is_null($risposta->getDocumentoRisposta())) {
			return $this->addErrorRedirect("Nessun documento associato alla risposta", "dettaglio_integrazione_istruttoria", array("id_integrazione_istruttoria" => $id_integrazione_istruttoria));
		}

		return $this->get("documenti")->scaricaDaId($risposta->getDocumentoRisposta()->getId());
	}
	
	/**
	 *  @Route("/{id_integrazione_istruttoria}/scarica_integrazione_risposta_firmata", name="scarica_integrazione_risposta_firmata")
	 *  @ControlloAccesso(contesto="soggetto", classe="IstruttorieBundle:IntegrazioneIstruttoria", opzioni={"id" = "id_integrazione_istruttoria"})
	 */
	public function scaricaRispostaFirmataAction($id_integrazione_istruttoria) {
		$integrazione_istruttoria = $this->getEm()->getRepository("IstruttorieBundle\Entity\IntegrazioneIstruttoria")->find($id_integrazione_istruttoria);
		$risposta = $integrazione_istruttoria->getRisposta();

		if (is_null($risposta)) {
			return $this->addErrorRedirect("Richiesta non valida", "elenco_richieste");
		}

		if (is_null($risposta->getDocumentoRispostaFirmato())) {
			return $this->addErrorRedirect("Nessun documento associato alla risposta", "dettaglio_integrazione_istruttoria", array("id_integrazione_istruttoria" => $id_integrazione_istruttoria));
		}

		return $this->get("documenti")->scaricaDaId($risposta->getDocumentoRispostaFirmato()->getId());
	}	

	/**
	 * @Route("/{id_integrazione_istruttoria}/carica_integrazione_risposta_firmata", name="carica_integrazione_risposta_firmata")
	 * @Template("IstruttorieBundle:RispostaIntegrazione:caricaRispostaFirmata.html.twig")
	 * @PaginaInfo(titolo="Carica risposta integrazione firmata",sottoTitolo="pagina per caricare la risposta a richiesta di integrazione firmata")
	 * @Breadcrumb(elementi={
	 *		@ElementoBreadcrumb(testo="Dettaglio integrazione", route="dettaglio_integrazione_istruttoria", parametri={"id_integrazione_istruttoria"}),
	 *		@ElementoBreadcrumb(testo="Carica risposta")
	 * })
	 * @ControlloAccesso(contesto="soggetto", classe="IstruttorieBundle:IntegrazioneIstruttoria", opzioni={"id" = "id_integrazione_istruttoria"})
	 */
	public function caricaRispostaFirmataAction($id_integrazione_istruttoria) {
		$em = $this->getEm();

		$request = $this->getCurrentRequest();

		$documento_file = new \DocumentoBundle\Entity\DocumentoFile();

		$integrazione = $this->getEm()->getRepository("IstruttorieBundle\Entity\IntegrazioneIstruttoria")->find($id_integrazione_istruttoria);
        $risposta_integrazione = $integrazione->getRisposta();


		if (!$risposta_integrazione) {
			throw $this->createNotFoundException('Risorsa non trovata');
		}

		try {

			if (!$risposta_integrazione->getStato()->uguale(StatoIntegrazione::INT_VALIDATA)) {
				throw new SfingeException("Stato non valido per effettuare l'operazione");
			}
		} catch (SfingeException $e) {
			return $this->addErrorRedirect("Errore geenrico", "dettaglio_integrazione_istruttoria", array("id_integrazione_istruttoria" => $id_integrazione_istruttoria));
		}

		$opzioni_form["tipo"] = TipologiaDocumento::RICHIESTA_INTEGRAZIONE_RISPOSTA_FIRMATO;
		$opzioni_form["cf_firmatario"] = $risposta_integrazione->getFirmatario()->getCodiceFiscale();
		$form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documento_file, $opzioni_form);
		$form->add("pultanti", "BaseBundle\Form\SalvaIndietroType", array("url" => $this->generateUrl("dettaglio_integrazione_istruttoria", array("id_integrazione_istruttoria" => $id_integrazione_istruttoria))));
		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				try {
					$this->container->get("documenti")->carica($documento_file, 0);
					$risposta_integrazione->setDocumentoRispostaFirmato($documento_file);
					$this->container->get("sfinge.stati")->avanzaStato($risposta_integrazione, StatoIntegrazione::INT_FIRMATA, true);
					$em->flush();
					return $this->addSuccessRedirect("Documento caricato correttamente", "dettaglio_integrazione_istruttoria", array("id_integrazione_istruttoria" => $id_integrazione_istruttoria));
				} catch (\Exception $e) {
					//TODO gestire cancellazione del file
					$this->addFlash('error', "Errore generico");
				}
			}
		}
		$form_view = $form->createView();

		return array("id_integrazione_istruttoria" => $id_integrazione_istruttoria, "form" => $form_view);
	}

	/**
	 * @Route("/{id_integrazione_istruttoria}/invia_risposta_integrazione_istruttoria", name="invia_risposta_integrazione_istruttoria")
	 * @ControlloAccesso(contesto="soggetto", classe="IstruttorieBundle:IntegrazioneIstruttoria", opzioni={"id" = "id_integrazione_istruttoria"})
	 */
	public function inviaRispostaAction($id_integrazione_istruttoria) {
		$this->get('base')->checkCsrf('token');
        $integrazione = $this->getEm()->getRepository("IstruttorieBundle\Entity\IntegrazioneIstruttoria")->find($id_integrazione_istruttoria);
		$rispostaIntegrazione = $integrazione->getRisposta();
		try {
			$this->getSession()->set("gestore_integrazione_bundle", "IstruttorieBundle");
			$gestore = $this->get("gestore_integrazione")->getGestore($this->getSession()->get("gestore_integrazione_bundle"));

			$response = $gestore->inviaRisposta($rispostaIntegrazione->getId());
			return $response->getResponse();
		} catch (SfingeException $e) {
			return $this->addErrorRedirect($e->getMessage(), "dettaglio_integrazione_istruttoria", array("id_integrazione_istruttoria" => $integrazione->getId()));
		} catch (\Exception $e) {
			//mettere log
			return $this->addErrorRedirect("Errore generico", "dettaglio_integrazione_istruttoria", array("id_integrazione_istruttoria" => $integrazione->getId()));
		}
	}
	
	/**
	 * @Route("/{id_integrazione_istruttoria}/invalida_integrazione_istruttoria", name="invalida_integrazione_istruttoria")
	 * @ControlloAccesso(contesto="soggetto", classe="IstruttorieBundle:IntegrazioneIstruttoria", opzioni={"id" = "id_integrazione_istruttoria"})
	 */
	public function invalidaIntegrazioneAction($id_integrazione_istruttoria) {
		$this->get('base')->checkCsrf('token');
        $integrazione = $this->getEm()->getRepository("IstruttorieBundle\Entity\IntegrazioneIstruttoria")->find($id_integrazione_istruttoria);
        $rispostaIntegrazione = $integrazione->getRisposta();
		try {
			$this->getSession()->set("gestore_integrazione_bundle", "IstruttorieBundle");
			$gestore = $this->get("gestore_integrazione")->getGestore($this->getSession()->get("gestore_integrazione_bundle"));

			$response = $gestore->invalidaIntegrazione($rispostaIntegrazione->getId());
			return $response->getResponse();
		} catch (SfingeException $e) {
			return $this->addErrorRedirect($e->getMessage(), "dettaglio_integrazione_istruttoria", array("id_integrazione_istruttoria" => $id_integrazione_istruttoria));
		} catch (\Exception $e) {
			//mettere log
			return $this->addErrorRedirect("Errore generico", "dettaglio_integrazione_istruttoria", array("id_integrazione_istruttoria" => $id_integrazione_istruttoria));
		}
	}

    /**
     * @Route("/{id_integrazione_istruttoria}/imposta_risposta_integrazione_come_letta/{da_comunicazione}", defaults={"da_comunicazione" = "false"}, name="imposta_risposta_integrazione_come_letta")
     * @param int $id_integrazione_istruttoria
     * @param $da_comunicazione
     * @return mixed|RedirectResponse
     */
    public function impostaRispostaComeLettaAction(int $id_integrazione_istruttoria, $da_comunicazione) {
        $this->get('base')->checkCsrf('token');
        try {
            $this->getSession()->set("gestore_integrazione_bundle", "IstruttorieBundle");
            $gestore = $this->get("gestore_integrazione")->getGestore($this->getSession()->get("gestore_integrazione_bundle"));
            $da_comunicazione == 'false' ? $da_comunicazione = false : $da_comunicazione = true;
            $response = $gestore->impostaRispostaComeLetta($id_integrazione_istruttoria, $da_comunicazione);
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "dettaglio_integrazione_istruttoria", ["id_integrazione_istruttoria" => $id_integrazione_istruttoria]);
        } catch (Exception $e) {
            return $this->addErrorRedirect("Errore generico", "dettaglio_integrazione_istruttoria", ["id_integrazione_istruttoria" => $id_integrazione_istruttoria]);
        }
    }

    /**
     * @Route("/{id_integrazione_istruttoria}/carica_documento_richiesta_dropzone", name="carica_documento_integrazione_istruttoria_dropzone")
     * @Method({"POST"})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="IstruttorieBundle:IntegrazioneIstruttoria", opzioni={"id"="id_integrazione_istruttoria"})
     * @param Request $request
     * @param $id_integrazione_istruttoria
     * @return JsonResponse
     * @throws Exception
     */
    public function caricaDocumentoDropzoneAction(Request $request, $id_integrazione_istruttoria): JsonResponse
    {
        $gestore = $this->get("gestore_integrazione")->getGestore($this->getSession()->get("gestore_integrazione_bundle"));
        $arrayResult = $gestore->caricaDocumentoDropzone($request, $id_integrazione_istruttoria);
        return new JsonResponse($arrayResult);
    }

    /**
     * @Route("/{id_integrazione_istruttoria}/concat_chunks_documento_integrazione_istruttoria_dropzone", name="concat_chunks_documento_integrazione_istruttoria_dropzone")
     * @Method({"POST"})
     * @throws Exception
     */
    public function concatChunksDocumentoDropzoneAction(Request $request, $id_integrazione_istruttoria): JsonResponse
    {
        $gestore = $this->get("gestore_integrazione")->getGestore($this->getSession()->get("gestore_integrazione_bundle"));
        $arrayResult = $gestore->concatChunksDocumentoDropzone($request, $id_integrazione_istruttoria);
        return new JsonResponse($arrayResult);
    }
}
