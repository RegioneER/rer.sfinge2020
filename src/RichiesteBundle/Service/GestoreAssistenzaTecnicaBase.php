<?php

namespace RichiesteBundle\Service;

use BaseBundle\Exception\SfingeException;


class GestoreAssistenzaTecnicaBase extends GestoreProcedureParticolariBase {
	const SUFFISSO_ROUTE = '_at';
	public function nuovaRichiesta($id_richiesta, $opzioni = array()) {
		$richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
		if (is_null($richiesta)) {
			return $this->addErrorRedirect("La richiesta non è stata trovata", "seleziona_procedura_at");
		}

		$beneficiario = new \RichiesteBundle\Form\Entity\AssistenzaTecnica\AssistenzaTecnicaBeneficiario();
		$request = $this->getCurrentRequest();
		$proponenti = $richiesta->getProponenti();
		$proponente = $proponenti[0];
		$form = $this->createForm("RichiesteBundle\Form\AssistenzaTecnica\AssistenzaTecnicaBeneficiarioType", $beneficiario, $opzioni);

		if ($request->isMethod('POST')) {

			$form->handleRequest($request);
			if ($form->isValid()) {
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

					return new GestoreResponse($this->redirect($this->generateUrl('dettaglio_richiesta_at', array("id_richiesta" => $richiesta->getId()))));
				} catch (SfingeException $e) {

					$em->rollback();
					$this->addFlash('error', $e->getMessage());
				} catch (\Exception $e) {
                                    throw $e;
                                    $em->rollback();
					$this->addFlash('error', "Errore nel salvataggio delle informazioni");
				}
			}
		}

		$dati["form"] = $form->createView();
		//aggiungo il titolo della pagina e le info della breadcrumb
		$this->container->get("pagina")->setTitolo("Selezione del beneficiario");
		$this->container->get("pagina")->setSottoTitolo("pagina per selezionare il beneficiario della domanda");
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco richieste", $this->generateUrl("elenco_richieste_at"));

		$response = $this->render("RichiesteBundle:ProcedureParticolari:nuovaRichiesta.html.twig", $dati);

		return new GestoreResponse($response, "RichiesteBundle:ProcedureParticolari:nuovaRichiesta.html.twig", $dati);
	}
	
	public function getTipiDocumenti($id_richiesta, $solo_obbligatori) {
		$richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
		$procedura_id = $richiesta->getProcedura()->getId();
		$obbligatori = array();

		$res = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->ricercaDocumentiRichiesta($id_richiesta, $procedura_id, $solo_obbligatori);
		if (!$solo_obbligatori) {
			$tipologie_con_duplicati = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findBy(array("abilita_duplicati" => 1, "codice" => 'ALL_ASS_TECNICA', "tipologia" => 'attuazione'));
			$res = array_merge($res, $tipologie_con_duplicati);
		}

		return $res;
	}
	protected function getRouteNameByTipoProcedura(string $route): string{
		return $route . self::SUFFISSO_ROUTE; 
	}
}
