<?php

namespace MonitoraggioBundle\Controller;

use BaseBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Description of ContestoController.
 *
 * @author lfontana
 * @Route("/contesto")
 */
class ContestoController extends BaseController {
    /**
     * @PaginaInfo(titolo="Elenco tabelle di contesto", sottoTitolo="mostra l'elenco delle tabelle di contesto")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/elenco/{sort}/{direction}/{page}", defaults={ "sort": "i.id", "direction": "asc", "page": "1"}, name="elenco_tabelle_contesto")
     */
    public function elencoAction() {
        return $this->get('gestore_tabelle_contesto')->getGestore(null)->getElencoTabelle();
    }

    /**
     * @PaginaInfo(titolo="Dettaglio tabelle di contesto", sottoTitolo="mostra il contenuto delle tabelle di contesto")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco tabelle di contesto", route="elenco_tabelle_contesto"),
     * 				})
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/dettaglio/{tabellaId}/{sort}/{direction}/{page}", defaults={"tabellaId": 0, "sort": "i.id", "direction": "asc", "page": "1"}, name="dettaglio_tabelle_contesto")
     */
    public function dettaglioAction($tabellaId = 0) {
        $tabella = $this->getEm()->getRepository('MonitoraggioBundle:ElencoTabelleContesto')->findOneById($tabellaId);

        return $this->get('gestore_tabelle_contesto')->getGestore($tabella)->getElenco();
    }

    /**
     * @PaginaInfo(titolo="Elenco tabelle di contesto", sottoTitolo="mostra l'elenco delle tabelle di contesto")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/elenco_pulisci/", name="elenco_tabelle_contesto_pulisci")
     */
    public function elencoPulisciAction() {
        return $this->get('gestore_tabelle_contesto')->getGestore(null)->pulisciElenco();
    }

    /**
     * @PaginaInfo(titolo="Elenco tabelle di contesto", sottoTitolo="mostra l'elenco delle tabelle di contesto")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/dettaglio_pulisci/{tabellaId}", name="dettaglio_tabelle_contesto_pulisci", defaults={"tabellaId": ""})
     */
    public function dettaglioPulisciAction($tabellaId) {
        $tabella = $this->getEm()->getRepository('MonitoraggioBundle:ElencoTabelleContesto')->findOneById($tabellaId);

        return $this->get('gestore_tabelle_contesto')->getGestore($tabella)->pulisciElenco();
    }

    /**
     * @Route("/inserisci/{tabellaId}", name="inserisci_tabelle_contesto")
     *
     * @param int $tabellaId
     * @PaginaInfo(titolo="Inserimento nuova voce")
     * @Breadcrumb(elementi={
     *     @ElementoBreadcrumb(testo="Elenco tabelle di contesto", route="elenco_tabelle_contesto"),
     * })
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     */
    public function inserisciElementoAction($tabellaId) {
        $tabella = $this->getEm()->getRepository('MonitoraggioBundle:ElencoTabelleContesto')->findOneById($tabellaId);

        return $this->get('gestore_tabelle_contesto')->getGestore($tabella)->inserisciElemento();
    }

    /**
     * @PaginaInfo(titolo="Modifica tabella di contesto", sottoTitolo="Modifica elemento della tabelle di contesto")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/modifica/{tabellaId}/{recordId}", name="modifica_tabelle_contesto")
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     */
    public function modificaElementoAction($tabellaId, $recordId) {
        $tabella = $this->getEm()->getRepository('MonitoraggioBundle:ElencoTabelleContesto')->findOneById($tabellaId);

        return $this->get('gestore_tabelle_contesto')->getGestore($tabella)->modificaElemento($recordId);
    }

    /**
     * @PaginaInfo(titolo="Visualizza tabella di contesto", sottoTitolo="Visualizza elemento della tabella di contesto")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/visualizza/{tabellaId}/{recordId}", name="visualizza_tabelle_contesto")
     */
    public function visualizzaElementoAction($tabellaId, $recordId) {
        $tabella = $this->getEm()->getRepository('MonitoraggioBundle:ElencoTabelleContesto')->findOneById($tabellaId);

        return $this->get('gestore_tabelle_contesto')->getGestore($tabella)->visualizzaElemento($recordId);
    }
}
