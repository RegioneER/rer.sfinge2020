<?php

namespace AttuazioneControlloBundle\Controller\Revoche;

use BaseBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;

/**
 * @Route("/revoche/consultazione")
 */
class RevocaRecuperoConsultazioneController extends BaseController {

	/**
	 * @Route("/elenco_revoca/{sort}/{direction}/{page}", defaults={"sort" = "a.id", "direction" = "asc", "page" = "1"}, name="elenco_atti_revoca")
	 * @Menuitem(menuAttivo = "elencoAttoRevoca")
	 * @PaginaInfo(titolo="Elenco atti revoca", sottoTitolo="pagina per gestione degli atti di revoca")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco atti revoca")})
	 */
	public function elencoAttiAction() {

		$datiRicerca = new \AttuazioneControlloBundle\Form\Entity\Revoche\RicercaAttoRevoca();
		$risultato = $this->get("ricerca")->ricerca($datiRicerca);

		return $this->render('AttuazioneControlloBundle:Revoche:elencoAtti.html.twig', array('atti' => $risultato["risultato"], "formRicercaAtto" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"]));
	}

	/**
	 * @Route("/visualizza_atto_revoca/{id_atto}", name="visualizza_atto_revoca")
	 * @Template("AttuazioneControlloBundle:Revoche:atto.html.twig")
	 * @PaginaInfo(titolo="Visualizza atto",sottoTitolo="pagina per visualizzare i dati dell'atto selezionato")
	 * @Menuitem(menuAttivo = "elencoAttoRevoca")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco atti revoca", route="elenco_atti_revoca"), 
	 *                       @ElementoBreadcrumb(testo="Visualizza Atto")})
	 */
	public function visualizzaAttoAction($id_atto) {
		$em = $this->getDoctrine()->getManager();
		$atto = $em->getRepository('AttuazioneControlloBundle:Revoche\AttoRevoca')->find($id_atto);

		$vecchioDocumento = $atto->getDocumento();
		$documentoConvenzione = new \DocumentoBundle\Entity\DocumentoFile();
		$atto->setDocumento($documentoConvenzione);

		$options["readonly"] = true;
		$options["url_indietro"] = $this->generateUrl("elenco_atti_revoca");

		$options["documento_opzionale"] = true;
		$options["TIPOLOGIA_DOCUMENTO"] = $em->getRepository('DocumentoBundle\Entity\TipologiaDocumento')->findOneByCodice('ATTO_REVOCA');

		$form = $this->createForm('AttuazioneControlloBundle\Form\Revoche\AttoRevocaType', $atto, $options);

		$form_params["form"] = $form->createView();
		$form_params["atto"] = $atto;
		$form_params["mode"] = "show";
		$form_params["lettura"] = true;
		$form_params["vecchioDocumento"] = $vecchioDocumento;

		return $form_params;
	}

	/**
	 * @Route("/{id_revoca}/visualizza_revoca", name="visualizza_revoca")
	 * @Template("AttuazioneControlloBundle:Revoche:revoca.html.twig")
	 * @PaginaInfo(titolo="Nuovo Atto revoca",sottoTitolo="pagina per creare una revoca")
	 * @Menuitem(menuAttivo = "elencoRichiesteAttuazione")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Attuazione progetti")})
	 */
	public function visualizzaRevocaAction($id_revoca) {

		$em = $this->getDoctrine()->getManager();
		$revoca = $em->getRepository("AttuazioneControlloBundle\Entity\Revoche\Revoca")->find($id_revoca);

		$richiesta = $revoca->getAttuazioneControlloRichiesta()->getRichiesta();
		$procedura = $richiesta->getProcedura();
		$asse = $procedura->getAsse();

		$options["readonly"] = true;
		$options["url_indietro"] = $this->generateUrl("elenco_revoche", array("id_richiesta" => $richiesta->getId()));
		$options["mostra_indietro"] = true;

       $conPenalita = false; 
		$procedureConPenalita = array(6,7,33,65);  
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

		$form_params["form"] = $form->createView();
		$form_params["richiesta"] = $richiesta;
		$form_params["revoca"] = $revoca;
		$form_params["asse"] = $asse->getCodice();
        $form_params['penalita'] = $conPenalita;

		return $form_params;
	}

	/**
	 * @Route("/{id_recupero}/visualizza_recupero", name="visualizza_recupero")
	 * @Template("AttuazioneControlloBundle:Revoche:recupero.html.twig")
	 * @PaginaInfo(titolo="Visualizza recupero",sottoTitolo="pagina per visualizzazione recupero")
	 * @Menuitem(menuAttivo = "elencoRichiesteAttuazione")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Attuazione progetti")})
	 */
	public function visualizzaRecuperoAction($id_recupero) {

		$em = $this->getDoctrine()->getManager();
		$recupero = $em->getRepository("\AttuazioneControlloBundle\Entity\Revoche\Recupero")->find($id_recupero);
		$richiesta = $recupero->getRevoca()->getAttuazioneControlloRichiesta()->getRichiesta();
		$procedura = $richiesta->getProcedura();
		$asse = $procedura->getAsse();

		$options["readonly"] = true;
		$options["url_indietro"] = $this->generateUrl("elenco_recuperi", array("id_richiesta" => $richiesta->getId()));
		$options["mostra_indietro"] = true;
		$options['penalita'] = $recupero->getRevoca()->hasPenalita();

		$form = $this->createForm('AttuazioneControlloBundle\Form\Revoche\RecuperoType', $recupero, $options);

		$form_params["form"] = $form->createView();
		$form_params["richiesta"] = $richiesta;
		$form_params["asse"] = $asse->getCodice();
		$form_params["recupero"] = $recupero;
		$form_params["readonly"] = $options["readonly"];
		$form_params['penalita'] = $options['penalita'];


		return $form_params;
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

}
