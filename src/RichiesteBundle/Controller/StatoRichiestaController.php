<?php

namespace RichiesteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Menuitem;
use BaseBundle\Annotation\ControlloAccesso;
use BaseBundle\Controller\BaseController;
use RichiesteBundle\Service\IGestoreStatoRichiesta;
use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\HttpFoundation\Response;
use RichiesteBundle\Entity\Proponente;
use PaginaBundle\Services\Pagina;

/**
 * @Route("/stato")
 */
class StatoRichiestaController extends BaseController {
    /**
     * @Route("/piano_costi/{id_proponente}", name="piano_costi_ammesso")
     * @PaginaInfo(
     * 	titolo="Piano dei costi ammesso",
     * 	sottoTitolo="pagina per visualizzare il piano dei costi ammesso per proponente"
     * )
     * @Menuitem(menuAttivo="selezionaBando")
     * @ControlloAccesso(contesto="soggettoMandatario", classe="RichiesteBundle:Proponente", opzioni={"id" : "id_proponente"})
     * @ControlloAccesso(contesto="richiesta", classe="RichiesteBundle:Proponente", opzioni={"id" : "id_proponente"}, azione=\RichiesteBundle\Security\RichiestaVoter::READ)
     * @ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Proponente", opzioni={"id" : "id_proponente"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_proponente
     */
    public function pianoCostiEsitatoAction($id_proponente): Response {
        $proponente = $this->getProponente($id_proponente);
        $richiesta = $proponente->getRichiesta();

        /** @var Pagina $paginaService */
        $paginaService = $this->get('pagina');
        $paginaService->setMenuAttivo("elencoRichieste", $this->getSession());
        $paginaService->aggiungiElementoBreadcrumb("Elenco richieste", $this->generateUrl("elenco_richieste"));
        $paginaService->aggiungiElementoBreadcrumb("Dettaglio richiesta", $this->generateUrl("dettaglio_richiesta", ["id_richiesta" => $richiesta->getId()]));

        $statoRichiesta = $this->getStatoRichiestaService($richiesta);

        return $statoRichiesta->visualizzaPianoCosti($proponente);
    }

    protected function getStatoRichiestaService(Richiesta $richiesta): IGestoreStatoRichiesta {
        return $this->get('stato_richiesta')->getGestore($richiesta);
    }

    protected function getProponente($id_proponente): Proponente {
        /** @var Richiesta $richiesta */
        $proponente = $this->getEm()->getRepository('RichiesteBundle:Proponente')->find($id_proponente);

        return $proponente;
    }
}
