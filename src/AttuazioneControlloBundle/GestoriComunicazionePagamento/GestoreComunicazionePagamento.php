<?php

namespace AttuazioneControlloBundle\GestoriComunicazionePagamento;

use AttuazioneControlloBundle\Entity\Istruttoria\RispostaComunicazionePagamento;
use BaseBundle\Entity\StatoComunicazionePagamento;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

class GestoreComunicazionePagamento extends \AttuazioneControlloBundle\Service\GestoreComunicazionePagamentoBase {

    /**
     * @param RispostaComunicazionePagamento $rispostaComunicazionePagamento
     * @return array
     */
    public function calcolaAzioniAmmesse(RispostaComunicazionePagamento $rispostaComunicazionePagamento) {
        /** @var  csrfTokenManager */
        $csrfTokenManager = $this->container->get("security.csrf.token_manager");
        $token = $csrfTokenManager->getToken("token")->getValue();

        $vociMenu = array();

        $stato = $rispostaComunicazionePagamento->getStato()->getCodice();
        if ($stato == StatoComunicazionePagamento::COM_PAG_INSERITA && $this->isBeneficiario()) {
            // Firmatario
            $voceMenu["label"] = "Firmatario";
            $voceMenu["path"] = $this->generateUrl("risposta_comunicazione_pagamento_firmatario", array("id" => $rispostaComunicazionePagamento->getId()));
            $vociMenu[] = $voceMenu;

            // Validazione
            $esitoValidazione = $this->controllaValiditaComunicazionePagamento($rispostaComunicazionePagamento);
            if ($esitoValidazione->getEsito()) {
                $voceMenu["label"] = "Valida";
                $voceMenu["path"] = $this->generateUrl("valida_risposta_comunicazione_pagamento", array("id" => $rispostaComunicazionePagamento->getId(), "_token" => $token));
                $vociMenu[] = $voceMenu;
            }
        }

        // Scarica pdf domanda
        if ($stato != StatoComunicazionePagamento::COM_PAG_INSERITA) {
            $voceMenu["label"] = "Scarica risposta";
            $voceMenu["path"] = $this->generateUrl("scarica_risposta_comunicazione_pagamento", array("id" => $rispostaComunicazionePagamento->getId()));
            $vociMenu[] = $voceMenu;
        }

        // Carica richiesta firmata
        if ($stato == StatoComunicazionePagamento::COM_PAG_VALIDATA && $this->isBeneficiario()) {
            $voceMenu["label"] = "Carica risposta firmata";
            $voceMenu["path"] = $this->generateUrl("carica_risposta_firmata_comunicazione_pagamento", array("id" => $rispostaComunicazionePagamento->getId()));
            $vociMenu[] = $voceMenu;
        }

        // Scarica documento firmato
        if (!($stato == StatoComunicazionePagamento::COM_PAG_INSERITA || $stato == StatoComunicazionePagamento::COM_PAG_VALIDATA)) {
            $voceMenu["label"] = "Scarica risposta firmata";
            $voceMenu["path"] = $this->generateUrl("scarica_risposta_firmata_comunicazione_pagamento", array("id" => $rispostaComunicazionePagamento->getId()));
            $vociMenu[] = $voceMenu;
        }
        
        // Invio alla pa
        if ($stato == StatoComunicazionePagamento::COM_PAG_FIRMATA && $this->isBeneficiario()) {
            $voceMenu["label"] = "Invia risposta";
            $voceMenu["path"] = $this->generateUrl("invia_risposta_comunicazione_pagamento", array("id" => $rispostaComunicazionePagamento->getId(), "_token" => $token));
            $voceMenu["attr"] = "data-confirm=\"Continuando non sarà più possibile modificare la risposta, nemmeno dall'assistenza tecnica. Si intende procedere comunque?\" data-target=\"#dataConfirmModal\" data-toggle=\"modal\"";
            $vociMenu[] = $voceMenu;
        }

        // Invalidazione
        if (($stato == StatoComunicazionePagamento::COM_PAG_VALIDATA || $stato == StatoComunicazionePagamento::COM_PAG_FIRMATA) && $this->isBeneficiario()) {
            $voceMenu["label"] = "Invalida";
            $voceMenu["path"] = $this->generateUrl("invalida_risposta_comunicazione_pagamento", array("id" => $rispostaComunicazionePagamento->getId(), "_token" => $token));
            $voceMenu["attr"] = "data-confirm=\"Confermi l'invalidazione della risposta?\" data-target=\"#dataConfirmModal\" data-toggle=\"modal\"";
            $vociMenu[] = $voceMenu;
        }

        return $vociMenu;
    }
}
