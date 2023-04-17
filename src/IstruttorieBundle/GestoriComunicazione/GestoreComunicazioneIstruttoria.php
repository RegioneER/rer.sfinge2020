<?php

namespace IstruttorieBundle\GestoriComunicazione;

use BaseBundle\Entity\StatoComunicazioneEsitoIstruttoria;

class GestoreComunicazioneIstruttoria extends \IstruttorieBundle\Service\GestoreComunicazioneBase {

	public function calcolaAzioniAmmesse($risposta_comunicazione_istruttoria) {
		$csrfTokenManager = $this->container->get("security.csrf.token_manager");
		$token = $csrfTokenManager->getToken("token")->getValue();

		$vociMenu = array();
		$comunicazione = $risposta_comunicazione_istruttoria->getComunicazione();
		$stato = $risposta_comunicazione_istruttoria->getStato()->getCodice();
		if ($stato == StatoComunicazioneEsitoIstruttoria::ESI_INSERITA && $this->isBeneficiario()) {
            if ($comunicazione->getProcedura()->isRichiestaFirmaDigitaleStepSuccessivi()) {
                // firmatario
                $voceMenu["label"] = "Firmatario";
                $voceMenu["path"] = $this->generateUrl("risposta_comunicazione_istruttoria_firmatario",
                    array("id_comunicazione" => $comunicazione->getId()));
                $vociMenu[] = $voceMenu;
            }

			//validazione
			$esitoValidazione = $this->controllaValiditaRisposta($risposta_comunicazione_istruttoria);

			if ($esitoValidazione->getEsito()) {
				$voceMenu["label"] = "Valida";
				$voceMenu["path"] = $this->generateUrl("valida_comunicazione_risposta", array("id_comunicazione_risposta" => $risposta_comunicazione_istruttoria->getId(), "_token" => $token));
				$vociMenu[] = $voceMenu;
			}
		}

		//scarica pdf domanda
		if ($stato != StatoComunicazioneEsitoIstruttoria::ESI_INSERITA) {
			$voceMenu["label"] = "Scarica risposta";
			$voceMenu["path"] = $this->generateUrl("scarica_comunicazione_risposta", array("id_comunicazione_risposta" => $risposta_comunicazione_istruttoria->getId()));
			$vociMenu[] = $voceMenu;
		}

		//carica richiesta firmata
        if ($stato == StatoComunicazioneEsitoIstruttoria::ESI_VALIDATA && $this->isBeneficiario() && $comunicazione->getProcedura()->isRichiestaFirmaDigitaleStepSuccessivi()) {
			$voceMenu["label"] = "Carica risposta firmata";
			$voceMenu["path"] = $this->generateUrl("carica_comunicazione_risposta_firmata", array("id_comunicazione_risposta" => $risposta_comunicazione_istruttoria->getId(), "id_comunicazione" => $comunicazione->getId()));
			$vociMenu[] = $voceMenu;
		}

        if (!($stato == StatoComunicazioneEsitoIstruttoria::ESI_INSERITA || $stato == StatoComunicazioneEsitoIstruttoria::ESI_VALIDATA)
            && $comunicazione->getProcedura()->isRichiestaFirmaDigitaleStepSuccessivi()) {
			$voceMenu["label"] = "Scarica risposta firmata";
			$voceMenu["path"] = $this->generateUrl("scarica_comunicazione_risposta_firmata", array("id_comunicazione_risposta" => $risposta_comunicazione_istruttoria->getId()));
			$vociMenu[] = $voceMenu;
		}

		//invio alla pa
		if ($stato == StatoComunicazioneEsitoIstruttoria::ESI_FIRMATA && $this->isBeneficiario()) {
			$voceMenu["label"] = "Invia risposta";
			$voceMenu["path"] = $this->generateUrl("invia_risposta_comunicazione_istruttoria", array("id_comunicazione_risposta" => $risposta_comunicazione_istruttoria->getId(), "_token" => $token));
			$voceMenu["attr"] = "data-confirm=\"Continuando non sarà più possibile modificare la comunicazione nemmeno dall'assistenza tecnica. Si intende procedere comunque?\" data-target=\"#dataConfirmModal\" data-toggle=\"modal\"";
			$vociMenu[] = $voceMenu;
		}

		//invalidazione
		if (($stato == StatoComunicazioneEsitoIstruttoria::ESI_VALIDATA || $stato == StatoComunicazioneEsitoIstruttoria::ESI_FIRMATA) && $this->isBeneficiario()) {
			$voceMenu["label"] = "Invalida";
			$voceMenu["path"] = $this->generateUrl("invalida_comunicazione_risposta", array("id_comunicazione_risposta" => $risposta_comunicazione_istruttoria->getId(), "_token" => $token));
			$voceMenu["attr"] = "data-confirm=\"Confermi l'invalidazione della risposta?\" data-target=\"#dataConfirmModal\" data-toggle=\"modal\"";
			$vociMenu[] = $voceMenu;
		}

		return $vociMenu;
	}

	public function validaRispostaComunicazione($comunicazione_risposta, $opzioni = array()) {
		$comunicazione = $comunicazione_risposta->getComunicazione();
		//$this->getEm()->detach($comunicazione_risposta);
		$opzioni['url_indietro'] = $this->generateUrl('dettaglio_risposta_comunicazione', array('id_comunicazione' => $comunicazione->getId()));
		return parent::validaRispostaComunicazione($comunicazione_risposta, $opzioni);
	}

	public function invalidaRispostaComunicazione($comunicazione_risposta, $opzioni = array()) {
		$comunicazione = $comunicazione_risposta->getComunicazione();
		//$this->getEm()->detach($comunicazione_risposta);
		$opzioni['url_indietro'] = $this->generateUrl('dettaglio_risposta_comunicazione', array('id_comunicazione' => $comunicazione->getId()));
		return parent::invalidaRispostaComunicazione($comunicazione_risposta, $opzioni);
	}

	public function generaPdf($comunicazione_risposta, $opzioni = array()) {
		$comunicazione = $comunicazione_risposta->getComunicazione();
		$opzioni['url_indietro'] = $this->generateUrl('dettaglio_risposta_comunicazione', array('id_comunicazione' => $comunicazione->getId()));
		return parent::generaPdf($comunicazione_risposta);
	}

	public function inviaRisposta($comunicazione_risposta, $opzioni = array()) {
		$comunicazione = $comunicazione_risposta->getComunicazione();
		//$this->getEm()->detach($comunicazione_risposta);
		$opzioni['url_indietro'] = $this->generateUrl('dettaglio_risposta_comunicazione', array('id_comunicazione' => $comunicazione->getId()));
		return parent::inviaRisposta($comunicazione_risposta, $opzioni);
	}

}
