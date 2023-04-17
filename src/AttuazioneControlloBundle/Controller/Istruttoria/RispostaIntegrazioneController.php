<?php

namespace AttuazioneControlloBundle\Controller\Istruttoria;

use AttuazioneControlloBundle\Service\GestoreIntegrazionePagamentoBase;
use BaseBundle\Controller\BaseController;
use BaseBundle\Exception\SfingeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use BaseBundle\Annotation\ControlloAccesso;
use BaseBundle\Entity\StatoIntegrazione;
use DocumentoBundle\Entity\TipologiaDocumento;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Exception;

/**
 * @Route("/beneficiario/pagamenti/integrazioni")
 */
class RispostaIntegrazioneController extends BaseController {

	/**
	 * @Route("/elenco_integrazioni_pagamento/{id_pagamento}", name="elenco_integrazioni_pagamento")
	 * Template("AttuazioneControlloBundle:RispostaIntegrazione:elencoIntegrazioni.html.twig")
	 * @PaginaInfo(titolo="Elenco integrazioni",sottoTitolo="mostra l'elenco delle integrazioni richieste")
	 * @Menuitem(menuAttivo = "elencoRichiesteGestione")
	 */
	public function elencoIntegrazioniAction($id_pagamento) {
		$soggettoSession = $this->getSession()->get(self::SESSIONE_SOGGETTO);
		$soggetto = $this->getEm()->getRepository("SoggettoBundle\Entity\Soggetto")->findOneById($soggettoSession->getId());
		if (is_null($soggetto)) {
			return $this->addErrorRedirect("Soggetto non valido", "home");
		}

		$pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->findOneBy(array('id' => $id_pagamento));
		$integrazioni_protocollate = array();
		foreach ($pagamento->getIntegrazioni() as $integrazione) {
			if ($integrazione->getStato() == 'INT_PROTOCOLLATA') {
				$integrazioni_protocollate[] = $integrazione;
			}
		}
		$view = $this->renderView("AttuazioneControlloBundle:RispostaIntegrazione:elencoIntegrazioni.html.twig", array("integrazioni" => $integrazioni_protocollate));
		return new \Symfony\Component\HttpFoundation\Response($view);
	}

	/**
	 * @Route("/{id_integrazione_pagamento}/dettaglio", name="dettaglio_integrazione_pagamento")
	 * @PaginaInfo(titolo="Integrazione",sottoTitolo="pagina di dettaglio per una integrazione pagamento")
	 * @Menuitem(menuAttivo = "elencoIntegrazioni")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Dettaglio integrazione")})
	 * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento", opzioni={"id" = "id_integrazione_pagamento"})
	 * @ControlloAccesso(contesto="integrazione", classe="AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento", opzioni={"id" = "id_integrazione_pagamento"}, azione=\AttuazioneControlloBundle\Security\IntegrazionePagamentoVoter::WRITE)
	 */
	public function dettaglioIntegrazioneAction($id_integrazione_pagamento) {
		$integrazione_pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento")->find($id_integrazione_pagamento);
		$risposta = $integrazione_pagamento->getRisposta();
		if (is_null($risposta) || is_null($risposta->getFirmatario())) {
			return $this->redirectToRoute("risposta_integrazione_pagamento_firmatario", array("id_integrazione_pagamento" => $id_integrazione_pagamento));
		}

		$this->getSession()->set("gestore_integrazione_pagamento_bundle", "AttuazioneControlloBundle");
		$gestore = $this->get("gestore_integrazione_pagamento")->getGestore($this->getSession()->get("gestore_integrazione_pagamento_bundle"));

		$dati = array();
		$dati["integrazione_pagamento"] = $integrazione_pagamento;
		$dati["azioni_ammesse"] = $gestore->calcolaAzioniAmmesse($integrazione_pagamento->getRisposta());
		$dati["avanzamenti"] = $gestore->gestioneBarraAvanzamento($integrazione_pagamento->getRisposta());

		return $this->render('AttuazioneControlloBundle:RispostaIntegrazione:dettaglioIntegrazione.html.twig', $dati);
	}

	/**
	 * @Route("/{id_integrazione_pagamento}/scelta_firmatario", name="risposta_integrazione_pagamento_firmatario")
	 * @PaginaInfo(titolo="Scelta firmatario",sottoTitolo="pagina per scegliere il firmatario del pagamento")
	 * @Breadcrumb(elementi={
	 * 		@ElementoBreadcrumb(testo="Dettaglio integrazione", route="dettaglio_integrazione_pagamento", parametri={"id_integrazione_pagamento"}),
	 * 		@ElementoBreadcrumb(testo="Scelta firmatario")
	 * })
	 * @Menuitem(menuAttivo = "elencoIntegrazioni")
	 * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento", opzioni={"id" = "id_integrazione_pagamento"})
	 * @ControlloAccesso(contesto="integrazione", classe="AttuazioneControlloBundle:Istruttoria\IntegrazionePagamento", opzioni={"id" = "id_integrazione_pagamento"}, azione=\AttuazioneControlloBundle\Security\IntegrazionePagamentoVoter::WRITE)
	 */
	public function sceltaFirmatarioAction($id_integrazione_pagamento) {

		try {
			$this->getSession()->set("gestore_integrazione_pagamento_bundle", "AttuazioneControlloBundle");
			$gestore = $this->get("gestore_integrazione_pagamento")->getGestore($this->getSession()->get("gestore_integrazione_pagamento_bundle"));

			$integrazione_pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento")->find($id_integrazione_pagamento);
			$richiesta = $integrazione_pagamento->getPagamento()->getRichiesta();
			$opzioni = array("form_options" => array());
			$opzioni["form_options"]["url_indietro"] = $this->generateUrl("dettaglio_integrazione_pagamento", array("id_integrazione_pagamento" => $id_integrazione_pagamento));
			$opzioni["form_options"]["firmatabili"] = $this->getEm()->getRepository("SoggettoBundle:Soggetto")->getFirmatariAmmissibili($richiesta->getSoggetto());

			$response = $gestore->sceltaFirmatario($integrazione_pagamento, $opzioni);
			return $response->getResponse();
		} catch (SfingeException $e) {
			return $this->addErrorRedirect($e->getMessage(), "dettaglio_integrazione_pagamento", array("id_integrazione_pagamento" => $id_integrazione_pagamento));
		} catch (\Exception $e) {
			//mettere log
			return $this->addErrorRedirect("Errore generico", "dettaglio_integrazione_pagamento", array("id_integrazione_pagamento" => $id_integrazione_pagamento));
		}
	}

	/**
	 * @Route("/{id_integrazione_pagamento}/nota_risposta", name="nota_risposta_integrazione_pagamento")
	 * @PaginaInfo(titolo="Nota risposta integrazione")
	 * @Menuitem(menuAttivo = "elencoIntegrazioni")
	 * @Breadcrumb(elementi={
	 * 		@ElementoBreadcrumb(testo="Dettaglio integrazione", route="dettaglio_integrazione_pagamento", parametri={"id_integrazione_pagamento"}),
	 * 		@ElementoBreadcrumb(testo="Nota risposta")
	 * })
	 * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento", opzioni={"id" = "id_integrazione_pagamento"})
	 * @ControlloAccesso(contesto="integrazione", classe="AttuazioneControlloBundle:Istruttoria\IntegrazionePagamento", opzioni={"id" = "id_integrazione_pagamento"}, azione=\AttuazioneControlloBundle\Security\IntegrazionePagamentoVoter::WRITE)
	 */
	public function notaRispostaIntegrazioneAction($id_integrazione_pagamento) {
		$integrazione_pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento")->find($id_integrazione_pagamento);

		$this->getSession()->set("gestore_integrazione_pagamento_bundle", "AttuazioneControlloBundle");
		$gestore = $this->get("gestore_integrazione_pagamento")->getGestore($this->getSession()->get("gestore_integrazione_pagamento_bundle"));

		$opzioni = array("form_options" => array());
		$opzioni["form_options"]["url_indietro"] = $this->generateUrl("dettaglio_integrazione_pagamento", array("id_integrazione_pagamento" => $id_integrazione_pagamento));
		return $gestore->notaRispostaIntegrazione($integrazione_pagamento, $opzioni)->getResponse();
	}

	/**
	 * 
	 * @Route("/elenco_documenti_int_pagamento/{id_integrazione_pagamento}", name="risposta_integrazione_elenco_documenti_pagamento")
	 * @PaginaInfo(titolo="Elenco Documenti",sottoTitolo="carica i documenti richiesti")
	 * @Menuitem(menuAttivo = "elencoIntegrazioni")
	 * @Breadcrumb(elementi={
	 * 		@ElementoBreadcrumb(testo="Dettaglio integrazione", route="dettaglio_integrazione_pagamento", parametri={"id_integrazione_pagamento"}),
	 * 		@ElementoBreadcrumb(testo="Documenti in integrazione")
	 * })
	 * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento", opzioni={"id" = "id_integrazione_pagamento"})
	 * @ControlloAccesso(contesto="integrazione", classe="AttuazioneControlloBundle:Istruttoria\IntegrazionePagamento", opzioni={"id" = "id_integrazione_pagamento"}, azione=\AttuazioneControlloBundle\Security\IntegrazionePagamentoVoter::WRITE)
	 */
	public function elencoDocumentiAction($id_integrazione_pagamento) {
		$integrazione_pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento")->find($id_integrazione_pagamento);

		if (is_null($integrazione_pagamento->getRisposta()->getFirmatario())) {
			return $this->redirectToRoute("risposta_integrazione_pagamento_firmatario", array("id_integrazione_pagamento" => $id_integrazione_pagamento));
		}

		$this->getSession()->set("gestore_integrazione_pagamento_bundle", "AttuazioneControlloBundle");
		/** @var GestoreIntegrazionePagamentoBase $gestore */
		$gestore = $this->get("gestore_integrazione_pagamento")->getGestore($this->getSession()->get("gestore_integrazione_pagamento_bundle"));

		$opzioni = array();
		$opzioni["url_corrente"] = $this->generateUrl("risposta_integrazione_elenco_documenti_pagamento", array("id_integrazione_pagamento" => $id_integrazione_pagamento));
		$opzioni["url_indietro"] = $this->generateUrl("dettaglio_integrazione_pagamento", array("id_integrazione_pagamento" => $id_integrazione_pagamento));
		$opzioni["route_cancellazione_documento"] = "risposta_integrazione_elimina_documento_int";

		$response = $gestore->elencoDocumenti($integrazione_pagamento, $opzioni);
		return $response->getResponse();
	}

	/**
	 * @Route("/elimina_documento_int/{id_documento_integrazione}", name="risposta_integrazione_elimina_documento_int")
	 */
	public function eliminaDocumentoAction($id_documento_integrazione) {
		$this->get('base')->checkCsrf('token');
		$documento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\DocumentoRispostaIntegrazionePagamento")->find($id_documento_integrazione);
		$integrazionePagamento = $documento->getRispostaIntegrazione()->getIntegrazione();

		$contestoSoggetto = $this->get('contesto')->getContestoRisorsa($integrazionePagamento, "soggetto");
		$contestoRichiesta = $this->get('contesto')->getContestoRisorsa($documento->getRispostaIntegrazione()->getIntegrazione(), "integrazione");

		$accessoConsentitoS = $this->isGranted(\SoggettoBundle\Security\SoggettoVoter::ALL, $contestoSoggetto);
		$accessoConsentitoR = $this->isGranted(\AttuazioneControlloBundle\Security\IntegrazionePagamentoVoter::WRITE, $contestoRichiesta);
		if (!$accessoConsentitoS && !$accessoConsentitoR) {
			throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException('Accesso non consentito al documento di richiesta di chiarimenti');
		}

		$this->getSession()->set("gestore_integrazione_pagamento_bundle", "AttuazioneControlloBundle");
		$gestore = $this->get("gestore_integrazione_pagamento")->getGestore($this->getSession()->get("gestore_integrazione_pagamento_bundle"));

		$response = $gestore->eliminaDocumento($id_documento_integrazione, array("url_indietro" => $this->generateUrl("risposta_integrazione_elenco_documenti_pagamento", array("id_integrazione_pagamento" => $integrazionePagamento->getId()))));

		return $response->getResponse();
	}

	/**
	 *
	 * @Route("/valida_integrazione_pagamento/{id_integrazione_pagamento}", name="valida_integrazione_pagamento")
	 * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento", opzioni={"id" = "id_integrazione_pagamento"})
	 * @ControlloAccesso(contesto="integrazione", classe="AttuazioneControlloBundle:Istruttoria\IntegrazionePagamento", opzioni={"id" = "id_integrazione_pagamento"}, azione=\AttuazioneControlloBundle\Security\IntegrazionePagamentoVoter::WRITE)
	 */
	public function validaIntegrazioneAction($id_integrazione_pagamento) {
		$this->get('base')->checkCsrf('token');
		try {
			$this->getSession()->set("gestore_integrazione_pagamento_bundle", "AttuazioneControlloBundle");
			$gestore = $this->get("gestore_integrazione_pagamento")->getGestore($this->getSession()->get("gestore_integrazione_pagamento_bundle"));

			$response = $gestore->validaIntegrazione($id_integrazione_pagamento);
			return $response->getResponse();
		} catch (SfingeException $e) {
			return $this->addErrorRedirect($e->getMessage(), "dettaglio_integrazione_pagamento", array("id_integrazione_pagamento" => $id_integrazione_pagamento));
		} catch (\Exception $e) {
			//mettere log
			return $this->addErrorRedirect("Errore generico", "dettaglio_integrazione_pagamento", array("id_integrazione_pagamento" => $id_integrazione_pagamento));
		}
	}

	/**
	 *  @Route("/{id_integrazione_pagamento}/scarica_integrazione_risposta_pag", name="scarica_integrazione_risposta_pag")
	 *  @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento", opzioni={"id" = "id_integrazione_pagamento"})
	 *  @ControlloAccesso(contesto="integrazione", classe="AttuazioneControlloBundle:Istruttoria\IntegrazionePagamento", opzioni={"id" = "id_integrazione_pagamento"}, azione=\AttuazioneControlloBundle\Security\IntegrazionePagamentoVoter::WRITE)
	 */
	public function scaricaRispostaAction($id_integrazione_pagamento) {
		$integrazione = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento")->find($id_integrazione_pagamento);
		$risposta = $integrazione->getRisposta();

		if (is_null($risposta)) {
			return $this->addErrorRedirect("Richiesta non valida", "elenco_richieste");
		}

		if (is_null($risposta->getDocumentoRisposta())) {
			return $this->addErrorRedirect("Nessun documento associato alla risposta", "dettaglio_integrazione_pagamento", array("id_integrazione_pagamento" => $id_integrazione_pagamento));
		}

		return $this->get("documenti")->scaricaDaId($risposta->getDocumentoRisposta()->getId());
	}

	/**
	 * @Route("/{id_integrazione_pagamento}/carica_integrazione_risposta_firmata_pag", name="carica_integrazione_risposta_firmata_pag")
	 * @Template("AttuazioneControlloBundle:RispostaIntegrazione:caricaRispostaFirmata.html.twig")
	 * @PaginaInfo(titolo="Carica risposta integrazione firmata",sottoTitolo="pagina per caricare la risposta a richiesta di integrazione firmata")
	 * @Breadcrumb(elementi={
	 * 		@ElementoBreadcrumb(testo="Dettaglio integrazione", route="dettaglio_integrazione_pagamento", parametri={"id_integrazione_pagamento"}),
	 * 		@ElementoBreadcrumb(testo="Carica risposta")
	 * })
	 * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento", opzioni={"id" = "id_integrazione_pagamento"})
	 * @ControlloAccesso(contesto="integrazione", classe="AttuazioneControlloBundle:Istruttoria\IntegrazionePagamento", opzioni={"id" = "id_integrazione_pagamento"}, azione=\AttuazioneControlloBundle\Security\IntegrazionePagamentoVoter::WRITE)
	 */
	public function caricaRispostaFirmataAction($id_integrazione_pagamento) {
		$em = $this->getEm();

		$request = $this->getCurrentRequest();

		$documento_file = new \DocumentoBundle\Entity\DocumentoFile();

		$risposta_integrazione = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\RispostaIntegrazionePagamento")->find($id_integrazione_pagamento);

		if (!$risposta_integrazione) {
			throw $this->createNotFoundException('Risorsa non trovata');
		}

		try {

			if (!$risposta_integrazione->getStato()->uguale(StatoIntegrazione::INT_VALIDATA)) {
				throw new SfingeException("Stato non valido per effettuare l'operazione");
			}
		} catch (SfingeException $e) {
			return $this->addErrorRedirect("Errore geenrico", "dettaglio_integrazione_pagamento", array("id_integrazione_pagamento" => $id_integrazione_pagamento));
		}

		$opzioni_form["tipo"] = TipologiaDocumento::RICHIESTA_INTEGRAZIONE_RISPOSTA_FIRMATO;
		$opzioni_form["cf_firmatario"] = $risposta_integrazione->getFirmatario()->getCodiceFiscale();
		$form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documento_file, $opzioni_form);
		$form->add("pultanti", "BaseBundle\Form\SalvaIndietroType", array("url" => $this->generateUrl("dettaglio_integrazione_pagamento", array("id_integrazione_pagamento" => $id_integrazione_pagamento))));
		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				try {
					$this->container->get("documenti")->carica($documento_file, 0);
					$risposta_integrazione->setDocumentoRispostaFirmato($documento_file);
					$this->container->get("sfinge.stati")->avanzaStato($risposta_integrazione, StatoIntegrazione::INT_FIRMATA, true);
					$em->flush();
					return $this->addSuccessRedirect("Documento caricato correttamente", "dettaglio_integrazione_pagamento", array("id_integrazione_pagamento" => $id_integrazione_pagamento));
				} catch (\Exception $e) {
					//TODO gestire cancellazione del file
					$this->addFlash('error', "Errore generico");
				}
			}
		}
		$form_view = $form->createView();

		return array("id_integrazione_pagamento" => $id_integrazione_pagamento, "form" => $form_view);
	}

	/**
	 * @Route("/{id_integrazione_pagamento}/invia_risposta_integrazione_pagamento", name="invia_risposta_integrazione_pagamento")
	 * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento", opzioni={"id" = "id_integrazione_pagamento"})
	 * @ControlloAccesso(contesto="integrazione", classe="AttuazioneControlloBundle:Istruttoria\IntegrazionePagamento", opzioni={"id" = "id_integrazione_pagamento"}, azione=\AttuazioneControlloBundle\Security\IntegrazionePagamentoVoter::WRITE)
	 */
	public function inviaRispostaAction($id_integrazione_pagamento) {
		$this->get('base')->checkCsrf('token');
		try {
			$this->getSession()->set("gestore_integrazione_pagamento_bundle", "AttuazioneControlloBundle");
			$gestore = $this->get("gestore_integrazione_pagamento")->getGestore($this->getSession()->get("gestore_integrazione_pagamento_bundle"));

			$response = $gestore->inviaRisposta($id_integrazione_pagamento);
			return $response->getResponse();
		} catch (SfingeException $e) {
			return $this->addErrorRedirect($e->getMessage(), "dettaglio_integrazione_pagamento", array("id_integrazione_pagamento" => $id_integrazione_pagamento));
		} catch (\Exception $e) {
			//mettere log
			return $this->addErrorRedirect("Errore generico", "dettaglio_integrazione_pagamento", array("id_integrazione_pagamento" => $id_integrazione_pagamento));
		}
	}

	/**
	 * @Route("/{id_integrazione_pagamento}/invalida_integrazione_pagamento", name="invalida_integrazione_pagamento")
	 * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento", opzioni={"id" = "id_integrazione_pagamento"})
	 * @ControlloAccesso(contesto="integrazione", classe="AttuazioneControlloBundle:Istruttoria\IntegrazionePagamento", opzioni={"id" = "id_integrazione_pagamento"}, azione=\AttuazioneControlloBundle\Security\IntegrazionePagamentoVoter::WRITE)
	 */
	public function invalidaIntegrazioneAction($id_integrazione_pagamento) {
		$this->get('base')->checkCsrf('token');
		try {
			$this->getSession()->set("gestore_integrazione_pagamento_bundle", "AttuazioneControlloBundle");
			$gestore = $this->get("gestore_integrazione_pagamento")->getGestore($this->getSession()->get("gestore_integrazione_pagamento_bundle"));

			$response = $gestore->invalidaIntegrazione($id_integrazione_pagamento);
			return $response->getResponse();
		} catch (SfingeException $e) {
			return $this->addErrorRedirect($e->getMessage(), "dettaglio_integrazione_pagamento", array("id_integrazione_pagamento" => $id_integrazione_pagamento));
		} catch (\Exception $e) {
			return $this->addErrorRedirect("Errore generico", "dettaglio_integrazione_pagamento", array("id_integrazione_pagamento" => $id_integrazione_pagamento));
		}
	}

	/**
	 *  @Route("/{id_integrazione_pagamento}/scarica_integrazione_risposta_firmata_pag", name="scarica_integrazione_risposta_firmata_pag")
	 *  @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento", opzioni={"id" = "id_integrazione_pagamento"})
	 *  @ControlloAccesso(contesto="integrazione", classe="AttuazioneControlloBundle:Istruttoria\IntegrazionePagamento", opzioni={"id" = "id_integrazione_pagamento"}, azione=\AttuazioneControlloBundle\Security\IntegrazionePagamentoVoter::WRITE)
	 */
	public function scaricaRispostaFirmataAction($id_integrazione_pagamento) {
		$integrazione_istruttoria = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento")->find($id_integrazione_pagamento);
		$risposta = $integrazione_istruttoria->getRisposta();

		if (is_null($risposta)) {
			return $this->addErrorRedirect("Richiesta non valida", "elenco_richieste");
		}

		if (is_null($risposta->getDocumentoRispostaFirmato())) {
			return $this->addErrorRedirect("Nessun documento associato alla risposta", "dettaglio_integrazione_pagamento", array("id_integrazione_pagamento" => $id_integrazione_pagamento));
		}

		return $this->get("documenti")->scaricaDaId($risposta->getDocumentoRispostaFirmato()->getId());
	}

    /**
     * @Route("/{id_integrazione_pagamento}/carica_documento_integrazione_pagamento_dropzone", name="carica_documento_integrazione_pagamento_dropzone")
     * @Method({"POST"})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento", opzioni={"id" = "id_integrazione_pagamento"})
     * @ControlloAccesso(contesto="integrazione", classe="AttuazioneControlloBundle:Istruttoria\IntegrazionePagamento", opzioni={"id" = "id_integrazione_pagamento"}, azione=\AttuazioneControlloBundle\Security\IntegrazionePagamentoVoter::WRITE)
     * @param Request $request
     * @param $id_integrazione_pagamento
     * @return JsonResponse
     * @throws Exception
     */
    public function caricaDocumentoDropzoneAction(Request $request, $id_integrazione_pagamento): JsonResponse
    {
        $gestore = $this->get("gestore_integrazione_pagamento")->getGestore($this->getSession()->get("gestore_integrazione_pagamento_bundle"));
        $arrayResult = $gestore->caricaDocumentoDropzone($request, $id_integrazione_pagamento);
        return new JsonResponse($arrayResult);
    }

    /**
     * @Route("/{id_integrazione_pagamento}/concat_chunks_documento_integrazione_pagamento_dropzone", name="concat_chunks_documento_integrazione_pagamento_dropzone")
     * @Method({"POST"})
     * @throws Exception
     */
    public function concatChunksDocumentoDropzoneAction(Request $request, $id_integrazione_pagamento): JsonResponse
    {
        $gestore = $this->get("gestore_integrazione_pagamento")->getGestore($this->getSession()->get("gestore_integrazione_pagamento_bundle"));
        $arrayResult = $gestore->concatChunksDocumentoDropzone($request, $id_integrazione_pagamento);
        return new JsonResponse($arrayResult);
    }
}
