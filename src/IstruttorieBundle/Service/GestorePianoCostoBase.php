<?php

namespace IstruttorieBundle\Service;

use RichiesteBundle\Utility\EsitoValidazione;
use RichiesteBundle\Service\GestoreResponse;
use RichiesteBundle\Entity\Proponente;

class GestorePianoCostoBase extends AGestorePianoCosto {
	
	public function getIstruttoriaRichiesta($id_richiesta) {
		return $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta)->getIstruttoria();
	}
	
	public function istruttoriaPianoCostiProponente($id_proponente, $annualita){
		/** @var Proponente $proponente */
		$proponente = $this->getEm()->getRepository("RichiesteBundle:Proponente")->find($id_proponente);
		$richiesta = $proponente->getRichiesta();
		$istruttoria = $richiesta->getIstruttoria();
		$id_richiesta = $richiesta->getId();

		$request = $this->getCurrentRequest();

		$proponente->ordinaVociPianoCosto();
		$voci_piano_costo = $proponente->getVociPianoCosto();
		$this->generaIstruttorieVociPianoCosto($voci_piano_costo);
		
		$opzioni['annualita'] = $annualita;
		$opzioni['url_indietro'] = $this->generateUrl("istruttoria_piano_costi", array('id_richiesta' => $id_richiesta,'id_proponente'=> $id_proponente, 'annualita' => $annualita));
		$opzioni['disabled'] = !$this->isGranted('ROLE_ISTRUTTORE') || !is_null($istruttoria->getEsito());
		$opzioni['modalita_finanziamento_attiva'] = $richiesta->getProcedura()->getModalitaFinanziamentoAttiva();
				$form = $this->createForm("IstruttorieBundle\Form\IstruttoriaPianoCostiBaseType", $proponente, $opzioni);

		$form->handleRequest($request);
		if ($form->isSubmitted()) {
			
			$this->callbackValidazionePianoCosti($proponente, $form);
			
			if ($form->isValid()) {
				$em = $this->getEm();
				$gestore_istruttoria = $this->container->get("gestore_istruttoria")->getGestore($richiesta->getProcedura());
				$gestore_istruttoria->creaLogIstruttoria($istruttoria, "piano_costi");					
				try {
					$totaleCostoAmmesso = $istruttoria->getTotaleAmmesso();
					$istruttoria->setCostoAmmesso($totaleCostoAmmesso);
                    //serve il flush perchè alcuni bandi non prendono dal totale ma dai piano costo e se non flushi non setta il valore ammesso del voci
                    $em->flush();
                    $contributo = $this->calcolaContributoPianoCosto($istruttoria);
					$istruttoria->setContributoAmmesso($contributo);
					$em->flush();
					$this->addFlash('success', "Modifiche salvate correttamente");

					return new GestoreResponse($this->redirect($this->generateUrl("istruttoria_piano_costi", array('id_richiesta' => $id_richiesta,'id_proponente'=> $id_proponente, 'annualita' => $annualita))));
				} catch (\Exception $e) {
					$this->addFlash('error', "Errore nel salvataggio delle informazioni");
				}
			} else {
				$error = new \Symfony\Component\Form\FormError("Sono presenti valori non corretti o non validi.");
				$form->addError($error);
			}
		}

		$dati['onKeyUp'] = 'calcolaTotaleSezione';
		$dati["form"] = $form->createView();
		$dati["annualita"] = $opzioni['annualita'];
		$dati["istruttoria"] = $richiesta->getIstruttoria();
		$dati["menu"] = "piano_costi";
		$dati["proponente"] = $proponente;
		
		$titolo_aggiuntivo = "";
		if (count($richiesta->getProponentiPianoCosto()) > 1) {
			$titolo_aggiuntivo .= $proponente->getSoggettoVersion(). " / ";
		}
		
		$annualita_piano_costi = $this->container->get("gestore_piano_costo")->getGestore($richiesta->getProcedura())->getAnnualita($id_proponente);
		$titolo_aggiuntivo .= ("Annualità ".$annualita_piano_costi[$annualita]);
		
		//aggiungo il titolo della pagina e le info della breadcrumb
		$this->container->get("pagina")->setTitolo("Istruttoria Piano costi: ".$titolo_aggiuntivo);


		$response = $this->render("IstruttorieBundle:Istruttoria:pianoCosti.html.twig", $dati);

		return new GestoreResponse($response);
	}
	
	public function ordina(\Doctrine\Common\Collections\Collection $array, $oggettoInterno, $campo = null) {
		$valori = $array->getValues();
		usort($valori, function ($a, $b) use ($oggettoInterno, $campo) {
			$oggettoInterno = 'get' . $oggettoInterno;
			if ($campo) {
				$campo = 'get' . $campo;
				return $a->$oggettoInterno()->$campo() > $b->$oggettoInterno()->$campo();
			} else {
				return $a->$oggettoInterno() > $b->$oggettoInterno();
			}
		});
		return $valori;
	}

	public function generaIstruttorieVociPianoCosto($voci_piano_costo) {
		foreach ($voci_piano_costo as $voce_piano_costo) {
			if (is_null($voce_piano_costo->getIstruttoria())) {
				$istruttoria = new \IstruttorieBundle\Entity\IstruttoriaVocePianoCosto();
				$istruttoria->setVocePianoCosto($voce_piano_costo);
				$voce_piano_costo->setIstruttoria($istruttoria);
			} else {
				continue;
			}
		}
	}
	
	public function calcolaContributoPianoCosto($istruttoria_richiesta) {
		throw new \Exception("Deve essere implementato nella classe derivata");		
	}
	
	protected function totaliPianoCostoCommon($richiesta, $calcolaCotributo = false) {
		$dati = array();
		$proponente = $richiesta->getMandatario();
		$annualita_piano_costi = $this->container->get("gestore_piano_costo")->getGestore($richiesta->getProcedura())->getAnnualita($proponente->getId());
		$somme = array("presentato" => 0, "taglio" => 0);
		$totali = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->getTotaliRichiesta($richiesta->getId());
		$dati["esiste_variazione"] = false;
		foreach ($totali as $chiave => $valore) {
			if (preg_match("/^presentato/", $chiave)) {
				$somme["presentato"] += $valore;
			} elseif (preg_match("/^taglio/", $chiave)) {
				$somme["taglio"] += $valore;
			}
		}
		
		$istruttoria_richiesta = $richiesta->getIstruttoria();
		if(!is_null($istruttoria_richiesta) && $calcolaCotributo == true){
			$istruttoria_richiesta->setContributoAmmesso($this->calcolaContributoPianoCosto($istruttoria_richiesta));
		}
		
		if (!is_null($richiesta->getAttuazioneControllo())) {
			$variazione = $richiesta->getAttuazioneControllo()->getUltimaVariazioneApprovata();
			
			if (!is_null($variazione)) {
				$somme_var = array("variato" => 0, "approvato" => 0);
				$totali_var = $this->getEm()->getRepository("AttuazioneControlloBundle:VariazionePianoCosti")->getTotaliVariazione($variazione->getId());
				foreach ($totali_var as $chiave => $valore) {
					if (preg_match("/^variato/", $chiave)) {
						$somme_var["variato"] += $valore;
					} elseif (preg_match("/^approvato/", $chiave)) {
						$somme_var["approvato"] += $valore;
					}
				}

				$dati["totali_variazione"] = $totali_var;
				$dati["somme_variazione"] = $somme_var;
				$dati["annualita_piano_costi_variazione"] = $annualita_piano_costi;
				$dati["variazione"] = $variazione;
				$dati["esiste_variazione"] = true;
			}
		}

		$dati["totali"] = $totali;
		$dati["somme"] = $somme;
		$dati["annualita_piano_costi"] = $annualita_piano_costi;
		$dati["richiesta"] = $richiesta;
		$dati["menu"] = "piano_costi";

		return $dati;
	}
	
	public function totaliPianoCostiConMaggiorazione($id_richiesta) {
		$richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
		$istruttoria_richiesta = $richiesta->getIstruttoria();
		
		$dati = $this->totaliPianoCostoCommon($richiesta);
		
		$dati["maggiorazione_attiva"] = true;

		$options = array();
		$options["disabled"] = !$this->isGranted("ROLE_ISTRUTTORE");
		$form = $this->createForm("IstruttorieBundle\Form\PianoCostiTotaleMaggiorazioneType", $istruttoria_richiesta, $options);
		$dati["form"] = $form->createView();

		$request = $this->getCurrentRequest();

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				$em = $this->getEm();
				try {
					$istruttoria_richiesta->setContributoAmmesso($this->calcolaContributoPianoCosto($istruttoria_richiesta));
					
					$em->flush();
					$this->addFlash('success', "Modifiche salvate correttamente");
					return new GestoreResponse($this->redirect($this->generateUrl('totali_piano_costi', array("id_richiesta" => $id_richiesta))));

				} catch (\Exception $e) {
					$this->addFlash('error', "Errore nel salvataggio delle informazioni");
				}
			}
		}

		$response = $this->render("IstruttorieBundle:Istruttoria:totaliPianoCostiMaggiorazione.html.twig", $dati);

		return new GestoreResponse($response);
	}

	public function totaliPianoCosti($id_richiesta, $maggiorazione = false) {
		
		if($maggiorazione == true) {
			return $this->totaliPianoCostiConMaggiorazione($id_richiesta);
		}
		
		$richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
		$dati = $this->totaliPianoCostoCommon($richiesta);
		$response = $this->render("IstruttorieBundle:Istruttoria:totaliPianoCosti.html.twig", $dati);
		return new GestoreResponse($response);		
	}
	
	public function getSezioni($id_procedura) {
		return $this->getEm()->getRepository("RichiesteBundle:PianoCosto")->getSezioniDaProcedura($id_procedura);
	}

	public function getVociSpesa($id_procedura) {
		return $this->getEm()->getRepository("RichiesteBundle:PianoCosto")->getDistinctVociDaProcedura($id_procedura);
	}

	// implementare dove serve nei gestori specifici, appendendo gli errori al $form
	protected function callbackValidazionePianoCosti($proponente, $form) {
		// do nothing
	}
	
}
