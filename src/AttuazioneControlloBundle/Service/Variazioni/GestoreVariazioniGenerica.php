<?php

namespace AttuazioneControlloBundle\Service\Variazioni;

use AttuazioneControlloBundle\Entity\DocumentoVariazione;
use AttuazioneControlloBundle\Entity\StatoVariazione;
use AttuazioneControlloBundle\Entity\VariazioneRichiesta;
use BaseBundle\Exception\SfingeException;
use BaseBundle\Service\BaseServiceTrait;
use DocumentoBundle\Entity\DocumentoFile;
use DocumentoBundle\Entity\TipologiaDocumento;
use PaginaBundle\Services\Pagina;
use RichiesteBundle\Utility\EsitoValidazione;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class GestoreVariazioniGenerica implements IGestoreVariazioni {
    use BaseServiceTrait;
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var VariazioneRichiesta
     */
    protected $variazione;

    public function __construct(VariazioneRichiesta $variazione, ContainerInterface $container) {
        $this->variazione = $variazione;
        $this->container = $container;
    }

    public function datiGeneraliVariazione(): Response {
        $request = $this->getCurrentRequest();
        $dettaglioVariazioneRoute = $this->generateUrl("dettaglio_variazione", ["id_variazione" => $this->variazione->getId()]);
        $opzioni["url_indietro"] = $dettaglioVariazioneRoute;
        $opzioni["disabled"] = $this->variazione->isRichiestaDisabilitata();

        $form = $this->createForm("AttuazioneControlloBundle\Form\DatiGeneraliVariazioneType", $this->variazione, $opzioni);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getEm();
            try {
                $em->flush();
                $this->addSuccess("Dati generali salvati correttamente");

                return $this->redirect($dettaglioVariazioneRoute);
            } catch (\Exception $e) {
                throw new SfingeException("Dati generali non salvati", 0, $e);
            }
        }
        /** @var Pagina $paginaService */
        $paginaService = $this->container->get("pagina");
        $paginaService->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $paginaService->aggiungiElementoBreadcrumb("Elenco variazioni", $this->generateUrl("elenco_variazioni", ["id_richiesta" => $this->variazione->getRichiesta()->getId()]));
        $paginaService->aggiungiElementoBreadcrumb("Dettaglio variazione", $dettaglioVariazioneRoute);
        $paginaService->aggiungiElementoBreadcrumb("Dati generali variazione");

        $dati = [
            "id_variazione" => $this->variazione->getId(),
            "form" => $form->createView(),
        ];

        return $this->render("AttuazioneControlloBundle:Variazioni:datiGenerali.html.twig", $dati);
    }

    public function eliminaDocumentoVariazione(DocumentoVariazione $documento_variazione): Response {
        if (\in_array($this->variazione->getStato(), [StatoVariazione::VAR_INVIATA_PA, StatoVariazione::VAR_PROTOCOLLATA])) {
            $id_richiesta = $this->variazione->getRichiesta()->getId();
            return $this->addErrorRedirect("L'operazione non è compatibile con lo stato della variazione.", "elenco_variazioni", ["id_richiesta" => $id_richiesta]);
        }

        try {
            $em = $this->getEm();
            $em->remove($documento_variazione);
            $em->flush();
            return $this->addSuccesRedirect(
                "Il documento è stato correttamente eliminato",
                "documenti_variazione",
                ["id_variazione" => $this->variazione->getId()]
            );
        } catch (\Exception $e) {
            return $this->addErrorRedirect(
                "Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.",
                "gestione_documenti_pagamento",
                ["id_variazione" => $this->variazione->getId()]
            );
        }
    }

    public function isVariazioneBloccata(): bool {
        return true == $this->variazione->getRichiesta()->getBloccoVariazione();
    }

    public function validaDatiGenerali(): EsitoValidazione {
        $esito = new EsitoValidazione(true);
        $esito->setSezione("Dati generali");
        $note = $this->variazione->getNote();

        if (\is_null($note) || 0 == \strlen($note)) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Il campo NOTE BENEFICIARIO non è compilato.");
        }

        return $esito;
    }

    public function validaDocumenti(): EsitoValidazione {
        $esito = new EsitoValidazione(true);
        $esito->setSezione("Documenti");
        
        if(count($this->variazione->getDocumentiVariazione()) == 0) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("E' necessario caricare almeno un documento");
            $esito->addMessaggio("E' necessario caricare almeno un documento");
        }
        
        return $esito;
    }

    public function eliminaVariazione(): Response {
        if (\in_array($this->variazione->getStato()->getCodice(), [StatoVariazione::VAR_INSERITA, StatoVariazione::VAR_VALIDATA, StatoVariazione::VAR_FIRMATA])) {
            try {
                $em = $this->getEm();
                $em->remove($this->variazione);
                $em->flush();
                $this->addSuccess("La variazione è stata correttamente eliminata");
            } catch (\Exception $e) {
                $this->container->get('logger')->error($e->getTraceAsString());
                $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
            }
        } else {
            $this->addError("L'operazione non è compatibile con lo stato del pagamento.");
        }
        $id_richiesta = $this->variazione->getRichiesta()->getId();
        $url = $this->generateUrl("elenco_variazioni", ["id_richiesta" => $id_richiesta]);

        return $this->redirect($url);
    }

    public function caricaVariazioneFirmata(): Response {
        if (!$this->variazione->getStato()->uguale(StatoVariazione::VAR_VALIDATA)) {
            return $this->addErrorRedirect("Stato non valido per effettuare l'operazione", "elenco_richieste");
        }

        $documento_file = new DocumentoFile();
        $opzioni_form["tipo"] = TipologiaDocumento::VARIAZIONE_RICHIESTA_FIRMATA;
        $opzioni_form["cf_firmatario"] = $this->variazione->getFirmatario()->getCodiceFiscale();
        $form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documento_file, $opzioni_form);
        $id_variazione = $this->variazione->getId();
        $form->add("pultanti", "BaseBundle\Form\SalvaIndietroType", ["url" => $this->generateUrl("dettaglio_variazione", ['id_variazione' => $id_variazione])]);
        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $richiesta = $this->variazione->getRichiesta();
                $this->container->get("documenti")->carica($documento_file, 0, $richiesta);
                $this->variazione->setDocumentoVariazioneFirmato($documento_file);
                $this->container->get("sfinge.stati")->avanzaStato($this->variazione, StatoVariazione::VAR_FIRMATA);
                $em = $this->getEm();
                $em->persist($this->variazione);
                $em->flush();

                return $this->addSuccessRedirect("Documento caricato correttamente", "dettaglio_variazione", ['id_variazione' => $id_variazione]);
            } catch (\Exception $e) {
                //TODO gestire cancellazione del file
                $this->addFlash('error', "Errore generico");
            }
        }

        return $this->render('AttuazioneControlloBundle:Variazioni:caricaVariazioneFirmata.html.twig', [
            "id_variazione" => $id_variazione,
            "form" => $form->createView(),
        ]);
    }

    public function scaricaDomanda(): Response {
        return $this->downloadDocumento($this->variazione->getDocumentoVariazione());
    }

    private function downloadDocumento(?DocumentoFile $documento): Response {
        if (\is_null($documento)) {
            return $this->addErrorRedirect("Documento non trovato", "dettaglio_variazione", ["id_variazione" => $this->variazione->getId()]);
        }

        return $this->container->get("documenti")->scaricaDaId($documento->getId());
    }

    public function scaricaVariazioneFirmata(): Response {
        return $this->downloadDocumento($this->variazione->getDocumentoVariazioneFirmato());
    }

    /**
     * @throws SfingeException
     */
    public function invalidaVariazione(): Response {
        $statoVariazione = $this->variazione->getStato();
        if ($statoVariazione->uguale(StatoVariazione::VAR_VALIDATA) ||
                $statoVariazione->uguale(StatoVariazione::VAR_FIRMATA)) {
            $this->variazione->setDocumentoVariazione(null);
            $em = $this->getEm();
            $connection = $em->getConnection();
            try {
                $connection->beginTransaction();
                $this->container->get("sfinge.stati")->avanzaStato($this->variazione, StatoVariazione::VAR_INSERITA, false);
                $em->flush();
                $connection->commit();
                return $this->addSuccesRedirect("Variazione invalidata", "dettaglio_variazione", ['id_variazione' => $this->variazione->getId()]);
            } catch (\Exception $e) {
                if ($connection->isTransactionActive()) {
                    $connection->rollBack();
                }
                throw new SfingeException("Errore durante il salvataggio delle informazioni", 0, $e);
            }
        }
        throw new SfingeException("Stato non valido per effettuare la invalidazione");
    }

    public function validaVariazioneInviabile(): EsitoValidazione {
        $esito = new EsitoValidazione(true);
        if (!$this->variazione->getStato()->uguale(StatoVariazione::VAR_FIRMATA)) {
            $esito->setEsito(false);
            $esito->addMessaggio('Stato non valido per effettuare la validazione');
        }

        return $esito;
    }

    public function modificaFirmatario(): Response {
        $indietro = $this->generateUrl("dettaglio_variazione", ["id_variazione" => $this->variazione->getId()]);
        if ($this->variazione->isRichiestaDisabilitata()) {
            $this->addError("Impossibile modificare il firmatario");
            return $this->redirect($indietro);
        }
        $em = $this->getEm();
        $richiesta = $this->variazione->getRichiesta();

        $options = [
            "url_indietro" => $indietro,
            "firmatabili" => $em->getRepository("SoggettoBundle:Soggetto")->getFirmatariAmmissibili($richiesta->getSoggetto()),
        ];
        $form = $this->createForm(\AttuazioneControlloBundle\Form\VariazioneType::class, $this->variazione, $options);
        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->flush();
                $this->addSuccess("Il firmatario è stato correttamente modificato");
                return $this->redirect($indietro);
            } catch (\Exception $e) {
                $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
            }
        }
        $dati = ["form" => $form->createView()];

        return $this->render("AttuazioneControlloBundle:Variazioni:aggiungiVariazione.html.twig", $dati);
    }
}
