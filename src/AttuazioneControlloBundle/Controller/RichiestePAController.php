<?php

namespace AttuazioneControlloBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use BaseBundle\Annotation\ControlloAccesso;
use AttuazioneControlloBundle\Form\Entity\RicercaAttuazione;
use BaseBundle\Exception\SfingeException;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/pa/richieste_atc")
 */
class RichiestePAController extends \BaseBundle\Controller\BaseController {

    /**
     * @Route("/elenco/{sort}/{direction}/{page}", defaults={"sort" = "a.id", "direction" = "asc", "page" = "1"}, name="elenco_gestione_pa")
     * @Template()
     * @PaginaInfo(titolo="Elenco operazioni in attuazione",sottoTitolo="mostra l'elenco delle operazioni in attuazione e controllo")
     * @Menuitem(menuAttivo = "elencoRichiesteAttuazione")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Attuazione operazioni")})
     */
    public function elencoRichiesteAction() {
        $datiRicerca = new RicercaAttuazione();
        $datiRicerca->setUtente($this->getUser());

        $risultato = $this->get("ricerca")->ricerca($datiRicerca);

        return $this->render('AttuazioneControlloBundle:PA/Richieste:elencoRichieste.html.twig', array('richieste' => $risultato["risultato"], "formRicercaIstruttoria" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"]));
    }

    /**
     * @Route("/elenco_attuazione_pulisci", name="elenco_attuazione_pulisci")
     */
    public function elencoAttuazionePulisciAction() {
        $this->get("ricerca")->pulisci(new RicercaAttuazione());
        return $this->redirectToRoute("elenco_gestione_pa");
    }

    /**
     * @Route("/{id_richiesta}/riepilogo", name="riepilogo_richiesta_attuazione")
     * @PaginaInfo(titolo="Riepilogo del progetto",sottoTitolo="dati riepilogativi del progetto")
     * @Menuitem(menuAttivo = "elencoRichiesteAttuazione")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Attuazione progetti", route="elenco_gestione_pa")})
     * ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function riepilogoRichiestaAction($id_richiesta) {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
        return $this->get("gestore_richieste_atc")->getGestore($richiesta->getProcedura())->riepilogoRichiestaPA($richiesta);
    }

    /**
     * @Route("/{id_richiesta}/dati_richiesta", name="dati_richiesta_attuazione")
     * @PaginaInfo(titolo="Dati del progetto",sottoTitolo="dati del progetto")
     * @Menuitem(menuAttivo = "elencoRichiesteAttuazione")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Attuazione progetti", route="elenco_gestione_pa")})
     * ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function datiRichiestaAction($id_richiesta) {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
        return $this->get("gestore_richieste_atc")->getGestore($richiesta->getProcedura())->datiRichiestaPA($richiesta);
    }

    /**
     * @Route("/{id_richiesta}/documenti_richiesta", name="documenti_richiesta_attuazione")
     * @PaginaInfo(titolo="Documenti progetto",sottoTitolo="documenti caricati nel progetto")
     * @Menuitem(menuAttivo = "elencoRichiesteAttuazione")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Attuazione progetti", route="elenco_gestione_pa"),
     * 				@ElementoBreadcrumb(testo="Documenti progetto")
     * 				})
     * ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function documentiRichiestaAction($id_richiesta) {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
        return $this->get("gestore_richieste_atc")->getGestore($richiesta->getProcedura())->documentiRichiestaPA($richiesta);
    }

    /**
     * @Route("/{id_richiesta}/documenti_richiesta_istruttoria_pa", name="documenti_richiesta_istruttoria_pa")
     * @PaginaInfo(titolo="Documenti progetto",sottoTitolo="documenti caricati nel progetto")
     * @Menuitem(menuAttivo = "elencoRichiesteAttuazione")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Attuazione progetti", route="elenco_gestione_pa"),
     * 				@ElementoBreadcrumb(testo="Documenti istruttoria")
     * 				})
     * ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function documentiRichiestaIstruttoriaAction($id_richiesta) {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
        return $this->get("gestore_richieste_atc")->getGestore($richiesta->getProcedura())->documentiRichiestaIstruttoriaPA($richiesta);
    }

    /**
     * @Route("/{id_richiesta}/elimina_documento_attuazione/{id_documento}", name="elimina_documento_attuazione")
     * ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function eliminaDocumentoAttuazioneAction($id_richiesta, $id_documento) {
        if ($this->getUser()->isConsulenteFesr() == true) {
            return $this->addErrorRedirect('Non sei autorizzato ad eseguire l\'operazione', "riepilogo_richiesta_attuazione", array("id_richiesta" => $id_richiesta));
        }
        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
        return $this->get("gestore_richieste_atc")->getGestore($richiesta->getProcedura())->eliminaDocumentoAttuazione($richiesta, $id_documento);
    }

    /**
     * @Route("/{id_richiesta}/riepilogo_beneficiari", name="riepilogo_beneficiari")
     * @PaginaInfo(titolo="Riepilogo dei beneficiari",sottoTitolo="dati riepilogativi dei beneficiari di una richiesta")
     * @Menuitem(menuAttivo = "elencoRichiesteAttuazione")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Attuazione progetti", route="elenco_gestione_pa"),
     * 				@ElementoBreadcrumb(testo="Riepilogo beneficiari")
     * 				})
     * ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function riepilogoBeneficiariAction($id_richiesta) {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
        return $this->get("gestore_richieste_atc")->getGestore($richiesta->getProcedura())->riepilogoBeneficiari($richiesta);
    }

    /**
     * @Route("/{id_richiesta}/elenco_pagamenti", name="elenco_pagamenti_attuazione")
     * @PaginaInfo(titolo="Elenco pagamenti",sottoTitolo="pagina con l'elenco dei pagamenti associati al progetto")
     * @Menuitem(menuAttivo = "elencoRichiesteAttuazione")
     * @Breadcrumb(elementi={
     *              @ElementoBreadcrumb(testo="Attuazione progetti", route="elenco_gestione_pa"),
     *              @ElementoBreadcrumb(testo="Elenco pagamenti")})
     * ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function elencoPagamentiAction($id_richiesta) {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
        return $this->get("gestore_richieste_atc")->getGestore($richiesta->getProcedura())->elencoPagamenti($id_richiesta);
    }

    /**
     * @Route("/{id_pagamento}/dettaglio_pagamento", name="dettaglio_pagamento_attuazione")
     * @PaginaInfo(titolo="Dettaglio pagamento",sottoTitolo="mostra le informazioni di dettaglio di un pagamento")
     * @Menuitem(menuAttivo = "elencoRichiesteAttuazione")
     * ControlloAccesso(contesto="procedura", classe="AttuazioneControlloBundle:Pagamento", opzioni={"id" = "id_pagamento"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function dettaglioPagamentoAction($id_pagamento) {
        $pagamento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->find($id_pagamento);
        $richiesta = $pagamento->getAttuazioneControlloRichiesta()->getRichiesta();
        return $this->get("gestore_richieste_atc")->getGestore($richiesta->getProcedura())->dettaglioPagamento($pagamento);
    }

    /**
     * @Route("/{id_giustificativo}/dettaglio_giustificativo", name="dettaglio_giustificativo_attuazione")
     * @PaginaInfo(titolo="Dettaglio giustificativo",sottoTitolo="mostra le informazioni di dettaglio di un giustificativo")
     * @Menuitem(menuAttivo = "elencoRichiesteAttuazione")
     * ControlloAccesso(contesto="procedura", classe="AttuazioneControlloBundle:GiustificativoPagamento", opzioni={"id" = "id_giustificativo"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function dettaglioGiustificativoAction($id_giustificativo) {
        $giustificativo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\GiustificativoPagamento")->find($id_giustificativo);
        $richiesta = $giustificativo->getPagamento()->getAttuazioneControlloRichiesta()->getRichiesta();
        return $this->get("gestore_richieste_atc")->getGestore($richiesta->getProcedura())->dettaglioGiustificativo($giustificativo);
    }

    /**
     * @Route("/{id_quietanza}/dettaglio_quietanza", name="dettaglio_quietanza_attuazione")
     * @PaginaInfo(titolo="Dettaglio quietanza",sottoTitolo="mostra le informazioni di dettaglio di una quietanza")
     * @Menuitem(menuAttivo = "elencoRichiesteAttuazione")
     * ControlloAccesso(contesto="procedura", classe="AttuazioneControlloBundle:QuietanzaGiustificativo", opzioni={"id" = "id_quietanza"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function dettaglioQuietanzaAction($id_quietanza) {
        $quietanza = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\QuietanzaGiustificativo")->find($id_quietanza);
        $richiesta = $quietanza->getGiustificativoPagamento()->getPagamento()->getAttuazioneControlloRichiesta()->getRichiesta();
        return $this->get("gestore_richieste_atc")->getGestore($richiesta->getProcedura())->dettaglioQuietanza($quietanza);
    }

    /**
     * @Route("/{id_richiesta}/avanzamento_piano_costi/{id_proponente}/{annualita}", name="avanzamento_piano_costi")
     * @PaginaInfo(titolo="Avanzamento piano costi",sottoTitolo="mostra l'avanzamento del piano costi della richiesta")
     * @Menuitem(menuAttivo = "elencoRichiesteAttuazione")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Attuazione richieste", route="elenco_gestione_pa"),
     * 				@ElementoBreadcrumb(testo="Avanzamento piano costi")
     * 				})
     * ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function avanzamentoPianoCostiAction($id_richiesta, $id_proponente, $annualita) {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
        $proponente = $this->getEm()->getRepository("RichiesteBundle\Entity\Proponente")->find($id_proponente);

        return $this->get("gestore_richieste_atc")->getGestore($richiesta->getProcedura())->avanzamentoPianoCosti($richiesta, $proponente, $annualita == "0" ? null : $annualita);
    }

    /**
     * @Route("/{id_richiesta}/riepilogo_proroghe", name="riepilogo_proroghe")
     * @PaginaInfo(titolo="Riepilogo proroghe",sottoTitolo="dati riepilogativi delle proroghe inviate dal beneficiario")
     * @Menuitem(menuAttivo = "elencoRichiesteInviate")
     * @Breadcrumb(elementi={
     * 		@ElementoBreadcrumb(testo="elenco operazioni inviate", route="elenco_richieste_inviate"),
     * 		@ElementoBreadcrumb(testo="riepilogo proroghe")
     * })
     * ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function riepilogoProrogheAction($id_richiesta) {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
        $stato = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\StatoProroga")->findByCodice('PROROGA_PROTOCOLLATA');
        $attuazione = $richiesta->getAttuazioneControllo();
        $proroghe = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Proroga")->findBy(array("attuazione_controllo_richiesta" => $attuazione, "stato" => $stato));

        return $this->render('AttuazioneControlloBundle:PA/Proroghe:elencoProroghe.html.twig', array("proroghe" => $proroghe, "richiesta" => $richiesta, "menu" => 'proroghe', 'attuazione_controllo' => $attuazione));
    }

    /**
     * @Route("/{id_proroga}/istruttoria_proroga", name="istruttoria_proroga")
     * @PaginaInfo(titolo="Istruttoria proroga")
     * @Menuitem(menuAttivo = "elencoRichiesteInviate")
     * @Breadcrumb(elementi={
     * 		@ElementoBreadcrumb(testo="elenco operazioni inviate", route="elenco_richieste_inviate"),
     * 		@ElementoBreadcrumb(testo="istruttoria proroga")
     * 	})
     * ControlloAccesso(contesto="procedura", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" = "id_variazione"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function istruttoriaProrogaAction($id_proroga) {
        $proroga = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Proroga")->find($id_proroga);
        return $this->get("gestore_proroghe")->getGestore($proroga->getRichiesta()->getProcedura())->istruttoriaProroga($proroga);
    }

    /**
     * @Route("/{id_richiesta}/riepilogo_proroghe_atc", name="riepilogo_proroghe_atc")
     * @PaginaInfo(titolo="Riepilogo dei beneficiari",sottoTitolo="dati riepilogativi delle proroghe inviate dal beneficiario")
     * @Menuitem(menuAttivo = "elencoRichiesteAttuazione")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Attuazione progetti", route="elenco_gestione_pa"),
     * 				@ElementoBreadcrumb(testo="Riepilogo proroghe")
     * 				})
     * ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function riepilogoProrogheAtcAction($id_richiesta) {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
        $attuazione = $richiesta->getAttuazioneControllo();

        $stato = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\StatoProroga")->findByCodice('PROROGA_PROTOCOLLATA');
        $proroghe = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Proroga")->findBy(array("attuazione_controllo_richiesta" => $attuazione, "stato" => $stato));

        //$proroghe = $this->getEm()->getRepository('AttuazioneControlloBundle:Proroga')->getUltimeProroghe($richiesta);

        return $this->render('AttuazioneControlloBundle:PA/Richieste:attuazioneElencoProroghe.html.twig', array("proroghe" => $proroghe, "richiesta" => $richiesta, "menu" => 'proroghe', 'attuazione_controllo' => $attuazione));
    }

    /**
     * @Route("/{id_proroga}/riepilogo_proroga_atc", name="riepilogo_proroga_atc")
     * @PaginaInfo(titolo="Istruttoria proroga")
     * @Menuitem(menuAttivo = "elencoRichiesteAttuazione")
     * ControlloAccesso(contesto="procedura", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" = "id_variazione"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function riepilogoProrogaAtcAction($id_proroga) {
        $proroga = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Proroga")->find($id_proroga);
        return $this->get("gestore_proroghe")->getGestore($proroga->getRichiesta()->getProcedura())->riepilogoAtcProroga($proroga);
    }

    /**
     * @Route("/{id_richiesta}/elenco_comunicazioni_attuazione", name="elenco_comunicazioni_attuazione")	
     * @Template("AttuazioneControlloBundle:PA/Richieste:elencoComunicazioni.html.twig")
     * @PaginaInfo(titolo="Comunicazioni",sottoTitolo="")
     * @Menuitem(menuAttivo = "elencoRichiesteAttuazione")
     * ControlloAccesso(contesto="procedura", classe="IstruttorieBundle:IstruttoriaRichiesta", opzioni={"id" = "id_istruttoria"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function elencoComunicazioniAttuazioneAction($id_richiesta) {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
        return array('richiesta' => $richiesta, 'menu' => 'comunicazioni');
    }

    /**
     * @Route("/crea_comunicazione_attuazione/{id_richiesta}", name="crea_comunicazione_attuazione")
     * ControlloAccesso(contesto="procedura", classe="IstruttorieBundle:ComunicazioneProgetto", opzioni={"id" = "id_valutazione_checklist"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function creaComunicazioneAttuazioneAction($id_richiesta) {
        if ($this->getUser()->isConsulenteFesr() == true) {
            return $this->addErrorRedirect('Non sei autorizzato ad eseguire l\'operazione', "riepilogo_richiesta_attuazione", array("id_richiesta" => $id_richiesta));
        }
        try {
            $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
            $gestore = $this->get("gestore_comunicazione_attuazione")->getGestore($richiesta->getProcedura());
            return $gestore->creaComunicazione($richiesta, 'ATTUAZIONE');
        } catch (SfingeException $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect($e->getMessage(), "riepilogo_richiesta_attuazione", array("id_richiesta" => $id_richiesta));
        } catch (\Exception $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect("Si è verificato un errore a sistema. Si prega di contattare l'assistenza", "riepilogo_richiesta_attuazione", array("id_richiesta" => $id_richiesta));
        }
    }

    /**
     * @Route("/gestione_comunicazione_attuazione/{id_comunicazione}", name="gestione_comunicazione_attuazione")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste_inviate")
     * 				})
     * @Menuitem(menuAttivo = "elencoRichiesteAttuazione")
     * @PaginaInfo(titolo="Comunicazione",sottoTitolo="inserimento di una comunicazione")
     * ControlloAccesso(contesto="richiesta", classe="IstruttorieBundle:ComunicazioneProgetto", opzioni={"id" = "id_comunicazione"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     */
    public function gestioneComunicazioneAttuazioneAction($id_comunicazione) {
        try {
            $comunicazione = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\ComunicazioneAttuazione")->find($id_comunicazione);

            $richiesta = $comunicazione->getRichiesta();
            $this->get('pagina')->aggiungiElementoBreadcrumb('Riepilogo istruttoria', $this->generateUrl("riepilogo_richiesta", array("id_richiesta" => $richiesta->getId())));

            $gestore = $this->get("gestore_comunicazione_attuazione")->getGestore($richiesta->getProcedura());
            return $gestore->gestioneComunicazioneAttuazione($comunicazione)->getResponse();
        } catch (SfingeException $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste_inviate");
        } catch (\Exception $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect("Si è verificato un errore a sistema. Si prega di contattare l'assistenza", "elenco_richieste_inviate");
        }
    }

    /**
     * @Route("/{id_comunicazione}/genera_facsimile_comunicazione_attuazione", name="genera_facsimile_comunicazione_attuazione")
     */
    public function generaFacsimileComunicazioneAttuazioneAction($id_comunicazione) {
        $comunicazione = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\ComunicazioneAttuazione")->find($id_comunicazione);
        return $this->get("gestore_comunicazione_attuazione")->getGestore($comunicazione->getRichiesta()->getProcedura())->generaFacsimileComunicazioneAttuazione($comunicazione);
    }

    /**
     * @Route("/dettaglio_comunicazione_progetto_pa/{id_comunicazione_progetto}/{da_comunicazione}", defaults={"da_comunicazione" = "false"}, name="dettaglio_comunicazione_progetto_pa")
     * @Template("IstruttorieBundle:RispostaComunicazioneProgetto:dettaglioComunicazioneProgetto.html.twig")
     * @PaginaInfo(titolo="Dettaglio comunicazione progetto")
     * @Menuitem(menuAttivo = "elencoRichiesteAttuazione")
     * @ControlloAccesso(contesto="procedura", classe="IstruttorieBundle:ComunicazioneProgetto", opzioni={"id" = "id_comunicazione_progetto"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function dettaglioComunicazioneProgettoAction($id_comunicazione_progetto, $da_comunicazione) {
        $comunicazione = $this->getEm()->getRepository("IstruttorieBundle\Entity\ComunicazioneProgetto")->find($id_comunicazione_progetto);
        $richiesta = $comunicazione->getRichiesta();
        $istruttoria = $richiesta->getIstruttoria();
        $this->get('pagina')->aggiungiElementoBreadcrumb('Riepilogo istruttoria', $this->generateUrl("riepilogo_richiesta", array("id_richiesta" => $richiesta->getId())));
        $documenti = array();
        if ($comunicazione->hasRispostaInviata()) {
            $documenti = $comunicazione->getRisposta()->getDocumenti();
        }
        $da_comunicazione == 'false' ? $da_comunicazione = false : $da_comunicazione = true;

        return array('menu' => 'comunicazioni', 'istruttoria' => $istruttoria, 'comunicazione_progetto' => $comunicazione, "documenti" => $documenti, "da_comunicazione" => $da_comunicazione);
    }

    /**
     * @Route("/{id_richiesta}/elimina_documento_comunicazione_attuazione/{id_documento}", name="elimina_documento_comunicazione_attuazione")
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function eliminaDocumentoComunicazioneAttuazioneAction($id_documento, $id_richiesta) {
        $documento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\ComunicazioneAttuazioneDocumento")->find($id_documento);
        $comunicazione = $documento->getComunicazione();
        $opzioni["url_indietro"] = $this->generateUrl("gestione_comunicazione_attuazione", array("id_comunicazione" => $comunicazione->getId()));
        $response = $this->get("gestore_comunicazione_attuazione")->getGestore($comunicazione->getRichiesta()->getProcedura())->eliminaDocumentoComunicazioneAttuazione($documento, $opzioni);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_richiesta}/scarica_comunicazione_attuazione/{id_comunicazione}", name="scarica_comunicazione_attuazione")	
     * @Template("AttuazioneControlloBundle:PA/Richieste:elencoComunicazioni.html.twig")
     * @PaginaInfo(titolo="Comunicazioni",sottoTitolo="")
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function scaricaComunicazioneProgettoAction($id_comunicazione, $id_richiesta) {

        $comunicazione = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\ComunicazioneAttuazione")->find($id_comunicazione);
        if (is_null($comunicazione)) {
            return $this->addErrorRedirect("Comunicazione non valida", "elenco_comunicazioni_attuazione", array("id_richiesta" => $id_richiesta));
        }
        if (is_null($comunicazione->getDocumento())) {
            return $this->addErrorRedirect("Nessun documento associato alla comuncazione", "elenco_comunicazioni_attuazione", array("id_richiesta" => $id_richiesta));
        }
        return $this->get("documenti")->scaricaDaId($comunicazione->getDocumento()->getId());
    }

    /**
     * @Route("/dettaglio_comunicazione_attuazione_pa/{id_comunicazione}/{da_comunicazione}", defaults={"da_comunicazione" = "false"}, name="dettaglio_comunicazione_attuazione_pa")
     * @Template("AttuazioneControlloBundle:PA/Richieste:dettaglioComunicazioneAttuazione.html.twig")
     * @PaginaInfo(titolo="Dettaglio comunicazione progetto")
     * @Menuitem(menuAttivo = "elencoRichiesteAttuazione")
     * @ControlloAccesso(contesto="procedura", classe="AttuazioneControlloBundle:ComunicazioneAttuazione", opzioni={"id" = "id_comunicazione"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     */
    public function dettaglioComunicazioneAttuazioneAction($id_comunicazione, $da_comunicazione) {
        $comunicazione = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\ComunicazioneAttuazione")->find($id_comunicazione);
        $richiesta = $comunicazione->getRichiesta();
        $this->get('pagina')->aggiungiElementoBreadcrumb('Elenco comunicazioni', $this->generateUrl("elenco_comunicazioni_attuazione", array("id_richiesta" => $richiesta->getId())));
        $documenti = array();
        if ($comunicazione->hasRispostaInviata()) {
            $documenti = $comunicazione->getRisposta()->getDocumenti();
        }
        $da_comunicazione == 'false' ? $da_comunicazione = false : $da_comunicazione = true;

        return array('menu' => 'comunicazioni', 'richiesta' => $richiesta, 'comunicazione' => $comunicazione, "documenti" => $documenti, "da_comunicazione" => $da_comunicazione);
    }

    /**
     * @Route("/estrazione_universo_progetti_atc/", name="estrazione_universo_progetti_atc")
     */
    public function estrazioniUniversoProgettiAtc() {
        return $this->get("gestore_richieste_atc")->getGestore()->estraiProgettiUniversoVolantinoAtc();
    }

    /**
     * @Route("/estrazione_valutatori", name="estrazione_valutatori")
     */
    public function estrazioneValutatoriAction(): Response {
        /** @var SpreadsheetFactory */
        $excelService = $this->get('phpoffice.spreadsheet');
        $spreadSheet = $excelService->getSpreadSheet();
        $spreadSheet->getProperties()->setCreator("Sfinge 2104-2020")
                ->setLastModifiedBy("Sfinge 2104-2020")
                ->setTitle("Esportazione procedure")
                ->setSubject("")
                ->setDescription("")
                ->setKeywords("")
                ->setCategory("");
        $procedureSheet = $spreadSheet->getActiveSheet();
        $this->procedureSheet($procedureSheet);

        return $excelService->createResponse($spreadSheet, 'estrazione valutatori.xlsx');
    }

    private function procedureSheet(Worksheet &$sheet): void {
        $sheet->setTitle('Progetti');
        $sheet->fromArray([
            'Id',
            'Bando',
            'Asse',
            'Azioni',
            'Data pubblicazione',
            'Scadenza termini di partecipazione',
            'Numero progetti finanziati',
            'Numero di progetti conclusi',
            'Numero di beneficiari',
            'Investimento totale',
            'Ammontare contributo pubblico',
            'Pagamenti (IMPORTO totale liquidato)',
            'Numero "nuovi" occupati',
        ]);

        $connection = $this->getEm()->getConnection();
        $sql = "
            select    
		distinct(po.id),
        po.titolo as titolo_procedura,
        assi.titolo as asse,              
   		(
   			select group_concat(azioni.codice SEPARATOR ', ') from azioni 
   			join procedure_operative_azioni poa on azioni.id = poa.azione_id
   			join procedure_operative po1 on poa.procedura_id = po1.id 
   			where po1.id = po.id
   		) as azioni,
        coalesce(DATE_FORMAT(po.data_pubblicazione, '%d/%m/%Y'),'-') as pubblicazione,
        coalesce(DATE_FORMAT(po.data_ora_fine_presentazione, '%d/%m/%Y'), '-') as scadenza,
        coalesce(( 
       		select count(r.id) from richieste r join procedure_operative po1 on r.procedura_id = po1.id  join istruttorie_richieste i on i.richiesta_id = r.id 
       		where i.`concessione` = 1 and po1.id = po.id and r.flag_por = 1 and r.data_cancellazione is null
       	),0) AS finanziati,
       	coalesce((
       		select count(r.id) from richieste r 
			join procedure_operative po1 on r.procedura_id = po1.id  
			join attuazione_controllo_richieste atc on atc.richiesta_id = r.id
			join pagamenti as saldo
        	on saldo.attuazione_controllo_richiesta_id = atc.id and saldo.data_cancellazione is null and saldo.stato_id = 10 and saldo.modalita_pagamento_id in (3, 4)
        	left join mandati_pagamenti as mandato on mandato.id = saldo.mandato_pagamento_id and mandato.data_cancellazione is null 
			where po1.id = po.id and r.flag_por = 1 and r.data_cancellazione is null
       	),0)  AS conclusi,
       	coalesce((
       		select count(prop.id) from richieste r 
        	join proponenti prop on prop.richiesta_id = r.id and prop.data_cancellazione is null
        	join procedure_operative po1 on r.procedura_id = po1.id
        	join istruttorie_richieste i on i.richiesta_id = r.id 
        	where po1.id = po.id and r.flag_por = 1 and r.data_cancellazione is null and i.concessione = 1 
       	),0) as proponenti,
        coalesce(( 
       		select sum(coalesce(i.costo_ammesso, 0)) from richieste r join procedure_operative po1 on r.procedura_id = po1.id  join istruttorie_richieste i on i.richiesta_id = r.id 
       		where i.concessione = 1 and po1.id = po.id and r.flag_por = 1 and r.data_cancellazione is null
       	),0) AS costo,
       	coalesce(( 
       		select sum(coalesce(i.contributo_ammesso, 0)) from richieste r join procedure_operative po1 on r.procedura_id = po1.id  join istruttorie_richieste i on i.richiesta_id = r.id 
       		where i.`concessione` = 1 and po1.id = po.id and r.flag_por = 1 and r.data_cancellazione is null
       	),0) AS contributo,
       	coalesce((
       		select sum(mandato.importo_pagato) from richieste r 
			join procedure_operative po1 on r.procedura_id = po1.id  
			join attuazione_controllo_richieste atc on atc.richiesta_id = r.id
			join pagamenti as pag
        	on pag.attuazione_controllo_richiesta_id = atc.id and pag.data_cancellazione is null
        	left join mandati_pagamenti as mandato on mandato.id = pag.mandato_pagamento_id and mandato.data_cancellazione is null 
			where po1.id = po.id and r.flag_por = 1 and r.data_cancellazione is null
       	),0) AS erogato,
       	coalesce((
       		select sum(cast( ind.valore_realizzato as UNSIGNED)) from richieste r 
			join procedure_operative po1 on r.procedura_id = po1.id 
        	left join indicatori_output as ind
        	on ind.richiesta_id=r.id
        	and ind.data_cancellazione is null
        	and ind.indicatore_id in (8,28)
        	where po1.id = po.id and r.flag_por = 1 and r.data_cancellazione is null
       	),'-') as occupati     	      	
        from procedure_operative as po
        join procedure_operative_azioni as poa on poa.procedura_id = po.id and po.data_cancellazione is null
        
        join assi on assi.id = po.asse_id and assi.id <> 8
        order by po.id
        
        
		";

        $stmt = $connection->prepare($sql);
        $stmt->execute([]);
        $values = $stmt->getIterator();
        foreach ($values as $idx => $record) {
            $sheet->fromArray($record, null, "A" . ($idx + 2));
        }
        $ultimaRiga = \count($this->getProcedure()) + 1;
        $sheet->getStyle("J2:L2$ultimaRiga")
                ->getNumberFormat()
                ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
    }
    
    protected function getProcedure(): array {
        $repository = $this->getEm()->getRepository('SfingeBundle:Procedura');
        return $repository->findAll();
    }

}
