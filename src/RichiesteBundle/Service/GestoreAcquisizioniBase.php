<?php

namespace RichiesteBundle\Service;

use BaseBundle\Exception\SfingeException;
use RichiesteBundle\Form\Entity\Acquisizioni\Beneficiario;
use RichiesteBundle\Form\Acquisizioni\BeneficiarioType;
use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\HttpFoundation\Response;
use MonitoraggioBundle\Service\IGestoreIterProgetto;


class GestoreAcquisizioniBase extends GestoreProcedureParticolariBase {
	const SUFFISSO_ROUTE = '_acquisizioni';

	public function nuovaRichiesta($id_richiesta, $opzioni = array()) {
		/** @var Richiesta $richiesta */
		$richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
		if (is_null($richiesta)) {
			return $this->addErrorRedirect("La richiesta non è stata trovata", "seleziona_procedura_acquisizioni");
		}

		$beneficiario = new Beneficiario();
		$request = $this->getCurrentRequest();
		$proponenti = $richiesta->getProponenti();
		$proponente = $proponenti[0];
		$form = $this->createForm(BeneficiarioType::class, $beneficiario, $opzioni);
		$form->handleRequest($request);

		if ($request->isMethod('POST') && $form->isValid()) {
			$em = $this->getEm();
			try {
				$em->beginTransaction();
				$proponente->setSoggetto($beneficiario->getBeneficiario());

				$em->persist($proponente);
				$em->persist($richiesta);
                $this->container->get('monitoraggio.iter_progetto')->getIstanza($richiesta)->aggiungiFasiProcedurali();
				$em->flush();

				if ($this->container->getParameter("stacca_protocollo_al_volo")) {
					$this->container->get("docerinitprotocollazione")->setTabProtocollazione($richiesta->getId(), 'FINANZIAMENTO');
				}

				$gestore_istruttoria = $this->container->get("gestore_istruttoria")->getGestore();
				$gestore_istruttoria->aggiornaIstruttoriaRichiesta($richiesta->getId());
				$gestore_istruttoria->avanzamentoATC($richiesta->getId());

				$em->flush();
				$em->commit();
				$this->addFlash('success', "Modifiche salvate correttamente");

				return new GestoreResponse($this->redirect($this->getUrlDettaglio($richiesta)));
			} catch (SfingeException $e) {

				$em->rollback();
				$this->addFlash('error', $e->getMessage());
			} catch (\Exception $e) {
				throw $e;
				$em->rollback();
				$this->addFlash('error', "Errore nel salvataggio delle informazioni");
			}
		}

		$dati["form"] = $form->createView();
		//aggiungo il titolo della pagina e le info della breadcrumb
		$this->container->get("pagina")->setTitolo("Selezione del beneficiario");
		$this->container->get("pagina")->setSottoTitolo("pagina per selezionare il beneficiario della domanda");
		$this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco richieste", $this->generateUrl("elenco_richieste_acquisizioni"));

		$response = $this->render("RichiesteBundle:ProcedureParticolari:nuovaRichiesta.html.twig", $dati);

		return new GestoreResponse($response, "RichiesteBundle:ProcedureParticolari:nuovaRichiesta.html.twig", $dati);
	}
	
	public function getTipiDocumenti($id_richiesta, $solo_obbligatori) {
		$richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
		$procedura_id = $richiesta->getProcedura()->getId();
		$obbligatori = array();

		$res = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->ricercaDocumentiRichiesta($id_richiesta, $procedura_id, $solo_obbligatori);
		if (!$solo_obbligatori) {
			$tipologie_con_duplicati = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findBy(array("abilita_duplicati" => 1, "codice" => 'ALL_ACQUISIZIONI', "tipologia" => 'attuazione'));
			$res = array_merge($res, $tipologie_con_duplicati);
		}

		return $res;
	}

	public function gestioneImpegni(Richiesta $richiesta) {
		$impegni = $richiesta->getMonImpegni();

		$dati = array(
			"richiesta" => $richiesta,
			"impegni" => $impegni,
			'is_richiesta_disabilitata' => false
		);
		return $this->render("RichiesteBundle:Richieste:monitoraggioElencoImpegni.html.twig", $dati);
	}
	
	protected function getRouteNameByTipoProcedura(string $route): string{
		return $route . self::SUFFISSO_ROUTE; 
	}

	public function generaPianoDeiCosti($id_proponente, $opzioni = array()) {
		$em = $this->getEm();
		$proponente = $em->getRepository("RichiesteBundle:Proponente")->find($id_proponente);
		$richiesta = $proponente->getRichiesta();

		$vociPiano = $em->getRepository("RichiesteBundle:PianoCosto")->getVociAcquisizioni();

		try {
			foreach ($vociPiano as $vocePiano) {
				$voce = new \RichiesteBundle\Entity\VocePianoCosto();
				$voce->setPianoCosto($vocePiano);
				$voce->setProponente($proponente);
				$voce->setRichiesta($richiesta);
				$em->persist($voce);
			}
			return true;
		} catch (\Exception $e) {
			return false;
		}
	}
}
