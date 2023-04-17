<?php

namespace CipeBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use CipeBundle\Controller\CipeBaseController;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use Symfony\Component\HttpFoundation\Response;



class CipeAdminController extends CipeBaseController
{

	public function __construct() {
		parent::__construct();
		$this->setProfile("admin");
	}
	
	/**
	 * Route("/inoltra_richiesta/{id_progetto}", name="cipe_admin_inoltra_richiesta")
	 * @param integer $id_progetto
	 */
	public function inoltraRichiestaAction($id_progetto) {
		return parent::inoltraRichiestaAction($id_progetto);
	}
	
	/**
	 * Route("/ws_genera_cup/visualizza/{WsGeneraCup_id}", name="cipe_admin_visualizza_ws_genera_cup")
	 * @param integer $WsGeneraCup_id
	 */
	public function visualizzaWsGeneraCupAction($WsGeneraCup_id) {
		return parent::visualizzaWsGeneraCup($WsGeneraCup_id);
	}
	
	/**
	 * Route("/ws_genera_cup/elenco", name="cipe_admin_ws_genera_cup_elenco")
	 * @Menuitem(menuAttivo = "elencoGeneraCup")
	 * @PaginaInfo(titolo="Elenco Genera Cup", sottoTitolo="pagina per gestione delle richieste Genera Cup")
 	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco genera cup")})
	 */
	public function elencoWsGeneraCupAction() {
		return parent::elencoWsGeneraCupAction();
	}

	/**
	 * @Route("/classificazione/cup_stato/elenco/{page}", defaults={"page" = "1"}, name="cipe_admin_classificazione_cup_stato_elenco")
	 * @Menuitem(menuAttivo = "elencoCupStati")
	 * @PaginaInfo(titolo="Classificazione Cup Stati", sottoTitolo="pagina di elenco Classificazione Cup Stati")
 	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco classificazione cup stati")})
	 */
	public function elencoClassificazioneCupStatoAction() {
		return parent::elencoClassificazioneCupStatoAction();
	}


	/**
	 * @Route("/classificazione/cup_strumento_programmazione/elenco/{page}", defaults={"page" = "1"}, name="cipe_admin_classificazione_cup_strumento_programmazione_elenco")
	 * @Menuitem(menuAttivo = "elencoCupStrumentiProgrammazione")
	 * @PaginaInfo(titolo="Classificazione Cup Strumenti Programmazione", sottoTitolo="pagina di elenco Classificazione Cup Strumenti Programmazione")
 	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco classificazione cup strumenti programmazione")})
	 */
	public function elencoClassificazioneStrumentoProgrammazioneAction() {
		return parent::elencoClassificazioneStrumentoProgrammazioneAction();
	}

	
	/**
	 * @Route("/classificazione/cup_tipo_copertura_finanziaria/elenco/{page}", defaults={"page" = "1"}, name="cipe_admin_classificazione_cup_tipo_copertura_finanziaria_elenco")
	 * @Menuitem(menuAttivo = "elencoCupTipiCoperturaFinanziaria")
	 * @PaginaInfo(titolo="Classificazione Cup Tipi Copertura Finanziaria", sottoTitolo="pagina di elenco Classificazione Cup Tipi Copertura Finanziaria")
 	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco classificazione cup tipi copertura finanziaria")})
	 */
	public function elencoClassificazioneTipoCoperturaFinanziariaAction() {
		return parent::elencoClassificazioneTipoCoperturaFinanziariaAction();
	}
	
	
	/**
	 * @Route("/classificazione/cup_tipo_indirizzo/elenco/{page}", defaults={"page" = "1"}, name="cipe_admin_classificazione_cup_tipo_indirizzo_elenco")
	 * @Menuitem(menuAttivo = "elencoCupTipiIndirizzo")
	 * @PaginaInfo(titolo="Classificazione Cup Tipi Indirizzo", sottoTitolo="pagina di elenco Classificazione Cup Tipi Indirizzo")
 	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco classificazione cup tipi indirizzo")})
	 */
	public function elencoClassificazioneTipoIndirizzoAction() {
		return parent::elencoClassificazioneTipoIndirizzoAction();
	}
	
	
	/**
	 * @Route("/classificazione/cup_natura/elenco/{page}", defaults={"page" = "1"}, name="cipe_admin_classificazione_cup_natura_elenco")
	 * @Menuitem(menuAttivo = "elencoCupNatura")
	 * @PaginaInfo(titolo="Classificazione Cup Nature", sottoTitolo="pagina di elenco Classificazione Cup Nature")
 	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco classificazione cup nature")})
	 */
	public function elencoClassificazioneCupNaturaAction() {
		return parent::elencoClassificazioneCupNaturaAction();
	}
	
	/**
	 * @Route("/classificazione/cup_tipologia/elenco/{page}", defaults={"page" = "1"}, name="cipe_admin_classificazione_cup_tipologia_elenco")
	 * @Menuitem(menuAttivo = "elencoCupTipologia")
	 * @PaginaInfo(titolo="Classificazione Cup Tipologie", sottoTitolo="pagina di elenco Classificazione Cup Tipologie")
 	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco classificazione cup tipologie")})
	 */
	public function elencoClassificazioneCupTipologiaAction() {
		return parent::elencoClassificazioneCupTipologiaAction();
	}
	
	/**
	 * @Route("/classificazione/cup_settore/elenco/{page}", defaults={"page" = "1"}, name="cipe_admin_classificazione_cup_settore_elenco")
	 * @Menuitem(menuAttivo = "elencoCupSettore")
	 * @PaginaInfo(titolo="Classificazione Cup Settori", sottoTitolo="pagina di elenco Classificazione Cup Settori")
 	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco classificazione cup settori")})
	 */
	public function elencoClassificazioneCupSettoreAction() {
		return parent::elencoClassificazioneCupSettoreAction();
	}
	
	
	/**
	 * @Route("/classificazione/cup_sottosettore/elenco/{page}", defaults={"page" = "1"}, name="cipe_admin_classificazione_cup_sottosettore_elenco")
	 * @Menuitem(menuAttivo = "elencoCupSottosettore")
	 * @PaginaInfo(titolo="Classificazione Cup Sottosettori", sottoTitolo="pagina di elenco Classificazione Cup Sottosettori")
 	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco classificazione cup sottosettori")})
	 */
	public function elencoClassificazioneCupSottosettoreAction() {
		return parent::elencoClassificazioneCupSottosettoreAction();
	}
	
	/**
	 * @Route("/classificazione/cup_categoria/elenco/{page}", defaults={"page" = "1"}, name="cipe_admin_classificazione_cup_categoria_elenco")
	 * @Menuitem(menuAttivo = "elencoCupCategoria")
	 * @PaginaInfo(titolo="Classificazione Cup Categorie", sottoTitolo="pagina di elenco Classificazione Cup Categorie")
 	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco classificazione cup categorie")})
	 */	
	public function elencoClassificazioniCupCategoriaAction() {
		return parent::elencoClassificazioniCupCategoriaAction();
	}
	

	/**
	 * Route("/test_cup_batch/{n}", name="test_cup_batch")
	 */
	public function test_richiesta_cipe($n) {
		/* @var $richiesta_cipe_service \RichiesteBundle\Service\Cipe\RichiestaCipeService */
		$richiesta_cipe_service = $this->get("richiesta_cipe_service");
//		$richiesta_cipe_service->test_elaboraDettaglioElaborazioneCupArrayFromXml();
		$richiesta_cipe_service->test_GeneraRichiestaProtocollazioneBatch($n);
	}
	
	
	/**
	 * Route("/test_genera_tracciato_risposte_corrette", name="test_genera_tracciato_risposte_corrette")
	 */
	public function test_genera_tracciato_risposte_corrette() {
		
		/* @var $RichiestaRepository \RichiesteBundle\Entity\RichiestaRepository */
		$RichiestaRepository = $this->getDoctrine()->getRepository(\get_class(new \RichiesteBundle\Entity\Richiesta()));
		$Richieste = $RichiestaRepository->getRichiesteInTracciatoSenzaCup();
		
		/* @var $Richiesta \RichiesteBundle\Entity\Richiesta */
		$count = 0;
		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"
				."<DETTAGLIO_ELABORAZIONE_CUP>";
		
		foreach ($Richieste as $Richiesta) {
			$protocollo = $Richiesta->getProtocollo();
			$id_progetto = $Richiesta->getId();
			$xml.="<DETTAGLIO_CUP_GENERAZIONE id_progetto=\"$id_progetto\" codifica_locale=\"$protocollo\">"
				. "<DATI_CUP>"
				. "<CODICE_CUP>E99J14001850$count</CODICE_CUP>"
				. "</DATI_CUP></DETTAGLIO_CUP_GENERAZIONE>";
			$count++;
		}
		$xml.="</DETTAGLIO_ELABORAZIONE_CUP>";
		
		$filename = __DIR__."/../../../app/cache/tracciato_risposte_corrette.xml";
		file_put_contents($filename, $xml);
		
		return new Response("ok");
		
		
	}
	
	/**
	 * Route("/test_genera_tracciato_scarti", name="test_genera_tracciato_scarti")
	 */
	public function test_genera_tracciato_scarti() {
		/* @var $RichiestaRepository \RichiesteBundle\Entity\RichiestaRepository */
		$RichiestaRepository = $this->getDoctrine()->getRepository(\get_class(new \RichiesteBundle\Entity\Richiesta()));
		$Richieste = $RichiestaRepository->getRichiesteInTracciatoSenzaCup();
		
		/* @var $Richiesta \RichiesteBundle\Entity\Richiesta */
		$count = 0;
		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"
				."<DETTAGLIO_ELABORAZIONE_CUP>";
		
		foreach ($Richieste as $Richiesta) {
			$protocollo = $Richiesta->getProtocollo();
			$id_progetto = $Richiesta->getId();
			$xml.="<DETTAGLIO_CUP_GENERAZIONE id_progetto=\"$id_progetto\" codifica_locale=\"$protocollo\">"
				. "<MESSAGGI_DI_SCARTO>Errore scarto $count</MESSAGGI_DI_SCARTO></DETTAGLIO_CUP_GENERAZIONE>";
			$count++;
		}
		$xml.="</DETTAGLIO_ELABORAZIONE_CUP>";
		
		$filename = __DIR__."/../../../app/cache/tracciato_scarti.xml";
		file_put_contents($filename, $xml);
		
		return new Response("ok");
		
	}
}
