<?php

namespace RichiesteBundle\Service;

use BaseBundle\Exception\SfingeException;
use Doctrine\Common\Collections\ArrayCollection;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Utility\EsitoValidazione;
use SoggettoBundle\Entity\Soggetto;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\Collection;
use RichiesteBundle\Entity\VoceFaseProcedurale;
use RichiesteBundle\Entity\Richiesta;

class GestoreFaseProceduraleBase extends AGestoreFaseProcedurale {

	public $message_data_avvio_prevista = "Voci previste: la data avvio non può essere uguale o successiva alla data conclusione";
	public $message_data_avvio_effettiva = "Voci effettive: la data avvio non può essere uguale o successiva alla data conclusione";

	public function verificaFasiDaProcedura($id_procedura, $opzioni = array()) {
		
	}

	public function ottieniFasiDaRichiestaProcedura($id_richiesta, $opzioni = array()) {
		
	}

	public function generaFaseProceduraleRichiesta($id_richiesta, $opzioni = array()) {

		$esito = new \stdClass();

		$em = $this->getEm();
		/** @var Richiesta $richiesta */
		$richiesta = $em->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
		$procedura = $richiesta->getProcedura();
		$vociFase = $em->getRepository("RichiesteBundle:FaseProcedurale")->getFasiDaProcedura($procedura->getId());
		if (count($vociFase) == 0) {
			$esito->res = false;
			$esito->messaggio = "Non sono state definite le voci per la procedura selezionata";
			return $esito;
		}

		try {
			$em->beginTransaction();
			foreach ($vociFase as $voceFase) {
				$voce = new VoceFaseProcedurale();
				$voce->setFaseProcedurale($voceFase);
				$voce->setRichiesta($richiesta);
				$richiesta->addVociFaseProcedurale($voce);
				$em->persist($voce);
			}
			$em->persist($richiesta);
			$em->flush();
			$em->commit();
			$esito->res = true;
			return $esito;
		} catch (\Exception $e) {
			$em->rollback();
			$esito->res = false;
			$esito->messaggio = "Errore generico, contattare l'assistenza tecnica";
			return $esito;
		}
	}

	public function aggiornaFaseProceduraleRichiesta($id_richiesta, $opzioni = array(), $twig = "RichiesteBundle:Richieste:faseProcedurale.html.twig", $opzioni_twig = array()) {

		$em = $this->getEm();
		$richiesta = $em->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);

		$request = $this->getCurrentRequest();

		if (!array_key_exists('url_indietro', $opzioni)) {
			$opzioni['url_indietro'] = $this->generateUrl("dettaglio_richiesta", array('id_richiesta' => $id_richiesta));
		}
		
		$voci_fase_procedurale = $this->ordina($richiesta->getVociFaseProcedurale(), 'FaseProcedurale', 'Ordinamento');
		$richiesta->setVociFaseProcedurale($voci_fase_procedurale);

		if (!array_key_exists('attiva_opzionale', $opzioni)) {
			$opzioni['attiva_opzionale'] = false;
		}
		if (!array_key_exists('labels', $opzioni)) {
			$opzioni['labels'] = array('data_avvio_prevista' => 'Avvio previsto',
				'data_conclusione_prevista' => 'Conclusione prevista',
				'data_avvio_effettivo' => 'Avvio effettivo',
				'data_conclusione_effettiva' => 'Conclusione effettiva',
				'data_approvazione' => 'Approvazione');
		}

		$opzioni['disabled'] = $this->container->get("gestore_richieste")->getGestore()->isRichiestaDisabilitata();
		
		if (!array_key_exists('type', $opzioni)) {
			$type = "RichiesteBundle\Form\FaseProceduraleBaseType";
		} else {
			$type = $opzioni['type'];
			unset($opzioni['type']);
		}

		$form = $this->createForm($type, $richiesta, $opzioni);

		if ($request->isMethod('POST')) {
			$form->handleRequest($request);
			if ($form->isValid()) {
				$em = $this->getEm();
				try {
					$em->beginTransaction();

					$em->persist($richiesta);

					$em->flush();
					$em->commit();
					$this->addFlash('success', "Modifiche salvate correttamente");

					return new GestoreResponse($this->redirect($this->generateUrl('dettaglio_richiesta', array("id_richiesta" => $id_richiesta))));
				} catch (\Exception $e) {
					$em->rollback();
					$this->addFlash('error', "Errore nel salvataggio delle informazioni");
				}
			}
		}

		$dati["form"] = $form->createView();
		$dati = array_merge($dati, $opzioni_twig);
		//aggiungo il titolo della pagina e le info della breadcrumb
		$this->container->get("pagina")->setTitolo("Stato di avanzamento progettualità");
		$this->container->get("pagina")->setSottoTitolo("");
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco richieste", $this->generateUrl("elenco_richieste"));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio richiesta", $this->generateUrl("dettaglio_richiesta", array("id_richiesta" => $id_richiesta)));
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Fasi Procedurali");

		$response = $this->render($twig, $dati);

		return new GestoreResponse($response);
	}

	/**
	 * @return EsitoValidazione
	 */
	public function validaFaseProceduraleRichiesta($id_richiesta, $opzioni = array()) {

		$esito = new EsitoValidazione();
		$esito->setEsito(true);

		$em = $this->getEm();
		$richiesta = $em->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);

		$fasi = $richiesta->getVociFaseProcedurale();
		$messaggi = array();

		if (count($fasi) == 0) {
			$esito->setEsito(false);
			$esito->addMessaggioSezione("Fase procedurale vuota");
			return $esito;
		}

        /*
		foreach ($fasi as $voce) {
			if (is_null($voce->getDataAvvioPrevista()) || is_null($voce->getDataConclusionePrevista())) {
				$esito->setEsito(false);
				$esito->addMessaggioSezione("Alcune date della sezione 'previsto' sono vuote");
				break;
			}
			if (is_null($voce->getDataAvvioEffettivo()) || is_null($voce->getDataConclusioneEffettiva())) {
				$esito->setEsito(false);
				$esito->addMessaggioSezione("Alcune date della sezione 'effettivo' sono vuote");
				break;
			}
			if (is_null($voce->getDataApprovazione())) {
				$esito->setEsito(false);
				$esito->addMessaggioSezione("Alcune date della sezione 'approvazione' sono vuote");
				break;
			}
		}
        */

		foreach ($fasi as $voce) {

			if (!is_null($voce->getDataAvvioPrevista()) && !is_null($voce->getDataConclusionePrevista())) {
				if ($voce->getDataAvvioPrevista() >= $voce->getDataConclusionePrevista()) {
					$esito->setEsito(false);
					$esito->addMessaggioSezione($this->message_data_avvio_prevista);
					break;
				}
			}
			if (!is_null($voce->getDataAvvioEffettivo()) && !is_null($voce->getDataConclusioneEffettiva())) {
				if ($voce->getDataAvvioEffettivo() >= $voce->getDataConclusioneEffettiva()) {
					$esito->setEsito(false);
					$esito->addMessaggioSezione($this->message_data_avvio_effettiva);
					break;
				}
			}
		}

		if (!$esito->getEsito()) {
			$esito->setMessaggio($messaggi);
		}

		return $esito;
	}

	public function ordina(Collection $array, $oggettoInterno, $campo = null) {
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

}
