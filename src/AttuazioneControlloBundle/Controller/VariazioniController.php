<?php

namespace AttuazioneControlloBundle\Controller;

use AttuazioneControlloBundle\Entity\DocumentoVariazione;
use AttuazioneControlloBundle\Entity\VariazioneDatiBancariProponente;
use AttuazioneControlloBundle\Entity\VariazionePianoCosti;
use AttuazioneControlloBundle\Entity\VariazioneReferente;
use AttuazioneControlloBundle\Entity\VariazioneRichiesta;
use AttuazioneControlloBundle\Entity\VariazioneSedeOperativa;
use AttuazioneControlloBundle\Entity\VariazioneSingoloReferente;
use AttuazioneControlloBundle\Service\IGestoreVariazioniBando;
use AttuazioneControlloBundle\Service\Istruttoria\Variazioni\IGestoreVariazioniDatiBancari;
use AttuazioneControlloBundle\Service\Istruttoria\Variazioni\IGestoreVariazioniPianoCosti;
use AttuazioneControlloBundle\Service\Variazioni\IGestoreVariazioniConcreta;
use AttuazioneControlloBundle\Service\Variazioni\IGestoreVariazioniSedeOperativa;
use BaseBundle\Annotation\ControlloAccesso;
use BaseBundle\Exception\SfingeException;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Entity\Referente;
use RichiesteBundle\Entity\Richiesta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/beneficiario/variazioni")
 */
class VariazioniController extends \BaseBundle\Controller\BaseController {
    /**
     * @Route("/{id_richiesta}/elenco_variazioni", name="elenco_variazioni")
     * @PaginaInfo(titolo="Elenco variazioni progetto", sottoTitolo="mostra l'elenco delle variazioni richieste per un progetto")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco progetti", route="elenco_gestione_beneficiario"),
     * @ElementoBreadcrumb(testo="Elenco variazioni progetto")})
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ParamConverter("richiesta", options={"id" : "id_richiesta"})
     */
    public function elencoVariazioniAction(Richiesta $richiesta): Response {
        return $this->getGestoreVariazioniProcedura($richiesta->getProcedura())->elencoVariazioni($richiesta);
    }

    protected function getGestoreVariazioniProcedura(Procedura $procedura = null): IGestoreVariazioniBando {
        /** @var \AttuazioneControlloBundle\Service\GestoreVariazioniService $service */
        $service = $this->get("gestore_variazioni");
        $gestore = $service->getGestore($procedura);

        return $gestore;
    }

    /**
     * @Route("/{id_richiesta}/aggiungi", name="aggiungi_variazione")
     * @PaginaInfo(titolo="Creazione variazione", sottoTitolo="pagina di creazione di una variazione")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco progetti", route="elenco_gestione_beneficiario"),
     *     @ElementoBreadcrumb(testo="Elenco variazioni progetto", route="elenco_variazioni", parametri={"id_richiesta" : "id_richiesta"}),
     * @ElementoBreadcrumb(testo="creazione variazione")})
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Richiesta", opzioni={"id" : "id_richiesta"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ParamConverter("richiesta", options={"id" : "id_richiesta"})
     */
    public function aggiungiVariazioneAction(Richiesta $richiesta): Response {
        return $this->getGestoreVariazioniProcedura($richiesta->getProcedura())->aggiungiVariazione($richiesta);
    }

    /**
     * @Route("/{id_variazione}/dettaglio", name="dettaglio_variazione")
     * @PaginaInfo(titolo="Dettaglio variazione", sottoTitolo="pagina di riepilogo della variazione")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"})
     * @ControlloAccesso(contesto="variazionerichiesta", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"}, azione=\AttuazioneControlloBundle\Security\VariazioneRichiestaVoter::WRITE)
     * @ParamConverter("variazione", options={"id" : "id_variazione"})
     */
    public function dettaglioVariazioneAction(VariazioneRichiesta $variazione): Response {
        return $this->getGestoreVariazioni($variazione)->dettaglioVariazione();
    }

    /**
     * @return IGestoreVariazioniConcreta|IGestoreVariazioniDatiBancari|IGestoreVariazioniSedeOperativa|IGestoreVariazioniPianoCosti
     */
    protected function getGestoreVariazioni(VariazioneRichiesta $variazione): IGestoreVariazioniConcreta {
        /** @var \AttuazioneControlloBundle\Service\GestoreVariazioniService $factory */
        $factory = $this->get("gestore_variazioni");
        $gestore = $factory->getGestoreVariazione($variazione);

        return $gestore;
    }

    /**
     * @Route("/{id_variazione}/dati_generali", name="dati_generali_variazione")
     * @PaginaInfo(titolo="Dati generali variazione", sottoTitolo="dati generali della variazione")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"})
     * @ControlloAccesso(contesto="variazionerichiesta", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"}, azione=\AttuazioneControlloBundle\Security\VariazioneRichiestaVoter::WRITE)
     * @ParamConverter("variazione", options={"id" : "id_variazione"})
     */
    public function datiGeneraliVariazioneAction(VariazioneRichiesta $variazione): Response {
        return $this->getGestoreVariazioni($variazione)->datiGeneraliVariazione();
    }

    /**
     * @Route("/{id_variazione}/documenti", name="documenti_variazione")
     * @PaginaInfo(titolo="Documenti variazione", sottoTitolo="Documenti della variazione")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"})
     * @ControlloAccesso(contesto="variazionerichiesta", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"}, azione=\AttuazioneControlloBundle\Security\VariazioneRichiestaVoter::WRITE)
     * @ParamConverter("variazione", options={"id" : "id_variazione"})
     */
    public function gestioneDocumentiAction(VariazioneRichiesta $variazione): Response {
        return $this->getGestoreVariazioni($variazione)->gestioneDocumentiVariazione();
    }

    /**
     * @Route("/{id_variazione}/piano_costi/{annualita}/{id_proponente}", name="piano_costi_variazione", defaults={"id_proponente" : "-"})
     * @PaginaInfo(titolo="Piano costi variazione", sottoTitolo="piano costi della variazione")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"})
     * @ControlloAccesso(contesto="variazionerichiesta", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"}, azione=\AttuazioneControlloBundle\Security\VariazioneRichiestaVoter::WRITE)
     * @ParamConverter("variazione", options={"id" : "id_variazione"})
     * @param mixed $annualita
     * @param mixed $id_proponente
     */
    public function pianoCostiVariazioneAction(VariazionePianoCosti $variazione, $annualita, $id_proponente): Response {
        $proponente = "-" == $id_proponente ? null : $this->getEm()->getRepository(Proponente::class)->find($id_proponente);

        return $this->getGestoreVariazioni($variazione)->pianoCostiVariazione($annualita, $proponente);
    }

    /**
     * @Route("/{id_variazione}/valida", name="valida_variazione")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"})
     * @ControlloAccesso(contesto="variazionerichiesta", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"}, azione=\AttuazioneControlloBundle\Security\VariazioneRichiestaVoter::WRITE)
     * @ParamConverter("variazione", options={"id" : "id_variazione"})
     */
    public function validaVarizioneAction(VariazioneRichiesta $variazione): Response {
        return $this->getGestoreVariazioni($variazione)->validaVariazione();
    }

    /**
     * @Route("/{id_variazione}/scarica_variazione", name="scarica_variazione")
     * @Method({"GET"})
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"})
     * @ControlloAccesso(contesto="variazionerichiesta", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"}, azione=\AttuazioneControlloBundle\Security\VariazioneRichiestaVoter::WRITE)
     * @ParamConverter("variazione", options={"id" : "id_variazione"})
     */
    public function scaricaDomandaAction(VariazioneRichiesta $variazione): Response {
        return $this->getGestoreVariazioni($variazione)->scaricaDomanda();
    }

    /**
     * @Route("/{id_variazione}/carica_variazione_firmata", name="carica_variazione_firmata")
     * @PaginaInfo(titolo="Carica variazione firmata", sottoTitolo="pagina per caricare la variazione firmata")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"})
     * @ControlloAccesso(contesto="variazionerichiesta", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"}, azione=\AttuazioneControlloBundle\Security\VariazioneRichiestaVoter::WRITE)
     * @ParamConverter("variazione", options={"id" : "id_variazione"})
     */
    public function caricaVariazioneFirmataAction(VariazioneRichiesta $variazione): Response {
        return $this->getGestoreVariazioni($variazione)->caricaVariazioneFirmata();
    }

    /**
     * @Route("/{id_variazione}/scarica_variazione_firmata", name="scarica_variazione_firmata")
     * @Method({"GET"})
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"})
     * @ControlloAccesso(contesto="variazionerichiesta", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"}, azione=\AttuazioneControlloBundle\Security\VariazioneRichiestaVoter::WRITE)
     * @ParamConverter("variazione", options={"id" : "id_variazione"})
     */
    public function scaricaVariazioneFirmataAction(VariazioneRichiesta $variazione): Response {
        return $this->getGestoreVariazioni($variazione)->scaricaVariazioneFirmata();
    }

    /**
     * @Route("/{id_variazione}/invalida", name="invalida_variazione")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"})
     * @ControlloAccesso(contesto="variazionerichiesta", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"}, azione=\AttuazioneControlloBundle\Security\VariazioneRichiestaVoter::WRITE)
     * @ParamConverter("variazione", options={"id" : "id_variazione"})
     */
    public function invalidaVariazioneAction(VariazioneRichiesta $variazione): Response {
        return $this->getGestoreVariazioni($variazione)->invalidaVariazione();
    }

    /**
     * @Route("/{id_variazione}/invia_variazione", name="invia_variazione")
     * @Method({"GET"})
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"})
     * @ControlloAccesso(contesto="variazionerichiesta", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"}, azione=\AttuazioneControlloBundle\Security\VariazioneRichiestaVoter::WRITE)
     * @ParamConverter("variazione", options={"id" : "id_variazione"})
     */
    public function inviaVariazioneAction(VariazioneRichiesta $variazione): Response {
        $this->get('base')->checkCsrf('token');
        try {
            $response = $this->getGestoreVariazioni($variazione)->inviaVariazione();
            return $response;
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "dettaglio_variazione", ["id_variazione" => $variazione->getId()]);
        } catch (\Exception $e) {
            $this->get('logger')->error($e->getTraceAsString());
            return $this->addErrorRedirect("Errore generico", "dettaglio_variazione", ["id_variazione" => $variazione->getId()]);
        }
    }

    /**
     * @Route("/{id_documento_variazione}/elimina_documento_variazione", name="elimina_documento_variazione")
     * @ParamConverter("documento", options={"id" : "id_documento_variazione"})
     */
    public function eliminaDocumentoVariazioneAction(DocumentoVariazione $documento): Response {
        $this->get('base')->checkCsrf('token');
        $variazione = $documento->getVariazione();

        $contestoSoggetto = $this->get('contesto')->getContestoRisorsa($variazione, "soggetto");
        $accessoConsentitoS = $this->isGranted(\SoggettoBundle\Security\SoggettoVoter::ALL, $contestoSoggetto);

        $contestoRichiesta = $this->get('contesto')->getContestoRisorsa($variazione, "variazionerichiesta");
        $accessoConsentitoR = $this->isGranted(\AttuazioneControlloBundle\Security\VariazioneRichiestaVoter::WRITE, $contestoRichiesta);
        if ($accessoConsentitoS || $accessoConsentitoR) {
            return $this->getGestoreVariazioni($variazione)->eliminaDocumentoVariazione($documento);
        }

        throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException('Accesso non consentito al documento di variazione');
    }

    /**
     * @Route("/{id_variazione}/elimina", name="elimina_variazione")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"})
     * @ControlloAccesso(contesto="variazionerichiesta", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"}, azione=\AttuazioneControlloBundle\Security\VariazioneRichiestaVoter::WRITE)
     * @ParamConverter("variazione", options={"id" : "id_variazione"})
     */
    public function eliminaVariazioneAction(VariazioneRichiesta $variazione): Response {
        $this->get('base')->checkCsrf('token');
        return $this->getGestoreVariazioni($variazione)->eliminaVariazione();
    }

    /**
     * @Route("/{id_variazione}/modifica_firmatario", name="modifica_firmatario_variazione")
     * @PaginaInfo(titolo="Modifica firmatario", sottoTitolo="pagina di modifica del firmatario")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"})
     * @ControlloAccesso(contesto="variazionerichiesta", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"}, azione=\AttuazioneControlloBundle\Security\VariazioneRichiestaVoter::WRITE)
     * @ParamConverter("variazione", options={"id" : "id_variazione"})
     */
    public function modificaFirmatarioVariazioneAction(VariazioneRichiesta $variazione): Response {
        return $this->getGestoreVariazioni($variazione)->modificaFirmatario();
    }

    /**
     * @Route("/{id_variazione}/dati_bancari/{id_dati_bancari}", name="dati_bancari_variazione")
     * @PaginaInfo(titolo="Modifica dati bancari", sottoTitolo="pagina di modifica dei dati bancari")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"})
     * @ControlloAccesso(contesto="variazionerichiesta", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"}, azione=\AttuazioneControlloBundle\Security\VariazioneRichiestaVoter::WRITE)
     * @ParamConverter("dati", options={"id" : "id_dati_bancari"})
     */
    public function modificaDatiBancariVariazioneAction(VariazioneDatiBancariProponente $dati): Response {
        $variazione = $dati->getVariazione();

        return $this->getGestoreVariazioni($variazione)->modificaDatiBancariProponente($dati);
    }

    /**
     * @Route("/{id_variazione}/sede_operativa", name="sede_operativa_variazione")
     * @PaginaInfo(titolo="Modifica UL/sede del progetto", sottoTitolo="pagina di modifica della UL/sede del progetto")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"})
     * @ControlloAccesso(contesto="variazionerichiesta", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"}, azione=\AttuazioneControlloBundle\Security\VariazioneRichiestaVoter::WRITE)
     * @ParamConverter("variazione", options={"id" : "id_variazione"})
     */
    public function cambioSedeOperativaAction(VariazioneSedeOperativa $variazione): Response {
        $paginaService = $this->get('pagina');
        $paginaService->aggiungiElementoBreadcrumb(
            "Elenco progetti",
            $this->generateUrl("elenco_gestione_beneficiario")
        );
        $paginaService->aggiungiElementoBreadcrumb(
            "Elenco variazioni progetto",
            $this->generateUrl("elenco_variazioni", ["id_richiesta" => $variazione->getRichiesta()->getId()])
        );
        $paginaService->aggiungiElementoBreadcrumb(
            "Dettaglio variazione",
            $this->generateUrl("dettaglio_variazione", ["id_variazione" => $variazione->getId()])
        );

        return $this->getGestoreVariazioni($variazione)->cambioSedeOperativa();
    }

    /**
     * @Route("/{id_variazione}/referenti_variazione", name="referenti_variazione")
     * @PaginaInfo(titolo="Elenco referenti del progetto", sottoTitolo="pagina di elenco dei referenti del progetto")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"})
     * @ControlloAccesso(contesto="variazionerichiesta", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"}, azione=\AttuazioneControlloBundle\Security\VariazioneRichiestaVoter::WRITE)
     * @ParamConverter("variazione", options={"id" : "id_variazione"})
     */
    public function elencoReferentiAction(VariazioneReferente $variazione): Response {
        return $this->getGestoreVariazioni($variazione)->elencoReferenti();
    }

    /**
     * @Route("/{id_variazione}/modifica_referente/{id_referente}", name="modifica_referente_variazione")
     * @PaginaInfo(titolo="Modifica referenti del progetto", sottoTitolo="pagina di modifica dei referenti del progetto")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"})
     * @ControlloAccesso(contesto="variazionerichiesta", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"}, azione=\AttuazioneControlloBundle\Security\VariazioneRichiestaVoter::WRITE)
     * @ParamConverter("variazione", options={"id" : "id_variazione"})
     * @ParamConverter("referente", options={"id" : "id_referente"})
     */
    public function modificaReferenteAction(VariazioneReferente $variazione, Referente $referente): Response {
        return $this->getGestoreVariazioni($variazione)->modificaReferente($referente);
    }

    /**
     * @Route("/{id_variazione}/elimina_referente/{id_variazione_singolo}", name="elimina_referente_variazione")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"})
     * @ControlloAccesso(contesto="variazionerichiesta", classe="AttuazioneControlloBundle:VariazioneRichiesta", opzioni={"id" : "id_variazione"}, azione=\AttuazioneControlloBundle\Security\VariazioneRichiestaVoter::WRITE)
     * @ParamConverter("variazione", options={"id" : "id_variazione"})
     * @ParamConverter("singolo", options={"id" : "id_variazione_singolo"})
     */
    public function eliminaVariazioneReferente(VariazioneReferente $variazione, VariazioneSingoloReferente $singolo): Response {
        $this->checkCsrf('token');
        return $this->getGestoreVariazioni($variazione)->eliminaSingolaVariazione($singolo);
    }

    /**
     * @Route("/referente_inserisci/{id_variazione}/{id_referente}}", name="inserisci_referente_variazione")
     * @PaginaInfo(titolo="Registra Referente", sottoTitolo="")
     * @Menuitem(menuAttivo="elencoRichiesteGestione")
     */
    public function inserisciReferenteAction($id_variazione, $id_referente): Response {
        $route = 'modifica_referente_variazione';
        $options = [
            'id_variazione' => $id_variazione,
            'id_referente' => $id_referente,
        ];
        $url = $this->generateUrl($route, $options);

        return $this->get('inserimento_persona')->inserisciPersona($url, $route, $options, [

        ]);
    }
}
