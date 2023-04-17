<?php

namespace AttuazioneControlloBundle\GestoriIntegrazionePagamento;

use AttuazioneControlloBundle\Entity\Istruttoria\RispostaIntegrazionePagamento;
use BaseBundle\Entity\StatoIntegrazione;


class GestoreIntegrazionePagamento extends \AttuazioneControlloBundle\Service\GestoreIntegrazionePagamentoBase {


	/**
	 * metodo che torna un array con in chiave la label da mostrare nel link e il link a cui andare
	 * @param RispostaIntegrazionePagamento $risposta_integrazione_pagamento
	 * @return array
	 */
	public function calcolaAzioniAmmesse($risposta_integrazione_pagamento) {
		$csrfTokenManager = $this->container->get("security.csrf.token_manager");
		$token = $csrfTokenManager->getToken("token")->getValue();
        $isRichiestaFirmaDigitale = $risposta_integrazione_pagamento->getProcedura()->isRichiestaFirmaDigitaleStepSuccessivi();

		$vociMenu = array();

		$stato = $risposta_integrazione_pagamento->getStato()->getCodice();
		if ($stato == StatoIntegrazione::INT_INSERITA && $this->isBeneficiario()) {
			// firmatario
			$voceMenu["label"] = "Firmatario";
			$voceMenu["path"] = $this->generateUrl("risposta_integrazione_pagamento_firmatario", array("id_integrazione_pagamento" => $risposta_integrazione_pagamento->getId()));
			$vociMenu[] = $voceMenu;

			//validazione
			$esitoValidazione = $this->controllaValiditaIntegrazione($risposta_integrazione_pagamento);

			if ($esitoValidazione->getEsito()) {
				$voceMenu["label"] = "Valida";
				$voceMenu["path"] = $this->generateUrl("valida_integrazione_pagamento", array("id_integrazione_pagamento" => $risposta_integrazione_pagamento->getId(), "_token" => $token));
				$vociMenu[] = $voceMenu;
			}
		}

		//scarica pdf domanda
		if ($stato != StatoIntegrazione::INT_INSERITA) {
			$voceMenu["label"] = "Scarica risposta";
			$voceMenu["path"] = $this->generateUrl("scarica_integrazione_risposta_pag", array("id_integrazione_pagamento" => $risposta_integrazione_pagamento->getId()));
			$vociMenu[] = $voceMenu;
		}

		//carica richiesta firmata
		if ($stato == StatoIntegrazione::INT_VALIDATA && $this->isBeneficiario()) {
			$voceMenu["label"] = "Carica risposta firmata";
			$voceMenu["path"] = $this->generateUrl("carica_integrazione_risposta_firmata_pag", array("id_integrazione_pagamento" => $risposta_integrazione_pagamento->getId()));
			$vociMenu[] = $voceMenu;
		}


		if (!($stato == StatoIntegrazione::INT_INSERITA || $stato == StatoIntegrazione::INT_VALIDATA) && $isRichiestaFirmaDigitale) {
			$voceMenu["label"] = "Scarica risposta firmata";
			$voceMenu["path"] = $this->generateUrl("scarica_integrazione_risposta_firmata_pag", array("id_integrazione_pagamento" => $risposta_integrazione_pagamento->getId()));
			$vociMenu[] = $voceMenu;
		}
		
		//invio alla pa
		if ($stato == StatoIntegrazione::INT_FIRMATA && $this->isBeneficiario()) {
			$voceMenu["label"] = "Invia risposta";
			$voceMenu["path"] = $this->generateUrl("invia_risposta_integrazione_pagamento", array("id_integrazione_pagamento" => $risposta_integrazione_pagamento->getId(), "_token" => $token));
			$voceMenu["attr"] = "data-confirm=\"Continuando non sarà più possibile modificare l'integrazione nemmeno dall'assistenza tecnica. Si intende procedere comunque?\" data-target=\"#dataConfirmModal\" data-toggle=\"modal\"";
			$vociMenu[] = $voceMenu;
		}

		//invalidazione
		if (($stato == StatoIntegrazione::INT_VALIDATA || $stato == StatoIntegrazione::INT_FIRMATA) && $this->isBeneficiario()) {
			$voceMenu["label"] = "Invalida";
			$voceMenu["path"] = $this->generateUrl("invalida_integrazione_pagamento", array("id_integrazione_pagamento" => $risposta_integrazione_pagamento->getId(), "_token" => $token));
			$voceMenu["attr"] = "data-confirm=\"Confermi l'invalidazione della risposta?\" data-target=\"#dataConfirmModal\" data-toggle=\"modal\"";
			$vociMenu[] = $voceMenu;
		}

		return $vociMenu;
	}

	public function validaIntegrazione($id_integrazione_pagamento, $opzioni = array()) {
		$risposta_integrazione = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\RispostaIntegrazionePagamento")->find($id_integrazione_pagamento);
		$this->getEm()->detach($risposta_integrazione);
		$opzioni['url_indietro'] = $this->generateUrl('dettaglio_integrazione_pagamento', array('id_integrazione_pagamento' => $id_integrazione_pagamento));
		return parent::validaIntegrazione($risposta_integrazione->getId(), $opzioni);
	}

	public function invalidaIntegrazione($id_integrazione_pagamento, $opzioni = array()) {
		$risposta_integrazione_pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\RispostaIntegrazionePagamento")->find($id_integrazione_pagamento);
		$this->getEm()->detach($risposta_integrazione_pagamento);
		$opzioni['url_indietro'] = $this->generateUrl('dettaglio_integrazione_pagamento', array('id_integrazione_pagamento' => $id_integrazione_pagamento));
		return parent::invalidaIntegrazione($risposta_integrazione_pagamento->getId(), $opzioni);
	}
	
	public function generaPdf($id_integrazione, $opzioni = array()) {
		$opzioni['url_indietro'] = $this->generateUrl('dettaglio_integrazione_pagamento', array('id_integrazione_pagamento' => $id_integrazione));
		return parent::generaPdf($id_integrazione);
	}
	
	public function inviaRisposta($id_integrazione_pagamento, $opzioni = array()) {
		$risposta_integrazione = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Istruttoria\RispostaIntegrazionePagamento")->find($id_integrazione_pagamento);
		$this->getEm()->detach($risposta_integrazione);
		$opzioni['url_indietro'] = $this->generateUrl('dettaglio_integrazione_pagamento', array('id_integrazione_pagamento' => $id_integrazione_pagamento));
		return parent::inviaRisposta($risposta_integrazione->getId(), $opzioni);
	}	

}
