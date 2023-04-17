<?php

/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 21/01/16
 * Time: 17:23
 */

namespace RichiesteBundle\Service;

use BaseBundle\Controller\BaseController;
use BaseBundle\Exception\SfingeException;
use BaseBundle\Form\CommonType;
use BaseBundle\Service\BaseService;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use FascicoloBundle\Entity\Fascicolo;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Entity\Referente;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Entity\SedeOperativa;
use RichiesteBundle\Ricerche\RicercaPersonaReferente;
use RichiesteBundle\Ricerche\RicercaSoggettoProponente;
use RichiesteBundle\Utility\EsitoValidazione;
use SfingeBundle\Entity\Procedura;
use SoggettoBundle\Entity\Soggetto;
use SoggettoBundle\Form\Entity\RicercaSoggetto;
use Symfony\Component\HttpFoundation\Response;
use RichiesteBundle\Entity\DocumentoRichiesta;
use RichiesteBundle\Entity\DocumentoProponente;
use DocumentoBundle\Entity\DocumentoFile;
use RichiesteBundle\Form\ModificaFirmatarioType;
use DocumentoBundle\Component\ResponseException;
use Symfony\Component\HttpFoundation\Session\Session;
use SoggettoBundle\Entity\Azienda;
use SoggettoBundle\Entity\Sede;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GestoreProponenteBase extends BaseService implements IGestoreProponenti {

	/**
	 * @return ArrayCollection
	 */
	public function getProponenti() {
		return $this->getRichiesta()->getProponenti();
	}

	public function getSoggetto() {
		$soggetto = $this->getSession()->get(BaseController::SESSIONE_SOGGETTO);
		if (is_null($soggetto)) {
			throw new \Exception("Soggetto non specificato");
		}
		$soggetto = $this->getEm()->merge($soggetto);
		return $soggetto;
	}

	/**
	 * @return Soggetto
	 */
	public function getCapofila() {
		return $this->getSoggetto();
	}

	/**
	 * @return Procedura
	 * @throws SfingeException
	 */
	public function getProcedura($id_bando = null) {
		if( !\is_null($id_bando)){
			return $this->getEm()->getRepository('SfingeBundle:Procedura')->findOneById($id_bando);
		}
		
		$id_bando = $this->container->get("request_stack")->getCurrentRequest()->get("id_bando");
		if (is_null($id_bando)) {
			$id_richiesta = $this->container->get("request_stack")->getCurrentRequest()->get("id_richiesta");
			if (is_null($id_richiesta)) {
				throw new SfingeException("Nessun id_richiesta indicato");
			}
			$richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
			if (is_null($richiesta)) {
				throw new SfingeException("Nessuna richiesta trovata");
			}
			return $richiesta->getProcedura();
		}
		throw new SfingeException("Nessuna richiesta trovata");
	}

	/**
	 * @return Fascicolo
	 */
	public function getFascicoli() {
		$fascicoli = array();
		foreach ($this->getProcedura()->getFascicoliProcedura() as $fascioloProcedura) {
			$fascicoli[] = $fascioloProcedura->getFascicolo();
		}
		return $fascicoli;
	}

	/**
	 * @return Fascicolo
	 */
	public function getFascicoloProponente() {
		// TODO: Implement getFascicoloProponente() method.
	}

	/**
	 * @return integer
	 */
	public function numeroMaxProponenti() {
		$this->getProcedura()->getNumeroProponenti();
	}

	/**
	 * @return Richiesta
	 * @throws SfingeException
	 */
	public function getRichiesta() {
		$id_richiesta = $this->container->get("request_stack")->getCurrentRequest()->get("id_richiesta");
		if (is_null($id_richiesta)) {
			throw new SfingeException("Id richiesta non trovata");
		}
		$richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
		if (is_null($richiesta)) {
			throw new SfingeException("Richiesta non trovata");
		}

		return $richiesta;
	}

	public function getTipiDocumentiProponenti($id_richiesta, $id_proponente, $solo_obbligatori) {
		$richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
		$procedura_id = $richiesta->getProcedura()->getId();
		$res = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->ricercaDocumentiProponente($id_proponente, $procedura_id, $solo_obbligatori);
		if (!$solo_obbligatori) {
			$tipologie_con_duplicati = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findBy(array("abilita_duplicati" => 1, "procedura" => $richiesta->getProcedura(), "tipologia" => 'proponente'));
			$res = array_merge($res, $tipologie_con_duplicati);
		}
		return $res;
	}

	public function getTipiReferenzaAmmessi($id_procedura = null) {
		$tipiAmmessi = array();
		foreach ($this->getProcedura($id_procedura)->getTipiReferenza() as $tipoReferenzaProcedura) {
			$tipiAmmessi[] = $tipoReferenzaProcedura->getTipoReferenza();
		}
		return $tipiAmmessi;
	}

	public function getTipiReferenzaProcedureParticolari() {
		$tipiAmmessi = array();
		$tipiAmmessi = $this->getEm()->getRepository("RichiesteBundle\Entity\TipoReferenza")->findByCodice('RESP_ASS_TECNICA');
		return $tipiAmmessi;
	}

	public function elencoProponenti($id_richiesta, $opzioni = array()) {
		/** @var Richiesta $richiesta */
		$richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
		if (\is_null($richiesta)) {
			return new GestoreResponse($this->addErrorRedirect("Richiesta non trovata", "home"));
		}
		$procedura = $richiesta->getProcedura();
		$proponenti = $richiesta->getProponenti();
		if (\is_null($proponenti) || \count($proponenti) == 0) {
			$proponenti = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->getProponentiRichiesta($id_richiesta);
			if (is_null($proponenti) || count($proponenti) == 0) {
				return new GestoreResponse($this->addErrorRedirect("Non ci sono proponenti per questa richiesta, rivolgersi all'assistenza", "home"));
			}
		}

		//verifico se può inserire o meno un ulterire proponenti
		$isRichiestaDisabilitata = $this->container->get("gestore_richieste")->getGestore($procedura)->isRichiestaDisabilitata();
		$numProponentiPrevisti = $this->getProcedura()->getNumeroProponenti();
		$abilita_aggiungi_proponenti = false;
		if (count($proponenti) < $numProponentiPrevisti && !$isRichiestaDisabilitata) {
			$abilita_aggiungi_proponenti = true;
		}

		$has_documenti = false;
		$documenti = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findBy(array("procedura" => $richiesta->getProcedura(), "tipologia" => 'proponente'));
		if (count($documenti) > 0) {
			$has_documenti = true;
		}

		$dati = array("proponenti" => $proponenti, "id_richiesta" => $id_richiesta, "abilita_aggiungi_proponenti" => $abilita_aggiungi_proponenti, "has_documenti" => $has_documenti);

		$response = $this->render("RichiesteBundle:Richieste:elencoProponenti.html.twig", $dati);

		return new GestoreResponse($response, "RichiesteBundle:Richieste:elencoProponenti.html.twig", $dati);
	}

	/**
	 * @param $id_richiesta
	 * @param array $opzioni
	 * @return Response
	 */
	public function cercaProponente($id_richiesta, $opzioni = array()) {
		/** @var Richiesta $richiesta */
		$richiesta = $this->getEm()->getRepository('RichiesteBundle:Richiesta')->find($id_richiesta);

		$isRichiestaDisabilitata = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->isRichiestaDisabilitata();

		if ($isRichiestaDisabilitata) {
			throw new SfingeException("Impossibile effettuare questa operazione");
		}

		$soggetto = new RicercaSoggettoProponente();
		$soggetto->richiesta = $richiesta;
		$risultato = $this->container->get("ricerca")->ricerca($soggetto);

		$dati = array('soggetti' => $risultato["risultato"], "form" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"], "id_richiesta" => $id_richiesta);

		$response = $this->render("RichiesteBundle:Richieste:cercaProponente.html.twig", $dati);

		return new GestoreResponse($response, "RichiesteBundle:Richieste:cercaProponente.html.twig", $dati);
	}

	/**
	 * Consente di associare un determinato soggetto alla richiesta come proponete
	 *
	 * @param $id_richiesta
	 * @param $id_soggetto
	 * @param array $opzioni
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 * @throws SfingeException
	 */
	public function associaProponente($id_richiesta, $id_soggetto, $opzioni = array()) {
		/** @var Richiesta $richiesta */
		$richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
		if (\is_null($richiesta)) {
			throw new SfingeException("Richiesta indicata non esiste");
		}
		$isRichiestaDisabilitata = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->isRichiestaDisabilitata();

		if ($isRichiestaDisabilitata) {
			throw new SfingeException("Impossibile effettuare questa operazione");
		}


		$soggetto = $this->getEm()->getRepository("SoggettoBundle:Soggetto")->find($id_soggetto);
		if (\is_null($soggetto)) {
			throw new SfingeException("Il soggetto indicato non esiste");
		}
		

		$nProponenti = $this->getProponenti()->count();
		if ($nProponenti >= $this->getProcedura()->getNumeroProponenti()) {
			throw new SfingeException("Impossibile aggiungere un ulteriore proponente");
		}

		$proponentePresente = $richiesta->getProponenti()->filter(function(Proponente $p) use($soggetto){
			return $p->getSoggetto() == $soggetto;
		})->count() > 0;

		if($proponentePresente){
			throw new SfingeException("Il soggetto indicato è già presente nella richiesta");
		}

		$proponente = new Proponente();
		$proponente->setSoggetto($soggetto);
		$proponente->setRichiesta($richiesta);

		$fascicoli_proponente = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->getFascicoli("proponente");
		if (\count($fascicoli_proponente) > 0) {
			$fascicolo_proponente = $fascicoli_proponente[0];
			$istanzaFascicolo_proponente = new \FascicoloBundle\Entity\IstanzaFascicolo();
			$istanzaFascicolo_proponente->setFascicolo($fascicolo_proponente);
			$proponente->setIstanzaFascicolo($istanzaFascicolo_proponente);
			$indice_proponente = new \FascicoloBundle\Entity\IstanzaPagina();
			$indice_proponente->setPagina($fascicolo_proponente->getIndice());
			$istanzaFascicolo_proponente->setIndice($indice_proponente);
			$this->getEm()->persist($istanzaFascicolo_proponente);
			$this->inizializzaIstanzaFascicoloProponente($indice_proponente);
		}

		$this->getEm()->persist($proponente);
		$this->getEm()->flush();

		return new GestoreResponse($this->addSuccesRedirect("Proponente aggiunto correttamente", "elenco_proponenti", array("id_richiesta" => $richiesta->getId())));
	}

	public function rimuoviProponente($id_proponente, $opzioni = array()) {
		/** @var Proponente $proponente */
		$proponente = $this->getEm()->getRepository("RichiesteBundle:Proponente")->find($id_proponente);
		if (is_null($proponente)) {
			throw new SfingeException("Il proponente indicato non esiste");
		}

		$richiesta = $proponente->getRichiesta();
		if (\is_null($richiesta)) {
			$richiesta = $proponente->getOggettoRichiesta()->getRichiesta();
		}

		if (\is_null($richiesta)) {
			throw new SfingeException("Richiesta non trovata");
		}

		if ($proponente->getMandatario()) {
			return new GestoreResponse($this->addErrorRedirect("Il proponente mandatario non può essere rimosso", "elenco_proponenti", array("id_richiesta" => $richiesta->getId())));
		}
		//rimuovo i child
		$em = $this->getEm();
		foreach ($proponente->getReferenti() as $proponente_persona) {
			$em->remove($proponente_persona);
			$proponente->removeReferenti($proponente_persona);
		}
		foreach ($proponente->getVociPianoCosto() as $voce) {
			$em->remove($voce);
			$proponente->removeVociPianoCosto($voce);
		}

		$em->remove($proponente);
		$em->flush();
		return new GestoreResponse($this->addSuccesRedirect("Proponente rimosso correttamente", "elenco_proponenti", array("id_richiesta" => $richiesta->getId())));
	}

	public function dettagliProponente($id_proponente, $opzioni = array()) {
		$proponente = $this->getEm()->getRepository("RichiesteBundle:Proponente")->find($id_proponente);

		if (is_null($proponente)) {
			throw new SfingeException("Proponente non trovato");
		}

		$richiesta = $proponente->getRichiesta();	/** @var \RichiesteBundle\Entity\Richiesta $richiesta */
		$procedura = $richiesta->getProcedura();	/** @var \SfingeBundle\Entity\Procedura $procedura */
		$isRichiestaDisabilitata = $this->container->get("gestore_richieste")->getGestore($procedura)->isRichiestaDisabilitata();

		$abilita_aggiungi_referenti = false;
		$tipiReferenza = $this->getTipiReferenzaAmmessi($procedura->getId());
		if (count($tipiReferenza) > 0 && $proponente->getMandatario() && !$isRichiestaDisabilitata) {
			$abilita_aggiungi_referenti = true;
		}

		// SE VIENE RICHIESTO UN SINGOLO REFERENTE (è settata l'opzione e l'opzione è a true)
		// E GIA' C'E' UN REFERENTE, ALLORA DISABILITIAMO $abilita_aggiungi_referenti
		$referente_presente = !($proponente->getReferenti()->count() == 0);
		$opzioni["singolo_referente"] = isset($opzioni["singolo_referente"]) && ($opzioni["singolo_referente"] == true) && $referente_presente;

		$abilita_aggiungi_referenti = isset($opzioni['abilita_aggiungi_referenti']) ? $opzioni['abilita_aggiungi_referenti'] : $abilita_aggiungi_referenti;
		$abilita_sedi = !$isRichiestaDisabilitata;

		$opzioni['richiesta_disabilitata'] = $isRichiestaDisabilitata;
		$opzioni["proponente"] = $proponente;
		$opzioni["id_richiesta"] = $richiesta->getId();
		$opzioni["abilita_aggiungi_referenti"] = $abilita_aggiungi_referenti;
		
		if($isRichiestaDisabilitata){
			// Se la richiesta è disabilitata le sedi vanno comunque abilitate...
			$opzioni["abilita_sedi"] = false;
		} else {
			// viceversa vanno abilitate, a meno che non viene sovrascritto dall'opzione abilita_sedi
			$opzioni["abilita_sedi"] = (isset($opzioni["abilita_sedi"]) ? $opzioni["abilita_sedi"] : true );
		}
		
		if ($richiesta->getProcedura() instanceof \SfingeBundle\Entity\AssistenzaTecnica) {
			$opzioni["assistenza_tecnica"] = true;
		}

		$response = $this->render("RichiesteBundle:Richieste:dettaglioProponente.html.twig", $opzioni);

		return new GestoreResponse($response, "RichiesteBundle:Richieste:dettaglioProponente.html.twig", $opzioni);
	}

	public function modificaFirmatario($id_richiesta, $opzioni = array()) {

		$richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);

		$request = $this->getCurrentRequest();

		$opzioni["url_indietro"] = $this->generateUrl("elenco_proponenti", array("id_richiesta" => $richiesta->getId()));
		$opzioni["firmatabili"] = $this->getEm()->getRepository("SoggettoBundle:Soggetto")->getFirmatariAmmissibili($this->getCapofila());
		$opzioni["disabled"] = $this->container->get("gestore_richieste")->getGestore()->isRichiestaDisabilitata();
		$form = $this->createForm("RichiesteBundle\Form\ModificaFirmatarioType", $richiesta, $opzioni);

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				$em = $this->getEm();
				try {
					$em->beginTransaction();
					$em->persist($richiesta);

					$em->flush();
					$em->commit();

					return new GestoreResponse($this->addSuccesRedirect("Firmatario salvato correttamente", "elenco_proponenti", array("id_richiesta" => $richiesta->getId())));
				} catch (\Exception $e) {
					$em->rollback();
					throw new SfingeException("Firmatario non modificato");
				}
			}
		}

		$dati = array("id_richiesta" => $richiesta->getId(), "firmatario" => $richiesta->getFirmatario(), "form" => $form->createView());

		$response = $this->render("RichiesteBundle:Richieste:modificaFirmatario.html.twig", $dati);

		return new GestoreResponse($response, "RichiesteBundle:Richieste:modificaFirmatario.html.twig", $dati);
	}

	public function validaProponenti($id_richiesta, $opzioni = array()) {
		$esito = new EsitoValidazione(true);
		$richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
		foreach ($richiesta->getProponenti() as $proponente) {
			$esitoProponente = $this->validaProponente($proponente->getId());
			if (!$esitoProponente->getEsito()) {
				if (count($esitoProponente->getMessaggi()) > 0) {
					$esito->addMessaggioSezione("I dati inseriti per il proponente " . $proponente->getSoggetto() . " non sono completi. Selezionare la voce 'Visualizza' dal menu 'Azioni'");
					//$messaggi = $esitoProponente->getMessaggi();
					// $esito->addMessaggioSezione($messaggi[0]);
				} else {
					$esito->addMessaggioSezione('Uno o più proponenti non sono correttamente inseriti');
				}
				$esito->setEsito(false);
				break;
			}
		}
		return $esito;
	}

	public function validaProponente($id_proponente, $opzioni = array()) {
		//se il bando prevede dei referenti controllo che ci siano
		$esito = new EsitoValidazione(true);
		$proponente = $this->getEm()->getRepository("RichiesteBundle:Proponente")->find($id_proponente);	/** @var \RichiesteBundle\Entity\Proponente $proponente */
		$procedura = $proponente->getRichiesta()->getProcedura();
		$tipiReferenza = $this->getTipiReferenzaAmmessi($procedura->getId());
		$tipiInseriti = array();
		if (count($tipiReferenza) && $proponente->getMandatario()) {
			foreach ($proponente->getReferenti() as $referente) {
				$tipiInseriti[$referente->getTipoReferenza()->getCodice()] = true;
			}
			foreach ($tipiReferenza as $tipoReferenza) {
				if (!array_key_exists($tipoReferenza->getCodice(), $tipiInseriti)) {
					$esito->setEsito(false);
					$esito->addMessaggio("Per il proponente " . $proponente->getSoggetto()->getDenominazione() . " occorre indicare il referente di tipo " . $tipoReferenza->getDescrizione());
				}
			}
		}
		$esitoDocumenti = $this->validaDocumentiProponente($id_proponente);
		if (!$esitoDocumenti->getEsito()) {
			$esito->addMessaggio("Caricare tutti gli allegati previsti nella sezione allegati del proponente");
			foreach ($esitoDocumenti->getMessaggi() as $msgErrori) {
				$esito->addMessaggio($msgErrori);
			}
			$esito->setEsito(false);
		}
		return $esito;
	}

	public function cercaReferente($id_proponente, $opzioni = array()) {
		$proponente = $this->getEm()->getRepository("RichiesteBundle:Proponente")->find($id_proponente);

		if (is_null($proponente)) {
			throw new SfingeException("Proponente non trovato");
		}
		$richiesta = $proponente->getRichiesta();

		$isRichiestaDisabilitata = $this->container->get("gestore_richieste")->getGestore()->isRichiestaDisabilitata();

		if ($isRichiestaDisabilitata) {
			throw new SfingeException("Impossibile effettuare questa operazione");
		}

		$ricercaPersone = new RicercaPersonaReferente();
		$ricercaPersone->setConsentiRicercaVuota(false);
		$risultato = $this->container->get("ricerca")->ricerca($ricercaPersone);

		$dati = array('persone' => $risultato["risultato"], "form" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"], "richiesta" => $richiesta,
			"id_proponente" => $id_proponente);

		$response = $this->render("RichiesteBundle:Richieste:cercaReferente.html.twig", $dati);

		return new GestoreResponse($response, "RichiesteBundle:Richieste:cercaReferente.html.twig", $dati);
	}

	public function cercaReferenteIntervento($id_intervento, $opzioni = array()) {
		$intervento = $this->getEm()->getRepository("RichiesteBundle:Intervento")->find($id_intervento);

		if (is_null($intervento)) {
			throw new SfingeException("Intervento non trovato");
		}
		$richiesta = $intervento->getProponente()->getRichiesta();

		$isRichiestaDisabilitata = $this->container->get("gestore_richieste")->getGestore()->isRichiestaDisabilitata();

		if ($isRichiestaDisabilitata) {
			throw new SfingeException("Impossibile effettuare questa operazione");
		}

		$ricercaPersone = new RicercaPersonaReferente();
		$ricercaPersone->setConsentiRicercaVuota(false);
		$risultato = $this->container->get("ricerca")->ricerca($ricercaPersone);

		$dati = array('persone' => $risultato["risultato"], "form" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"], "richiesta" => $richiesta,
			"id_intervento" => $intervento->getId());

		$response = $this->render("RichiesteBundle:Richieste:cercaReferenteIntervento.html.twig", $dati);

		return new GestoreResponse($response, "RichiesteBundle:Richieste:cercaReferenteIntervento.html.twig", $dati);
	}

	public function cercaSedeOperativa($id_proponente, $opzioni = array()) {
		$isRichiestaDisabilitata = $this->container->get("gestore_richieste")->getGestore()->isRichiestaDisabilitata();

		if ($isRichiestaDisabilitata) {
			throw new SfingeException("Impossibile effettuare questa operazione");
		}

		$proponente = $this->getEm()->getRepository("RichiesteBundle:Proponente")->find($id_proponente);

		if (is_null($proponente)) {
			throw new SfingeException("Proponente non trovato");
		}

		$richiesta = $proponente->getRichiesta();
		$risultato = $proponente->getSoggetto()->getSedi()->filter(function(Sede $sede){
			return $sede->isAttiva();
		});
		$pubblico  = $proponente->getSoggetto() instanceof Azienda ? false : true;
		$dati = array('sedi' => $risultato, "id_richiesta" => $richiesta->getId(), "id_proponente" => $id_proponente, "soggetto" => $proponente->getSoggetto(), "pubblico" => $pubblico);
		$response = $this->render("RichiesteBundle:Richieste:cercaSedeOperativa.html.twig", $dati);
		return new GestoreResponse($response, "RichiesteBundle:Richieste:cercaSedeOperativa.html.twig", $dati);
	}

	public function inserisciSedeOperativa($id_proponente, $id_sede, $opzioni = array()) {
		$isRichiestaDisabilitata = $this->container->get("gestore_richieste")->getGestore()->isRichiestaDisabilitata();

		if ($isRichiestaDisabilitata) {
			throw new SfingeException("Impossibile effettuare questa operazione");
		}

		$em = $this->getEm();		
		$proponente = $em->getRepository("RichiesteBundle:Proponente")->find($id_proponente);
		$sede = $em->getRepository("SoggettoBundle:Sede")->find($id_sede);
		
		if (is_null($proponente)) {
			throw new SfingeException("Proponente non trovato");
		}

		if (is_null($sede)) {
			throw new SfingeException("Sede non trovata");
		}
        $richiesta = $proponente->getRichiesta();
		
		$sedeControllo = $this->getEm()->getRepository("RichiesteBundle:SedeOperativa")->findBy(array('sede' => $sede,'proponente' => $proponente));
		if (count($sedeControllo) > 0) {
			//throw new SfingeException("Impossibile effettuare questa operazione la sede è già presente");
            return new GestoreResponse($this->addErrorRedirect("Impossibile effettuare questa operazione la sede è già presente", "dettaglio_proponente", array("id_richiesta" => $richiesta->getId(), "id_proponente" => $proponente->getId())));
		}

		$sedeOperativa = new SedeOperativa();

		try {
			$sedeOperativa->setSede($sede);
			$sedeOperativa->setProponente($proponente);
			$em->persist($sedeOperativa);
			$em->flush();
			return new GestoreResponse($this->addSuccesRedirect("Sede Operativa aggiunta correttamente", "dettaglio_proponente", array("id_richiesta" => $richiesta->getId(), "id_proponente" => $proponente->getId())));
		} catch (\Exception $e) {
			throw new SfingeException("Sede non aggiunta");
		}
	}
	
	public function gestioneSedeOperativa($id_richiesta, $id_proponente, $id_sede, $opzioni = array()) {
		$richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
		$isRichiestaDisabilitata = $this->container->get("gestore_richieste")->getGestore()->isRichiestaDisabilitata();

		/*if ($isRichiestaDisabilitata) {
			throw new SfingeException("Impossibile effettuare questa operazione");
		}*/
		
		$em = $this->getEm();
		$proponente = $em->getRepository("RichiesteBundle:Proponente")->find($id_proponente);
		$sede = $em->getRepository("RichiesteBundle:SedeOperativa")->find($id_sede);
		
		$opzioni['url_indietro'] = $this->generateUrl("dettaglio_proponente", array("id_richiesta" => $richiesta->getId(), "id_proponente" => $proponente->getId()));
		$opzioni['disabled'] = $isRichiestaDisabilitata;
		
		if (is_null($proponente)) {
			throw new SfingeException("Proponente non trovato");
		}

		if (is_null($sede)) {
			throw new SfingeException("Sede non trovata");
		}
		
		if (array_key_exists('form_type', $opzioni)) {
			$type = $opzioni["form_type"];
			unset($opzioni["form_type"]);
		}
		
		if (array_key_exists('twig', $opzioni)) {
			$twig = $opzioni["twig"];
			unset($opzioni["twig"]);
		}

		$form = $this->createForm($type, $sede, $opzioni);
		$request = $this->getCurrentRequest();
		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				$em = $this->getEm();
				try {
					$em->persist($sede);
					$em->flush();

                    if (array_key_exists( 'salva_contributo', $opzioni) && $opzioni['salva_contributo']) {
                        // cerco un gestore per quel bando
                        $nomeClasse = "RichiesteBundle\GestoriPianiCosto\GestorePianoCostoBando_"
                            . $richiesta->getProcedura()->getId();
                        try {
                            $gestorePianoCostoBando = new $nomeClasse($this->container);
                            $contributo = $gestorePianoCostoBando->calcolaContributo($richiesta);
                            $richiesta->setContributoRichiesta($contributo);
                            $em->persist($richiesta);
                            $em->flush();
                        } catch (Exception $e) {
                            $this->addError("GestorePianoCostoBando_"
                                . $richiesta->getProcedura()->getId() . ' non presente.');
                        }
                    }

					return new GestoreResponse($this->addSuccesRedirect("Dati salvati correttamente", "dettaglio_proponente", array("id_richiesta" => $richiesta->getId(), "id_proponente" => $proponente->getId())));
				} catch (\Exception $e) {
					throw new SfingeException("Errore nel salvataggio");
				}
			}
		}
		
		$dati = array("id_richiesta" => $richiesta->getId(), "id_proponente" => $id_proponente, "sede" => $sede, "form" => $form->createView());

		$response = $this->render($twig, $dati);

		return new GestoreResponse($response, $twig, $dati);
	}

	public function inserisciReferente($id_proponente, $id_persona, $opzioni = array(), $twig = null) {
		/** @var \RichiesteBundle\Entity\Proponente $proponente */
		$proponente = $this->getEm()->getRepository("RichiesteBundle:Proponente")->find($id_proponente);

		if (is_null($proponente)) {
			throw new SfingeException("Proponente non trovato");
		}
		$richiesta = $proponente->getRichiesta();
		$procedura = $richiesta->getProcedura();

		$isRichiestaDisabilitata = $this->container->get("gestore_richieste")->getGestore($procedura)->isRichiestaDisabilitata();

		if ($isRichiestaDisabilitata) {
			throw new SfingeException("Impossibile effettuare questa operazione");
		}

		$persona = $this->getEm()->getRepository("AnagraficheBundle:Persona")->find($id_persona);

		$request = $this->getCurrentRequest();

		$referente = new Referente();

		if ($richiesta->isProceduraParticolare()) {
			$opzioni["tipi_referenza"] = $this->getTipiReferenzaProcedureParticolari();
		} else {
			$opzioni["tipi_referenza"] = $this->getTipiReferenzaAmmessi($procedura->getId());
		}

		$opzioni["url_indietro"] = $this->generateUrlByTipoProcedura("cerca_referente", $richiesta->getProcedura(), array("id_richiesta" => $richiesta->getId(), "id_proponente" => $proponente->getId()));

		$type = "RichiesteBundle\Form\ReferenteType";

		if (is_null($twig)) {
			$twig = "RichiesteBundle:Richieste:inserisciReferente.html.twig";
		}

		if (array_key_exists('form_type', $opzioni)) {
			$type = $opzioni["form_type"];
			unset($opzioni["form_type"]);
		}

		$form = $this->createForm($type, $referente, $opzioni);

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				$em = $this->getEm();
				try {
					$em->beginTransaction();
					$referente->setPersona($persona);
					$referente->setProponente($proponente);
					$em->persist($referente);

					$em->flush();
					$em->commit();
					$msg = "Referente aggiunto correttamente";
					return new GestoreResponse($this->addSuccesRedirectByTipoProcedura($msg, "dettaglio_proponente", $richiesta->getProcedura(), array("id_richiesta" => $richiesta->getId(), "id_proponente" => $proponente->getId())));
				} catch (\Exception $e) {
					$em->rollback();
					throw new SfingeException("Referente non aggiunto");
				}
			}
		}

		$dati = array("id_richiesta" => $richiesta->getId(), "id_proponente" => $id_proponente, "persona" => $persona, "form" => $form->createView());

		$response = $this->render($twig, $dati);

		return new GestoreResponse($response, $twig, $dati);
	}

	public function dettagliReferente($id_referente, $opzioni = array()) {
		$referente = $this->getEm()->getRepository("RichiesteBundle:Referente")->find($id_referente);

		if (is_null($referente)) {
			throw new SfingeException("Referente non trovato");
		}
		$richiesta = $referente->getProponente()->getRichiesta();

		$dati = array("referente" => $referente, "id_richiesta" => $richiesta->getId(), "id_proponente" => $referente->getProponente()->getId());
		$dati = array_merge($dati, $opzioni);

		$response = $this->render("RichiesteBundle:Richieste:dettaglioReferente.html.twig", $dati);

		return new GestoreResponse($response, "RichiesteBundle:Richieste:dettaglioReferente.html.twig", $dati);
	}

	public function rimuoviReferente($id_referente, $opzioni = array()) {
		$isRichiestaDisabilitata = $this->container->get("gestore_richieste")->getGestore()->isRichiestaDisabilitata();

		if ($isRichiestaDisabilitata) {
			throw new SfingeException("Impossibile effettuare questa operazione");
		}

		$referente = $this->getEm()->getRepository("RichiesteBundle:Referente")->find($id_referente);
		if (is_null($referente)) {
			throw new SfingeException("Il referente indicato non esiste");
		}

		$richiesta = $referente->getProponente()->getRichiesta();
		if (is_null($richiesta)) {
			$richiesta = $referente->getProponente()->getOggettoRichiesta()->getRichiesta();
		}

		if (is_null($richiesta)) {
			throw new SfingeException("Richiesta non trovata");
		}

		$this->getEm()->remove($referente);
		$this->getEm()->flush();
		return new GestoreResponse($this->addSuccesRedirect("Referente rimosso correttamente", "dettaglio_proponente", array("id_richiesta" => $richiesta->getId(), "id_proponente" => $referente->getProponente()->getId()))
		);
	}

	public function rimuoviReferenteIntervento($id_referente, $opzioni = array()) {
		$isRichiestaDisabilitata = $this->container->get("gestore_richieste")->getGestore()->isRichiestaDisabilitata();

		if ($isRichiestaDisabilitata) {
			throw new SfingeException("Impossibile effettuare questa operazione");
		}

		$referente = $this->getEm()->getRepository("RichiesteBundle:Referente")->find($id_referente);
		if (is_null($referente)) {
			throw new SfingeException("Il referente indicato non esiste");
		}

		$richiesta = $referente->getIntervento()->getProponente()->getRichiesta();

		if (is_null($richiesta)) {
			throw new SfingeException("Richiesta non trovata");
		}

		$this->getEm()->remove($referente);
		$this->getEm()->flush();
		return new GestoreResponse($this->addSuccesRedirect("Referente rimosso correttamente", "elenco_interventi", array("id_richiesta" => $richiesta->getId()))
		);
	}

	public function inserisciReferenteIntervento($id_intervento, $id_persona, $opzioni = array(), $twig = null) {
		/** @var \RichiesteBundle\Entity\Intervento $intervento */
		$intervento = $this->getEm()->getRepository("RichiesteBundle:Intervento")->find($id_intervento);

		if (is_null($intervento)) {
			throw new SfingeException("Intervento non trovato");
		}
		$richiesta = $intervento->getProponente()->getRichiesta();
		$procedura = $richiesta->getProcedura();

		$isRichiestaDisabilitata = $this->container->get("gestore_richieste")->getGestore()->isRichiestaDisabilitata();

		if ($isRichiestaDisabilitata) {
			throw new SfingeException("Impossibile effettuare questa operazione");
		}

		$persona = $this->getEm()->getRepository("AnagraficheBundle:Persona")->find($id_persona);

		$request = $this->getCurrentRequest();

		$referente = new Referente();

		if ($richiesta->isProceduraParticolare()) {
			$opzioni["tipi_referenza"] = $this->getTipiReferenzaProcedureParticolari();
		} else {
			$opzioni["tipi_referenza"] = $this->getTipiReferenzaAmmessi($procedura->getId());
		}

		$opzioni["url_indietro"] = $this->generateUrlByTipoProcedura("cerca_referente_intervento", $richiesta->getProcedura(), array("id_richiesta" => $richiesta->getId(), "id_intervento" => $intervento->getId()));

		$type = "RichiesteBundle\Form\ReferenteType";

		if (is_null($twig)) {
			$twig = "RichiesteBundle:Richieste:inserisciReferente.html.twig";
		}

		if (array_key_exists('form_type', $opzioni)) {
			$type = $opzioni["form_type"];
			unset($opzioni["form_type"]);
		}

		$form = $this->createForm($type, $referente, $opzioni);

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				$em = $this->getEm();
				try {
					$em->beginTransaction();
					$referente->setPersona($persona);
					$referente->setIntervento($intervento);
					$em->persist($referente);

					$em->flush();
					$em->commit();
					$msg = "Referente aggiunto correttamente";
					return new GestoreResponse($this->addSuccesRedirectByTipoProcedura($msg, "elenco_interventi", $richiesta->getProcedura(), array("id_richiesta" => $richiesta->getId())));
				} catch (\Exception $e) {
					$em->rollback();
					throw new SfingeException("Referente non aggiunto");
				}
			}
		}

		$dati = array("id_richiesta" => $richiesta->getId(), "id_proponente" => $intervento->getProponente()->getId(), "persona" => $persona, "form" => $form->createView());

		$response = $this->render($twig, $dati);

		return new GestoreResponse($response, $twig, $dati);
	}

	public function dettagliReferenteIntervento($id_referente, $opzioni = array()) {
		$referente = $this->getEm()->getRepository("RichiesteBundle:Referente")->find($id_referente);

		if (is_null($referente)) {
			throw new SfingeException("Referente non trovato");
		}
		$richiesta = $referente->getIntervento()->getProponente()->getRichiesta();

		$dati = array("referente" => $referente, "id_richiesta" => $richiesta->getId(), "id_intervento" => $referente->getIntervento()->getId());
		$dati = array_merge($dati, $opzioni);

		$response = $this->render("RichiesteBundle:Richieste:dettaglioReferenteIntervento.html.twig", $dati);

		return new GestoreResponse($response, "RichiesteBundle:Richieste:dettaglioReferenteIntervento.html.twig", $dati);
	}

	public function rimuoviSedeOperativa($id_proponente, $id_sede, $opzioni = array()) {
		/** @var Proponente $proponente */
		$proponente = $this->getEm()->getRepository("RichiesteBundle:Proponente")->find($id_proponente);
		$sedeOperativa = $this->getEm()->getRepository("RichiesteBundle:SedeOperativa")->findOneBy(array("proponente" => $id_proponente, "sede" => $id_sede));
		$richiesta = $proponente->getRichiesta();
		
		$isRichiestaDisabilitata = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->isRichiestaDisabilitata();
		if($isRichiestaDisabilitata){
			throw new SfingeException("Non è possibile rimuovere la sede operativa");
		}

		if (is_null($sedeOperativa)) {
			throw new SfingeException("Sede Operativa non trovata");
		}
		if(count($sedeOperativa->getInterventiSede()) > 0) {
			foreach ($sedeOperativa->getInterventiSede() as $intervento) {
				$this->getEm()->remove($intervento);
			}
		}
		$this->getEm()->remove($sedeOperativa);
		$this->getEm()->flush();
		return new GestoreResponse($this->addSuccesRedirect("Sede operativa rimossa correttamente", "dettaglio_proponente", array("id_richiesta" => $richiesta->getId(), "id_proponente" => $proponente->getId()))
		);
	}

	public function elencoDocumentiProponente($id_richiesta, $id_proponente, $opzioni = array()) {

		$em = $this->getEm();
		$request = $this->getCurrentRequest();

		$documento_proponente = new DocumentoProponente();
		$documento_file = new DocumentoFile();

		$proponente = $em->getRepository("RichiesteBundle\Entity\Proponente")->find($id_proponente);
		$richiesta = $em->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
		$listaTipi = $this->getTipiDocumentiProponenti($id_richiesta, $id_proponente, 0);
		$opzioni_form["lista_tipi"] = $listaTipi;
		
		if(!is_null($richiesta->getFirmatario())){
			$opzioni_form["cf_firmatario"] = $richiesta->getFirmatario()->getCodiceFiscale();
		}
		
		$documenti_caricati = $em->getRepository("RichiesteBundle\Entity\DocumentoProponente")->findDocumentiCaricati($id_proponente);

		$isRichiestaDisabilitata = $this->container->get("gestore_richieste")->getGestore()->isRichiestaDisabilitata();

		if (count($listaTipi) > 0 && !$isRichiestaDisabilitata) {
			$form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documento_file, $opzioni_form);
			$form->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('label' => 'Salva'));
			if ($request->isMethod('POST')) {
				$form->handleRequest($request);
				if ($form->isValid()) {
					try {
						$this->container->get("documenti")->carica($documento_file, 0, $richiesta);

						$documento_proponente->setDocumentoFile($documento_file);
						$documento_proponente->setProponente($proponente);
						$em->persist($documento_proponente);

						$em->flush();
						return new GestoreResponse($this->addSuccesRedirect("Documento caricato correttamente", "elenco_documenti_proponente", array("id_richiesta" => $id_richiesta, 'id_proponente' => $id_proponente))
						);
					} catch (ResponseException $e) {
						$this->addFlash('error', $e->getMessage());
					}
				}
			}
			$form_view = $form->createView();
		} else {
			$form_view = null;
		}

		$dati = array("documenti" => $documenti_caricati, "id_richiesta" => $id_richiesta, "form" => $form_view, "id_proponente" => $id_proponente, 'is_richiesta_disabilitata' => $isRichiestaDisabilitata);
		$response = $this->render("RichiesteBundle:Richieste:elencoDocumentiProponente.html.twig", $dati);

		return new GestoreResponse($response, "RichiesteBundle:Richieste:elencoDocumentiProponente.html.twig", $dati);
	}

	public function caricaDocumentoProponente($id_proponente, $opzioni = array()) {
		// TODO: Implement caricaDocumentoProponente() method.
	}

	public function eliminaDocumentoProponente($id_documento_proponente, $opzioni = array()) {

		$em = $this->getEm();
		$documento = $em->getRepository("RichiesteBundle\Entity\DocumentoProponente")->find($id_documento_proponente);
		$id_proponente = $documento->getProponente()->getId();
		$id_richiesta = $documento->getProponente()->getRichiesta()->getId();
		try {
			// $this->container->get("documenti")->cancella($documento->getDocumentoFile(), 0);
			$em->remove($documento->getDocumentoFile());
			$em->remove($documento);
			$em->flush();
			return new GestoreResponse($this->addSuccesRedirect("Documento eliminato correttamente", "elenco_documenti_proponente", array("id_richiesta" => $id_richiesta, 'id_proponente' => $id_proponente))
			);
		} catch (ResponseException $e) {
			$this->addFlash('error', $e->getMessage());
		}
	}

	public function validaDocumentiProponente($id_proponente, $opzioni = array()) {
		$em = $this->getEm();
		$esito = new EsitoValidazione(true);
		$proponente = $em->getRepository("RichiesteBundle\Entity\Proponente")->find($id_proponente);
		$id_richiesta = $proponente->getRichiesta()->getId();
		$documenti_caricati = $em->getRepository("RichiesteBundle\Entity\DocumentoProponente")->findDocumentiCaricati($proponente->getId());
		$documenti_obbligatori = $this->getTipiDocumentiProponenti($id_richiesta, $proponente->getId(), 1);

		$tipi_documenti_caricati = array();

		foreach ($documenti_caricati as $documento_caricato) {
			$tipi_documenti_caricati[] = $documento_caricato->getDocumentoFile()->getTipologiaDocumento()->getId();
		}

		if (!is_null($documenti_obbligatori)) {
			foreach ($documenti_obbligatori as $documento) {
				if (!in_array($documento->getId(), $tipi_documenti_caricati)) {
					$esito->setEsito(false);
					$esito->addMessaggio('Caricare il documento ' . $documento->getDescrizione());
				}
			}
		}
		return $esito;
	}

	public function generateUrlByTipoProcedura($route, $procedura, $params = array()) {
		if ($procedura->isAssistenzaTecnica() == true) {
			$route = $route . "_at";
		}

		if ($procedura->isIngegneriaFinanziaria() == true) {
			$route = $route . "_ing_fin";
		}
		
		if ($procedura->isAcquisizioni() == true) {
			$route = $route . "_acquisizioni";
		}

		return $this->generateUrl($route, $params);
	}

	public function addSuccesRedirectByTipoProcedura($msg, $route, $procedura, $params = array()) {
		if ($procedura->isAssistenzaTecnica() == true) {
			$route = $route . "_at";
		}

		if ($procedura->isIngegneriaFinanziaria() == true) {
			$route = $route . "_ing_fin";
		}
		
		if ($procedura->isAcquisizioni() == true) {
			$route = $route . "_acquisizioni";
		}
		return $this->addSuccesRedirect($msg, $route, $params);
	}

	public function addErrorRedirectByTipoProcedura($msg, $route, $procedura, $params = array()) {
		if ($procedura->isAssistenzaTecnica() == true) {
			$route = $route . "_at";
		}

		if ($procedura->isIngegneriaFinanziaria() == true) {
			$route = $route . "_ing_fin";
		}
		
		if ($procedura->isAcquisizioni() == true) {
			$route = $route . "_acquisizioni";
		}
		return $this->addErrorRedirect($msg, $route, $params);
	}

	public function inizializzaIstanzaFascicoloProponente($istanza_pagina_indice) {
        
    }

    public function dettaglioProfessionista($id_richiesta, $id_proponente) {
        throw new SfingeException("Risorsa non trovata");
    }
    
    public function esisteReferente($codiceTipo, $proponente) {
        foreach ($proponente->getReferenti() as $referente) {
            if($referente->getTipoReferenza()->getCodice() == $codiceTipo) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $id_proponente
     * @return GestoreResponse
     * @throws Exception
     */
    public function inserisciDimensioneImpresa($id_proponente) {
        $em = $this->getEm();
        $proponente = $em->getRepository('RichiesteBundle:Proponente')->find($id_proponente);
        /** @var Richiesta $richiesta */
        $richiesta = $proponente->getRichiesta();
        $opzioni['disabled'] = $this->container->get('gestore_richieste')->getGestore()->isRichiestaDisabilitata();
        
        $formBuilder = $this->createFormBuilder(null, $opzioni);
        $formBuilder->add('dimensione_impresa', CommonType::entity, [
            'required' => true,
            'label' => 'Dimensione impresa',
            'class' => 'SoggettoBundle\Entity\DimensioneImpresa',
            'choices' => $em->getRepository('SoggettoBundle\Entity\DimensioneImpresa')->findAll(),
        ]);
        $formBuilder->add('submit', CommonType::salva_indietro, [
            'url' => $this->generateUrl("dettaglio_proponente",
                ['id_richiesta' => $richiesta->getId(), 'id_proponente' => $id_proponente])
        ]);

        $form = $formBuilder->getForm();
        $request = $this->getCurrentRequest();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                try {
                    /** @var Soggetto $soggetto */
                    $soggetto = $proponente->getSoggetto();
                    // Vado ad impostare il valore anche nel soggetto per i futuri bandi
                    $soggetto->setDimensioneImpresa($form->getData()['dimensione_impresa']);
                    
                    $proponente->setDimensioneImpresa($form->getData()['dimensione_impresa']);
                   
                    $em->persist($soggetto);
                    $em->persist($proponente);
                    $em->flush();
                } catch (Exception $e) {
                    $this->addError('Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l\'assistenza.');
                }
                
                $response = $this->addSuccesRedirect('Dati correttamente salvati', 'dettaglio_proponente',
                    ['id_richiesta' => $richiesta->getId(), 'id_proponente' => $id_proponente]);
                return new GestoreResponse($response);
            }
        }
        
        $dati = array('form' => $form->createView());
        $response = $this->render('RichiesteBundle:Richieste:modificaDimensioneImpresa.html.twig', $dati);
        return new GestoreResponse($response, 'RichiesteBundle:Richieste:modificaDimensioneImpresa.html.twig', $dati);
    }

    /**
     * @param $id_richiesta
     * @param $id_proponente
     * @return GestoreResponse
     * @throws Exception
     */
    public function elencoReferentiProponente($id_richiesta, $id_proponente) {
        /** @var Proponente $proponente */
        $proponente = $this->getEm()->getRepository("RichiesteBundle:Proponente")->find($id_proponente);
        if (\is_null($proponente)) {
            return new GestoreResponse($this->addErrorRedirect("Proponente non trovato", "home"));
        }

        $isRichiestaDisabilitata = $this->container->get("gestore_richieste")->getGestore($proponente->getRichiesta()->getProcedura())->isRichiestaDisabilitata();
        $referenti = $proponente->getReferenti()->count();
        
        $dati = [
            'proponente' => $proponente, 
            'id_richiesta' => $id_richiesta, 
            'aggiungi_referente' => $referenti == 0 ? true : false,
            'is_disabilitata' => $isRichiestaDisabilitata,
        ];
        $response = $this->render('RichiesteBundle:Richieste:elencoReferenti.html.twig', $dati);
        return new GestoreResponse($response, 'RichiesteBundle:Richieste:elencoReferenti.html.twig', $dati);
    }

    /**
     * @param $id_richiesta
     * @param $id_proponente
     * @return GestoreResponse
     * @throws Exception
     */
    public function elencoSediProponente($id_richiesta, $id_proponente) {
        /** @var Proponente $proponente */
        $proponente = $this->getEm()->getRepository("RichiesteBundle:Proponente")->find($id_proponente);
        if (\is_null($proponente)) {
            return new GestoreResponse($this->addErrorRedirect("Proponente non trovato", "home"));
        }

        $isRichiestaDisabilitata = $this->container->get("gestore_richieste")->getGestore($proponente->getRichiesta()->getProcedura())->isRichiestaDisabilitata();
        $sedi = $proponente->getSedi()->count();
        $dati = [
            'proponente' => $proponente,
            'id_richiesta' => $id_richiesta,
            'aggiungi_sede' => $sedi == 0 ? true : false,
            'is_disabilitata' => $isRichiestaDisabilitata,
        ];
        $response = $this->render('RichiesteBundle:Richieste:elencoSedi.html.twig', $dati);
        return new GestoreResponse($response, 'RichiesteBundle:Richieste:elencoSedi.html.twig', $dati);
    }
}
