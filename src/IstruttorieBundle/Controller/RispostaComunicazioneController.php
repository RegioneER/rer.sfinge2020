<?php

namespace IstruttorieBundle\Controller;

use BaseBundle\Controller\BaseController;
use BaseBundle\Exception\SfingeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use BaseBundle\Annotation\ControlloAccesso;
use RichiesteBundle\Form\Entity\RicercaRichiesta;
use BaseBundle\Entity\StatoComunicazioneEsitoIstruttoria;
use DocumentoBundle\Entity\TipologiaDocumento;

class RispostaComunicazioneController extends BaseController {

	/**
	 * @Route("/elenco/{sort}/{direction}/{page}", defaults={"sort" = "i.id", "direction" = "asc", "page" = "1"}, name="elenco_comunicazioni_beneficiario")
	 * @Template("IstruttorieBundle:RispostaComunicazione:elencoComunicazioni.html.twig")
	 * @PaginaInfo(titolo="Elenco comunicazioni",sottoTitolo="mostra l'elenco delle comunicazioni inviate dalla Regione")
	 * @Menuitem(menuAttivo = "elencoComunicazioni")
	 */
	public function elencoComunicazioniAction() {
		$soggettoSession = $this->getSession()->get(self::SESSIONE_SOGGETTO);
		$soggetto = $this->getEm()->getRepository("SoggettoBundle\Entity\Soggetto")->findOneById($soggettoSession->getId());

		$datiRicerca = new \IstruttorieBundle\Form\Entity\RicercaComunicazione();
		$datiRicerca->setSoggetto($soggetto);

		$risultato = $this->get("ricerca")->ricerca($datiRicerca);
        $risultato["risultato"] = $this->get('app.manager.comunicazioni_manager')->aggiornaSlidingPaginationElementiVisibili($risultato["risultato"], $this->getUser(), $soggetto, 'ESITOPRG');

		$params = array('menu' => 'com_esito', 'risultati' => $risultato["risultato"], "form_ricerca" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"]);

		return $params;
	}

	/**
	 * @Route("/{id_comunicazione}/dettaglio", name="dettaglio_comunicazione")
	 * @PaginaInfo(titolo="Comunicazione",sottoTitolo="pagina di dettaglio della comunicazione")
	 * @Menuitem(menuAttivo = "elencoComunicazioni")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Dettaglio comunicazione")})
	 * @ControlloAccesso(contesto="soggetto", classe="IstruttorieBundle:ComunicazioneEsitoIstruttoria", opzioni={"id" = "id_comunicazione"})
	 */
	public function dettaglioComunicazioneAction($id_comunicazione) {
		$comunicazione = $this->getEm()->getRepository("IstruttorieBundle\Entity\ComunicazioneEsitoIstruttoria")->find($id_comunicazione);
		$dati = array(
			"comunicazione" => $comunicazione
		);
		return $this->render('IstruttorieBundle:RispostaComunicazione:comunicazioneEsito.html.twig', $dati);
	}

	/**
	 * @Route("/{id_comunicazione}/scarica_comunicazione", name="scarica_comunicazione_beneficiario")	
	 * @Template("IstruttorieBundle:Istruttoria:elencoComunicazioni.html.twig")
	 * @PaginaInfo(titolo="Comunicazioni",sottoTitolo="")
	 * @ControlloAccesso(contesto="soggetto", classe="IstruttorieBundle:ComunicazioneEsitoIstruttoria", opzioni={"id" = "id_comunicazione"})
	 */
	public function scaricaComunicazioneEsitoAction($id_comunicazione) {

		$comunicazione = $this->getEm()->getRepository("IstruttorieBundle:ComunicazioneEsitoIstruttoria")->find($id_comunicazione);
		if (is_null($comunicazione)) {
			return $this->addErrorRedirect("Comunicazione non valida", "elenco_richieste");
		}
		if (is_null($comunicazione->getDocumento())) {
			return $this->addErrorRedirect("Nessun documento associato alla comuncazione", "elenco_richieste");
		}
		return $this->get("documenti")->scaricaDaId($comunicazione->getDocumento()->getId());
	}

	/**
	 * @Route("/{id_comunicazione}/scelta_firmatario_risposta", name="risposta_comunicazione_istruttoria_firmatario")
	 * @PaginaInfo(titolo="Scelta firmatario",sottoTitolo="pagina per scegliere il firmatario della richiesta")
	 * @Breadcrumb(elementi={
	 * 		@ElementoBreadcrumb(testo="Dettaglio comunicazione", route="dettaglio_comunicazione", parametri={"id_comunicazione"}),
	 * 		@ElementoBreadcrumb(testo="Scelta firmatario")
	 * })
	 * @Menuitem(menuAttivo = "elencoComunicazioni")
	 * @ControlloAccesso(contesto="soggetto", classe="IstruttorieBundle:ComunicazioneEsitoIstruttoria", opzioni={"id" = "id_comunicazione"})
	 */
	public function sceltaFirmatarioAction($id_comunicazione) {

		try {
			$this->getSession()->set("gestore_comunicazione_bundle", "IstruttorieBundle");
			$gestore = $this->get("gestore_comunicazione")->getGestore($this->getSession()->get("gestore_comunicazione_bundle"));

			$comunicazione = $this->getEm()->getRepository("IstruttorieBundle\Entity\ComunicazioneEsitoIstruttoria")->find($id_comunicazione);
			$richiesta = $comunicazione->getIstruttoria()->getRichiesta();
			$opzioni = array("form_options" => array());
			$opzioni["form_options"]["url_indietro"] = $this->generateUrl("dettaglio_risposta_comunicazione", array("id_comunicazione" => $id_comunicazione));
			$opzioni["form_options"]["firmatabili"] = $this->getEm()->getRepository("SoggettoBundle:Soggetto")->getFirmatariAmmissibili($richiesta->getSoggetto());

			$response = $gestore->sceltaFirmatario($comunicazione, $opzioni);
			return $response->getResponse();
		} catch (SfingeException $e) {
			return $this->addErrorRedirect($e->getMessage(), "dettaglio_comunicazione", array("id_comunicazione" => $id_comunicazione));
		} catch (\Exception $e) {
			//mettere log
			return $this->addErrorRedirect("Errore generico", "dettaglio_comunicazione", array("id_comunicazione" => $id_comunicazione));
		}
	}

	/**
	 * @Route("/{id_comunicazione}/dettaglio_risposta", name="dettaglio_risposta_comunicazione")
	 * @PaginaInfo(titolo="Comunicazione",sottoTitolo="pagina di dettaglio per una risposta di comunicazione")
	 * @Menuitem(menuAttivo = "elencoComunicazioni")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Dettaglio risposta comunicazione")})
	 * @ControlloAccesso(contesto="soggetto", classe="IstruttorieBundle:ComunicazioneEsitoIstruttoria", opzioni={"id" = "id_comunicazione"})
	 */
	public function dettaglioRispostaComunicazioneAction($id_comunicazione) {
		$em = $this->getEm();
		$comunicazione = $em->getRepository("IstruttorieBundle\Entity\ComunicazioneEsitoIstruttoria")->find($id_comunicazione);
		if (is_null($comunicazione->getRisposta())) {
			$risposta = new \IstruttorieBundle\Entity\RispostaComunicazioneEsitoIstruttoria();
			try {
				$this->container->get("sfinge.stati")->avanzaStato($risposta, \BaseBundle\Entity\StatoComunicazioneEsitoIstruttoria::ESI_INSERITA);
				$risposta->setComunicazione($comunicazione);
				$em->persist($comunicazione);
				$em->flush();
			} catch (\Exception $e) {
				$this->addFlash('error', "Errore generico");
			}
		} else {
			$risposta = $comunicazione->getRisposta();
		}
        if (is_null($risposta->getFirmatario()) && $comunicazione->getProcedura()->isRichiestaFirmaDigitaleStepSuccessivi()) {
			return $this->redirectToRoute("risposta_comunicazione_istruttoria_firmatario", array("id_comunicazione" => $id_comunicazione));
		}

		$this->getSession()->set("gestore_comunicazione_bundle", "IstruttorieBundle");
		$gestore = $this->get("gestore_comunicazione")->getGestore($this->getSession()->get("gestore_comunicazione_bundle"));

		$dati = array();
		$dati["comunicazione"] = $comunicazione;
		$dati["azioni_ammesse"] = $gestore->calcolaAzioniAmmesse($comunicazione->getRisposta());
		$dati["avanzamenti"] = $gestore->gestioneBarraAvanzamento($comunicazione->getRisposta());

		return $this->render('IstruttorieBundle:RispostaComunicazione:dettaglioRisposta.html.twig', $dati);
	}

	/**
	 * @Route("/{id_comunicazione}/nota_risposta_es", name="nota_risposta_comunicazione_istruttoria_es")
	 * @PaginaInfo(titolo="Nota risposta comunicazione")
	 * @Menuitem(menuAttivo = "elencoComunicazioni")
	 * @Breadcrumb(elementi={
	 * 		@ElementoBreadcrumb(testo="Dettaglio risposta comunicazione", route="dettaglio_risposta_comunicazione", parametri={"id_comunicazione"}),
	 * 		@ElementoBreadcrumb(testo="Nota risposta")
	 * })
	 * @ControlloAccesso(contesto="soggetto", classe="IstruttorieBundle:ComunicazioneEsitoIstruttoria", opzioni={"id" = "id_comunicazione"})
	 */
	public function notaRispostaComunicazioneAction($id_comunicazione) {
		$integrazione_istruttoria = $this->getEm()->getRepository("IstruttorieBundle\Entity\ComunicazioneEsitoIstruttoria")->find($id_comunicazione);

		$this->getSession()->set("gestore_comunicazione_bundle", "IstruttorieBundle");
		$gestore = $this->get("gestore_comunicazione")->getGestore($this->getSession()->get("gestore_comunicazione_bundle"));

		$opzioni = array("form_options" => array());
		$opzioni["form_options"]["url_indietro"] = $this->generateUrl("dettaglio_risposta_comunicazione", array("id_comunicazione" => $id_comunicazione));
		return $gestore->notaRispostaComunicazione($integrazione_istruttoria, $opzioni)->getResponse();
	}

	/**
	 * 
	 * @Route("/{id_comunicazione}/elenco_documenti_es/{id_proponente}", name="risposta_comunicazione_elenco_documenti_richiesta_es", defaults={"id_proponente" = "-"})
	 * @PaginaInfo(titolo="Elenco Documenti",sottoTitolo="carica i documenti richiesti")
	 * @Menuitem(menuAttivo = "elencoComunicazioni")
	 * @Breadcrumb(elementi={
	 * 		@ElementoBreadcrumb(testo="Dettaglio risposta comunicazione", route="dettaglio_risposta_comunicazione", parametri={"id_comunicazione"}),
	 * 		@ElementoBreadcrumb(testo="Documenti risposta comunicazione")
	 * })
	 * @ControlloAccesso(contesto="soggetto", classe="IstruttorieBundle:ComunicazioneEsitoIstruttoria", opzioni={"id" = "id_comunicazione"})
	 */
	public function elencoDocumentiAction($id_comunicazione, $id_proponente) {
		$comunicazione = $this->getEm()->getRepository("IstruttorieBundle\Entity\ComunicazioneEsitoIstruttoria")->find($id_comunicazione);

        if (is_null($comunicazione->getRisposta()->getFirmatario()) && $comunicazione->getProcedura()->isRichiestaFirmaDigitaleStepSuccessivi()) {
			return $this->redirectToRoute("dettaglio_risposta_comunicazione", array("id_comunicazione" => $id_comunicazione));
		}

		$this->getSession()->set("gestore_comunicazione_bundle", "IstruttorieBundle");
		$gestore = $this->get("gestore_comunicazione")->getGestore($this->getSession()->get("gestore_comunicazione_bundle"));

		$proponente = $id_proponente == "-" ? null : $this->getEm()->getRepository("RichiesteBundle\Entity\Proponente")->find($id_proponente);

		$opzioni = array();
		$opzioni["url_corrente"] = $this->generateUrl("risposta_comunicazione_elenco_documenti_richiesta_es", array("id_comunicazione" => $id_comunicazione, "id_proponente" => $id_proponente));
		$opzioni["url_indietro"] = $this->generateUrl("dettaglio_risposta_comunicazione", array("id_comunicazione" => $id_comunicazione));
		$opzioni["route_cancellazione_documento"] = "elimina_documento_risposta_comunicazione_es";

		$response = $gestore->elencoDocumenti($comunicazione, $proponente, $opzioni);
		return $response->getResponse();
	}

	/**
	 *
	 * @Route("/{id_comunicazione_risposta}/valida_comunicazione_risposta", name="valida_comunicazione_risposta")
	 * @ControlloAccesso(contesto="soggetto", classe="IstruttorieBundle:RispostaComunicazioneEsitoIstruttoria", opzioni={"id" = "id_comunicazione_risposta"})
	 */
	public function validaComunicazioneRispotaAction($id_comunicazione_risposta) {
		$this->get('base')->checkCsrf('token');
		$comunicazione_risposta = $this->getEm()->getRepository("IstruttorieBundle\Entity\RispostaComunicazioneEsitoIstruttoria")->find($id_comunicazione_risposta);
		$comunicazione = $comunicazione_risposta->getComunicazione();
		try {
			$this->getSession()->set("gestore_comunicazione_bundle", "IstruttorieBundle");
			$gestore = $this->get("gestore_comunicazione")->getGestore($this->getSession()->get("gestore_comunicazione_bundle"));

			$response = $gestore->validaRispostaComunicazione($comunicazione_risposta);
			return $response->getResponse();
		} catch (SfingeException $e) {
			return $this->addErrorRedirect($e->getMessage(), "dettaglio_risposta_comunicazione", array("id_comunicazione" => $comunicazione->getId()));
		} catch (\Exception $e) {
			return $this->addErrorRedirect("Errore generico", "dettaglio_risposta_comunicazione", array("id_comunicazione" => $comunicazione->getId()));
		}
	}

	/**
	 * @Route("/{id_proponente}/elimina_documento_es/{id_documento_risposta}", name="elimina_documento_risposta_comunicazione_es", defaults={"id_proponente" = "-"})
	 */
	public function eliminaDocumentoAction($id_documento_risposta, $id_proponente) {
		$this->get('base')->checkCsrf('token');
		$documento = $this->getEm()->getRepository("IstruttorieBundle\Entity\RispostaEsitoIstruttoriaDocumento")->find($id_documento_risposta);
		$comunicazione = $documento->getRispostaComunicazione()->getComunicazione();

		$contestoSoggetto = $this->get('contesto')->getContestoRisorsa($comunicazione, "soggetto");
		$accessoConsentito = $this->isGranted(\SoggettoBundle\Security\SoggettoVoter::ALL, $contestoSoggetto);
		if (!$accessoConsentito) {
			throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException('Accesso non consentito al documento integrazione');
		}

		$this->getSession()->set("gestore_comunicazione_bundle", "IstruttorieBundle");
		$gestore = $this->get("gestore_comunicazione")->getGestore($this->getSession()->get("gestore_comunicazione_bundle"));

		$response = $gestore->eliminaDocumento($id_documento_risposta, array("url_indietro" => $this->generateUrl("risposta_comunicazione_elenco_documenti_richiesta_es", array("id_proponente" => $id_proponente, "id_comunicazione" => $comunicazione->getId()))));

		return $response->getResponse();
	}

	/**
	 *  @Route("/{id_comunicazione_risposta}/scarica_comunicazione_risposta", name="scarica_comunicazione_risposta")
	 *  @ControlloAccesso(contesto="soggetto", classe="IstruttorieBundle:RispostaComunicazioneEsitoIstruttoria", opzioni={"id" = "id_comunicazione_risposta"})
	 */
	public function scaricaRispostaAction($id_comunicazione_risposta) {
		$risposta = $this->getEm()->getRepository("IstruttorieBundle\Entity\RispostaComunicazioneEsitoIstruttoria")->find($id_comunicazione_risposta);
		$comunicazione = $risposta->getComunicazione();

		if (is_null($risposta)) {
			return $this->addErrorRedirect("Richiesta non valida", "elenco_richieste");
		}

		if (is_null($risposta->getDocumentoRisposta())) {
			return $this->addErrorRedirect("Nessun documento associato alla risposta", "dettaglio_risposta_comunicazione", array("id_comunicazione" => $comunicazione->getId()));
		}

		return $this->get("documenti")->scaricaDaId($risposta->getDocumentoRisposta()->getId());
	}

	/**
	 *  @Route("/{id_comunicazione_risposta}/scarica_comunicazione_risposta_firmata", name="scarica_comunicazione_risposta_firmata")
	 *  @ControlloAccesso(contesto="soggetto", classe="IstruttorieBundle:RispostaComunicazioneEsitoIstruttoria", opzioni={"id" = "id_comunicazione_risposta"})
	 */
	public function scaricaRispostaFirmataAction($id_comunicazione_risposta) {
		$risposta = $this->getEm()->getRepository("IstruttorieBundle\Entity\RispostaComunicazioneEsitoIstruttoria")->find($id_comunicazione_risposta);
		$comunicazione = $risposta->getComunicazione();

		if (is_null($risposta)) {
			return $this->addErrorRedirect("Richiesta non valida", "elenco_richieste");
		}

		if (is_null($risposta->getDocumentoRispostaFirmato())) {
			return $this->addErrorRedirect("Nessun documento associato alla risposta", "dettaglio_risposta_comunicazione", array("id_comunicazione" => $comunicazione->getId()));
		}

		return $this->get("documenti")->scaricaDaId($risposta->getDocumentoRispostaFirmato()->getId());
	}

	/**
	 * @Route("/{id_comunicazione}/carica_comunicazione_risposta_firmata/{id_comunicazione_risposta}", name="carica_comunicazione_risposta_firmata")
	 * @Template("IstruttorieBundle:RispostaIntegrazione:caricaRispostaFirmata.html.twig")
	 * @PaginaInfo(titolo="Carica risposta comunicazione firmata",sottoTitolo="pagina per caricare la risposta di comunicazione firmata")
	 * @Breadcrumb(elementi={
	 * 		@ElementoBreadcrumb(testo="Dettaglio risposta comunicazione", route="dettaglio_risposta_comunicazione", parametri={"id_comunicazione"}),
	 * 		@ElementoBreadcrumb(testo="Carica risposta")
	 * })
	 * @ControlloAccesso(contesto="soggetto", classe="IstruttorieBundle:RispostaComunicazioneEsitoIstruttoria", opzioni={"id" = "id_comunicazione_risposta"})
	 */
	public function caricaRispostaFirmataAction($id_comunicazione_risposta, $id_comunicazione) {
		$em = $this->getEm();

		$request = $this->getCurrentRequest();

		$documento_file = new \DocumentoBundle\Entity\DocumentoFile();

		$comunicazione_risposta = $this->getEm()->getRepository("IstruttorieBundle\Entity\RispostaComunicazioneEsitoIstruttoria")->find($id_comunicazione_risposta);
		$comunicazione = $comunicazione_risposta->getComunicazione();
		
		if (!$comunicazione_risposta) {
			throw $this->createNotFoundException('Risorsa non trovata');
		}

		try {

			if (!$comunicazione_risposta->getStato()->uguale(\BaseBundle\Entity\StatoComunicazioneEsitoIstruttoria::ESI_VALIDATA)) {
				throw new SfingeException("Stato non valido per effettuare l'operazione");
			}
		} catch (SfingeException $e) {
			return $this->addErrorRedirect("Errore geenrico", "dettaglio_risposta_comunicazione", array("id_comunicazione" => $comunicazione->getId()));
		}

		$opzioni_form["tipo"] = TipologiaDocumento::ESITO_RISPOSTA_ISTRUTTORIA_FIRMATO;
		$opzioni_form["cf_firmatario"] = $comunicazione_risposta->getFirmatario()->getCodiceFiscale();
		$form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documento_file, $opzioni_form);
		$form->add("pultanti", "BaseBundle\Form\SalvaIndietroType", array("url" => $this->generateUrl("dettaglio_risposta_comunicazione", array("id_comunicazione" => $comunicazione->getId()))));
		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				try {
					$this->container->get("documenti")->carica($documento_file, 0);
					$comunicazione_risposta->setDocumentoRispostaFirmato($documento_file);
					$this->container->get("sfinge.stati")->avanzaStato($comunicazione_risposta, StatoComunicazioneEsitoIstruttoria::ESI_FIRMATA, true);
					$em->flush();
					return $this->addSuccessRedirect("Documento caricato correttamente", "dettaglio_risposta_comunicazione", array("id_comunicazione" => $comunicazione->getId()));
				} catch (\Exception $e) {
					//TODO gestire cancellazione del file
					$this->addFlash('error', "Errore generico");
				}
			}
		}
		$form_view = $form->createView();

		return array("id_comunicazione_risposta" => $id_comunicazione_risposta, "form" => $form_view);
	}

	/**
	 * @Route("/{id_comunicazione_risposta}/invia_risposta_comunicazione_istruttoria", name="invia_risposta_comunicazione_istruttoria")
	 * @ControlloAccesso(contesto="soggetto", classe="IstruttorieBundle:RispostaComunicazioneEsitoIstruttoria", opzioni={"id" = "id_comunicazione_risposta"})
	 */
	public function inviaComunicazioneRispostaAction($id_comunicazione_risposta) {
		$this->get('base')->checkCsrf('token');
		$comunicazione_risposta = $this->getEm()->getRepository("IstruttorieBundle\Entity\RispostaComunicazioneEsitoIstruttoria")->find($id_comunicazione_risposta);
		$comunicazione = $comunicazione_risposta->getComunicazione();
		try {
			$this->getSession()->set("gestore_comunicazione_bundle", "IstruttorieBundle");
			$gestore = $this->get("gestore_comunicazione")->getGestore($this->getSession()->get("gestore_comunicazione_bundle"));

			$response = $gestore->inviaRisposta($comunicazione_risposta);
			return $response->getResponse();
		} catch (SfingeException $e) {
			return $this->addErrorRedirect($e->getMessage(), "dettaglio_risposta_comunicazione", array("id_comunicazione" => $comunicazione->getId()));
		} catch (\Exception $e) {
			//mettere log
			return $this->addErrorRedirect("Errore generico", "dettaglio_risposta_comunicazione", array("id_comunicazione" => $comunicazione->getId()));
		}
	}

	/**
	 * @Route("/{id_comunicazione_risposta}/invalida_comunicazione_risposta", name="invalida_comunicazione_risposta")
	 * @ControlloAccesso(contesto="soggetto", classe="IstruttorieBundle:RispostaComunicazioneEsitoIstruttoria", opzioni={"id" = "id_comunicazione_risposta"})
	 */
	public function invalidaComunicazioneRispostaAction($id_comunicazione_risposta) {
		$this->get('base')->checkCsrf('token');
		$comunicazione_risposta = $this->getEm()->getRepository("IstruttorieBundle\Entity\RispostaComunicazioneEsitoIstruttoria")->find($id_comunicazione_risposta);
		$comunicazione = $comunicazione_risposta->getComunicazione();
		try {
			$this->getSession()->set("gestore_comunicazione_bundle", "IstruttorieBundle");
			$gestore = $this->get("gestore_comunicazione")->getGestore($this->getSession()->get("gestore_comunicazione_bundle"));

			$response = $gestore->invalidaRispostaComunicazione($comunicazione_risposta);
			return $response->getResponse();
		} catch (SfingeException $e) {
			return $this->addErrorRedirect($e->getMessage(), "dettaglio_risposta_comunicazione", array("id_comunicazione" => $comunicazione->getId()));
		} catch (\Exception $e) {
			//mettere log
			return $this->addErrorRedirect("Errore generico", "dettaglio_risposta_comunicazione", array("id_comunicazione" => $comunicazione->getId()));
		}
	}

}
