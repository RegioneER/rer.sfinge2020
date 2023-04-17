<?php

namespace RichiesteBundle\Controller;

use BaseBundle\Entity\StatoRichiesta;
use BaseBundle\Exception\SfingeException;
use DocumentoBundle\Entity\TipologiaDocumento;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use RichiesteBundle\Form\SelezioneBandoType;
use RichiesteBundle\Entity\DocumentoRichiesta;
use DocumentoBundle\Entity\DocumentoFile;
use BaseBundle\Annotation\ControlloAccesso;
use DocumentoBundle\Component\ResponseException;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/beneficiario")
 */
class PresentazioneBeneficiarioController extends AbstractController {

    /**
     * @Route("/selezione_bando", name="selezione_bando")
     * @Method({"GET", "POST"})
     * @Template("RichiesteBundle:Richieste:selezionaBando.html.twig")
     * @PaginaInfo(titolo="Elenco bandi", sottoTitolo="mostra l'elenco dei bandi disponibili")
     * @Menuitem(menuAttivo="selezionaBando")
     */
    public function selezionaBandoAction(Request $request) {
        $soggettoSession = $this->getSession()->get(self::SESSIONE_SOGGETTO);
        $soggetto = $this->getEm()->getRepository("SoggettoBundle\Entity\Soggetto")->findOneById($soggettoSession->getId());
        $em = $this->getEm();
        if (\is_null($soggetto)) {
            return $this->addErrorRedirect("Soggetto non valido", "home");
        }

        $legaleRappr = $em->getRepository("SoggettoBundle\Entity\Soggetto")->getLegaleRappresentante($soggetto);

        if (count($legaleRappr) == 0) {
            $legaleRapprDaConfermare = $em->getRepository("SoggettoBundle\Entity\Soggetto")->getLegaleRappresentanteDaConfermare($soggetto);
            if (count($legaleRapprDaConfermare) != 0) {
                return $this->addErrorRedirect("Non risulta un legale rappresentante attivo", "elenco_incarichi");
            } else {
                return $this->addErrorRedirect("Non è stato inserito alcun legale rappresentate, oppure l'incarico risulta revocato", "elenco_incarichi");
            }
        }
        $OpRich = $this->getEm()->getRepository("SoggettoBundle:IncaricoPersona")->haIncaricoPersonaOpRichAttivo($soggetto, $this->getPersona()->getCodiceFiscale());
        if ($OpRich) {
            return $this->addErrorRedirect("Il ruolo di operatore progetto non è abilitato alla creazione di nuove richieste", "home");
        }

        $this->creaRichiesta();
        $this->richiesta->setAbilitaGestioneBandoChiuso(false);

        $options["url_indietro"] = $this->generateUrl("elenco_richieste");

        $form = $this->createForm(SelezioneBandoType::class, $this->richiesta, $options);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $procedura = $this->richiesta->getProcedura();

            if (\is_null($procedura)) {
                $this->addFlash('error', "Non è stato selezionato alcun bando");
                return ["form" => $form->createView()];
            }
            $gestoreRichieste = $this->get("gestore_richieste")->getGestore($procedura);
            $richiesteDaSoggetto = $gestoreRichieste->getRichiesteDaSoggetto($soggetto->getId(), $procedura->getId());
            if (\count($richiesteDaSoggetto) > 0) {
                if ($procedura->getAbilitaReinvioNonAmmesse()) {
                    if (\count($richiesteDaSoggetto) >= $procedura->getNumeroRichieste()) {
                        if ($gestoreRichieste->hasRichiesteAmmesseInIstruttoria($richiesteDaSoggetto)) {
                            $form->get('procedura')->addError(new FormError("È già stato raggiunto il numero di richieste ammesso per la procedura / bando selezionato"));
                        }
                    }
                } elseif (\count($richiesteDaSoggetto) >= $procedura->getNumeroRichieste()) {
                    $form->get('procedura')->addError(new FormError("È già stato raggiunto il numero di richieste ammesso per la procedura / bando selezionato"));
                }
            }
            
            $erroreCapofila = $gestoreRichieste->controllaCapofila();
            if (!\is_null($erroreCapofila)) {
                $form->addError(new FormError($erroreCapofila));
            }

            if ($form->isValid()) {
                try {
                    $this->setInfoRichiestaDaProcedura();

                    // In caso di unica tipologia di pagamento di marca da bollo e con esenzione non possibile
                    // popolo subito il campo della tipologia.
                    if (!$procedura->getEsenzioneMarcaBollo() && !is_null($procedura->getTipologiaMarcaDaBollo())
                        && ($procedura->getTipologiaMarcaDaBollo() == 'FISICA' || $procedura->getTipologiaMarcaDaBollo() == 'DIGITALE') ) {
                        $this->richiesta->setTipologiaMarcaDaBollo($procedura->getTipologiaMarcaDaBollo());
                    }

                    $em->persist($this->richiesta);
                    $em->flush();

                    // Se è prevista la marca da bollo in formato digitale oppure è prevista la scelta per la
                    // marca da bollo fisica o digitale, creo il documento.
                    if ($this->richiesta->getProcedura()->getMarcaDaBollo() && ($this->richiesta->getProcedura()->getTipologiaMarcaDaBollo() == Procedura::MARCA_DA_BOLLO_FISICA_E_DIGITALE
                        || $this->richiesta->getProcedura()->getTipologiaMarcaDaBollo() == Procedura::MARCA_DA_BOLLO_DIGITALE)) {
                        // Creo il PDF per l'eventuale pagamento della marca da bollo digitale
                        $pdfDocumentoMarcaDaBollo = $gestoreRichieste->generaPdfMarcaDaBolloDigitale($this->richiesta);

                        // Persisto il documento
                        $tipoDocumento = $this->getEm()->getRepository("DocumentoBundle:TipologiaDocumento")
                            ->findOneByCodice(TipologiaDocumento::DOCUMENTO_MARCA_DA_BOLLO_DIGITALE);
                        $documentoMarcaDaBolloDigitale = $this->container->get("documenti")
                            ->caricaDaByteArray($pdfDocumentoMarcaDaBollo, $this->getNomePdfMarcaDaBolloDigitale($this->richiesta->getId()) . ".pdf", $tipoDocumento, false, $this->richiesta);

                        // Associo il documento alla richiesta
                        $this->richiesta->setDocumentoMarcaDaBolloDigitale($documentoMarcaDaBolloDigitale);

                        $em->persist($this->richiesta);
                        $em->flush();
                    }

                    $this->addFlash('success', "Modifiche salvate correttamente");
                    return $this->redirect($this->generateUrl('nuova_richiesta', ["id_richiesta" => $this->richiesta->getId()]));
                } catch (ResponseException|Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }

        return ["form" => $form->createView()];
    }

    /**
     * @Route("/{id_richiesta}/nuova_richiesta", name="nuova_richiesta")
     * @Method({"GET", "POST"})
     * Impossibile fare il controllo perchè il proponente non è ancora noto
     * ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"})
     * @param mixed $id_richiesta
     */
    public function nuovaRichiestaAction($id_richiesta) {
        $soggettoSession = $this->getSession()->get(self::SESSIONE_SOGGETTO);
        $soggetto = $this->getEm()->getRepository("SoggettoBundle\Entity\Soggetto")->findOneById($soggettoSession->getId());
        if (is_null($soggetto)) {
            return $this->addErrorRedirect("Soggetto non valido", "home");
        }
        $OpRich = $this->getEm()->getRepository("SoggettoBundle:IncaricoPersona")->haIncaricoPersonaOpRichAttivo($soggetto, $this->getPersona()->getCodiceFiscale());
        if ($OpRich) {
            return $this->addErrorRedirect("Il ruolo di operatore progetto non è abilitato alla creazione di nuove richieste", "home");
        }
        $gestore = $this->get("gestore_richieste")->getGestore();
        $response = $gestore->nuovaRichiesta($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/cerca_proponente/{page}", defaults={"page" : 1}, name="cerca_proponente")
     * @PaginaInfo(titolo="Aggiunta proponente", sottoTitolo="pagina per cercare ed aggungere un nuovo proponente")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco proponenti", route="elenco_proponenti", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Aggiunta proponente")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
     * @param mixed $id_richiesta
     */
    public function cercaProponenteAction($id_richiesta) {
        try {
            $response = $this->get("gestore_proponenti")->getGestore()->cercaProponente($id_richiesta);
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste");
        } catch (\Exception $e) {
            //mettere log
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }
    }

    /**
     * @Route("/{id_richiesta}/inserisci_proponente/{id_soggetto}", name="inserisci_proponente")
     * @Method({"GET"})
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
     * @param mixed $id_richiesta
     * @param mixed $id_soggetto
     */
    public function inserisciProponenteAction($id_richiesta, $id_soggetto) {
        try {
            $response = $this->get("gestore_proponenti")->getGestore()->associaProponente($id_richiesta, $id_soggetto);
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste");
        } catch (\Exception $e) {
            //mettere log
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }
    }

    /**
     * @Route("/{id_richiesta}/rimuovi_proponente/{id_proponente}", name="rimuovi_proponente")
     * @ControlloAccesso(contesto="soggettoMandatario", classe="RichiesteBundle:Proponente", opzioni={"id" : "id_proponente"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
     * @param mixed $id_richiesta
     * @param mixed $id_proponente
     */
    public function rimuoviProponenteAction($id_richiesta, $id_proponente) {
        $this->get('base')->checkCsrf('token');
        try {
            $response = $this->get("gestore_proponenti")->getGestore()->rimuoviProponente($id_proponente);
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste");
        } catch (\Exception $e) {
            //mettere log
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }
    }

    /**
     * @Route("/{id_richiesta}/modifica_firmatario", name="modifica_firmatario")
     * @PaginaInfo(titolo="Modifica firmatario", sottoTitolo="pagina per modificare il firmatario della richiesta")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco proponenti", route="elenco_proponenti", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Modifica firmatario")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
     * @param mixed $id_richiesta
     */
    public function modificaFirmatarioAction($id_richiesta) {
        try {
            $response = $this->get("gestore_proponenti")->getGestore()->modificaFirmatario($id_richiesta);
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste");
        } catch (\Exception $e) {
            //mettere log
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }
    }

    /**
     * @Route("/{id_richiesta}/cerca_referente/{id_proponente}/{page}", defaults={"page" : 1}, name="cerca_referente")
     * @PaginaInfo(titolo="Aggiunta referente", sottoTitolo="pagina per cercare ed aggiungere un nuovo referente")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco proponenti", route="elenco_proponenti", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Dettagli proponente", route="dettaglio_proponente", parametri={"id_richiesta", "id_proponente"}),
     * 				@ElementoBreadcrumb(testo="Aggiunta referente")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggettoMandatario", classe="RichiesteBundle:Proponente", opzioni={"id" : "id_proponente"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
     * @param mixed $id_richiesta
     * @param mixed $id_proponente
     */
    public function cercaReferenteAction($id_richiesta, $id_proponente) {
        try {
            $response = $this->get("gestore_proponenti")->getGestore()->cercaReferente($id_proponente);
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste");
        } catch (\Exception $e) {
            //mettere log
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }

        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/cerca_referente_intervento/{id_intervento}/{page}", defaults={"page" : 1}, name="cerca_referente_intervento")
     * @PaginaInfo(titolo="Aggiunta referente", sottoTitolo="pagina per cercare ed aggiungere un nuovo referente")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Aggiunta referente")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * ControlloAccesso(contesto="soggettoMandatario", classe="RichiesteBundle:Proponente", opzioni={"id" = "id_proponente"})
     * @param mixed $id_richiesta
     * @param mixed $id_intervento
     */
    public function cercaReferenteInterventoAction($id_richiesta, $id_intervento) {
        try {
            $response = $this->get("gestore_proponenti")->getGestore()->cercaReferenteIntervento($id_intervento);
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste");
        } catch (\Exception $e) {
            //mettere log
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }

        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/elenco_referenti_proponente/{id_proponente}", name="elenco_referenti_proponente")
     * @PaginaInfo(titolo="Elenco referenti", sottoTitolo="referenti relativi ad un proponente associato ad una richiesta")
     * @Breadcrumb(elementi={
     *              @ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     *              @ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     *              @ElementoBreadcrumb(testo="Elenco referenti")
     *              })
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggettoMandatario", classe="RichiesteBundle:Proponente", opzioni={"id" : "id_proponente"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     * @param mixed $id_proponente
     * @return mixed
     * @throws Exception
     */
    public function elencoReferentiProponenteAction($id_richiesta, $id_proponente) {
        $response = $this->get("gestore_proponenti")->getGestore()->elencoReferentiProponente($id_richiesta, $id_proponente);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/elenco_sedi_proponente/{id_proponente}", name="elenco_sedi_proponente")
     * @PaginaInfo(titolo="Elenco sedi", sottoTitolo="sedi relativi ad un proponente associato ad una richiesta")
     * @Breadcrumb(elementi={
     *              @ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     *              @ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     *              @ElementoBreadcrumb(testo="Elenco sedi")
     *              })
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggettoMandatario", classe="RichiesteBundle:Proponente", opzioni={"id" : "id_proponente"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_richiesta
     * @param mixed $id_proponente
     * @return mixed
     * @throws Exception
     */
    public function elencoSediProponenteAction($id_richiesta, $id_proponente) {
        $response = $this->get("gestore_proponenti")->getGestore()->elencoSediProponente($id_richiesta, $id_proponente);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/cerca_sede/{id_proponente}/{page}", defaults={"page" : 1}, name="cerca_sede")
     * @PaginaInfo(titolo="Aggiunta sede", sottoTitolo="Aggiungi una sede operativa\intervento")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco proponenti", route="elenco_proponenti", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Dettagli proponente", route="dettaglio_proponente", parametri={"id_richiesta", "id_proponente"}),
     * 				@ElementoBreadcrumb(testo="Aggiunta sede operativa\intervento")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggettoMandatario", classe="RichiesteBundle:Proponente", opzioni={"id" : "id_proponente"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
     * @param mixed $id_richiesta
     * @param mixed $id_proponente
     */
    public function cercaSedeOperativaAction($id_richiesta, $id_proponente) {
        try {
            $response = $this->get("gestore_proponenti")->getGestore()->cercaSedeOperativa($id_proponente);
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste");
        } catch (\Exception $e) {
            //mettere log
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }
    }

    /**
     * @Route("/{id_richiesta}/inserisci_sede_operativa/{id_proponente}/{id_sede}", name="inserisci_sede_operativa")
     * @Method({"GET", "POST"})
     * @ControlloAccesso(contesto="soggettoMandatario", classe="RichiesteBundle:Proponente", opzioni={"id" : "id_proponente"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
     * @param mixed $id_richiesta
     * @param mixed $id_proponente
     * @param mixed $id_sede
     */
    public function inserisciSedeOperativaAction($id_richiesta, $id_proponente, $id_sede) {
        //$this->get('base')->checkCsrf('token');
        try {
            $response = $this->get("gestore_proponenti")->getGestore()->inserisciSedeOperativa($id_proponente, $id_sede);
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste");
        } catch (\Exception $e) {
            //mettere log
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }
    }

    /**
     * @Route("/{id_richiesta}/inserisci_referente/{id_proponente}/{persona_id}", name="inserisci_referente")
     * @Method({"GET", "POST"})
     * @PaginaInfo(titolo="Selezione tipologia", sottoTitolo="pagina per indicare la tipologia di relazione con il proponente")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco proponenti", route="elenco_proponenti", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Dettagli proponente", route="dettaglio_proponente", parametri={"id_richiesta", "id_proponente"}),
     * 				@ElementoBreadcrumb(testo="Aggiunta referente", route="cerca_referente", parametri={"id_richiesta", "id_proponente"}),
     * 				@ElementoBreadcrumb(testo="Selezione tipologia")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggettoMandatario", classe="RichiesteBundle:Proponente", opzioni={"id" : "id_proponente"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
     * @param mixed $id_richiesta
     * @param mixed $id_proponente
     * @param mixed $persona_id
     */
    public function inserisciReferenteAction($id_richiesta, $id_proponente, $persona_id) {
        try {
            $response = $this->get("gestore_proponenti")->getGestore()->inserisciReferente($id_proponente, $persona_id);
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste");
        } catch (\Exception $e) {
            //mettere log
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }
    }

    /**
     * @Route("/{id_richiesta}/inserisci_referente_intervento/{id_intervento}/{persona_id}", name="inserisci_referente_intervento")
     * @Method({"GET", "POST"})
     * @PaginaInfo(titolo="Selezione tipologia", sottoTitolo="pagina per indicare la tipologia di relazione con il proponente")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco interventi", route="elenco_interventi", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Selezione tipologia")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * ControlloAccesso(contesto="soggettoMandatario", classe="RichiesteBundle:Proponente", opzioni={"id" = "id_proponente"})
     * @param mixed $id_richiesta
     * @param mixed $id_intervento
     * @param mixed $persona_id
     */
    public function inserisciReferenteInterventoAction($id_richiesta, $id_intervento, $persona_id) {
        try {
            $response = $this->get("gestore_proponenti")->getGestore()->inserisciReferenteIntervento($id_intervento, $persona_id);
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste");
        } catch (\Exception $e) {
            //mettere log
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }
    }

    /**
     * @Route("/{id_richiesta}/rimuovi_referente/{id_referente}", name="rimuovi_referente")
     * @ControlloAccesso(contesto="soggettoMandatario", classe="RichiesteBundle:Referente", opzioni={"id" : "id_referente"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
     * @param mixed $id_richiesta
     * @param mixed $id_referente
     */
    public function rimuoviReferenteAction($id_richiesta, $id_referente) {
        $this->get('base')->checkCsrf('token');
        try {
            $response = $this->get("gestore_proponenti")->getGestore()->rimuoviReferente($id_referente);
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste");
        } catch (\Exception $e) {
            //mettere log
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }
    }

    /**
     * @Route("/{id_richiesta}/rimuovi_referente_intervento/{id_referente}", name="rimuovi_referente_intervento")
     * ControlloAccesso(contesto="soggettoMandatario", classe="RichiesteBundle:Referente", opzioni={"id" = "id_referente"})
     * @param mixed $id_richiesta
     * @param mixed $id_referente
     */
    public function rimuoviReferenteInterventoAction($id_richiesta, $id_referente) {
        $this->get('base')->checkCsrf('token');
        try {
            $response = $this->get("gestore_proponenti")->getGestore()->rimuoviReferenteIntervento($id_referente);
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste");
        } catch (\Exception $e) {
            //mettere log
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }
    }

    /**
     * @Route("/{id_richiesta}/rimuovi_sede_operativa/{id_proponente}/{id_sede}", name="rimuovi_sede_operativa")
     * @ControlloAccesso(contesto="soggettoMandatario", classe="RichiesteBundle:Proponente", opzioni={"id" : "id_proponente"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
     * @param mixed $id_richiesta
     * @param mixed $id_proponente
     * @param mixed $id_sede
     */
    public function rimuoviSedeOperativaAction($id_richiesta, $id_proponente, $id_sede) {
        //$this->get('base')->checkCsrf('token');
        try {
            $response = $this->get("gestore_proponenti")->getGestore()->rimuoviSedeOperativa($id_proponente, $id_sede);
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste");
        } catch (\Exception $e) {
            //mettere log
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }
    }

    /**
     * @Route("/{id_richiesta}/aggiungi_persona_referente/{id_proponente}", name="aggiungi_persona_referente")
     * @PaginaInfo(titolo="Dettaglio referente")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco proponenti", route="elenco_proponenti", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Dettaglio proponente", route="dettaglio_proponente", parametri={"id_richiesta", "id_proponente"}),
     * 				@ElementoBreadcrumb(testo="Aggiunta referente", route="cerca_referente", parametri={"id_richiesta", "id_proponente"}),
     * 				@ElementoBreadcrumb(testo="Aggiungi persona")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggettoMandatario", classe="RichiesteBundle:Proponente", opzioni={"id" : "id_proponente"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
     * @param mixed $id_richiesta
     * @param mixed $id_proponente
     */
    public function aggiungiPersonaReferenteAction($id_richiesta, $id_proponente) {
        $parametriUrl = ["id_richiesta" => $id_richiesta, "id_proponente" => $id_proponente];
        $urlIndietro = $this->generateUrl("dettaglio_proponente", $parametriUrl);

        return $this->get("inserimento_persona")->inserisciPersona($urlIndietro, "inserisci_referente", $parametriUrl);
    }

    /**
     * @Route("/{id_richiesta}/aggiungi_persona_referente_non_annidata/{id_proponente}", name="aggiungi_persona_referente_non_annidata")
     * @PaginaInfo(titolo="Dettaglio referente")
     * @Breadcrumb(elementi={
     *              @ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     *              @ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     *              @ElementoBreadcrumb(testo="Dettaglio proponente", route="dettaglio_proponente", parametri={"id_richiesta", "id_proponente"}),
     *              @ElementoBreadcrumb(testo="Aggiunta referente", route="cerca_referente", parametri={"id_richiesta", "id_proponente"}),
     *              @ElementoBreadcrumb(testo="Aggiungi persona")
     *              })
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggettoMandatario", classe="RichiesteBundle:Proponente", opzioni={"id" : "id_proponente"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
     * @param mixed $id_richiesta
     * @param mixed $id_proponente
     * @return RedirectResponse|Response
     */
    public function aggiungiPersonaReferenteNonAnnidataAction($id_richiesta, $id_proponente) {
        $parametriUrl = ["id_richiesta" => $id_richiesta, "id_proponente" => $id_proponente];
        $urlIndietro = $this->generateUrl("cerca_referente", $parametriUrl);
        return $this->get("inserimento_persona")->inserisciPersona($urlIndietro, "inserisci_referente", $parametriUrl);
    }

    /**
     * @Route("/{id_richiesta}/aggiungi_persona_referente_intervento/{id_intervento}", name="aggiungi_persona_referente_intervento")
     * @PaginaInfo(titolo="Dettaglio referente")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Aggiungi persona")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * ControlloAccesso(contesto="soggettoMandatario", classe="RichiesteBundle:Proponente", opzioni={"id" = "id_proponente"})
     * @param mixed $id_richiesta
     * @param mixed $id_intervento
     */
    public function aggiungiPersonaReferenteInterventoAction($id_richiesta, $id_intervento) {
        $parametriUrl = ["id_richiesta" => $id_richiesta, "id_intervento" => $id_intervento];
        $urlIndietro = $this->generateUrl("elenco_interventi", $parametriUrl);

        return $this->get("inserimento_persona")->inserisciPersona($urlIndietro, "inserisci_referente", $parametriUrl);
    }

    /**
     * @Route("/{id_richiesta}/elimina_documento_richiesta/{id_documento_richiesta}", name="elimina_documento_richiesta")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:DocumentoRichiesta", opzioni={"id" : "id_documento_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
     * @param mixed $id_documento_richiesta
     * @param mixed $id_richiesta
     */
    public function eliminaDocumentoRichiestaAction($id_documento_richiesta, $id_richiesta) {
        $this->get('base')->checkCsrf('token');
        $response = $this->get("gestore_richieste")->getGestore()->eliminaDocumentoRichiesta($id_documento_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/elimina_documento_proponente/{id_documento_proponente}", name="elimina_documento_proponente")
     * @ControlloAccesso(contesto="soggettoMandatario", classe="RichiesteBundle:DocumentoProponente", opzioni={"id" : "id_documento_proponente"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
     * @param mixed $id_documento_proponente
     * @param mixed $id_richiesta
     */
    public function eliminaDocumentoProponenteAction($id_documento_proponente, $id_richiesta) {
        $this->get('base')->checkCsrf('token');
        $response = $this->get("gestore_proponenti")->getGestore()->eliminaDocumentoProponente($id_documento_proponente);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/valida_richiesta", name="valida_richiesta")
     * @Method({"GET"})
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
     * @param mixed $id_richiesta
     */
    public function validaRichiesta($id_richiesta) {
        $this->get('base')->checkCsrf('token');
        try {
            $response = $this->get("gestore_richieste")->getGestore()->validaRichiesta($id_richiesta);
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste");
        } catch (\Exception $e) {
            $this->get('monolog.logger.schema31')->error($e->getMessage());
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }
    }

    /**
     * @Route("/{id_richiesta}/invalida_richiesta", name="invalida_richiesta")
     * @Method({"GET"})
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
     * @param mixed $id_richiesta
     */
    public function invalidaRichiesta($id_richiesta) {
        $this->get('base')->checkCsrf('token');
        try {
            $response = $this->get("gestore_richieste")->getGestore()->invalidaRichiesta($id_richiesta);
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste");
        } catch (\Exception $e) {
            //mettere log
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }
    }

    /**
     * @Route("/{id_richiesta}/genera_pdf", name="genera_pdf")
     * @Method({"GET"})
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
     * @param mixed $id_richiesta
     */
    public function generaPdf($id_richiesta) {
        $richiesta = $this->getRichiesta($id_richiesta);
        try {
            return $this->get("gestore_richieste")->getGestore($richiesta->getProcedura())->generaPdf($id_richiesta);
        } catch(SfingeException $e) {
            throw $e;
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste");
        } catch(\Exception $e) {
            throw $e;
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }
    }

    /**
     * @Route("/{id_richiesta}/carica_domanda_firmata", name="carica_domanda_firmata")
     * @PaginaInfo(titolo="Carica richiesta firmata", sottoTitolo="pagina per caricare la richiesta di contributo firmata")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Carica richiesta firmata")
     * 				})
     * @Template("RichiesteBundle:Richieste:caricaDomandaFirmata.html.twig")
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
     * @param mixed $id_richiesta
     */
    public function caricaDomandaFirmataAction($id_richiesta) {
        $em = $this->getEm();

        $request = $this->getCurrentRequest();

        $documento_file = new DocumentoFile();

        $richiesta = $em->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);

        if (!$richiesta) {
            throw $this->createNotFoundException('Risorsa non trovata');
        }

        try {
            if ($this->get("gestore_richieste")->getGestore()->isRichiestaDisabilitataInoltro()) {
                throw new SfingeException("Il bando è chiuso e la richiesta non è più inoltrabile");
            }

            if (!$richiesta->getStato()->uguale(StatoRichiesta::PRE_VALIDATA)) {
                throw new SfingeException("Stato non valido per effettuare l'operazione");
            }

            /** @var Procedura $procedura */
            $procedura = $richiesta->getProcedura();

            if (!empty($procedura->getAttualeFinestraTemporalePresentazione())) {
                if ($richiesta->getFinestraTemporale() < $procedura->getAttualeFinestraTemporalePresentazione()) {
                    throw new SfingeException("Impossibile procedere. Si sta compilando una richiesta di contributo di una finestra precedente. E' necessario creare una nuova richiesta di contributo.");
                }
            }
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste");
        }

        $opzioni_form["tipo"] = TipologiaDocumento::RICHIESTA_CONTRIBUTO_FIRMATO;
        $opzioni_form["cf_firmatario"] = $richiesta->getFirmatario()->getCodiceFiscale();
        $form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documento_file, $opzioni_form);
        $form->add("pultanti", "BaseBundle\Form\SalvaIndietroType", ["url" => $this->generateUrl("elenco_richieste")]);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $this->container->get("documenti")->carica($documento_file, 0, $richiesta);
                    $richiesta->setDocumentoRichiestaFirmato($documento_file);
                    $this->get("sfinge.stati")->avanzaStato($richiesta, StatoRichiesta::PRE_FIRMATA);
                    $em->persist($richiesta);
                    $em->flush();
                    return $this->addSuccessRedirect("Documento caricato correttamente", "dettaglio_richiesta", ['id_richiesta' => $id_richiesta]);
                } catch (\Exception $e) {
                    //TODO gestire cancellazione del file
                    $this->addFlash('error', "Errore generico");
                }
            }
        }
        $form_view = $form->createView();

        return ["id_richiesta" => $id_richiesta, "form" => $form_view];
    }

    /**
     * @Route("/{id_richiesta}/invia_richiesta", name="invia_richiesta")
     * @Method({"GET"})
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
     * @param mixed $id_richiesta
     */
    public function inviaRichiesta($id_richiesta) {
        $this->get('base')->checkCsrf('token');
        try {
            $response = $this->get("gestore_richieste")->getGestore()->inviaRichiesta($id_richiesta);
            return $response->getResponse();
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste");
        } catch (\Exception $e) {
            //mettere log
            $this->container->get('logger')->error($e->getMessage());
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }
    }

    /**
     * @Route("/{id_richiesta}/elimina_plesso_edificio/{id_indirizzo_catastale}", name="elimina_plesso_edificio")
     *
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
     * @param mixed $id_richiesta
     * @param mixed $id_indirizzo_catastale
     */
    public function eliminaEdificioPlessoAction($id_richiesta, $id_indirizzo_catastale) {
        $this->get('base')->checkCsrf('token');
        $response = $this->get("gestore_richieste")->getGestore()->eliminaEdificioPlesso($id_richiesta, $id_indirizzo_catastale);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/crea_intervento", name="crea_intervento")
     * @PaginaInfo(titolo="Crea sede di intervento", sottoTitolo="")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Elenco sedi di intervento", route="elenco_interventi", parametri={"id_richiesta"}),
     * 				@ElementoBreadcrumb(testo="Crea sede di intervento")
     * 				})
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
     * @param mixed $id_richiesta
     */
    public function creaInteventoAction($id_richiesta) {
        $response = $this->get("gestore_richieste")->getGestore()->creaIntervento($id_richiesta);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/{id_intervento}/elimina_intervento", name="elimina_intervento")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
     * @param mixed $id_richiesta
     * @param mixed $id_intervento
     */
    public function eliminaInterventoAction($id_richiesta, $id_intervento) {
        $response = $this->get("gestore_richieste")->getGestore()->eliminaIntervento($id_richiesta, $id_intervento);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/inserisci_dimensione_impresa/{id_proponente}", name="inserisci_dimensione_impresa")
     * @PaginaInfo(titolo="Inserisci dimensione impresa")
     * @Breadcrumb(elementi={
     *              @ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     *              @ElementoBreadcrumb(testo="Dettaglio richiesta", route="dettaglio_richiesta", parametri={"id_richiesta"}),
     *              @ElementoBreadcrumb(testo="Elenco proponenti", route="elenco_proponenti", parametri={"id_richiesta"}),
     *              @ElementoBreadcrumb(testo="Dettagli proponente", route="dettaglio_proponente", parametri={"id_richiesta", "id_proponente"}),
     *              @ElementoBreadcrumb(testo="Inserisci dimensione impresa ")
     *              })
     * @Menuitem(menuAttivo="elencoRichieste")
     * @ControlloAccesso(contesto="soggettoMandatario", classe="RichiesteBundle:Proponente", opzioni={"id" : "id_proponente"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::WRITE)
     * @param int $id_richiesta
     * @param int $id_proponente
     * @return RedirectResponse
     * @throws Exception
     */
    public function inserisciDimensioneImpresaAction(int $id_richiesta, int $id_proponente) {
        try {
            $response = $this->get("gestore_proponenti")->getGestore()->inserisciDimensioneImpresa($id_proponente);
            return $response->getResponse();
        } catch (Exception $e) {
            return $this->addErrorRedirect("Errore generico", "elenco_richieste");
        }
    }

}
