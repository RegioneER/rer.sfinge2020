<?php

/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 21/01/16
 * Time: 17:23
 */

namespace RichiesteBundle\Service;

use BaseBundle\Entity\StatoRichiesta;
use BaseBundle\Exception\SfingeException;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use DocumentoBundle\Entity\TipologiaDocumento;
use Exception;
use FascicoloBundle\Entity\Fascicolo;
use PdfBundle\Wrapper\PdfWrapper;
use Performer\PayERBundle\Entity\MarcaDaBollo;
use Performer\PayERBundle\Service\EBolloInterface;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Entity\Fornitore;
use RichiesteBundle\Entity\FornitoreServizio;
use RichiesteBundle\Utility\EsitoValidazione;
use SfingeBundle\Entity\Procedura;
use SfingeBundle\Entity\Utente;
use SoggettoBundle\Entity\Soggetto;
use SoggettoBundle\Entity\TipoIncarico;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use RichiesteBundle\Entity\DocumentoRichiesta;
use DocumentoBundle\Entity\DocumentoFile;
use RichiesteBundle\Form\DatiProgettoType;
use DocumentoBundle\Component\ResponseException;
use Symfony\Component\Routing\Generator\UrlGenerator;
use MonitoraggioBundle\Service\GestoreIndicatoreService;
use RichiesteBundle\Form\FornitoreType;
use RichiesteBundle\Entity\Intervento;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class GestoreRichiestaBase extends AGestoreRichiesta {

    /**
     * @return ArrayCollection
     */
    public function getProponenti() {
        return $this->getRichiesta()->getProponenti();
    }

    public function getPianiDeiCosti() {
        // TODO: Implement getPianiDeiCosti() method.
    }

    /**
     * metodo che torna un array con in chiave la label da mostrare nel link e il link a cui andare
     * @param $id_richiesta
     * @return array
     */
    public function dammiVociMenuElencoRichieste($id_richiesta) {
        $csrfTokenManager = $this->container->get("security.csrf.token_manager");
        $token = $csrfTokenManager->getToken("token")->getValue();
        //viene anche usato nell'elenco richieste quindi inietto il parametro id_richiesta
        $this->container->get("request_stack")->getCurrentRequest()->attributes->set("id_richiesta", $id_richiesta);
        $vociMenu = array();
        /** @var Richiesta $richiesta */
        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);

        if (!is_null($richiesta->getStato())) {
            $stato = $richiesta->getStato()->getCodice();
            if ($stato == StatoRichiesta::PRE_INSERITA && $this->isBeneficiario()) {
                $voceMenu["label"] = "Compila";
                $voceMenu["path"] = $this->generateUrl("dettaglio_richiesta", array("id_richiesta" => $id_richiesta));
                $vociMenu[] = $voceMenu;

                $voceMenu["label"] = "Genera domanda pdf";
                $voceMenu["path"] = $this->generateUrl("genera_pdf", array("id_richiesta" => $id_richiesta));
                $vociMenu[] = $voceMenu;

                //validazione
                $esitoValidazione = $this->controllaValiditaRichiesta($id_richiesta);

                if ($esitoValidazione->getEsito()) {
                    $voceMenu["label"] = "Valida";
                    $voceMenu["path"] = $this->generateUrl("valida_richiesta", array("id_richiesta" => $id_richiesta, "_token" => $token));
                    $vociMenu[] = $voceMenu;
                }
            } else {
                $voceMenu["label"] = "Visualizza";
                $voceMenu["path"] = $this->generateUrl("dettaglio_richiesta", array("id_richiesta" => $id_richiesta));
                $vociMenu[] = $voceMenu;
            }

            //scarica pdf domanda
            if ($stato != StatoRichiesta::PRE_INSERITA && is_null($richiesta->getIdSfinge2013())) {
                $voceMenu["label"] = "Scarica domanda";
                $voceMenu["path"] = $this->generateUrl("scarica_domanda", array("id_richiesta" => $id_richiesta));
                $vociMenu[] = $voceMenu;
            }

            //carica richiesta firmata
            if ($stato == StatoRichiesta::PRE_VALIDATA && $this->isBeneficiario()) {
                $voceMenu["label"] = "Carica domanda firmata";
                $voceMenu["path"] = $this->generateUrl("carica_domanda_firmata", array("id_richiesta" => $id_richiesta));
                $vociMenu[] = $voceMenu;
            }


            if (($stato == StatoRichiesta::PRE_FIRMATA || $stato == StatoRichiesta::PRE_INVIATA_PA || $stato == StatoRichiesta::PRE_PROTOCOLLATA) && $richiesta->getProcedura()->isRichiestaFirmaDigitale()) {
                $voceMenu["label"] = "Scarica domanda firmata";
                $voceMenu["path"] = $this->generateUrl("scarica_domanda_firmata", array("id_richiesta" => $id_richiesta));
                $vociMenu[] = $voceMenu;
            }

            if (($stato == StatoRichiesta::PRE_FIRMATA || $stato == StatoRichiesta::PRE_INVIATA_PA || $stato == StatoRichiesta::PRE_PROTOCOLLATA) && $richiesta->getProcedura()->isRichiestaFirmaDigitale()) {
                $voceMenu["label"] = "Sezione documenti";
                $voceMenu["path"] = $this->generateUrl("elenco_documenti_caricati", array("id_richiesta" => $id_richiesta));
                $vociMenu[] = $voceMenu;
            }
            //invio alla pa
            if ($stato == StatoRichiesta::PRE_FIRMATA && $this->isBeneficiario()) {
                $dataClickDay = $richiesta->getProcedura()->getDataClickDay();
                $now = new DateTime();
                if (empty($dataClickDay) || ($now >= $dataClickDay)) {
                    $voceMenu["label"] = "Invia domanda";
                    $voceMenu["path"] = $this->generateUrl("invia_richiesta", array("id_richiesta" => $id_richiesta, "_token" => $token));
                    $voceMenu["attr"] = "data-confirm=\"Continuando non sarà più possibile modificare la richiesta nemmeno dall'assistenza tecnica. Si intende procedere comunque?\" data-target=\"#dataConfirmModal\" data-toggle=\"modal\"";
                    $vociMenu[] = $voceMenu;
                }
            }

            //invalidazione
            if (($stato == StatoRichiesta::PRE_VALIDATA || $stato == StatoRichiesta::PRE_FIRMATA) && $this->isBeneficiario()) {
                $voceMenu["label"] = "Invalida";
                $voceMenu["path"] = $this->generateUrl("invalida_richiesta", array("id_richiesta" => $id_richiesta, "_token" => $token));
                $voceMenu["attr"] = "data-confirm=\"Confermi l'invalidazione della richiesta?\" data-target=\"#dataConfirmModal\" data-toggle=\"modal\"";
                $vociMenu[] = $voceMenu;
            }

            if ($this->isRichiestaEliminabile($richiesta)) {
                $voceMenu["label"] = "Elimina";
                $voceMenu["path"] = $this->generateUrl("elimina_richiesta", array("id_richiesta" => $id_richiesta, "_token" => $token));
                $voceMenu["attr"] = 'data-confirm="Confermi l\'eliminazione della richiesta?" data-target="#dataConfirmModal" data-toggle="modal"';
                $vociMenu[] = $voceMenu;
            }
        }

        return $vociMenu;
    }

    protected function isRichiestaEliminabile(Richiesta $richiesta): bool {
        $bandiIrap = [118, 125];
        $bandoIrap = in_array($richiesta->getProcedura()->getId(), $bandiIrap) && \in_array(
                $richiesta->getStato()->getCodice(), [
                StatoRichiesta::PRE_INSERITA,
                StatoRichiesta::PRE_VALIDATA,
                StatoRichiesta::PRE_FIRMATA
        ]);
        $istruttoria = $richiesta->getIstruttoria();
        $eliminabileDaAssistenza = $this->isGranted('ROLE_SUPER_ADMIN') &&
            (
            \is_null($istruttoria) ||
            \is_null($istruttoria->getEsito())
            );

        return $bandoIrap || $eliminabileDaAssistenza;
    }

    public function getTipiDocumenti($id_richiesta, $solo_obbligatori) {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        $procedura_id = $richiesta->getProcedura()->getId();
        $res = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->ricercaDocumentiRichiesta($id_richiesta, $procedura_id, $solo_obbligatori);
        if (!$solo_obbligatori) {
            $tipologie_con_duplicati = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")->findBy(array("abilita_duplicati" => 1, "procedura" => $richiesta->getProcedura(), "tipologia" => 'richiesta'));
            $res = array_merge($res, $tipologie_con_duplicati);
        }

        return $res;
    }

    public function nuovaRichiesta($id_bando, $opzioni = array()) {
        // TODO: Implement nuovaRichiesta() method.
    }

    public function dettaglioRichiesta($id_richiesta, $opzioni = array()) {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        if (is_null($richiesta)) {
            $this->addErrorRedirect("La richiesta non è stata trovata", "elenco_richieste");
        }

        $sezioni_aggiuntive = $richiesta->getProcedura()->getSezioniAggiuntive();

        $dati["richiesta"] = $richiesta;

        if (!is_null($sezioni_aggiuntive)) {
            $dati["sezioni_aggiuntive"] = $sezioni_aggiuntive;
        }
        /** @var \MonitoraggioBundle\Service\IgestoreIterProgetto $gestoreIterProgetto */
        $gestoreIterProgetto = $this->container->get('monitoraggio.iter_progetto')->getIstanza($richiesta);

        $dati["oggetti_richiesta"] = $richiesta->getOggettiRichiesta();
        $dati["proponenti"] = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->getProponentiRichiesta($id_richiesta);
        $dati["mandatario"] = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->getMandatarioRichiesta($id_richiesta);
        $dati["piano_costo_attivo"] = $richiesta->getProcedura()->getPianoCostoAttivo();
        $dati["multi_piano_costo"] = $richiesta->getProcedura()->getMultiPianoCosto();
        $dati["priorita"] = $this->hasSezionePriorita();
        $dati["ambiti_prioritari_s3"] = $richiesta->getProcedura()->getSezioneAmbitiTematiciS3();
        $dati["avanzamenti"] = $this->gestioneBarraAvanzamento();
        $dati["fornitori"] = $this->hasSezioneFornitori();
        $dati["interventi"] = $this->hasSezioneInterventi($richiesta);
        $dati["risorse_progetto"] = $this->hasRisorseProgetto($richiesta);
        $dati['iter_progetto'] = $gestoreIterProgetto->hasSezioneRichiestaVisibile();
        $dati['obiettivi_realizzativi'] = $this->hasObiettiviRealizzativi();
        $dati["dnsh"] = $this->hasDichiarazioniDsnh();
        $dati["has_autodichiarazioni"] = $this->hasAutodichiarazioni($richiesta);

        // Aggiungo i dati inviati dai metodi personalizzati.
        foreach ($opzioni as $key => $value) {
            $dati[$key] = $value;
        }

        $twig = isset($opzioni["twig"]) ? $opzioni["twig"] : "RichiesteBundle:Richieste:mainRichiesta.html.twig";
        $response = $this->render($twig, $dati);
        return new GestoreResponse($response);
    }

    protected function hasObiettiviRealizzativi(): bool {
        return false;
    }

    /**
     * @param Richiesta $richiesta
     * @return bool
     */
    public function hasAutodichiarazioni(Richiesta $richiesta): bool
    {
        $elencoAutodichiarazioni = $this->getEm()->getRepository("RichiesteBundle:Autodichiarazioni\ElencoProceduraRichiesta")
            ->findOneBy(['procedura' => $richiesta->getProcedura()]);
        return (bool) $elencoAutodichiarazioni;
    }

    /**
     * @param $id_richiesta
     * @param $opzioni
     * @return GestoreResponse|RedirectResponse
     * @throws SfingeException
     */
    public function datiMarcaDaBollo($id_richiesta, $opzioni = []) {
        /** @var Richiesta $richiesta */
        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        $ebollo = $this->container->get(EBolloInterface::class);
        if ($richiesta->getAcquistoMarcaDaBollo() && $richiesta->getAcquistoMarcaDaBollo()->isInAttesaEsitoPagamento() && $richiesta->getAcquistoMarcaDaBollo()->getRichiesta()
        ) {
            $ebollo->aggiornaEsito($richiesta->getAcquistoMarcaDaBollo()->getRichiesta());
        }

        if (!$richiesta->getProcedura()->getMarcaDaBollo()) {
            return new GestoreResponse($this->redirectToRoute("dettaglio_richiesta", ["id_richiesta" => $richiesta->getId()]));
        }

        $request = $this->getCurrentRequest();

        $opzioni["url_indietro"] = $this->generateUrl("dettaglio_richiesta", ["id_richiesta" => $richiesta->getId()]);
        $opzioni["marca_da_bollo"] = is_null($richiesta->getProcedura()->isMarcaDaBollo()) ? false : $richiesta->getProcedura()->isMarcaDaBollo();
        $opzioni["tipologia_marca_da_bollo"] = $richiesta->getProcedura()->getTipologiaMarcaDaBollo();
        $opzioni["esenzione_marca_bollo"] = is_null($richiesta->getProcedura()->getEsenzioneMarcaBollo()) ? false : $richiesta->getProcedura()->getEsenzioneMarcaBollo();
        $opzioni["numero_marca_da_bollo_digitale"] = $richiesta->getNumeroMarcaDaBolloDigitale();
        $opzioni["disabled"] = $this->isRichiestaDisabilitata();
        $form = $this->createForm("RichiesteBundle\Form\MarcaDaBolloType", $richiesta, $opzioni);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($opzioni["esenzione_marca_bollo"]) {
                $riferimentoNormativo = $richiesta->getRiferimentoNormativoEsenzione();
                if ($richiesta->getEsenteMarcaDaBollo() && empty($riferimentoNormativo)) {
                    $form->get('riferimento_normativo_esenzione')
                        ->addError(new FormError('In caso di esenzione è necessario specificare il riferimento normativo'));
                }
            }

            if ($form->isValid()) {
                $em = $this->getEm();
                try {
                    if ($richiesta->getProcedura()->getEsenzioneMarcaBollo() && $form->get('esente_marca_da_bollo')->getData() === false) {
                        if ($richiesta->getProcedura()->getTipologiaMarcaDaBollo() == Procedura::MARCA_DA_BOLLO_FISICA) {
                            $richiesta->setTipologiaMarcaDaBollo(Procedura::MARCA_DA_BOLLO_FISICA);
                        } elseif ($richiesta->getProcedura()->getTipologiaMarcaDaBollo() == Procedura::MARCA_DA_BOLLO_DIGITALE) {
                            $richiesta->setTipologiaMarcaDaBollo(Procedura::MARCA_DA_BOLLO_DIGITALE);
                        }
                    }

                    $em->persist($richiesta);
                    $em->flush();

                    if ($richiesta->getTipologiaMarcaDaBollo() == Procedura::MARCA_DA_BOLLO_DIGITALE
                    ) {
                        if (is_null($richiesta->getAcquistoMarcaDaBollo()) || $richiesta->getAcquistoMarcaDaBollo()->isPagamentoFallito()
                        ) {
                            $fileDocumentoMarcaDaBolloDigitale = file_get_contents($richiesta->getDocumentoMarcaDaBolloDigitale()->getPath() . $richiesta->getDocumentoMarcaDaBolloDigitale()->getNome());
                            $acquistoMarcaDaBollo = $ebollo->createAcquistoMarcaDaBollo(
                                MarcaDaBollo::MDB_16_00,
                                base64_encode($fileDocumentoMarcaDaBolloDigitale),
                                $richiesta->getMandatario()->getSoggetto()->getCodiceFiscale(),
                                $richiesta->getMandatario()->getSoggetto()->getDenominazione(),
                                $richiesta->getMandatario()->getSoggetto()->getComune()->getProvincia()->getSiglaAutomobilistica(),
                                $richiesta->getMandatario()->getSoggetto()->getEmail()
                            );

                            $richiesta->setAcquistoMarcaDaBollo($acquistoMarcaDaBollo);
                        }
                    } else {
                        $richiesta->setAcquistoMarcaDaBollo(null);
                    }

                    $em->persist($richiesta);
                    $em->flush();

                    if ($form->getClickedButton()->getName() == 'pulsante_submit_e_paga_marca_da_bollo_digitale' && is_null($richiesta->getNumeroMarcaDaBolloDigitale())) {
                        if (!$richiesta->getAcquistoMarcaDaBollo()->hasEsitoPagamento() || !$richiesta->getAcquistoMarcaDaBollo()->isPagamentoEseguito()) {
                            return $this->redirectToRoute('performer.pay_er.ebollo_acquista', [
                                    'identificativoVersante' => $this->getUser()->getPersona()->getCodiceFiscale(),
                                    'denominazioneVersante' => $this->getUser()->getPersona()->getNomeCognome(),
                                    'emailVersante' => $this->getUser()->getPersona()->getEmailPrincipale(),
                                    'acquistoMarcaDaBollos' => [$richiesta->getAcquistoMarcaDaBollo()->getId()],
                                    'urlRitorno' => $this->generateUrl('dati_marca_da_bollo', ['id_richiesta' => $richiesta->getId()], UrlGenerator::ABSOLUTE_URL),
                            ]);
                        }
                    }

                    return new GestoreResponse($this
                            ->addSuccessRedirect('Dati marca da bollo salvati correttamente', 'dettaglio_richiesta', [
                                'id_richiesta' => $richiesta->getId()
                    ]));
                } catch (Exception $e) {
                    throw new SfingeException('Dati marca da bollo non salvati');
                }
            }
        }

        $dati = [
            'richiesta' => $richiesta,
            'form' => $form->createView(),
            'esenzione_marca_bollo' => $opzioni['esenzione_marca_bollo'],
            'tipologia_marca_da_bollo' => $opzioni['tipologia_marca_da_bollo'],
            'numero_marca_da_bollo_digitale' => $opzioni['numero_marca_da_bollo_digitale'],
        ];

        $response = $this->render("RichiesteBundle:Richieste:datiMarcaDaBollo.html.twig", $dati);
        return new GestoreResponse($response, "RichiesteBundle:Richieste:datiMarcaDaBollo.html.twig", $dati);
    }

    public function validaDatiMarcaDaBollo(Richiesta $richiesta, $opzioni = array()) {
        $esito = new EsitoValidazione(true);

        $statoRichiesta = $richiesta->getStato();
        if ($statoRichiesta && ($statoRichiesta->uguale(StatoRichiesta::PRE_INVIATA_PA) || $statoRichiesta->uguale(StatoRichiesta::PRE_PROTOCOLLATA))
        ) {
            return $esito;
        }

        $procedura = $richiesta->getProcedura();

        if ($procedura->isMarcaDaBollo()) {
            if (is_null($richiesta->isEsenteMarcaDaBollo()) && $procedura->getEsenzioneMarcaBollo()) {
                $esito->addMessaggioSezione("Indicare se si è esenti o meno dal pagamento della marca da bollo", "esenzione_marca_da_bollo");
                $esito->setEsito(false);
            } elseif (!$richiesta->isEsenteMarcaDaBollo() && ($procedura->getTipologiaMarcaDaBollo() == Procedura::MARCA_DA_BOLLO_FISICA || $richiesta->getTipologiaMarcaDaBollo() == Procedura::MARCA_DA_BOLLO_FISICA)) {
                $dataMarcaDaBollo = $richiesta->getDataMarcaDaBollo();
                $numeroMarcaDaBollo = $richiesta->getNumeroMarcaDaBollo();

                if (empty($dataMarcaDaBollo)) {
                    if ($procedura->getEsenzioneMarcaBollo()) {
                        $esito->addMessaggioSezione("La data della marca da bollo deve essere indicata qualora non ci sia l’esenzione", "data_marca_da_bollo");
                    } else {
                        $esito->addMessaggioSezione("Indicare la data della marca da bollo", "data_marca_da_bollo");
                    }

                    $esito->setEsito(false);
                }

                if (empty($numeroMarcaDaBollo)) {
                    if ($procedura->getEsenzioneMarcaBollo()) {
                        $esito->addMessaggioSezione("Il numero della marca da bollo deve essere indicato qualora non ci sia l’esenzione", "numero_marca_da_bollo");
                    } else {
                        $esito->addMessaggioSezione("Indicare  il numero della marca da bollo", "numero_marca_da_bollo");
                    }

                    $esito->setEsito(false);
                }

                if (!empty($numeroMarcaDaBollo)) {
                    $rgxMarcaBollo = "/^[0-9]{14}+$/";
                    if (!preg_match($rgxMarcaBollo, $numeroMarcaDaBollo)) {
                        $esito->addMessaggioSezione("Il numero della marca da bollo deve essere un valore numerico di lunghezza 14", "numero_marca_da_bollo");
                        $esito->setEsito(false);
                    }
                }
            } elseif (!$richiesta->isEsenteMarcaDaBollo() && ($procedura->getTipologiaMarcaDaBollo() == Procedura::MARCA_DA_BOLLO_DIGITALE || $richiesta->getTipologiaMarcaDaBollo() == Procedura::MARCA_DA_BOLLO_DIGITALE)) {
                $numeroMarcaDaBolloDigitale = $richiesta->getNumeroMarcaDaBolloDigitale();

                if (empty($numeroMarcaDaBolloDigitale)) {
                    if ($procedura->getTipologiaMarcaDaBollo() == Procedura::MARCA_DA_BOLLO_DIGITALE) {
                        $esito->addMessaggioSezione("È necessario entrare nella sezione per eseguire il pagamento digitale della marca da bollo", "numero_marca_da_bollo");
                        $esito->setEsito(false);
                    } else {
                        // È un brutta "soluzione" ma devo permettere il salvataggio della form e prevedere
                        // l'acquisto della marca da bollo digitale in un secondo tempo.
                        // In questo modo nel salvataggio della form permetto che non sia presente il numero della
                        // marca da bollo digitale ma il controllo viene eseguito in maniera completa per la
                        // validità della sezione.
                        if (empty($_POST)) {
                            $esito->addMessaggioSezione("Si è scelto di acquistare la marca da bollo digitale ma non è ancora stato effettuato l’acquisto della stessa", "numero_marca_da_bollo");
                            $esito->setEsito(false);
                        }
                    }
                }
            } elseif ($procedura->getTipologiaMarcaDaBollo() == Procedura::MARCA_DA_BOLLO_FISICA_E_DIGITALE && !$richiesta->isEsenteMarcaDaBollo() && is_null($richiesta->getTipologiaMarcaDaBollo())) {
                $esito->addMessaggioSezione("Indicare la tipologia di marca da bollo", "tipologia_marca_da_bollo");
                $esito->setEsito(false);
            } else {
                if (is_null($richiesta->getRiferimentoNormativoEsenzione())) {
                    $esito->addMessaggioSezione("Indicare il riferimento normativo dell’esenzione", "riferimento_normativo_esenzione");
                    $esito->setEsito(false);
                }
            }
        }

        return $esito;
    }

    public function datiGenerali($id_richiesta, $opzioni = array()) {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);

        if (!$richiesta->getProcedura()->isSezioneDatiGenerali()) {
            return new GestoreResponse($this->redirectToRoute("dettaglio_richiesta", array("id_richiesta" => $richiesta->getId())));
        }

        $request = $this->getCurrentRequest();

        $opzioni["url_indietro"] = $this->generateUrl("dettaglio_richiesta", array("id_richiesta" => $richiesta->getId()));
        $opzioni["rating"] = is_null($richiesta->getProcedura()->isRating()) ? false : $richiesta->getProcedura()->isRating();
        $opzioni["requisiti_rating"] = is_null($richiesta->getProcedura()->isRequisitiRating()) ? false : $richiesta->getProcedura()->isRequisitiRating();
        $opzioni["femminile"] = is_null($richiesta->getProcedura()->isFemminile()) ? false : $richiesta->getProcedura()->isFemminile();
        $opzioni["giovanile"] = is_null($richiesta->getProcedura()->isGiovanile()) ? false : $richiesta->getProcedura()->isGiovanile();
        $opzioni["incremento_occupazionale"] = is_null($richiesta->getProcedura()->isIncrementoOccupazionale()) ? false : $richiesta->getProcedura()->isIncrementoOccupazionale();
        $opzioni["dati_incremento_occupazionale"] = is_null($richiesta->getProcedura()->isDatiIncrementoOccupazionale()) ? false : $richiesta->getProcedura()->isDatiIncrementoOccupazionale();
        $opzioni["stelle"] = is_null($richiesta->getProcedura()->getStelle()) ? false : $richiesta->getProcedura()->getStelle();
        $opzioni["sede_montana"] = is_null($richiesta->getProcedura()->getSedeMontana()) ? false : $richiesta->getProcedura()->getSedeMontana();
        $opzioni["disabled"] = $this->isRichiestaDisabilitata();
        $form = $this->createForm("RichiesteBundle\Form\DatiGeneraliType", $richiesta, $opzioni);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($opzioni["incremento_occupazionale"]) {
                $numeroDipendentiAttuale = $richiesta->getNumeroDipendentiAttuale();
                $numeroNuoveUnita = $richiesta->getNumeroNuoveUnita();

                if ($richiesta->getProcedura()->isDatiIncrementoOccupazionale() && $richiesta->isIncrementoOccupazionale()) {
                    if ($numeroDipendentiAttuale === null && $numeroNuoveUnita === null) {
                        $form->get('incremento_occupazionale')->addError(new FormError('In caso di incremento occupazionale è necessario specificare l’attuale numero di dipendenti ed il numero di nuove unità previste'));
                    } elseif ($numeroDipendentiAttuale === null) {
                        $form->get('incremento_occupazionale')->addError(new FormError('In caso di incremento occupazionale è necessario specificare l’attuale numero di dipendenti'));
                    } elseif (empty($numeroNuoveUnita)) {
                        $form->get('incremento_occupazionale')->addError(new FormError('In caso di incremento occupazionale è necessario specificare il numero di nuove unità previste (il valore non può essere 0)'));
                    }
                }
            }

            if ($form->isValid()) {
                $em = $this->getEm();
                try {
                    $em->persist($richiesta);
                    $em->flush();

                    if (array_key_exists( 'salva_contributo', $opzioni) && $opzioni['salva_contributo']) {
                        // cerco un gestore per quel bando
                        $nomeClasse = "RichiesteBundle\GestoriPianiCosto\GestorePianoCostoBando_"
                            . $richiesta->getProcedura()->getId();
                        try {
                            $gestorePianoCostoBando = new $nomeClasse($this->container);
                            $contributo = $gestorePianoCostoBando->calcolaContributo($richiesta);
                            $richiesta->setContributoRichiesta($contributo);
                            $em->persist($richiesta);
                            $em->flush();
                        } catch (Exception $e) {
                            $this->addError("GestorePianoCostoBando_"
                                . $richiesta->getProcedura()->getId() . ' non presente.');
                        }
                    }

                    return new GestoreResponse($this->addSuccesRedirect("Dati generali salvati correttamente", "dettaglio_richiesta", array("id_richiesta" => $richiesta->getId())));
                } catch (\Exception $e) {
                    throw new SfingeException("Dati generali non salvati");
                }
            }
        }

        $dati = [
            'id_richiesta' => $richiesta->getId(),
            'form' => $form->createView(),
            'incremento_occupazionale' => $opzioni['incremento_occupazionale'],
        ];

        $response = $this->render("RichiesteBundle:Richieste:datiGenerali.html.twig", $dati);

        return new GestoreResponse($response, "RichiesteBundle:Richieste:datiGenerali.html.twig", $dati);
    }

    /**
     * @param Richiesta $richiesta
     * @param $opzioni
     * @return EsitoValidazione
     */
    public function validaDatiGenerali(Richiesta $richiesta, $opzioni = array()): EsitoValidazione
    {
        $esito = new EsitoValidazione(true);
        $statoRichiesta = $richiesta->getStato();
        if ($statoRichiesta && ($statoRichiesta->uguale(StatoRichiesta::PRE_INVIATA_PA) || $statoRichiesta->uguale(StatoRichiesta::PRE_PROTOCOLLATA))) {
            return $esito;
        }

        if ($richiesta->getProcedura()->getStelle() == 1 && $richiesta->getRating() == 1 && $richiesta->getStelleRating() == 0) {
            $esito->addMessaggioSezione("Il numero di stelle deve essere indicato se il soggetto è in possesso del rating di legalità", "stelle_rating");
            $esito->setEsito(false);
        }

        if ($richiesta->getProcedura()->getStelle() == 1 && $richiesta->getRating() == 0 && $richiesta->getStelleRating() > 0) {
            $esito->addMessaggioSezione("Il numero di stelle deve essere indicato solo se il soggetto è in possesso del rating di legalità", "stelle_rating");
            $esito->setEsito(false);
        }

        if ($richiesta->getProcedura()->getRequisitiRating() == 1 && $richiesta->getRating() == 1 && $richiesta->getRequisitiRating() == 0) {
            $esito->addMessaggioSezione("Campo obbligatorio se si è in possesso del rating di legalità", "requisiti_rating");
            $esito->setEsito(false);
        }

        /** Fix temporaneo */
        if ($richiesta->getProcedura()->getId() == 183) {
            if ($richiesta->getProcedura()->getRating() == 1 && is_null($richiesta->getRating())) {
                $esito->addMessaggioSezione("Indicare se si è in possesso del rating di legalità", "requisiti_rating");
                $esito->setEsito(false);
            }
        }

        if ($richiesta->getProcedura()->getFemminile() == 1 && is_null($richiesta->getFemminile())) {
            $esito->addMessaggioSezione("Indicare se si possiede la caratteristica di impresa femminile", "femminile");
            $esito->setEsito(false);
        }

        if ($richiesta->getProcedura()->getGiovanile() == 1 && is_null($richiesta->getGiovanile())) {
            $esito->addMessaggioSezione("Indicare se si possiede la caratteristica di impresa giovanile", "giovanile");
            $esito->setEsito(false);
        }

        return $esito;
    }

    public function gestioneDatiProgetto($id_richiesta, $opzioni = array()) {
        $em = $this->getEm();
        $richiesta = $em->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        $request = $this->getCurrentRequest();
        if (is_null($richiesta)) {
            $this->addErrorRedirect("La richiesta non è stata trovata", "elenco_richieste");
        }
        $opzioni['url_indietro'] = $this->generateUrl("dettaglio_richiesta", array("id_richiesta" => $richiesta->getId()));
        $opzioni["disabled"] = $this->isRichiestaDisabilitata();

        $class_type = DatiProgettoType::class;
        if (array_key_exists('form_type', $opzioni)) {
            $class_type = $opzioni['form_type'];
            unset($opzioni['form_type']);
        }
        $form = $this->createForm($class_type, $richiesta, $opzioni);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getEm();
                try {
                    $em->beginTransaction();
                    $em->persist($richiesta);
                    $em->flush();
                    $em->commit();
                    return new GestoreResponse($this->addSuccesRedirect("Dati del progetto modificati correttamente", "dettaglio_richiesta", array("id_richiesta" => $richiesta->getId()))
                    );
                } catch (\Exception $e) {
                    $em->rollback();
                    throw new SfingeException("Dati del progetto non modificati");
                }
            }
        }

        $dati = array("id_richiesta" => $richiesta->getId(), "form" => $form->createView());

        $response = $this->render("RichiesteBundle:Richieste:datiProgetto.html.twig", $dati);

        return new GestoreResponse($response, "RichiesteBundle:Richieste:datiProgetto.html.twig", $dati);
    }

    public function gestioneDichiarazioniDnsh($id_richiesta, $opzioni = array()) {
        $em = $this->getEm();
        $richiesta = $em->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        $request = $this->getCurrentRequest();
        if (is_null($richiesta)) {
            $this->addErrorRedirect("La richiesta non è stata trovata", "elenco_richieste");
        }
        //nel caso serva il codice conviene passarlo alle opzioni da metodo overridato
        if (!empty($opzioni['codice_dnsh'])) {
            $testi = $em->getRepository("RichiesteBundle:DnshProcedura")->findOneBy(array("procedura" => $richiesta->getProcedura(), 'codice' => $opzioni['codice_dnsh']));
        } else {
            $testi = $em->getRepository("RichiesteBundle:DnshProcedura")->findOneBy(array("procedura" => $richiesta->getProcedura()));
        }

        $opzioni['url_indietro'] = $this->generateUrl("dettaglio_richiesta", array("id_richiesta" => $richiesta->getId()));
        $opzioni["disabled"] = $this->isRichiestaDisabilitata();

        $opzioni["non_arreca"] = $testi->getTestoNonArreca();
        $opzioni["adotta_misure"] = $testi->getTestoAdottaMisure();
        $opzioni["specifica_documentazione"] = $testi->getTestoSpecificaDocumentazione();

        $class_type = \RichiesteBundle\Form\DichirazioniDnshType::class;
        if (array_key_exists('form_type', $opzioni)) {
            $class_type = $opzioni['form_type'];
            unset($opzioni['form_type']);
        }
        $dich = $richiesta->getDichiarazioneDnsh();
        $form = $this->createForm($class_type, $dich, $opzioni);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            $dnsh = $dich;
            $nonArreca = $dnsh->getNonArreca();
            $adottaMisure = $dnsh->getAdottaMisure();
            $produceDocumentazione = $dnsh->getSpecificaDocumentazione();

            if (!$nonArreca && !$adottaMisure && !$produceDocumentazione) {
                $form->addError(new FormError("Selezionare almeno un'opzione"));
            }

            if ($dnsh->hasAdottaMisure() && (!$dnsh->getDescrizioneAdottaMisure() || $dnsh->getDescrizioneAdottaMisure() == '')) {
                $form->addError(new FormError("Indicare la descrizione della seconda dichiarazione"));
            }

            if ($dnsh->hasSpecificaDocumentazione() && (!$dnsh->getDescrizioneSpecificaDocumentazione() || $dnsh->getDescrizioneSpecificaDocumentazione() == '')) {
                $form->addError(new FormError("Indicare la descrizione della terza dichiarazione"));
            }

            if (!$dnsh->hasAdottaMisure() && (!is_null($dnsh->getDescrizioneAdottaMisure()) || $dnsh->getDescrizioneAdottaMisure() != '')) {
                $form->addError(new FormError("Se non selezionato non è possibile indicare la descrizione della seconda dichiarazione"));
            }

            if (!$dnsh->hasSpecificaDocumentazione() && (!is_null($dnsh->getDescrizioneSpecificaDocumentazione()) || $dnsh->getDescrizioneSpecificaDocumentazione() != '')) {
                $form->addError(new FormError("Se non selezionato non è possibile indicare la descrizione della terza dichiarazione"));
            }


            if ($form->isValid()) {
                $em = $this->getEm();
                try {
                    $em->beginTransaction();
                    $em->persist($richiesta);
                    $em->flush();
                    $em->commit();
                    return new GestoreResponse($this->addSuccesRedirect("Dati del progetto modificati correttamente", "dettaglio_richiesta", array("id_richiesta" => $richiesta->getId()))
                    );
                } catch (\Exception $e) {
                    $em->rollback();
                    throw new SfingeException("Dati del progetto non modificati");
                }
            }
        }

        $dati = array("id_richiesta" => $richiesta->getId(), "form" => $form->createView());

        $response = $this->render("RichiesteBundle:Richieste:dnsh.html.twig", $dati);

        return new GestoreResponse($response, "RichiesteBundle:Richieste:dnsh.html.twig", $dati);
    }

    public function validaDatiProgetto($id_richiesta, $opzioni = array()) {
        //i dati del form sono statici tra i vari bandi pertanto controllo solo che siano valorizzati
        $esito = new EsitoValidazione(true);
        $esito->setSezione("Dati generali");

        $richiesta = $this->getRichiesta();

        $titolo = $richiesta->getTitolo();
        $abstract = $richiesta->getAbstract();
        $validator = $this->container->get('validator');

        if (empty($titolo)) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Il titolo del progetto deve essere valorizzato");
        } else {
            if (array_key_exists('titolo_max_length', $opzioni)) {
                $violations = $validator->validate($titolo, array(
                    new \BaseBundle\Validator\Constraints\ValidaLunghezza(array('min' => 5, 'max' => $opzioni["titolo_max_length"])),
                ));
                if (0 !== count($violations)) {
                    $esito->setEsito(false);
                    $esito->addMessaggioSezione("Il titolo non può essere più lungo di " . $opzioni["titolo_max_length"] . " caratteri");
                }
            }
        }

        if (empty($abstract)) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("L'abstract del progetto deve essere valorizzato");
        } else {
            if (array_key_exists('abstract_max_length', $opzioni)) {
                $violations = $validator->validate($abstract, array(
                    new \BaseBundle\Validator\Constraints\ValidaLunghezza(array('min' => 5, 'max' => $opzioni["abstract_max_length"])),
                ));
                if (0 !== count($violations)) {
                    $esito->setEsito(false);
                    $esito->addMessaggioSezione("L'abstract non può essere più lungo di " . $opzioni["abstract_max_length"] . " caratteri");
                }
            }
        }

        return $esito;
    }

    public function validaDnsh() {
        //i dati del form sono statici tra i vari bandi pertanto controllo solo che siano valorizzati
        $esito = new EsitoValidazione(true);
        $esito->setSezione("Dichiarazioni dnsh");

        $richiesta = $this->getRichiesta();

        $dnsh = $richiesta->getDichiarazioneDnsh();
        $nonArreca = $dnsh->getNonArreca();
        $adottaMisure = $dnsh->getAdottaMisure();
        $produceDocumentazione = $dnsh->getSpecificaDocumentazione();
        if (!$nonArreca && !$adottaMisure && !$produceDocumentazione) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Selezionare almeno un'opzione");
        }

        if (!$adottaMisure && (!is_null($dnsh->getDescrizioneAdottaMisure()) || $dnsh->getDescrizioneAdottaMisure() != '')) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Descrizione non ammessa in quanto il flag non risulta selezionato");
        }

        if (!$produceDocumentazione && (!is_null($dnsh->getDescrizioneSpecificaDocumentazione()) || $dnsh->getDescrizioneSpecificaDocumentazione() != '')) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Descrizione non ammessa in quanto il flag non risulta selezionato");
        }

        return $esito;
    }

    public function validaObiettiviRealizzativi(Richiesta $richiesta): EsitoValidazione {
        if (!$this->hasObiettiviRealizzativi()) {
            return new EsitoValidazione(true);
        }
        /** @var GestoreObiettiviRealizzativiService $factory */
        $factory = $this->container->get('gestore_obiettivi_realizzativi');
        $gestore = $factory->getGestore($richiesta);
        $esito = $gestore->valida();

        return $esito;
    }

    public function elencoDocumenti($id_richiesta, $opzioni = array())
    {
        $em = $this->getEm();
        $request = $this->getCurrentRequest();

        $documento_richiesta = new DocumentoRichiesta();
        $documento_file = new DocumentoFile();
        $codice_documento_video = $this->getCodiceVideoDiPresentazione($id_richiesta);
        $documenti_caricati = $em->getRepository("RichiesteBundle\Entity\DocumentoRichiesta")->findDocumentiCaricati($id_richiesta);

        // Tolgo gli eventuali video caricati dall'elenco dei documenti
        $documenti_caricati = array_filter($documenti_caricati, function(DocumentoRichiesta $documentoRichiesta) use ($codice_documento_video) {
            return $documentoRichiesta->getDocumentoFile()->getTipologiaDocumento()->getCodice() != $codice_documento_video;
        });

        $richiesta = $em->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
        $listaTipi = $this->getTipiDocumenti($id_richiesta, 0);

        foreach ($listaTipi as $key => $tipo) {
            if ($tipo->getCodice() === $codice_documento_video) {
                unset($listaTipi[$key]);
            }
        }

        if (count($listaTipi) > 0 && !$this->isRichiestaDisabilitata()) {
            $opzioni_form["lista_tipi"] = $listaTipi;

            // Faccio questo controllo perché quando non si carica
            // la richiesta di contributo firmata non si imposta il firmatario
            if ($richiesta->getProcedura()->isRichiestaFirmaDigitale()) {
                $opzioni_form["cf_firmatario"] = $richiesta->getFirmatario()->getCodiceFiscale();
            } else {
                $opzioni_form["cf_firmatario"] = $this->getUser()->getUsername();
            }

            $form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documento_file, $opzioni_form);
            $form->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('label' => 'Salva'));

            if ($request->isMethod('POST')) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    try {

                        $this->container->get("documenti")->carica($documento_file, 0, $richiesta);

                        $documento_richiesta->setDocumentoFile($documento_file);
                        $documento_richiesta->setRichiesta($richiesta);
                        $em->persist($documento_richiesta);

                        $em->flush();
                        return new GestoreResponse($this->addSuccesRedirect("Documento caricato correttamente", "elenco_documenti_richiesta", array("id_richiesta" => $richiesta->getId())));
                    } catch (ResponseException $e) {
                        $this->addFlash('error', $e->getMessage());
                    }
                }
            }
            $form_view = $form->createView();
        } else {
            $form_view = null;
        }

        $dati = array("documenti" => $documenti_caricati, "id_richiesta" => $id_richiesta, "form" => $form_view, 'is_richiesta_disabilitata' => $this->isRichiestaDisabilitata());
        $response = $this->render("RichiesteBundle:Richieste:elencoDocumentiRichiesta.html.twig", $dati);
        return new GestoreResponse($response, "RichiesteBundle:Richieste:elencoDocumentiRichiesta.html.twig", $dati);
    }

    /**
     * @param $id_documento_richiesta
     * @param $opzioni
     * @return GestoreResponse|void
     */
    public function eliminaDocumentoRichiesta($id_documento_richiesta, $opzioni = []) {
        $em = $this->getEm();
        $documento = $em->getRepository("RichiesteBundle\Entity\DocumentoRichiesta")->find($id_documento_richiesta);
        $tipologia = $documento->getDocumentoFile()->getTipologiaDocumento()->getCodice();
        $id_richiesta = $documento->getRichiesta()->getId();
        $codice_documento_video = $this->getCodiceVideoDiPresentazione($id_richiesta);
        try {
            $em->remove($documento->getDocumentoFile());
            $em->remove($documento);
            $em->flush();

            if ($tipologia == $codice_documento_video) {
                return new GestoreResponse($this->addSuccesRedirect("Documento eliminato correttamente", "elenco_documenti_richiesta_dropzone", ["id_richiesta" => $id_richiesta]));
            } else {
                return new GestoreResponse($this->addSuccesRedirect("Documento eliminato correttamente", "elenco_documenti_richiesta", ["id_richiesta" => $id_richiesta]));
            }
        } catch (ResponseException $e) {
            $this->addFlash('error', $e->getMessage());
        }
    }

    public function validaDocumenti($id_richiesta, $opzioni = array()) {
        $esito = new EsitoValidazione(true);
        $documenti_obbligatori = $this->getTipiDocumenti($id_richiesta, 1);
        $codice_documento_video = $this->getCodiceVideoDiPresentazione($id_richiesta);
        
        foreach ($documenti_obbligatori as $key => $documento) {
            if ($documento->getCodice() === $codice_documento_video) {
                unset($documenti_obbligatori[$key]);
            }
        }

        foreach ($documenti_obbligatori as $documento) {
            $esito->addMessaggio('Caricare il documento ' . $documento->getDescrizione());
        }

        if (count($documenti_obbligatori) > 0) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Caricare tutti gli allegati previsti dalla procedura");
        }

        return $esito;
    }

    public function controllaValiditaRichiesta($id_richiesta, $opzioni = array()) {

        //viene anche usato nell'elenco richieste quindi inietto il parametro id_richiesta
        $this->container->get("request_stack")->getCurrentRequest()->attributes->set("id_richiesta", $id_richiesta);
        $richiesta = $this->getRichiesta();

        if (array_key_exists('esito', $opzioni)) {
            $esitiSezioni[] = $opzioni['esito'];
        } else {
            $esitiSezioni = array();
        }

        $esitiSezioni[] = $this->validaDatiMarcaDaBollo($this->getRichiesta());
        $esitiSezioni[] = $this->validaDatiGenerali($this->getRichiesta());

        /**
         *  se voglio skippare la validazione dei dati progetto (fatto risultato necessario nel bando 23) 
         *  definisco l'opzione validaDatiProgetto con vaolre false
         */
        if (!array_key_exists('validaDatiProgetto', $opzioni) || $opzioni['validaDatiProgetto'] != false) {
            $esitiSezioni[] = $this->validaDatiProgetto($id_richiesta);
        }

        $esitiSezioni[] = $this->container->get("gestore_proponenti")->getGestore()->validaProponenti($id_richiesta);
        if ($richiesta->getProcedura()->getPianoCostoAttivo()) {
            $esitiSezioni[] = $this->container->get("gestore_piano_costo")->getGestore()->validaPianoDeiCosti($id_richiesta);
        }

        if ($richiesta->getProcedura()->getFasiProcedurali()) {
            $esitiSezioni[] = $this->container->get("gestore_fase_procedurale")->getGestore()->validaFaseProceduraleRichiesta($id_richiesta);
        }

        $arrayEsclusiIter = array(189);
        if ($this->container->get('monitoraggio.iter_progetto')->getIstanza($richiesta)->hasSezioneRichiestaVisibile() && !in_array($richiesta->getProcedura()->getId(), $arrayEsclusiIter)) {
            $esitiSezioni[] = $this->container->get("monitoraggio.iter_progetto")->getIstanza($richiesta)->validaInPresentazioneDomanda();
        }

        $codiceVideoDiPresentazione = $this->getCodiceVideoDiPresentazione($id_richiesta);
        $listaDocumenti = $this->getTipiDocumenti($id_richiesta, 1);
        $documentoVideoDiPresentazione = array_filter($listaDocumenti, function (TipologiaDocumento $tipo) use ($codiceVideoDiPresentazione) {
            return $tipo->getCodice() == $codiceVideoDiPresentazione;
        });

        if ($documentoVideoDiPresentazione) {
            $esitiSezioni[] = $this->container->get("gestore_richieste")->getGestore()->validaDocumentiDropzone($id_richiesta);
        }

        $esitiSezioni[] = $this->validaDocumenti($id_richiesta);

        //valido il questionario

        $istanzaFascicoloService = $this->container->get("fascicolo.istanza");
        foreach ($richiesta->getOggettiRichiesta() as $oggetto) {
            $validitaQuestionario = $istanzaFascicoloService->validaIstanzaPagina($oggetto->getIstanzaFascicolo()->getIndice());
            if (!$validitaQuestionario->getEsito()) {
                $esitiSezioni[] = new EsitoValidazione(false, null, array("Il questionario " . $oggetto->getDescrizione() . " non è compilato in tutte le sue sezioni"));
            }
        }

        $fornitore = $richiesta->getProcedura()->getFornitori();

        if ($fornitore == 1) {
            $esitiSezioni[] = $this->validaFornitori($id_richiesta);
        }
        if ($this->hasDichiarazioniDsnh()) {
            $esitiSezioni[] = $this->validaDnsh();
        }

        $esitiSezioni[] = $this->validaIndicatoriOutput($richiesta);
        $esitiSezioni[] = $this->validaObiettiviRealizzativi($richiesta);

        $esito = true;
        $messaggi = array();
        $messaggiSezione = array();
        foreach ($esitiSezioni as $esitoSezione) {
            $esito &= $esitoSezione->getEsito();
            $messaggi = array_merge_recursive($messaggi, $esitoSezione->getMessaggi());
            $messaggiSezione = array_merge_recursive($messaggiSezione, $esitoSezione->getMessaggiSezione());
        }

        $esito = new EsitoValidazione($esito, $messaggi, $messaggiSezione);
        return $esito;
    }

    public function validaRichiesta($id_richiesta, $opzioni = array()) {

        if (isset($opzioni['errori_validazione_extra'])) {
            $messaggio = "<b>RICHIESTA NON VALIDATA.</b><br/> ";
            foreach ($opzioni['errori_validazione_extra'] as $erroreExtra) {
                $messaggio .= $erroreExtra . ".<br/>";
            }
            return new GestoreResponse($this->addErrorRedirect($messaggio, "dettaglio_richiesta", array('id_richiesta' => $id_richiesta)));
        }

        if ($this->isRichiestaDisabilitataInoltro()) {
            throw new SfingeException("Il bando è chiuso e la richiesta non è più validabile");
        }

        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        if (!$richiesta->getStato()->uguale(StatoRichiesta::PRE_INSERITA)) {
            throw new SfingeException("Stato non valido per effettuare la validazione");
        }

        $esitoValidazione = $this->controllaValiditaRichiesta($id_richiesta);
        if (!$esitoValidazione->getEsito()) {
            throw new SfingeException("La richiesta non è validabile");
        }

        // Faccio il controllo nel caso in cui il bando preveda la validazione solamente da parte del legale rappresentante o del delegato.
        if ($richiesta->getProcedura()->isRichiestaFirmaDigitale() == false) {
            if ($this->isIncaricatoLegaleRappresentanteODelegato($richiesta) == false) {
                throw new SfingeException("Impossibile procedere, solamente il legale rappresentante o un suo delegato possono validare la richiesta.");
            }
        }

        //cancello il vecchio documento se esiste
        if (!is_null($richiesta->getDocumentoRichiesta())) {
            $this->container->get("documenti")->cancella($richiesta->getDocumentoRichiesta(), 1);
        }

        //genero il nuovo pdf
        $pdf = $this->generaPdf($id_richiesta, false, false);

        //lo persisto
        $tipoDocumento = $this->getEm()->getRepository("DocumentoBundle:TipologiaDocumento")->findOneByCodice(TipologiaDocumento::RICHIESTA_CONTRIBUTO);
        $documentoRichiesta = $this->container->get("documenti")->caricaDaByteArray($pdf, $this->getNomePdfDomanda(false) . ".pdf", $tipoDocumento, false, $richiesta);

        //associo il documento alla richiesta
        $richiesta->setDocumentoRichiesta($documentoRichiesta);
        $this->getEm()->persist($richiesta);

        //avanzo lo stato della richiesta
        $this->container->get("sfinge.stati")->avanzaStato($richiesta, StatoRichiesta::PRE_VALIDATA);

        $this->getEm()->flush();
        return new GestoreResponse($this->addSuccesRedirect("Richiesta validata", "dettaglio_richiesta", array('id_richiesta' => $id_richiesta)));
    }

    public function invalidaRichiesta($id_richiesta, $opzioni = array()) {
        $richiesta = $this->getRichiesta();
        if ($this->isRichiestaDisabilitataInoltro()) {
            throw new SfingeException("Il bando è chiuso e la richiesta non è più invalidabile");
        }

        if (!empty($richiesta->getProcedura()->getAttualeFinestraTemporalePresentazione())) {
            if ($richiesta->getFinestraTemporale() < $richiesta->getProcedura()->getAttualeFinestraTemporalePresentazione()) {
                $this->addFlash('error', "Impossibile procedere. Si sta compilando una richiesta di contributo di una finestra precedente. E' necessario creare una nuova richiesta di contributo.");
                return new GestoreResponse($this->redirect($this->generateUrl('dettaglio_richiesta', array('id_richiesta' => $richiesta->getId()))));
            }
        }

        if ($richiesta->getStato()->uguale(StatoRichiesta::PRE_VALIDATA) ||
            $richiesta->getStato()->uguale(StatoRichiesta::PRE_FIRMATA)) {
            $this->container->get("sfinge.stati")->avanzaStato($richiesta, StatoRichiesta::PRE_INSERITA, true);
            return new GestoreResponse($this->addSuccesRedirect("Richiesta invalidata", "dettaglio_richiesta", array('id_richiesta' => $id_richiesta)));
        }
        throw new SfingeException("Stato non valido per effettuare la validazione");
    }

    public function inviaRichiesta($id_richiesta, $opzioni = array()) {
        $em = $this->getEm();
        /** @var Richiesta $richiesta */
        $richiesta = $em->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        /** @var GestoreIndicatoreService $gestoreIndicatoreFactory */
        $gestoreIndicatoreFactory = $this->container->get('monitoraggio.indicatori_output');
        $gestoreIndicatori = $gestoreIndicatoreFactory->getGestore($richiesta);
        $gestoreIndicatori->valorizzaValoriProgrammatiIndicatoriAutomatici();
        try {
            $this->checkRichiestaInviabile($richiesta);
        } catch (SfingeException $e) {
            $this->addFlash('error', $e->getMessage());
            return new GestoreResponse($this->redirect($this->generateUrl('dettaglio_richiesta', array('id_richiesta' => $richiesta->getId()))));
        }

        // Faccio il controllo nel caso in cui il bando preveda l'invio solamente da parte del legale rappresentante o del delegato.
        if ($richiesta->getProcedura()->isRichiestaFirmaDigitale() == false) {
            if ($this->isIncaricatoLegaleRappresentanteODelegato($richiesta) == false) {
                throw new SfingeException("Impossibile procedere con l'invio, solamente il legale rappresentante o un suo delegato possono inviare la richiesta.");
            }
        }

        $connection = $em->getConnection();
        try {
            //Avvio la transazione
            $connection->beginTransaction();
            $this->creaOggettiVersions($richiesta);
            $richiesta->setDataInvio(new \DateTime());
            $richiesta->setUtenteInvio($this->getUser()->getUsername());
            $this->container->get("sfinge.stati")->avanzaStato($richiesta, StatoRichiesta::PRE_INVIATA_PA);
            $em->persist($richiesta);
            $em->flush();

            /* Popolamento tabelle protocollazione
             * - richieste_protocollo
             * - richieste_protocollo_documenti
             */
            if ($this->container->getParameter("stacca_protocollo_al_volo")) {
                $this->container->get("docerinitprotocollazione")->setTabProtocollazione($id_richiesta, 'FINANZIAMENTO');
            }
            $connection->commit();
            return new GestoreResponse($this->addSuccesRedirect("Richiesta inviata correttamente", "dettaglio_richiesta", array('id_richiesta' => $id_richiesta)));
        } catch (\Exception $ex) {
            //Effettuo il rollback
            if ($connection->isTransactionActive()) {
                $connection->rollback();
            }
            throw new SfingeException('Errore nell\'invio della richiesta');
        }
    }

    protected function checkRichiestaInviabile(Richiesta $richiesta): void {
        if ($this->isRichiestaDisabilitataInoltro()) {
            throw new SfingeException("Il bando è chiuso e la richiesta non è più inviabile");
        }

        // In caso di click-day il pulsante invia non viene mostrato.
        // Per sicurezza effettuo comunque il controllo anche qui.
        $now = new DateTime();
        $clickDay = $richiesta->getProcedura()->getDataClickDay();
        if (!empty($clickDay) && $now < $clickDay) {
            throw new SfingeException('Non è possibile inviare la richiesta di contributo prima del ' . $clickDay->format('d/m/Y H:i:s'));
        }

        if (!$this->isValidoNumeroMaxRichiesteBando($richiesta)) {
            throw new SfingeException("Impossibile procedere, è stato raggiunto il limite massimo di {$richiesta->getProcedura()->getNumeroMassimoRichiesteProcedura()} richieste previste per questo bando");
        }

        if (!$richiesta->getStato()->uguale(StatoRichiesta::PRE_FIRMATA)) {
            throw new SfingeException("Stato non valido per effettuare la validazione");
        }
        /** @var \SfingeBundle\Entity\Bando $procedura */
        $procedura = $richiesta->getProcedura();
        $now = new \DateTime();
        if ($procedura->getDataOraInizioPresentazione() > $now) {
            throw new SfingeException("Non è possibile inviare la domanda prima della data di inizio presentazione");
        }

        if ($now > $procedura->getDataOraFinePresentazione()) {
            throw new SfingeException("Non è possibile inviare la domanda dopo la data ultima di presentazione");
        }

        if (!empty($procedura->getAttualeFinestraTemporalePresentazione())) {
            if ($richiesta->getFinestraTemporale() < $procedura->getAttualeFinestraTemporalePresentazione()) {
                throw new SfingeException("Impossibile procedere. Si sta compilando una richiesta di contributo di una finestra precedente. E' necessario creare una nuova richiesta di contributo.");
            }
        }
    }

    protected function isValidoNumeroMaxRichiesteBando(Richiesta $richiesta): bool {
        $max = $richiesta->getProcedura()->getNumeroMassimoRichiesteProcedura();
        if (\is_null($max)) {
            return true;
        }

        $em = $this->getEm();
        $finestraTemporale = $richiesta->getProcedura()->getAttualeFinestraTemporalePresentazione() ?: null;
        $richiesteInviate = $em->getRepository('RichiesteBundle:Richiesta')->countRichiesteProtocollateProcedura($richiesta->getProcedura(), $finestraTemporale);
        return $richiesteInviate < $max;
    }

    protected function isValidoNumeroMassimoRichiesteSoggettoInviate(Richiesta $richiesta): bool {
        $max = $richiesta->getProcedura()->getNumeroRichieste();
        if (\is_null($max)) {
            return true;
        }
        /** @var \RichiesteBundle\Entity\RichiestaRepository $richiesteRepo */
        $richiesteRepo = $this->getEm()->getRepository('RichiesteBundle:Richiesta');
        $numeroRichiesteInviate = $richiesteRepo->countRichiesteInviateDaSoggetto($richiesta);
        return $numeroRichiesteInviate <= $max;
    }

    public function creaOggettiVersions($richiesta) {
        $versioning = $this->container->get("soggetto.versioning");
        foreach ($richiesta->getProponenti() as $proponente) {
            if (is_null($proponente->getSoggettoVersion())) {
                $soggetto_version = $versioning->creaSoggettoVersion($proponente->getSoggetto());
                $proponente->setSoggettoVersion($soggetto_version);
            }

            foreach ($proponente->getSedi() as $sedeOperativa) {
                if (is_null($sedeOperativa->getSedeVersion())) {
                    $sede_version = $versioning->creaSedeVersion($sedeOperativa->getSede());
                    $sedeOperativa->setSedeVersion($sede_version);
                }
            }
        }
    }

    public function controllaCapofila() {
        return null;
    }

    public function generaPdf($id_richiesta, $facsimile = true, $download = true) {
        throw new SfingeException("Deve essere implementato nella classe derivata");
    }

    public function isBeneficiario() {
        return $this->isGranted("ROLE_UTENTE");
    }

    public function isRichiestaDisabilitata($id_richiesta = null) {
        if (!$this->isBeneficiario()) {
            return true;
        }

        $em = $this->getEm();
        if (is_null($id_richiesta)) {
            $id_richiesta = $this->container->get("request_stack")->getCurrentRequest()->get("id_richiesta");
        }
        $richiesta = $em->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        $scadenza = $richiesta->getProcedura()->getDataOraFinePresentazione();

        // Controllo a livello di finestra
        if (!$richiesta->isFinestraPresentazioneAbilitata()) {
            return true;
        }

        if (is_null($scadenza)) {
            return false;
        }

        $ora = new \DateTime();
        if ($scadenza < $ora && !$richiesta->getAbilitaGestioneBandoChiuso()) {
            return true;
        }

        $stato = $richiesta->getStato()->getCodice();
        if ($stato != StatoRichiesta::PRE_INSERITA) {
            return true;
        }

        return false;
    }

    public function isRichiestaDisabilitataInoltro($id_richiesta = null) {
        if (!$this->isBeneficiario()) {
            return true;
        }

        $em = $this->getEm();
        if (is_null($id_richiesta)) {
            $id_richiesta = $this->container->get("request_stack")->getCurrentRequest()->get("id_richiesta");
        }
        $richiesta = $em->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        $scadenza = $richiesta->getProcedura()->getDataOraFinePresentazione();

        // Controllo a livello di finestra
        if (!$richiesta->isFinestraPresentazioneAbilitata()) {
            return true;
        }

        if (is_null($scadenza)) {
            return false;
        }

        $ora = new \DateTime();
        if ($scadenza < $ora && !$richiesta->getAbilitaGestioneBandoChiuso()) {
            return true;
        }

        return false;
    }

    // Da non confondere l'attributo alias del Fascicolo con l'alias della Pagina: in questa funzione viene individuato un fascicolo in base all'alias della su pagina/indice
    public function getFascicoloByAlias(iterable $fascicoli, string $alias) {
        foreach ($fascicoli as $fascicolo) {
            $pagina = $fascicolo->getIndice();
            $aliasPagina = $pagina->getAlias();
            if ($aliasPagina == $alias) {
                return $fascicolo;
            }
        }
        return false;
    }

    public function elencoDocumentiCaricati($id_richiesta, $opzioni = array()) {

        $em = $this->getEm();

        $documenti_caricati = $em->getRepository("RichiesteBundle\Entity\DocumentoRichiesta")->findDocumentiCaricati($id_richiesta);
        $richiesta = $em->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);

        $domanda_importata = !is_null($richiesta->getIdSfinge2013());
        $domanda = $richiesta->getDocumentoRichiestaFirmato();
        if (!$richiesta->getProcedura()->isRichiestaFirmaDigitale()) {
            $domanda = $richiesta->getDocumentoRichiesta();
        }

        $dati = array("documenti" => $documenti_caricati, "domanda" => $domanda, "domanda_importata" => $domanda_importata);
        $response = $this->render("RichiesteBundle:Richieste:elencoDocumentiCaricati.html.twig", $dati);
        return new GestoreResponse($response, "RichiesteBundle:Richieste:elencoDocumentiCaricati.html.twig", $dati);
    }

    /*
     * Funzionalità per gestire la sezione Priorità di una richiesta
     */

    public function hasSezionePriorita() {
        return false;
    }

    public function hasSezioneAmbitiTematiciS3(): bool {
        return false;
    }

    public function hasSezioneFornitori() {
        return false;
    }

    public function hasSezioneInterventi($richiesta) {
        return false;
    }

    public function hasSezioneInterventiRichiesta() {
        return false;
    }

    public function isPrioritaRichiesta() {
        return true;
    }

    public function isAmbitiTematiciS3Richiesta(): bool {
        return true;
    }

    public function hasSistemiProduttiviMultipli() {
        return false;
    }

    public function aggiungiEdificioPlesso($id_richiesta) {
        throw new SfingeException("Deve essere implementato nella classe derivata");
    }

    public function hasRisorseProgetto($richiesta) {
        return $richiesta->getProcedura()->hasRisorseProgetto();
    }

    public function gestioneBarraAvanzamento() {
        /** @var Richiesta $richiesta */
        $richiesta = $this->getRichiesta();
        $statoRichiesta = $richiesta->getStato()->getCodice();
        $arrayStati = ['Inserita' => true, 'Validata' => false, 'Firmata' => false, 'Inviata' => false];

        switch ($statoRichiesta) {
            case 'PRE_PROTOCOLLATA':
            case 'PRE_INVIATA_PA':
                $arrayStati['Inviata'] = true;
            case 'PRE_FIRMATA':
                $arrayStati['Firmata'] = true;
            case 'PRE_VALIDATA':
                $arrayStati['Validata'] = true;
        }

        if (!$richiesta->getProcedura()->isRichiestaFirmaDigitale()) {
            unset($arrayStati['Firmata']);
        }

        return $arrayStati;
    }

    public function elencoFornitori($id_richiesta, $opzioni = array()) {

        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        $fornitori = $this->getEm()->getRepository("RichiesteBundle:Fornitore")->findByRichiesta($richiesta);
        //$fornitori = array();

        if (is_null($richiesta)) {
            return new GestoreResponse($this->addErrorRedirect("Richiesta non trovata", "home"));
        }

        $dati = array("fornitori" => $fornitori, "id_richiesta" => $id_richiesta, "is_richiesta_disabilitata" => $this->isRichiestaDisabilitata($id_richiesta));
        $response = $this->render("RichiesteBundle:Richieste:elencoFornitori.html.twig", $dati);
        return new GestoreResponse($response, "RichiesteBundle:Richieste:elencoFornitori.html.twig", $dati);
    }

    public function elencoInterventi($id_richiesta, $opzioni = array()) {

        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);

        if (is_null($richiesta)) {
            return new GestoreResponse($this->addErrorRedirect("Richiesta non trovata", "home"));
        }

        $dati = array("richiesta" => $richiesta, "is_richiesta_disabilitata" => $this->isRichiestaDisabilitata($id_richiesta));
        $twig = 'RichiesteBundle:Richieste:elencoInterventi.html.twig';
        if (array_key_exists('twig', $opzioni)) {
            $twig = $opzioni['twig'];
        }

        if (array_key_exists('mostra_col_proponente', $opzioni)) {
            $dati['mostra_col_proponente'] = $opzioni['mostra_col_proponente'];
        } else {
            $dati['mostra_col_proponente'] = true;
        }

        $response = $this->render($twig, $dati);
        return new GestoreResponse($response);
    }

    public function creaIntervento($id_richiesta, $opzioni = array(), $twig = null) {

        $funzioniService = $this->container->get("funzioni_utili");

        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);

        $oggetti = $richiesta->getOggettiRichiesta();
        $intervento = new \RichiesteBundle\Entity\Intervento();
        $intervento->setOggettoRichiesta($oggetti[0]);

        $request = $this->getCurrentRequest();

        $opzioni["disabled"] = $this->isRichiestaDisabilitata($id_richiesta);

        $opzioni["proponenti"] = $richiesta->getProponenti();
        $opzioni["url_indietro"] = $this->generateUrl("elenco_interventi", array("id_richiesta" => $id_richiesta));

        if (!$opzioni["is_multiproponente"]) {
            $intervento->setProponente($richiesta->getMandatario());
        }

        $typeClass = !isset($opzioni["typeClass"]) ? "intervento" : $opzioni["typeClass"];
        if (isset($opzioni["typeClass"])) {
            unset($opzioni["typeClass"]);
        }

        if (!isset($opzioni["is_multiproponente"])) {
            $opzioni["is_multiproponente"] = $oggetti[0]->isMultiProponente();
        }

        $data = $funzioniService->getDataIndirizzoInterventoFromRequest($request, $intervento->getIndirizzo(), $typeClass);
        $opzioni["dataIndirizzo"] = $data;

        if (!isset($opzioni["form"])) {
            $form = $this->createForm("RichiesteBundle\Form\InterventoType", $intervento, $opzioni);
        } else {
            $form = $this->createForm($opzioni["form"], $intervento, $opzioni);
        }

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getEm();

                //recupero il fascicolo
                $fascicolo = $this->getFascicoloIntervento($intervento);

                if (!is_null($fascicolo)) {
                    $istanzaFascicolo = new \FascicoloBundle\Entity\IstanzaFascicolo();
                    $istanzaFascicolo->setFascicolo($fascicolo);
                    $intervento->setIstanzaFascicolo($istanzaFascicolo);

                    $indice = new \FascicoloBundle\Entity\IstanzaPagina();
                    $indice->setPagina($fascicolo->getIndice());
                    $istanzaFascicolo->setIndice($indice);
                    $em->persist($istanzaFascicolo);
                }

                try {
                    $em->persist($intervento);
                    $em->flush();

                    return new GestoreResponse($this->addSuccesRedirect("Dati sede di intervento salvati correttamente", "elenco_interventi", array("id_richiesta" => $richiesta->getId())));
                } catch (\Exception $e) {
                    throw new SfingeException("Sede di intervento non salvato" . $e->getMessage());
                }
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["is_multiproponente"] = $opzioni["is_multiproponente"];

        if (is_null($twig)) {
            $twig = "RichiesteBundle:Richieste:creaIntervento.html.twig";
        }

        $response = $this->render($twig, $form_params);
        return new GestoreResponse($response);
    }

    public function modificaIntervento($id_richiesta, $id_intervento, $opzioni = array(), $twig = null) {

        $funzioniService = $this->container->get("funzioni_utili");

        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);

        $oggetti = $richiesta->getOggettiRichiesta();
        $intervento = $this->getEm()->getRepository("RichiesteBundle:Intervento")->find($id_intervento);

        $request = $this->getCurrentRequest();

        $opzioni["disabled"] = $this->isRichiestaDisabilitata($id_richiesta);

        if (!isset($opzioni["is_multiproponente"])) {
            $opzioni["is_multiproponente"] = $oggetti[0]->isMultiProponente();
        }

        $opzioni["proponenti"] = $richiesta->getProponenti();
        $opzioni["url_indietro"] = $this->generateUrl("elenco_interventi", array("id_richiesta" => $id_richiesta));

        $typeClass = !isset($opzioni["typeClass"]) ? "intervento" : $opzioni["typeClass"];
        if (isset($opzioni["typeClass"])) {
            unset($opzioni["typeClass"]);
        }

        $data = $funzioniService->getDataIndirizzoInterventoFromRequest($request, $intervento->getIndirizzo(), $typeClass);
        $opzioni["dataIndirizzo"] = $data;

        if (!isset($opzioni["form"])) {
            $form = $this->createForm("RichiesteBundle\Form\InterventoType", $intervento, $opzioni);
        } else {
            $form = $this->createForm($opzioni["form"], $intervento, $opzioni);
        }

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getEm();

                try {
                    $em->flush();

                    return new GestoreResponse($this->addSuccesRedirect("Dati sede di intervento salvati correttamente", "elenco_interventi", array("id_richiesta" => $richiesta->getId())));
                } catch (\Exception $e) {
                    throw new SfingeException("Sede di intervento non salvato" . $e->getMessage());
                }
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["is_multiproponente"] = $opzioni["is_multiproponente"];

        if (is_null($twig)) {
            $twig = "RichiesteBundle:Richieste:creaIntervento.html.twig";
        }
        $response = $this->render($twig, $form_params);
        return new GestoreResponse($response);
    }

    public function getFascicoloIntervento(Intervento $intervento): ?Fascicolo {
        return null;
    }

    public function eliminaIntervento($id_richiesta, $id_intervento, $opzioni = array()) {
        $isRichiestaDisabilitata = $this->container->get("gestore_richieste")->getGestore()->isRichiestaDisabilitata($id_richiesta);

        if ($isRichiestaDisabilitata) {
            throw new SfingeException("Impossibile effettuare questa operazione");
        }

        $intervento = $this->getEm()->getRepository("RichiesteBundle:Intervento")->find($id_intervento);
        if (is_null($intervento)) {
            throw new SfingeException("La sede di intervento indicata non esiste");
        }

        $this->getEm()->remove($intervento);
        $this->getEm()->flush();

        return new GestoreResponse($this->addSuccesRedirect("La sede di intervento è stata rimossa correttamente", "elenco_interventi", array("id_richiesta" => $id_richiesta)));
    }

    public function creaFornitore($id_richiesta, $opzioniCustom = array()) {
        /** @var Richiesta $richiesta */
        $richiesta = $this->getEm()->getRepository(Richiesta::class)->find($id_richiesta);

        $fornitore = new Fornitore();
        $fornitore->setRichiesta($richiesta);
        $richiesta->addFornitori($fornitore);

        $opzioniResolver = new OptionsResolver();
        $opzioniResolver->setDefaults([
            'form_options' => [],
            'form_type' => FornitoreType::class,
        ]);
        $opzioni = $opzioniResolver->resolve($opzioniCustom);

        $em = $this->getEm();
        $formOptionResolver = new OptionsResolver();
        $formOptionResolver->setDefaults([
            'disabled' => $this->isRichiestaDisabilitata($richiesta),
            'url_indietro' => $this->generateUrl("elenco_fornitori", array("id_richiesta" => $id_richiesta)),
        ]);
        $formOptions = $formOptionResolver->resolve($opzioni['form_options']);

        $form = $this->createForm($opzioni['form_type'], $fornitore, $formOptions);

        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($fornitore);
                $em->flush();

                return new GestoreResponse($this->addSuccesRedirect("Dati generali salvati correttamente", "elenco_fornitori", array("id_richiesta" => $richiesta->getId())));
            } catch (\Exception $e) {
                throw new SfingeException("Fornitore non salvato" . $e->getMessage(), 0, $e);
            }
        }

        $viewData["form"] = $form->createView();
        $viewData["fornitore"] = $fornitore;

        $twig = "RichiesteBundle:Richieste:creaFornitore.html.twig";
        $response = $this->render($twig, $viewData);
        return new GestoreResponse($response, $twig, $viewData);
    }

    public function modificaFornitore($id_richiesta, $id_fornitore, $opzioniCustom = array()) {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        $fornitore = $this->getEm()->getRepository("RichiesteBundle:Fornitore")->find($id_fornitore);

        $opzioniResolver = new OptionsResolver();
        $opzioniResolver->setDefaults([
            'form_options' => [],
            'form_type' => FornitoreType::class,
        ]);
        $opzioni = $opzioniResolver->resolve($opzioniCustom);

        $em = $this->getEm();
        $formOptionResolver = new OptionsResolver();
        $formOptionResolver->setDefaults([
            'disabled' => $this->isRichiestaDisabilitata($richiesta),
            'url_indietro' => $this->generateUrl("elenco_fornitori", array("id_richiesta" => $id_richiesta)),
        ]);
        $formOptions = $formOptionResolver->resolve($opzioni['form_options']);

        $form = $this->createForm($opzioni['form_type'], $fornitore, $formOptions);

        $request = $this->getCurrentRequest();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($fornitore);

                $em->flush();

                return new GestoreResponse($this->addSuccesRedirect("Dati generali salvati correttamente", "elenco_fornitori", array("id_richiesta" => $richiesta->getId())));
            } catch (\Exception $e) {
                throw new SfingeException("Fornitore non salvato" . $e->getMessage(), 0, $e);
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["fornitore"] = $fornitore;

        $twig = "RichiesteBundle:Richieste:creaFornitore.html.twig";

        $response = $this->render($twig, $form_params);
        return new GestoreResponse($response, $twig, $form_params);
    }

    public function visualizzaFornitore($id_richiesta, $id_fornitore, $opzioni = []) {
        $opzioni['form_options'] = ['disabled' => true];
        return $this->modificaFornitore($id_richiesta, $id_fornitore, $opzioni);
    }

    public function aggiungiFornitoreServizio($id_richiesta, $id_fornitore, $opzioni = array()) {

        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        $fornitore = $this->getEm()->getRepository("RichiesteBundle:Fornitore")->find($id_fornitore);

        $request = $this->getCurrentRequest();

        $opzioni["disabled"] = $this->isRichiestaDisabilitata($id_richiesta);
        $opzioni["url_indietro"] = $this->generateUrl("elenco_fornitore_servizi", array("id_richiesta" => $id_richiesta, "id_fornitore" => $id_fornitore));

        $fornitore_servizio = new FornitoreServizio();

        if (array_key_exists('form_type', $opzioni)) {
            /* Dovevo definire un'altra variabile array ma sinceramente per un campo mi pare pena 
             * quindi uso la opzioni già definito e semmai si rientrasse in questo if faccio l'unset
             * per non fare incazzare il required del form
             */
            $class_type = $opzioni['form_type'];
            unset($opzioni['form_type']);
            $form = $this->createForm($class_type, $fornitore_servizio, $opzioni);
        } else {
            $form = $this->createForm("RichiesteBundle\Form\FornitoreServizioType", $fornitore_servizio, $opzioni);
        }

        $fornitore_servizio->setFornitore($fornitore);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getEm();

                try {

                    $em->beginTransaction();
                    $em->persist($fornitore_servizio);

                    $em->flush();
                    $em->commit();

                    return new GestoreResponse($this->addSuccesRedirect("Dati salvati correttamente", "elenco_fornitore_servizi", array("id_richiesta" => $richiesta->getId(), "id_fornitore" => $fornitore->getId())));
                } catch (\Exception $e) {
                    $em->rollback();
                    throw new SfingeException("Fornitore non salvato" . $e->getMessage());
                }
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["fornitore_servizio"] = $fornitore_servizio;

        $twig = "RichiesteBundle:Richieste:aggiungiFornitoreServizio.html.twig";
        $response = $this->render($twig, $form_params);
        return new GestoreResponse($response, $twig, $form_params);
    }

    public function elencoServiziFornitore($id_richiesta, $id_fornitore, $opzioni = array()) {

        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        $fornitore = $this->getEm()->getRepository("RichiesteBundle:Fornitore")->find($id_fornitore);
        $servizi = $this->getEm()->getRepository("RichiesteBundle:FornitoreServizio")->findByFornitore($fornitore);

        if (is_null($richiesta)) {
            return new GestoreResponse($this->addErrorRedirect("Richiesta non trovata", "home"));
        }

        $dati = array("fornitore" => $fornitore, "servizi" => $servizi, "id_richiesta" => $id_richiesta, "is_richiesta_disabilitata" => $this->isRichiestaDisabilitata($id_richiesta));
        $response = $this->render("RichiesteBundle:Richieste:elencoServiziFornitore.html.twig", $dati);
        return new GestoreResponse($response, "RichiesteBundle:Richieste:elencoServiziFornitore.html.twig", $dati);
    }

    public function modificaFornitoreServizio($id_richiesta, $id_fornitore, $id_servizio, $opzioni = array()) {

        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        $fornitore = $this->getEm()->getRepository("RichiesteBundle:Fornitore")->find($id_fornitore);
        $servizio = $this->getEm()->getRepository("RichiesteBundle:FornitoreServizio")->find($id_servizio);

        $request = $this->getCurrentRequest();

        $opzioni["disabled"] = false;
        $opzioni["url_indietro"] = $this->generateUrl("elenco_fornitore_servizi", array("id_richiesta" => $id_richiesta, "id_fornitore" => $id_fornitore));

        $formType = $opzioni['form_type'] ?? "RichiesteBundle\Form\FornitoreServizioType";
        if (array_key_exists('form_type', $opzioni)) {
            unset($opzioni['form_type']);
        }
        $form = $this->createForm($formType, $servizio, \array_merge($opzioni, $opzioni['form_options'] ?? []));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getEm();
            try {
                $em->persist($servizio);
                $em->flush();

                return new GestoreResponse($this->addSuccesRedirect("Dati salvati correttamente", "elenco_fornitore_servizi", array("id_richiesta" => $richiesta->getId(), "id_fornitore" => $fornitore->getId())));
            } catch (\Exception $e) {
                throw new SfingeException("Fornitore non salvato " . $e->getMessage());
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["servizio"] = $servizio;

        $twig = "RichiesteBundle:Richieste:aggiungiFornitoreServizio.html.twig";
        $response = $this->render($twig, $form_params);
        return new GestoreResponse($response, $twig, $form_params);
    }

    public function visualizzaFornitoreServizio($id_richiesta, $id_fornitore, $id_servizio, $opzioni = array()) {

        $servizio = $this->getEm()->getRepository("RichiesteBundle:FornitoreServizio")->find($id_servizio);

        $opzioni["disabled"] = true;
        $opzioni["url_indietro"] = $this->generateUrl("elenco_fornitore_servizi", array("id_richiesta" => $id_richiesta, "id_fornitore" => $id_fornitore));

        if (array_key_exists('form_type', $opzioni)) {
            /* Dovevo definire un'altra variabile array ma sinceramente per un campo mi pare pena 
             * quindi uso la opzioni già definito e semmai si rientrasse in questo if faccio l'unset
             * per non fare incazzare il required del form
             */
            $class_type = $opzioni['form_type'];
            unset($opzioni['form_type']);
            $form = $this->createForm($class_type, $servizio, $opzioni);
        } else {
            $form = $this->createForm("RichiesteBundle\Form\FornitoreServizioType", $servizio, $opzioni);
        }

        $form_params["form"] = $form->createView();
        $form_params["servizio"] = $servizio;

        $twig = "RichiesteBundle:Richieste:aggiungiFornitoreServizio.html.twig";
        $response = $this->render($twig, $form_params);
        return new GestoreResponse($response, $twig, $form_params);
    }

    public function eliminaFornitoreServizio($id_richiesta, $id_fornitore, $id_servizio, $opzioni = array()) {
        $isRichiestaDisabilitata = $this->container->get("gestore_richieste")->getGestore()->isRichiestaDisabilitata($id_richiesta);

        if ($isRichiestaDisabilitata) {
            throw new SfingeException("Impossibile effettuare questa operazione");
        }

        $servizio = $this->getEm()->getRepository("RichiesteBundle:FornitoreServizio")->find($id_servizio);
        if (is_null($servizio)) {
            throw new SfingeException("Il servizio indicato non esiste");
        }

        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        if (is_null($richiesta)) {
            throw new SfingeException("Richiesta non trovata");
        }

        $this->getEm()->remove($servizio);
        $this->getEm()->flush();
        return new GestoreResponse($this->addSuccesRedirect("Il serivizio è stato rimosso correttamente", "elenco_fornitore_servizi", array("id_richiesta" => $id_richiesta, "id_fornitore" => $id_fornitore)));
    }

    public function eliminaFornitore($id_richiesta, $id_fornitore, $opzioni = array()) {
        $isRichiestaDisabilitata = $this->container->get("gestore_richieste")->getGestore()->isRichiestaDisabilitata($id_richiesta);

        if ($isRichiestaDisabilitata) {
            throw new SfingeException("Impossibile effettuare questa operazione");
        }

        $fornitore = $this->getEm()->getRepository("RichiesteBundle:Fornitore")->find($id_fornitore);
        if (is_null($fornitore)) {
            throw new SfingeException("Il fornitore indicato non esiste");
        }

        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        if (is_null($richiesta)) {
            throw new SfingeException("Richiesta non trovata");
        }

        $this->getEm()->remove($fornitore);
        $this->getEm()->flush();

        $fornitore = $this->getEm()->getRepository("RichiesteBundle:Fornitore")->find($id_fornitore);
        $servizi = $this->getEm()->getRepository("RichiesteBundle:FornitoreServizio")->findByFornitore($fornitore);
        foreach ($servizi as $key => $servizio) {
            $this->getEm()->remove($servizio);
            $this->getEm()->flush();
        }

        return new GestoreResponse($this->addSuccesRedirect("Il fornitore è stato rimosso correttamente", "elenco_fornitori", array("id_richiesta" => $id_richiesta)));
    }

    public function validaFornitori($id_richiesta, $opzioni = array()) {
        $esito = new EsitoValidazione(true);
        $richiesta = $this->getRichiesta();
        $fornitori = $this->getEm()->getRepository("RichiesteBundle:Fornitore")->findByRichiesta($richiesta);

        if (count($fornitori) == 0) {
            $esito->addMessaggioSezione("Creare almeno un fornitore", "");
            $esito->setEsito(false);
        }

        foreach ($fornitori as $key => $fornitore) {
            $servizi = $this->getEm()->getRepository("RichiesteBundle:FornitoreServizio")->findByFornitore($fornitore);
            if (count($servizi) == 0) {
                $esito->addMessaggioSezione("Creare almeno un servizio per il fornitore: " . $fornitore->getDenominazione(), "");
                $esito->setEsito(false);
            }
        }

        return $esito;
    }

    public function validaInterventi($id_richiesta, $opzioni = array()) {
        $esito = new EsitoValidazione(true);

        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        $oggettiRichiesta = $richiesta->getOggettiRichiesta();
        $interventi = $oggettiRichiesta[0]->getInterventi();

        if (count($interventi) == 0) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Aggiungere almeno una sede di intervento");
        } else {
            foreach ($interventi as $intervento) {
                $esito_intervento = $this->validaIntervento($intervento);
                if (!$esito_intervento->getEsito()) {
                    $esito->setEsito(false);
                    $esito->addMessaggioSezione("Dati non completi o non validi per l'intervento con sede: " . $intervento->getIndirizzo());
                }
                if (count($intervento->getReferenti()) == 0) {
                    $esito->setEsito(false);
                    $esito->addMessaggioSezione("Manca il referente per l'intervento con sede: " . $intervento->getIndirizzo());
                }
            }
        }

        return $esito;
    }

    public function validaIntervento($intervento) {
        $esito = new EsitoValidazione(true);

        $validitaQuestionario = $this->container->get("fascicolo.istanza")->validaIstanzaPagina($intervento->getIstanzaFascicolo()->getIndice());
        if (!$validitaQuestionario->getEsito()) {
            $esito->setEsito(false);
        }

        return $esito;
    }

    public function inizializzaIstanzaFascicoloRichiesta($istanza_pagina_indice) {
        
    }

    public function impegniRichiesta($id_richiesta) {
        throw new SfingeException("Pagina non implementata");
    }

    public function calcolaLunghezzaStringa($stringa) {
        $chars = array('\r');
        $stringa = str_replace($chars, '', $stringa);
        if (function_exists("mb_strlen")) {
            $lunghezza = mb_strlen($stringa, "utf-8");
        } else {
            $lunghezza = strlen($stringa);
        }
        return $lunghezza;
    }

    public function validaServiziPianoCosti() {

        $richiesta = $this->getRichiesta();
        $fornitori = $this->getEm()->getRepository("RichiesteBundle:Fornitore")->findByRichiesta($richiesta);
        $proponente = $richiesta->getProponenti()->get(0);
        $opzioni = array();

        $array_associativo = $this->getArrayAssociativoServPiano();
        $array_costi_fornitori = array_combine(array_keys($array_associativo), array_fill(0, count($array_associativo), 0));
        $voci_piano_costo = array_unique(array_values($array_associativo));
        $array_piano_costi = array();

        foreach ($voci_piano_costo as $key) {
            $array_piano_costi[$key] = $this->getEm()->getRepository("RichiesteBundle\Entity\VocePianoCosto")->getVoceDaProponenteCodiceSezioneCodice($proponente->getId(), 'INNOV2017', $key)->getImportoAnno1();
        }

        foreach ($fornitori as $key => $fornitore) {
            foreach ($fornitore->getServizi() as $key => $servizio) {
                $array_costi_fornitori[$fornitore->getTipologiaFornitore()->getCodice()] += $servizio->getCosto();
            }
        }

        foreach ($array_associativo as $key => $value) {
            if (round($array_costi_fornitori[$key], 2) != round($array_piano_costi[$value], 2)) {
                $opzioni['errori_validazione_extra'][] = "Gli importi per i servizi dei fornitori di tipo " . $this->getKeyString($key) . " non coincidono con il piano costo, "
                    . "costi servizi: " . number_format($array_costi_fornitori[$key], 2, ",", ".") . "; importo voce piano costo: " . number_format($array_piano_costi[$value], 2, ",", ".");
            }
        }

        return $opzioni;
    }

    /**
     * @return array
     */
    public function getArrayAssociativoServPiano() {
        /* Metodo che ritorna l'array associativo tra piano costo e tipologia fornitore 
         * da utilizzare per bandi che prevedono controlli incrociati piano costo-forniture-servizi	
         * il metodo va definito nella classe figlia	 
         */
        throw new SfingeException("Deve essere implementato nella classe derivata");
    }

    //Ritorna il key statndard per il codice di validazione dei fornitori
    public function getKeyString($key) {
        return $key;
    }

    // in caso di più finestre temporali ridefinire nel gestore specifico
    public function getRichiesteDaSoggetto($soggettoId, $proceduraId, $finestraTemporale = null) {
        return $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->getRichiesteDaSoggetto($soggettoId, $proceduraId, $finestraTemporale);
    }

    public function hasRichiesteAmmesseInIstruttoria($richiesteDaSoggetto) {
        foreach ($richiesteDaSoggetto as $richiesta) {
            $istruttoria = $richiesta->getIstruttoria();
            if (!is_null($istruttoria)) {
                if (!is_null($istruttoria->getEsito())) {
                    if ($istruttoria->getEsito()->getCodice() == 'AMMESSO' || $istruttoria->getEsito()->getCodice() == 'SOSPESO') {
                        return true;
                    }
                } else {
                    return true;
                }
            } else {
                return true;
            }
        }
        return false;
    }

    public function gestioneAutodichiarazioniAutorizzazioniAction($richiesta, $opzioni = array()) {

        $em = $this->getEm();

        $formData = new \stdClass();
        $formData->accettazione = $richiesta->getAccettazioneAutodichiarazioni();

        $label = 'Dichiaro di aver preso visione e di accettare integralmente le clausole riportate in questa sezione';
        if (array_key_exists('label', $opzioni)) {
            $label = $opzioni['label'];
        }

        $options = array();
        $options['disabled'] = $this->isRichiestaDisabilitata($richiesta->getId());

        $formBuilder = $this->createFormBuilder($formData, $options);
        $formBuilder->add('accettazione', \BaseBundle\Form\CommonType::checkbox, array(
            'required' => true,
            'label' => $label
        ));
        $formBuilder->add('submit', \BaseBundle\Form\CommonType::salva_indietro, array(
            'url' => $this->generateUrl("dettaglio_richiesta", array("id_richiesta" => $richiesta->getId()))
        ));

        $form = $formBuilder->getForm();

        $elenchiProcedura = $em->getRepository('RichiesteBundle\Entity\Autodichiarazioni\ElencoProceduraRichiesta')->getElenchiProceduraByRichiesta($richiesta);

        $dati = array('form' => $form->createView(), 'elenchiProcedura' => $elenchiProcedura, 'richiesta' => $richiesta);

        $request = $this->getCurrentRequest();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $accettazione = $formData->accettazione;

            if (!$accettazione) {
                return $this->addErrorRedirect('Attenzione, l\'accettazione è obbligatoria', 'autodichiarazioni_autorizzazioni_richiesta', array("id_richiesta" => $richiesta->getId()));
            }

            if ($form->isValid()) {
                try {
                    $richiesta->setAccettazioneAutodichiarazioni($accettazione);
                    $em->flush();
                } catch (\Exception $e) {
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                }

                return $this->addSuccesRedirect("Dati correttamente salvati", "dettaglio_richiesta", array("id_richiesta" => $richiesta->getId()));
            }
        }

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco richiesta", $this->generateUrl("elenco_richieste"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio richiesta", $this->generateUrl("dettaglio_richiesta", array("id_richiesta" => $richiesta->getId())));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Autodichiarazioni");

        $twig = $this->getTwigAutodichiarazioni();

        if (array_key_exists('twig', $opzioni)) {
            $twig = $opzioni['twig'];
        }

        return $this->render($twig, $dati);
    }

    public function validaAutodicharazioni($id_richiesta) {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        $esito = new EsitoValidazione(true);
        if (!$richiesta->getAccettazioneAutodichiarazioni()) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione('Accettazione obbligatoria');
        }

        return $esito;
    }

    public function elencoInterventiSede($id_richiesta, $id_sede = null, $opzioni = array()) {

        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        if (!is_null($id_sede)) {
            $sede = $this->getEm()->getRepository("RichiesteBundle:SedeOperativa")->find($id_sede);
            $proponente = $sede->getProponente();
        } else {
            $sede = null;
            $proponente = $richiesta->getMandatario();
        }
        if (is_null($richiesta) && is_null($id_sede)) {
            return new GestoreResponse($this->addErrorRedirect("Richiesta o sede non trovata", "home"));
        }

        $dati = array("proponente" => $proponente, "richiesta" => $richiesta, "sede" => $sede, "is_richiesta_disabilitata" => $this->isRichiestaDisabilitata($id_richiesta));
        $twig = 'RichiesteBundle:Richieste:elencoInterventiSede.html.twig';
        if (array_key_exists('twig', $opzioni)) {
            $twig = $opzioni['twig'];
        }

        if ($this->hasSezioneInterventiRichiesta() == true) {
            $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco richiesta", $this->generateUrl("elenco_richieste"));
            $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio richiesta", $this->generateUrl("dettaglio_richiesta", array("id_richiesta" => $id_richiesta)));
            $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco interventi");
        } else {
            $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco richiesta", $this->generateUrl("elenco_richieste"));
            $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio richiesta", $this->generateUrl("dettaglio_richiesta", array("id_richiesta" => $id_richiesta)));
            $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio proponente", $this->generateUrl("dettaglio_proponente", array("id_richiesta" => $id_richiesta, "id_proponente" => $proponente->getId())));
            $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco interventi sede");
        }
        $response = $this->render($twig, $dati);
        return new GestoreResponse($response);
    }

    public function aggiungiInterventoSede($id_richiesta, $id_sede = null, $opzioni = array()) {
        $em = $this->getEm();
        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        $intervento = new \RichiesteBundle\Entity\InterventoSede();
        $intervento->setRichiesta($richiesta);
        if (!is_null($id_sede)) {
            $sede = $this->getEm()->getRepository("RichiesteBundle:SedeOperativa")->find($id_sede);
            $intervento->setSedeOperativa($sede);
        } else {
            $id_sede = 'null';
        }

        try {
            $em->beginTransaction();
            $em->persist($intervento);
            $em->flush();
            $em->commit();
        } catch (\Exception $e) {
            $em->rollback();
            throw new SfingeException("Fornitore non salvato" . $e->getMessage());
        }
        return new GestoreResponse($this->addSuccesRedirect("Dati salvati correttamente", "modifica_intervento_sede", array("id_richiesta" => $richiesta->getId(), "id_sede" => $id_sede, "id_intervento" => $intervento->getId())));
    }

    public function modificaInterventoSede($id_richiesta, $id_sede, $id_intervento, $opzioni = array()) {
        $em = $this->getEm();
        $richiesta = $em->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        $intervento = $em->getRepository("RichiesteBundle:InterventoSede")->find($id_intervento);
        if (!is_null($id_sede)) {
            $sede = $this->getEm()->getRepository("RichiesteBundle:SedeOperativa")->find($id_sede);
            $id_proponente = $sede->getProponente()->getId();
        } else {
            $id_proponente = $richiesta->getMandatario()->getId();
            $id_sede = 'null';
        }

        $request = $this->getCurrentRequest();

        $opzioni["disabled"] = $this->isRichiestaDisabilitata($id_richiesta);
        $opzioni["url_indietro"] = $this->generateUrl("elenco_interventi_sede", array("id_richiesta" => $id_richiesta, "id_sede" => $id_sede, 'id_proponente' => $id_proponente));
        $opzioni['piano_costo'] = $em->getRepository("RichiesteBundle:PianoCosto")->getVociDaProceduraSenzaTotale($richiesta->getProcedura()->getId());
        $opzioni['finestra_temporale'] = $richiesta->getFinestraTemporale();
        $opzioni['id_procedura'] = $richiesta->getProcedura()->getId();

        if (array_key_exists('form_type', $opzioni)) {
            /* Dovevo definire un'altra variabile array ma sinceramente per un campo mi pare pena 
             * quindi uso la opzioni già definito e semmai si rientrasse in questo if faccio l'unset
             * per non fare incazzare il required del form
             */
            $class_type = $opzioni['form_type'];
            unset($opzioni['form_type']);
            $form = $this->createForm($class_type, $intervento, $opzioni);
        } else {
            $anni = $this->getAnniIntervento($richiesta->getFinestraTemporale());
            $opzioni['anni'] = $anni;
            $form = $this->createForm("RichiesteBundle\Form\InterventoSedeType", $intervento, $opzioni);
        }
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getEm();

                try {

                    $em->beginTransaction();
                    $em->persist($intervento);

                    $em->flush();
                    $em->commit();
                } catch (\Exception $e) {
                    $em->rollback();
                    throw new SfingeException("Fornitore non salvato" . $e->getMessage());
                }
                if ($this->hasSezioneInterventiRichiesta() == true) {
                    return new GestoreResponse($this->addSuccesRedirect("Dati salvati correttamente", "elenco_interventi_sede_esterno", array("id_richiesta" => $id_richiesta)));
                } else {
                    return new GestoreResponse($this->addSuccesRedirect("Dati salvati correttamente", "elenco_interventi_sede", array("id_richiesta" => $id_richiesta, "id_sede" => $id_sede, 'id_proponente' => $id_proponente)));
                }
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["intervento"] = $intervento;

        $twig = "RichiesteBundle:Richieste:modificaInterventoSede.html.twig";

        if ($this->hasSezioneInterventiRichiesta() == true) {
            $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco richiesta", $this->generateUrl("elenco_richieste"));
            $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio richiesta", $this->generateUrl("dettaglio_richiesta", array("id_richiesta" => $id_richiesta)));
            $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco interventi", $this->generateUrl("elenco_interventi_sede_esterno", array("id_richiesta" => $id_richiesta)));
            $this->container->get("pagina")->aggiungiElementoBreadcrumb("Modifica intervento");
        } else {
            $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco richiesta", $this->generateUrl("elenco_richieste"));
            $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio richiesta", $this->generateUrl("dettaglio_richiesta", array("id_richiesta" => $id_richiesta)));
            $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio proponente", $this->generateUrl("dettaglio_proponente", array("id_richiesta" => $id_richiesta, "id_proponente" => $id_proponente)));
            $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco interventi sede", $this->generateUrl("elenco_interventi_sede", array("id_richiesta" => $id_richiesta, "id_proponente" => $id_proponente, "id_sede" => $sede->getId())));
            $this->container->get("pagina")->aggiungiElementoBreadcrumb("Modifica intervento");
        }
        $response = $this->render($twig, $form_params);
        return new GestoreResponse($response, $twig, $form_params);
    }

    public function eliminaInterventoSede($id_richiesta, $id_proponente, $id_intervento, $opzioni = array()) {
        $em = $this->getEm();
        try {
            $intervento = $em->getRepository("RichiesteBundle:InterventoSede")->findOneById($id_intervento);
            $sede = $intervento->getSedeOperativa();
            $em->remove($intervento);
            $em->flush();
        } catch (\Exception $e) {
            $em->rollback();
            throw new SfingeException("Errore nella cancellazione" . $e->getMessage());
        }
        return new GestoreResponse($this->addSuccesRedirect("Intervento rimosso correttamente", "elenco_interventi_sede", array("id_richiesta" => $id_richiesta, "id_proponente" => $id_proponente, "id_sede" => $sede->getId()))
        );
    }

    public function eliminaInterventoRichiesta($id_richiesta, $id_intervento, $opzioni = array()) {
        $em = $this->getEm();
        try {
            $intervento = $em->getRepository("RichiesteBundle:InterventoSede")->findOneById($id_intervento);
            $em->remove($intervento);
            $em->flush();
        } catch (\Exception $e) {
            $em->rollback();
            throw new SfingeException("Errore nella cancellazione" . $e->getMessage());
        }
        return new GestoreResponse($this->addSuccesRedirect("Intervento rimosso correttamente", "elenco_interventi_sede_esterno", ["id_richiesta" => $id_richiesta])
        );
    }

    public function validaInterventiPianoCosti() {
        throw new SfingeException("Deve essere implementato nella classe derivata");
    }

    public function validaInterventiPianoCostiProponente(\RichiesteBundle\Entity\Proponente $proponente) {
        throw new SfingeException("Deve essere implementato nella classe derivata");
    }

    public function validaRisorseProgetto($id_richiesta) {
        throw new SfingeException("Deve essere implementato nella classe derivata");
    }

    public function popolaIndicatoriOutput(Richiesta &$richiesta): void {
        /** @var \MonitoraggioBundle\Service\IGestoreIndicatoreOutput $indicatoriService */
        $indicatoriService = $this->container->get('monitoraggio.indicatori_output')->getGestore($richiesta);
        $indicatoriService->popolaIndicatoriOutput();
    }

    public function richiestaMaggiorazione($id_richiesta, $opzioni = array()) {

        $richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
        if (is_null($richiesta)) {
            throw new SfingeException("Richiesta non trovato");
        }

        $isRichiestaDisabilitata = $this->isRichiestaDisabilitata($id_richiesta);

        $request = $this->getCurrentRequest();

        $oggettiRichiesta = $richiesta->getOggettiRichiesta();
        $oggettoRichiesta = $oggettiRichiesta[0];

        $options = array();
        $options["disabled"] = $isRichiestaDisabilitata;
        $options["isAssociazione"] = $oggettiRichiesta[0]->isAssociazione();
        $options["urlIndietro"] = $this->generateUrl('dettaglio_richiesta', array('id_richiesta' => $richiesta->getId()));

        if (array_key_exists('twig', $opzioni)) {
            $twig = $opzioni['twig'];
        } else {
            $twig = "RichiesteBundle:Richieste:richiestaMaggiorazione.html.twig";
        }

        if (array_key_exists('form_type', $opzioni)) {
            /* Dovevo definire un'altra variabile array ma sinceramente per un campo mi pare pena 
             * quindi uso la opzioni già definito e semmai si rientrasse in questo if faccio l'unset
             * per non fare incazzare il required del form
             */
            $class_type = $opzioni['form_type'];
            unset($opzioni['form_type']);
            $form = $this->createForm($class_type, $oggettoRichiesta, $options);
        } else {
            $form = $this->createForm(new \RichiesteBundle\Form\RichiestaMaggiorazioneType, $oggettoRichiesta, $options);
        }

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                try {

                    // nel caso in cui sia già stato compilato il piano costi e si cambiano i dati di questa sezione
                    // bisogna ricalcolare il totale contributo concesso..poichè si passa dal 45% al 40% in caso di deflaggamento e viceversa
                    if (!is_null($richiesta->getContributoRichiesta())) {
                        $vociSpesa = $richiesta->getMandatario()->getVociPianoCosto();
                        $totale = null;
                        foreach ($vociSpesa as $voce) {
                            if ($voce->getPianoCosto()->getCodice() == 'TOT') {
                                $totale = $voce->getTotale();
                            }
                        }
                        if (!is_null($totale)) {
                            $sogliaContributo = 25000;
                            $contributo = round($totale * ($oggettoRichiesta->hasMaggiorazione() ? 45 : 40) / 100, 2);
                            if ($contributo > $sogliaContributo) {
                                $contributo = $sogliaContributo;
                            }

                            $richiesta->setContributoRichiesta($contributo);
                        }
                    }


                    $em = $this->getEm();
                    $em->persist($oggettoRichiesta);
                    $em->flush();
                    $this->addFlash('success', "Modifiche salvate correttamente");

                    return new GestoreResponse($this->redirect($this->generateUrl('dettaglio_richiesta', array('id_richiesta' => $richiesta->getId()))));
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Si è verificato un errore durante il salvataggio delle informazioni');
                }
            } else {
                $this->addFlash('error', 'Sono presenti degli errori');
            }
        }

        $opzioni["form"] = $form->createView();

        $response = $this->render($twig, $opzioni);
        return new GestoreResponse($response, $twig, $opzioni);
    }

    public function validaIndicatoriOutput(Richiesta $richiesta): EsitoValidazione {
        /** @var \MonitoraggioBundle\Service\IGestoreIndicatoreOutput $indicatoriService */
        $indicatoriService = $this->container->get('monitoraggio.indicatori_output')->getGestore($richiesta);

        $valido = $indicatoriService->isRichiestaValida();

        if ($valido) {
            return new EsitoValidazione(true);
        }

        $esito = new EsitoValidazione(false);
        $esito->addMessaggioSezione('Compilare la sezione');

        return $esito;
    }

    public function gestioneIndicatoreOutput(Richiesta $richiesta, array $options = []): GestoreResponse {
        /** @var \MonitoraggioBundle\Service\IGestoreIndicatoreOutput $indicatoriService */
        $indicatoriService = $this->container->get('monitoraggio.indicatori_output')->getGestore($richiesta);

        $opzioni = [
            'disabled' => $this->isRichiestaDisabilitata($richiesta->getId()),
        ];

        $response = $indicatoriService->getFormRichiestaValoriProgrammati($opzioni);

        return new GestoreResponse($response);
    }

    public function gestioneEliminaProceduraAggiudicazione(Richiesta $richiesta, $id_procedura_aggiudicazione) {
        
    }

    public function gestioneModificaProceduraAggiudicazione(Richiesta $richiesta, $id_procedura_aggiudicazione) {
        
    }

    public function gestioneProceduraAggiudicazione(Richiesta $richiesta): Response {
        throw new \LogicException('Funzione non implementata');
    }

    public function validaProceduraAggiudicazione(Richiesta $richiesta): EsitoValidazione {
        throw new \LogicException('Funzione non implementata');
    }

    public function gestioneIterProgetto(Richiesta $richiesta): Response {
        $indietro = $this->generateUrl("dettaglio_richiesta", ["id_richiesta" => $richiesta->getId()]);

        return $this->container->get('monitoraggio.iter_progetto')->getIstanza($richiesta)->modificaIterFaseRichiesta([
                'form_options' => [
                    'indietro' => $indietro
                ]
        ]);
    }

    public function hasNuovoModuloPagamento() {
        return true;
    }

    public function getAnniIntervento($finestra_temporale) {
        if ($finestra_temporale > 1) {
            $anni = array('2019' => '2019');
        } else {
            $anni = array('2018' => '2018', '2019' => '2019');
        }
        return $anni;
    }

    public function getTwigAutodichiarazioni() {
        return "RichiesteBundle:Richieste:autodichiarazioniAutorizzazioni.html.twig";
    }

    /**
     * @param int $id_richiesta
     * @param array $opzioni
     */
    public function gestioneProgramma($id_richiesta, $opzioni = array()) {
        // Se necessario implementare nelle classi figlie.
    }

    public function getTipiDocumentiCodiceDescrizione($id_richiesta, $solo_obbligatori) {
        /** @var TipologiaDocumento[] $tipiDocumenti */
        $tipiDocumenti = $this->getTipiDocumenti($id_richiesta, $solo_obbligatori);
        $retVal = [];
        foreach ($tipiDocumenti as $tipoDocumento) {
            $retVal[$tipoDocumento->getCodice()] = $tipoDocumento->getDescrizione();
        }

        return $retVal;
    }

    /**
     * @param $id_richiesta
     * @param $solo_obbligatori
     * @return array|TipologiaDocumento[]
     */
    public function getTipiDocumentiCodiceDescrizioneNonCaricati($id_richiesta, $solo_obbligatori) {
        /** @var Richiesta $richiesta */
        $richiesta = $this->getEm()->getRepository('RichiesteBundle:Richiesta')->find($id_richiesta);

        $documentiObbligatori = $this->getEm()->getRepository("DocumentoBundle\Entity\TipologiaDocumento")
            ->findBy(["obbligatorio" => $solo_obbligatori, "procedura" => $richiesta->getProcedura(), "tipologia" => 'richiesta']);

        $documentiCaricati = $richiesta->getDocumentiRichiesta();

        foreach ($documentiObbligatori as $key => $documentoObbligatorio) {
            foreach ($documentiCaricati as $documentoCaricato) {
                if ($documentoObbligatorio->getCodice() == $documentoCaricato->getDocumentoFile()->getTipologiaDocumento()->getCodice()) {
                    unset($documentiObbligatori[$key]);
                }
            }
        }
        return $documentiObbligatori;
    }

    /**
     * @param Procedura|null $procedura
     * @return IGestoreProponenti
     * @throws Exception
     */
    protected function getGestoreProponenti(?Procedura $procedura = null): IGestoreProponenti {
        return $this->container->get("gestore_proponenti")->getGestore($procedura);
    }

    public function eliminaRichiesta(Richiesta $richiesta): Response {
        if (!$this->isRichiestaEliminabile($richiesta)) {
            throw new AccessDeniedException('Impossibile cancellare progetto');
        }

        $em = $this->getEm();
        try {
            foreach ($richiesta->getProponenti() as $proponente) {
                $richiesta->removeProponenti($proponente);
                $em->remove($proponente);
            }
            foreach ($richiesta->getVociPianoCosto() as $voce) {
                $richiesta->removeVociPianoCosto($voce);
                $em->remove($voce);
            }
            foreach ($richiesta->getRichiesteProtocollo() as $protocollo) {
                $richiesta->removeRichiesteProtocollo($protocollo);
                $em->remove($protocollo);
            }
            $em->remove($richiesta);

            $em->flush();

            $this->addSuccess('Operazione effettuata correttamente');
        } catch (\Exception $e) {
            $this->container->get('logger')->error($e->getTraceAsString());
            $this->addError('Errore di salvataggio');
        }

        return $this->redirectToRoute('elenco_richieste');
    }

    public function hasIncrementoDaOggetto($richiesta) {
        return false;
    }

    public function hasIncrementoDaFascicolo($proponente) {
        return false;
    }

    public function hasIncrementoDaOccupazioneProponente($proponente) {
        return false;
    }

    public function hasIncrementoDaRisorse($richiesta) {
        return false;
    }

    /**
     * @param Richiesta $richiesta
     * @return bool
     */
    public function isIncaricatoLegaleRappresentanteODelegato(Richiesta $richiesta) {
        /** @var Soggetto $soggetto */
        $soggetto = $richiesta->getMandatario()->getSoggetto();
        foreach ($soggetto->getIncarichiPersone() as $incaricato) {
            $soggetto = $incaricato->getSoggetto();

            if ($incaricato->getStato() == 'ATTIVO' && $soggetto->getId() == $richiesta->getMandatario()->getSoggetto()->getId()) {
                if (($incaricato->getTipoIncarico()->getCodice() == 'LR' || $incaricato->getTipoIncarico()->getCodice() == 'DELEGATO') && ($this->getUser()->getUsername() == $incaricato->getIncaricato()->getCodiceFiscale())) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param $id_richiesta
     * @param array $opzioni
     * @throws SfingeException
     */
    public function validaDocumentiDropzone($id_richiesta, $opzioni = []) {
        $esito = new EsitoValidazione(true);
        /** @var TipologiaDocumento[] $documenti_obbligatori */
        $documenti_obbligatori = $this->getTipiDocumenti($id_richiesta, 1);
        $codice_documento_video = $this->getCodiceVideoDiPresentazione($id_richiesta);
        foreach ($documenti_obbligatori as $documento) {
            if ($documento->getCodice() == $codice_documento_video) {
                $esito->setEsito(false);
                $esito->addMessaggioSezione("Caricare il video di presentazione");
            }
        }

        return $esito;
    }

    /**
     * @param $id_richiesta
     * @param $opzioni
     * @return GestoreResponse
     */
    public function elencoDocumentiRichiestaDropzone($id_richiesta, $opzioni = []): GestoreResponse {
        set_time_limit(0);
        $em = $this->getEm();

        /** @var Richiesta $richiesta */
        $richiesta = $em->getRepository('RichiesteBundle:Richiesta')->find($id_richiesta);

        $codice_documento_video = $this->getCodiceVideoDiPresentazione($id_richiesta);
        $documenti_caricati = $em->getRepository("RichiesteBundle\Entity\DocumentoRichiesta")->findDocumentiCaricati($id_richiesta);
        $isFileCaricato = false;
        foreach ($documenti_caricati as $key => $tipo) {
            if ($tipo->getDocumentoFile()->getTipologiaDocumento()->getCodice() == $codice_documento_video) {
                $isFileCaricato = true;
            } else {
                unset($documenti_caricati[$key]);
            }
        }

        $dati = [
            'id_richiesta' => $id_richiesta,
            'is_richiesta_disabilitata' => $this->isRichiestaDisabilitata(),
            'is_file_caricato' => $isFileCaricato,
            'documenti_caricati' => $documenti_caricati,
            'richiesta' => $richiesta,
        ];

        $this->container->get('pagina')->setTitolo('Video di presentazione');
        $this->container->get('pagina')->setSottoTitolo('carica video di presentazione');
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Video di presentazione");

        $response = $this->render("RichiesteBundle:Richieste:elencoDocumentiRichiestaDropzone.html.twig", $dati);
        return new GestoreResponse($response, "RichiesteBundle:Richieste:elencoDocumentiRichiestaDropzone.html.twig", $dati);
    }

    /**
     * @param Request $request
     * @param $id_richiesta
     * @return array|string[]
     */
    public function caricaDocumentoDropzone(Request $request, $id_richiesta) {
        set_time_limit(0);
        $em = $this->getEm();

        /** @var Richiesta $richiesta */
        $richiesta = $em->getRepository('RichiesteBundle:Richiesta')->find($id_richiesta);

        $codice_documento_video = $this->getCodiceVideoDiPresentazione($id_richiesta);
        $documenti_caricati = $em->getRepository("RichiesteBundle\Entity\DocumentoRichiesta")->findDocumentiCaricati($id_richiesta);
        foreach ($documenti_caricati as $tipo) {
            if ($tipo->getDocumentoFile()->getTipologiaDocumento()->getCodice() == $codice_documento_video) {
                return ['status' => 'error', 'info' => 'Video di presentazione già caricato'];
            }
        }

        if ($this->isRichiestaDisabilitata()) {
            return ['status' => 'error', 'info' => 'La richiesta di contributo è disabilitata'];
        }

        /** @var UploadedFile $file */
        $file = $request->files->get('file');

        $fileId = $request->get('dzuuid');
        $chunkIndex = $request->get('dzchunkindex') + 1;

        // Imposto la directory di uplaod
        $tipologiaDocumento = $em->getRepository('DocumentoBundle:TipologiaDocumento')->findOneByCodice($codice_documento_video);
        $targetPath = $this->container->get("documenti")->getRealPath($richiesta, $tipologiaDocumento->getTipologia());

        $fileName = $fileId . '.' . $chunkIndex;

        if (!$file->move($targetPath, $fileName)) {
            return ['status' => 'error', 'info' => 'Errore nello spostamento dei file'];
        }

        return ['status' => 'success', null];
    }

    /**
     * @param Request|null $request
     * @param $id_richiesta
     * @return array
     */
    public function concatChunksDocumentoDropzone(Request $request = null, $id_richiesta) {
        set_time_limit(0);
        $em = $this->getEm();
        /** @var Richiesta $richiesta */
        $richiesta = $em->getRepository('RichiesteBundle:Richiesta')->find($id_richiesta);
        $codice_documento_video = $this->getCodiceVideoDiPresentazione($id_richiesta);

        $fileId = $request->get('dzuuid');
        $chunkTotal = $request->get('dztotalchunkcount');
        $filename = $request->get('filename');

        $tipologiaDocumento = $em->getRepository('DocumentoBundle:TipologiaDocumento')->findOneByCodice($codice_documento_video);

        $prefix = $tipologiaDocumento->getPrefix();
        $path = $this->container->get("documenti")->getRealPath($richiesta);

        $originalFileName = preg_replace("/[^a-zA-Z0-9_. -]{1}/", "_", $filename);
        $nome = str_replace(' ', '_', $prefix . "_" . $this->container->get("documenti")->getMicroTime() . "_" . $originalFileName);
        $destinazione = $path . $nome;

        // prendo il nome file originale
        $originalFileName = $filename;

        // imposto la directory di uplaod
        $targetPath = $this->container->get("documenti")->getRealPath($richiesta, $tipologiaDocumento->getTipologia());

        for ($i = 1; $i <= $chunkTotal; $i++) {
            $temp_file_path = $targetPath . $fileId . '.' . $i;
            $chunk = file_get_contents($temp_file_path);

            file_put_contents($destinazione, $chunk, FILE_APPEND | LOCK_SH);

            unlink($temp_file_path);
        }

        $md5 = md5_file($destinazione);
        // calcolo le dimensioni
        $fileDimension = filesize($destinazione);
        // prendo il mimeType
        $fileMimeType = mime_content_type($destinazione);

        $documentoFile = new DocumentoFile();
        $documentoFile->setNomeOriginale($originalFileName);
        $documentoFile->setMimeType($fileMimeType);
        $documentoFile->setFileSize($fileDimension);
        $documentoFile->setMd5($md5);
        $documentoFile->setNome($nome);
        $documentoFile->setPath($targetPath);
        $documentoFile->setTipologiaDocumento($tipologiaDocumento);

        $em->persist($documentoFile);

        $documentoRichiesta = new DocumentoRichiesta();
        $documentoRichiesta->setRichiesta($richiesta);
        $documentoRichiesta->setDocumentoFile($documentoFile);
        $em->persist($documentoRichiesta);
        $em->flush();
        return [
            'status' => 'success',
            null,
            'uploaded' => true,
            'nomeOriginale' => $originalFileName,
        ];
    }

    public function isFsc() {
        return false;
    }

    /**
     * @param $id_richiesta
     * @return string
     */
    public function getCodiceVideoDiPresentazione($id_richiesta): string
    {
        /** @var Richiesta $richiesta */
        $richiesta = $this->getEm()->getRepository('RichiesteBundle:Richiesta')->find($id_richiesta);
        $tipologiaVideoPresentazionePerBando = 'VIDEO_DI_PRESENTAZIONE_' . $richiesta->getProcedura()->getId();
        $tipologiaVideoPresentazione = $this->getEm()->getRepository('DocumentoBundle:TipologiaDocumento')
            ->findOneBy(['tipologia' => 'richiesta', 'codice' => $tipologiaVideoPresentazionePerBando]);
        if ($tipologiaVideoPresentazione) {
            return $tipologiaVideoPresentazione->getCodice();
        }
        return 'VIDEO_DI_PRESENTAZIONE';
    }

    /**
     * @param Richiesta $richiesta
     * @param Utente $utente
     * @param string $returnValue
     * @return false|string|null
     */
    public function getRuoloUtenteCorrente(Richiesta $richiesta, Utente $utente, string $returnValue = 'codice') {
        /** @var Soggetto $soggetto */
        $soggetto = $richiesta->getMandatario()->getSoggetto();
        foreach ($soggetto->getIncarichiPersone() as $incaricato) {
            if ($incaricato->getStato() == 'ATTIVO') {
                if ($incaricato->getTipoIncarico()->getCodice() == 'LR' && $utente->getUsername() == $incaricato->getIncaricato()->getCodiceFiscale()) {
                    // $ruoloFirmatario = $incarico->getTipoIncarico()->getDescrizione();
                    if ($returnValue == 'codice') {
                        return TipoIncarico::LR;
                    } else {
                        return $incaricato->getTipoIncarico()->getDescrizione();
                    }
                } elseif ($incaricato->getTipoIncarico()->getCodice() == 'DELEGATO' && $utente->getUsername() == $incaricato->getIncaricato()->getCodiceFiscale()) {
                    if ($returnValue == 'codice') {
                        return TipoIncarico::DELEGATO;
                    } else {
                        return $incaricato->getTipoIncarico()->getDescrizione();
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param Richiesta $richiesta
     * @return string
     * @throws Exception
     */
    public function generaPdfMarcaDaBolloDigitale(Richiesta $richiesta): string {
        $dati = [
            'id_richiesta' => $richiesta->getId(),
            'id_procedura' => $richiesta->getProcedura()->getId(),
            'timestamp' => (int) (microtime(true) * 1000),
        ];

        /** @var PdfWrapper $pdf */
        $pdf = $this->container->get("pdf");
        $pdf->setPageOrientation('portrait');
        $pdf->load('@Richieste/Pdf/pdf_documento_marca_da_bollo_digitale.html.twig', $dati);
        return $pdf->binaryData();
    }

    /**
     * @param Richiesta $richiesta
     * @return mixed
     */
    public function getIncarichi(Richiesta $richiesta) {
        /** @var Soggetto $soggetto */
        $soggetto = $richiesta->getMandatario()->getSoggetto();
        $dati['cf_legali_rappresentanti'] = [];
        $dati['cf_delegati'] = [];
        foreach ($soggetto->getIncarichiPersone() as $incaricato) {
            $soggetto = $incaricato->getSoggetto();

            if ($incaricato->getStato() == 'ATTIVO' && $soggetto->getId() == $richiesta->getMandatario()->getSoggetto()->getId()) {
                if ($incaricato->getTipoIncarico()->getCodice() == 'LR') {
                    $dati['cf_legali_rappresentanti'][] = $incaricato->getIncaricato()->getCodiceFiscale();
                } elseif ($incaricato->getTipoIncarico()->getCodice() == 'DELEGATO') {
                    $dati['cf_delegati'][] = $incaricato->getIncaricato()->getCodiceFiscale();
                }
            }
        }

        return $dati;
    }

    public function creaMandatario(Richiesta $richiesta): \RichiesteBundle\Entity\Proponente {
        $proponente = new \RichiesteBundle\Entity\Proponente($richiesta);
        $soggetto = $this->getSoggetto();
        $proponente->setSoggetto($soggetto);
        $proponente->setMandatario(true);

        return $proponente;
    }
}
