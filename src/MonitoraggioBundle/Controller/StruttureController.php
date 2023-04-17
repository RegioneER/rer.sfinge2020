<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 23/06/17
 * Time: 15:53.
 */

namespace MonitoraggioBundle\Controller;

use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Menuitem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use BaseBundle\Controller\BaseController;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Description of ContestoController.
 *
 * @author afavilli
 * @Route("/strutture")
 */
class StruttureController extends BaseController {
    /**
     * @PaginaInfo(titolo="Elenco strutture protocollo", sottoTitolo="mostra l'elenco delle strutture protocollo")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/elenco/{sort}/{direction}/{page}", defaults={ "sort": "i.id", "direction": "asc", "page": "1"}, name="elenco_strutture_protocollo")
     */
    public function elencoAction() {
        return $this->get('gestore_strutture_protocollo')->getGestore(null)->getElencoStrutture();
    }

    /**
     * @PaginaInfo(titolo="Dettaglio struttura protocollo", sottoTitolo="mostra il contenuto della struttura protocollo")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco strutture protocollo", route="elenco_strutture_protocollo"),
     * 				})
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/dettaglio/{strutturaId}/{sort}/{direction}/{page}", defaults={"strutturaId": 0, "sort": "i.id", "direction": "asc", "page": "1"}, name="dettaglio_strutture_protocollo")
     */
    public function dettaglioAction($strutturaId) {
        $tabella = $this->getEm()->getRepository('MonitoraggioBundle:ElencoStruttureProtocollo')->findOneById($strutturaId);

        return $this->get('gestore_strutture_protocollo')->getGestore($tabella)->getElenco();
    }

    /**
     * @PaginaInfo(titolo="Elenco strutture protocollo", sottoTitolo="mostra l'elenco delle strutture protocollo")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/elenco_pulisci/", name="elenco_strutture_pulisci")
     */
    public function elencoPulisciAction() {
        return $this->get('gestore_strutture_protocollo')->getGestore(null)->pulisciElenco();
    }

    /**
     * @PaginaInfo(titolo="Dettaglio struttura protocollo", sottoTitolo="mostra il contenuto della struttura protocollo")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/dettaglio_pulisci/{strutturaId}", name="dettaglio_strutture_protocollo_pulisci", defaults={"strutturaId": ""})
     */
    public function dettaglioPulisciAction($strutturaId) {
        $tabella = $this->getEm()->getRepository('MonitoraggioBundle:ElencoStruttureProtocollo')->findOneById($strutturaId);

        return $this->get('gestore_strutture_protocollo')->getGestore($tabella)->pulisciElenco();
    }

    /**
     * Route("/inserisci/{tabellaId}", name="inserisci_struttura_protocollo").
     *
     * @param int $tabellaId
     * @PaginaInfo(titolo="Inserimento nuova voce")
     * @Breadcrumb(elementi={
     *     @ElementoBreadcrumb(testo="Elenco strutture protocollo", route="elenco_strutture_protocollo"),
     * })
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     */
    public function inserisciElementoAction($tabellaId) {
        $tabella = $this->getEm()->getRepository('MonitoraggioBundle:ElencoStruttureProtocollo')->findOneById($tabellaId);

        return $this->get('gestore_strutture_protocollo')->getGestore($tabella)->inserisciElemento();
    }

    /**
     * @PaginaInfo(titolo="Modifica struttura protocollo", sottoTitolo="Modifica elemento della struttura")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/modifica/{tabellaId}/{recordId}", name="modifica_struttura_protocollo")
     * @Security("has_role('ROLE_MONITORAGGIO_SCRITTURA')")
     */
    public function modificaElementoAction($tabellaId, $recordId) {
        $tabella = $this->getEm()->getRepository('MonitoraggioBundle:ElencoStruttureProtocollo')->findOneById($tabellaId);

        return $this->get('gestore_strutture_protocollo')->getGestore($tabella)->modificaElemento($recordId);
    }

    /**
     * @PaginaInfo(titolo="Visualizza struttura protocollo", sottoTitolo="Visualizza elemento della struttura")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/visualizza/{tabellaId}/{recordId}", name="visualizza_struttura_protocollo")
     */
    public function visualizzaElementoAction($tabellaId, $recordId) {
        $tabella = $this->getEm()->getRepository('MonitoraggioBundle:ElencoStruttureProtocollo')->findOneById($tabellaId);

        return $this->get('gestore_strutture_protocollo')->getGestore($tabella)->visualizzaElemento($recordId);
    }

    /**
     * @Route("/api/tc/{tabella}", name="api_monitoraggio_tc", defaults={"tabella": ""})
     */
    public function getAjaxValues($tabella = null) {
        if (!$tabella) {
            return new JsonResponse();
        }
        $request = $this->getRequest();
        try {
            $q = $request->query->get('keys');
            $result = $this->getEm()->getRepository('MonitoraggioBundle:' . $tabella)->ajaxRequest($q)->getQuery()->getResult();

            return new JsonResponse(
                   $result
                   );
        } catch (\Exception $e) {
            $this->container->get('monolog.logger.schema31')->error($e->getMessage());

            return new JSonResponse(); //Silenza errore ritornando un valore nullo
        }
    }
}
