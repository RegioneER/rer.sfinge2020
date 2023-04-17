<?php

namespace DocumentoBundle\Controller;

use BaseBundle\Controller\BaseController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use DocumentoBundle\Form\Entity\RicercaDocumento;

class GestioneController extends BaseController
{
    /**
     * @Route("/elenco_documenti/{sort}/{direction}/{page}", defaults={"sort" = "i.id", "direction" = "asc", "page" = "1"}, name="elenco_documenti")
     * @Template()
     * @PaginaInfo(titolo="Gestione documenti", sottoTitolo="elenco dei documenti presenti a sistema")
     * @Menuitem(menuAttivo = "elenco_documenti")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Gestione documenti")})
     */
    public function elencoDocumentiAction(){

        $risultato = $this->get("ricerca")->ricerca(new RicercaDocumento());

        return array('documenti' => $risultato["risultato"],"form_ricerca"=>$risultato["form_ricerca"],"filtro_attivo"=>$risultato["filtro_attivo"]);
    }

    /**
     * @Route("/elenco_documenti_admin_pulisci", name="elenco_documenti_admin_pulisci")
     */
    public function elencoDocumentiPulisciAction() {
        $this->get("ricerca")->pulisci(new RicercaDocumento());
        return $this->redirectToRoute("elenco_documenti");
    }
 
}
