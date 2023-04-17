<?php

namespace RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo;

use RichiesteBundle\GestoriRichiestePA\ASezioneRichiesta;
use Symfony\Component\HttpFoundation\RedirectResponse;
use RichiesteBundle\Entity\Richiesta;

class FasiProcedurali extends ASezioneRichiesta {
    const TITOLO = 'Gestione fasi procedurali';
    const SOTTOTITOLO = 'Inserire voci relative allo stato di avanzamento';
    const NOME_SEZIONE = 'stati_avanzamento';

    public function getTitolo() {
        return self::TITOLO . ' ' . ($this->richiesta->getMandatario());
    }

    public function getUrl() {
        return $this->generateUrl(self::ROTTA, array(
                    'id_richiesta' => $this->richiesta->getId(),
                    'nome_sezione' => self::NOME_SEZIONE,
                    'parametro1' => $this->richiesta->getId(),
        ));
    }

    public function valida() {
        $em = $this->getEm();
        if (0 == $this->richiesta->getVociFaseProcedurale()->count()) {
            $esito = $this->getGestoreFaseProcedurale()->generaFaseProceduraleRichiesta($this->richiesta->getId());
            if (!$esito->res) {
                $messaggio = "Errore durante la generazione dello stato di avanzamento, contattare l'assistenza tecnica " . "( " . $esito->messaggio . " )";
                return $this->addErrorRedirect($messaggio, "home");
			}
			
			$richiestaDB = $em->getRepository('RichiesteBundle:Richiesta')->find($this->richiesta->getId());
			/** @var Richiesta $richiestaDB */
			$this->richiesta->setVociFaseProcedurale(
				$richiestaDB->getVociFaseProcedurale()
			);
        }
        $esito = $this->getGestoreFaseProcedurale()->validaFaseProceduraleRichiesta($this->richiesta->getId(), array());
        $this->listaMessaggi = \array_merge($this->listaMessaggi, $esito->getMessaggiSezione());
    }

    public function visualizzaSezione(array $parametri) {
        $this->setupPagina(self::TITOLO, self::SOTTOTITOLO);

        $response = $this->getGestoreFaseProcedurale()->aggiornaFaseProceduraleRichiesta($this->richiesta->getId());

        if ($response->getResponse() instanceof RedirectResponse) {
            return $this->redirectToRoute("procedura_pa_dettaglio_richiesta", array("id_richiesta" => $this->richiesta->getId()));
        } else {
            return $response->getResponse();
        }
    }
}
