<?php

namespace IstruttorieBundle\GestoriIntegrazione;

use PHPExcel_Exception;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\DependencyInjection\ContainerInterface;
use BaseBundle\Entity\StatoIntegrazione;
use RichiesteBundle\Utility\EsitoValidazione;
use RichiesteBundle\Service\GestoreResponse;
use BaseBundle\Exception\SfingeException;
use DocumentoBundle\Entity\DocumentoFile;
use IstruttorieBundle\Entity\RispostaIntegrazioneIstruttoria;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GestoreIntegrazioneIstruttoria extends \IstruttorieBundle\Service\GestoreIntegrazioneBase {

	/**
	 * metodo che torna un array con in chiave la label da mostrare nel link e il link a cui andare
	 * @param RispostaIntegrazioneIstruttoria $risposta_integrazione_istruttoria
	 * @return array
	 */
	public function calcolaAzioniAmmesse($risposta_integrazione_istruttoria) {
		$integrazioneIstruttoria = $risposta_integrazione_istruttoria->getIntegrazione();
		$csrfTokenManager = $this->container->get("security.csrf.token_manager");
		$token = $csrfTokenManager->getToken("token")->getValue();

		$vociMenu = array();

		$stato = $risposta_integrazione_istruttoria->getStato()->getCodice();
		if ($stato == StatoIntegrazione::INT_INSERITA && $this->isBeneficiario()) {
            if ($integrazioneIstruttoria->getProcedura()->isRichiestaFirmaDigitaleStepSuccessivi()) {
                // firmatario
                $voceMenu["label"] = "Firmatario";
                $voceMenu["path"] = $this->generateUrl("risposta_integrazione_scelta_firmatario",
                    array("id_integrazione_istruttoria" => $integrazioneIstruttoria->getId()));
                $vociMenu[] = $voceMenu;
            }

			//validazione
			$esitoValidazione = $this->controllaValiditaIntegrazione($risposta_integrazione_istruttoria);

			if ($esitoValidazione->getEsito()) {
				$voceMenu["label"] = "Valida";
				$voceMenu["path"] = $this->generateUrl("valida_integrazione_istruttoria", array("id_risposta_integrazione" => $risposta_integrazione_istruttoria->getId(), "_token" => $token));
				$vociMenu[] = $voceMenu;
			}
		}

		//scarica pdf domanda
		if ($stato != StatoIntegrazione::INT_INSERITA) {
			$voceMenu["label"] = "Scarica risposta";
			$voceMenu["path"] = $this->generateUrl("scarica_integrazione_risposta", array("id_integrazione_istruttoria" => $integrazioneIstruttoria->getId()));
			$vociMenu[] = $voceMenu;
		}

		//carica richiesta firmata
		if ($stato == StatoIntegrazione::INT_VALIDATA && $this->isBeneficiario() && $integrazioneIstruttoria->getProcedura()->isRichiestaFirmaDigitaleStepSuccessivi()) {
			$voceMenu["label"] = "Carica risposta firmata";
			$voceMenu["path"] = $this->generateUrl("carica_integrazione_risposta_firmata", array("id_integrazione_istruttoria" => $integrazioneIstruttoria->getId()));
			$vociMenu[] = $voceMenu;
		}

		if (!($stato == StatoIntegrazione::INT_INSERITA || $stato == StatoIntegrazione::INT_VALIDATA)
            && $integrazioneIstruttoria->getProcedura()->isRichiestaFirmaDigitaleStepSuccessivi()) {
			$voceMenu["label"] = "Scarica risposta firmata";
			$voceMenu["path"] = $this->generateUrl("scarica_integrazione_risposta_firmata", array("id_integrazione_istruttoria" => $integrazioneIstruttoria->getId()));
			$vociMenu[] = $voceMenu;
		}
		
		//invio alla pa
		if ($stato == StatoIntegrazione::INT_FIRMATA && $this->isBeneficiario()) {
			$voceMenu["label"] = "Invia risposta";
			$voceMenu["path"] = $this->generateUrl("invia_risposta_integrazione_istruttoria", array("id_integrazione_istruttoria" => $integrazioneIstruttoria->getId(), "_token" => $token));
			$voceMenu["attr"] = "data-confirm=\"Continuando non sarà più possibile modificare l'integrazione nemmeno dall'assistenza tecnica. Si intende procedere comunque?\" data-target=\"#dataConfirmModal\" data-toggle=\"modal\"";
			$vociMenu[] = $voceMenu;
		}

		//invalidazione
		if (($stato == StatoIntegrazione::INT_VALIDATA || $stato == StatoIntegrazione::INT_FIRMATA) && $this->isBeneficiario()) {
			$voceMenu["label"] = "Invalida";
			$voceMenu["path"] = $this->generateUrl("invalida_integrazione_istruttoria", array("id_integrazione_istruttoria" => $integrazioneIstruttoria->getId(), "_token" => $token));
			$voceMenu["attr"] = "data-confirm=\"Confermi l'invalidazione della risposta?\" data-target=\"#dataConfirmModal\" data-toggle=\"modal\"";
			$vociMenu[] = $voceMenu;
		}

		return $vociMenu;
	}

	public function validaIntegrazione($id_risposta_integrazione, $opzioni = array()) {
		$risposta_integrazione_istruttoria = $this->getEm()->getRepository("IstruttorieBundle\Entity\RispostaIntegrazioneIstruttoria")->find($id_risposta_integrazione);
		$integrazione = $risposta_integrazione_istruttoria->getIntegrazione();
		$this->getEm()->detach($risposta_integrazione_istruttoria);
		$opzioni['url_indietro'] = $this->generateUrl('dettaglio_integrazione_istruttoria', array('id_integrazione_istruttoria' => $integrazione->getId()));
		return parent::validaIntegrazione($risposta_integrazione_istruttoria->getId(), $opzioni);
	}

	public function invalidaIntegrazione($id_risposta_integrazione, $opzioni = array()) {
		$risposta_integrazione_istruttoria = $this->getEm()->getRepository("IstruttorieBundle\Entity\RispostaIntegrazioneIstruttoria")->find($id_risposta_integrazione);
        $id_integrazione_istruttoria = $risposta_integrazione_istruttoria->getIntegrazione()->getId();
		$this->getEm()->detach($risposta_integrazione_istruttoria);
		$opzioni['url_indietro'] = $this->generateUrl('dettaglio_integrazione_istruttoria', array('id_integrazione_istruttoria' => $id_integrazione_istruttoria));
		return parent::invalidaIntegrazione($risposta_integrazione_istruttoria->getId(), $opzioni);
	}
	
	public function generaPdf($id_integrazione, $opzioni = array()) {
		$opzioni['url_indietro'] = $this->generateUrl('dettaglio_integrazione_istruttoria', array('id_integrazione_istruttoria' => $id_integrazione));
		return parent::generaPdf($id_integrazione);
	}
	
	public function inviaRisposta($id_risposta_integrazione, $opzioni = array()) {
		$risposta_integrazione_istruttoria = $this->getEm()->getRepository("IstruttorieBundle\Entity\RispostaIntegrazioneIstruttoria")->find($id_risposta_integrazione);
		$risposta_integrazione_istruttoria->getIntegrazione()->getIstruttoria()->setSospesa(false);
        $id_integrazione_istruttoria = $risposta_integrazione_istruttoria->getIntegrazione()->getId();
		$this->getEm()->detach($risposta_integrazione_istruttoria);
		$opzioni['url_indietro'] = $this->generateUrl('dettaglio_integrazione_istruttoria', array('id_integrazione_istruttoria' => $id_integrazione_istruttoria));
		return parent::inviaRisposta($risposta_integrazione_istruttoria->getId(), $opzioni);
	}

	/**
	 * @param $id_integrazione_istruttoria
	 * @param bool $da_comunicazione
	 * @return GestoreResponse
	 * @throws SfingeException
	 */
	public function impostaRispostaComeLetta($id_integrazione_istruttoria, $da_comunicazione = false) {
		$risposta_integrazione_istruttoria = $this->getEm()->getRepository("IstruttorieBundle\Entity\RispostaIntegrazioneIstruttoria")->findOneBy(['integrazione' => $id_integrazione_istruttoria]);
		$this->getEm()->detach($risposta_integrazione_istruttoria);
		if ($da_comunicazione) {
			$opzioni['url_indietro'] = $this->generateUrl('elenco_comunicazioni', ['id_istruttoria' => $risposta_integrazione_istruttoria->getIntegrazione()->getIstruttoria()->getId()]);
		} else {
			$opzioni['url_indietro'] = $this->generateUrl('home');
		}
		return parent::impostaRispostaComeLetta($risposta_integrazione_istruttoria->getId(), $opzioni);
	}

	/**
	 * @param Procedura $procedura
	 * @return StreamedResponse
	 * @throws PHPExcel_Exception
	 */
	public function esportazioneCruscottoComunicazioniIstruttoria(Procedura $procedura) {
		return parent::esportazioneCruscottoComunicazioniIstruttoria($procedura);
	}
}
