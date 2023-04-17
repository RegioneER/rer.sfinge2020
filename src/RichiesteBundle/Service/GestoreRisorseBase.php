<?php

namespace RichiesteBundle\Service;

use BaseBundle\Exception\SfingeException;
use RichiesteBundle\Utility\EsitoValidazione;
use RichiesteBundle\Entity\RisorsaProgetto;

class GestoreRisorseBase extends AGestoreRisorse {

	public function elencoRisorse($id_richiesta, $tipo, $opzioni = array()) {
		$richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
		$opzioni["url_indietro"] = $this->generateUrl("dettaglio_richiesta", array("id_richiesta" => $id_richiesta));
		$opzioni["disabled"] = $this->isRichiestaDisabilitata($richiesta->getProcedura());
		$opzioni["risorse"] = $richiesta->getRisorseProgetto();
		$opzioni["richiesta"] = $richiesta;
		if (array_key_exists('twig', $opzioni)) {
			$twig = $opzioni['twig'];
		} else {
			$twig = "RichiesteBundle:Richieste:elencoRisorseProgetto.html.twig";
		}
		$response = $this->render($twig, $opzioni);
		return new GestoreResponse($response, $twig, $opzioni);
	}

	public function AggiungiRisorsa($id_richiesta, $opzioni = array()) {
		$richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);

		$request = $this->getCurrentRequest();

		$opzioni["disabled"] = $this->isRichiestaDisabilitata($richiesta->getProcedura());
		$opzioni["url_indietro"] = $this->generateUrl("elenco_risorse_progetto", array("id_richiesta" => $id_richiesta, "tipo" => 'default'));
		$opzioni["procedura"] = $richiesta->getProcedura();

		$risorsaProgetto = new RisorsaProgetto();

		if (array_key_exists('form_type', $opzioni)) {
			/* Dovevo definire un'altra variabile array ma sinceramente per un campo mi pare pena 
			 * quindi uso la opzioni già definito e semmai si rientrasse in questo if faccio l'unset
			 * per non fare incazzare il required del form
			 */
			$class_type = $opzioni['form_type'];
			unset($opzioni['form_type']);
			$form = $this->createForm($class_type, $risorsaProgetto, $opzioni);
		} else {
			$form = $this->createForm("RichiesteBundle\Form\RisorsaProgettoType", $risorsaProgetto, $opzioni);
		}

		$risorsaProgetto->setRichiesta($richiesta);

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			$tipo = $risorsaProgetto->getTipologiaRisorsa()->getCodice();
			$esitoControllo = $this->isPossibileAggiungereRisorsa($richiesta, $risorsaProgetto->getTipologiaRisorsa()->getCodice());
			if ($esitoControllo->getEsito() == false) {
				$messaggi = $esitoControllo->getMessaggiSezione();
				$form->get('tipologia_risorsa')->addError(new \Symfony\Component\Form\FormError($messaggi[0]));
			}
			if ($form->isValid()) {
				$em = $this->getEm();

				try {

					$em->beginTransaction();
					$em->persist($risorsaProgetto);

					$em->flush();
					$em->commit();

					return new GestoreResponse($this->addSuccesRedirect("Dati salvati correttamente", "gestione_risorsa_progetto", 
							array("id_richiesta" => $richiesta->getId(), "id_risorsa" => $risorsaProgetto->getId(), "tipo" => $tipo)));
				} catch (\Exception $e) {
					$em->rollback();
					throw new SfingeException("Elemento non salvato" . $e->getMessage());
				}
			}
		}

		$form_params["form"] = $form->createView();
		$form_params["risorsaProgetto"] = $risorsaProgetto;

		if (array_key_exists('twig', $opzioni)) {
			$twig = $opzioni['form_type'];
			unset($opzioni['twig']);
		} else {
			$twig = "RichiesteBundle:Richieste:aggiungiRisorsaProgetto.html.twig";
		}

		$response = $this->render($twig, $form_params);
		return new GestoreResponse($response, $twig, $form_params);
	}

	public function gestioneRisorsa($id_risorsa, $id_richiesta, $tipo, $opzioni = array()) {
		throw new SfingeException("Deve essere implementato nella classe derivata");
	}

	public function validaRisorsa($id_risorsa, $id_richiesta) {
		throw new SfingeException("Deve essere implementato nella classe derivata");
	}
	
	public function validaRisorseProgetto($id_richiesta) {
		throw new SfingeException("Deve essere implementato nella classe derivata");
	}

	public function cancellaRisorsa($id_risorsa, $id_richiesta) {
		
	}

	public function isRichiestaDisabilitata($procedura) {
		return $this->container->get("gestore_richieste")->getGestore($procedura)->isRichiestaDisabilitata();
	}

	public function isPossibileAggiungereRisorsa($richiesta, $tipo) {
		$esito = new EsitoValidazione(true);
		return $esito;
	}

}
