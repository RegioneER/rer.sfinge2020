<?php

namespace AttuazioneControlloBundle\Controller\Istruttoria;

use AttuazioneControlloBundle\Entity\VariazionePianoCosti;
use AttuazioneControlloBundle\Entity\VariazioneReferente;
use AttuazioneControlloBundle\Entity\VariazioneRichiesta;
use AttuazioneControlloBundle\Entity\VariazioneSedeOperativa;
use AttuazioneControlloBundle\Form\Entity\Istruttoria\RicercaVariazioni;
use AttuazioneControlloBundle\Service\Istruttoria\IGestoreVariazioni;
use AttuazioneControlloBundle\Service\Istruttoria\Variazioni\IGestoreVariazioniDatiBancari;
use AttuazioneControlloBundle\Service\Istruttoria\Variazioni\IGestoreVariazioniPianoCosti;
use AttuazioneControlloBundle\Service\Istruttoria\Variazioni\IGestoreVariazioniReferente;
use AttuazioneControlloBundle\Service\Istruttoria\Variazioni\IGestoreVariazioniSedeOperativa;
use BaseBundle\Annotation\ControlloAccesso;
use BaseBundle\Controller\BaseController;
use BaseBundle\Exception\SfingeException;
use IstruttorieBundle\Entity\ComunicazioneProgetto;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use RichiesteBundle\Entity\Proponente;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/istruttoria/variazioni")
 */
class VariazioniController extends BaseController {
    /**
     * @Route("/elenco_variazioni/{sort}/{direction}/{page}", defaults={"sort" : "i.id", "direction" : "asc", "page" : "1"}, name="elenco_istruttoria_variazioni")
     * @PaginaInfo(titolo="Elenco variazioni in istruttoria", sottoTitolo="mostra l'elenco delle variazioni richieste e non ancora valutate")
     * @Menuitem(menuAttivo="elencoIstruttoriaVariazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco variazioni")})
     */
    public function elencoVariazioniAction(): Response {
        $datiRicerca = new RicercaVariazioni();
        $datiRicerca->setUtente($this->getUser());

        $risultato = $this->get("ricerca")->ricerca($datiRicerca);

        return $this->render('AttuazioneControlloBundle:Istruttoria\Variazioni:elencoVariazioni.html.twig', ['risultati' => $risultato["risultato"], "formRicerca" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"]]);
    }

    /**
     * @Route("/elenco_pagamenti_pulisci", name="elenco_istruttoria_variazioni_pulisci")
     */
    public function elencoVariazioniPulisciAction(): Response {
        $this->get("ricerca")->pulisci(new RicercaVariazioni());
        return $this->redirectToRoute("elenco_istruttoria_variazioni");
    }

    /**
     * @Route("/{id_variazione}/riepilogo", name="riepilogo_istruttoria_variazione")
     * @PaginaInfo(titolo="Riepilogo della variazione in istruttoria", sottoTitolo="dati riepilogativi della variazione")
     * @Menuitem(menuAttivo="elencoIstruttoriaVariazioni")
     * @ControlloAccesso(contesto="procedura", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @ParamConverter("variazione", options={"id" : "id_variazione"})
     */
    public function riepilogoVariazioneAction(VariazioneRichiesta $variazione): Response {
        return $this->getGestoreIstruttoriaVariazioni($variazione)->riepilogoVariazione();
    }

    /**
     * @return IGestoreVariazioni|IGestoreVariazioniDatiBancari|IGestoreVariazioniPianoCosti|IGestoreVariazioniSedeOperativa|IGestoreVariazioniReferente
     */
    protected function getGestoreIstruttoriaVariazioni(VariazioneRichiesta $variazione): IGestoreVariazioni {
        /** @var \AttuazioneControlloBundle\Service\Istruttoria\GestoreVariazioniService $factory */
        $factory = $this->get("gestore_istruttoria_variazioni");
        $service = $factory->getGestore($variazione);

        return $service;
    }

    /**
     * @Route("/{id_variazione}/documenti", name="documenti_istruttoria_variazione")
     * @PaginaInfo(titolo="Documenti variazione", sottoTitolo="documenti caricati per la variazione")
     * @Menuitem(menuAttivo="elencoIstruttoriaVariazioni")
     * @ParamConverter("variazione", options={"id" : "id_variazione"})
     */
    public function documentiVariazioneAction(VariazioneRichiesta $variazione): Response {
        return $this->getGestoreIstruttoriaVariazioni($variazione)->documentiVariazione();
    }

    /**
     * @Route("/{id_variazione}/piano_costi/{annualita}/{id_proponente}", name="piano_costi_istruttoria_variazione", defaults={"id_proponente" : "-"})
     * @PaginaInfo(titolo="Piano costi variazione", sottoTitolo="istruttoria del piano costi della variazione")
     * @Menuitem(menuAttivo="elencoIstruttoriaVariazioni")
     * @ParamConverter("variazione", options={"id" : "id_variazione"})
     * @param mixed $annualita
     * @param mixed $id_proponente
     */
    public function pianoCostiVariazioneAction(VariazionePianoCosti $variazione, $annualita, $id_proponente): Response {
        $proponente = "-" == $id_proponente ? null : $this->getEm()->getRepository("RichiesteBundle\Entity\Proponente")->find($id_proponente);
        return $this->getGestoreIstruttoriaVariazioni($variazione)->pianoCostiVariazione($annualita, $proponente);
    }

    /**
     * @Route("/{id_variazione}/esito_finale", name="esito_finale_istruttoria_variazioni")
     * @PaginaInfo(titolo="Esito finale istruttoria variazione")
     * @Menuitem(menuAttivo="elencoIstruttoriaVariazioni")
     * @ControlloAccesso(contesto="procedura", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @ParamConverter("variazione", options={"id" : "id_variazione"})
     */
    public function esitoFinaleAction(VariazioneRichiesta $variazione): Response {
        return $this->getGestoreIstruttoriaVariazioni($variazione)->esitoFinale();
    }

    /**
     * @Route("/{id_variazione}/totali_piano_costi_variazione", name="totali_piano_costi_variazione")
     * @PaginaInfo(titolo="Totali piano costi")
     * @Menuitem(menuAttivo="elencoIstruttoriaVariazioni")
     * @ControlloAccesso(contesto="procedura", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @ParamConverter("variazione", options={"id" : "id_variazione"})
     */
    public function totaliPianoCostiVariazioneAction(VariazionePianoCosti $variazione): Response {
        $response = $this->getGestoreIstruttoriaVariazioni($variazione)->totaliPianoCosti();
        return $response;
    }

    /**
     * @Route("/{id_variazione}/elenco_comunicazioni_variazione", name="elenco_comunicazioni_variazione")
     * @PaginaInfo(titolo="Comunicazioni", sottoTitolo="")
     * @Menuitem(menuAttivo="elencoRichiesteInviate")
     * @ControlloAccesso(contesto="procedura", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @ParamConverter("variazione", options={"id" : "id_variazione"})
     */
    public function elencoComunicazioniAction(VariazioneRichiesta $variazione): Response {
        $datiTwig = ['variazione' => $variazione, 'menu' => 'comunicazioni'];

        return $this->render("AttuazioneControlloBundle:Istruttoria:Variazioni/elencoComunicazioni.html.twig", $datiTwig);
    }

    /**
     * @Route("/crea_comunicazione_variazione/{id_variazione}", name="crea_comunicazione_variazione")
     * ControlloAccesso(contesto="procedura", classe="IstruttorieBundle:ComunicazioneProgetto", opzioni={"id" = "id_valutazione_checklist"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @ParamConverter("variazione", options={"id" : "id_variazione"})
     */
    public function creaComunicazioneVariazioneAction(VariazioneRichiesta $variazione): Response {
        try {
            if (count($variazione->getComunicazioniProgetto()) > 0) {
                $this->addFlash('error', "Impossibile inviare più di una comunicazione per la variazione");
                return $this->redirect($this->generateUrl("elenco_comunicazioni_variazione", ['id_variazione' => $variazione->getId()]));
            }
            $richiesta = $variazione->getRichiesta();
            $gestore_istruttoria = $this->get("gestore_comunicazione_progetto")->getGestore($richiesta->getProcedura());
            return $gestore_istruttoria->CreaComunicazioneProgetto($variazione, 'VARIAZIONE')->getResponse();
        } catch (SfingeException $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect($e->getMessage(), "elenco_richieste_inviate");
        } catch (\Exception $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect("Si è verificato un errore a sistema. Si prega di contattare l'assistenza", "elenco_richieste_inviate");
        }
    }

    /**
     * @Route("/gestione_comunicazione_variazione/{id_comunicazione_progetto}", name="gestione_comunicazione_variazione")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco variazioni", route="elenco_istruttoria_variazioni")
     * 				})
     * @Menuitem(menuAttivo="elencoRichiesteInviate")
     * @PaginaInfo(titolo="Comunicazione progetto", sottoTitolo="inserimento di una comunicazione di progetto")
     * ControlloAccesso(contesto="procedura", classe="IstruttorieBundle:IstruttoriaRichiesta", opzioni={"id" = "id_istruttoria"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @ParamConverter("comunicazione", options={"id" : "id_comunicazione_progetto"})
     */
    public function gestioneComunicazioneVariazioneAction(ComunicazioneProgetto $comunicazione): Response {
        try {
            $variazione = $comunicazione->getVariazione();
            $this->get('pagina')->aggiungiElementoBreadcrumb('Riepilogo variazione', $this->generateUrl("riepilogo_istruttoria_variazione", ["id_variazione" => $variazione->getId()]));

            $gestore = $this->get("gestore_comunicazione_progetto")->getGestore($variazione->getProcedura());
            return $gestore->gestioneComunicazioneProgetto($comunicazione)->getResponse();
        } catch (SfingeException $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect($e->getMessage(), "riepilogo_istruttoria_variazione", ["id_variazione" => $variazione->getId()]);
        } catch (\Exception $e) {
            $this->get("logger")->error($e->getMessage());
            return $this->addErrorRedirect("Si è verificato un errore a sistema. Si prega di contattare l'assistenza", "riepilogo_istruttoria_variazione", ["id_variazione" => $variazione->getId()]);
        }
    }

    /**
     * @Route("/dettaglio_comunicazione_variazione_pa/{id_comunicazione_progetto}/{da_comunicazione}", defaults={"da_comunicazione" : "false"}, name="dettaglio_comunicazione_variazione_pa")
     * @Template("AttuazioneControlloBundle:Istruttoria/Variazioni:dettaglioComunicazioneProgetto.html.twig")
     * @PaginaInfo(titolo="Dettaglio comunicazione progetto")
     * @Menuitem(menuAttivo="elencoRichiesteInviate")
     * @ControlloAccesso(contesto="procedura", classe="IstruttorieBundle:ComunicazioneProgetto", opzioni={"id" : "id_comunicazione_progetto"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @ParamConverter("comunicazione_progetto", options={"id" : "id_comunicazione_progetto"})
     * @param mixed $da_comunicazione
     */
    public function dettaglioComunicazioneVariazioneAction(ComunicazioneProgetto $comunicazione_progetto, $da_comunicazione) {
        $variazione = $comunicazione_progetto->getVariazione();
        $richiesta = $variazione->getRichiesta();

        $this->get('pagina')->aggiungiElementoBreadcrumb('Riepilogo istruttoria', $this->generateUrl("riepilogo_richiesta", ["id_richiesta" => $richiesta->getId()]));
        $documenti = [];
        if ($comunicazione_progetto->hasRispostaInviata()) {
            $documenti = $comunicazione_progetto->getRisposta()->getDocumenti();
        }
        'false' == $da_comunicazione ? $da_comunicazione = false : $da_comunicazione = true;

        return ['menu' => 'comunicazioni', 'variazione' => $variazione, 'comunicazione_progetto' => $comunicazione_progetto, "documenti" => $documenti, "da_comunicazione" => $da_comunicazione];
    }

    /**
     * @Route("/{id_comunicazione}/genera_facsimile_comunicazione_variazione", name="genera_facsimile_comunicazione_variazione")
     * @ParamConverter("comunicazione_progetto", options={"id" : "id_comunicazione"})
     */
    public function generaFacsimileComunicazioneProgettoAction(ComunicazioneProgetto $comunicazione_progetto) {
        return $this->get("gestore_comunicazione_progetto")->getGestore($comunicazione_progetto->getVariazione()->getProcedura())->generaFacsimileComunicazioneProgetto($comunicazione_progetto);
    }

    /**
     * @Route("/{id_variazione}/scarica_comunicazione_progetto_variazione/{id_comunicazione}", name="scarica_comunicazione_progetto_variazione")
     * @Template("IstruttorieBundle:Istruttoria:elencoComunicazioni.html.twig")
     * @PaginaInfo(titolo="Comunicazioni", sottoTitolo="")
     * @ControlloAccesso(contesto="procedura", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_comunicazione
     * @param mixed $id_variazione
     */
    public function scaricaComunicazioneProgettoAction($id_comunicazione, $id_variazione) {
        $comunicazione = $this->getEm()->getRepository("IstruttorieBundle:ComunicazioneProgetto")->find($id_comunicazione);
        if (is_null($comunicazione)) {
            return $this->addErrorRedirect("Comunicazione non valida", "elenco_istruttoria_variazioni");
        }
        if (is_null($comunicazione->getDocumento())) {
            return $this->addErrorRedirect("Nessun documento associato alla comuncazione", "elenco_istruttoria_variazioni");
        }
        return $this->get("documenti")->scaricaDaId($comunicazione->getDocumento()->getId());
    }

    /**
     * @Route("/{id_variazione}/elimina_documento_comunicazione_variazione/{id_documento}", name="elimina_documento_comunicazione_variazione")
     * @ControlloAccesso(contesto="procedura", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_documento
     * @param mixed $id_variazione
     */
    public function eliminaDocumentoComunicazioneProgettoAction($id_documento, $id_variazione): Response {
        $documento = $this->getEm()->getRepository("IstruttorieBundle\Entity\ComunicazioneProgettoDocumento")->find($id_documento);
        $comunicazione = $documento->getComunicazione();
        $opzioni["url_indietro"] = $this->generateUrl("gestione_comunicazione_variazione", ["id_comunicazione_progetto" => $comunicazione->getId()]);
        $response = $this->get("gestore_comunicazione_progetto")->getGestore($comunicazione->getVariazione()->getRichiesta()->getProcedura())->eliminaDocumentoComunicazioneRichiesta($documento, $opzioni);
        return $response->getResponse();
    }

    /**
     * @Route("/{id_variazione}/elimina_documento_istruttoria_variazione/{id_documento}", name="elimina_documento_istruttoria_variazione")
     * @ControlloAccesso(contesto="procedura", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_documento
     * @param mixed $id_variazione
     */
    public function eliminaDocumentoIstruttoriaVariazioneAction($id_documento, $id_variazione) {
        $documento = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\DocumentoVariazione")->find($id_documento);
        $variazione = $documento->getVariazione();

        return $this->getGestoreIstruttoriaVariazioni($variazione)->eliminaDocumentoIstruttoriaVariazione($documento);
    }

    /**
     * @Route("/{id_variazione}/dati_bancari/{id_proponente}", name="dati_bancari_istruttoria_variazione")
     * @PaginaInfo(titolo="Riepilogo della variazione in istruttoria", sottoTitolo="dati riepilogativi della variazione")
     * @Menuitem(menuAttivo="elencoIstruttoriaVariazioni")
     * @ControlloAccesso(contesto="procedura", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @ParamConverter("variazione", options={"id" : "id_variazione"})
     * @ParamConverter("proponente", options={"id" : "id_proponente"})
     */
    public function datiBancariVariazioneAction(VariazioneRichiesta $variazione, Proponente $proponente): Response {
        return $this->getGestoreIstruttoriaVariazioni($variazione)->dettaglioDatiBancari($proponente);
    }

    /**
     * @Route("/{id_variazione}/sede_operativa", name="sede_operativa_istruttoria_variazione")
     * @PaginaInfo(titolo="Riepilogo della variazione della UL / sede progetto", sottoTitolo="dati riepilogativi della UL / sede progetto")
     * @Menuitem(menuAttivo="elencoIstruttoriaVariazioni")
     * @ControlloAccesso(contesto="procedura", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @ParamConverter("variazione", options={"id" : "id_variazione"})
     */
    public function sedeOperativaVariazioneAction(VariazioneSedeOperativa $variazione): Response {
        return $this->getGestoreIstruttoriaVariazioni($variazione)->dettaglioSedeOperativa();
    }

    /**
     * @Route("/{id_variazione}/referente/{id_proponente}", name="referente_istruttoria_variazione")
     * @PaginaInfo(titolo="Riepilogo della variazione del referente per proponente", sottoTitolo="dati riepilogativi del referente del progetto")
     * @Menuitem(menuAttivo="elencoIstruttoriaVariazioni")
     * @ControlloAccesso(contesto="procedura", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @ParamConverter("variazione", options={"id" : "id_variazione"})
     * @ParamConverter("proponente", options={"id" : "id_proponente"})
     */
    public function referenteVariazioneAction(VariazioneReferente $variazione, Proponente $proponente): Response {
        return $this->getGestoreIstruttoriaVariazioni($variazione)->dettaglioReferente($proponente);
    }


    /**
     * @Route("/{id_variazione}/genera_excel_piano_costi_variazione", name="genera_excel_piano_costi_variazione")
     * @param VariazionePianoCosti $variazione
     * @ParamConverter("variazione", options={"id" : "id_variazione"})
     * @return mixed
     */
    public function generaExcelPianoCostiVariazioneAction(VariazionePianoCosti $variazione) {
        return $this->getGestoreIstruttoriaVariazioni($variazione)->generaExcelPianoCostiVariazione($variazione);
    }
}
