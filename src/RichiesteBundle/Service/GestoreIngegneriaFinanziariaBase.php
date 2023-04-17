<?php

namespace RichiesteBundle\Service;

use BaseBundle\Exception\SfingeException;


class GestoreIngegneriaFinanziariaBase extends GestoreProcedureParticolariBase {
	const SUFFISSO_ROUTE = '_ing_fin';

	public function nuovaRichiesta($id_richiesta, $opzioni = array()) {
		$richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
		if (is_null($richiesta)) {
			return $this->addErrorRedirect("La richiesta non è stata trovata", "seleziona_procedura");
		}

		$beneficiario = new \RichiesteBundle\Form\Entity\IngegneriaFinanziaria\IngegneriaFinanziariaBeneficiario();
		$proponenti = $richiesta->getProponenti();
		$proponente = $proponenti[0];
		$form = $this->createForm("RichiesteBundle\Form\IngegneriaFinanziaria\IngegneriaFinanziariaBeneficiarioType", $beneficiario, $opzioni);
		
		$request = $this->getCurrentRequest();
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {

				$em = $this->getEm();
				try {
					$em->beginTransaction();
					$proponente->setSoggetto($beneficiario->getBeneficiario());

					$em->persist($proponente);
					$em->persist($richiesta);

					$em->flush();
					

					if ($this->container->getParameter("stacca_protocollo_al_volo")) {
						$this->container->get("docerinitprotocollazione")->setTabProtocollazione($richiesta->getId(), 'FINANZIAMENTO');
					}
					$this->getGestoreIterProgetto($richiesta)->aggiungiFasiProcedurali();
					$gestore_istruttoria = $this->container->get("gestore_istruttoria")->getGestore($richiesta->getProcedura());
					$gestore_istruttoria->aggiornaIstruttoriaRichiesta($richiesta->getId());
					$gestore_istruttoria->avanzamentoATC($richiesta->getId());

					$em->flush();

					$em->commit();
					$this->addFlash('success', "Modifiche salvate correttamente");

					return new GestoreResponse($this->redirect($this->generateUrl('dettaglio_richiesta_ing_fin', array("id_richiesta" => $richiesta->getId()))));
				} catch (SfingeException $e) {

					$em->rollback();
					$this->addFlash('error', $e->getMessage());
				} catch (\Exception $e) {

					$em->rollback();
					$this->addFlash('error', "Errore nel salvataggio delle informazioni");
			}
		}

		$dati["form"] = $form->createView();
		//aggiungo il titolo della pagina e le info della breadcrumb
		$this->container->get("pagina")->setTitolo("Selezione del beneficiario");
		$this->container->get("pagina")->setSottoTitolo("pagina per selezionare il beneficiario della domanda");
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco richieste", $this->generateUrl("seleziona_procedura_ing_fin"));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Seleziona bando", $this->generateUrl("seleziona_procedura_ing_fin"));

		$response = $this->render("RichiesteBundle:ProcedureParticolari:nuovaRichiesta.html.twig", $dati);

		return new GestoreResponse($response, "RichiesteBundle:ProcedureParticolari:nuovaRichiesta.html.twig", $dati);
	}
	
	public function getTipiDocumenti($id_richiesta, $solo_obbligatori) {
		$richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
		$procedura_id = $richiesta->getProcedura()->getId();
		$obbligatori = array();
		$res = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->ricercaDocumentiRichiesta($id_richiesta, $procedura_id, $solo_obbligatori);
		if (!$solo_obbligatori) {
			$tipologie_con_duplicati = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findBy(array("abilita_duplicati" => 1, "tipologia" => 'ing_finanziaria'));
			$res = array_merge($res, $tipologie_con_duplicati);
		}

		return $res;
	}
	
	public function datiTrasferimentoFondo($id_richiesta){
		$richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
		$atto = $richiesta->getAtti()->first();
		$options = array();
		$options['procedura_id'] = $richiesta->getProcedura()->getId();
		$options['url_indietro'] = $this->generateUrl("dettaglio_richiesta", array('id_richiesta' => $id_richiesta));

		$data = new \stdClass();
		$data->atto = $atto;
		
		$form = $this->createForm(new \RichiesteBundle\Form\IngegneriaFinanziaria\DatiTrasferimentoType(), $data, $options);
		$request = $this->getCurrentRequest();
		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				
				$richiesta->setAtti(array($data->atto));
				try{
					$em = $this->getEm();
					$em->flush();
					$this->addFlash('success', "Modifiche salvate correttamente");					
				}catch(\Exception $e){
					$this->addFlash('error', "Errore nel salvataggio delle informazioni");
				}					
				
			}
		}
		
		$dati = array('form' => $form->createView());
		
		$response = $this->render("RichiesteBundle:ProcedureParticolari:datiTrasferimentoFondo.html.twig", $dati);	
		
		return new GestoreResponse($response, "RichiesteBundle:ProcedureParticolari:datiTrasferimentoFondo.html.twig", $dati);			
	}
	
	public function controllaValiditaRichiesta($id_richiesta, $opzioni = array()) {

		//viene anche usato nell'elenco richieste quindi inietto il parametro id_richiesta
		$this->container->get("request_stack")->getCurrentRequest()->attributes->set("id_richiesta", $id_richiesta);
		$richiesta = $this->getRichiesta();

		$esitiSezioni = array();
		$esitiSezioni[] = $this->validaDatiProgetto($id_richiesta);
		$esitiSezioni[] = $this->container->get("gestore_proponenti")->getGestore($richiesta->getProcedura())->validaProponenti($id_richiesta);
		$esitiSezioni[] = $this->validaDatiTrasferimentoFondo($id_richiesta);
		$esitiSezioni[] = $this->container->get("gestore_pagamenti")->getGestore($richiesta->getProcedura())->validaPagamenti($richiesta);
		if ($richiesta->getProcedura()->getPianoCostoAttivo()) {
			$esitiSezioni[] = $this->container->get("gestore_piano_costo")->getGestore($richiesta->getProcedura())->validaPianoDeiCosti($id_richiesta);
		}
		if ($richiesta->getProcedura()->getFasiProcedurali()) {
			$esitiSezioni[] = $this->container->get("gestore_fase_procedurale")->getGestore($richiesta->getProcedura())->validaFaseProceduraleRichiesta($id_richiesta);
		}

		$esito = true;
		$messaggi = array();
		$messaggiSezione = array();
		foreach ($esitiSezioni as $esitoSezione) {
			$esito &= $esitoSezione->getEsito();
			$messaggi = array_merge_recursive($messaggi, $esitoSezione->getMessaggi());
			$messaggiSezione = array_merge_recursive($messaggiSezione, $esitoSezione->getMessaggiSezione());
		}


		$esito = new \RichiesteBundle\Utility\EsitoValidazione($esito, $messaggi, $messaggiSezione);
		return $esito;
	}

	protected function getRouteNameByTipoProcedura(string $route): string{
		return $route . self::SUFFISSO_ROUTE; 
	}
}
