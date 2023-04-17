<?php

namespace AttuazioneControlloBundle\Controller\Revoche;

use BaseBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use CertificazioniBundle\Entity\StatoChiusuraCertificazione;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;
use RichiesteBundle\Entity\Richiesta;
use AttuazioneControlloBundle\Entity\Revoche\Revoca;
use AttuazioneControlloBundle\Entity\RichiestaPagamento;
use AttuazioneControlloBundle\Entity\PagamentoAmmesso;
use AttuazioneControlloBundle\Entity\Pagamento;
use AttuazioneControlloBundle\Entity\Revoche\Recupero;
use MonitoraggioBundle\Entity\TC39CausalePagamento;
use AttuazioneControlloBundle\Form\Entity\Revoche\RicercaAttoRevoca;
use AttuazioneControlloBundle\Entity\Revoche\AttoRevoca;
use DocumentoBundle\Entity\DocumentoFile;
use DocumentoBundle\Entity\TipologiaDocumento;
use AttuazioneControlloBundle\Entity\Revoche\RataRecupero;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/revoche/gestione")
 */
class RevocaRecuperoController extends BaseController {

    /**
     * @Route("/elenco_revoca/{sort}/{direction}/{page}", defaults={"sort" = "a.id", "direction" = "asc", "page" = "1"}, name="elenco_atti_revoca")
     * @Menuitem(menuAttivo = "elencoAttoRevoca")
     * @PaginaInfo(titolo="Elenco atti revoca", sottoTitolo="pagina per gestione degli atti di revoca")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco atti revoca")})
     */
    public function elencoAttiAction() {

        $datiRicerca = new RicercaAttoRevoca();
        $risultato = $this->get("ricerca")->ricerca($datiRicerca);

        return $this->render('AttuazioneControlloBundle:Revoche:elencoAtti.html.twig', array('atti' => $risultato["risultato"], "formRicercaAtto" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"]));
    }

    /**
     * @Route("/crea_atto_revoca", name="crea_atto_revoca")
     * @Template("AttuazioneControlloBundle:Revoche:atto.html.twig")
     * @PaginaInfo(titolo="Nuovo Atto revoca",sottoTitolo="pagina per creare un nuovo atto di revoca")
     * @Menuitem(menuAttivo = "elencoAttoRevoca")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco atti revoca", route="elenco_atti_revoca"), 
     *                       @ElementoBreadcrumb(testo="Crea Atto di revoca")})
     */
    public function creaAttoAction() {

        $em = $this->getDoctrine()->getManager();
        $atto = new AttoRevoca();
        $request = $this->getCurrentRequest();
        $options["readonly"] = (!$this->isGranted("ROLE_REVOCHE") && !$this->isGranted("ROLE_SUPER_ADMIN"));
        $options["url_indietro"] = $this->generateUrl("elenco_atti_revoca");
        $options["mostra_indietro"] = false;

        $documento = new DocumentoFile();
        $atto->setDocumento($documento);
        $options["documento_opzionale"] = false;
        $options["TIPOLOGIA_DOCUMENTO"] = $em->getRepository(TipologiaDocumento::class)->findOneByCodice('ATTO_REVOCA');

        $form = $this->createForm('AttuazioneControlloBundle\Form\Revoche\AttoRevocaType', $atto, $options);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $id_atto = $atto->getId();
            if ($atto->getTipo()->getCodice() == 'PAR' && $atto->getTipoMotivazione()->getCodice() == '1') {
                return $this->addErrorRedirect("Se la revoca è parziale non è possbile selezionare la rinuncia del beneficiario come motivazione ", "crea_atto_revoca");
            }
            if ($atto->getTipo()->getCodice() == 'RIN' && $atto->getTipoMotivazione()->getCodice() != '1') {
                return $this->addErrorRedirect("Se la revoca è per rinuncia è possbile selezionare solo la rinuncia del beneficiario come motivazione ", "crea_atto_revoca");
            }

            if ($form->isValid()) {
                try {
                    $this->get("documenti")->carica($documento);

                    $em->persist($atto);
                    $em->flush();
                    $this->addFlash('success', "Atto di revoca caricato correttamente");

                    return $this->redirect($this->generateUrl('elenco_atti_revoca'));
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["atto"] = $atto;
        $form_params["lettura"] = false;

        return $form_params;
    }

    /**
     * @Route("/modifica_atto_revoca/{id_atto}", name="modifica_atto_revoca")
     * @Template("AttuazioneControlloBundle:Revoche:atto.html.twig")
     * @PaginaInfo(titolo="Modifica Atto",sottoTitolo="pagina per modificare un atto")
     * @Menuitem(menuAttivo = "elencoAttoRevoca")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco atti revoca", route="elenco_atti_revoca"), 
     *                       @ElementoBreadcrumb(testo="Modifica Atto")})
     */
    public function modificaAttoAction(Request $request, $id_atto) {
        $em = $this->getDoctrine()->getManager();
        $atto = $em->getRepository('AttuazioneControlloBundle:Revoche\AttoRevoca')->find($id_atto);

        $vecchioDocumento = $atto->getDocumento();
        $documentoConvenzione = new DocumentoFile();
        $atto->setDocumento($documentoConvenzione);

        $options["readonly"] = (!$this->isGranted("ROLE_REVOCHE") && !$this->isGranted("ROLE_SUPER_ADMIN"));
        $options["url_indietro"] = $this->generateUrl("elenco_atti_revoca");

        $options["documento_opzionale"] = true;
        $options["TIPOLOGIA_DOCUMENTO"] = $em->getRepository('DocumentoBundle\Entity\TipologiaDocumento')->findOneByCodice('ATTO_REVOCA');

        $form = $this->createForm('AttuazioneControlloBundle\Form\Revoche\AttoRevocaType', $atto, $options);

        if ($request->isMethod('POST')) {

            $form->handleRequest($request);

            if ($atto->getTipo()->getCodice() == 'PAR' && $atto->getTipoMotivazione()->getCodice() == '1') {
                return $this->addErrorRedirect("Se la revoca è parziale non è possbile selezionare la rinuncia del beneficiario come motivazione ", "modifica_atto_revoca", array('id_atto' => $id_atto));
            }
            if ($atto->getTipo()->getCodice() == 'RIN' && $atto->getTipoMotivazione()->getCodice() != '1') {
                return $this->addErrorRedirect("Se la revoca è per rinuncia è possbile selezionare solo la rinuncia del beneficiario come motivazione ", "modifica_atto_revoca", array('id_atto' => $id_atto));
            }

            if ($form->isValid()) {
                try {
                    // Se non ho inserito un nuovo documento ri-associo quello vecchio.
                    if (!is_null($atto->getDocumento()->getFile())) {
                        $documento = $atto->getDocumento();
                        $this->get("documenti")->carica($documento);
                        // cancello il vecchio file
                        $vecchioDocumento->setDataCancellazione(new \DateTime());
                    } else {
                        $atto->setDocumento($vecchioDocumento);
                    }
                    $em->beginTransaction();

                    $em->persist($atto);
                    $em->flush();
                    $em->commit();
                    $this->addFlash('success', "Modifiche salvate correttamente");

                    return $this->redirect($this->generateUrl('elenco_atti_revoca'));
                } catch (\Exception $e) {
                    $em->rollback();
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["atto"] = $atto;
        $form_params["lettura"] = false;
        $form_params["vecchioDocumento"] = $vecchioDocumento;

        return $form_params;
    }

    /**
     * @Route("/elenco_revoche/{id_richiesta}",  name="elenco_revoche")
     * @Menuitem(menuAttivo = "elencoRichiesteAttuazione")
     * @PaginaInfo(titolo="Elenco revoche operazione", sottoTitolo="pagina per gestione delle revoche")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Attuazione progetti")})
     */
    public function elencoRevocheAction($id_richiesta) {

        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
        $revoche = $richiesta->getAttuazioneControllo()->getRevoca();

        return $this->render('AttuazioneControlloBundle:Revoche:elencoRevoche.html.twig', array('revoche' => $revoche, 'richiesta' => $richiesta, 'menu' => 'revoche'));
    }

    /**
     * @Route("/{id_richiesta}/crea_revoca", name="crea_revoca")
     * @Template("AttuazioneControlloBundle:Revoche:revoca.html.twig")
     * @PaginaInfo(titolo="Nuovo Atto revoca",sottoTitolo="pagina per creare una revoca")
     * @Menuitem(menuAttivo = "elencoRichiesteAttuazione")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Attuazione progetti")})
     */
    public function creaRevocaAction($id_richiesta) {

        $em = $this->getDoctrine()->getManager();
        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
        $chiusura = $this->getEm()->getRepository("CertificazioniBundle\Entity\CertificazioneChiusura")->getChiusureLavorabili();

        $procedura = $richiesta->getProcedura();
        $asse = $procedura->getAsse();

        $revoca = new Revoca();
        $request = $this->getCurrentRequest();
        $options["readonly"] = (!$this->isGranted("ROLE_REVOCHE") && !$this->isGranted("ROLE_SUPER_ADMIN"));
        $options["url_indietro"] = $this->generateUrl("elenco_revoche", array("id_richiesta" => $richiesta->getId()));
        $options["mostra_indietro"] = false;
        $conPenalita = false;
        $procedureConPenalita = array(6, 7, 33, 65);
        if (in_array($procedura->getId(), $procedureConPenalita)) {
            $form = $this->createForm('AttuazioneControlloBundle\Form\Revoche\RevocaAsse1Type', $revoca, $options);
            $conPenalita = true;
        } else {
            $form = $this->createForm('AttuazioneControlloBundle\Form\Revoche\RevocaType', $revoca, $options);
        }
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($revoca->getTaglioAda() == false) {
                if ($revoca->getInvioConti() == false) {
                    if ($this->hasPagamentiMandato($richiesta) == true && ($revoca->getConRecupero() == false && $revoca->getConRitiro() == false)) {
                        $form->addError(new FormError("Esite un pagamento con mandato, selezionare se recupero o ritiro "));
                    }
                    if ($this->hasPagamentiMandato($richiesta) == false && ($revoca->getConRecupero() == true || $revoca->getConRitiro() == true)) {
                        $form->addError(new FormError("Non esitono pagamenti con mandato e non è possibile, deselezionare i flag revoca e/o recupero"));
                    }
                    if ($revoca->getConRecupero() == true && $revoca->getConRitiro() == true) {
                        $form->addError(new FormError("Non è possibile selezionare contemporaneamente ritiro e recupero "));
                    }
                    if ($revoca->getArticolo137() == true) {
                        $form->addError(new FormError("In caso di articolo 137 co.2 deve essere anche un invio conti"));
                    }
                } else {
                    if (is_null($chiusura)) {
                        $form->addError(new FormError("Per l'invio nei conti è necessario che sia presente una chiusura lavorabile"));
                    }
                    /* if ($revoca->getConRecupero() == true || $revoca->getConRitiro() == true) {
                      $form->addError(new \Symfony\Component\Form\FormError("In caso di invio conti attivo non è possibile flaggare le opzioni recupero e ritiro"));
                      } */
                }
            } else {
                if ($revoca->getInvioConti() == false) {
                    $form->addError(new FormError("In caso di taglio ada attivo è necessario selezionare l'opzioni invio nei conti"));
                }
                if (is_null($revoca->getContributoAda())) {
                    $form->addError(new FormError("In caso di taglio ada attivo è necessario inseirire la quota di contributo del taglio"));
                }
                /* if ($revoca->getConRecupero() == true || $revoca->getConRitiro() == true) {
                  $form->addError(new \Symfony\Component\Form\FormError("In caso di invio conti attivo non è possibile selezionare le opzioni recupero e ritiro"));
                  } */
            }
            if ((($revoca->getTipoIrregolarita()->count() > 0) && $revoca->hasTipoIrregolaritaAltro() == true) && (is_null($revoca->getSpecificare()) || $revoca->getSpecificare() == '')) {
                $form->addError(new FormError('Se il tipo irregolarità è altro bisogna compilare il campo "Altro specificare"'));
            }


            if ($form->isValid()) {
                try {
                    $revoca->setAttuazioneControlloRichiesta($richiesta->getAttuazioneControllo());
                    if ($revoca->getInvioConti() == true) {
                        $revoca->setChiusura($chiusura);
                    }

                    /** @var \MonitoraggioBundle\Service\IGestoreImpegni $impegniService */
                    $impegniService = $this->container->get('monitoraggio.impegni')->getGestore($richiesta);
                    $impegniService->aggiornaRevoca($revoca);

                    $em->persist($revoca);
                    $em->flush();
                    $this->addFlash('success', "Revoca salvata correttamente");

                    return $this->redirect($this->generateUrl('elenco_revoche', array("id_richiesta" => $richiesta->getId())));
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["richiesta"] = $richiesta;
        $form_params["mode"] = "new";
        $form_params["asse"] = $asse->getCodice();
        $form_params['menu'] = 'revoche';
        $form_params['penalita'] = $conPenalita;

        return $form_params;
    }

    protected function aggiornaRecuperoMonitoraggio(RataRecupero $rata): void {
        $recupero = $rata->getRecupero();
        $revoca = $recupero->getRevoca();
        $richiesta = $revoca->getRichiesta();
        $contributo = $rata->getImportoRata() ?: 0.0;
        $dataRata = $rata->getDataIncasso();
        if (\is_null($dataRata)) {
            return;
        }
        $causale = $this->getEm()->getRepository(TC39CausalePagamento::class)->findOneBy([
            'causale_pagamento' => 'SNA',
        ]);

        $pagamento = $rata->getPagamentoMonitoraggio();
        if (\is_null($pagamento)) {
            $pagamento = new RichiestaPagamento();
            $pagamento->setRataRecupero($rata);
            $pagamento->setCausalePagamento($causale);
            $pagamento->setTipologiaPagamento(RichiestaPagamento::RETTIFICA);
            $pagamento->setCodice($pagamento->generaCodice());
        }
        $pagamento->setImporto($contributo);
        $pagamento->setDataPagamento($dataRata);

        if ($pagamento->getPagamentiAmmessi()->isEmpty()) {

            $ammesso = new PagamentoAmmesso($pagamento);
            $pagamento->addPagamentiAmmessi($ammesso);

            $richiesta->addMonRichiestePagamento($pagamento);
        } else {
            /** @var PagamentoAmmesso $ammesso */
            $ammesso = $pagamento->getPagamentiAmmessi()->first();
            $ammesso->aggiornaDaPagamento();
        }
    }

    /**
     * @Route("/{id_revoca}/modifica_revoca", name="modifica_revoca")
     * @Template("AttuazioneControlloBundle:Revoche:revoca.html.twig")
     * @PaginaInfo(titolo="Nuovo Atto revoca",sottoTitolo="pagina per creare una revoca")
     * @Menuitem(menuAttivo = "elencoRichiesteAttuazione")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Attuazione progetti")})
     */
    public function modificaRevocaAction($id_revoca) {

        $em = $this->getDoctrine()->getManager();
        $revoca = $em->getRepository("AttuazioneControlloBundle\Entity\Revoche\Revoca")->find($id_revoca);
        $richiesta = $revoca->getAttuazioneControlloRichiesta()->getRichiesta();

        if (!is_null($revoca->getChiusura())) {
            $chiusura = $revoca->getChiusura();
        } else {
            $chiusura = $em->getRepository("CertificazioniBundle\Entity\CertificazioneChiusura")->getChiusureLavorabili();
        }

        $procedura = $richiesta->getProcedura();
        $asse = $procedura->getAsse();

        $request = $this->getCurrentRequest();
        $options["readonly"] = (!$this->isGranted("ROLE_REVOCHE") && !$this->isGranted("ROLE_SUPER_ADMIN"));
        $options["url_indietro"] = $this->generateUrl("elenco_revoche", array("id_richiesta" => $richiesta->getId()));
        $options["mostra_indietro"] = true;

        $conPenalita = false;
        $procedureConPenalita = array(6, 7, 33, 65);
        if (in_array($procedura->getId(), $procedureConPenalita)) {
            $form = $this->createForm('AttuazioneControlloBundle\Form\Revoche\RevocaAsse1Type', $revoca, $options);
            $conPenalita = true;
        } else {
            $form = $this->createForm('AttuazioneControlloBundle\Form\Revoche\RevocaType', $revoca, $options);
        }
        if (!is_null($revoca->getAttoRevoca())) {
            $atto = $this->datiAttoById($revoca->getAttoRevoca()->getId());
            $form->get('data_atto')->setData($atto['data_atto']);
            $form->get('tipo_revoca')->setData($atto['tipo_atto']);
            $form->get('tipo_motivazione')->setData($atto['motivazione']);
        }
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($revoca->getTaglioAda() == false) {
                if ($revoca->getInvioConti() == false) {
                    if ($this->hasPagamentiMandato($richiesta) == true && ($revoca->getConRecupero() == false && $revoca->getConRitiro() == false)) {
                        $form->addError(new FormError("Esite un pagamento con mandato, selezionare se recupero o ritiro "));
                    }
                    if ($this->hasPagamentiMandato($richiesta) == false && ($revoca->getConRecupero() == true || $revoca->getConRitiro() == true)) {
                        $form->addError(new FormError("Non esitono pagamenti con mandato e non è possibile, deselezionare i flag revoca e/o recuepro"));
                    }
                    if ($revoca->getConRecupero() == true && $revoca->getConRitiro() == true) {
                        $form->addError(new FormError("Non è possibile selezionare contemporaneamente ritiro e recupero "));
                    }
                    if ($revoca->getConRecupero() == true && (is_null($revoca->getContributo()) || $revoca->getContributo() == 0 )) {
                        $form->addError(new FormError("Non è possibile avere un contributo da recuperare pari a 0 se è previsto un recupero "));
                    }
                    if ($revoca->getConRecupero() == false && $revoca->getContributo() != 0) {
                        $form->addError(new FormError("Non è possibile avere un contributo da recuperare diverso da 0 se non è previsto un recupero "));
                    }
                } else {
                    if (is_null($chiusura)) {
                        $form->addError(new FormError("Per l'invio nei conti è necessario che sia presente una chiusura lavorabile"));
                    }
                    /* if ($revoca->getConRecupero() == true || $revoca->getConRitiro() == true) {
                      $form->addError(new \Symfony\Component\Form\FormError("In caso di invio conti attivo non è possibile flaggare le opzioni recupero e ritiro"));
                      } */
                }
            } else {
                if ($revoca->getInvioConti() == false) {
                    $form->addError(new FormError("In caso di taglio ada attivo è necessario selezionare l'opzioni invio nei conti"));
                }
                if (is_null($revoca->getContributoAda())) {
                    $form->addError(new FormError("In caso di taglio ada attivo è necessario inseirire la quota di contributo del taglio"));
                }
                /* if ($revoca->getConRecupero() == true || $revoca->getConRitiro() == true) {
                  $form->addError(new \Symfony\Component\Form\FormError("In caso di invio conti attivo non è possibile selezionare le opzioni recupero e ritiro"));
                  } */
            }

            if ($form->isValid()) {
                try {
                    $revoca->setAttuazioneControlloRichiesta($richiesta->getAttuazioneControllo());
                    if ($revoca->getInvioConti() == true) {
                        $revoca->setChiusura($chiusura);
                    }

                    /** @var \MonitoraggioBundle\Service\IGestoreImpegni $impegniService */
                    $impegniService = $this->container->get('monitoraggio.impegni')->getGestore($richiesta);
                    $impegniService->aggiornaRevoca($revoca);

                    $em->persist($revoca);
                    $em->flush();
                    $this->addFlash('success', "Revoca salvata correttamente");

                    return $this->redirect($this->generateUrl('elenco_revoche', array("id_richiesta" => $richiesta->getId())));
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["richiesta"] = $richiesta;
        $form_params["mode"] = "edit";
        $form_params["asse"] = $asse->getCodice();
        $form_params['menu'] = 'revoche';
        $form_params['penalita'] = $conPenalita;

        return $form_params;
    }

    /**
     * @Route("/{id_revoca}/cancella_revoca", name="cancella_revoca")
     */
    public function cancellaRevocaAction($id_revoca) {

        $em = $this->getDoctrine()->getManager();
        $revoca = $em->getRepository("\AttuazioneControlloBundle\Entity\Revoche\Revoca")->find($id_revoca);
        $richiesta = $revoca->getAttuazioneControlloRichiesta()->getRichiesta();
        try {
            $em->beginTransaction();
            foreach ($revoca->getRecuperi() as $recupero) {
                $em->remove($recupero);
            }

            /** @var \MonitoraggioBundle\Service\IGestoreImpegni $impegniService */
            $impegniService = $this->container->get('monitoraggio.impegni')->getGestore($richiesta);
            $impegniService->rimuoviImpegniRevoca($revoca);

            $em->remove($revoca);
            $em->flush();
            $em->commit();
            return $this->redirect($this->generateUrl('elenco_revoche', array("id_richiesta" => $richiesta->getId())));
        } catch (\Exception $e) {
            $em->rollback();
            $this->addFlash('error', $e->getMessage());
        }
    }

    /**
     * @Route("/elenco_recuperi/{id_richiesta}",  name="elenco_recuperi")
     * @Menuitem(menuAttivo = "elencoRichiesteAttuazione")
     * @PaginaInfo(titolo="Elenco revoche operazione", sottoTitolo="pagina per gestione delle revoche")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Attuazione progetti")})
     */
    public function elencoRecuperiAction($id_richiesta) {

        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
        $revoche = $richiesta->getAttuazioneControllo()->getRevoca();

        return $this->render('AttuazioneControlloBundle:Revoche:elencoRecuperi.html.twig', array('revoche' => $revoche, 'richiesta' => $richiesta, 'menu' => 'recuperi'));
    }

    /**
     * @Route("/{id_revoca}/crea_recupero", name="crea_recupero")
     * @Template("AttuazioneControlloBundle:Revoche:recupero.html.twig")
     * @PaginaInfo(titolo="Nuovo recupero",sottoTitolo="pagina per creare un recupero")
     * @Menuitem(menuAttivo = "elencoRichiesteAttuazione")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Attuazione progetti")})
     */
    public function creaRecuperoAction($id_revoca) {

        $em = $this->getDoctrine()->getManager();
        $revoca = $this->getEm()->getRepository(Revoca::class)->find($id_revoca);
        $richiesta = $revoca->getAttuazioneControlloRichiesta()->getRichiesta();
        $procedura = $richiesta->getProcedura();
        $asse = $procedura->getAsse();

        $recupero = new Recupero();

        $request = $this->getCurrentRequest();
        $options["readonly"] = (!$this->isGranted("ROLE_REVOCHE") && !$this->isGranted("ROLE_SUPER_ADMIN"));
        $options["url_indietro"] = $this->generateUrl("elenco_recuperi", array("id_richiesta" => $richiesta->getId()));
        $options["mostra_indietro"] = true;
        $options['penalita'] = $revoca->hasPenalita();

        $form = $this->createForm('AttuazioneControlloBundle\Form\Revoche\RecuperoType', $recupero, $options);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $this->validaRecupero($form, $recupero);
            if ($form->isValid()) {
                try {
                    $recupero->setRevoca($revoca);
                    $em->persist($recupero);
                    $em->flush();
                    $this->addFlash('success', "Recupero salvata correttamente");

                    return $this->redirect($this->generateUrl("elenco_recuperi", array("id_richiesta" => $richiesta->getId())));
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["richiesta"] = $richiesta;
        $form_params["asse"] = $asse->getCodice();
        $form_params['menu'] = 'recuperi';
        $form_params['penalita'] = $options['penalita'];
        $form_params["readonly"] = $options["readonly"];

        return $form_params;
    }

    /**
     * @Route("/{id_recupero}/modifica_recupero", name="modifica_recupero")
     * @Template("AttuazioneControlloBundle:Revoche:recupero.html.twig")
     * @PaginaInfo(titolo="Modifica recupero",sottoTitolo="pagina per modificare un recupero")
     * @Menuitem(menuAttivo = "elencoRichiesteAttuazione")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Attuazione progetti")})
     */
    public function modificaRecuperoAction($id_recupero) {

        $em = $this->getDoctrine()->getManager();
        $recupero = $em->getRepository("\AttuazioneControlloBundle\Entity\Revoche\Recupero")->find($id_recupero);
        $richiesta = $recupero->getRevoca()->getAttuazioneControlloRichiesta()->getRichiesta();
        $procedura = $richiesta->getProcedura();
        $asse = $procedura->getAsse();

        $request = $this->getCurrentRequest();
        $options["readonly"] = (!$this->isGranted("ROLE_REVOCHE") && !$this->isGranted("ROLE_SUPER_ADMIN")) || $recupero->isRecuperoChiuso();
        $options["url_indietro"] = $this->generateUrl("elenco_recuperi", array("id_richiesta" => $richiesta->getId()));
        $options["mostra_indietro"] = true;
        $options['penalita'] = $recupero->getRevoca()->hasPenalita();

        $form = $this->createForm('AttuazioneControlloBundle\Form\Revoche\RecuperoType', $recupero, $options);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $this->validaRecupero($form, $recupero);
            if ($form->isValid()) {
                try {
                    $em->persist($recupero);
                    $em->flush();
                    $this->addFlash('success', "Recupero salvato correttamente");

                    return $this->redirect($this->generateUrl("elenco_recuperi", array("id_richiesta" => $richiesta->getId())));
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["richiesta"] = $richiesta;
        $form_params["recupero"] = $recupero;
        $form_params["asse"] = $asse->getCodice();
        $form_params['menu'] = 'recuperi';
        $form_params["readonly"] = $options["readonly"];
        $form_params['penalita'] = $options['penalita'];

        return $form_params;
    }

    /**
     * @Route("/{id_recupero}/cancella_recupero", name="cancella_recupero")
     */
    public function cancellaRecuperoAction($id_recupero) {

        $em = $this->getDoctrine()->getManager();
        $recupero = $em->getRepository("\AttuazioneControlloBundle\Entity\Revoche\Recupero")->find($id_recupero);
        $richiesta = $recupero->getRevoca()->getAttuazioneControlloRichiesta()->getRichiesta();
        try {
            $em->remove($recupero);
            $em->flush();
            return $this->redirect($this->generateUrl("elenco_recuperi", array("id_richiesta" => $richiesta->getId())));
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }
    }

    /**
     * @Route("/{atto_id}/dati_atto_revoca", name="dati_atto_revoca_ajax")
     */
    public function attoByIdAjaxAction($atto_id) {
        $em = $this->get('doctrine.orm.entity_manager');
        $r = $em->getRepository('AttuazioneControlloBundle\Entity\Revoche\AttoRevoca');
        $atto = $r->find($atto_id);
        $dati = array();
        $dati['data_atto'] = is_null($atto->getData()) ? '' : $atto->getData()->format('d-m-Y');
        $dati['tipo_atto'] = is_null($atto->getTipo()) ? '' : $atto->getTipo()->getDescrizione();
        $dati['motivazione'] = is_null($atto->getTipoMotivazione()) ? '' : $atto->getTipoMotivazione()->getDescrizione();

        $json = json_encode($dati);

        return new JsonResponse($dati);
    }

    public function datiAttoById($atto_id) {
        $em = $this->get('doctrine.orm.entity_manager');
        $r = $em->getRepository('AttuazioneControlloBundle\Entity\Revoche\AttoRevoca');
        $atto = $r->find($atto_id);
        $dati = array();
        $dati['data_atto'] = is_null($atto->getData()) ? '' : $atto->getData();
        $dati['tipo_atto'] = is_null($atto->getTipo()) ? '' : $atto->getTipo()->getDescrizione();
        $dati['motivazione'] = is_null($atto->getTipoMotivazione()) ? '' : $atto->getTipoMotivazione()->getDescrizione();

        return $dati;
    }

    public function validaRecupero($form, $recupero) {
        $msgCorso = "Questo campo non può essere vuoto in caso di recupero in corso";
        $msgCompleto = "Questo campo non può essere vuoto in caso di recupero completo";
        $msgMancato = "Questo campo non può essere vuoto in caso di mancato recupero";

        if ($recupero->getTipoFaseRecupero()->getCodice() == 'MANCATO') {
            if (is_null($recupero->getContributoNonRecuperato())) {
                $form->get('contributo_non_recuperato')->addError(new FormError($msgMancato));
            }
            if (is_null($recupero->getAzioniMancatoRecupero())) {
                $form->get('azioni_mancato_recupero')->addError(new FormError($msgCorso));
            }
        }
    }

    public function hasPagamentiMandato(Richiesta $richiesta) {
        $atc = $richiesta->getAttuazioneControllo();
        if (\is_null($atc)) {
            return false;
        }
        /** @var Pagamento $pagamento */
        foreach ($atc->getPagamenti() as $pagamento) {
            if ($pagamento->hasMandatoPagamento()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @Route("/{id_atto}/elimina_documento_atto_revoca", name="elimina_documento_atto_revoca")
     */
    public function eliminaDocumentoAttoRevoca($id_atto) {
        $em = $this->getEm();
        $atto = $em->getRepository('AttuazioneControlloBundle:Revoche\AttoRevoca')->find($id_atto);
        $documento = $atto->getDocumento();
        $this->get('base')->checkCsrf('token');
        try {
            $em->remove($documento);
            $atto->setDocumento(null);
            $em->flush();
            return $this->addSuccessRedirect("Documento eliminato correttamente", "modifica_atto_revoca", array("id_atto" => $id_atto));
        } catch (\Exception $e) {
            return $this->addErrorRedirect("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.", "modifica_atto_revoca", array("id_atto" => $id_atto));
        }
    }

    /**
     * @Route("/elenco_revoche_recuperi_pulisci", name="elenco_revoche_recuperi_pulisci")
     */
    public function elencoAttiLiquidazionePulisciAction() {
        $this->get("ricerca")->pulisci(new RicercaAttoRevoca());
        return $this->redirectToRoute("elenco_atti_revoca");
    }

    /**
     * @Route("/{id_recupero}/crea_rata_recupero", name="crea_rata_recupero")
     * @Template("AttuazioneControlloBundle:Revoche:rataRecupero.html.twig")
     * @PaginaInfo(titolo="Nuovo rata",sottoTitolo="pagina per creare una nuova rata")
     * @Menuitem(menuAttivo = "elencoRichiesteAttuazione")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Attuazione progetti")})
     */
    public function creaRataRecuperoAction($id_recupero) {

        $em = $this->getDoctrine()->getManager();
        /** @var Recupero $recupero */
        $recupero = $this->getEm()->getRepository("\AttuazioneControlloBundle\Entity\Revoche\Recupero")->find($id_recupero);

        if ($recupero->isRecuperoChiuso()) {
            return $this->addErrorRedirect("Il recupero è chiuso e non più possibile aggiungere rate ad esso associate", "modifica_recupero", array("id_recupero" => $id_recupero));
        }

        $richiesta = $recupero->getRevoca()->getAttuazioneControlloRichiesta()->getRichiesta();
        $procedura = $richiesta->getProcedura();
        $asse = $procedura->getAsse();

        $rata = new RataRecupero($recupero);

        $request = $this->getCurrentRequest();
        $options["readonly"] = (!$this->isGranted("ROLE_REVOCHE") && !$this->isGranted("ROLE_SUPER_ADMIN"));
        $options["url_indietro"] = $this->generateUrl("modifica_recupero", array("id_recupero" => $id_recupero));
        $options["mostra_indietro"] = true;
        $options['penalita'] = $recupero->getRevoca()->hasPenalita();

        $form = $this->createForm('AttuazioneControlloBundle\Form\Revoche\RataRecuperoType', $rata, $options);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                try {
                    $recupero->addRate($rata);
                    $em->getConnection()->beginTransaction();
                    $this->aggiornaDatiRecupero($recupero);
                    $this->aggiornaRecuperoMonitoraggio($rata);
                    $em->persist($rata);
                    $em->flush();
                    $em->getConnection()->commit();
                    $this->addFlash('success', "Recupero salvata correttamente");
                    return $this->redirect($this->generateUrl("modifica_recupero", array("id_recupero" => $id_recupero)));
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                    throw $e;
                }
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["richiesta"] = $richiesta;
        $form_params["recupero"] = $recupero;
        $form_params["asse"] = $asse->getCodice();
        $form_params['menu'] = 'recuperi';
        $form_params['penalita'] = $options['penalita'];

        return $form_params;
    }

    /**
     * @Route("/{id_recupero}/modifica_rata_recupero/{id_rata}", name="modifica_rata_recupero")
     * @Template("AttuazioneControlloBundle:Revoche:rataRecupero.html.twig")
     * @PaginaInfo(titolo="Nuovo rata",sottoTitolo="pagina per creare una nuova rata")
     * @Menuitem(menuAttivo = "elencoRichiesteAttuazione")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Attuazione progetti")})
     */
    public function modificaRataRecuperoAction($id_recupero, $id_rata) {

        $em = $this->getDoctrine()->getManager();
        $recupero = $this->getEm()->getRepository("\AttuazioneControlloBundle\Entity\Revoche\Recupero")->find($id_recupero);

        if ($recupero->isRecuperoChiuso()) {
            return $this->addErrorRedirect("Il recupero è chiuso e non più possibile modificare rate ad esso associate", "modifica_recupero", array("id_recupero" => $id_recupero));
        }

        $richiesta = $recupero->getRevoca()->getAttuazioneControlloRichiesta()->getRichiesta();
        $procedura = $richiesta->getProcedura();
        $asse = $procedura->getAsse();

        $rata = $this->getEm()->getRepository("\AttuazioneControlloBundle\Entity\Revoche\RataRecupero")->find($id_rata);

        $request = $this->getCurrentRequest();
        $options["readonly"] = (!$this->isGranted("ROLE_REVOCHE") && !$this->isGranted("ROLE_SUPER_ADMIN")) || $recupero->isRecuperoChiuso();
        $options["url_indietro"] = $this->generateUrl("modifica_recupero", array("id_recupero" => $id_recupero));
        $options["mostra_indietro"] = true;
        $options['penalita'] = $recupero->getRevoca()->hasPenalita();

        $form = $this->createForm('AttuazioneControlloBundle\Form\Revoche\RataRecuperoType', $rata, $options);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $em->getConnection()->beginTransaction();
                    $this->aggiornaRecuperoMonitoraggio($rata);
                    $em->persist($rata);

                    $this->aggiornaDatiRecupero($recupero);

                    $em->flush();
                    $em->getConnection()->commit();
                    $this->addFlash('success', "Recupero salvata correttamente");
                    return $this->redirect($this->generateUrl("modifica_recupero", array("id_recupero" => $id_recupero)));
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["richiesta"] = $richiesta;
        $form_params["recupero"] = $recupero;
        $form_params["asse"] = $asse->getCodice();
        $form_params['menu'] = 'recuperi';
        $form_params['penalita'] = $options['penalita'];

        return $form_params;
    }

    /**
     * @Route("/{id_rata}/cancella_rata_recupero", name="cancella_rata_recupero")
     */
    public function cancellaRataRecuperoAction($id_rata) {

        $em = $this->getDoctrine()->getManager();
        /** @var RataRecupero $rata */
        $rata = $em->getRepository(RataRecupero::class)->find($id_rata);
        $recupero = $rata->getRecupero();

        if ($recupero->isRecuperoChiuso()) {
            return $this->addErrorRedirect("Il recupero è chiuso e non più possibile cancellare rate ad esso associate", "modifica_recupero", array("id_recupero" => $recupero->getId()));
        }

        try {
            $rettifica = $rata->getPagamentoMonitoraggio();
            $em->remove($rettifica);
            $rata->setPagamentoMonitoraggio(NULL);
            $recupero->removeRate($rata);
            $em->remove($rata);

            $this->aggiornaDatiRecupero($recupero);

            $em->flush();
            return $this->redirect($this->generateUrl("modifica_recupero", array("id_recupero" => $rata->getRecupero()->getId())));
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }
    }

    private function aggiornaDatiRecupero($recupero) {
        $contributo_restituito = 0.0;
        $interessi_legali = 0.0;
        $interessi_mora = 0.0;
        foreach ($recupero->getRate() as $rata) {
            $contributo_restituito += $rata->getImportoRata();
            $interessi_legali += $rata->getImportoInteresseLegale();
            $interessi_mora += $rata->getImportoInteresseMora();
        }
        $recupero->setContributoRestituito($contributo_restituito);
        $recupero->setImportoInteresseLegale($interessi_legali);
        $recupero->setImportoInteresseMora($interessi_mora);
        $em = $this->getDoctrine()->getManager();
    }

}
