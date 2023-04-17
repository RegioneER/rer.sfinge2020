<?php

namespace AttuazioneControlloBundle\Service\Variazioni;

use AttuazioneControlloBundle\Entity\StatoVariazione;
use AttuazioneControlloBundle\Entity\VariazioneRichiesta;
use BaseBundle\Exception\SfingeException;
use BaseBundle\Service\BaseServiceTrait;
use DocumentoBundle\Component\ResponseException;
use DocumentoBundle\Entity\TipologiaDocumento;
use DocumentoBundle\Entity\TipologiaDocumentoRepository;
use PaginaBundle\Services\Pagina;
use RichiesteBundle\Utility\EsitoValidazione;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\HttpFoundation\Response;

abstract class GestoreVariazioniSpecifica implements IGestoreVariazioniConcreta {
    use BaseServiceTrait;
    use GestoreVariazioneWrapperTrait;

    /**
     * @var VariazioneRichiesta
     */
    protected $variazione;

    protected function gestioneBarraAvanzamento() {
        $statoRichiesta = $this->variazione->getStato()->getCodice();
        /** @var Procedura $procedura */
        $procedura = $this->variazione->getProcedura();

        $arrayStati = [
            'Inserita' => true,
            'Validata' => false,
            'Firmata' => false,
            'Inviata' => false,
        ];

        switch ($statoRichiesta) {
            case 'VAR_PROTOCOLLATA':
            case 'VAR_INVIATA_PA':
                $arrayStati['Inviata'] = true;
                // no break
            case 'VAR_FIRMATA':
                $arrayStati['Firmata'] = true;
                // no break
            case 'VAR_VALIDATA':
                $arrayStati['Validata'] = true;
        }

        if (!$procedura->isRichiestaFirmaDigitaleStepSuccessivi()) {
            unset($arrayStati['Firmata']);
        }

        return $arrayStati;
    }

    public function validaVariazione(): Response {
        $richiesta = $this->variazione->getRichiesta();
        $esitoValidazione = new EsitoValidazione(true);
        if ($this->variazione->getStato()->uguale(StatoVariazione::VAR_INSERITA)) {
            if (!$esitoValidazione->getEsito()) {
                throw new SfingeException("La variazione non è validabile");
            }
            if (!is_null($this->variazione->getDocumentoVariazione())) {
                $this->container->get("documenti")->cancella($this->variazione->getDocumentoVariazione(), 1);
            }
            //genero il nuovo pdf
            $pdf = $this->generaPdf(false);

            //lo persisto
            $tipoDocumento = $this->getEm()->getRepository(TipologiaDocumento::class)->findOneByCodice(TipologiaDocumento::VARIAZIONE_RICHIESTA);
            $documentoVariazione = $this->container->get("documenti")->caricaDaByteArray($pdf, $this->getNomePdfVariazione() . ".pdf", $tipoDocumento, false, $richiesta);

            //associo il documento al pagamento
            $this->variazione->setDocumentoVariazione($documentoVariazione);
            $this->getEm()->persist($this->variazione);

            //avanzo lo stato del pagamento
            $this->container->get("sfinge.stati")->avanzaStato($this->variazione, StatoVariazione::VAR_VALIDATA);

            $this->getEm()->flush();

            return $this->addSuccesRedirect("Variazione validata", "dettaglio_variazione", ["id_variazione" => $this->variazione->getId()]);
        }

        throw new SfingeException("Stato non valido per effettuare la validazione");
    }

    public function gestioneDocumentiVariazione(): Response {
        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        $documento_variazione = new \AttuazioneControlloBundle\Entity\DocumentoVariazione();
        $documento_file = new \DocumentoBundle\Entity\DocumentoFile();

        $richiesta = $this->variazione->getAttuazioneControlloRichiesta()->getRichiesta();
        $listaTipi = $this->getTipiDocumentiCaricabili(false);

        if (count($listaTipi) > 0 && !$this->variazione->isRichiestaDisabilitata()) {
            $opzioni_form["lista_tipi"] = $listaTipi;
            $opzioni_form["cf_firmatario"] = $this->variazione->getFirmatario()->getCodiceFiscale();
            $form = $this->createForm(\DocumentoBundle\Form\Type\DocumentoFileType::class, $documento_file, $opzioni_form);
            $form->add('submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, ['label' => 'Carica']);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $this->container->get("documenti")->carica($documento_file, 0, $richiesta);

                    $documento_variazione->setDocumentoFile($documento_file);
                    $documento_variazione->setVariazione($this->variazione);
                    $em->persist($documento_variazione);

                    $em->flush();
                    return $this->addSuccesRedirect("Il documento è stato correttamente salvato", "documenti_variazione", ["id_variazione" => $this->variazione->getId()]);
                } catch (ResponseException $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
            $form_view = $form->createView();
        } else {
            $form_view = null;
        }

        /** @var Pagina $paginaService */
        $paginaService = $this->container->get("pagina");
        $paginaService->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $paginaService->aggiungiElementoBreadcrumb("Elenco variazioni", $this->generateUrl("elenco_variazioni", ["id_richiesta" => $richiesta->getId()]));
        $paginaService->aggiungiElementoBreadcrumb("Dettaglio variazione", $this->generateUrl("dettaglio_variazione", ["id_variazione" => $this->variazione->getId()]));
        $paginaService->aggiungiElementoBreadcrumb("Gestione documenti");

        $dati = [
            "variazione" => $this->variazione,
            "form" => $form_view,
            'is_richiesta_disabilitata' => $this->variazione->isRichiestaDisabilitata(),
        ];

        return $this->render("AttuazioneControlloBundle:Variazioni:gestioneDocumenti.html.twig", $dati);
    }

    protected function getTipiDocumentiCaricabili(bool $solo_obbligatori = false): array {
        $richiesta = $this->variazione->getAttuazioneControlloRichiesta()->getRichiesta();
        $procedura_id = $richiesta->getProcedura()->getId();
        /** @var TipologiaDocumentoRepository $tipologiaRepository */
        $tipologiaRepository = $this->getEm()->getRepository(TipologiaDocumento::class);
        $standardDoc = $tipologiaRepository->findByTipologia('documento_variazione_std');
        $tipologieDocVariazione = $tipologiaRepository->ricercaDocumentiVariazione($this->variazione->getId(), $procedura_id, false);
        $tipologie_con_duplicati = $tipologiaRepository->findBy(["abilita_duplicati" => 1, "procedura" => $richiesta->getProcedura(), "tipologia" => 'variazione']);
        $res = \array_merge($tipologieDocVariazione, $tipologie_con_duplicati, $standardDoc);

        return $res;
    }

    abstract protected function generaPdf(bool $facsimile = true): string;

    protected function getNomePdfVariazione() {
        $date = new \DateTime();
        $data = $date->format('d-m-Y');
        return "Richiesta di variazione " . $this->variazione->getId() . " " . $data;
    }

    abstract public function dettaglioVariazione(): Response;

    public function dammiVociMenuElencoVariazioni(): array {
        $csrfTokenManager = $this->container->get("security.csrf.token_manager");
        $token = $csrfTokenManager->getToken("token")->getValue();
        $procedura = $this->variazione->getProcedura();
        $vociMenu = [];
        $voceMenu["path"] = '#';
        if (!is_null($this->variazione->getStato())) {
            $stato = $this->variazione->getStato()->getCodice();
            if (StatoVariazione::VAR_INSERITA == $stato) {
                //validazione
                $esitoValidazione = $this->controllaValiditaVariazione();
                if (true == $esitoValidazione->getEsito() && false == $this->isVariazioneBloccata()) {
                    $voceMenu["label"] = "Valida";
                    $voceMenu["path"] = $this->generateUrl("valida_variazione", ["id_variazione" => $this->variazione->getId(), "_token" => $token]);
                    $vociMenu[] = $voceMenu;
                }
                $voceMenu["label"] = "Modifica Firmatario";
                $voceMenu["path"] = $this->generateUrl("modifica_firmatario_variazione", ["id_variazione" => $this->variazione->getId(), "_token" => $token]);
                $vociMenu[] = $voceMenu;
            }

            if (false == $this->isVariazioneBloccata()) {
                //scarica pdf domanda
                if (StatoVariazione::VAR_INSERITA != $stato) {
                    $voceMenu["label"] = "Scarica variazione";
                    $voceMenu["path"] = $this->generateUrl("scarica_variazione", ["id_variazione" => $this->variazione->getId()]);
                    $vociMenu[] = $voceMenu;
                }

                if (!(StatoVariazione::VAR_INSERITA == $stato || StatoVariazione::VAR_VALIDATA == $stato) && $procedura->isRichiestaFirmaDigitaleStepSuccessivi()) {
                    $voceMenu["label"] = "Scarica variazione firmato";
                    $voceMenu["path"] = $this->generateUrl("scarica_variazione_firmata", ["id_variazione" => $this->variazione->getId()]);
                    $vociMenu[] = $voceMenu;
                }
                //invio alla pa
                if (StatoVariazione::VAR_FIRMATA == $stato) {
                    $voceMenu["label"] = "Invia variazione";
                    $voceMenu["path"] = $this->generateUrl("invia_variazione", ["id_variazione" => $this->variazione->getId(), "_token" => $token]);
                    $voceMenu["attr"] = "data-confirm=\"Continuando non sarà più possibile modificare la variazione nemmeno dall'assistenza tecnica. Si intende procedere comunque?\" data-target=\"#dataConfirmModal\" data-toggle=\"modal\"";
                    $vociMenu[] = $voceMenu;
                }
            }
            //carica variazione firmata
            if (StatoVariazione::VAR_VALIDATA == $stato) {
                $voceMenu["label"] = "Carica variazione firmata";
                $voceMenu["path"] = $this->generateUrl("carica_variazione_firmata", ["id_variazione" => $this->variazione->getId()]);
                $vociMenu[] = $voceMenu;
            }

            //invalidazione
            if ((StatoVariazione::VAR_VALIDATA == $stato || StatoVariazione::VAR_FIRMATA == $stato)) {
                $voceMenu["label"] = "Invalida";
                $voceMenu["path"] = $this->generateUrl("invalida_variazione", ["id_variazione" => $this->variazione->getId(), "_token" => $token]);
                $voceMenu["attr"] = "data-confirm=\"Confermi l'invalidazione della variazione?\" data-target=\"#dataConfirmModal\" data-toggle=\"modal\"";
                $vociMenu[] = $voceMenu;
            }
        }

        return $vociMenu;
    }

    public function inviaVariazione(): Response {
        if (!$this->validaVariazioneInviabile()->getEsito()) {
            throw new SfingeException("La validazione non è valida");
        }

        $this->variazione->setDataInvio(new \DateTime());
        /*
        * Popolamento tabelle protocollazione
        */
        if ($this->container->getParameter("stacca_protocollo_al_volo")) {
            //stacca protocollo
            $this->container->get("docerinitprotocollazione")->setTabProtocollazioneVariazione($this->variazione);
        }

        $this->operazioniSpecificheInvioVariazione();

        $em = $this->getEm();
        $connection = $em->getConnection();
        try {
            $connection->beginTransaction();
            $this->getEm()->persist($this->variazione);
            $this->container->get("sfinge.stati")->avanzaStato($this->variazione, StatoVariazione::VAR_INVIATA_PA);
            $this->getEm()->flush();
            $connection->commit();
        } catch (\Exception $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }
            $this->container->get('logger')->error($e->getTraceAsString());
            $this->addError('Errore durante il salvataggio delle informazioni');
        }

        return $this->addSuccesRedirect("Variazione inviata correttamente",
                                        "dettaglio_variazione",
                                        [
                                            'id_variazione' => $this->variazione->getId(),
                                        ]
        );
    }

    public function controllaValiditaVariazione(): EsitoValidazione {
        $esitiSezioni = [
            $this->validaDatiGenerali(),
            $this->validaDocumenti(),
        ];
        /** Le voci delle sezioni sono cancellate
         * @var EsitoValidazione
         */
        $esito = \array_reduce($esitiSezioni, function (EsitoValidazione $carry, EsitoValidazione $esito) {
            $res = $carry->merge($esito);

            return $res;
        }, new EsitoValidazione(true));
        $esito->setSezione('Principale');

        return $esito;
    }

    abstract protected function operazioniSpecificheInvioVariazione(): void;
}
